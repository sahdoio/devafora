<script setup lang="ts">
import { ClockIcon } from '@heroicons/vue/24/outline'
import { Link } from '@inertiajs/vue3'

interface Post {
  id: number
  title: string
  slug: string
  excerpt: string
  image?: string
  readTime: string
  tags: string[]
  publishedAt: string
}

defineProps<{
  post: Post
}>()
</script>

<template>
  <Link
    :href="`/posts/${post.slug}`"
    class="group block overflow-hidden rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm transition-all duration-300 hover:border-blue-500/30 hover:bg-white/10 hover:shadow-xl hover:shadow-blue-500/5"
  >
    <!-- Image -->
    <div v-if="post.image" class="aspect-video w-full overflow-hidden">
      <img
        :src="post.image"
        :alt="post.title"
        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
      />
    </div>
    <div v-else class="aspect-video w-full bg-gradient-to-br from-blue-600/80 to-purple-600/80"></div>

    <!-- Content -->
    <div class="p-6">
      <h3 class="mb-2 text-xl font-bold text-white line-clamp-2 transition-colors group-hover:text-blue-400">
        {{ post.title }}
      </h3>
      <p class="mb-4 text-sm text-gray-400 line-clamp-2">
        {{ post.excerpt }}
      </p>

      <!-- Tags -->
      <div class="mb-4 flex flex-wrap gap-2">
        <span
          v-for="tag in post.tags"
          :key="tag"
          class="rounded-full bg-blue-500/10 px-3 py-1 text-xs font-medium text-blue-400"
        >
          {{ tag }}
        </span>
      </div>

      <!-- Meta -->
      <div class="flex items-center gap-4 text-sm text-gray-500">
        <div class="flex items-center gap-1">
          <ClockIcon class="h-4 w-4" />
          <span>{{ post.readTime }}</span>
        </div>
        <span>{{ post.publishedAt }}</span>
      </div>
    </div>
  </Link>
</template>
