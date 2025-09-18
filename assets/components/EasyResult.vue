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
              :key="item.oxid"
              @click="selectPosition(item)"
              class="cursor-pointer px-4 py-2 rounded-full border border-stone-300 hover:bg-orange-100 dark:hover:bg-orange-600 transition"
            >
              <strong>{{ item.oxordernr }}:</strong> Position {{ item.ddposition * 10 }} – {{ item.oxtitle }}
            </li>
          </ul>
          <div class="mt-6 flex items-center justify-end">
            <button @click="cancelSelection" class="px-4 py-2 bg-stone-300 dark:bg-stone-700 rounded">Abbrechen</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Storno Modal -->
    <div
      v-if="showStornoConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 z-[2000] flex items-center justify-center"
    >
      <div class="bg-white dark:bg-stone-900 rounded-lg shadow-lg max-w-sm w-full p-6 text-center">
        <h3 class="text-lg font-semibold mb-4 dark:text-stone-200">
          Möchten Sie wirklich stornieren?
        </h3>
        <div class="flex justify-center gap-4">
          <button
            @click="confirmStorno"
            class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white"
          >
            Ja, stornieren
          </button>
          <button
            @click="showStornoConfirm = false"
            class="px-4 py-2 rounded bg-stone-300 hover:bg-stone-400 dark:bg-stone-700 dark:hover:bg-stone-600 dark:text-white"
          >
            Abbrechen
          </button>
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

              <!-- Bereits bestellt (disabled) -->
              <button
                v-if="selectedItem && alreadyOrdered"
                disabled
                class="px-3 py-2 rounded bg-lime-600 dark:bg-lime-500 text-white cursor-not-allowed"
                title="Diese Position wurde bereits bestellt"
              >
                bereits bestellt
              </button>

              <!-- NEU: Auftrag stornieren -->
              <button
                v-if="selectedItem && alreadyOrdered"
                :disabled="stornoSubmitting"
                @click="openStornoConfirm(selectedItem)"
                class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white disabled:opacity-50"
              >
                {{ stornoSubmitting ? 'Storniert…' : 'Auftrag stornieren' }}
              </button>

              <!-- Standard: Bestellung absenden -->
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
            class="border-2 border-dashed p-6 text-center cursor-pointer bg-white dark:bg-stone-800 rounded-3xl"
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
            <p v-if="fileFront" class="mt-2 text-sm text-lime-700 dark:text-lime-200">{{ fileFront.name }} als Datei hochladen</p>
          </div>

          <!-- Rückseite Upload -->
          <div
            class="border-2 border-dashed p-6 text-center cursor-pointer bg-white dark:bg-stone-800 rounded-3xl"
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
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()

// --- Sichtbare Fehler/Toasts global ---
axios.interceptors.response.use(
  r => r,
  err => {
    console.error('AXIOS ERROR:', err?.config?.url, err?.response?.status, err?.response?.data || err.message)
    const msg = err?.response?.data?.error || err?.response?.data?.message || err.message || 'Unbekannter Fehler'
    try { error.value = msg } catch {}
    try { toast.error(String(msg)) } catch {}
    return Promise.reject(err)
  }
)
window.addEventListener('error', (ev) => {
  console.error('WINDOW ERROR:', ev.message, ev.filename, ev.lineno, ev.error)
  try { toast.error('JS-Fehler: ' + ev.message) } catch {}
})
window.addEventListener('unhandledrejection', (ev) => {
  console.error('PROMISE REJECTION:', ev.reason)
  try { toast.error('Fehler (Promise): ' + (ev.reason?.message || String(ev.reason))) } catch {}
})

const orderNr = route.query.orderNr || ''
const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const results = ref([])
const selectedItem = ref(null)
const showModal = ref(false)
const lastOrderResponse = ref(null)
const stornoSubmitting = ref(false)

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
const showStornoConfirm = ref(false)
let stornoItem = null

function markTouched(field) {
  if (field === 'w') widthTouched.value = true
  if (field === 'h') heightTouched.value = true
}

const mappedCodes = reactive({
  product: null,
  paper: null,
  color: null,
  wvkaschieren: null,
  wvheftung: null,
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
    const stored = sessionStorage.getItem('pendingToast')
    if (stored) {
      const { message, type } = JSON.parse(stored)
      if (type === 'success') toast.success(message)
      else if (type === 'error') toast.error(message)
      sessionStorage.removeItem('pendingToast')
    }
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
    mappedCodes.register = (oxartnum.startsWith('REG-'))
      ? { value: 'heftung_lose', label: 'wvheftung' }
      : { value: null, label: null }
    mappedCodes.wvkaschieren = c.wvkaschieren ?? null

    if (!getCode(mappedCodes.product) && !getCode(mappedCodes.paper) && !getCode(mappedCodes.color) && !getCode(mappedCodes.wvkaschieren)) {
      mappingError.value = 'Kein Mapping gefunden. Prüfe EASY-Mapping & SKU.'
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
  alreadyOrdered.value = await checkAlreadyOrdered(item.oxordernr, item.ddposition)
  toast.success(`Auftrag ${item.oxordernr} - ${item.ddposition*10} geladen!`)
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
    toast.info('Leite Bestellung an PIMS weiter …')

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

    // ---- ORDER anlegen
    const orderRes = await placePimsOrderViaProxy(orderPayload)
    console.log('ORDER RES:', orderRes)

    // Fehlerliste prüfen
    if (handlePimsErrors(orderRes)) {
      submitting.value = false
      console.error('PIMS Order Fehler:', orderRes)
      return
    }
    // AlreadyTransferred prüfen
    if (orderRes?.errorlist?.error?.some(e => e.text === 'AlreadyTransferred')) {
      submitting.value = false
      console.error('PIMS Order Fehler:', orderRes)
      toast.error('Auftrag wurde bereits angelegt')
      return
    }
    // Minimalvalidierung
    if (!orderRes || (!orderRes.orderid && !orderRes.ordernr)) {
      submitting.value = false
      error.value = 'PIMS Order konnte nicht angelegt werden (keine orderid/ordernr).'
      toast.error(error.value)
      return
    }

    let productRes = {}
    let parcelRes = {}

    if (orderRes?.orderid && orderRes?.ordernr) {
      // ggf. Mapping nachladen
      if (!mappedCodes.product || !mappedCodes.paper || !mappedCodes.color) {
        await fetchMappings(item.oxartnum)
      }

      const productCode = getCode(mappedCodes.product)
      const paperCode   = getCode(mappedCodes.paper?.value)
      const colorCode   = getCode(mappedCodes.color)
      const wvkCode     = getCode(mappedCodes.wvkaschieren)
      const wvheftung   = getCode(mappedCodes.wvheftung?.value)

      const missing = []
      if (!productCode) missing.push('Produkt')
      if (!paperCode)   missing.push('Papier')
      if (!colorCode)   missing.push('Farbe')
      if (!wvkCode)     missing.push('Kaschierung')
      if (missing.length) {
        submitting.value = false
        error.value = `Fehlende Pflichtzuordnungen: ${missing.join(', ')}. Bitte EASY-Mapping / pims-map prüfen.`
        toast.error(error.value)
        return
      }

      const qty = Math.ceil((Number(item.oxamount) || 0) * 1.1 + 25)

      const form = new FormData()
      form.append('orderid',   String(orderRes.orderid))
      form.append('uniqueid',  `${item.oxordernr}-${item.ddposition * 10}`)
      form.append('product',   productCode)
      form.append('readytoprint', 'A')
      form.append('paper',     paperCode)
      form.append('color',     colorCode)
      form.append('duration',  3)
      form.append('quantity',  String(qty))
      form.append('width',     String(productForm.width ?? 0))
      form.append('height',    String(productForm.height ?? 0))
      form.append('pages',     String(pagesModel.value))
      form.append('comment',   String(productForm.comment ?? '').trim())
      form.append('identifier', `${item.oxordernr}-${item.ddposition * 10} - ${item.oxtitle}` ?? '')
      form.append('checkmail', 'info@easyordner.de')
      form.append('neutral',   'N')
      form.append('wvkaschieren', wvkCode)
      if (wvheftung) form.append('wvheftung', wvheftung)

      form.append('file_front', fileFront.value, fileFront.value.name)
      if (fileBack.value) form.append('file_back', fileBack.value, fileBack.value.name)

      // Debug: zeige an, was wirklich gesendet wird
      for (const [k, v] of form.entries()) {
        console.log('PIMS-PRODUCT FD', k, v instanceof File ? `File(${v.name})` : v)
      }

      // ---- PRODUCT anlegen
      productRes = await placePimsProductViaProxy(form)
      console.log('PRODUCT RES:', productRes)

      // Produktfehler
      if (productRes?.errorlist?.error?.some(e => e.text === 'AlreadyTransferred')) {
        submitting.value = false
        console.error('PIMS Product Fehler:', productRes)
        toast.error('Produkt wurde bereits angelegt')
        return
      }
      if (handlePimsErrors(productRes)) {
        submitting.value = false
        console.error('PIMS Product Fehler:', productRes)
        return
      }
      if (!productRes || !productRes.productid) {
        submitting.value = false
        error.value = 'PIMS Product konnte nicht angelegt werden (keine productid).'
        toast.error(error.value)
        return
      }

      // ---- PARCEL anlegen
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
    toast.error(String(error.value))
  } finally {
    submitting.value = false
  }
}

function handlePimsErrors(response) {
  const errors = response?.errorlist?.error
  if (!Array.isArray(errors)) return false
  let any = false
  errors.forEach(err => {
    any = true
    const msg = `${err.field || '-'}: ${err.text || 'Fehler'}`
    toast.error(msg)
    try { error.value = msg } catch {}
  })
  return any
}

function getNextWeekday(weekday) {
  const today = new Date()
  const dayOfWeek = today.getDay() // So=0, Mo=1, Di=2, Mi=3, Do=4, Fr=5, Sa=6
  const diff = (weekday - dayOfWeek + 7) % 7
  today.setDate(today.getDate() + diff)
  return today
}

// Nächster Mittwoch (3) und Freitag (5)
const nextWednesday = getNextWeekday(3)
const nextFriday = getNextWeekday(5)

// Der nähere der beiden Tage
let nextDay = nextWednesday
if (nextFriday - nextWednesday < 3 * 24 * 60 * 60 * 1000) {
  nextDay = nextFriday
}

// "YYYY-MM-DD"
const year = nextDay.getFullYear()
const month = String(nextDay.getMonth() + 1).padStart(2, '0')
const day = String(nextDay.getDate()).padStart(2, '0')
const formattedNextDay = `${year}-${month}-${day}`
console.log('formattedNextDay', formattedNextDay)

async function submitParcel(productRes, orderRes, item) {
  const form = new FormData()
  form.append('productid', String(productRes?.productid ?? ''))

  // uniqueid korrekt aus Order + ausgewählter Position (nicht aus productRes)
  form.append('uniqueid', `${orderRes?.ordernr ?? orderRes?.orderid}-${item.ddposition * 10}`)

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

  if (fileFront.value) form.append('file_front', fileFront.value, fileFront.value.name)
  if (fileBack.value)  form.append('file_back',  fileBack.value,  fileBack.value.name)

  // Debug Parcel-Felder
  for (const [k, v] of form.entries()) {
    console.log('PIMS-PARCEL FD', k, v instanceof File ? `File(${v.name})` : v)
  }

  try {
    // Keine manuellen multipart-Header setzen -> Browser kümmert sich um Boundary
    const { data } = await axios.post('/api/proxy/pims-parcel', form)
    console.log('Parcel Response:', data)
    await submitOrderToBackend(orderRes, productRes, item)
    reloadPageWithToast('Druckauftrag angelegt')
  } catch (error) {
    console.error('Fehler beim Absenden des Parcel:', error)
    toast.error('Parcel-Fehler: ' + (error?.response?.data?.error || error.message))
    throw error
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
    toast.error('Speicherfehler: ' + (error?.response?.data?.error || error.message))
  }
}

function isProductActive(prod) {
  const s = (prod?.status ?? '').toString().toLowerCase()
  const inactive = ['storniert','canceled','cancelled','deleted','abgeschlossen','completed','versendet','shipped']
  return !inactive.some(x => s.includes(x))
}

async function getOrderStatus(orderid) {
  const { data } = await axios.get('/api/proxy/pims-order-status', { params: { orderid } })
  return data
}

async function cancelAllActiveProducts(orderid) {
  const status = await getOrderStatus(orderid)
  const products = Array.isArray(status?.products?.product) ? status.products.product : []
  const active = products.filter(isProductActive)

  for (const prod of active) {
    const productid = prod.productid || prod.id
    if (!productid) continue
    try {
      const { data } = await axios.post('/api/proxy/pims-product-storno', { productid })
      console.log('Produkt-Storno', productid, data)
    } catch (e) {
      console.error('Produkt-Storno Fehler:', e)
      // optional: throw e;
    }
  }
}

async function stornoOrder(item) {
  stornoSubmitting.value = true
  error.value = null
  try {
    // orderid ermitteln
    let orderid = lastOrderResponse.value?.order?.orderid
    if (!orderid) {
      if (!item?.oxordernr || !item?.ddposition) throw new Error('Weder orderid noch (oxordernr/ddposition) vorhanden.')
      const { data: lookup } = await axios.get('/api/orders/orderid', { params: { oxordernr: item.oxordernr, ddposition: item.ddposition } })
      if (lookup?.success === 1 && lookup?.orderid) orderid = lookup.orderid
      else throw new Error(lookup?.error || 'orderid konnte nicht ermittelt werden.')
    }

    // 1) Alle aktiven Produkte stornieren
    await cancelAllActiveProducts(orderid)

    // 2) Prüfen, ob noch aktive Produkte da sind
    {
      const statusAfter = await getOrderStatus(orderid)
      const productsAfter = Array.isArray(statusAfter?.products?.product) ? statusAfter.products.product : []
      const stillActive = productsAfter.filter(isProductActive)
      if (stillActive.length > 0) {
        const list = stillActive.map(p => `${p.productnr ?? p.productid ?? 'Produkt'}: ${p.status}`).join(', ')
        error.value = `Storno nicht möglich: Noch aktive Produkte vorhanden. Bitte erneut versuchen oder manuell prüfen. Aktive: ${list}`
        return
      }
    }

    // 3) Jetzt Order stornieren
    const { data } = await axios.post('/api/proxy/pims-storno', { orderid })
    console.log('STORNO RAW:', data)
    lastOrderResponse.value = { ...(lastOrderResponse.value || {}), storno: data }
    if (data?.success === 1) {
      alreadyOrdered.value = false
      reloadPageWithToast('Druckauftrag storniert')
    } else {
      const msgParts = [data?.error, data?.message, data?.reason, data?.raw, data?.status].filter(Boolean)
      error.value = msgParts.length ? msgParts.join(' | ') : 'Storno nicht erfolgreich.'
    }
  } catch (e) {
    error.value = e?.response?.data?.error || e.message || 'Fehler beim Storno.'
  } finally {
    stornoSubmitting.value = false
  }
}

function openStornoConfirm(item) {
  stornoItem = item
  showStornoConfirm.value = true
}

async function confirmStorno() {
  if (!stornoItem) return
  showStornoConfirm.value = false
  await stornoOrder(stornoItem)
  stornoItem = null
}

// Wenn sich die Auswahl ändert: erneut prüfen
watch(selectedItem, async (val) => {
  if (val) {
    alreadyOrdered.value = await checkAlreadyOrdered(val.oxordernr, val.ddposition)
  } else {
    alreadyOrdered.value = false
  }
})

function reloadPageWithToast(message, type = 'success') {
  sessionStorage.setItem('pendingToast', JSON.stringify({ message, type }))
  window.location.reload()
}
</script>

<style scoped>
input:focus, textarea:focus, select:focus {
  outline: none;
  border-color: #fb923c;
}
</style>
