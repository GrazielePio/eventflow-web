<template>
  <div class="login-container">
    <div class="login-box">
      <img src="/public/images/logo.png" alt="EventFlow" class="logo-img">
      <h2>Entrar</h2>
      
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">E-mail</label>
          <input 
            type="email" 
            id="email" 
            v-model="email"
            placeholder="Seu e-mail"
            required
          >
          <span class="error" v-if="errors.email">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="password">Senha</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            placeholder="Sua senha"
            required
          >
          <span class="error" v-if="errors.password">{{ errors.password }}</span>
        </div>

        <div class="form-group">
          <button type="submit" :disabled="loading">
            {{ loading ? 'Entrando...' : 'Entrar' }}
          </button>
        </div>

        <div class="message error" v-if="messageError">
          {{ messageError }}
        </div>

        <div class="message success" v-if="messageSuccess">
          {{ messageSuccess }}
        </div>
      </form>

      <p class="register-link">
        Não tem uma conta? <a href="/register">Cadastre-se</a>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      email: '',
      password: '',
      loading: false,
      errors: {},
      messageError: '',
      messageSuccess: ''
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      this.errors = {}
      this.messageError = ''
      this.messageSuccess = ''

      // Obter token CSRF do cookie
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      try {
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password
          })
        })

        const data = await response.json()

        if (!response.ok) {
          if (data.errors) {
            this.errors = data.errors
          } else if (data.message) {
            this.messageError = data.message
          }
          return
        }

        // Login bem sucedido
        if (data.token) {
          // 1. Salva o Token para autenticação nas próximas requisições
          localStorage.setItem('token', data.token)
          
          // 2. Salva o objeto completo (opcional, bom ter)
          localStorage.setItem('user', JSON.stringify(data.user))
          
          // 3. AQUI ESTÁ A CHAVE: Salva o nome especificamente para a Home usar
          // Verifique se no seu Laravel o campo é 'name' ou 'nome'
          localStorage.setItem('userName', data.user.name) 
          
          this.messageSuccess = 'Login realizado com sucesso!'
          
          // Redirecionar para home
          setTimeout(() => {
            
            window.location.href = '/home'
          }, 1000)
        }

      } catch (error) {
        console.error('Erro:', error)
        this.messageError = 'Erro ao conectar com o servidor. Tente novamente.'
      } finally {
        this.loading = false
      }
    }
  },
  mounted() {
    // Se já estiver logado, redirecionar para home
    const token = localStorage.getItem('token')
    if (token) {
      window.location.href = '/home'
    }
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-box {
  background: white;
  padding: 40px;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
}

.login-box h1 {
  text-align: center;
  color: #104387;
  margin-bottom: 10px;
}

.login-box h2 {
  text-align: center;
  color: #333;
  margin-bottom: 30px;
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

.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 14px;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.form-group button {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #ff3131 0%, #eb9338 100%);
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.3s;
}

.form-group button:hover:not(:disabled) {
  opacity: 0.9;
}

.form-group button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 5px;
  display: block;
}

.message {
  padding: 10px;
  border-radius: 5px;
  text-align: center;
  margin-bottom: 15px;
}

.message.error {
  background: #fee;
  color: #c33;
}

.message.success {
  background: #efe;
  color: #3c3;
}

.register-link {
  text-align: center;
  margin-top: 20px;
  color: #666;
}

.register-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.register-link a:hover {
  text-decoration: underline;
}

.logo-img {
  display: block;
  margin: 0 auto 20px;
  max-width: 180px;
  height: auto;
  border-radius: 10px;
}
</style>