// resources/js/app.js
import { createApp }   from 'vue'
import { createPinia } from 'pinia'
import axios           from 'axios'
import App    from './App.vue'
import router from './router'
import { i18n } from './i18n'

// ─── Axios config global ──────────────────────────────────
axios.defaults.baseURL = 'http://192.168.12.125:8000'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.headers.common['Accept'] = 'application/json'

// Restaurar token si existe en localStorage
const token = localStorage.getItem('auth_token')
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

const app   = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(i18n)
app.mount('#app')