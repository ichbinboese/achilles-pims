import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user  = ref(null)

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
    const router = useRouter();
    token.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
    router.push('/')
  }

  function initializeToken() {
    const stored = localStorage.getItem('token')
    if (stored) {
      token.value = stored
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + stored
    }
  }

  async function fetchUser() {
    // hol dir den angemeldeten User (muss im Symfony-Backend /api/user existieren)
    const { data } = await axios.get('/api/user')
    user.value = data
  }

  return {
    token,
    user,
    login,
    logout,
    initializeToken,
    fetchUser
  }

})