<template>
  <nav class=" bg-gradient-to-r from-amber-50 to-amber-60 dark:to-stone-950 px-4 py-3 flex items-center justify-between flex-wrap border-b-2 border-orange-600 text-stone-800">
    <!-- Logo -->
    <router-link to="/" class="text-xl font-semibold flex items-center space-x-4">
      <span><img src="https://www.achilles.de/wp-content/uploads/ACH_Logo_Wortbildmarke_CMYK.png" class="max-h-10" /></span>
      <span><img src="https://pinguindruck.de/load/images/pinguindruck.svg" class="h-10" /></span>
            <span class="ml-20">Druckbogenbestellsystem</span>
    </router-link>

    <!-- Toggler Button -->
    <button
      @click="isOpen = !isOpen"
      class="block lg:hidden text-stone-300 hover:text-white focus:outline-none"
    >
      <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- Menu -->
    <div :class="['w-full lg:flex lg:items-center lg:w-auto', isOpen ? 'block' : 'hidden']" v-if="auth.token">
      <ul class="lg:flex lg:space-x-6 mt-4 lg:space-y-0 space-y-4 lg:mt-0">
        <li>
          <router-link
            to="/bestellungen"
            class="block py-2 px-3 hover:bg-stone-400 hover:text-stone-800 rounded-full dark:hover:bg-stone-300 text-stone-100 bg-orange-600"
          >
            Bestellungen suchen
          </router-link>
        </li>
        <li>
          <router-link
            to="/search"
            class="block py-2 px-3 hover:bg-stone-400 hover:text-stone-800 rounded-full dark:hover:bg-stone-300 text-stone-100 bg-orange-600"
          >
            Bestellung aus b7
          </router-link>
        </li>
        <li>
          <router-link
            to="/search-easy"
            class="block py-2 px-3 hover:bg-stone-400 hover:text-stone-800 rounded-full dark:hover:bg-stone-300 text-stone-100 bg-orange-600"
          >
            Bestellung von easyOrdner.de
          </router-link>
        </li>
        <li>

          <router-link
            to="/dashboard"
            class="block py-2 px-3 hover:bg-stone-400 hover:text-stone-800 rounded-full dark:hover:bg-stone-300 text-stone-100 bg-orange-600"
          >
            Dashboard
          </router-link>
        </li>
      </ul>

      <!-- User + Logout + Darkmode -->
      <div class="mt-4 lg:mt-0 lg:ml-10 flex items-center space-x-4">
        <span class="text-sm text-white bg-lime-600 rounded-full px-3 py-1">
          Angemeldet als {{ ldapUser }}
        </span>

        <!-- Darkmode Toggle -->
        <button
          @click="toggleDarkMode"
          class="bg-stone-200 hover:bg-stone-700 dark:bg-stone-900 dark:hover:bg-stone-700 text-white text-sm px-3 py-1 rounded-full"
        >
          <span v-if="isDark">🌙</span>
          <span v-else>☀️</span>
        </button>

        <!-- Logout -->
        <button
          @click="handleLogout"
          class="bg-stone-700 hover:bg-stone-300 hover:text-stone-800 text-white text-sm py-1 px-3 rounded-full"
        >
          Logout
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from "axios";

const isOpen = ref(false)
const isDark = ref(false)
const ldapUser = ref('')
const auth = useAuthStore()

// Toggle dark mode class on <html>
function toggleDarkMode() {
  isDark.value = !isDark.value
  const root = document.documentElement
  root.classList.toggle('dark', isDark.value)
  localStorage.setItem('darkmode', isDark.value ? '1' : '0')
}

// Restore state on load
onMounted(async () => {
  isDark.value = localStorage.getItem('darkmode') === '1'
  if (isDark.value) document.documentElement.classList.add('dark')

  try {
    axios.defaults.headers.common['Authorization'] = `Bearer ${localStorage.getItem('token')}`
    const { data } = await axios.get('/api/ldap-user', { withCredentials: true })
    console.log('LDAP-User:', data) // 🐞 Hier wird das Ergebnis in der Browser-Konsole ausgegeben
    if (data.status === 'ok') {
      ldapUser.value = data.firstname || data.username
    }
  } catch (error) {
    console.warn('LDAP-User konnte nicht geladen werden')
    console.error('Fehler beim Laden des LDAP-Benutzers:', error)
  }
})


function handleLogout() {
  auth.token = null
  localStorage.removeItem('token')
  delete axios.defaults.headers.common['Authorization']
  window.location.href = '/'
}
</script>
