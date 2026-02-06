<template>
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
            <input type="checkbox" v-model="hours[d.key].closed" />
            <span>Geschlossen</span>
          </label>

          <div class="timeInputs" :class="{ disabled: hours[d.key].closed }">
            <input
              v-model="hours[d.key].from"
              type="time"
              :disabled="hours[d.key].closed"
            />
            <span class="sep">–</span>
            <input
              v-model="hours[d.key].to"
              type="time"
              :disabled="hours[d.key].closed"
            />
          </div>
        </div>
      </div>

      <div class="row">
        <button class="btn primary" type="button" @click="$emit('save')">
          Öffnungszeiten speichern
        </button>
      </div>
    </details>
  </section>
</template>

<script setup>
defineProps({
  days: { type: Array, required: true },
  hours: { type: Object, required: true },
});

defineEmits(["save"]);
</script>
