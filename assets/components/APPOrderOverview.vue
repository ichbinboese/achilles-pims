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
          placeholder="z.B. 114AB... 114BE..."
          class="w-72 p-2 border rounded-full dark:bg-stone-700 dark:text-white"
        />
      </div>
      <div class="text-sm text-stone-600 dark:text-stone-300">
        Gefunden: {{ filtered.length }} · Seite {{ currentPage }} / {{ totalPages || 1 }}
      </div>
    </div>

    <!-- Loader / Progressbar (sichtbar solange nicht komplett geladen) -->
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
            <template v-if="uniqueBestnrTotal > 0">
              (Datensätze {{ processedBestnr }} / {{ uniqueBestnrTotal }})
            </template>
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
        <!-- Skeleton-Zeilen solange noch geladen wird -->
        <tr v-if="!allLoaded" v-for="n in 3" :key="'skeleton-'+n" class="animate-pulse">
          <td colspan="7" class="py-3">
            <div class="h-3 w-1/2 bg-stone-200 dark:bg-stone-700 rounded"></div>
          </td>
        </tr>

        <tr v-for="order in paged" :key="order.orderId" class="odd:bg-gray-100 dark:odd:bg-stone-900">
          <td>{{ order.aufnr }}</td>
          <td>
            <router-link
              :to="{
                name: 'result',
                query: {
                  fiNr: 114,
                  bestnr: order.oxordernr,
                  bestpos: order.ddposition,
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
          <td colspan="7" class="text-center py-6 text-stone-500 odd:bg-gray-100 dark:odd:bg-stone-800">
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
import { useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const route = useRoute();
const toast = useToast();

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
    const fields = [
      o.aufnr,
      o.oxordernr,
      o.orderNr,
      o.productNr,
    ]
      .map(v => String(v ?? '').toLowerCase().trim())
      .filter(Boolean);

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

// ===== Loader / Progress =====
const isLoading = ref(true);
const progressPercent = ref(0);            // 0..100
const totalSteps = ref(1);                 // wird nach Grundliste neu gesetzt
const completedSteps = ref(0);

// Für die Statuszeile: wie viele BESTNR-Lookups stehen an / sind fertig?
const uniqueBestnrTotal = ref(0);
const processedBestnr = ref(0);

const allLoaded = computed(() => !isLoading.value && progressPercent.value >= 100);

function resetProgress() {
  isLoading.value = true;
  progressPercent.value = 0;
  totalSteps.value = 1;
  completedSteps.value = 0;
  uniqueBestnrTotal.value = 0;
  processedBestnr.value = 0;
}

function bump(step = 1) {
  completedSteps.value += step;
  // Schutz vor Division durch 0
  const pct = totalSteps.value > 0 ? Math.round((completedSteps.value / totalSteps.value) * 100) : 0;
  progressPercent.value = Math.min(100, Math.max(0, pct));
}

// ===== helper: aufnr-Index bauen (mit Progress-Callback) =====
const FI_NR = 114;

async function buildAufnrIndex(fiNr, normalizedList, onStep = () => {}) {
  const uniqueBestnrs = Array.from(
    new Set(
      normalizedList
        .map(o => String(o.oxordernr || '').trim())
        .filter(v => v && v !== '—')
    )
  );

  uniqueBestnrTotal.value = uniqueBestnrs.length;

  const index = new Map(); // key: `${bestnr}|${bestpos}` -> aufnr

  // Parallel abrufen, aber Schrittzähler nach JEDEM Request erhöhen
  await Promise.all(
    uniqueBestnrs.map(async (bestnr) => {
      try {
        const { data } = await axios.get('/api/bestellung', {
          params: { fiNr, bestnr }
        });
        (data || []).forEach(row => {
          const key = `${String(row.bestnr).trim()}|${String(row.bestpos).trim()}`;
          index.set(key, row.aufnr ?? null);
        });
      } catch (e) {
        console.error('Fehler beim Laden /api/bestellung für', { fiNr, bestnr }, e);
      } finally {
        processedBestnr.value += 1;
        onStep(1); // Fortschritt für diesen Lookup
      }
    })
  );

  return index;
}

onMounted(async () => {
  resetProgress();

  // Toast aus Querystring anzeigen (falls vorhanden)
  if (route.query.toastMessage) {
    const type = route.query.toastType || 'success';
    const msg = String(route.query.toastMessage);
    if (typeof toast[type] === 'function') {
      toast[type](msg);
    } else {
      toast.success(msg);
    }
  }

  try {
    // STEP 1: Grunddaten laden
    const { data } = await axios.get('/api/app-orders');
    bump(1); // initialer Schritt erledigt

    // Normalisieren + Basisfelder
    const normalizedBase = (data || []).map(o => ({
      orderId: String(o.orderid ?? '').trim(),
      orderNr: String(o.ordernr ?? '').trim(),
      appbestnr: String(o.appbestnr ?? '').trim(),
      appposnr: String(o.appposnr ?? '').trim(),
      oxordernr: String(o.appbestnr ?? '').trim(),   // BESTNR
      ddposition: String(o.appposnr ?? '').trim(),   // BESTPOS
    }));

    // Jetzt kennen wir die Anzahl der BESTNR-Lookups → totalSteps aktualisieren:
    // total = 1 (Grundliste) + uniqueBestnr + 1 (Status-Batch)
    const uniqueCount = Array.from(new Set(normalizedBase.map(o => o.oxordernr).filter(Boolean))).length;
    totalSteps.value = 1 + uniqueCount + 1;

    // STEP 2: AUFNR-Index aufbauen (mit Progress)
    const aufnrIndex = await buildAufnrIndex(FI_NR, normalizedBase, bump);

    // AUFNR mergen
    const normalized = normalizedBase.map(o => {
      const bestnr = String(o.appbestnr || '').trim();
      const bestpos = String(o.appposnr || '').trim();
      const key = `${bestnr}|${bestpos}`;
      const aufnr = bestnr && bestpos ? (aufnrIndex.get(key) ?? '—') : '—';
      return { ...o, aufnr };
    });

    // STEP 3: Status-Batch
    const ids = normalized.map(o => o.orderId);
    const { data: batch } = await axios.post(
      '/api/proxy/pims-order-status/batch',
      { orderids: ids },
      { headers: { 'Content-Type': 'application/json' } }
    );
    bump(1); // Batch erledigt

    const byId = Object.fromEntries(normalized.map(o => [o.orderId, o]));

    ordersWithStatus.value = (batch?.items || []).map(item => ({
      orderId: item.orderId,
      orderNr: byId[item.orderId]?.orderNr || item.orderNr || '—',
      oxordernr: byId[item.orderId]?.oxordernr || '—',
      ddposition: byId[item.orderId]?.ddposition || '—',
      aufnr: byId[item.orderId]?.aufnr || '—',
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
    // Falls einzelne Schritte fehlten, den Fortschritt sauber beenden
    progressPercent.value = 100;
    isLoading.value = false;
  }
});
</script>

<style scoped>
/* Optional: kleine Verbesserungen für Tabellen-Looks – Tailwind-first, aber hier neutral gehalten. */
.table th, .table td {
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}
thead tr th {
  text-align: left;
  white-space: nowrap;
}
</style>
