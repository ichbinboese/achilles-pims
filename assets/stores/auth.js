import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user  = ref(null)
  const tokenString = computed(() => token.value || '')
  const isAuthenticated = computed(() => !!token.value)

  // Stelle sicher, dass Axios den Token verwendet
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
  }

  function login(email, password) {
    return axios.post('/api/login', { email, password }).then(response => {
      token.value = response.data.token ?? 'demo'
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
    })
  }

  function logout() {
    token.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }

  function initializeToken() {
    const stored = localStorage.getItem('token')
    if (stored) {
      token.value = stored
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + stored
    }
  }

async function fetchUser() {
        const { data } = await axios.get('/api/ldap-user')     // liefert username  evtl. email
        user.value = data
}

  return {
    token,
    user,
    tokenString,
    isAuthenticated,
    login,
    logout,
    initializeToken,
    fetchUser
  }

})