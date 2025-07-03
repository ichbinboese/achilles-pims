<template>
  <div class="container mx-auto mt-10 px-4">
    <div v-if="loading" class="text-stone-600">Lade Daten...</div>

    <div v-else-if="error" class="bg-red-100 border border-red-500 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
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

    <!-- Auswahlmodal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
      <div class="bg-white dark:bg-stone-900 border border-orange-600 rounded shadow-lg w-full max-w-md mx-auto">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-semibold dark:text-stone-300">Position auswählen</h2>
        </div>
        <div class="px-6 py-4 dark:text-stone-300">
          <p class="mb-4">Welche Position möchten Sie verwenden?</p>
          <ul class="space-y-2">
            <li
              v-for="item in results"
              :key="item.bestpos"
              @click="selectPosition(item)"
              class="cursor-pointer px-4 py-2 rounded-full border border-stone-300 hover:bg-orange-100 dark:hover:bg-orange-600 transition"
            >
              Position {{ item.bestpos }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Anzeige der gewählten Bestellung -->
    <div v-if="selected" class="mt-8 bg-stone-100 dark:bg-stone-800 p-5">
      <h3 class="text-xl font-semibold mb-4 dark:text-stone-300">
        Details zur Position {{ selected.bestpos }} von {{ selected.bestnr }}:
      </h3>
      <table class="w-1/2 table-auto border border-stone-300 bg-white dark:bg-stone-900 overflow-hidden text-sm dark:text-stone-300">
        <tbody>
        <tr class="border-b">
          <th class="text-left p-2 w-1/5">Firma:</th>
          <td class="p-2">{{ selected.fiNr }}</td>
        </tr>
        <tr class="border-b">
          <th class="text-left p-2">Bestellnummer:</th>
          <td class="p-2">{{ selected.bestnr }}</td>
        </tr>
        <tr>
          <th class="text-left p-2">Bestelltext:</th>
          <td class="p-2 whitespace-pre-line">{{ selected.txtlong ?? '-' }}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Absende-Button -->
    <button
      v-if="selected"
      @click="showConfirm = true"
      class="my-6 inline-block bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-full"
    >
      Bestellung erfassen
    </button>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import md5 from 'crypto-js/md5'
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()

const results = ref([])
const selected = ref(null)
const showModal = ref(false)
const showConfirm = ref(false)
const loading = ref(true)
const error = ref(null)

const selectPosition = (item) => {
  selected.value = item
  showModal.value = false
}

const submitOrder = async () => {
  const uniqueId = md5(`${selected.value.bestnr}-${selected.value.bestpos}`).toString()

  const form = new FormData()
  form.append('uniqueid', uniqueId)
  form.append('title', 'firma') // oder 'herr', 'frau' je nach Auswahl
  form.append('name', 'Pinguindruck GmbH')
  form.append('street', 'Marienburger Str. 16')
  form.append('postcode', '10405')
  form.append('locale', 'Berlin')
  form.append('country', 'deutschland')
  form.append('mail', 'me@somewhere.org')
  form.append('vat', 0.19)
  form.append('payment', 'rechnung')

  try {
    const response = await axios.post('https://pims-api.stage.printdays.net/v1/pimsOrder.php', form, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': 'Basic QmVuamFtaW4uQm9lc2U6LHhLUTFOei4lRFpZTTc/Qw=='
      }
    })

    if (response.data.success === 1) {
      toast.success(`Bestellung erfolgreich erstellt: ${response.data.ordernr}`)
    } else {
      toast.error('Bestellung konnte nicht erstellt werden.')
    }
  } catch (e) {
    console.error(e)
    toast.error('Fehler beim Absenden der Bestellung.')
  } finally {
    showConfirm.value = false
  }
}

onMounted(async () => {
  try {
    const { fiNr, bestnr } = route.query
    const response = await axios.get('/api/bestellung', { params: { fiNr, bestnr } })

    results.value = response.data

    if (results.value.length === 1) {
      selectPosition(results.value[0])
    } else if (results.value.length > 1) {
      showModal.value = true
    } else {
      error.value = 'Keine Ergebnisse gefunden.'
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
input:focus {
  outline: none;
  border-color: #fb923c;
}
</style>
