<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon, ClockIcon } from '@heroicons/vue/24/outline'
import { onMounted, nextTick } from 'vue'
import hljs from 'highlight.js'
import 'highlight.js/styles/monokai.css'
import { useI18n } from '@/composables/useI18n'
import SiteNavbar from '@/components/SiteNavbar.vue'
import Comments from '@/components/Comments.vue'

const { t, locale } = useI18n()

interface Post {
  id: number
  title: string
  slug: string
  excerpt: string
  content: string
  author?: string
  image?: string
  read_time: number
  tags: string[]
  published_at: string
}

defineProps<{
  post: Post
}>()

onMounted(() => {
  nextTick(() => {
    document.querySelectorAll('pre code').forEach((block) => {
      hljs.highlightElement(block as HTMLElement)
    })
  })
})
</script>

<template>
  <Head :title="post.title" />

  <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    <SiteNavbar />

    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/50 pt-16">
      <div class="container mx-auto max-w-4xl px-4 py-6">
        <Link
          href="/posts"
          class="inline-flex items-center gap-2 text-gray-400 transition-colors hover:text-white"
        >
          <ArrowLeftIcon class="h-5 w-5" />
          <span>{{ t('back') }}</span>
        </Link>
      </div>
    </header>

    <!-- Post Content -->
    <article class="py-12">
      <div class="container mx-auto max-w-4xl px-4">
        <!-- Featured Image -->
        <div v-if="post.image" class="mb-8 overflow-hidden rounded-2xl">
          <img :src="post.image" :alt="post.title" class="h-auto w-full" />
        </div>

        <!-- Title -->
        <h1 class="mb-6 font-heading text-4xl font-semibold leading-tight tracking-tight text-white md:text-5xl">
          {{ post.title }}
        </h1>

        <!-- Meta Info -->
        <div class="mb-8 flex flex-wrap items-center gap-4 text-sm text-gray-400">
          <div v-if="post.author" class="flex items-center gap-2">
            <span class="font-medium text-white">{{ post.author }}</span>
          </div>
          <div v-if="post.read_time" class="flex items-center gap-1">
            <ClockIcon class="h-4 w-4" />
            <span>{{ post.read_time }} {{ t('minRead') }}</span>
          </div>
          <div v-if="post.published_at">
            {{ new Date(post.published_at).toLocaleDateString(locale === 'en' ? 'en-US' : 'pt-BR', { year: 'numeric', month: 'long', day: 'numeric' }) }}
          </div>
        </div>

        <!-- Tags -->
        <div class="mb-8 flex flex-wrap gap-2">
          <span
            v-for="tag in post.tags"
            :key="tag"
            class="rounded-full bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-400"
          >
            {{ tag }}
          </span>
        </div>

        <!-- Excerpt -->
        <div class="mb-8 border-l-4 border-blue-500 bg-slate-800/50 p-6">
          <p class="text-lg italic text-gray-300">{{ post.excerpt }}</p>
        </div>

        <!-- Content -->
        <div
          class="prose prose-invert prose-lg max-w-none prose-headings:font-heading prose-headings:font-semibold prose-headings:tracking-tight prose-headings:text-white prose-h2:mt-14 prose-h2:mb-5 prose-h2:text-3xl prose-h3:mt-10 prose-h3:mb-4 prose-h3:text-2xl prose-p:my-6 prose-p:leading-8 prose-p:text-gray-300 prose-a:text-blue-400 prose-strong:text-white prose-code:rounded prose-code:bg-slate-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-blue-400 prose-pre:overflow-x-auto prose-pre:rounded-xl prose-pre:bg-slate-900 prose-pre:p-0 prose-img:my-8 prose-img:mx-auto prose-img:rounded-xl prose-li:my-1.5 prose-ul:my-6 prose-ol:my-6 prose-hr:my-12 prose-hr:border-slate-800 prose-table:my-8"
          v-html="post.content"
        ></div>

        <!-- Comments (Giscus) -->
        <Comments :slug="post.slug" />

        <!-- Back Button -->
        <div class="mt-12 text-center">
          <Link
            href="/"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-3 font-semibold text-white transition-all hover:from-blue-700 hover:to-purple-700"
          >
            <ArrowLeftIcon class="h-5 w-5" />
            <span>{{ t('backHome') }}</span>
          </Link>
        </div>
      </div>
    </article>
  </div>
</template>
