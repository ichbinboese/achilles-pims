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
          <label class="block text-sm font-semibold text-stone-700 dark:text-stone-300 mb-1 ml-4">Auflage:</label>
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
import { useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()

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

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/me', { withCredentials: true })
    if (data?.authenticated) {
      form.value.checkmail = data.email
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

async function submitProduct() {
  try {
    const state = form.value;
    const payload = new FormData();

    // Werte aus der Route
    payload.append('orderid', String(route.params.orderid));
    payload.append('checkmail', form.value.checkmail)
    payload.append('uniqueid', `${route.params.bestnr}-${route.params.position}`);

    // Werte aus dem Formular
    payload.append('product',  state.product);
    payload.append('duration', '3')
    payload.append('paper',    state.paper);
    payload.append('color',    state.color);
    if (state.wvkaschierung) payload.append('wvkaschieren', state.wvkaschierung);
    payload.append('readytoprint', 'A');
    payload.append('quantity', String(state.quantity));
    payload.append('width',    String(state.width));
    payload.append('height',   String(state.height));
    payload.append('neutral', 'N')
    // falls du keine Seitenzahl im UI hast, lass "pages" ganz weg oder setze fix 1:
    if (fileBack.value) {
      payload.append('pages', '2')
    } else {
      payload.append('pages', '1')
    }
    if (state.comment) payload.append('comment', state.comment);

    if (fileFront.value) payload.append('file_front', fileFront.value, fileFront.value.name);
    if (fileBack.value)  payload.append('file_back',  fileBack.value,  fileBack.value.name);

    const productRes = await axios.post('/api/proxy/pims-product', payload, {
      withCredentials: true,
    });

    console.log(productRes.data);

    if (productRes.data?.success !== 1) {
      console.error('PIMS Product Fehler:', productRes.data);
      toast.error('Produkt anlegen fehlgeschlagen');
      return;
    }

    // APPProduct persistieren
    await axios.post('/api/app-product', {
      orderId:   String(route.params.orderid),
      orderNr:   String(route.params.ordernr),
      productId: String(productRes.data.productid ?? ''),
      productNr: String(productRes.data.productnr ?? ''),
    });

    toast.success(`Produkt angelegt: ${productRes.data.productnr || productRes.data.productid}`);
  } catch (e) {
    console.error(e);
    toast.error('Fehler beim Anlegen des Produkts');
  }
}


async function submitParcel() {
  try {
    const form = new FormData()
    form.append('orderid', String(route.params.orderid))
    form.append('delivery', formState.delivery || 'dhl')
    form.append('shipper_additional1', 'easyOrdner')
    form.append('shipper_additional2', 'info@easyordner.de')
    form.append('title', formState.title || 'firma')
    form.append('name', formState.name || 'Achilles Präsentationsprodukte GmbH')
    form.append('street', formState.street || 'Bruchkampweg 40')
    form.append('postcode', formState.postcode || '29227')
    form.append('locale', formState.locale || 'Celle')
    form.append('country', formState.country || 'deutschland')
    form.append('mail', formState.mail || 'info@easyordner.de')
    if (formState.phone) form.append('phone', formState.phone)
    if (formState.date)  form.append('date',  String(formState.date))

    const parcelRes = await axios.post(
      'https://pims-api.stage.printdays.net/v1/pimsParcel.php',
      form,
      {
        headers: {
          'Content-Type': 'multipart/form-data; boundary=---011000010111000001101001',
          Accept: 'application/json, application/xml',
          Authorization: 'Basic QmVuamFtaW4uQm9lc2U6LHhLUTFOei4lRFpZTTc/Qw=='
        },
        params: { key: '91aislr7f513g8qn0jdi5yige2mhtg6' },
      }
    )

    if (parcelRes.data?.success !== 1) {
      toast.error('Versand konnte nicht angelegt werden.')
      console.error(parcelRes.data)
      return
    }
    toast.success('Versanddaten angelegt.')
  } catch (e) {
    console.error(e)
    toast.error('Fehler beim Anlegen der Versanddaten.')
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
