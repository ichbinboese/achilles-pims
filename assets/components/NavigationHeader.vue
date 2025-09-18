<template>
  <nav class="bg-gradient-to-r from-amber-50 to-amber-60 dark:to-stone-950 px-4 py-3 flex items-center justify-between flex-wrap border-b-2 border-orange-600 text-stone-800 gap-y-6">
    <!-- Logo -->
    <router-link to="/" class="text-xl font-semibold flex items-center space-x-4">
      <span><img src="https://www.achilles.de/wp-content/uploads/ACH_Logo_Wortbildmarke_CMYK.png" alt="achilles-logo" class="max-h-10" /></span>
      <span><img src="https://pinguindruck.de/load/images/pinguindruck.svg" class="h-10" alt="pinguin-logo" /></span>
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
    <div :class="['w-full lg:flex lg:items-center lg:w-auto', isOpen ? 'block' : 'hidden']">
      <!-- Wenn Benutzer geladen ist, zeigen wir die Links -->
      <ul v-if="user" class="lg:flex lg:space-x-2 mt-4 lg:space-y-0 space-y-4 lg:mt-0">
        <li v-if="user.username !== 'biagrie'">
          <router-link
            to="/app-orders"
            class="nav-link-items"
          >
            Bestellliste b7 - APP
          </router-link>
        </li>
        <li v-if="user.username !== 'biagri'">
          <router-link
            to="/search"
            class="nav-link-items"
          >
            Bestellung aus b7 - APP
          </router-link>
        </li>
        <li v-if="user.username === 'bboese' || user.username === 'biagri'">
          <router-link
            to="/easy-orders"
            class="nav-link-items"
          >
            Bestellliste easyOrdner.de
          </router-link>
        </li>
        <li v-if="user.username === 'bboese' || user.username === 'biagri'">
          <router-link
            to="/easy-form"
            class="nav-link-items"
          >
            Bestellung aus easyOrdner.de
          </router-link>
        </li>
        <li>
          <router-link
            to="/dashboard"
            class="nav-link-items"
          >
            Dashboard
          </router-link>
        </li>
      </ul>

      <!-- User + Logout + Darkmode -->
      <div v-if="user" class="mt-4 lg:mt-0 lg:ml-10 flex items-center space-x-4">
        <span class="text-sm text-white bg-lime-600 rounded-full px-3 py-1">Angemeldet als {{ displayFirstname }} {{ displayLastname }}</span>

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
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import axios from "axios";

const isOpen = ref(false)
const isDark = ref(false)
const ldapUser = ref(null)
const loadingUser = ref(false)

const router = useRouter()
const auth = useAuthStore()

// Einzige "user"-Quelle: Store
const user = computed(() => auth.user)
const displayFirstname = computed(() => user.value?.firstname ?? '')
const displayLastname  = computed(() => user.value?.lastname ?? '')

function toggleDarkMode() {
  isDark.value = !isDark.value
  const root = document.documentElement
  root.classList.toggle('dark', isDark.value)
  localStorage.setItem('darkmode', isDark.value ? '1' : '0')
}

async function ensureUserLoaded() {
  if (user.value) return
  loadingUser.value = true
  try {
    if (auth.fetchUser) {
      await auth.fetchUser() // befüllt auth.user aus /api/user
    }
  } finally {
    loadingUser.value = false
  }
}

onMounted(async () => {
  isDark.value = localStorage.getItem('darkmode') === '1'
  if (isDark.value) document.documentElement.classList.add('dark')
  await ensureUserLoaded()
})

// Wichtig: den Ref selbst beobachten, nicht eine Getter-Funktion
watch(auth.token, async (t) => {
  if (t) await ensureUserLoaded()
})

async function handleLogout() {
  try {
    // wichtig: REQUEST schicken, nicht router.push
    await axios.post('/api/logout', null, { withCredentials: true })
  } catch { /* 404/CSRF etc. ignorieren */ }
  auth.logout()         // Token/Header clientseitig wegräumen
  router.replace('/')   // zurück zur Login-Seite
}
</script>
