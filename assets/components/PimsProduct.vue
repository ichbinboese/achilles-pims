<template>
  <div class="p-6 bg-white dark:bg-stone-900 rounded shadow-md max-w-2xl mx-auto space-y-6">
    <h2 class="text-xl font-bold text-stone-800 dark:text-stone-100">Druckbogen anlegen</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Bestellung -->
      <div>
        <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Bestellung</label>
        <select v-model="form.product" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required>
          <option value="">Bitte wählen...</option>
          <option v-for="p in produkte" :key="p.code" :value="p.code">
            {{ p.bezeichnung }}
          </option>
        </select>
      </div>

      <!-- Papier Auswahl -->
      <div>
        <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Papier</label>
        <select v-model="form.paper" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required>
          <option value="">Bitte wählen...</option>
          <option v-for="p in papiere" :key="p.code" :value="p.code">
            {{ p.bezeichnung }}
          </option>
        </select>
      </div>

      <!-- Farben Auswahl -->
      <div>
        <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-3">Farben</label>
        <select v-model="form.color" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required>
          <option value="">Bitte wählen...</option>
          <option v-for="f in farben" :key="f.code" :value="f.code">
            {{ f.bezeichnung }}
          </option>
        </select>
      </div>

      <!-- Kaschierung Auswahl -->
      <div>
        <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Kaschierung</label>
        <select v-model="form.wvkaschierung" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required>
          <option value="">Bitte wählen...</option>
          <option v-for="k in kaschierungen" :key="k.code" :value="k.code">
            {{ k.bezeichnung }}
          </option>
        </select>
      </div>
    </div>

    <div class="flex flex-col">
      <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Endformat (zur schnelleren Dateiprüfung)</label>
      <div class="flex gap-4">
        <div class="w-1/3">
          <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Breite (in mm):</label>
          <input type="number" v-model="form.width" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required/>
        </div>
        <div class="w-1/3">
          <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Höhe (in mm):</label>
          <input type="number" v-model="form.height" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required />
        </div>
        <div class="w-1/3">
          <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Anzahl Nutzen:</label>
          <input type="number" v-model="form.quantity" class="w-full p-2 border rounded-full dark:bg-stone-800 dark:text-white px-4" required/>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 ml-4 w-2/3">
      <div class="flex items-center space-x-2">
        <input
          type="checkbox"
          v-model="form.wvstanzen"
          class="form-checkbox h-5 w-5  accent-orange-500 hover:accent-orange-300 rounded"
        />
        <label class="text-sm font-semibold text-stone-700 dark:text-stone-300">
          Datei hat Stanzlinien
        </label>
      </div>

      <div class="flex items-center space-x-2">
        <input
          type="checkbox"
          v-model="form.wvuvlack"
          class="form-checkbox h-5 w-5  accent-orange-500 hover:accent-orange-300 rounded"
        />
        <label class="text-sm font-semibold text-stone-700 dark:text-stone-300">
          UV-Lack
        </label>
      </div>

      <div class="flex items-center space-x-2">
        <input
          type="checkbox"
          v-model="form.wvpraegenheiss"
          class="form-checkbox h-5 w-5  accent-orange-500 hover:accent-orange-300 rounded"
        />
        <label class="text-sm font-semibold text-stone-700 dark:text-stone-300">
          Heißfolienprägung
        </label>
      </div>

      <div class="flex items-center space-x-2">
        <input
          type="checkbox"
          v-model="form.wvpraegenblind"
          class="form-checkbox h-5 w-5  accent-orange-500 hover:accent-orange-300 rounded"
        />
        <label class="text-sm font-semibold text-stone-700 dark:text-stone-300">
          Blindprägung
        </label>
      </div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Kommentar an Pinguin</label>
      <textarea v-model="form.comment" class="w-full p-2 border rounded-3xl dark:bg-stone-800 dark:text-white px-4" rows="1"></textarea>
    </div>

    <div class="space-y-6">
      <!-- Upload Vorderseite -->
      <div
        class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer bg-white dark:bg-stone-800 rounded-3xl"
        :class="[
    frontDragOver || fileFront ? 'border-lime-500 bg-lime-100' : 'border-stone-300 dark:border-stone-600'
  ]"
        @dragover.prevent="frontDragOver = true"
        @dragleave.prevent="frontDragOver = false"
        @drop.prevent="handleDrop($event, 'front')"
      >
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-2">
          Datei für <strong>Vorderseite</strong> (PDF, Pflichtfeld)
        </p>
        <input
          type="file"
          accept="application/pdf"
          class="hidden"
          ref="fileFrontInput"
          @change="handleFileChange($event, 'front')"
        />
        <button @click="triggerFile('front')" class="text-orange-600 font-medium underline">Datei auswählen</button>
        <p v-if="fileFront" class="mt-2 text-sm text-lime-700 dark:text-lime-200">{{ fileFront.name }} als Datei hochladen</p>
      </div>

      <!-- Upload Rückseite -->
      <div
        class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer bg-white dark:bg-stone-800 rounded-3xl"
        :class="[
    backDragOver || fileBack ? 'border-lime-500 bg-lime-100' : 'border-stone-300 dark:border-stone-600'
  ]"
        @dragover.prevent="backDragOver = true"
        @dragleave.prevent="backDragOver = false"
        @drop.prevent="handleDrop($event, 'back')"
      >
        <p class="text-sm text-stone-600 dark:text-stone-300 mb-2">
          Datei für <strong>Rückseite</strong> (PDF, optional)
        </p>
        <input
          type="file"
          accept="application/pdf"
          class="hidden"
          ref="fileBackInput"
          @change="handleFileChange($event, 'back')"
        />
        <button @click="triggerFile('back')" class="text-orange-600 font-medium underline">Datei auswählen</button>
        <p v-if="fileBack" class="mt-2 text-sm text-lime-700 dark:text-lime-200">{{ fileBack.name }} als Datei hochladen</p>
      </div>

    </div>

    <button @click="submitProduct" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 rounded-full px-4">
      Produkt senden
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()
const router = useRouter()

const form = ref({
  product: '',
  checkmail: '',
  paper: '',
  color: '',
  wvkaschierung: '',  // ← UI-Auswahl; an API geht 'wvkaschieren'
  quantity: '',
  width: '',
  height: '',
  // alle Optionen als Boolean flags:
  wvstanzen: false,
  wvuvlack: false,
  wvpraegenheiss: false,
  wvpraegenblind: false,
  comment: '',
  fileFront: null,
  fileBack: null,
  fname: '',
  lname: '',
  phone: '',
})

const produkte = ref([])
const papiere = ref([])
const farben = ref([])
const kaschierungen = ref([])

// Drag state
const frontDragOver = ref(false)
const backDragOver = ref(false)

// File state
const fileFront = ref(null)
const fileBack = ref(null)

const fileFrontInput = ref(null)
const fileBackInput = ref(null)

const emit = defineEmits(['update:fileFront', 'update:fileBack'])

const lastProductId = ref(null)

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const parcelState = ref({
  delivery: 'dhl',
  title: 'firma',
  name: 'Achilles Präsentationsprodukte GmbH',
  street: 'Bruchkampweg 40',
  postcode: '29227',
  locale: 'Celle',
  country: 'Deutschland',
  phone: '',
  date: '', // optional: ISO-String oder leer
  shipper_additional1: 'Druckbogeneingang',
  shipper_additional2: '', // setzen wir beim Laden des Users
  mail: '' // wird aus /api/ldap-user befüllt
})


onMounted(async () => {
  try {
    const { data } = await axios.get('/api/ldap-user', { withCredentials: true })

    console.log(data)
    console.log('Aktiver User:', data.firstname, '', data.lastname, ', E-Mail: ', data.email, ', Telefon:' , data.phone)

    if (data?.authenticated) {
      const userMail = data.email
      form.value.checkmail = userMail
      // optional: wenn du Vor-/Nachname wirklich brauchst:
      form.value.phone = data.phone ?? ''
      form.value.fname = data.firstname ?? data.fname ?? ''
      form.value.lname = data.lastname  ?? data.lname  ?? ''

      // für Parcel:
      parcelState.value.mail = userMail
      parcelState.value.shipper_additional2 = userMail

      const fullName = [form.value.fname, form.value.lname].filter(Boolean).join(' ').trim()
      if (fullName) parcelState.value.shipper_additional1 = fullName
    }

    const [prod, pap, farb, kasch] = await Promise.all([
      axios.get('/api/pims-produkt'),
      axios.get('/api/pims-papier'),
      axios.get('/api/pims-druckfarben'),
      axios.get('/api/pims-kaschierung')
    ])
    produkte.value = prod.data
    papiere.value = pap.data
    farben.value = farb.data
    kaschierungen.value = kasch.data
  } catch (err) {
    toast.error('Fehler beim Laden der Auswahldaten')
  }
})

async function ensureUserMail() {
  // 1) Falls bereits gesetzt & valide → ok
  if (parcelState.value.mail && emailRegex.test(parcelState.value.mail)) return parcelState.value.mail;

  // 2) Erst aus /api/ldap-user versuchen
  try {
    const { data } = await axios.get('/api/ldap-user', { withCredentials: true });
    const mail = data?.email?.trim();
    if (mail && emailRegex.test(mail)) {
      parcelState.value.mail = mail;
      form.value.checkmail = mail; // für product
      return mail;
    }
  } catch (_) {}

  // 3) Fallback: /api/me
  try {
    const { data } = await axios.get('/api/me', { withCredentials: true });
    const mail = data?.email?.trim();
    if (mail && emailRegex.test(mail)) {
      parcelState.value.mail = mail;
      form.value.checkmail = mail;
      return mail;
    }
  } catch (_) {}

  // 4) Fehler
  throw new Error('Keine gültige E-Mail ermittelbar');
}

// FIX: Produkt anlegen und productId an submitParcel übergeben
async function submitProduct() {
  try {
    const mail = await ensureUserMail();

    const state = form.value
    const payload = new FormData()

    // Werte aus der Route
    payload.append('orderid', String(route.params.orderid))
    payload.append('checkmail', String(mail))
    payload.append('uniqueid', `${route.params.bestnr}-${route.params.position}`)

    // Werte aus dem Formular
    payload.append('product',  state.product)
    payload.append('duration', '3')
    payload.append('paper',    state.paper)
    payload.append('color',    state.color)
    if (state.wvkaschierung) payload.append('wvkaschieren', state.wvkaschierung)
    payload.append('readytoprint', 'A')
    payload.append('quantity', String(state.quantity))
    payload.append('width',    String(state.width))
    payload.append('height',   String(state.height))
    payload.append('neutral', 'N')

    // Seitenzahl aus Uploads ableiten
    payload.append('pages', fileBack.value ? '2' : '1')

    if (state.comment) payload.append('comment', state.comment)

    if (fileFront.value) payload.append('file_front', fileFront.value, fileFront.value.name)
    if (fileBack.value)  payload.append('file_back',  fileBack.value,  fileBack.value.name)

    const productRes = await axios.post('/api/proxy/pims-product', payload, { withCredentials: true })
    console.log('Product Response:', productRes.data)

    if (productRes.data?.errorlist?.error?.some(e => e.text === "AlreadyTransferred")) {
      console.error("PIMS Product Fehler:", productRes.data)
      toast.error("Produkt wurde bereits angelegt")
      return
    }

    if (handlePimsErrors(productRes.data)) {
      console.error("PIMS Product Fehler:", productRes.data)
      return
    }

    if (productRes.data?.success !== 1) {
      console.error('PIMS Product Fehler:', productRes.data)
      toast.error('Produkt anlegen fehlgeschlagen')
      return
    }

    const productId = String(productRes.data.productid ?? productRes.data.productId ?? '')
    lastProductId.value = productId

    // WICHTIG: productId an Parcel übergeben
    await submitParcel(productId, mail)

    navigateWithToast('app-orders', {}, {}, `Produkt angelegt: ${productRes.data.productnr || productRes.data.productid}`, 'success')
  } catch (e) {
    console.error(e)
    toast.error('Fehler beim Anlegen des Produkts')
  }
}

function handlePimsErrors(response) {
  const errors = response?.errorlist?.error
  if (!Array.isArray(errors)) return false

  errors.forEach(err => {
    toast.error(`${err.field}: ${err.text}`)
  })

  return true
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

function coerceJson(data) {
  if (data && typeof data === 'object') return data
  if (typeof data === 'string') {
    // greift das letzte {...} bis zum Ende der Response
    const m = data.match(/\{[\s\S]*\}\s*$/)
    if (m) {
      try { return JSON.parse(m[0]) } catch (_) {}
    }
    // Fallback: sehr grob – erkennt Erfolg im String
    if (/"success"\s*:\s*1/.test(data) || /"parcelid"\s*:\s*\d+/.test(data)) {
      return { success: 1, guessedFrom: 'string' }
    }
  }
  return null
}

// FIX: Signatur anpassen + fehlerhafte fd.append Aufrufe korrigieren
// FIX: Parcel-Aufruf – uniqueid mitgeben, Content-Type Header NICHT manuell setzen,
// Mail/Checkmail validieren, ein paar Defaults härten.
async function submitParcel(productId, mail) {
  try {
    const p = parcelState.value
    const fd = new FormData()

    // Pflicht-IDs
    fd.append('productid', String(productId))
    fd.append('uniqueid', `${route.params.bestnr}-${route.params.position}`)

    // Versanddaten
    fd.append('delivery', p.delivery || 'dhl')
    fd.append('title',    p.title    || 'firma')
    fd.append('name',     p.name     || 'Achilles Präsentationsprodukte GmbH')
    fd.append('street',   p.street   || 'Bruchkampweg 40')
    fd.append('postcode', p.postcode || '29227')
    fd.append('locale',   p.locale   || 'Celle')
    fd.append('country',  'deutschland')

    // Mail/Checkmail (beide) – nur wenn valide
    const safeMail = (mail || '').trim()
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(safeMail)
    if (!emailOk) {
      console.error('Parcel: ungültige E-Mail →', safeMail)
      toast.error(`Parcel: Ungültige E-Mail (${safeMail || 'leer'})`)
      return
    }
    fd.append('mail', safeMail)
    fd.append('checkmail', safeMail)

    // Telefon (optional)
    const phone = (form.value.phone ?? p.phone ?? '').trim()
    if (phone) fd.append('phone', phone)

    // Zusatzinfos
    const fullName   = [form.value.fname, form.value.lname].filter(Boolean).join(' ').trim()
    const additional1 = (p.shipper_additional1 || fullName || 'Druckbogeneingang').trim()
    fd.append('shipper_additional1', additional1)
    fd.append('shipper_additional2', `${route.params.bestnr}-${route.params.position}`)

    // Datum
    fd.append('date', formattedNextDay)

    // Dateien (optional)
    if (fileFront.value) fd.append('file_front', fileFront.value, fileFront.value.name)
    if (fileBack.value)  fd.append('file_back',  fileBack.value,  fileBack.value.name)

    const resp = await axios.post('/api/proxy/pims-parcel', fd, { withCredentials: true })

    // Debug (ohne Files)
    for (const [k, v] of fd.entries()) {
      if (k !== 'file_front' && k !== 'file_back') console.log('FD', k, '→', v)
    }

    // 🔧 Robust: JSON aus evtl. HTML+JSON-Antwort extrahieren
    const data = coerceJson(resp?.data)
    console.log('Parcel Response (coerced):', data)

    // Erfolg früh erkennen
    const successVal = (typeof data?.success !== 'undefined') ? String(data.success) : ''
    const isOk = successVal === '1' || successVal.toLowerCase?.() === 'true' || !!data?.parcelid
    if (isOk) {
      toast.success(`Parcel angelegt (ID: ${data?.parcelid ?? '—'})`)
      return
    }

    // Fehlerliste nur prüfen, wenn NICHT erfolgreich
    if (data?.errorlist?.error) {
      const list = Array.isArray(data.errorlist.error) ? data.errorlist.error : [data.errorlist.error]
      list.filter(Boolean).forEach(e => toast.error(`${e?.field ?? 'parcel'}: ${e?.text ?? 'Fehler'}`))
      return
    }

    // Fallback
    toast.error('Paket/Versand anlegen fehlgeschlagen')
  } catch (error) {
    console.error('Fehler beim Absenden des Parcel:', error)
    toast.error('Fehler beim Absenden des Parcel')
  }
}

function reloadPageWithToast(message, type = 'success') {
  sessionStorage.setItem('pendingToast', JSON.stringify({ message, type }))
  window.location.reload()
}


function navigateWithToast(name, params, query, msg, type = 'success') {
  const p = params ?? {}
  const q = { ...(query ?? {}), toastMessage: msg, toastType: type }
  try {
    return router.push({ name, params: p, query: q })
  } catch (e) {
    const href = router.resolve({ name, params: p, query: q }).href
    window.location.assign(href)
  }
}

async function loadActiveUserMail() {
  const { data } = await axios.get('/api/me', { withCredentials: true })
  if (data?.authenticated) {
    // Beispiel: an ein Formularfeld binden
    // form.value.mail = data.email
    console.log('Aktiver User:', data.username, 'Mail:', data.email)
    return data.email
  }
  return null
}


function triggerFile(type) {
  if (type === 'front') fileFrontInput.value?.click()
  if (type === 'back') fileBackInput.value?.click()
}

function handleFileChange(event, type) {
  const file = event.target.files[0]
  if (!file || file.type !== 'application/pdf') return

  if (type === 'front') {
    fileFront.value = file
    emit('update:fileFront', file)
  } else if (type === 'back') {
    fileBack.value = file
    emit('update:fileBack', file)
  }
}

function handleDrop(event, type) {
  const file = event.dataTransfer.files[0]
  if (!file || file.type !== 'application/pdf') return

  if (type === 'front') {
    frontDragOver.value = false
    fileFront.value = file
    emit('update:fileFront', file)
  } else if (type === 'back') {
    backDragOver.value = false
    fileBack.value = file
    emit('update:fileBack', file)
  }
}
</script>
