<template>
  <div class="container max-w-6xl mx-auto px-4 mt-4 bg-white dark:bg-stone-800 pb-4 mb-4">
    <!-- Button zum Öffnen des Bestätigungs-Modal -->
    <div class="flex justify-end my-6">
      <button @click="showConfirm = true" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-full mt-4">
        Bestellung aufgeben
      </button>
    </div>

    <!-- Modal zur Bestätigung -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-stone-800 rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-semibold mb-4 dark:text-stone-200">
          Möchten Sie die Bestellung bei Pinguin-Druck aufgeben?
        </h2>
        <div class="flex justify-end space-x-4">
          <button @click="showConfirm = false" class="px-4 py-2 bg-stone-300 dark:bg-stone-700 rounded">
            Abbrechen
          </button>
          <button @click="submitOrder" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded">
            Ja, absenden
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center h-full">
      <svg
        class="animate-spin h-8 w-8 text-orange-600"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12" cy="12" r="10"
          stroke="currentColor" stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
        />
      </svg>
    </div>

    <div
      v-else-if="error"
      class="bg-red-100 border border-red-500 text-red-700 px-4 pt-3 pb-6 rounded mb-6"
    >
      {{ error }}
    </div>
    <div class="flex flex-col md:flex-row gap-4 mx-auto">
      <div class="overflow-hidden rounded-lg shadow-lg w-2/3">
        <table class="w-full table-auto border-collapse">
          <thead>
          <tr class="bg-orange-600">
            <th class="px-4 py-2 text-left">Pos</th>
            <th class="px-4 py-2 text-left">Bestellnr</th>
            <th class="px-4 py-2 text-left">Titel</th>
          </tr>
          </thead>
          <tbody>
          <template v-for="item in results" :key="item.oxid">
            <tr class="dark:odd:bg-stone-900 dark:text-stone-300 text-stone-800">
              <td class="px-4 py-2">{{ item.ddposition * 10 }}</td>
              <td class="px-4 py-2">{{ item.oxordernr }}</td>
              <td class="px-4 py-2">{{ item.oxtitle }}</td>
            </tr>
            <tr class="bg-stone-100 dark:bg-stone-700 dark:text-stone-300">
              <td class="px-4 py-2 text-sm" colspan="3">Art-Nr.: {{ item.oxartnum }}</td>
            </tr>
            <tr class="bg-stone-100 dark:bg-stone-700 dark:text-stone-300">
              <td class="px-4 py-2" colspan="3"><strong>Auflage:</strong> {{ item.oxamount }}</td>
            </tr>
            <tr class="bg-stone-100 dark:bg-stone-700 dark:text-stone-300">
              <td class="px-4 py-2 whitespace-pre-line" colspan="3">
                {{ item.oxshortdesc }}
              </td>
            </tr>
          </template>
          </tbody>
        </table>
      </div>
      <div class="mt-4 md:mt-0 flex-1 w-1/3">
        <!-- Farben-Auswahl basierend auf Mapping -->
        <form class="flex flex-col md:flex-row md:items-center gap-4 mb-4 mt-6">
          <div>
            <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1" for="color">
              Farben
            </label>
            <select
              id="color"
              v-model="form.color"
              class="w-full p-2 border rounded dark:bg-stone-800 dark:text-white"
              required
            >
              <option value="">Bitte wählen...</option>
              <option v-for="opt in colorOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route       = useRoute()
const orderNr     = route.query.orderNr || ''
const loading     = ref(true)
const error       = ref(null)
const results     = ref([])
const showConfirm = ref(false)

// Formular-Daten inkl. Ausgewählte Farbe und aktueller oxartnum
const form = reactive({
  oxartnum: '',
  color: ''
})

// Alle Druckfarben
const druckfarben = ref([])

// Daten laden…
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/easy-search', { params: { orderNr } })
    results.value = data.map(i => ({
      oxid:         i.oxid,
      oxordernr:    i.oxordernr,
      oxtitle:      i.oxtitle,
      oxartnum:     i.oxartnum,
      oxshortdesc:  i.oxshortdesc,
      ddposition:   i.ddposition,
      oxamount:     i.oxamount
    }))
    // Setze form.oxartnum auf die erste Position
    form.oxartnum = results.value[0]?.oxartnum || ''

    // Druckfarben laden
    const resp = await axios.get('/api/pims-druckfarben')
    druckfarben.value = resp.data
  } catch (err) {
    error.value = err.response?.data?.message || err.message
  } finally {
    loading.value = false
  }
})

// Computed: Optionen für das Farben-Select, nur key+value match
const colorOptions = computed(() => {
  if (!form.oxartnum) return []
  return druckfarben.value.reduce((acc, df) => {
    let mapping = {}
    try {
      mapping = JSON.parse(df.easymapping)
    } catch {
      return acc
    }
    for (const code of Object.keys(mapping)) {
      const val = mapping[code]
      if (val && form.oxartnum.includes(`${code}${val}`)) {
        acc.push({ value: df.code, label: df.bezeichnung })
        break
      }
    }
    return acc
  }, [])
})

// Bestellung abschicken
async function submitOrder() {
  showConfirm.value = false
  loading.value = true
  error.value = null
  try {
    const orderResponses = []
    for (const item of results.value) {
      const uniqueId = `${item.oxordernr}-${item.ddposition}`
      const { data: orderData } = await axios.post('/api/proxy/pims-order', { uniqueid: uniqueId })
      orderResponses.push({ item, orderData })
    }
    for (const { item, orderData } of orderResponses) {
      const { data: productData } = await axios.post('/api/proxy/pims-product', {
        orderid: orderData.orderid,
        ordernr: orderData.ordernr,
        artnum: item.oxartnum,
        title: item.oxtitle,
        quantity: item.oxamount,
        position: item.ddposition
      })
      await axios.post('/api/proxy/pims-parcel', { productid: productData.productid })
    }
  } catch (err) {
    console.error('Fehler beim Absenden:', err)
    error.value = 'Fehler beim Absenden der Bestellung.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
input:focus {
  outline: none;
  border-color: #fb923c;
}
</style>