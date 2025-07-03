import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const router = useRouter()

  function login(email, password) {
    return axios.post('/api/login', { email, password })
        .then(response => {
          token.value = response.data.token
          localStorage.setItem('token', token.value)
          axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
        })
  }

  function logout() {
    token.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
    router.push('/')
  }

  function initializeToken() {
    if (token.value) {
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
    }
  }

  return { token, login, logout, initializeToken }
})
