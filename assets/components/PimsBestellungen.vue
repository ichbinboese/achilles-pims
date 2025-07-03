<template>
  <div class="container mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">PIMS Bestellungen Übersicht</h1>

    <!-- Suchfeld und Filter -->
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Suche nach Bestellnummer..."
        class="w-full md:w-1/3 rounded border border-stone-300 p-2 dark:bg-stone-800 dark:border-stone-600 dark:text-white"
      />
      <button
        @click="fetchBestellungen"
        class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded"
      >
        Suchen
      </button>
    </div>

    <!-- Tabelle -->
    <div class="overflow-x-auto border rounded shadow">
      <table class="min-w-full divide-y divide-stone-300 dark:divide-stone-700">
        <thead class="bg-stone-100 dark:bg-stone-800">
        <tr>
          <th class="px-4 py-2 text-left">App-Bestellnummer</th>
          <th class="px-4 py-2 text-left">Position</th>
          <th class="px-4 py-2 text-left">PIMS-ID</th>
          <th class="px-4 py-2 text-left">PIMS-Bestellnummer</th>
        </tr>
        </thead>
        <tbody class="bg-white dark:bg-stone-900 divide-y divide-stone-300 dark:divide-stone-700">
        <tr
          v-for="bestellung in paginated"
          :key="bestellung.id"
        >
          <td class="px-4 py-2">
            <router-link
              :to="{
                      name: 'result',
                      query: {
                        fiNr: bestellung.appfirma,
                        bestnr: bestellung.appbestellnummer
                      }
                    }"
              class="text-orange-700 hover:underline"
            >
              {{ bestellung.appbestellnummer }}
            </router-link>
          </td>

          <td class="px-4 py-2">{{ bestellung.appbestellposition }}</td>
          <td class="px-4 py-2">{{ bestellung.pimsid }}</td>
          <td class="px-4 py-2">{{ bestellung.pimsbestellnummer }}</td>
        </tr>
        <tr v-if="paginated.length === 0">
          <td colspan="4" class="px-4 py-4 text-center text-stone-500">Keine Ergebnisse gefunden.</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4">
      <div class="text-sm text-stone-600 dark:text-stone-300">
        Seite {{ page }} von {{ totalPages }}
      </div>
      <div class="flex gap-2">
        <button
          @click="prevPage"
          :disabled="page === 1"
          class="px-3 py-1 rounded border bg-stone-200 hover:bg-stone-300 disabled:opacity-50 dark:bg-stone-700 dark:border-stone-600 dark:text-white"
        >
          Zurück
        </button>
        <button
          @click="nextPage"
          :disabled="page === totalPages"
          class="px-3 py-1 rounded border bg-stone-200 hover:bg-stone-300 disabled:opacity-50 dark:bg-stone-700 dark:border-stone-600 dark:text-white"
        >
          Weiter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const bestellungen = ref([])
const search = ref('')
const page = ref(1)
const perPage = 10

/**
 * Daten vom API laden
 */
const fetchBestellungen = async () => {
  try {
    const { data } = await axios.get('/api/pims-bestellungen')
    bestellungen.value = data
    page.value = 1
  } catch (err) {
    console.error('Fehler beim Laden:', err)
  }
}

/**
 * Gefilterte Daten
 */
const filtered = computed(() => {
  if (!search.value) return bestellungen.value
  const s = search.value.toLowerCase()
  return bestellungen.value.filter(
    (b) =>
      b.appbestellnummer?.toLowerCase().includes(s) ||
      b.appbestellposition?.toLowerCase().includes(s) ||
      b.pimsbestellnummer?.toLowerCase().includes(s) ||
      b.pimsid?.toLowerCase().includes(s)
  )
})

/**
 * Pagination
 */
const totalPages = computed(() =>
  Math.max(1, Math.ceil(filtered.value.length / perPage))
)
const paginated = computed(() =>
  filtered.value.slice((page.value - 1) * perPage, page.value * perPage)
)

const prevPage = () => {
  if (page.value > 1) page.value--
}
const nextPage = () => {
  if (page.value < totalPages.value) page.value++
}

onMounted(fetchBestellungen)
</script>

<style scoped>
input:focus {
  outline: none;
  border-color: #fb923c;
}
</style>
