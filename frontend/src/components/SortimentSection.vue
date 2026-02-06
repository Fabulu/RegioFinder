<template>
  <section class="card">
    <div class="splitHead">
      <div class="splitLeft">
        <h2>Sortiment</h2>

        <!-- advanced toggle next to title -->
        <button
          class="linkBtn tinyLink"
          type="button"
          @click="$emit('update:showAdvanced', !showAdvanced)"
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
            <button v-if="!baseOpen" class="btn tiny" type="button" @click="$emit('update:baseOpen', true)">
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

              <button class="iconBtn danger" type="button" @click="$emit('removeItem', item.id)" title="Entfernen">
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
            <button class="btn tiny" type="button" @click="$emit('update:baseOpen', false)">
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

              <button class="iconBtn danger" type="button" @click="$emit('removeItem', item.id)" title="Entfernen">
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
          v-model="draft.name"
          type="text"
          placeholder="Neues Produkt…"
        />
        <button class="btn primary addBtn" type="button" @click="onAdd">
          + Hinzufügen
        </button>
      </div>

      <div class="addAdvanced" v-if="showAdvanced">
        <label class="miniField">
          <span class="miniLabel">Saisonal bis (optional)</span>
          <input v-model="draft.expiresOn" type="date" />
        </label>
      </div>

      <span class="error" v-if="error">{{ error }}</span>
    </div>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from "vue";

const props = defineProps({
  sortiment: { type: Array, required: true },
  showAdvanced: { type: Boolean, required: true },
  baseOpen: { type: Boolean, required: true },
  formatDate: { type: Function, required: true },
  isExpired: { type: Function, required: true },
});

const emit = defineEmits(["update:showAdvanced", "update:baseOpen", "addItem", "removeItem"]);


const draft = reactive({ name: "", expiresOn: "" });
const error = ref("");

const basissortiment = computed(() => props.sortiment.filter((x) => !x.expiresOn));
const saisonangebot = computed(() => props.sortiment.filter((x) => !!x.expiresOn));

const sortimentExpiredCount = computed(() =>
  props.sortiment.filter((x) => props.isExpired(x.expiresOn)).length
);

function normalizeName(s) {
  return (s ?? "").trim().replace(/\s+/g, " ");
}

function onAdd() {
  error.value = "";
  const name = normalizeName(draft.name);

  if (!name) {
    error.value = "Bitte Produktname eingeben.";
    return;
  }

  const exists = props.sortiment.some((x) => x.name.toLowerCase() === name.toLowerCase());
  if (exists) {
    error.value = "Dieses Produkt ist bereits im Sortiment.";
    return;
  }

  // emit full payload to parent (parent owns the array)
  const payload = {
    name,
    expiresOn: draft.expiresOn || "",
  };

  // eslint-disable-next-line vue/require-explicit-emits
  // (emit is provided by defineEmits; this comment is just to silence some linters)
  // We can emit directly:
  // @ts-ignore
  emit("addItem", payload);

  draft.name = "";
  draft.expiresOn = "";
}
</script>
