<template>
  <section class="card">
    <div class="splitHead">
      <div>
        <h2>Ferien</h2>
      </div>

      <label class="toggleBig">
        <input type="checkbox" v-model="vacation.enabled" />
        <span><strong>Aktiv</strong></span>
      </label>
    </div>

    <div class="formGrid" :class="{ disabled: !vacation.enabled }">
      <label class="field">
        <span class="label">Von</span>
        <input v-model="vacation.from" type="date" :disabled="!vacation.enabled" />
      </label>

      <label class="field">
        <span class="label">Bis</span>
        <input v-model="vacation.to" type="date" :disabled="!vacation.enabled" />
      </label>

      <label class="field wide">
        <span class="label">Notiz (optional)</span>
        <input
          v-model="vacation.note"
          type="text"
          placeholder="z.B. Ab 03.03. wieder offen"
          :disabled="!vacation.enabled"
        />
      </label>
    </div>

    <div class="row">
      <button class="btn primary" type="button" @click="$emit('save')">
        Ferien speichern
      </button>
    </div>
  </section>
</template>

<script setup>
defineProps({
  vacation: { type: Object, required: true },
});

defineEmits(["save"]);
</script>
