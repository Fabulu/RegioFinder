<template>
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
            <button class="btn danger" type="button" @click="$emit('remove', p.id)">
              Löschen
            </button>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  posts: { type: Array, required: true },
  kindLabel: { type: Function, required: true },
  formatWhen: { type: Function, required: true },
  formatUntil: { type: Function, required: true },
});
defineEmits(["remove"]);
</script>
