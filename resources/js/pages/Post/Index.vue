<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import PostCard from '@/components/PostCard.vue'
import SiteNavbar from '@/components/SiteNavbar.vue'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

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

defineProps<{
  posts: Post[]
}>()
</script>

<template>
  <Head title="Posts - DevAfora" />

  <div class="min-h-screen bg-surface-primary font-sans text-text-primary">
    <SiteNavbar />

    <!-- Back to home -->
    <div class="mx-auto max-w-6xl px-4 pt-24">
      <Link
        href="/"
        class="inline-flex items-center gap-2 text-sm text-text-muted transition-colors hover:text-brand"
      >
        <ArrowLeftIcon class="h-5 w-5" />
        <span>{{ t('backHome') }}</span>
      </Link>
    </div>

    <!-- Page Title -->
    <section class="px-4 pb-12 pt-8">
      <div class="mx-auto max-w-6xl text-center">
        <h1 class="mb-4 font-heading text-4xl font-semibold tracking-tight text-white md:text-5xl">
          {{ t('allPosts') }}
        </h1>
        <p class="text-lg text-gray-400">
          {{ t('allPostsSubtitle') }}
        </p>
      </div>
    </section>

    <!-- Posts Grid -->
    <section class="px-4 pb-16">
      <div class="mx-auto max-w-6xl">
        <div v-if="posts && posts.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <PostCard v-for="post in posts" :key="post.slug" :post="post" />
        </div>

        <div v-else class="rounded-xl border border-white/5 bg-white/5 p-12 text-center">
          <p class="text-lg text-gray-400">
            {{ t('noPosts') }}
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 px-4 py-8">
      <div class="mx-auto max-w-6xl text-center text-sm text-gray-500">
        <p>© {{ new Date().getFullYear() }} DevAfora. {{ t('rights') }}</p>
      </div>
    </footer>
  </div>
</template>
