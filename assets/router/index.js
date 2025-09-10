import { createRouter, createWebHistory } from 'vue-router'

import LoginForm from '../components/LoginForm.vue'
import Dashboard from '../components/Dashboard.vue'
import SearchForm from '../components/SearchForm.vue'
import SearchResult from '../components/SearchResult.vue'
import PimsProduct from '../components/PimsProduct.vue'
import NotFound from '../components/NotFound.vue'
import EasyForm from '../components/EasyForm.vue'
import EasyResult from '../components/EasyResult.vue'
import EasyOrderOverview from '../components/EasyOrderOverview.vue'
import APPOrderOverview from '../components/APPOrderOverview.vue'

const routes = [
  { path: '/', component: LoginForm },
  { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/search', name: 'search', component: SearchForm, meta: { requiresAuth: true } },
  { path: '/result', name: 'result', component: SearchResult, meta: { requiresAuth: true } },
  { path: '/app-orders', name: 'app-orders', component: APPOrderOverview, meta: { requiresAuth: true } },
  { path: '/product/:bestnr/:position/:orderid/:ordernr', name: 'product', component: PimsProduct, props: true, meta: { requiresAuth: true } },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound },
  { path: '/easy-result', name: 'easy-result', component: EasyResult, meta: { requiresAuth: true } },
  { path: '/easy-form', name: 'easy-form', component: EasyForm, meta: { requiresAuth: true } },
  { path: '/easy-orders', name: 'easy-orders', component: EasyOrderOverview, meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
