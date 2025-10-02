import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user  = ref(null)
  const tokenString = computed(() => token.value || '')
  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Stelle sicher, dass Axios den Token verwendet
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
  }

  async function login(email, password) {
    const response = await axios.post('/api/login', { email, password })
    token.value = response.data.token
    localStorage.setItem('token', token.value)
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token.value
    await fetchUser()
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