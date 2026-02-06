<template>
  <main class="page" :data-theme="theme">
    <!-- MINIMAL HEADER -->
    <header class="header">
      <h1 class="title">{{ account.shopName }}</h1>

      <div class="headerRight">
        <button class="linkBtn" type="button" @click="theme = theme === 'light' ? 'dark' : 'light'">
          {{ theme === "light" ? "Dark" : "Light" }}
        </button>

        <div class="status">
          <span class="dot" :class="{ on: isSaved }"></span>
          <span class="muted">{{ isSaved ? "Gespeichert (Mock)" : "Noch nicht gespeichert" }}</span>
        </div>
      </div>
    </header>

    <!-- SORTIMENT -->
    <section class="card">
      <div class="splitHead">
        <div class="splitLeft">
          <h2>Sortiment</h2>

          <!-- advanced toggle moved next to title -->
          <button
            class="linkBtn tinyLink"
            type="button"
            @click="showAdvanced = !showAdvanced"
            title="Erweiterte Optionen"
            aria-label="Erweiterte Optionen"
          >
            …
          </button>
        </div>

        <div class="pillRow">
          <span class="pill">{{ sortiment.length }} Produkte</span>
          <span class="pill warnPill" v-if="sortimentExpiredCount">⚠ {{ sortimentExpiredCount }} abgelaufen</span>
        </div>
      </div>

      <div class="sortimentGrid" :class="{ solo: !baseOpen }">
        <!-- SAISONANGEBOT -->
        <div class="sortimentBox" :class="{ span2: !baseOpen }">
          <div class="boxHead">
            <div class="boxHeadLeft">
              <h3 class="boxTitle">Saisonangebot</h3>
              <span class="pill">{{ saisonangebot.length }}</span>
            </div>

            <!-- KEEP BUTTON LOCATION: show only when base is closed -->
            <div class="boxHeadRight">
              <button v-if="!baseOpen" class="btn tiny" type="button" @click="baseOpen = true">
                Basissortiment anzeigen
              </button>
            </div>
          </div>

          <div v-if="saisonangebot.length" class="sortimentList">
            <div class="sortimentItem" v-for="item in saisonangebot" :key="item.id">
              <div class="sortimentMain">
                <strong class="sortimentName">{{ item.name }}</strong>
                <span class="muted" v-if="item.expiresOn">• bis {{ formatDate(item.expiresOn) }}</span>
                <span class="pill warnPill" v-if="isExpired(item.expiresOn)">abgelaufen</span>
              </div>

              <div class="sortimentActions">
                <label class="miniField" v-if="showAdvanced">
                  <span class="miniLabel">Saisonal bis</span>
                  <input type="date" v-model="item.expiresOn" />
                </label>

                <button class="iconBtn danger" type="button" @click="removeSortimentItem(item.id)" title="Entfernen">
                  –
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty">
            Noch kein Saisonangebot.
          </div>
        </div>

        <!-- BASISSORTIMENT -->
        <div class="sortimentBox" v-if="baseOpen">
          <div class="boxHead">
            <div class="boxHeadLeft">
              <h3 class="boxTitle">Basissortiment</h3>
              <span class="pill">{{ basissortiment.length }}</span>
            </div>

            <!-- KEEP BUTTON LOCATION: hide base only from the right box -->
            <div class="boxHeadRight">
              <button class="btn tiny" type="button" @click="baseOpen = false">
                Basissortiment ausblenden
              </button>
            </div>
          </div>

          <div v-if="basissortiment.length" class="sortimentList">
            <div class="sortimentItem" v-for="item in basissortiment" :key="item.id">
              <div class="sortimentMain">
                <strong class="sortimentName">{{ item.name }}</strong>
                <span class="muted">• ohne Ablaufdatum</span>
              </div>

              <div class="sortimentActions">
                <label class="miniField" v-if="showAdvanced">
                  <span class="miniLabel">Saisonal bis</span>
                  <input type="date" v-model="item.expiresOn" />
                </label>

                <button class="iconBtn danger" type="button" @click="removeSortimentItem(item.id)" title="Entfernen">
                  –
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty">
            Noch kein Basissortiment.
          </div>
        </div>
      </div>

      <!-- ADD PRODUCT -->
      <div class="sortimentAdd">
        <div class="addRow">
          <input
            class="addName"
            v-model="sortimentDraft.name"
            type="text"
            placeholder="Neues Produkt…"
          />
          <button class="btn primary addBtn" type="button" @click="addSortimentItem">
            + Hinzufügen
          </button>
        </div>

        <div class="addAdvanced" v-if="showAdvanced">
          <label class="miniField">
            <span class="miniLabel">Saisonal bis (optional)</span>
            <input v-model="sortimentDraft.expiresOn" type="date" />
          </label>
        </div>

        <span class="error" v-if="sortimentError">{{ sortimentError }}</span>
      </div>
    </section>

    <!-- QUICK ACTIONS -->
    <section class="card hero">
      <div class="heroTop">
        <h2>Heute posten</h2>
        <p class="hint">
          Wähle ein Produkt aus dem Sortiment <strong>oder</strong> schreibe freien Text.
          Freitext wird beim Post automatisch ins Sortiment übernommen.
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
          <span class="label">Produkt (optional)</span>
          <select v-model="post.productId">
            <option value="">Keins (Freitext)</option>
            <option v-for="item in sortiment" :key="item.id" :value="item.id">
              {{ item.name }}
              <template v-if="item.expiresOn"> (bis {{ formatDate(item.expiresOn) }})</template>
            </option>
          </select>

          <span class="hint" v-if="post.productId && selectedProduct?.expiresOn && isExpired(selectedProduct.expiresOn)">
            ⚠ Dieses Produkt ist saisonal abgelaufen – trotzdem posten?
          </span>
        </label>

        <label class="field wide">
          <span class="label">Text *</span>
          <input
            v-model="post.text"
            type="text"
            placeholder="z.B. Heute: Raclette, Joghurt, Eier"
            :disabled="!!post.productId"
          />
          <span class="hint" v-if="post.productId">
            Text ist deaktiviert, weil ein Produkt ausgewählt ist.
          </span>
        </label>

        <label class="field">
          <span class="label">Preis (optional)</span>
          <input v-model="post.price" type="text" placeholder="z.B. CHF 9.50" />
        </label>

        <label class="field">
          <span class="label">Gültig bis (optional)</span>
          <input v-model="post.until" type="datetime-local" />
        </label>

        <!-- PHOTO DROPZONE -->
        <div class="field wide">
          <span class="label">Foto(s)</span>

          <div
            class="dropzone"
            :class="{ dragging: isDragging }"
            @click="triggerPostFilePicker"
            @dragenter.prevent="onDragEnter"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop"
            role="button"
            tabindex="0"
          >
            <div class="dzInner">
              <div class="dzTitle">+ Foto hinzufügen</div>
              <div class="dzSub">Klicken oder Bild(er) hierher ziehen</div>
            </div>

            <div class="dzPreview" v-if="postPreviewUrls.length">
              <img v-for="(u, idx) in postPreviewUrls" :key="idx" :src="u" alt="" />
            </div>
          </div>

          <input
            ref="postFileInput"
            class="hiddenFile"
            type="file"
            accept="image/*"
            multiple
            @change="onPostFilesSelected"
          />
        </div>
      </div>

      <div class="row">
        <button class="btn primary" type="button" @click="publishPostMock">
          Beitrag veröffentlichen
        </button>
        <span class="error" v-if="postError">{{ postError }}</span>
      </div>
    </section>

    <!-- POSTS FEED -->
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
              <span class="pill" v-if="p.productName">{{ p.productName }}</span>
              <span class="muted">{{ formatWhen(p.createdAt) }}</span>
              <span class="muted" v-if="p.until">• gültig bis {{ formatUntil(p.until) }}</span>
            </div>

            <div class="postText">
              <template v-if="p.productName && !p.text">
                {{ p.productName }}
              </template>
              <template v-else>
                {{ p.text }}
              </template>
            </div>

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
          <h2>Ferien</h2>
          <p class="hint">
            Schnell schalten: für 2 Wochen zu? Hier eintragen – dann wird’s überall sichtbar.
          </p>
        </div>
        <label class="toggleBig">
          <input type="checkbox" v-model="form.vacation.enabled" />
          <span><strong>Aktiv</strong></span>
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
          <span>Öffnungszeiten</span>
          <span class="muted">– aufklappen</span>
        </summary>

        <p class="hint" style="margin-top: 10px;">
          Pro Tag Zeiten setzen oder “Geschlossen”.
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

    <!-- PROFILE -->
    <section class="card">
      <details>
        <summary class="summary">
          <span>Profil / Standort</span>
          <span class="muted">– aufklappen</span>
        </summary>

        <div class="mapWrap">
          <div class="mapHead">
            <h3 class="subhead">Karte</h3>
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

    <!-- LOGIN INFO AT BOTTOM -->
    <footer class="footer">
      <span class="muted">Eingeloggt als</span>
      <strong>{{ account.displayName }}</strong>
    </footer>
  </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from "vue";

const theme = ref("light");
const showAdvanced = ref(false);

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

// Sortiment
const sortiment = reactive([
  { id: "p1", name: "Raclette", expiresOn: "" },
  { id: "p2", name: "Eier", expiresOn: "" },
  { id: "p3", name: "Erdbeeren", expiresOn: "2026-07-31" },
]);

const baseOpen = ref(false);

const basissortiment = computed(() => sortiment.filter((x) => !x.expiresOn));
const saisonangebot = computed(() => sortiment.filter((x) => !!x.expiresOn));

const sortimentDraft = reactive({
  name: "",
  expiresOn: "",
});
const sortimentError = ref("");

const post = reactive({
  kind: "availability",
  productId: "",
  text: "",
  price: "",
  until: "",
  files: [],
});

const postFileInput = ref(null);
const postPreviewUrls = ref([]);
const isDragging = ref(false);

const posts = ref([]);
const postError = ref("");
const isSaved = ref(false);

const selectedProduct = computed(() => sortiment.find((x) => x.id === post.productId) || null);

const sortimentExpiredCount = computed(() =>
  sortiment.filter((x) => isExpired(x.expiresOn)).length
);

const pretty = computed(() =>
  JSON.stringify(
    {
      account,
      profile: form,
      sortiment,
      posts: posts.value,
    },
    null,
    2
  )
);

function normalizeName(s) {
  return (s ?? "").trim().replace(/\s+/g, " ");
}

function addSortimentItem() {
  sortimentError.value = "";
  const name = normalizeName(sortimentDraft.name);

  if (!name) {
    sortimentError.value = "Bitte Produktname eingeben.";
    return;
  }

  const exists = sortiment.some((x) => x.name.toLowerCase() === name.toLowerCase());
  if (exists) {
    sortimentError.value = "Dieses Produkt ist bereits im Sortiment.";
    return;
  }

  sortiment.push({
    id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()),
    name,
    expiresOn: sortimentDraft.expiresOn || "",
  });

  sortimentDraft.name = "";
  sortimentDraft.expiresOn = "";
}

function removeSortimentItem(id) {
  const idx = sortiment.findIndex((x) => x.id === id);
  if (idx >= 0) sortiment.splice(idx, 1);
  if (post.productId === id) post.productId = "";
}

// When selecting a product -> disable/clear text
watch(
  () => post.productId,
  (val) => {
    if (val) post.text = "";
  }
);

// --- Drag & Drop for photos ---
function onDragEnter() {
  isDragging.value = true;
}
function onDragOver() {
  isDragging.value = true;
}
function onDragLeave() {
  isDragging.value = false;
}
function onDrop(e) {
  isDragging.value = false;
  const files = Array.from(e.dataTransfer?.files ?? []).filter((f) => f.type.startsWith("image/"));
  if (!files.length) return;
  applyPostFiles(files);
}

function applyPostFiles(files) {
  post.files = files;
  postPreviewUrls.value.forEach((u) => URL.revokeObjectURL(u));
  postPreviewUrls.value = files.map((f) => URL.createObjectURL(f));
}

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
function triggerPostFilePicker() {
  postFileInput.value?.click();
}

function onPostFilesSelected(e) {
  const files = Array.from(e.target.files ?? []);
  applyPostFiles(files);
}

function clearPostImages() {
  postPreviewUrls.value.forEach((u) => URL.revokeObjectURL(u));
  postPreviewUrls.value = [];
  post.files = [];
  if (postFileInput.value) postFileInput.value.value = "";
}

function publishPostMock() {
  postError.value = "";

  const hasProduct = !!post.productId;
  const text = normalizeName(post.text);

  if (!hasProduct && !text) {
    postError.value = "Bitte wähle ein Produkt ODER gib einen Text ein.";
    return;
  }

  let productName = "";
  let productId = post.productId;

  if (!hasProduct) {
    const name = text;
    const existing = sortiment.find((x) => x.name.toLowerCase() === name.toLowerCase());
    if (existing) {
      productId = existing.id;
      productName = existing.name;
    } else {
      const newItem = {
        id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()),
        name,
        expiresOn: "",
      };
      sortiment.push(newItem);
      productId = newItem.id;
      productName = newItem.name;
    }
  } else {
    productName = selectedProduct.value?.name || "";
  }

  const photos = post.files.map((f) => URL.createObjectURL(f));

  posts.value.unshift({
    id: crypto.randomUUID ? crypto.randomUUID() : String(Date.now()),
    kind: post.kind,
    productId,
    productName,
    text: hasProduct ? "" : text,
    price: normalizeName(post.price),
    until: post.until,
    photos,
    createdAt: new Date().toISOString(),
  });

  post.productId = "";
  post.text = "";
  post.price = "";
  post.until = "";
  clearPostImages();

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

function formatDate(yyyyMmDd) {
  try {
    const d = new Date(yyyyMmDd + "T00:00:00");
    return d.toLocaleDateString("de-CH", { dateStyle: "medium" });
  } catch {
    return yyyyMmDd;
  }
}

function isExpired(yyyyMmDd) {
  if (!yyyyMmDd) return false;
  const today = new Date();
  const d = new Date(yyyyMmDd + "T23:59:59");
  return d < today;
}

function saveMock() {
  isSaved.value = true;
  setTimeout(() => (isSaved.value = false), 1200);
}
</script>

<style scoped>
:root { color-scheme: light dark; }

* { box-sizing: border-box; }
img { max-width: 100%; }

.page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 22px 16px;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--text);
  font-size: 18px;
  line-height: 1.45;
  overflow-x: hidden;
  background: radial-gradient(1100px 600px at 10% 0%, color-mix(in srgb, var(--accent) 18%, transparent), transparent),
              radial-gradient(900px 500px at 90% 10%, color-mix(in srgb, var(--accent2) 14%, transparent), transparent);
}

/* Stronger contrast theme vars */
.page[data-theme="light"]{
  --bg: #f4f7fb;
  --card: #ffffff;
  --text: #0b1220;
  --muted: #334155;
  --border: rgba(15, 23, 42, 0.14);
  --shadow: 0 14px 34px rgba(2, 6, 23, 0.10);
  --accent: #0f766e;   /* teal */
  --accent2: #2563eb;  /* blue */
  --danger: #b91c1c;
  --pill: rgba(37, 99, 235, 0.14);
  --chip: rgba(15, 118, 110, 0.14);
  --warn: rgba(245, 158, 11, 0.22);
}

.page[data-theme="dark"]{
  --bg: #070b14;
  --card: #0b1220;
  --text: #f1f5f9;
  --muted: rgba(226, 232, 240, 0.78);
  --border: rgba(226,232,240,0.14);
  --shadow: 0 18px 44px rgba(0,0,0,0.60);
  --accent: #22c55e;
  --accent2: #38bdf8;
  --danger: #fb7185;
  --pill: rgba(56,189,248,0.20);
  --chip: rgba(34,197,94,0.18);
  --warn: rgba(245,158,11,0.26);
}

h1 { font-size: 28px; line-height: 1.15; margin: 0; }
h2 { font-size: 22px; margin: 0; }
h3 { font-size: 18px; margin: 0; }

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 14px;
}

.title {
  font-weight: 950;
  letter-spacing: -0.01em;
  color: var(--text);
  text-shadow: 0 1px 0 rgba(0,0,0,0.08);
}

.headerRight {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  min-width: 0;
}

.muted { color: var(--muted); }

.status {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: color-mix(in srgb, var(--muted) 40%, transparent);
  display: inline-block;
  box-shadow: 0 0 0 6px color-mix(in srgb, var(--accent) 0%, transparent);
}
.dot.on {
  background: var(--accent);
  box-shadow: 0 0 0 6px color-mix(in srgb, var(--accent) 25%, transparent);
}

.card {
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 18px;
  margin: 16px 0;
  background: color-mix(in srgb, var(--card) 96%, transparent);
  box-shadow: var(--shadow);
  backdrop-filter: blur(6px);
  min-width: 0;
}

.hero {
  border-color: color-mix(in srgb, var(--accent2) 40%, var(--border));
  background: linear-gradient(
    180deg,
    color-mix(in srgb, var(--accent2) 9%, var(--card)),
    var(--card)
  );
}

.splitHead {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
  min-width: 0;
}

.splitLeft{
  display: flex;
  align-items: center;
  gap: 10px;
}

.pillRow { display: flex; gap: 8px; flex-wrap: wrap; }

.pill {
  background: var(--pill);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 14px;
  padding: 6px 12px;
  border-radius: 999px;
}

.warnPill { background: var(--warn); }

.hint { color: var(--muted); font-size: 16px; margin-top: 8px; }
.error { color: var(--danger); font-size: 14px; }

/* Sortiment */
.sortimentGrid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  min-width: 0;
}

.sortimentBox {
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 14px;
  background: color-mix(in srgb, var(--bg) 22%, transparent);
  min-width: 0;
}

.boxHead {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  min-width: 0;
}

.boxHeadLeft {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  min-width: 0;
}

.boxHeadRight {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.boxTitle { font-weight: 950; }

.empty {
  margin-top: 10px;
  padding: 12px;
  border-radius: 14px;
  border: 1px dashed var(--border);
  color: var(--muted);
  background: color-mix(in srgb, var(--bg) 18%, transparent);
}

/* Add row inline */
.sortimentAdd { margin-top: 12px; }

.addRow {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: center;
  min-width: 0;
}

.addName {
  width: 100%;
  min-width: 0;
}

.addBtn { white-space: nowrap; }

.addAdvanced { margin-top: 10px; }

/* Form */
.formGrid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  margin-top: 12px;
  min-width: 0;
}

.field { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.field.wide { grid-column: span 2; }

.label { font-size: 16px; color: var(--muted); }

input,
select {
  border: 1px solid color-mix(in srgb, var(--border) 95%, transparent);
  background: color-mix(in srgb, var(--bg) 38%, transparent);
  color: var(--text);
  border-radius: 14px;
  padding: 14px 14px;
  font-size: 18px;
  outline: none;
  max-width: 100%;
  min-width: 0;
}

select option { background: var(--card); color: var(--text); }
input::placeholder { color: color-mix(in srgb, var(--muted) 72%, transparent); }

input:focus,
select:focus {
  border-color: color-mix(in srgb, var(--accent2) 70%, var(--border));
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent2) 22%, transparent);
}

.row { display: flex; align-items: center; gap: 12px; margin-top: 14px; flex-wrap: wrap; }

.btn {
  border: 1px solid color-mix(in srgb, var(--border) 100%, transparent);
  background: color-mix(in srgb, var(--bg) 26%, transparent);
  color: var(--text);
  padding: 14px 16px;
  border-radius: 14px;
  cursor: pointer;
  font-size: 18px;
  transition: transform 120ms ease, border-color 120ms ease, background 120ms ease, box-shadow 120ms ease;
  max-width: 100%;
}
.btn:hover {
  border-color: color-mix(in srgb, var(--accent2) 55%, var(--border));
  background: color-mix(in srgb, var(--accent2) 14%, transparent);
}
.btn:active { transform: translateY(1px); }

.btn.primary {
  border-color: color-mix(in srgb, var(--accent) 62%, var(--border));
  background: linear-gradient(
    135deg,
    color-mix(in srgb, var(--accent) 78%, #000 0%),
    color-mix(in srgb, var(--accent2) 66%, #000 0%)
  );
  color: #04110c;
  font-weight: 950;
  box-shadow: 0 10px 22px color-mix(in srgb, var(--accent2) 14%, transparent);
}

.btn.danger {
  border-color: color-mix(in srgb, var(--danger) 55%, var(--border));
  background: color-mix(in srgb, var(--danger) 14%, transparent);
  color: color-mix(in srgb, var(--danger) 92%, var(--text));
}

.btn.tiny {
  padding: 10px 12px;
  font-size: 14px;
  border-radius: 12px;
}

/* link button */
.linkBtn {
  border: 1px solid transparent;
  background: color-mix(in srgb, var(--bg) 18%, transparent);
  color: var(--text);
  cursor: pointer;
  font-size: 14px;
  padding: 10px 10px;
  border-radius: 12px;
}
.linkBtn:hover {
  border-color: color-mix(in srgb, var(--accent2) 40%, var(--border));
  background: color-mix(in srgb, var(--accent2) 14%, transparent);
}
.tinyLink {
  font-size: 18px;
  padding: 8px 10px;
  line-height: 1;
}

.iconBtn {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: color-mix(in srgb, var(--bg) 26%, transparent);
  color: var(--text);
  cursor: pointer;
  font-size: 22px;
  line-height: 1;
}
.iconBtn.danger {
  border-color: color-mix(in srgb, var(--danger) 60%, var(--border));
  background: color-mix(in srgb, var(--danger) 14%, transparent);
  color: color-mix(in srgb, var(--danger) 92%, var(--text));
}

/* Sortiment list */
.sortimentList { margin-top: 12px; display: grid; gap: 12px; }

.sortimentItem {
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 14px;
  background: color-mix(in srgb, var(--bg) 18%, transparent);
  display: flex;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  min-width: 0;
}

.sortimentMain { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; min-width: 0; }
.sortimentName { font-weight: 950; font-size: 18px; }

.sortimentActions { display: flex; gap: 12px; align-items: end; flex-wrap: wrap; }

.miniField { display: flex; flex-direction: column; gap: 6px; }
.miniLabel { font-size: 14px; color: var(--muted); }

/* Dropzone */
.dropzone {
  border: 2px dashed color-mix(in srgb, var(--accent2) 60%, var(--border));
  border-radius: 18px;
  padding: 18px;
  background: color-mix(in srgb, var(--accent2) 10%, transparent);
  cursor: pointer;
  user-select: none;
  transition: transform 120ms ease, background 120ms ease, border-color 120ms ease;
}
.dropzone:hover { background: color-mix(in srgb, var(--accent2) 14%, transparent); }
.dropzone.dragging {
  border-color: color-mix(in srgb, var(--accent) 70%, var(--border));
  background: color-mix(in srgb, var(--accent) 12%, transparent);
  transform: scale(1.01);
}

.dzInner { display: grid; gap: 6px; }
.dzTitle { font-weight: 950; color: var(--text); font-size: 18px; }
.dzSub { font-size: 16px; color: var(--muted); }

.dzPreview {
  margin-top: 12px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.dzPreview img {
  width: 96px;
  height: 96px;
  border-radius: 16px;
  object-fit: cover;
  border: 1px solid var(--border);
}

.hiddenFile { display: none; }

/* Feed */
.feed { display: grid; gap: 12px; margin-top: 12px; }

.post {
  border: 1px solid var(--border);
  border-radius: 18px;
  overflow: hidden;
  display: grid;
  grid-template-columns: 140px 1fr;
  background: color-mix(in srgb, var(--bg) 18%, transparent);
  min-width: 0;
}

.postThumb {
  width: 140px;
  height: 140px;
  background: color-mix(in srgb, var(--accent2) 12%, transparent);
  display: grid;
  place-items: center;
  overflow: hidden;
  border-right: 1px solid var(--border);
}
.postThumb img { width: 100%; height: 100%; object-fit: cover; }
.postThumb.placeholder { color: var(--muted); font-size: 16px; }

.postBody { padding: 14px; min-width: 0; }
.postMeta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 8px; }
.postText { color: var(--text); font-size: 18px; line-height: 1.35; }
.price { font-weight: 950; color: color-mix(in srgb, var(--accent) 80%, var(--text)); }

/* Map */
.map { height: 320px; border-radius: 18px; border: 1px solid var(--border); overflow: hidden; margin-top: 10px; }

/* Hours */
.hours { margin-top: 12px; display: flex; flex-direction: column; gap: 12px; }
.hoursRow { display: grid; grid-template-columns: 72px 160px 1fr; gap: 12px; align-items: center; min-width: 0; }
.day { font-weight: 950; color: var(--text); }

.toggleBig { display: flex; align-items: center; gap: 10px; }
.toggle { display: flex; align-items: center; gap: 10px; color: var(--muted); font-size: 16px; }

.timeInputs { display: flex; align-items: center; gap: 10px; min-width: 0; }
.sep { color: var(--muted); }

/* Details summary */
.summary {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 10px;
  cursor: pointer;
  list-style: none;
  padding: 6px 0;
}
details > summary::-webkit-details-marker { display: none; }

pre {
  background: color-mix(in srgb, var(--bg) 22%, transparent);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 14px;
  overflow: auto;
  font-size: 13px;
  color: var(--text);
}

.disabled { opacity: 0.6; pointer-events: none; }

/* Footer login info */
.footer {
  margin-top: 18px;
  padding: 12px 2px;
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: center;
  border-top: 1px solid var(--border);
  background: color-mix(in srgb, var(--bg) 10%, transparent);
  border-radius: 14px;
}

/* Grid helpers */
.sortimentGrid.solo { grid-template-columns: 1fr; }
.sortimentBox.span2 { grid-column: 1 / -1; }

/* Mobile */
@media (max-width: 820px) {
  .page {
    font-size: 16px;
    padding: 14px 12px;
  }

  h1 { font-size: 22px; }
  h2 { font-size: 18px; }
  h3 { font-size: 16px; }

  .header { align-items: flex-start; }
  .headerRight { gap: 10px; }

  .card { padding: 14px; }

  .pill { font-size: 12px; padding: 6px 10px; }

  .sortimentGrid { grid-template-columns: 1fr; }

  .formGrid { grid-template-columns: 1fr; }
  .field.wide { grid-column: span 1; }

  input, select {
    font-size: 16px;
    padding: 12px 12px;
    border-radius: 14px;
  }

  .btn {
    font-size: 16px;
    padding: 12px 14px;
    border-radius: 14px;
  }

  .btn.tiny { font-size: 14px; padding: 10px 12px; }

  .iconBtn {
    width: 44px;
    height: 44px;
    font-size: 22px;
    border-radius: 14px;
  }

  .hoursRow { grid-template-columns: 72px 1fr; }
  .timeInputs { grid-column: span 2; }

  .post { grid-template-columns: 1fr; }
  .postThumb {
    width: 100%;
    height: 200px;
    border-right: none;
    border-bottom: 1px solid var(--border);
  }

  .dzPreview img { width: 72px; height: 72px; }

  .addRow { grid-template-columns: 1fr; }
  .addBtn { width: 100%; }
}

/* Small transition */
.fade-enter-active, .fade-leave-active { transition: opacity 160ms ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
