<template>
  <div class="flex justify-center mt-16">
    <div class="w-full max-w-md bg-white dark:bg-stone-900 p-8 border-2 border-orange-600 rounded shadow">
      <h2 class="text-2xl font-bold text-center mb-6 dark:text-stone-200">Pinguin Bestellsystem</h2>
      <form @submit.prevent="login">
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 pl-5">
            Achilles-Benutzer
          </label>
          <input
            v-model="email"
            type="text"
            id="email"
            class="w-full border border-stone-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-600 dark:bg-stone-700 dark:text-stone-300"
            placeholder="Benutzername von Ihrem PC Login"
          />
        </div>
        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 pl-5">
            Passwort
          </label>
          <input
            v-model="password"
            type="password"
            id="password"
            class="w-full border border-stone-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-600 dark:bg-stone-700 dark:text-stone-300"
            placeholder="Passwort Ihres Achilles PC'S"
          />
        </div>
        <div class="flex items-center mb-6 pl-5">
          <input
            type="checkbox"
            id="rememberMe"
            v-model="rememberMe"
            class="h-4 w-4"
          />
          <label for="rememberMe" class="ml-2 block text-sm text-stone-700 dark:text-stone-300">
            Angemeldet bleiben
          </label>
        </div>
        <button
          type="submit"
          class="w-full bg-orange-600 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full focus:outline-none focus:ring-2 focus:ring-orange-600"
        >
          Anmelden
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const email = ref('')
const password = ref('')
const rememberMe = ref(false)

const toast = useToast()
const router = useRouter()
const auth = useAuthStore()

const login = async () => {
  try {
    await auth.login(email.value, password.value)

    // ✨ Token aus dem Store gesetzt – Auth-Header korrekt
    const { data } = await axios.get('/api/ldap-user')
    if (data.status === 'ok') {
      toast.success('Login erfolgreich')
      router.push('/dashboard')  // ← Weiterleitung hier
    } else {
      toast.error('Session konnte nicht validiert werden')
    }
  } catch (err) {
    toast.error('Login fehlgeschlagen: Benutzer oder Passwort falsch.')
    console.error(err)
  }
}
</script>