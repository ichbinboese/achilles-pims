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
// Helper, um Ref oder String sicher zu lesen
function tokenValue(maybeRef) {
  return (maybeRef && typeof maybeRef === 'object' && 'value' in maybeRef)
    ? maybeRef.value
    : maybeRef
  }
const bootToken = tokenValue(auth.token)
  if (bootToken) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + bootToken
  }

// Navigation Guard
router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore()
        const token = tokenValue(auth.token)

        // Falls Token da ist, aber user noch nicht geladen → jetzt laden
            if (token && !auth.user && typeof auth.fetchUser === 'function') {
        try { await auth.fetchUser() } catch {}
      }

        // Zugriffsschutz
            if (to.meta.requiresAuth && !token) return next('/')
        // Bereits eingeloggt? Dann Loginseite meiden
        if (to.path === '/' && token)   return next('/dashboard')
        return next()
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