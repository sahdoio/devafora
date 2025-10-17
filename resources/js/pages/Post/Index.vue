<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PostCard from '@/components/PostCard.vue'

interface Post {
  id: number
  title: string
  slug: string
  excerpt: string
  image?: string
  read_time: number
  tags: string[]
  published_at: string
}

const props = defineProps<{
  posts: Post[]
}>()
</script>

<template>
  <Head title="Posts - DevAfora" />

  <div class="min-h-screen bg-gradient-to-b from-[#0a1628] via-[#0f2847] to-[#1e3a8a]">
    <!-- Header -->
    <header class="border-b border-white/5 px-4 py-8">
      <div class="mx-auto max-w-6xl">
        <div class="flex items-center justify-between">
          <a href="/" class="flex items-center gap-3">
            <img
              src="/images/logo.png"
              alt="DevAfora"
              class="h-12 w-auto"
            />
          </a>
          <a
            href="/"
            class="text-sm text-gray-400 transition-colors hover:text-blue-400"
          >
            ← Voltar
          </a>
        </div>
      </div>
    </header>

    <!-- Page Title -->
    <section class="px-4 py-12">
      <div class="mx-auto max-w-6xl text-center">
        <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl">
          Todos os Posts
        </h1>
        <p class="text-lg text-gray-400">
          Explore nossos conteúdos sobre desenvolvimento web
        </p>
      </div>
    </section>

    <!-- Posts Grid -->
    <section class="px-4 pb-16">
      <div class="mx-auto max-w-6xl">
        <div v-if="posts && posts.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <PostCard v-for="post in posts" :key="post.id" :post="post" />
        </div>

        <div v-else class="rounded-xl border border-white/5 bg-white/5 p-12 text-center">
          <p class="text-lg text-gray-400">
            Nenhum post publicado ainda.
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 px-4 py-8">
      <div class="mx-auto max-w-6xl text-center text-sm text-gray-500">
        <p>© {{ new Date().getFullYear() }} DevAfora. Todos os direitos reservados.</p>
      </div>
    </footer>
  </div>
</template>
