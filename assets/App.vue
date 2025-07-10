<template>
  <div class="flex flex-col min-h-screen bg-stone-100 dark:bg-stone-900 text-stone-800 dark:text-stone-100">
    <NavigationHeader />

    <!-- Seiteninhalt -->
    <main class="flex-grow">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="text-center text-sm py-4 bg-stone-200 dark:bg-stone-800 text-gray-700 dark:text-gray-300">
      © {{ currentYear }} Benjamin Böse, Werner Achilles GmbH & Co. KG
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import NavigationHeader from './components/NavigationHeader.vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style>
.toast-container-custom {
  top: 100px !important;
}
</style>
