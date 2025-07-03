<template>
  <div class="flex flex-col min-h-screen">
    <!-- Hauptinhalt -->
    <main class="flex-grow">
      <!-- Dein Dashboard-Inhalt hier -->
    </main>

    <!-- Footer -->
    <footer class="bg-stone-200 dark:bg-stone-800 text-center text-sm text-gstone--700 dark:text-stone-300 py-4">
      © {{ currentYear }} Benjamin Böse, Werner Achilles GmbH & Co. KG
    </footer>
  </div>
</template>

<script setup>
const currentYear = new Date().getFullYear()
</script>
