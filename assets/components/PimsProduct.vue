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
          value="stanzen_werkzeugvorhanden"
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
          value="uvlack_part_1"
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
          value="praegenheiss_gold_werkzeugvorhanden_10"
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
          value="praegenblind_werkzeugvorhanden"
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
import { useToast } from 'vue-toastification'

const toast = useToast()

const form = ref({
  product: '',
  paper: '',
  color: '',
  wvkaschierung: '',
  quantity: '',
  width: '',
  height: '',
  wvstanzen: '',
  wvuvlack: '',
  comment: '',
  fileFront: null,
  fileBack: null,
  wvpraegenheiss: '',
  wvpraegenblind: '',
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

const submitProduct = async () => {
  try {
    const payload = new FormData()
    payload.append('product', form.value.product)
    payload.append('paper', form.value.paper)
    payload.append('color', form.value.color)
    payload.append('wvkaschierung', form.value.kaschierung)
    payload.append('width', form.value.width)
    payload.append('quantity', form.value.quantity)
    payload.append('height', form.value.height)
    payload.append('wvstanzen', form.value.wvstanzen)
    payload.append('wvuvlack', form.value.wvuvlack)
    payload.append('comment', form.value.comment)
    payload.append('wvpraegenheiss', form.value.wvpraegenheiss)
    payload.append('wvpraegenblind', form.value.wvpraegenblind)
    if (fileFront.value) payload.append('file_front', fileFront.value)
    if (fileBack.value) payload.append('file_back', fileBack.value)

    const response = await axios.post('/api/proxy/pims-product', payload)

    if (response.data.success) {
      toast.success(`Produkt erfolgreich angelegt: ${response.data.orderid}`)
    } else {
      toast.error('Fehler bei der Produkterstellung:' + response.data.message)
    }
  } catch (error) {
    toast.error('Fehler beim Senden der Daten:' + error.message )
  }
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
