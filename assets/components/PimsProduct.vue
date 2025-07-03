<template>
  <div class="container mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-6">Produkt zu Bestellung hinzufügen</h2>

    <form @submit.prevent="submitProduct" class="space-y-4">
      <FormInput label="Pinguin Order ID (orderid)" v-model="form.orderid" required type="number" />
      <FormInput label="Produkt ID (pdxxx)" v-model="form.product" required />
      <FormInput label="Papier ID" v-model="form.paper" required />
      <FormInput label="Farben ID" v-model="form.color" required />
      <FormInput label="Auflage" v-model="form.quantity" required type="number" />
      <FormInput label="Breite (mm)" v-model="form.width" required type="number" />
      <FormInput label="Höhe (mm)" v-model="form.height" required type="number" />
      <FormInput label="Format ID (optional)" v-model="form.format" />
      <FormInput label="Kommentar" v-model="form.comment" />
      <FormInput label="Dauer (1-5)" v-model="form.duration" required type="number" />
      <FormInput label="Druckbereit? (Y/N/A)" v-model="form.readytoprint" />

      <label class="block font-semibold text-sm text-stone-700 dark:text-stone-300">Vorderseite:</label>
      <input type="file" @change="handleFile($event, 'file_front')" class="mb-3" required />

      <label class="block font-semibold text-sm text-stone-700 dark:text-stone-300">Rückseite (optional):</label>
      <input type="file" @change="handleFile($event, 'file_back')" class="mb-3" />

      <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-full">
        Produkt senden
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useToast } from 'vue-toastification'
import FormInput from './FormInput.vue'
import axios from 'axios'
import md5 from 'md5'

const toast = useToast()

const form = reactive({
  orderid: '',
  uniqueid: '',
  product: '',
  paper: '',
  color: '',
  quantity: 1000,
  width: 210,
  height: 297,
  format: '',
  comment: '',
  duration: 3,
  readytoprint: 'Y',
  file_front: null,
  file_back: null
})

const handleFile = (e, key) => {
  form[key] = e.target.files[0]
}

const submitProduct = async () => {
  const payload = new FormData()
  const idString = `${form.orderid}-${form.product}-${form.paper}-${Date.now()}`
  form.uniqueid = md5(idString)

  for (const [key, value] of Object.entries(form)) {
    if (value !== null && value !== '') {
      payload.append(key, value)
    }
  }

  try {
    const { data } = await axios.post(
      'https://pims-api.stage.printdays.net/v1/pimsProduct.php?key=123',
      payload,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: 'Basic QmVuamFtaW4uQm9lc2U6LHhLUTFOei4lRFpZTTc/Qw=='
        }
      }
    )

    if (data.success === 1) {
      toast.success(`Produkt erfolgreich erstellt: ${data.productnr}`)
    } else {
      toast.error('Erstellung fehlgeschlagen.')
    }
  } catch (error) {
    console.error(error)
    toast.error('Fehler beim Senden des Produkts')
  }
}
</script>

<style scoped>
input:focus {
  outline: none;
  border-color: #fb923c;
}
</style>
