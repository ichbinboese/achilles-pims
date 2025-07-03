<template>
  <div class="min-h-screen bg-stone-100 dark:bg-stone-900 flex flex-col items-center justify-center text-center px-6">
    <h1 class="text-6xl font-extrabold text-orange-600 mb-4">404</h1>
    <h2 class="text-2xl font-semibold text-stone-800 dark:text-white mb-2">
      Seite nicht gefunden
    </h2>
    <p class="text-stone-600 dark:text-stone-300 mb-6 max-w-md">
      Die von dir angeforderte Seite existiert nicht oder wurde verschoben.
    </p>
    <router-link
      to="/"
      class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-5 rounded-full transition"
    >
      Zur Startseite
    </router-link>
  </div>
</template>

<script setup>
// keine Logik notwendig
</script>

<style scoped>
</style>
