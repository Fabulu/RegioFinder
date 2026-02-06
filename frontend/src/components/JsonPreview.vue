<template>
  <section class="card">
    <details class="debug">
      <summary class="summary">
        <span>JSON-Vorschau (Mock)</span>
        <span class="muted">– nur für Dev / Demo</span>
      </summary>
      <pre>{{ value }}</pre>
    </details>
  </section>
</template>

<script setup>
defineProps({
  value: { type: String, required: true },
});
</script>

<style scoped>
.muted { color: var(--muted); }

pre {
  background: color-mix(in srgb, var(--bg) 22%, transparent);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 14px;
  overflow: auto;
  font-size: 13px;
  color: var(--text);
}

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
</style>
