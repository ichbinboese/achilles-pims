<template>
  <div class="container mx-auto mt-12">
    <div class="search-form">
      <h2 class="text-2xl font-semibold mb-4 text-center dark:text-stone-300">Bestellung suchen</h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="relative w-full">
          <select
            v-model="fiNr"
            class="appearance-none w-full px-4 py-2 pr-10 bg-white border-2 border-stone-300 rounded-full shadow-sm dark:bg-stone-700 focus:outline-none focus:ring-1 focus:ring-orange-600 focus:border-orange-600 dark:text-stone-300"
          >
            <option disabled value="">-- bitte Firma wählen --</option>
            <option value="114">APP</option>
            <option value="104">MPP</option>
            <option value="414">KMC</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-stone-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
        </div>

        <div>
          <label for="bestnr" class="block text-sm font-medium text-stone-700 pl-5 mb-2 dark:text-stone-300">Bestellnummer:</label>
          <input
            id="bestnr"
            v-model="bestnr"
            type="text"
            class="appearance-none w-full px-4 py-2 pr-10 dark:bg-stone-700 bg-white border border-stone-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600"
            required
          />
        </div>

        <div class="text-right">
          <button
            type="submit"
            class="btn-pims btn-orange"
          >
            Suchen
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const fiNr = ref('')
const bestnr = ref('')
const router = useRouter()

function submitForm() {
  router.push({ name: 'result', query: { fiNr: fiNr.value, bestnr: bestnr.value } })
}
</script>
