import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

// Importar as views
import App from './App.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'
import Home from './views/Home.vue'

// Configura o Vue Router
const routes = [
    { path: '/login', name: 'login', component: Login },
    { path: '/register', name: 'register', component: Register },
    { path: '/home', name: 'home', component: Home, meta: { requiresAuth: true } },
    { path: '/', redirect: '/home' }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Guard de rota - verifica autenticação
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    
    if (to.meta.requiresAuth && !token) {
        next('/login')
    } else if ((to.path === '/login' || to.path === '/register') && token) {
        next('/home')
    } else {
        next()
    }
})

// Configura Axios
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// Adiciona token se existir
const token = localStorage.getItem('token')
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

// Cria e monta o app Vue
const app = createApp(App)
app.config.globalProperties.$axios = axios
app.use(router)
app.mount('#app')

console.log('Vue carregado com sucesso!')