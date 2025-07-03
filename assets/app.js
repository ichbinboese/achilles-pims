import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

axios.defaults.withCredentials = true

import LoginForm from './components/LoginForm.vue'
import Dashboard from './components/Dashboard.vue'
import SearchForm from './components/SearchForm.vue'
import SearchResult from './components/SearchResult.vue'
import PimsBestellungen from "./components/PimsBestellungen.vue";
import PimsProduct from "./components/PimsProduct.vue";
import NotFound from "./components/NotFound.vue";

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import './styles/app.css'


const routes = [
  { path: '/', component: LoginForm },
  { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/search', name: 'search', component: SearchForm, meta: { requiresAuth: true } },
  { path: '/result', name: 'result', component: SearchResult, meta: { requiresAuth: true } },
  { path: '/bestellungen', name: 'bestellungen', component: PimsBestellungen, meta: { requiresAuth: true } },
  { path: '/product/:id', name: 'product', component: PimsProduct, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token')
  if (to.path !== '/' && !isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)
app.mount('#app')

app.use(Toast, {
  // 💡 Position oben zentriert (alternativ: 'top-right', 'top-left', etc.)
  position: 'top-center',
  timeout: 5000,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  showCloseButtonOnHover: false,
  hideProgressBar: false
})
