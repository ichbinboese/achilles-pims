<template>
  <div class="container max-w-6xl mx-auto px-4 mt-4 bg-white dark:bg-stone-800 pb-4 mb-4 pt-4">
    <!-- Toolbar: Suche + Zähler -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
      <div class="flex items-center gap-2">
        <label for="searchOx" class="text-sm dark:text-stone-200">
          Suche nach Easy-Auftragsnummer:
        </label>
        <input
          id="searchOx"
          v-model.trim="search"
          type="text"
          placeholder="auch Kurzform z.B. ...9356"
          class="w-56 p-2 border rounded-full dark:bg-stone-700 dark:text-white"
        />
      </div>
      <div class="text-sm text-stone-600 dark:text-stone-300">
        Gefunden: {{ filtered.length }} · Seite {{ currentPage }} / {{ totalPages || 1 }}
      </div>
    </div>

    <!-- Loader / Progressbar -->
    <div
      v-if="!allLoaded"
      class="mb-4 rounded-lg border border-orange-200 dark:border-stone-700 bg-orange-50 dark:bg-stone-900"
      role="status"
      aria-live="polite"
    >
      <div class="px-4 py-3">
        <div class="flex items-center justify-between gap-3">
          <div class="text-sm text-stone-700 dark:text-stone-200">
            <span class="font-medium">Lade Daten…</span>
            <span class="ml-1 opacity-80">(Datensätze {{ completedSteps }} / {{ totalSteps }})</span>
          </div>
          <div class="text-sm tabular-nums text-stone-600 dark:text-stone-300">
            {{ progressPercent }}%
          </div>
        </div>
        <div class="mt-2 h-2 w-full rounded-full bg-orange-100 dark:bg-stone-700 overflow-hidden">
          <div
            class="h-2 bg-orange-500 transition-all"
            :style="{ width: progressPercent + '%' }"
            aria-label="Fortschritt der Daten-Ladung"
          />
        </div>
      </div>
    </div>

    <div class="order-overview text-left overflow-x-auto">
      <table class="table w-full">
        <thead>
        <tr>
          <th>Easy Auftragsnummer</th>
          <th>Easy Position</th>
          <th>Pinguin Auftragsnummer</th>
          <th>Auftragsstatus</th>
          <th>Produkt-Nr</th>
          <th>Druckdaten Status</th>
        </tr>
        </thead>
        <tbody>
        <!-- Skeleton-Zeilen solange noch geladen wird -->
        <tr v-if="!allLoaded" v-for="n in 3" :key="'skeleton-'+n" class="animate-pulse">
          <td colspan="6" class="py-3">
            <div class="h-3 w-1/2 bg-stone-200 dark:bg-stone-700 rounded"></div>
          </td>
        </tr>

        <tr v-for="order in paged" :key="order.orderId" class="odd:bg-gray-100 dark:odd:bg-stone-900">
          <td>
            <router-link
              :to="{
                  name: 'easy-result',
                  query: {
                    orderNr: order.oxordernr,
                  }
                }"
              class="text-orange-600 hover:underline"
            >
              {{ order.oxordernr }}
            </router-link>
          </td>
          <td>{{ order.ddposition }}</td>
          <td>{{ order.orderNr }}</td>
          <td>{{ order.status || 'Unbekannt' }}</td>
          <td>{{ order.productNr || 'Unbekannt' }}</td>
          <td>{{ order.productStatus || 'Unbekannt' }}</td>
        </tr>

        <tr v-if="paged.length === 0 && allLoaded">
          <td colspan="6" class="text-center py-6 text-stone-500 odd:bg-gray-100 dark:odd:bg-stone-600">
            Keine Einträge gefunden.
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
      <button
        class="px-3 py-2 border rounded-full disabled:opacity-40 dark:text-white"
        :disabled="currentPage === 1"
        @click="goPrev"
        aria-label="Vorherige Seite"
      >
        &laquo; Zurück
      </button>

      <div class="flex items-center gap-1">
        <!-- kompakte Seitenauswahl -->
        <button
          v-for="p in pageButtons"
          :key="p.key"
          :disabled="p.disabled"
          @click="p.page && goTo(p.page)"
          class="px-3 py-2 border rounded-full"
          :class="[
            p.page === currentPage ? 'bg-orange-600 text-white border-orange-600' : '',
            p.ellipsis ? 'cursor-default opacity-60' : ''
          ]"
        >
          {{ p.label }}
        </button>
      </div>

      <button
        class="px-3 py-2 border rounded-full disabled:opacity-40 dark:text-white"
        :disabled="currentPage === totalPages || totalPages === 0"
        @click="goNext"
        aria-label="Nächste Seite"
      >
        Weiter &raquo;
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const ordersWithStatus = ref([]);

// --- Suche + Pagination State ---
const search = ref('');
const currentPage = ref(1);
const pageSize = 20; // max. 20 Datensätze pro Seite

watch(search, () => {
  currentPage.value = 1;
});

// Gefilterte Liste (nur nach oxordernr)
const filtered = computed(() => {
  const q = (search.value || '').toLowerCase();
  if (!q) return ordersWithStatus.value;
  return ordersWithStatus.value.filter(o =>
    String(o.oxordernr ?? '').toLowerCase().includes(q)
  );
});

// Seitenzahlen
const totalPages = computed(() => Math.ceil(filtered.value.length / pageSize));

// Aktuelle Seite zuschneiden
const paged = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filtered.value.slice(start, start + pageSize);
});

// Pagination Controls
const goPrev = () => { if (currentPage.value > 1) currentPage.value--; };
const goNext = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const goTo = (p) => { if (p >= 1 && p <= totalPages.value) currentPage.value = p; };

// Kompakte Seitennavigation (1 … n)
const pageButtons = computed(() => {
  const buttons = [];
  const tp = totalPages.value || 1;
  const cp = currentPage.value;
  const add = (label, page, opts = {}) => buttons.push({ key: `${label}-${page ?? 'x'}`, label, page, ...opts });

  if (tp <= 7) {
    for (let i = 1; i <= tp; i++) add(i, i);
    return buttons;
  }

  add(1, 1);
  if (cp > 3) add('…', null, { ellipsis: true, disabled: true });

  const start = Math.max(2, cp - 1);
  const end = Math.min(tp - 1, cp + 1);
  for (let i = start; i <= end; i++) add(i, i);

  if (cp < tp - 2) add('…', null, { ellipsis: true, disabled: true });
  add(tp, tp);

  return buttons;
});

// ===== Loader / Progress =====
const isLoading = ref(true);
const progressPercent = ref(0);     // 0..100
const totalSteps = ref(2);          // (1) /api/orders + (2) status-batch
const completedSteps = ref(0);

const allLoaded = computed(() => !isLoading.value && progressPercent.value >= 100);

function resetProgress() {
  isLoading.value = true;
  progressPercent.value = 0;
  totalSteps.value = 2;
  completedSteps.value = 0;
}

function bump(step = 1) {
  completedSteps.value = Math.min(totalSteps.value, completedSteps.value + step);
  const pct = totalSteps.value > 0 ? Math.round((completedSteps.value / totalSteps.value) * 100) : 0;
  progressPercent.value = Math.min(100, Math.max(0, pct));
}

onMounted(async () => {
  resetProgress();

  try {
    // STEP 1: Grunddaten
    const { data } = await axios.get('/api/orders');
    bump(1);

    // Normalisieren inkl. oxordernr
    const normalized = (data || []).map(o => ({
      orderId: String(o.orderid ?? '').trim(),
      orderNr: String(o.ordernr ?? '').trim(),
      oxordernr: o.products?.[0]?.oxOrderNr ?? '—',
      ddposition: o.products?.[0]?.ddPosition != null ? (o.products[0].ddPosition * 10) : '—', // wie in deiner Vorlage
    }));

    const ids = normalized.map(o => o.orderId);

    // STEP 2: Status-Batch
    // selbst wenn ids leer sind, bumpen wir, damit der Loader sauber endet
    let batchItems = [];
    try {
      const { data: batch } = await axios.post(
        '/api/proxy/pims-order-status/batch',
        { orderids: ids },
        { headers: { 'Content-Type': 'application/json' } }
      );
      batchItems = batch?.items || [];
    } finally {
      bump(1);
    }

    const byId = Object.fromEntries(normalized.map(o => [o.orderId, o]));

    ordersWithStatus.value = batchItems.map(item => ({
      orderId: item.orderId,
      orderNr: byId[item.orderId]?.orderNr || item.orderNr || '—',
      oxordernr: byId[item.orderId]?.oxordernr || '—',
      ddposition: byId[item.orderId]?.ddposition ?? '—',
      productNr: item.productNr || 'Unbekannt',
      status: item.status || 'Unbekannt',
      productStatus: item.productStatus || 'Unbekannt',
    }));
  } catch (err) {
    console.error('Fehler beim Abrufen:', err);
    if (err.response) {
      console.error('Response data:', err.response.data);
    }
  } finally {
    progressPercent.value = 100;
    isLoading.value = false;
  }
});
</script>

<style scoped>
.table th, .table td {
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}
thead tr th {
  text-align: left;
  white-space: nowrap;
}
</style>
