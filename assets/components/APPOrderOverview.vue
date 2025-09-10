<template>
  <div class="container max-w-6xl mx-auto px-4 mt-4 bg-white dark:bg-stone-800 pb-4 mb-4 pt-4">
    <!-- Toolbar: Suche + Zähler -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
      <div class="flex items-center gap-2">
        <label for="searchOx" class="text-sm dark:text-stone-200">
          Suche in AB-Nr., APP-Bestellnr., Pinguin-Nr. oder Produkt-Nr.:
        </label>
        <input
          id="searchOx"
          v-model.trim="search"
          type="text"
          placeholder="z. B. BS2530 PR82017771 305AB…"
          class="w-72 p-2 border rounded-full dark:bg-stone-700 dark:text-white"
        />
      </div>
      <div class="text-sm text-stone-600 dark:text-stone-300">
        Gefunden: {{ filtered.length }} · Seite {{ currentPage }} / {{ totalPages || 1 }}
      </div>
    </div>

    <div class="order-overview text-left overflow-x-auto">
      <table class="table w-full">
        <thead>
        <tr>
          <th>APP AB-Nummer</th>
          <th>APP-Bestellnummer</th>
          <th>Position</th>
          <th>Pinguin Auftragsnummer</th>
          <th>Auftragsstatus</th>
          <th>Produkt-Nr</th>
          <th>Druckdaten Status</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="order in paged" :key="order.orderId">
          <td>{{ order.aufnr }}</td>
          <td>{{ order.oxordernr }}</td>
          <td>{{ order.ddposition }}</td>
          <td>{{ order.orderNr }}</td>
          <td>{{ order.status || 'Unbekannt' }}</td>
          <td>{{ order.productNr || 'Unbekannt' }}</td>
          <td>{{ order.productStatus || 'Unbekannt' }}</td>
        </tr>
        <tr v-if="paged.length === 0">
          <td colspan="6" class="text-center py-6 text-stone-500 odd:bg-gray-100 dark:odd:bg-stone-800">Keine Einträge gefunden.</td>
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
        << Zurück
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
        Weiter >>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from "vue-toastification";
import axios from 'axios';
const route = useRoute()
const toast = useToast()

const ordersWithStatus = ref([]);

// --- Suche + Pagination State ---
const search = ref('');
const currentPage = ref(1);
const pageSize = 20;

watch(search, () => { currentPage.value = 1; });

const filtered = computed(() => {
  const q = (search.value || '').toLowerCase().trim();
  if (!q) return ordersWithStatus.value;

  // Mehrwortsuche: "2530 PR820" -> beide Tokens müssen matchen
  const tokens = q.split(/\s+/).filter(Boolean);

  return ordersWithStatus.value.filter(o => {
    // alle relevanten Felder zu Strings machen
    const fields = [
      o.aufnr,
      o.oxordernr,
      o.orderNr,
      o.productNr,
    ]
      .map(v => String(v ?? '').toLowerCase().trim())
      .filter(Boolean); // leere raus

    // Jeder Token muss in mind. einem Feld vorkommen
    return tokens.every(t => fields.some(f => f.includes(t)));
  });
});

const totalPages = computed(() => Math.ceil(filtered.value.length / pageSize));
const paged = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filtered.value.slice(start, start + pageSize);
});

const goPrev = () => { if (currentPage.value > 1) currentPage.value--; };
const goNext = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const goTo = (p) => { if (p >= 1 && p <= totalPages.value) currentPage.value = p; };

const pageButtons = computed(() => {
  const buttons = [];
  const tp = totalPages.value || 1;
  const cp = currentPage.value;
  const add = (label, page, opts = {}) => buttons.push({ key: `${label}-${page ?? 'x'}`, label, page, ...opts });

  if (tp <= 7) { for (let i = 1; i <= tp; i++) add(i, i); return buttons; }
  add(1, 1);
  if (cp > 3) add('…', null, { ellipsis: true, disabled: true });
  const start = Math.max(2, cp - 1);
  const end = Math.min(tp - 1, cp + 1);
  for (let i = start; i <= end; i++) add(i, i);
  if (cp < tp - 2) add('…', null, { ellipsis: true, disabled: true });
  add(tp, tp);
  return buttons;
});

// ===== helper: aufnr-Index bauen =====
const FI_NR = 114;

async function buildAufnrIndex(fiNr, normalizedList) {
  const uniqueBestnrs = Array.from(
    new Set(
      normalizedList
        .map(o => String(o.oxordernr || '').trim())
        .filter(v => v && v !== '—')
    )
  );

  const index = new Map(); // key: `${bestnr}|${bestpos}` -> aufnr

  await Promise.all(
    uniqueBestnrs.map(async (bestnr) => {
      try {
        const { data } = await axios.get('/api/bestellung', {
          params: { fiNr, bestnr }
        });
        // data: [{ fiNr, bestnr, bestpos, txtlong, aufnr }]
        (data || []).forEach(row => {
          const key = `${String(row.bestnr).trim()}|${String(row.bestpos).trim()}`;
          index.set(key, row.aufnr ?? null);
        });
      } catch (e) {
        console.error('Fehler beim Laden /api/bestellung für', { fiNr, bestnr }, e);
      }
    })
  );

  return index;
}

onMounted(async () => {

  if (route.query.toastMessage) {
    toast[route.query.toastType || 'success'](route.query.toastMessage)
  }

  try {
    // 1) Grunddaten laden
    const { data } = await axios.get('/api/app-orders');

    // 2) Normalisieren + Basisfelder (inkl. oxordernr/bestnr + ddposition/bestpos)
    const normalizedBase = data.map(o => ({
      orderId: String(o.orderid ?? '').trim(),
      orderNr: String(o.ordernr ?? '').trim(),
      appbestnr: String(o.appbestnr ?? '').trim(),
      appposnr: String(o.appposnr ?? '').trim(),
      oxordernr: String(o.appbestnr ?? '').trim(),   // BESTNR
      ddposition: String(o.appposnr ?? '').trim(), // BESTPOS
    }));

    // 3) aufnr-Index aufbauen (ein Request pro unique BESTNR)
    const aufnrIndex = await buildAufnrIndex(FI_NR, normalizedBase);

    // 4) normalized um 'aufnr' erweitern
    const normalized = normalizedBase.map(o => {
      const bestnr = String(o.appbestnr || '').trim();
      const bestpos = String(o.appposnr || '').trim();
      const key = `${bestnr}|${bestpos}`;
      const aufnr = bestnr && bestpos ? (aufnrIndex.get(key) ?? '—') : '—';

      return {
        ...o,
        aufnr, // <-- HIER ist die gewünschte AUFNR drin
      };
    });

    // 5) Status-Batch laden wie gehabt
    const ids = normalized.map(o => o.orderId);
    const { data: batch } = await axios.post(
      '/api/proxy/pims-order-status/batch',
      { orderids: ids },
      { headers: { 'Content-Type': 'application/json' } }
    );

    const byId = Object.fromEntries(normalized.map(o => [o.orderId, o]));

    // 6) Finales Merge – 'aufnr' bleibt erhalten
    ordersWithStatus.value = (batch.items || []).map(item => ({
      orderId: item.orderId,
      orderNr: byId[item.orderId]?.orderNr || item.orderNr || '—',
      oxordernr: byId[item.orderId]?.oxordernr || '—',
      ddposition: byId[item.orderId]?.ddposition || '—',
      aufnr: byId[item.orderId]?.aufnr || '—', // <- verfügbar (falls du's später anzeigen willst)
      productNr: item.productNr || 'Unbekannt',
      status: item.status || 'Unbekannt',
      productStatus: item.productStatus || 'Unbekannt',
    }));
  } catch (err) {
    console.error('Fehler beim Abrufen:', err);
    if (err.response) {
      console.error('Response data:', err.response.data);
    }
  }
});
</script>


<style scoped>

</style>
