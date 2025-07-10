<template>
  <div class="min-h-9/10 bg-stone-100 dark:bg-stone-900 text-stone-800 dark:text-stone-300 flex flex-col dark:text-stone-100 items-center justify-center px-6 py-8">
    <!-- Header -->
    <div class="max-w-2xl text-center space-y-2">
      <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl dark:text-stone-300">
        Willkommen im internen Bestellsystem für Druckaufträge
      </h1>
      <p class="text-lg sm:text-xl text-stone-600 dark:text-stone-300">
        Hier können Sie bestehende Bestellungen suchen und neue Druckaufträge an PinguinDruck erfassen.
      </p>
    </div>

    <!-- Action Card -->
    <div class="bestellungen flex flex-col md:flex-row gap-8 mt-10">
      <div class="mt-10 bg-white dark:bg-stone-800 shadow-lg rounded-2xl px-8 pb-8 w-96 text-center">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Druckauftrag von b7-Bestellung bestellen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit einer Bestellung aus b7-Bestellung.
        </p>
        <router-link
          to="/search"
          class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-6 rounded-full transition duration-200"
        >
          Zur Bestellung
        </router-link>
      </div>
      <div class="mt-10 bg-white dark:bg-stone-800 shadow-lg rounded-2xl px-8 pb-8 w-96 text-center">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Druckauftrag von easyOrdner.de bestellen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit einer Bestellung von easyOrdner.de.
        </p>
        <router-link
          to="/easy-form"
          class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-6 rounded-full transition duration-200"
        >
          Zur Bestellung
        </router-link>
      </div>
      <div class="mt-10 bg-white dark:bg-stone-800 shadow-lg rounded-2xl px-8 pb-8 w-96 text-center">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Bestellübersicht - von Pinguinbestellungen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit der Suche <br>nach einer Bestellung.
        </p>
        <router-link
          to="/bestellungen"
          class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-6 rounded-full transition duration-200"
        >
          Zur Bestellsuche
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
</script>
