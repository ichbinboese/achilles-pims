<template>
  <div class="main-container-mini">
    <!-- Header -->
    <div class="max-w-2xl text-center space-y-4 mt-10">
      <h1 class="text-2xl font-extrabold sm:text-3xl dark:text-stone-300 px-10">
        Willkommen im internen Bestellsystem <br> für Druckaufträge
      </h1>
      <p class="text-lg sm:text-xl text-stone-600 dark:text-stone-300">
        Hier können Sie bestehende Bestellungen suchen und neue Druckaufträge an PinguinDruck erfassen.
      </p>
    </div>

    <!-- Action Card -->
    <div class="bestellungen flex flex-col space-y-6 justify-center">
      <div class="dashboard-items">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Druckauftrag von b7-Bestellung bestellen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit einer Bestellung aus b7-Bestellung.
        </p>
        <router-link
          to="/search"
          class="btn-orange btn-pims"
        >
          Zur b7 APP Bestellung
        </router-link>
      </div>
      <div class="dashboard-items">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Bestellübersicht - von Pinguinbestellungen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit der Suche <br>nach einer Bestellung.
        </p>
        <router-link
          to="/bestellungen"
          class="btn-orange btn-pims"
        >
          Zur Bestellübersicht
        </router-link>
      </div>
      <div class="dashboard-items">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Druckauftrag von easyOrdner.de bestellen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit einer Bestellung von easyOrdner.de.
        </p>
        <router-link
          to="/easy-form"
          class="btn-orange btn-pims"
        >
          Zur easyOrdner Bestellung
        </router-link>
      </div>
      <div class="dashboard-items">
        <div class="flex items-center justify-center mb-6">
        </div>
        <h2 class="text-2xl font-semibold mb-2">Bestelliste für EasyOrdner Bestellungen</h2>
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-6">
          Starten Sie direkt mit der Suche <br>nach einer Bestellung.
        </p>
        <router-link
          to="/easy-order"
          class="btn-orange btn-pims"
        >
          Zur Bestellliste
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
