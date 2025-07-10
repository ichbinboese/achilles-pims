<template>
  <div class="container mx-auto mt-12">
    <div class="max-w-md mx-auto border-2 dark:bg-stone-900 bg-white shadow-md p-6">
      <h2 class="text-2xl font-semibold mb-4 text-center dark:text-stone-300">
        Easy Bestellung suchen
      </h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="relative w-full">
          <input
            v-model="orderNr"
            type="text"
            placeholder="Bestellnummer"
            class="appearance-none w-full px-4 py-2 bg-white border-2 border-stone-300 rounded-full focus:ring-orange-600 focus:border-orange-600 dark:bg-stone-800 dark:text-stone-300"
          />
        </div>
        <button
          type="submit"
          class="w-full bg-orange-600 text-white py-2 rounded-full hover:bg-orange-700 transition"
        >
          Suchen
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const orderNr = ref('')
const router = useRouter()

function submitForm() {
  router.push({ name: 'easy-result', query: { orderNr: orderNr.value } })
}
</script>
