<template>
  <div class="flex justify-center mt-16">
    <div class="w-full max-w-md bg-white dark:bg-stone-900 p-8 border-2 shadow">
      <h2 class="text-2xl font-bold text-center mb-6 dark:text-stone-200">
        Pinguin Bestellsystem
      </h2>

      <!-- Wichtig: hier handleSubmit binden -->
      <form @submit.prevent="handleSubmit">
        <!-- Email -->
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 pl-5">
            Achilles-Benutzer
          </label>
          <input
            v-model="email"
            type="text"
            id="email"
            class="w-full border rounded-full px-4 py-2 dark:bg-stone-700 dark:text-stone-300"
            placeholder="Benutzername"
          />
        </div>

        <!-- Passwort -->
        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1 pl-5">
            Passwort
          </label>
          <input
            v-model="password"
            type="password"
            id="password"
            class="w-full border rounded-full px-4 py-2 dark:bg-stone-700 dark:text-stone-300"
            placeholder="Passwort"
          />
        </div>

        <!-- Remember -->
        <div class="flex items-center mb-6 pl-5">
          <input type="checkbox" id="rememberMe" v-model="rememberMe" class="h-4 w-4" />
          <label for="rememberMe" class="ml-2 text-sm text-stone-700 dark:text-stone-300">
            Angemeldet bleiben
          </label>
        </div>

        <button
          type="submit"
          class="w-full bg-orange-600 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-full focus:outline-none"
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
import { useAuthStore } from '../stores/auth.js'

const email = ref('')
const password = ref('')
const rememberMe = ref(false)

const toast = useToast()
const router = useRouter()
const auth = useAuthStore()

const handleSubmit = async () => {
  try {
    // 1) Login beim Backend → Token wird gesetzt
    await auth.login(email.value, password.value, { remember: rememberMe.value })

    // 2) User aus dem Backend laden, bevor weitergeleitet wird
    if (auth.fetchUser) {
      await auth.fetchUser()
    }

    // 3) Erfolg + Weiterleitung (jetzt ist user garantiert da)
    toast.success('Login erfolgreich')
    await router.replace('/dashboard')
  } catch (err) {
    toast.error('Login fehlgeschlagen: Benutzer oder Passwort falsch.')
    console.error(err)
  }
}
</script>

