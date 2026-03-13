<template>
  <div class="home-container">
    <header class="header">
      <div class="header-content">
        <img src="/public/images/logo.png" alt="EventFlow" class="logo-img">
        <div class="user-info">
          <span>Bem-vindo, {{ userName }}!</span>
          <button @click="handleLogout" class="btn-logout">Sair</button>
        </div>
      </div>
    </header>

    <main class="main-content">
      <div class="events-header">
        <div class="header-left">
          <h2>Próximos Eventos</h2>
          <div class="search-box">
            <input type="text" v-model="searchQuery" @input="filtrarEventos" placeholder="Pesquisar..." class="search-input">
          </div>
        </div>
        <button @click="showModal = true" class="btn-add">+ Novo Evento</button>
      </div>

      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Carregando eventos...</p>
      </div>

      <div v-else-if="eventosFiltrados.length > 0" class="events-grid">
        <div v-for="evento in eventosFiltrados" :key="evento.id" class="event-card">
          <div class="event-date">{{ formatDate(evento.data) }}</div>
          <h3>{{ evento.titulo }}</h3>
          <p class="event-local">{{ evento.local }}</p>
          <p class="event-descricao">{{ evento.descricao || 'Sem descrição' }}</p>
          <div class="event-actions" v-if="isOwner(evento)">
            <button @click="openEditModal(evento)" class="btn-edit">Editar</button>
            <button @click="confirmDelete(evento)" class="btn-delete">Excluir</button>
          </div>
        </div>
      </div>

      <div v-else-if="searchQuery" class="no-results">
        <p>Nenhum resultado para "{{ searchQuery }}"</p>
      </div>

      <div v-else class="no-events">
        <p>Nenhum evento encontrado.</p>
        <p>Seja o primeiro a criar um!</p>
      </div>

      <div v-if="messageError" class="message error">{{ messageError }}</div>
    </main>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>Criar Evento</h3>
        <form @submit.prevent="handleCreateEvent">
          <div class="form-group">
            <label>Título</label>
            <input type="text" v-model="novoEvento.titulo" required>
          </div>
          <div class="form-group">
            <label>Data</label>
            <input type="date" v-model="novoEvento.data" required>
          </div>
          <div class="form-group">
            <label>Local</label>
            <input type="text" v-model="novoEvento.local" required>
          </div>
          <div class="form-group">
            <label>Descrição</label>
            <textarea v-model="novoEvento.descricao" rows="3"></textarea>
          </div>
          <div class="modal-buttons">
            <button type="button" @click="closeModal" class="btn-cancel">Cancelar</button>
            <button type="submit" class="btn-submit">Criar</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal">
        <h3>Editar Evento</h3>
        <form @submit.prevent="handleUpdateEvent">
          <div class="form-group">
            <label>Título</label>
            <input type="text" v-model="eventoEditando.titulo" required>
          </div>
          <div class="form-group">
            <label>Data</label>
            <input type="date" v-model="eventoEditando.data" required>
          </div>
          <div class="form-group">
            <label>Local</label>
            <input type="text" v-model="eventoEditando.local" required>
          </div>
          <div class="form-group">
            <label>Descrição</label>
            <textarea v-model="eventoEditando.descricao" rows="3"></textarea>
          </div>
          <div class="modal-buttons">
            <button type="button" @click="closeEditModal" class="btn-cancel">Cancelar</button>
            <button type="submit" class="btn-submit">Salvar</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
      <div class="modal">
        <h3>Confirmar Exclusão</h3>
        <p>Excluir "{{ eventoParaExcluir?.titulo }}"?</p>
        <div class="modal-buttons">
          <button @click="cancelDelete" class="btn-cancel">Cancelar</button>
          <button @click="handleDelete" class="btn-delete">Excluir</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
  data() {
    return {
      eventos: [],
      eventosFiltrados: [],
      userName: '',
      userId: null,
      loading: true,
      showModal: false,
      showEditModal: false,
      showDeleteModal: false,
      messageError: '',
      searchQuery: '',
      novoEvento: { titulo: '', data: '', local: '', descricao: '' },
      eventoEditando: { id: null, titulo: '', data: '', local: '', descricao: '' },
      eventoParaExcluir: null
    }
  },
  methods: {
    getToken() {
      return document.querySelector('meta[name="csrf-token"]')?.content
    },
    isOwner(evento) {
      return evento.user_id === this.userId
    },
    filtrarEventos() {
      if (!this.searchQuery.trim()) {
        this.eventosFiltrados = [...this.eventos]
        return
      }
      const q = this.searchQuery.toLowerCase()
      this.eventosFiltrados = this.eventos.filter(e => 
        e.titulo.toLowerCase().includes(q) || 
        e.local.toLowerCase().includes(q) || 
        (e.descricao && e.descricao.toLowerCase().includes(q))
      )
    },
    async fetchEventos() {
      this.loading = true
      try {
        const token = localStorage.getItem('token')
        const res = await fetch('/api/eventos', {
          headers: { 
            'Authorization': token ? `Bearer ${token}` : '',
            'X-CSRF-TOKEN': this.getToken()
          }
        })
        const data = await res.json()
        if (!res.ok) { if (res.status === 401) this.handleLogout(); return }
        this.eventos = data.data || []
        this.eventosFiltrados = [...this.eventos]
      } catch (e) { console.error(e); this.messageError = 'Erro ao carregar' }
      finally { this.loading = false }
    },
    async handleCreateEvent() {
      try {
        const token = localStorage.getItem('token')
        await fetch('/api/eventos', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json', 
            'Authorization': `Bearer ${token}`,
            'X-CSRF-TOKEN': this.getToken()
          },
          body: JSON.stringify(this.novoEvento)
        })
        this.closeModal()
        this.novoEvento = { titulo: '', data: '', local: '', descricao: '' }
        await this.fetchEventos()
      } catch (e) { console.error(e) }
    },
    openEditModal(e) {
      this.eventoEditando = { id: e.id, titulo: e.titulo, data: e.data, local: e.local, descricao: e.descricao || '' }
      this.showEditModal = true
    },
    closeEditModal() { this.showEditModal = false },
    async handleUpdateEvent() {
      try {
        const token = localStorage.getItem('token')
        await fetch(`/api/eventos/${this.eventoEditando.id}`, {
          method: 'PUT',
          headers: { 
            'Content-Type': 'application/json', 
            'Authorization': `Bearer ${token}`,
            'X-CSRF-TOKEN': this.getToken()
          },
          body: JSON.stringify(this.eventoEditando)
        })
        this.closeEditModal()
        await this.fetchEventos()
      } catch (e) { console.error(e) }
    },
    confirmDelete(e) { this.eventoParaExcluir = e; this.showDeleteModal = true },
    cancelDelete() { this.showDeleteModal = false; this.eventoParaExcluir = null },
    async handleDelete() {
      try {
        const token = localStorage.getItem('token')
        await fetch(`/api/eventos/${this.eventoParaExcluir.id}`, {
          method: 'DELETE',
          headers: { 
            'Authorization': `Bearer ${token}`,
            'X-CSRF-TOKEN': this.getToken()
          }
        })
        this.cancelDelete()
        await this.fetchEventos()
      } catch (e) { console.error(e) }
    },
    closeModal() { this.showModal = false; this.novoEvento = { titulo: '', data: '', local: '', descricao: '' } },
    async handleLogout() {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    },
    formatDate(d) {
      if (!d) return '';
  
      // Divide a string 2026-03-12 em partes [2026, 03, 12]
      // Isso evita que o JS tente converter o fuso horário
      const [ano, mes, dia] = d.split('-');
      return `${dia}/${mes}/${ano}`;
  }
  },
  mounted() {
    const token = localStorage.getItem('token')
    const user = localStorage.getItem('user')
    if (!token) { window.location.href = '/login'; return }
    if (user) { const u = JSON.parse(user); this.userName = u.name; this.userId = u.id }
    this.fetchEventos()
  }
}
</script>

<style scoped>
.home-container { 
  min-height: 100vh; 
  background: #f5f5f5; 
}

.header { 
  background: linear-gradient(135deg, #667eea, #764ba2); 
  color: white; 
  padding: 20px; 
}

.header-content { 
  max-width: 1200px; 
  margin: 0 auto; 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
}

.header h1 { 
  margin: 0; 
}

.user-info { 
  display: flex; 
  gap: 15px; 
  align-items: center; 
}

.btn-logout { 
  padding: 8px 16px; 
  background: rgba(255,255,255,0.2); 
  color: white; 
  border: 1px solid white; 
  border-radius: 5px; 
  cursor: pointer; 
}

.main-content { 
  max-width: 1200px; 
  margin: 0 auto; 
  padding: 30px 20px; 
}

.events-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 30px; 
  flex-wrap: wrap; 
  gap: 15px; 
}

.header-left { 
  display: flex; 
  align-items: center; 
  gap: 20px; 
  flex: 1; 
}
.header-left h2 { 
  color: #333; 
  margin: 0; 
}

.search-box { 
  flex: 1; 
  max-width: 400px; 
}

.search-input { 
  width: 100%; 
  padding: 10px 15px; 
  border: 1px solid #ddd; 
  border-radius: 25px; 
}

.btn-add { 
  padding: 12px 24px; 
  background: linear-gradient(135deg, #667eea, #764ba2); 
  color: white; 
  border: none; 
  border-radius: 5px; 
  cursor: pointer; 
  font-weight: 600; 
}

.loading { 
  text-align: center; 
  padding: 50px; 
  color: #666; 
}

.spinner { 
  width: 40px; 
  height: 40px; 
  border: 4px solid #f3f3f3; 
  border-top: 4px solid #667eea; 
  border-radius: 50%; 
  animation: spin 1s linear infinite; 
  margin: 0 auto 15px; 
}

@keyframes spin { to { transform: rotate(360deg); } }

.events-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
  gap: 20px; 
}

.event-card { 
  background: white; 
  padding: 20px; 
  border-radius: 10px; 
  box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
}

.event-date { 
  background: linear-gradient(135deg, #667eea, #764ba2); 
  color: white; 
  padding: 8px 15px; 
  border-radius: 20px; 
  display: inline-block; 
  font-size: 14px; 
  margin-bottom: 15px; 
}

.event-card h3 { 
  color: #333; 
  margin: 0 0 10px; 
}

.event-local { 
  color: #666; 
  margin: 5px 0; 
}

.event-descricao { 
  color: #888; 
  font-size: 14px; 
}

.event-actions { 
  display: flex; 
  gap: 10px; 
  margin-top: 15px; 
  padding-top: 15px; 
  border-top: 1px solid #eee; 
}

.btn-edit { 
  flex: 1; 
  padding: 8px; 
  background: #eb9338; 
  border: none; 
  border-radius: 5px; 
  cursor: pointer; 
}

.btn-delete { 
  flex: 1; 
  padding: 8px; 
  background: #ff3131; 
  color: white; 
  border: none; 
  border-radius: 5px; 
  cursor: pointer; 
}

.no-events, .no-results { 
  text-align: center; 
  padding: 50px; 
  color: #666; 
}

.message.error { 
  background: #fee; 
  color: #c33; 
  padding: 15px; 
  border-radius: 5px; 
  margin-top: 20px; 
}

.modal-overlay { 
  position: fixed; 
  inset: 0; 
  background: rgba(0,0,0,0.5); 
  display: flex; 
  justify-content: center; 
  align-items: center; 
  z-index: 1000; 
}

.modal { 
  background: white; 
  padding: 30px; 
  border-radius: 10px; 
  width: 100%; 
  max-width: 500px; 
}

.modal h3 { 
  margin-top: 0; 
  color: #333; 
}

.form-group { 
  margin-bottom: 20px; 
}

.form-group label { 
  display: block; 
  margin-bottom: 5px; 
  color: #555; 
  font-weight: 500; 
}

.form-group input, .form-group textarea { 
  width: 100%; 
  padding: 12px; 
  border: 1px solid #ddd; 
  border-radius: 5px; 
  box-sizing: border-box; 
}

.modal-buttons { 
  display: flex; 
  gap: 10px; 
  justify-content: flex-end; 
}

.btn-cancel { 
  padding: 12px 24px; 
  background: #ddd; 
  border: none; 
  border-radius: 5px; 
  cursor: pointer; 
}

.btn-submit { 
  padding: 12px 24px; 
  background: linear-gradient(135deg, #667eea, #764ba2); 
  color: white; 
  border: none; 
  border-radius: 5px; 
  cursor: pointer; 
}

.logo-img { 
  height: 55px; 
  width: auto; 
  border-radius: 5px; 
}

</style>