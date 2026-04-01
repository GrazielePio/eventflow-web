<template>
  <div class="home-container">
    <header class="header">
      <div class="header-content">
        <img src="/public/images/logo.png" alt="EventFlow" class="logo-img">
        <div class="user-info">
          <span>Bem-vindo, <strong>{{ userName }}</strong>!</span>
          <button @click="handleLogout" class="btn-logout">Sair</button>
        </div>
      </div>
    </header>

    <main class="main-content">
      <div class="events-header">
        <div class="header-left">
          <h2>Próximos Eventos</h2>
          <div class="search-box">
            <input type="text" v-model="searchQuery" placeholder="Pesquisar..." class="search-input">
          </div>
        </div>
        <button @click="openCreateModal" class="btn-add">+ Novo Evento</button>
      </div>

      <div v-if="loading" class="loading">Carregando eventos...</div>
      
      <div v-else class="events-grid">
        <div v-for="evento in eventosFiltrados" :key="evento.id" class="event-card">
          <div class="event-banner">
            <img v-if="evento.foto && evento.foto !== ''" :src="formatarUrl(evento.foto, 'foto')" class="img-evento">
            <div v-else class="no-image">🖼️ Sem Foto</div>
          </div>

          <div class="card-body">
            <span class="category-badge">{{ evento.categoria }}</span>
            <h3>{{ evento.titulo }}</h3>
            <p class="event-desc">{{ evento.descricao || 'Sem descrição' }}</p>
            <p class="event-local">📍 {{ evento.cidade }} - {{ evento.logradouro }}</p>
            
            <a v-if="evento.manual_pdf" :href="formatarUrl(evento.manual_pdf, 'pdf')" target="_blank" class="link-pdf">
              📄 Abrir Manual (PDF)
            </a>

            <div class="event-actions">
              <button @click="openEditModal(evento)" class="btn-edit">Editar</button>
              <button @click="confirmDelete(evento)" class="btn-delete">Excluir</button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <div v-if="showModal || showEditModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <h3>{{ showEditModal ? 'Editar Evento' : 'Novo Evento' }}</h3>
        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label>Título *</label>
            <input type="text" v-model="formEvento.titulo" required>
          </div>
          
          <div class="form-group">
            <label>Descrição</label>
            <textarea v-model="formEvento.descricao" rows="2"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Data *</label>
              <input type="date" v-model="formEvento.data" required>
            </div>
            <div class="form-group">
              <label>Categoria</label>
              <select v-model="formEvento.categoria">
                <option value="Workshop">Workshop</option>
                <option value="Palestra">Palestra</option>
                <option value="Show">Show</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>CEP</label>
            <input type="text" v-model="formEvento.cep" @blur="buscarCep" placeholder="00000-000">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Rua</label>
              <input type="text" v-model="formEvento.logradouro" readonly class="readonly-input">
            </div>
            <div class="form-group">
              <label>Cidade</label>
              <input type="text" v-model="formEvento.cidade" readonly class="readonly-input">
            </div>
          </div>

          <div class="file-section">
            <label>Foto de Capa</label>
            <input type="file" @change="onFileChange($event, 'foto')" accept="image/*">
            
            <label style="margin-top: 10px; display: block;">Manual Instrutivo (PDF)</label>
            <input type="file" @change="onFileChange($event, 'pdf')" accept=".pdf">
          </div>

          <div class="modal-buttons">
            <button type="button" @click="closeModal" class="btn-cancel">Cancelar</button>
            <button type="submit" class="btn-submit">Salvar Evento</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      userName: localStorage.getItem('userName') || 'Usuário',
      eventos: [],
      loading: false,
      showModal: false,
      showEditModal: false,
      searchQuery: '',
      formEvento: { id: null, titulo: '', data: '', categoria: 'Workshop', cep: '', logradouro: '', cidade: '', estado: '', descricao: '', bairro: '' },
      fotoSelecionada: null,
      pdfSelecionado: null
    }
  },
  computed: {
    eventosFiltrados() {
      return this.eventos.filter(e => e.titulo.toLowerCase().includes(this.searchQuery.toLowerCase()));
    }
  },
  mounted() { this.listarEventos(); },
  methods: {
    formatarUrl(caminho, tipo) {
      if (!caminho) return '';
      let arquivo = caminho.replace('public/', '');
      
      
      const base = '/storage/';

      if (arquivo.includes('/')) return `${base}${arquivo}`;
      if (tipo === 'foto') return `${base}eventos/fotos/${arquivo}`;
      if (tipo === 'pdf') return `${base}eventos/manuais/${arquivo}`;
      return `${base}${arquivo}`;
    },

    onFileChange(e, tipo) {
      const file = e.target.files[0];
      if (!file) return;
      if (tipo === 'foto') this.fotoSelecionada = file;
      if (tipo === 'pdf') this.pdfSelecionado = file;
    },

    async listarEventos() {
      this.loading = true;
      try {
        const res = await axios.get('/api/eventos');
        this.eventos = res.data.data || [];
      } finally { this.loading = false; }
    },

    async buscarCep() {
      const cep = this.formEvento.cep.replace(/\D/g, '');
      if (cep.length !== 8) return;
      try {
        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await res.json();
        if (!data.erro) {
          this.formEvento.logradouro = data.logradouro;
          this.formEvento.cidade = data.localidade;
          this.formEvento.bairro = data.bairro;
          this.formEvento.estado = data.uf;
        }
      } catch (e) { console.error("Erro CEP"); }
    },

    openCreateModal() { this.closeModal(); this.showModal = true; },
    openEditModal(evento) { this.formEvento = { ...evento }; this.showEditModal = true; },

    async handleSubmit() {
      const fd = new FormData();
      
      // Adiciona dados de texto, ignorando campos de arquivo que vêm do objeto evento
      Object.keys(this.formEvento).forEach(key => {
        if (key !== 'foto' && key !== 'manual_pdf' && this.formEvento[key] !== null) {
            fd.append(key, this.formEvento[key]);
        }
      });

      // Adiciona arquivos novos se foram selecionados
      if (this.fotoSelecionada) fd.append('foto', this.fotoSelecionada);
      if (this.pdfSelecionado) fd.append('manual_pdf', this.pdfSelecionado);

      try {
        const config = { headers: { 'Content-Type': 'multipart/form-data' } };
        
        if (this.showEditModal) {
          fd.append('_method', 'PUT');
          await axios.post(`/api/eventos/${this.formEvento.id}`, fd, config);
        } else {
          await axios.post('/api/eventos', fd, config);
        }
        
        this.closeModal();
        this.listarEventos();
        alert("Evento salvo com sucesso!");
      } catch (e) {
        console.error("Erro no envio:", e.response?.data);
        alert("Erro ao salvar o evento.");
      }
    },

    async confirmDelete(evento) {
      if (confirm(`Excluir "${evento.titulo}"?`)) {
        await axios.delete(`/api/eventos/${evento.id}`);
        this.listarEventos();
      }
    },

    closeModal() {
      this.showModal = false;
      this.showEditModal = false;
      this.fotoSelecionada = null;
      this.pdfSelecionado = null;
      this.formEvento = { id: null, titulo: '', data: '', categoria: 'Workshop', cep: '', logradouro: '', cidade: '', estado: '', descricao: '', bairro: '' };
    },

    handleLogout() { localStorage.clear(); window.location.href = '/login'; }
  }
}
</script>

<style scoped>
.home-container { background: #f8fafc; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
.header { background: #4f46e5; color: white; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.header-content { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
.logo-img { height: 35px; }
.btn-logout { background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer; }
.main-content { max-width: 1200px; margin: 25px auto; padding: 0 20px; }
.events-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.search-input { padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; width: 280px; }
.btn-add { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
.events-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
.event-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.event-banner { height: 160px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
.img-evento { width: 100%; height: 100%; object-fit: cover; }
.card-body { padding: 20px; }
.category-badge { background: #e0e7ff; color: #4338ca; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
.event-desc { color: #475569; font-size: 14px; margin: 12px 0; height: 40px; overflow: hidden; line-height: 1.4; }
.event-local { font-size: 13px; color: #64748b; font-weight: 500; }
.link-pdf { color: #ef4444; font-weight: bold; text-decoration: none; display: block; margin: 15px 0; font-size: 14px; }
.event-actions { display: flex; gap: 10px; }
.btn-edit { flex: 1; background: #fbbf24; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }
.btn-delete { flex: 1; background: #ef4444; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; padding: 30px; border-radius: 15px; width: 480px; max-height: 90vh; overflow-y: auto; }
.form-group { margin-bottom: 15px; }
.form-row { display: flex; gap: 12px; }
input, select, textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; margin-top: 5px; box-sizing: border-box; }
.readonly-input { background: #f8fafc; color: #64748b; }
.file-section { background: #f8fafc; padding: 15px; border-radius: 10px; margin-top: 15px; border: 1px dashed #cbd5e1; }
.modal-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; }
.btn-cancel { background: #e2e8f0; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
.btn-submit { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
</style>