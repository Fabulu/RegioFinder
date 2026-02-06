<template>
  <main class="page">
    <header class="header">
      <div>
        <h1>Verkäufer-Portal – Jurapark Regional</h1>
        <p class="subtitle">
          Schnell Updates posten wie “Instagram”, aber strukturiert: Produkte, Verfügbarkeit, Ferien.
        </p>

        <div class="who">
          <span class="chip">Eingeloggt als</span>
          <strong>{{ account.displayName }}</strong>
          <span class="muted">({{ account.shopName }})</span>
        </div>
      </div>

      <div class="status">
        <span class="dot" :class="{ on: isSaved }"></span>
        <span>{{ isSaved ? "Gespeichert (Mock)" : "Noch nicht gespeichert" }}</span>
      </div>
    </header>

    <!-- QUICK ACTIONS -->
    <section class="card hero">
      <div class="heroTop">
        <h2>Heute posten</h2>
        <p class="hint">
          Das ist das, was du täglich brauchst: “Was gibt’s heute?” + Foto(s) + optional Preis.
        </p>
      </div>

      <div class="formGrid">
        <label class="field">
          <span class="label">Beitrag-Typ</span>
          <select v-model="post.kind">
            <option value="availability">Heute verfügbar</option>
            <option value="new">Neu im Sortiment</option>
            <option value="promo">Aktion / Hinweis</option>
          </select>
        </label>

        <label class="field">
          <span class="label">Kategorie</span>
          <select v-model="post.category">
            <option value="">(optional)</option>
            <option>Eier</option>
            <option>Milchprodukte</option>
            <option>Fleisch</option>
            <option>Brot</option>
            <option>Gemüse</option>
            <option>Obst</option>
            <option>Getränke</option>
            <option>Sonstiges</option>
          </select>
        </label>

        <label class="field wide">
          <span class="label">Text *</span>
          <input v-model="post.text" type="text" placeholder="z.B. Heute: Raclette, Joghurt, Eier" />
        </label>

        <label class="field">
          <span class="label">Preis (optional)</span>
          <input v-model="post.price" type="text" placeholder="z.B. CHF 9.50" />
        </label>

        <label class="field">
          <span class="label">Gültig bis (optional)</span>
          <input v-model="post.until" type="datetime-local" />
        </label>

        <label class="field wide">
          <span class="label">Foto(s)</span>
          <input class="file" type="file" accept="image/*" multiple @change="onPostFilesSelected" />
        </label>
      </div>

      <div class="row">
        <button class="btn primary" type="button" @click="publishPostMock">
          Beitrag veröffentlichen
        </button>
        <span class="error" v-if="postError">{{ postError }}</span>
      </div>
    </section>

    <!-- POSTS FEED (seller view) -->
    <section class="card" v-if="posts.length">
      <h2>Deine Beiträge (Mock)</h2>

      <div class="feed">
        <article class="post" v-for="p in posts" :key="p.id">
          <div class="postThumb" v-if="p.photos.length">
            <img :src="p.photos[0]" alt="" />
          </div>
          <div class="postThumb placeholder" v-else>Kein Foto</div>

          <div class="postBody">
            <div class="postMeta">
              <span class="pill">{{ kindLabel(p.kind) }}</span>
              <span class="pill" v-if="p.category">{{ p.category }}</span>
              <span class="muted">{{ formatWhen(p.createdAt) }}</span>
              <span class="muted" v-if="p.until">• gültig bis {{ formatUntil(p.until) }}</span>
            </div>

            <div class="postText">{{ p.text }}</div>

            <div class="postMeta" v-if="p.price">
              <span class="price">{{ p.price }}</span>
            </div>

            <div class="row">
              <button class="btn danger" type="button" @click="removePost(p.id)">
                Löschen
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- VACATION -->
    <section class="card">
      <div class="splitHead">
        <div>
          <h2>Ferien / Betriebsferien</h2>
          <p class="hint">
            Schnell schalten: für 2 Wochen zu? Hier eintragen – dann wird’s überall sichtbar.
          </p>
        </div>
        <label class="toggleBig">
          <input type="checkbox" v-model="form.vacation.enabled" />
          <span><strong>Ferien aktiv</strong></span>
        </label>
      </div>

      <div class="formGrid" :class="{ disabled: !form.vacation.enabled }">
        <label class="field">
          <span class="label">Von</span>
          <input v-model="form.vacation.from" type="date" :disabled="!form.vacation.enabled" />
        </label>

        <label class="field">
          <span class="label">Bis</span>
          <input v-model="form.vacation.to" type="date" :disabled="!form.vacation.enabled" />
        </label>

        <label class="field wide">
          <span class="label">Notiz (optional)</span>
          <input
            v-model="form.vacation.note"
            type="text"
            placeholder="z.B. Ab 03.03. wieder offen"
            :disabled="!form.vacation.enabled"
          />
        </label>
      </div>

      <div class="row">
        <button class="btn primary" type="button" @click="saveMock">
          Ferien speichern
        </button>
      </div>
    </section>

    <!-- OPENING HOURS -->
    <section class="card">
      <details>
        <summary class="summary">
          <span>Öffnungszeiten (selten ändern)</span>
          <span class="muted">– klicken zum Aufklappen</span>
        </summary>

        <p class="hint" style="margin-top: 10px;">
          Pro Tag Zeiten setzen oder “Geschlossen”. (Diese Daten ändern sich selten.)
        </p>

        <div class="hours">
          <div class="hoursRow" v-for="d in days" :key="d.key">
            <div class="day">{{ d.label }}</div>

            <label class="toggle">
              <input type="checkbox" v-model="form.hours[d.key].closed" />
              <span>Geschlossen</span>
            </label>

            <div class="timeInputs" :class="{ disabled: form.hours[d.key].closed }">
              <input
                v-model="form.hours[d.key].from"
                type="time"
                :disabled="form.hours[d.key].closed"
              />
              <span class="sep">–</span>
              <input
                v-model="form.hours[d.key].to"
                type="time"
                :disabled="form.hours[d.key].closed"
              />
            </div>
          </div>
        </div>

        <div class="row">
          <button class="btn primary" type="button" @click="saveMock">
            Öffnungszeiten speichern
          </button>
        </div>
      </details>
    </section>

    <!-- PROFILE (RARELY CHANGED) -->
    <section class="card">
      <details>
        <summary class="summary">
          <span>Profil / Standort (selten ändern)</span>
          <span class="muted">– klicken zum Aufklappen</span>
        </summary>

        <div class="mapWrap">
          <div class="mapHead">
            <h3 class="subhead">Karte (Standort-Vorschau)</h3>
            <p class="hint">
              Pin per Klick setzen. Für echte Adress→Koordinaten später Geokoding im Backend.
            </p>
          </div>

          <div ref="mapEl" class="map"></div>

          <div class="row">
            <button class="btn" type="button" @click="getLocation">
              Aktuellen Standort übernehmen
            </button>
            <button class="btn" type="button" @click="pinFromAddressMock">
              Pin aus Adresse setzen (Mock)
            </button>
          </div>
        </div>

        <div class="formGrid" style="margin-top: 14px;">
          <label class="field">
            <span class="label">Name des Betriebs *</span>
            <input v-model="form.name" type="text" placeholder="z.B. Hofladen Müller" />
          </label>

          <label class="field">
            <span class="label">Betriebstyp *</span>
            <select v-model="form.type">
              <option value="">Bitte wählen…</option>
              <option>Hofladen</option>
              <option>Direktvermarkter</option>
              <option>Metzgerei</option>
              <option>Bäckerei</option>
              <option>Chäslädeli</option>
              <option>Dorfladen</option>
              <option>Regiomarkt</option>
              <option>Manufaktur</option>
            </select>
          </label>

          <label class="field wide">
            <span class="label">Adresse *</span>
            <input v-model="form.address" type="text" placeholder="Strasse, PLZ, Ort" />
          </label>

          <label class="field">
            <span class="label">Telefon (optional)</span>
            <input v-model="form.phone" type="tel" placeholder="+41 79 123 45 67" />
          </label>

          <label class="field">
            <span class="label">Website / Instagram (optional)</span>
            <input v-model="form.link" type="url" placeholder="https://…" />
          </label>

          <label class="field">
            <span class="label">Breitengrad (optional)</span>
            <input v-model="form.lat" type="number" step="0.000001" placeholder="47.123456" />
          </label>

          <label class="field">
            <span class="label">Längengrad (optional)</span>
            <input v-model="form.lon" type="number" step="0.000001" placeholder="8.123456" />
          </label>
        </div>

        <div class="row">
          <button class="btn primary" type="button" @click="saveMock">
            Profil speichern
          </button>
        </div>
      </details>
    </section>

    <section class="card">
      <details class="debug">
        <summary class="summary">
          <span>JSON-Vorschau (Mock)</span>
          <span class="muted">– nur für Dev / Demo</span>
        </summary>
        <pre>{{ pretty }}</pre>
      </details>
    </section>
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from "vue";

const account = reactive({
  displayName: "Anna Hoyer",
  shopName: "Hofladen Beispiel",
});

const days = [
  { key: "mon", label: "Mo" },
  { key: "tue", label: "Di" },
  { key: "wed", label: "Mi" },
  { key: "thu", label: "Do" },
  { key: "fri", label: "Fr" },
  { key: "sat", label: "Sa" },
  { key: "sun", label: "So" },
];

const makeHours = () => ({
  mon: { closed: false, from: "09:00", to: "18:00" },
  tue: { closed: false, from: "09:00", to: "18:00" },
  wed: { closed: false, from: "09:00", to: "18:00" },
  thu: { closed: false, from: "09:00", to: "18:00" },
  fri: { closed: false, from: "09:00", to: "18:00" },
  sat: { closed: false, from: "09:00", to: "12:00" },
  sun: { closed: true, from: "", to: "" },
});

const form = reactive({
  name: account.shopName,
  type: "Hofladen",
  phone: "",
  link: "",
  address: "Beispielstrasse 1, 5200 Brugg",
  lat: "",
  lon: "",
  hours: makeHours(),
  vacation: {
    enabled: false,
    from: "",
    to: "",
    note: "",
  },
});

const post = reactive({
  kind: "availability",
  category: "",
  text: "",
  price: "",
  until: "",
  files: [],
});

const posts = ref([]);
const postError = ref("");
const isSaved = ref(false);

const pretty = computed(() =>
  JSON.stringify(
    {
      account,
      profile: form,
      posts: posts.value,
    },
    null,
    2
  )
);

// --- MAP (Leaflet via CDN injected at runtime) ---
const mapEl = ref(null);
let map = null;
let marker = null;

function injectLeaflet() {
  const cssId = "leaflet-css";
  const jsId = "leaflet-js";

  const ensureCss = () =>
    new Promise((resolve) => {
      if (document.getElementById(cssId)) return resolve();
      const link = document.createElement("link");
      link.id = cssId;
      link.rel = "stylesheet";
      link.href = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
      link.onload = () => resolve();
      document.head.appendChild(link);
    });

  const ensureJs = () =>
    new Promise((resolve) => {
      if (document.getElementById(jsId)) return resolve();
      const script = document.createElement("script");
      script.id = jsId;
      script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
      script.onload = () => resolve();
      document.body.appendChild(script);
    });

  return ensureCss().then(ensureJs);
}

function initMap() {
  if (!mapEl.value) return;
  const L = window.L;
  if (!L) return;

  const startLat = Number(form.lat) || 47.405;
  const startLon = Number(form.lon) || 8.06;

  map = L.map(mapEl.value, { zoomControl: true }).setView([startLat, startLon], 11);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: "© OpenStreetMap",
  }).addTo(map);

  marker = L.marker([startLat, startLon]).addTo(map);
  marker.bindPopup("Standort").openPopup();

  map.on("click", (e) => {
    const { lat, lng } = e.latlng;
    setPin(lat, lng, "Pin gesetzt");
  });
}

function setPin(lat, lon, label = "Standort") {
  if (!map || !marker) return;
  marker.setLatLng([lat, lon]);
  map.setView([lat, lon], Math.max(map.getZoom(), 14));
  marker.bindPopup(label).openPopup();
  form.lat = String(Number(lat).toFixed(6));
  form.lon = String(Number(lon).toFixed(6));
}

function pinFromAddressMock() {
  // Hackathon mock: if no coords, use Jurapark-ish fallback
  const fallbackLat = 47.405;
  const fallbackLon = 8.06;

  const lat = form.lat ? Number(form.lat) : fallbackLat;
  const lon = form.lon ? Number(form.lon) : fallbackLon;

  setPin(lat, lon, "Pin aus Adresse (Mock)");
}

function getLocation() {
  if (!navigator.geolocation) {
    alert("Geolocation wird von diesem Browser nicht unterstützt.");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      setPin(pos.coords.latitude, pos.coords.longitude, "Aktueller Standort");
    },
    (err) => {
      alert(`Standort konnte nicht ermittelt werden: ${err.message}`);
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

onMounted(async () => {
  await injectLeaflet();
  initMap();
});

onBeforeUnmount(() => {
  try {
    if (map) map.remove();
  } catch {}
  map = null;
  marker = null;
});

watch(
  () => [form.lat, form.lon],
  ([lat, lon]) => {
    const a = Number(lat);
    const b = Number(lon);
    if (Number.isFinite(a) && Number.isFinite(b) && map && marker) {
      marker.setLatLng([a, b]);
    }
  }
);

// --- Posting ---
function onPostFilesSelected(e) {
  post.files = Array.from(e.target.files ?? []);
}

function publishPostMock() {
  postError.value = "";

  if (!post.text.trim()) {
    postError.value = "Bitte gib einen Text ein (z.B. “Heute: Eier, Käse …”).";
    return;
  }

  const photos = post.files.map((f) => URL.createObjectURL(f));

  posts.value.unshift({
    id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()),
    kind: post.kind,
    category: post.category,
    text: post.text.trim(),
    price: post.price.trim(),
    until: post.until,
    photos,
    createdAt: new Date().toISOString(),
  });

  // reset fields (keep kind/category for speed)
  post.text = "";
  post.price = "";
  post.until = "";
  post.files = [];
  isSaved.value = true;
  setTimeout(() => (isSaved.value = false), 900);
}

function removePost(id) {
  posts.value = posts.value.filter((p) => p.id !== id);
}

function kindLabel(k) {
  if (k === "availability") return "Heute verfügbar";
  if (k === "new") return "Neu";
  if (k === "promo") return "Aktion";
  return "Beitrag";
}

function formatWhen(iso) {
  try {
    const d = new Date(iso);
    return d.toLocaleString("de-CH", { dateStyle: "medium", timeStyle: "short" });
  } catch {
    return iso;
  }
}

function formatUntil(val) {
  try {
    const d = new Date(val);
    return d.toLocaleString("de-CH", { dateStyle: "medium", timeStyle: "short" });
  } catch {
    return val;
  }
}

function saveMock() {
  isSaved.value = true;
  setTimeout(() => (isSaved.value = false), 1200);
}
</script>

<style scoped>
:root {
  color-scheme: light dark;
}

.page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 26px 16px;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--text);
  background: radial-gradient(1100px 600px at 10% 0%, color-mix(in srgb, var(--accent) 18%, transparent), transparent),
              radial-gradient(900px 500px at 90% 10%, color-mix(in srgb, var(--accent2) 14%, transparent), transparent);
}

/* Light/Dark variables */
@media (prefers-color-scheme: light) {
  .page {
    --bg: #ffffff;
    --card: #ffffff;
    --text: #111827;
    --muted: #4b5563;
    --border: #e5e7eb;
    --shadow: 0 10px 28px rgba(0,0,0,0.07);
    --accent: #16a34a;
    --accent2: #0ea5e9;
    --danger: #dc2626;
    --pill: rgba(14,165,233,0.12);
    --chip: rgba(22,163,74,0.12);
  }
}

@media (prefers-color-scheme: dark) {
  .page {
    --bg: #0b1220;
    --card: #0f1a2b;
    --text: #e5e7eb;
    --muted: #a8b1c2;
    --border: rgba(255,255,255,0.12);
    --shadow: 0 12px 32px rgba(0,0,0,0.45);
    --accent: #22c55e;
    --accent2: #38bdf8;
    --danger: #f87171;
    --pill: rgba(56,189,248,0.18);
    --chip: rgba(34,197,94,0.18);
  }
}

.header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
}

.subtitle {
  color: var(--muted);
  margin: 6px 0 0 0;
}

.who {
  margin-top: 10px;
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.chip {
  background: var(--chip);
  border: 1px solid var(--border);
  padding: 5px 10px;
  border-radius: 999px;
  color: var(--text);
  font-size: 12px;
}

.muted {
  color: var(--muted);
}

.status {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--muted);
  font-size: 14px;
  padding-top: 6px;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: color-mix(in srgb, var(--muted) 40%, transparent);
  display: inline-block;
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent) 0%, transparent);
}
.dot.on {
  background: var(--accent);
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent) 25%, transparent);
}

.card {
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 16px;
  margin: 14px 0;
  background: color-mix(in srgb, var(--card) 92%, transparent);
  box-shadow: var(--shadow);
  backdrop-filter: blur(6px);
}

.hero {
  border-color: color-mix(in srgb, var(--accent2) 35%, var(--border));
}

.heroTop {
  display: grid;
  gap: 6px;
  margin-bottom: 10px;
}

.formGrid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 10px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field.wide {
  grid-column: span 2;
}

.label {
  font-size: 13px;
  color: var(--muted);
}

/* IMPORTANT: selects must be readable in dark mode */
input,
select {
  border: 1px solid var(--border);
  background: color-mix(in srgb, var(--bg) 14%, transparent);
  color: var(--text);
  border-radius: 12px;
  padding: 10px 10px;
  font-size: 14px;
  outline: none;
}

select option {
  background: var(--card);
  color: var(--text);
}

input::placeholder {
  color: color-mix(in srgb, var(--muted) 70%, transparent);
}

input:focus,
select:focus {
  border-color: color-mix(in srgb, var(--accent2) 65%, var(--border));
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent2) 22%, transparent);
}

.file {
  padding: 8px;
}

.row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 12px;
  flex-wrap: wrap;
}

.hint {
  color: var(--muted);
  font-size: 13px;
}

.error {
  color: var(--danger);
  font-size: 13px;
}

.btn {
  border: 1px solid var(--border);
  background: color-mix(in srgb, var(--bg) 10%, transparent);
  color: var(--text);
  padding: 10px 12px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  transition: transform 120ms ease, border-color 120ms ease, background 120ms ease;
}

.btn:hover {
  border-color: color-mix(in srgb, var(--accent2) 40%, var(--border));
  background: color-mix(in srgb, var(--accent2) 10%, transparent);
}

.btn:active {
  transform: translateY(1px);
}

.btn.primary {
  border-color: color-mix(in srgb, var(--accent) 55%, var(--border));
  background: linear-gradient(
    135deg,
    color-mix(in srgb, var(--accent) 85%, #000 0%),
    color-mix(in srgb, var(--accent2) 55%, #000 0%)
  );
  color: #07140d;
  font-weight: 800;
}

.btn.danger {
  border-color: color-mix(in srgb, var(--danger) 55%, var(--border));
  background: color-mix(in srgb, var(--danger) 10%, transparent);
  color: color-mix(in srgb, var(--danger) 90%, var(--text));
}

.splitHead {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
}

.toggleBig {
  display: flex;
  align-items: center;
  gap: 10px;
}

.summary {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 10px;
  cursor: pointer;
  list-style: none;
  padding: 4px 0;
}

details > summary::-webkit-details-marker {
  display: none;
}

.hours {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.hoursRow {
  display: grid;
  grid-template-columns: 72px 130px 1fr;
  gap: 10px;
  align-items: center;
}

.day {
  font-weight: 800;
  color: var(--text);
}

.toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--muted);
  font-size: 14px;
}

.timeInputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.timeInputs.disabled {
  opacity: 0.55;
}

.sep {
  color: var(--muted);
}

.feed {
  display: grid;
  gap: 12px;
  margin-top: 10px;
}

.post {
  border: 1px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  display: grid;
  grid-template-columns: 110px 1fr;
  background: color-mix(in srgb, var(--bg) 6%, transparent);
}

.postThumb {
  width: 110px;
  height: 110px;
  background: color-mix(in srgb, var(--accent2) 10%, transparent);
  display: grid;
  place-items: center;
  overflow: hidden;
  border-right: 1px solid var(--border);
}

.postThumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.postThumb.placeholder {
  color: var(--muted);
  font-size: 13px;
}

.postBody {
  padding: 12px;
}

.postMeta {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 6px;
}

.pill {
  background: var(--pill);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 999px;
}

.postText {
  color: var(--text);
  font-size: 15px;
  line-height: 1.35;
}

.price {
  font-weight: 900;
  color: color-mix(in srgb, var(--accent) 85%, var(--text));
}

.mapWrap {
  margin-top: 12px;
}

.map {
  height: 320px;
  border-radius: 16px;
  border: 1px solid var(--border);
  overflow: hidden;
  margin-top: 10px;
}

.disabled {
  opacity: 0.6;
  pointer-events: none;
}

pre {
  background: color-mix(in srgb, var(--bg) 8%, transparent);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 12px;
  overflow: auto;
  font-size: 12px;
  color: var(--text);
}

@media (max-width: 760px) {
  .header {
    flex-direction: column;
  }
  .formGrid {
    grid-template-columns: 1fr;
  }
  .field.wide {
    grid-column: span 1;
  }
  .hoursRow {
    grid-template-columns: 72px 1fr;
  }
  .post {
    grid-template-columns: 1fr;
  }
  .postThumb {
    width: 100%;
    height: 180px;
    border-right: none;
    border-bottom: 1px solid var(--border);
  }
}
</style>
