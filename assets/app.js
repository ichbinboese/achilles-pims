import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import axios from 'axios'

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import './styles/app.css'

axios.defaults.withCredentials = true

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)

import { useAuthStore } from './stores/auth.js'

const auth = useAuthStore()
auth.initializeToken()

// Auth-Header bei vorhandenem Token setzen
if (auth.token) {
  axios.defaults.headers.common['Authorization'] = 'Bearer ' + auth.token
}

// Navigation Guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()
  const isAuthenticated = !!auth.token
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

app.use(router)

app.use(Toast, {
  position: 'top-center',
  timeout: 5000,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  containerClassName: 'toast-container-custom'
})
app.mount('#app')