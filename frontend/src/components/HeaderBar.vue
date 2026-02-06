<!-- src/components/HeaderBar.vue -->
<template>
  <header class="header">
    <h1 class="title">{{ shopName }}</h1>

    <div class="headerRight">
      <button class="linkBtn" type="button" @click="$emit('toggleTheme')">
        {{ theme === "light" ? "Dark" : "Light" }}
      </button>
    </div>
  </header>
</template>

<script setup>
defineProps({
  shopName: { type: String, required: true },
  displayName: { type: String, required: true }, // aktuell nicht angezeigt, aber bewusst als prop
  theme: { type: String, required: true },
  isSaved: { type: Boolean, required: true },
});

defineEmits(["toggleTheme"]);
</script>

<style scoped>
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 14px;

  /* IMPORTANT: header should be on a "card" surface, not bg */
  padding: 12px 14px;
  border-radius: 16px;
  border: 1px solid var(--border);
  background: color-mix(in srgb, var(--card) 96%, transparent);
  box-shadow: var(--shadow);

  min-width: 0;
}

.title {
  font-weight: 950;
  letter-spacing: -0.01em;
  color: var(--text);
  text-shadow: 0 1px 0 rgba(0,0,0,0.08);
  margin: 0;
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

/* theme button */
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

/* Mobile */
@media (max-width: 820px) {
  .header { align-items: flex-start; }
  .headerRight { gap: 10px; }
}
</style>
