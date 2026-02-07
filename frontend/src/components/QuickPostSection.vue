<template>
  <section class="card hero">
    <div class="heroTop">
      <h2>Heute posten</h2>

    </div>

    <div class="formGrid">
      <!-- KIND -->
      <label class="field">
        <span class="label">Beitrag-Typ</span>
        <select v-model="local.kind">
          <option value="availability">Heute verfügbar</option>
          <option value="new">Neu im Sortiment</option>
          <option value="promo">Aktion / Hinweis</option>
        </select>
      </label>

      <!-- PRODUCT -->
      <label class="field">
        <span class="label">Produkt</span>
        <select v-model="local.productId">
          <option value="">Keins (Freitext)</option>
          <option
            v-for="item in sortiment"
            :key="item.id"
            :value="item.id"
          >
            {{ item.name }}
            <template v-if="item.expiresOn">
              (bis {{ formatDate(item.expiresOn) }})
            </template>
          </option>
        </select>
      </label>

      <!-- TEXT -->
      <label class="field wide">
        <span class="label">Text</span>
        <input
          v-model="local.text"
          type="text"
          placeholder="z.B. Heute: Erdbeeren frisch vom Feld"
          :disabled="!!local.productId"
        />
      </label>

      <!-- PRICE -->
      <label class="field">
        <span class="label">Preis (optional)</span>
        <input v-model="local.price" type="text" placeholder="CHF 6.50" />
      </label>

      <!-- UNTIL -->
      <label class="field">
        <span class="label">Gültig bis</span>
        <input v-model="local.until" type="datetime-local" />
      </label>

      <!-- DROPZONE -->
      <div class="field wide">
        <span class="label">Foto(s)</span>

        <div
          class="dropzone"
          :class="{ dragging }"
          @click="openFilePicker"
          @dragenter.prevent="dragging = true"
          @dragover.prevent="dragging = true"
          @dragleave.prevent="dragging = false"
          @drop.prevent="onDrop"
          role="button"
        >
          <div class="dzInner" v-if="!previews.length">
            <div class="dzTitle">+ Foto hinzufügen</div>
            <div class="dzSub">Klicken oder Bild hierher ziehen</div>
          </div>

          <div class="dzPreview" v-else>
            <img v-for="(p, i) in previews" :key="i" :src="p" />
          </div>
        </div>

        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          multiple
          class="hiddenFile"
          @change="onFiles"
        />
      </div>
    </div>

    <div class="row">
      <button class="btn primary" type="button" @click="publish">
        Beitrag veröffentlichen
      </button>
      <span class="error" v-if="error">{{ error }}</span>
    </div>
  </section>
</template>

<script setup>
import { computed, reactive, ref, watch } from "vue";

const props = defineProps({
  sortiment: { type: Array, required: true },
  formatDate: { type: Function, required: true },
  isExpired: { type: Function, required: true },
});

const emit = defineEmits(["publish"]);

const local = reactive({
  kind: "availability",
  productId: "",
  text: "",
  price: "",
  until: "",
  files: [],
});

const error = ref("");
const previews = ref([]);
const dragging = ref(false);
const fileInput = ref(null);

const selectedProduct = computed(
  () => props.sortiment.find((x) => x.id === local.productId) || null
);

watch(
  () => local.productId,
  (val) => {
    if (val) local.text = "";
  }
);

function normalize(s) {
  return (s ?? "").trim().replace(/\s+/g, " ");
}

function openFilePicker() {
  fileInput.value?.click();
}

function onFiles(e) {
  applyFiles(Array.from(e.target.files ?? []));
}

function onDrop(e) {
  dragging.value = false;
  const files = Array.from(e.dataTransfer.files).filter((f) =>
    f.type.startsWith("image/")
  );
  applyFiles(files);
}

function applyFiles(files) {
  previews.value.forEach((u) => URL.revokeObjectURL(u));
  local.files = files;
  previews.value = files.map((f) => URL.createObjectURL(f));
}

function clearImages() {
  previews.value.forEach((u) => URL.revokeObjectURL(u));
  previews.value = [];
  local.files = [];
  if (fileInput.value) fileInput.value.value = "";
}

function publish() {
  error.value = "";

  const hasProduct = !!local.productId;
  const text = normalize(local.text);

  if (!hasProduct && !text) {
    error.value = "Produkt wählen oder Text eingeben.";
    return;
  }

  emit("publish", {
    kind: local.kind,
    productId: local.productId,
    text,
    price: normalize(local.price),
    until: local.until,
    files: local.files,
  });

  local.productId = "";
  local.text = "";
  local.price = "";
  local.until = "";
  clearImages();
}
</script>
