<template>
  <div class="register-container">
    <div class="register-box">
      <img src="/public/images/logo.png" alt="EventFlow" class="logo-img">
      <h2>Criar Conta</h2>
      
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="name">Nome Completo</label>
          <input 
            type="text" 
            id="name" 
            v-model="name" 
            placeholder="Seu nome completo"
            required
          >
          <span class="error" v-if="errors.name">{{ errors.name }}</span>
        </div>

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
            placeholder="Mínimo 8 caracteres"
            required
          >
          <span class="error" v-if="errors.password">{{ errors.password }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar Senha</label>
          <input 
            type="password" 
            id="password_confirmation" 
            v-model="password_confirmation" 
            placeholder="Confirme sua senha"
            required
          >
        </div>

        <div class="form-group">
          <button type="submit" :disabled="loading">
            {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
          </button>
        </div>

        <div class="message error" v-if="messageError">
          {{ messageError }}
        </div>

        <div class="message success" v-if="messageSuccess">
          {{ messageSuccess }}
        </div>
      </form>

      <p class="login-link">
        Já tem uma conta? <a href="/login">Entre</a>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Register',
  data() {
    return {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      loading: false,
      errors: {},
      messageError: '',
      messageSuccess: ''
    }
  },
  methods: {
    async handleRegister() {
      this.loading = true;
      this.errors = {};
      this.messageError = '';

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      // 1. Validação de Segurança (Frontend)
      if (this.password.length < 8) {
        this.errors.password = 'A senha deve ter no mínimo 8 caracteres.';
        this.loading = false;
        return; // Para a execução aqui
      }

      if (this.password !== this.password_confirmation) {
        this.messageError = 'As senhas digitadas não coincidem.';
        this.loading = false;
        return;
      }

      try {
        const response = await fetch('/api/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            name: this.name,
            email: this.email,
            password: this.password,
            password_confirmation: this.password_confirmation
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

        // Cadastro bem sucedido
        if (data.token) {
          localStorage.setItem('token', data.token)
          localStorage.setItem('user', JSON.stringify(data.user))
          this.messageSuccess = 'Cadastro realizado com sucesso!'
          
          // Redirecionar para home
          setTimeout(() => {
            window.location.href = '/home'
          }, 1500)
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
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.register-box {
  background: white;
  padding: 40px;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
}

.register-box h1 {
  text-align: center;
  color: #667eea;
  margin-bottom: 10px;
}

.register-box h2 {
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

.login-link {
  text-align: center;
  margin-top: 20px;
  color: #666;
}

.login-link a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.login-link a:hover {
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