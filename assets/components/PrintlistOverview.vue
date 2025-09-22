<template>
  <div class="container max-w-6xl mx-auto px-4 mt-4 bg-white dark:bg-stone-800 pb-4 mb-4 pt-4 rounded-lg shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
      <h2 class="text-xl font-semibold dark:text-stone-100">Drucklisten</h2>
      <div class="flex items-center gap-2">
        <input
          v-model.trim="q"
          type="text"
          placeholder="Suche (Nr. / Datum)"
          class="w-64 p-2 border rounded-full dark:bg-stone-700 dark:text-white"
        />
        <!-- Optional: neue Liste direkt erstellen -->
        <button
          class="px-3 py-2 rounded bg-orange-600 hover:bg-orange-700 text-white"
          @click="createFromOpen"
        >
          Liste aus offenen erstellen
        </button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full table-auto border-collapse">
        <thead>
        <tr class="bg-orange-600 text-white">
          <th class="px-4 py-2 text-left">Nr.</th>
          <th class="px-4 py-2 text-left">Erstellt</th>
          <th class="px-4 py-2 text-left">Positionen</th>
          <th class="px-4 py-2 text-left">Aktionen</th>
        </tr>
        </thead>
        <tbody>
        <tr
          v-for="pl in paginated"
          :key="pl.id"
          class="dark:odd:bg-stone-900 dark:text-stone-300 text-stone-800"
        >
          <td class="px-4 py-2 font-mono">{{ pl.number }}</td>
          <td class="px-4 py-2">
            {{ formatDate(pl.createdAt) }}
          </td>
          <td class="px-4 py-2">{{ pl.itemsCount }}</td>
          <td class="px-4 py-2">
            <div class="flex gap-2">
              <button
                class="px-3 py-1 rounded bg-stone-200 hover:bg-stone-300 dark:bg-stone-700 dark:hover:bg-stone-600"
                @click="reprint(pl)"
                title="Nachdrucken (PDF öffnen)"
              >
                Nachdrucken
              </button>
              <button
                class="px-3 py-1 rounded bg-stone-200 hover:bg-stone-300 dark:bg-stone-700 dark:hover:bg-stone-600"
                @click="download(pl)"
                title="PDF herunterladen"
              >
                Download
              </button>
              <button
                class="px-3 py-1 rounded bg-stone-200 hover:bg-stone-300 dark:bg-stone-700 dark:hover:bg-stone-600"
                @click="copyLink(pl)"
                title="PDF-Link kopieren"
              >
                Link kopieren
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="!loading && paginated.length === 0">
          <td colspan="4" class="px-4 py-6 text-center text-stone-500 dark:text-stone-400">
            Keine Drucklisten gefunden.
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls -->
    <div v-if="!loading && totalItems > 0" class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div class="text-sm dark:text-stone-300">
        Gefunden: {{ totalItems }} · Seite {{ currentPage }} / {{ totalPages }}
      </div>

      <div class="flex items-center gap-2">
        <label class="text-sm dark:text-stone-300">pro Seite</label>
        <select
          v-model.number="pageSize"
          class="p-2 border rounded-full dark:bg-stone-700 dark:text-white"
        >
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>

        <div class="flex items-center gap-1">
          <button
            class="px-2 py-1 rounded border dark:border-stone-600 disabled:opacity-50"
            :disabled="currentPage === 1"
            @click="goFirst"
            title="Erste Seite"
          >«</button>
          <button
            class="px-2 py-1 rounded border dark:border-stone-600 disabled:opacity-50"
            :disabled="currentPage === 1"
            @click="goPrev"
            title="Zurück"
          >‹</button>
          <span class="px-2 text-sm dark:text-stone-300">Seite {{ currentPage }} von {{ totalPages }}</span>
          <button
            class="px-2 py-1 rounded border dark:border-stone-600 disabled:opacity-50"
            :disabled="currentPage === totalPages"
            @click="goNext"
            title="Weiter"
          >›</button>
          <button
            class="px-2 py-1 rounded border dark:border-stone-600 disabled:opacity-50"
            :disabled="currentPage === totalPages"
            @click="goLast"
            title="Letzte Seite"
          >»</button>
        </div>
      </div>
    </div>

    <!-- Bestätigungs-Modal: Liste aus offenen erstellen -->
    <div
      v-if="showCreateConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 z-[2000] flex items-center justify-center"
    >
      <div class="bg-white dark:bg-stone-900 rounded-lg shadow-lg max-w-sm w-full p-6 text-center">
        <h3 class="text-lg font-semibold mb-4 dark:text-stone-200">
          Möchten Sie eine neue Liste aus allen offenen (listprint = false) erstellen?
        </h3>
        <div class="flex justify-center gap-4">
          <button
            @click="confirmCreate"
            class="px-4 py-2 rounded bg-orange-600 hover:bg-orange-700 text-white"
            :disabled="creating"
          >
            {{ creating ? 'Erstelle…' : 'Ja' }}
          </button>
          <button
            @click="showCreateConfirm = false"
            class="px-4 py-2 rounded bg-stone-300 hover:bg-stone-400 dark:bg-stone-700 dark:hover:bg-stone-600 dark:text-white"
          >
            Abbrechen
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="mt-6 flex justify-center">
      <svg class="animate-spin h-8 w-8 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
      </svg>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(true)
const rows = ref([])
const q = ref('')

// Pagination state
const currentPage = ref(1)
const pageSize = ref(10)

const showCreateConfirm = ref(false)
const creating = ref(false)

onMounted(load)

async function load() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/printlists')
    rows.value = (data || []).map(x => ({
      ...x,
      number: x.number ?? String(x.id).padStart(5, '0'),
    }))
    currentPage.value = 1 // bei neuem Load auf Seite 1
  } catch (e) {
    toast.error(e?.response?.data?.error || e.message)
  } finally {
    loading.value = false
  }
}

// Suche -> gefilterte Daten
const filtered = computed(() => {
  const s = q.value.trim().toLowerCase()
  if (!s) return rows.value
  return rows.value.filter(r =>
    r.number?.toLowerCase().includes(s) ||
    formatDate(r.createdAt).toLowerCase().includes(s)
  )
})

// Pagination-Helper
const totalItems = computed(() => filtered.value.length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / pageSize.value)))

// Clamp currentPage bei Änderungen (Suche, pageSize, Daten)
watch([filtered, pageSize], () => {
  if (currentPage.value > totalPages.value) currentPage.value = totalPages.value
  if (currentPage.value < 1) currentPage.value = 1
})

// Slice für aktuelle Seite
const paginated = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filtered.value.slice(start, start + pageSize.value)
})

// Navigation
function goFirst() { currentPage.value = 1 }
function goPrev()  { if (currentPage.value > 1) currentPage.value-- }
function goNext()  { if (currentPage.value < totalPages.value) currentPage.value++ }
function goLast()  { currentPage.value = totalPages.value }

function formatDate(iso) {
  try {
    const d = new Date(iso)
    return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`
  } catch { return iso }
}

function reprint(pl) {
  const url = pl.pdfUrl || `/api/printlist/${pl.id}/pdf`
  window.open(url, '_blank')
}

function download(pl) {
  const url = (pl.pdfUrl || `/api/printlist/${pl.id}/pdf`) + '?download=1'
  window.open(url, '_blank')
}

async function copyLink(pl) {
  const url = `${location.origin}${pl.pdfUrl || `/api/printlist/${pl.id}/pdf`}`
  try {
    await navigator.clipboard.writeText(url)
    toast.success('PDF-Link kopiert')
  } catch {
    toast.error('Kopieren fehlgeschlagen')
  }
}

// Button "Liste aus offenen erstellen" → Modal öffnen
function createFromOpen() {
  showCreateConfirm.value = true
}

// Modal: bestätigen → Endpoint /api/printlist/create (ohne productIds) aufrufen
async function confirmCreate() {
  creating.value = true
  try {
    const { data } = await axios.post('/api/printlist/create', {}, {
      headers: { 'Content-Type': 'application/json' }
    })
    if (data?.success) {
      toast.success(`Liste #${String(data.id).padStart(5, '0')} erstellt (${data.count} Positionen).`)
      window.open(`/api/printlist/${data.id}/pdf`, '_blank')
      showCreateConfirm.value = false
      await load()
    } else {
      toast.error(data?.error || 'Liste konnte nicht erstellt werden.')
    }
  } catch (e) {
    toast.error(e?.response?.data?.error || e.message)
  } finally {
    creating.value = false
  }
}
</script>
