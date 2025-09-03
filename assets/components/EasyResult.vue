<template>
  <div class="container max-w-6xl mx-auto px-4 mt-4 bg-white dark:bg-stone-800 pb-4 mb-4 pt-4">
    <!-- Auswahlmodal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-[1000] flex items-center justify-center">
      <div class="bg-white dark:bg-stone-900 border border-orange-600 rounded shadow-lg w-full max-w-xl mx-auto">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-semibold dark:text-stone-300">Position auswählen</h2>
        </div>
        <div class="px-6 py-4 dark:text-stone-300">
          <p class="mb-4">Bitte wählen Sie eine Position aus.</p>
          <ul class="space-y-2">
            <li
              v-for="item in results"
              :key="item.oxid"
              @click="selectPosition(item)"
              class="cursor-pointer px-4 py-2 rounded-full border border-stone-300 hover:bg-orange-100 dark:hover:bg-orange-600 transition"
            >
              Position {{ item.ddposition * 10 }} – {{ item.oxtitle }}
            </li>
          </ul>
          <div class="mt-6 flex items-center justify-end">
            <button @click="cancelSelection" class="px-4 py-2 bg-stone-300 dark:bg-stone-700 rounded">Abbrechen</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loader / Error -->
    <div v-if="loading" class="flex justify-center items-center h-full">
      <svg class="animate-spin h-8 w-8 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a 8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z" />
      </svg>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-500 text-red-700 px-4 pt-3 pb-6 rounded mb-6">
      {{ error }}
    </div>

    <!-- Tabelle & Sidebar -->
    <div class="flex flex-col md:flex-row gap-4 mx-auto" v-else>
      <!-- Tabelle -->
      <div class="overflow-hidden rounded-lg shadow-lg w-full md:w-2/3">
        <table class="w-full table-auto border-collapse">
          <thead>
          <tr class="bg-orange-600 text-white">
            <th class="px-4 py-2 text-left">Pos</th>
            <th class="px-4 py-2 text-left">Bestellnr</th>
            <th class="px-4 py-2 text-left">Titel</th>
          </tr>
          </thead>
          <tbody>
          <template v-for="item in displayedItems" :key="item.oxid">
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

      <!-- Sidebar -->
      <div class="w-full md:w-1/3 space-y-6">
        <div class="p-4 rounded-lg border dark:border-stone-700">
          <div class="text-sm dark:text-stone-300">
            <div class="mb-2">
              <strong>Gewählte Position: </strong><br>
              <span v-if="selectedItem"> {{ selectedItem.ddposition * 10 }} – {{ selectedItem.oxtitle }}</span>
              <span v-else>—</span>
            </div>
            <div class="flex items-center gap-2">
              <button
                v-if="results.length > 1"
                class="px-3 py-2 rounded bg-stone-200 dark:bg-stone-700 hover:bg-stone-300 dark:hover:bg-stone-600"
                @click="showModal = true"
              >
                Position ändern
              </button>

              <!-- NEU: Umschalten je nach alreadyOrdered -->
              <button
                v-if="selectedItem && alreadyOrdered"
                disabled
                class="px-3 py-2 rounded bg-lime-600 dark:bg-lime-500 text-white dark:text-white cursor-not-allowed"
                title="Diese Position wurde bereits bestellt"
              >
                bereits bestellt
              </button>

              <button
                v-else
                :disabled="!selectedItem || submitting"
                @click="submitOrder(selectedItem)"
                class="px-3 py-2 rounded bg-orange-600 hover:bg-orange-700 text-white disabled:opacity-50"
              >
                {{ submitting ? 'Sendet…' : 'Bestellung absenden' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Automapping & Parameter -->
        <div class="p-4 rounded-lg border dark:border-stone-700 bg-white dark:bg-stone-900">
          <h3 class="font-semibold mb-3 dark:text-stone-200">Automatische PIMS-Zuordnung</h3>
          <div class="grid grid-cols-2 gap-3 text-sm dark:text-stone-300">
            <div><span class="font-semibold">Produkt:</span> <code>{{ mappedCodes.product?.bezeichnung || mappedCodes.product?.code || mappedCodes.product || 'Achilles Ganzbogen SHOP' }}</code></div>
            <div>
              <span class="font-semibold">Papier:</span>
              <code>{{ mappedCodes.paper?.label || mappedCodes.paper?.value || '135g Bilderdruck matt' }}</code>
            </div>
            <div><span class="font-semibold">Farbe:</span> <code>{{ mappedCodes.color?.bezeichnung || mappedCodes.color?.code || mappedCodes.color || '—' }}</code></div>
            <div><span class="font-semibold">Folie:</span> <code>{{ mappedCodes.wvkaschieren?.bezeichnung || mappedCodes.wvkaschieren?.code || mappedCodes.wvkaschieren || '—' }}</code></div>
          </div>

          <!-- Schnell-Parameter -->
          <div class="mt-4 grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold ml-1 dark:text-stone-300">Breite (mm)</label>
              <input
                type="number"
                v-model.number="productForm.width"
                @input="markTouched('w')"
                class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold ml-1 dark:text-stone-300">Höhe (mm)</label>
              <input
                type="number"
                v-model.number="productForm.height"
                @input="markTouched('h')"
                class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold ml-1 dark:text-stone-300">Seiten</label>
              <select
                :key="selectedItem?.oxid || 'noitem'"
                v-model.number="pagesModel"
                autocomplete="off"
                name="pagesFixed"
                class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4"
              >
                <option :value="1">1 Seite (einseitig)</option>
                <option :value="2">2 Seiten (beidseitig)</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <label class="block text-xs font-semibold ml-1 dark:text-stone-300">Kommentar</label>
            <textarea v-model="productForm.comment" rows="1" class="w-full p-2 border rounded-3xl dark:bg-stone-800 dark:text-white px-4"></textarea>
          </div>
        </div>

        <!-- Uploads -->
        <div class="space-y-6">
          <!-- Vorderseite Upload -->
          <div
            class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer bg-white dark:bg-stone-800 rounded-3xl"
            :class="[frontDragOver || fileFront ? 'border-lime-500 bg-lime-100' : 'border-stone-300 dark:border-stone-600']"
            @dragover.prevent="frontDragOver = true"
            @dragleave.prevent="frontDragOver = false"
            @drop.prevent="handleDrop($event, 'front')"
          >
            <p class="text-sm text-stone-600 dark:text-stone-300 mb-2">
              Datei für <strong>Vorderseite</strong> (PDF, Pflichtfeld)
            </p>
            <input type="file" accept="application/pdf" class="hidden" ref="fileFrontInput" @change="handleFileChange($event, 'front')" />
            <button @click="triggerFile('front')" class="text-orange-600 font-medium underline">Datei auswählen</button>
            <p v-if="fileFront" class="mt-2 text-sm text-lime-700 dark:text-line-200">{{ fileFront.name }} als Datei hochladen</p>
          </div>

          <!-- Rückseite Upload -->
          <div
            class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer bg-white dark:bg-stone-800 rounded-3xl"
            :class="[backDragOver || fileBack ? 'border-lime-500 bg-lime-100' : 'border-stone-300 dark:border-stone-600']"
            @dragover.prevent="backDragOver = true"
            @dragleave.prevent="backDragOver = false"
            @drop.prevent="handleDrop($event, 'back')"
          >
            <p class="text-sm text-stone-600 dark:text-stone-300 mb-2">
              Datei für <strong>Rückseite</strong> (PDF, optional)
            </p>
            <input type="file" accept="application/pdf" class="hidden" ref="fileBackInput" @change="handleFileChange($event, 'back')" />
            <button @click="triggerFile('back')" class="text-orange-600 font-medium underline">Datei auswählen</button>
            <p v-if="fileBack" class="mt-2 text-sm text-lime-700 dark:text-lime-200">{{ fileBack.name }} als Datei hochladen</p>
          </div>
        </div>

        <!-- Response-Panel -->
        <div v-if="lastOrderResponse" class="p-4 rounded border border-stone-300 dark:border-stone-700 bg-stone-50 dark:bg-stone-900">
          <h3 class="font-semibold mb-2 dark:text-stone-200">PIMS Order Response</h3>
          <pre class="text-xs overflow-auto dark:text-stone-300">{{ JSON.stringify(lastOrderResponse, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const orderNr = route.query.orderNr || ''
const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const results = ref([])
const selectedItem = ref(null)
const showModal = ref(false)
const lastOrderResponse = ref(null)

const fileFront = ref(null)
const fileBack = ref(null)
const fileFrontInput = ref(null)
const fileBackInput = ref(null)
const frontDragOver = ref(false)
const backDragOver = ref(false)
const mappingError = ref('')

const widthTouched = ref(false)
const heightTouched = ref(false)
const alreadyOrdered = ref(false)

function markTouched(field) {
  if (field === 'w') widthTouched.value = true
  if (field === 'h') heightTouched.value = true
}

const mappedCodes = reactive({
  product: null,
  paper: null,
  color: null,
  wvkaschieren: null
})

const productForm = reactive({
  width: 560,
  height: 315,
  pages: 1,
  comment: ''
})

const displayedItems = computed(() => {
  if (selectedItem.value) return [selectedItem.value]
  if (results.value.length === 1) return results.value
  return []
})

const pagesModel = computed({
  get: () => (Number(productForm.pages) === 2 ? 2 : 1),
  set: (v) => { productForm.pages = Number(v) === 2 ? 2 : 1 }
})

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/easy-search', { params: { orderNr } })
    results.value = data.map(i => ({
      oxid: i.oxid,
      oxordernr: i.oxordernr,
      oxtitle: i.oxtitle,
      oxartnum: i.oxartnum,
      oxshortdesc: i.oxshortdesc,
      ddposition: i.ddposition,
      oxamount: i.oxamount
    }))
    if (results.value.length === 0) {
      error.value = 'Keine Datensätze gefunden.'
    } else if (results.value.length === 1) {
      selectedItem.value = results.value[0]
      productForm.pages = 1
      await fetchMappings(selectedItem.value.oxartnum)
      await applySizeFromOxartnum(selectedItem.value.oxartnum)
      // Hier den Already-Ordered-Check machen
      alreadyOrdered.value = await checkAlreadyOrdered(
        selectedItem.value.oxordernr,
        selectedItem.value.ddposition
      )
    } else {
      await nextTick()
      showModal.value = true
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message
  } finally {
    loading.value = false
  }
})

function getCode(val) {
  if (!val) return ''
  if (typeof val === 'string') return val.trim()
  if (typeof val === 'object') {
    if ('code' in val && val.code) return String(val.code).trim()
    if ('value' in val && val.value) return String(val.value).trim()
    if ('id' in val && val.id) return String(val.id).trim()
  }
  return ''
}

async function checkAlreadyOrdered(oxordernr, ddposition) {
  try {
    const { data } = await axios.get('/api/easy-product/exists', {
      params: { oxordernr, ddposition } // ddposition roh (ohne *10)
    })
    return Boolean(data?.exists)
  } catch (e) {
    console.warn('Check alreadyOrdered fehlgeschlagen:', e)
    return false
  }
}

async function fetchMappings(oxartnum) {
  try {
    const res = await fetch('/api/pims-map', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ oxartnum })
    })
    if (!res.ok) {
      console.warn('pims-map HTTP', res.status)
      mappingError.value = `Mapping-Endpoint Fehler: HTTP ${res.status}`
      return
    }
    const body = await res.json()

    const c = body?.codes ?? body ?? {}
    mappedCodes.product = c.product ?? 'pd5615'
    mappedCodes.paper = (oxartnum.startsWith('REG-'))
      ? { value: 'pd5624', label: '300g Bilderdruck matt' }
      : { value: 'pd5618', label: '135g Bilderdruck matt' }
    mappedCodes.color = c.color ?? null
    mappedCodes.wvkaschieren = c.wvkaschieren ?? null

    if (!getCode(mappedCodes.product) && !getCode(mappedCodes.paper) && !getCode(mappedCodes.color) && !getCode(mappedCodes.wvkaschieren)) {
      mappingError.value = 'Kein Mapping gefunden. Prüfe EASy-Mapping & SKU.'
    } else {
      mappingError.value = ''
    }
  } catch (e) {
    console.warn('Mapping konnte nicht geladen werden:', e)
    mappingError.value = 'Mapping-Request fehlgeschlagen.'
  }
}

async function applySizeFromOxartnum(oxartnum) {
  try {
    const { data } = await axios.get('/api/easy-size', { params: { oxartnum } })
    const m = data?.match
    if (m) {
      if (!widthTouched.value && Number(m.width) > 0) productForm.width = Number(m.width)
      if (!heightTouched.value && Number(m.height) > 0) productForm.height = Number(m.height)
    }
  } catch (e) {
    console.warn('easy-size mapping fehlgeschlagen', e)
  }
}

async function selectPosition(item) {
  selectedItem.value = item
  productForm.pages = 1
  showModal.value = false
  await fetchMappings(item.oxartnum)
  await applySizeFromOxartnum(item.oxartnum)
  // ebenfalls prüfen
  alreadyOrdered.value = await checkAlreadyOrdered(item.oxordernr, item.ddposition)
}

function cancelSelection() {
  showModal.value = false
}

async function placePimsOrderViaProxy(payload) {
  const formData = new FormData()
  Object.entries(payload).forEach(([k, v]) => formData.append(k, v ?? ''))
  const { data } = await axios.post('/api/proxy/pims-order', formData)
  return data
}

async function placePimsProductViaProxy(formData) {
  const { data } = await axios.post('/api/proxy/pims-product', formData)
  return data
}

async function placePimsParcelViaProxy(payload) {
  const fd = new FormData()
  Object.entries(payload).forEach(([k, v]) => fd.append(k, v ?? ''))
  const { data } = await axios.post('/api/proxy/pims-parcel', fd)
  return data
}

function triggerFile(type) {
  if (type === 'front') fileFrontInput.value?.click()
  if (type === 'back') fileBackInput.value?.click()
}
function handleFileChange(event, type) {
  const file = event.target.files[0]
  if (!file || file.type !== 'application/pdf') return
  if (type === 'front') fileFront.value = file
  if (type === 'back') fileBack.value = file
}
function handleDrop(event, type) {
  const file = event.dataTransfer.files[0]
  if (!file || file.type !== 'application/pdf') return
  if (type === 'front') {
    frontDragOver.value = false
    fileFront.value = file
  } else if (type === 'back') {
    backDragOver.value = false
    fileBack.value = file
  }
}

async function submitOrder(item) {
  if (!item) {
    error.value = 'Bitte zuerst eine Position auswählen.'
    return
  }
  if (!fileFront.value) {
    error.value = 'Bitte eine PDF für die Vorderseite hochladen.'
    return
  }

  submitting.value = true
  error.value = null
  lastOrderResponse.value = null

  try {
    const orderPayload = {
      uniqueid: `${item.oxordernr}-${item.ddposition * 10}`,
      title: 'firma',
      name: 'Achilles Präsentationsprodukte GmbH',
      street: 'Bruchkampweg 40',
      postcode: '29227',
      country: 'DE',
      locale: 'Celle',
      mail: 'rechnungseingang@achilles.de',
      vat: '0.19',
      payment: 'rechnung'
    }
    const orderRes = await placePimsOrderViaProxy(orderPayload)

    let productRes = {}
    let parcelRes = {}

    if (orderRes?.orderid && orderRes?.ordernr) {
      if (!mappedCodes.product || !mappedCodes.paper || !mappedCodes.color) {
        await fetchMappings(item.oxartnum)
      }

      const productCode = getCode(mappedCodes.product)
      const paperCode = getCode(mappedCodes.paper?.value)
      const colorCode = getCode(mappedCodes.color)
      const wvkCode = getCode(mappedCodes.wvkaschieren)

      const missing = []
      if (!productCode) missing.push('Produkt')
      if (!paperCode) missing.push('Papier')
      if (!colorCode) missing.push('Farbe')
      if (!wvkCode) missing.push('Kaschierung')
      if (missing.length) {
        error.value = `Fehlende Pflichtzuordnungen: ${missing.join(', ')}. Bitte EASY-Mapping / pims-map prüfen.`
        submitting.value = false
        return
      }

      const form = new FormData()
      form.append('orderid', String(orderRes.orderid))
      form.append('uniqueid', `${item.oxordernr}-${item.ddposition * 10}`)
      form.append('product', productCode)
      form.append('readytoprint', 'A')
      form.append('paper', paperCode)
      form.append('color', colorCode)
      form.append('duration', '3')
      form.append('quantity', String(Number((item.oxamount * 1.1) + 25)))
      form.append('width', String(productForm.width ?? 0))
      form.append('height', String(productForm.height ?? 0))
      form.append('pages', String(pagesModel.value)) // nur 1 oder 2
      form.append('comment', `${item.oxordernr}-${item.ddposition * 10}`)
      form.append('identifier', item.oxtitle ?? '')
      form.append('checkmail', 'info@easyordner.de')
      form.append('neutral', 'N')
      form.append('file_front', fileFront.value, fileFront.value.name)
      if (fileBack.value) {
        form.append('file_back', fileBack.value, fileBack.value.name)
      }
      form.append('wvkaschieren', wvkCode)

      productRes = await placePimsProductViaProxy(form)

      await submitParcel(productRes, orderRes, item)

      lastOrderResponse.value = { order: orderRes, product: productRes, parcel: parcelRes }
    }

  } catch (err) {
    console.error('Fehler beim Absenden:', err)
    error.value =
      err?.response?.data?.error ||
      err?.response?.data?.message ||
      err?.message ||
      'Fehler beim Absenden der Bestellung.'
  } finally {
    submitting.value = false
  }
}

function getNextWeekday(weekday) {
  const today = new Date()
  const dayOfWeek = today.getDay() // So=0, Mo=1, Di=2, Mi=3, Do=4, Fr=5, Sa=6
  const diff = (weekday - dayOfWeek + 7) % 7
  today.setDate(today.getDate() + diff)
  return today
}

// Berechne den nächsten Dienstag (2) und Freitag (5)
const nextTuesday = getNextWeekday(2)
const nextFriday = getNextWeekday(5)

// Bestimme, welcher der beiden Tage näher ist
let nextDay = nextTuesday
if (nextFriday - nextTuesday < 3 * 24 * 60 * 60 * 1000) {
  nextDay = nextFriday
}

// Formatieren im "YYYY-MM-DD"-Format
const year = nextDay.getFullYear()
const month = String(nextDay.getMonth() + 1).padStart(2, '0')
const day = String(nextDay.getDate()).padStart(2, '0')
const formattedNextDay = `${year}-${month}-${day}`
console.log('formattedNextDay', formattedNextDay)

async function submitParcel(productRes, orderRes, item) {
  const form = new FormData()
  form.append('productid', String(productRes?.productid ?? ''))
  form.append('uniqueid', `${productRes?.orderid}-${productRes?.ddposition * 10}`)
  form.append('delivery', 'dhl')
  form.append('shipper_additional1', 'easyOrdner')
  form.append('shipper_additional2', 'info@easyordner.de')
  form.append('title', 'firma')
  form.append('name', 'Achilles Präsentationsprodukte GmbH')
  form.append('street', 'Bruchkampweg 40')
  form.append('postcode', '29227')
  form.append('locale', 'Celle')
  form.append('country', 'deutschland')
  form.append('mail', 'info@easyordner.de')
  form.append('phone', '05141753241')
  form.append('date', String(formattedNextDay))

  if (fileFront.value) {
    form.append('file_front', fileFront.value, fileFront.value.name)
  }
  if (fileBack.value) {
    form.append('file_back', fileBack.value, fileBack.value.name)
  }

  const options = {
    method: 'POST',
    url: '/api/proxy/pims-parcel',
    headers: {
      'Content-Type': 'multipart/form-data',
      Accept: 'application/json'
    },
    data: form
  }

  try {
    const { data } = await axios.request(options)
    console.log('Parcel Response:', data)
    submitOrderToBackend(orderRes, productRes, item)
  } catch (error) {
    console.error('Fehler beim Absenden des Parcel:', error)
  }
}

async function submitOrderToBackend(orderRes, productRes, item) {
  try {
    const payload = {
      orderid: orderRes.orderid,
      ordernr: orderRes.ordernr,
      productid: productRes.productid,
      productnr: productRes.productnr,
      oxordernr: item.oxordernr,
      ddposition: item.ddposition,
    }
    const response = await axios.post('/api/save-order', payload)
    console.log('Bestellung erfolgreich gespeichert:', response.data)
  } catch (error) {
    console.error('Fehler beim Speichern der Bestellung:', error)
  }
}

// Wenn sich die Auswahl ändert: erneut prüfen
watch(selectedItem, async (val) => {
  if (val) {
    alreadyOrdered.value = await checkAlreadyOrdered(val.oxordernr, val.ddposition)
  } else {
    alreadyOrdered.value = false
  }
})
</script>

<style scoped>
input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: #fb923c;
}
</style>
