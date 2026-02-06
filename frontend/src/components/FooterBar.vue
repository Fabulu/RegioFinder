<!-- src/components/FooterBar.vue -->
<template>
  <footer class="footer">
    <span class="muted">Eingeloggt als</span>
    <strong class="name">{{ displayName }}</strong>
  </footer>
</template>

<script setup>
defineProps({
  displayName: { type: String, required: true },
});
</script>

<style scoped>
.muted { color: var(--muted); }
.name { color: var(--text); }

/* Footer stays readable in both themes */
.footer {
  margin-top: 18px;
  padding: 12px 10px;
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: center;

  border-top: 1px solid var(--border);

  /* use card-like surface (not bg mix) to avoid "too dark" look */
  background: color-mix(in srgb, var(--card) 96%, transparent);

  border-radius: 14px;
}
</style>
