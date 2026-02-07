<!-- src/pages/CustomerView.vue -->
<template>
  <section class="card">
    <div class="splitHead">
      <div>
        <h2>Kundenansicht</h2>
        <p class="hint">Klick eine Flagge auf der Karte, um einen Betrieb zu öffnen.</p>
      </div>

      <div class="pillRow">
        <span class="pill">{{ shops.length }} Betriebe</span>
        <span class="pill" v-if="activeShop">Aktiv: {{ activeShop.account.shopName }}</span>
      </div>
    </div>

    <div class="mapWrap" style="margin-top: 10px;">
      <div ref="mapEl" class="map"></div>

      <div class="row" style="margin-top: 12px;">
        <button class="btn" type="button" @click="fitAll" :disabled="!mapReady">
          Alle anzeigen
        </button>
        <button class="btn" type="button" @click="centerOnActive" :disabled="!mapReady || !activeShop">
          Zu aktivem Betrieb
        </button>
      </div>
    </div>
  </section>

  <section class="card" v-if="activeShop">
    <div class="splitHead">
      <div>
        <h2>{{ activeShop.account.shopName }}</h2>
        <p class="hint">{{ activeShop.profile.type }} • {{ activeShop.profile.address }}</p>
      </div>

      <div class="pillRow">
        <span class="pill">{{ activeShop.posts.length }} Beiträge</span>
        <span class="pill" v-if="openNow !== null">
          {{ openNow ? "Jetzt offen" : "Jetzt geschlossen" }}
        </span>
      </div>
    </div>

    <div class="row" style="margin-top: 10px;">
      <span class="pill" v-if="activeShop.profile.phone">☎ {{ activeShop.profile.phone }}</span>
      <span class="pill" v-if="activeShop.profile.link">{{ activeShop.profile.link }}</span>
    </div>

    <div class="feed" style="margin-top: 14px;" v-if="activeShop.posts.length">
      <article class="post" v-for="p in activeShop.posts" :key="p.id">
        <div class="postThumb" v-if="p.photos?.length">
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
        </div>
      </article>
    </div>

    <div class="empty" v-else style="margin-top: 14px;">
      Noch keine Beiträge.
    </div>
  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { mockShops } from "../services/mockData";

const shops = mockShops;

const activeShopId = ref(shops[0]?.id ?? 1);
const activeShop = computed(() => shops.find((s) => s.id === activeShopId.value) || null);

// ---- Leaflet via CDN injection (same approach as AdminDashboard) ----
const mapEl = ref(null);
let map = null;
let markers = [];
const mapReady = ref(false);

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

function makeFlagIcon(L) {
  const size = [34, 34];
  return L.divIcon({
    className: "flagIcon",
    iconSize: size,
    iconAnchor: [17, 34],
    popupAnchor: [0, -30],
    html: `
      <div class="flagPin">
        <div class="flagPole"></div>
        <div class="flagCloth"></div>
      </div>
    `,
  });
}

function initMap() {
  if (!mapEl.value) return;
  const L = window.L;
  if (!L) return;

  const first = shops[0];
  const startLat = Number(first?.profile?.lat) || 47.48;
  const startLon = Number(first?.profile?.lon) || 8.20;

  map = L.map(mapEl.value, { zoomControl: true }).setView([startLat, startLon], 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: "© OpenStreetMap",
  }).addTo(map);

  const flagIcon = makeFlagIcon(L);

  markers = shops.map((s) => {
    const lat = Number(s.profile.lat);
    const lon = Number(s.profile.lon);

    const m = L.marker([lat, lon], { icon: flagIcon }).addTo(map);
    m.on("click", () => {
      activeShopId.value = s.id;
      centerOnActive();
    });
    m.bindTooltip(s.account.shopName, { direction: "top", offset: [0, -10] });
    return m;
  });

  mapReady.value = true;
  fitAll();
}

function fitAll() {
  if (!map || !markers.length) return;
  const L = window.L;
  const group = L.featureGroup(markers);
  map.fitBounds(group.getBounds().pad(0.25));
}

function centerOnActive() {
  if (!map || !activeShop.value) return;
  const lat = Number(activeShop.value.profile.lat);
  const lon = Number(activeShop.value.profile.lon);
  map.setView([lat, lon], Math.max(map.getZoom(), 14));
}

// ---- "open now" (simple mock) ----
const openNow = computed(() => {
  const s = activeShop.value;
  if (!s) return null;

  if (s.profile.vacation?.enabled) return false;

  const dayKey = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"][new Date().getDay()];
  const h = s.profile.hours?.[dayKey];
  if (!h || h.closed) return false;

  // compare current time to from/to (hh:mm)
  const now = new Date();
  const nowMin = now.getHours() * 60 + now.getMinutes();

  const [fh, fm] = (h.from || "00:00").split(":").map(Number);
  const [th, tm] = (h.to || "00:00").split(":").map(Number);
  const fromMin = fh * 60 + fm;
  const toMin = th * 60 + tm;

  if (!h.from || !h.to) return false;
  return nowMin >= fromMin && nowMin <= toMin;
});

// ---- labels / formatting ----
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

onMounted(async () => {
  await injectLeaflet();
  initMap();
});

onBeforeUnmount(() => {
  try {
    if (map) map.remove();
  } catch {}
  map = null;
  markers = [];
  mapReady.value = false;
});
</script>

<style scoped>
/* flag marker visuals */
:global(.flagIcon) {
  background: transparent;
  border: none;
}

:global(.flagPin) {
  position: relative;
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  filter: drop-shadow(0 6px 10px rgba(0,0,0,0.25));
}

:global(.flagPole) {
  position: absolute;
  left: 15px;
  top: 6px;
  width: 4px;
  height: 22px;
  border-radius: 999px;
  background: color-mix(in srgb, var(--text) 70%, transparent);
}

:global(.flagCloth) {
  position: absolute;
  left: 18px;
  top: 7px;
  width: 14px;
  height: 10px;
  border-radius: 4px;
  background: var(--accent2);
  box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--text) 20%, transparent);
}

/* reuse existing global styles for .map, .feed, .post, etc. */
</style>
