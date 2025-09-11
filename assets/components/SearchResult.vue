<template>
  <div class="container max-w-6xl mx-auto px-4 mt-4 bg-white dark:bg-stone-800 pb-4 mb-4 pt-4">
    <!-- Auswahlmodal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-[1000] flex items-center justify-center">
      <div class="bg-white dark:bg-stone-900 border border-orange-600 rounded shadow-lg max-w-2xl w-full mx-auto">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-semibold dark:text-stone-300">Position auswählen</h2>
        </div>
        <div class="px-6 py-4 dark:text-stone-300">
          <p class="mb-4">Bitte wählen Sie eine Position aus.</p>
          <ul class="space-y-2">
            <li
              v-for="item in results"
              :key="`${item.bestnr}-${item.bestpos}`"
              @click="selectPosition(item)"
              class="cursor-pointer px-4 py-2 rounded-full border border-stone-300 hover:bg-orange-100 dark:hover:bg-orange-600 transition"
            >
              Bestellung <strong>{{ item.bestnr }}</strong> – Position <strong>{{ item.bestpos }}</strong>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Lade-/Fehlerzustände -->
    <div v-if="loading" class="text-stone-600">Lade Daten…</div>
    <div v-else-if="error" class="bg-red-100 border border-red-500 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>

    <!-- Anzeige der gewählten Bestellung -->
    <div v-else-if="selected" class="mt-6 space-y-6">
      <!-- Kopf mit Wechsel-Button -->
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-semibold dark:text-stone-300">
          Details zur Position {{ selected.bestpos }} von <strong>{{ selected.bestnr }}</strong>
        </h3>
        <button @click="showModal = true" class="px-4 py-2 rounded-full border border-stone-300 hover:bg-stone-100 dark:hover:bg-stone-700">
          Position wechseln
        </button>
      </div>

      <!-- Kerndaten -->
      <div class="p-4 rounded-lg border dark:border-stone-700 bg-white dark:bg-stone-900">
        <table class="table-auto w-full text-sm dark:text-stone-300">
          <tbody>
          <tr class="border-b">
            <th class="text-left p-2 align-top w-1/4">Firma:</th>
            <td class="p-2">{{ selected.fiNr }}</td>
          </tr>
          <tr class="border-b">
            <th class="text-left p-2 align-top">Bestellnummer:</th>
            <td class="p-2">{{ selected.bestnr }}</td>
          </tr>
          <tr class="border-b">
            <th class="text-left p-2 align-top">Position:</th>
            <td class="p-2">{{ selected.bestpos }}</td>
          </tr>
          <tr class="border-b">
            <th class="text-left p-2 align-top">Beschreibung:</th>
            <td class="p-2 whitespace-pre-line">{{ selected.txtlong }}</td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Aktionen -->
      <div class="flex items-center gap-3">
        <!-- Bereits bestellt => Link zur vorhandenen Product-Route -->
        <router-link
          v-if="selected && alreadyOrdered && existing"
          :to="{
      name: 'product',
      params: {
        bestnr: selected.bestnr,
        position: selected.bestpos,
        orderid: existing.orderid,
        ordernr: existing.ordernr
      },
      query: {
        appbestnr: selected.bestnr,
        appposnr: selected.bestpos
      }
    }"
          class="px-3 py-2 rounded bg-lime-600 dark:bg-lime-500 text-white"
          title="Diese Position wurde bereits bestellt – zur Produktseite"
        >
          bereits bestellt
        </router-link>

        <!-- Bestellung erfassen -->
        <button
          v-else
          @click="showConfirm = true"
          class="inline-flex items-center bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-full"
        >
          Bestellung erfassen
        </button>
      </div>
    </div>
  </div>

  <!-- Bestätigungsmodal -->
  <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 z-[1100] flex items-center justify-center">
    <div class="bg-white dark:bg-stone-800 rounded-lg shadow-lg p-6 w-full max-w-md border border-orange-600">
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
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import md5 from 'crypto-js/md5'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '../stores/auth.js'

const route = useRoute()
const router = useRouter()
const toast = useToast()
console.log(route.query.selected)

const results = ref([])
const selected = ref(route.query.selected || false)           // <- korrekt: selected
const alreadyOrdered = ref(false)    // <- Flag für Button
const showModal = ref(false)
const showConfirm = ref(false)
const loading = ref(true)
const error = ref(null)

const existing = ref(null) // { orderid, ordernr } falls schon vorhanden

async function checkAlreadyOrdered(appbestnr, appposnr) {
  try {
    const { data } = await axios.get('/api/app-order/by-app', { // <- neuer Endpoint
      params: { appbestnr, appposnr }
    })
    // Erwartet: { exists: boolean, orderid?: string|number, ordernr?: string|number }
    if (data?.exists) {
      existing.value = { orderid: String(data.orderid), ordernr: String(data.ordernr) }
      return true
    }
    existing.value = null
    return false
  } catch (e) {
    console.warn('Check alreadyOrdered fehlgeschlagen:', e)
    existing.value = null
    return false
  }
}

async function selectPosition(item) {
  selected.value = item
  showModal.value = false

  const appbestnr = String(item.bestnr ?? '').trim()
  const appposnr = Number(item.bestpos ?? 0)

  alreadyOrdered.value = await checkAlreadyOrdered(appbestnr, appposnr)
}

async function submitOrder() {
  if (!selected.value) return
  if (alreadyOrdered.value) return

  const uniqueId = md5(`${selected.value.bestnr}-${selected.value.bestpos}`).toString()

  const form = new FormData()
  form.append('uniqueid', uniqueId)
  form.append('title', 'firma')
  form.append('name', 'Achilles Präsentationsprodukte GmbH')
  form.append('street', 'Bruchkampweg 40')
  form.append('postcode', '29227')
  form.append('locale', 'Celle')
  form.append('country', 'deutschland')
  form.append('mail', 'rechnungseingang@achilles.de')
  form.append('vat', 0.19)
  form.append('payment', 'rechnung')

  try {
    const orderRes = await axios.post('/api/proxy/pims-order', form)

    if (orderRes.data?.errorlist?.error?.some(e => e.text === "AlreadyTransferred")) {
      console.error("PIMS Order Fehler:", productRes.data)
      toast.error("Auftrag wurde bereits angelegt")
      return
    }

    if (orderRes.data?.success !== 1) {
      toast.error('Bestellung konnte nicht erstellt werden.')
      return
    }

    const { orderid, ordernr } = orderRes.data

    await axios.post('/api/app-order', {
      appbestnr: selected.value.bestnr,
      appposnr: selected.value.bestpos,
      orderId: String(orderid),
      orderNr: String(ordernr),
    })

    toast.success(`Bestellung erfolgreich erstellt: ${ordernr}`)

    router.push({
      name: 'product',
      params: {
        bestnr: selected.value.bestnr,
        position: selected.value.bestpos,
        orderid: String(orderid),
        ordernr: String(ordernr),
      },
      query: {
        appbestnr: selected.value.bestnr,
        appposnr: selected.value.bestpos,
      }
    })
  } catch (e) {
    console.error(e)
    toast.error('Fehler beim Absenden der Bestellung.')
  } finally {
    showConfirm.value = false
  }
}

watch(selected, async (val) => {
  if (val) {
    const appbestnr = String(val.bestnr ?? '').trim()
    const appposnr = Number(val.bestpos ?? 0)
    alreadyOrdered.value = await checkAlreadyOrdered(appbestnr, appposnr)
  } else {
    alreadyOrdered.value = false
  }
})

onMounted(async () => {
  try {
    loading.value = true
    error.value = null

    const { fiNr, bestnr } = route.query
    const auth = useAuthStore()
    if (!auth.user) await auth.fetchUser()

    const response = await axios.get('/api/bestellung', { params: { fiNr, bestnr } })
    results.value = Array.isArray(response.data) ? response.data : []

    if (results.value.length === 1) {
      await selectPosition(results.value[0])
    } else if (results.value.length > 1) {
      showModal.value = true
    } else {
      error.value = 'Keine Ergebnisse gefunden.'
    }
  } catch (err) {
    error.value = err?.response?.data?.message || err?.message || 'Fehler beim Laden.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: #fb923c;
}
</style>
