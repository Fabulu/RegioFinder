<template>
  <section class="card">
    <div class="splitHead">
      <div class="splitLeft">
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

          <div class="boxHeadRight">
            <button
              v-if="!baseOpen"
              class="btn tiny"
              type="button"
              @click="emit('update:baseOpen', true)"
            >
              Basissortiment anzeigen
            </button>
          </div>
        </div>

        <div v-if="saisonangebot.length" class="sortimentList">
          <div class="sortimentItem" v-for="item in saisonangebot" :key="item.id">
            <div class="sortimentMain">
              <strong class="sortimentName">{{ item.name }}</strong>
            </div>

            <div class="sortimentActions">
              <button
                class="iconBtn danger"
                type="button"
                @click="emit('removeItem', item.id)"
                title="Entfernen"
              >
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

          <div class="boxHeadRight">
            <button class="btn tiny" type="button" @click="emit('update:baseOpen', false)">
              Basissortiment ausblenden
            </button>
          </div>
        </div>

        <div v-if="basissortiment.length" class="sortimentList">
          <div class="sortimentItem" v-for="item in basissortiment" :key="item.id">
            <div class="sortimentMain">
              <strong class="sortimentName">{{ item.name }}</strong>
            </div>

            <div class="sortimentActions">
              <button
                class="iconBtn danger"
                type="button"
                @click="emit('removeItem', item.id)"
                title="Entfernen"
              >
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
    <form class="sortimentAdd" @submit.prevent="onAdd">
      <div class="addRow">
        <input
          class="addName"
          v-model="draft.name"
          type="text"
          placeholder="Neues Produkt…"
        />
        <button class="btn primary addBtn" type="submit">
          + Hinzufügen
        </button>
      </div>

      <span class="error" v-if="error">{{ error }}</span>
    </form>
  </section>
</template>

<script setup>
import { computed, reactive, ref } from "vue";

const props = defineProps({
  sortiment: { type: Array, required: true },
  showAdvanced: { type: Boolean, required: true },
  baseOpen: { type: Boolean, required: true },
});

const emit = defineEmits(["update:showAdvanced", "update:baseOpen", "addItem", "removeItem"]);

const draft = reactive({ name: "" });
const error = ref("");

const basissortiment = computed(() => props.sortiment.filter((x) => !!x.isBase));
const saisonangebot = computed(() => props.sortiment.filter((x) => !x.isBase));

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

  const exists = props.sortiment.some((x) => (x.name ?? "").toLowerCase() === name.toLowerCase());
  if (exists) {
    error.value = "Dieses Produkt ist bereits im Sortiment.";
    return;
  }

  emit("addItem", { name });

  draft.name = "";
}
</script>
