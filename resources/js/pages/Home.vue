<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import NewsletterForm from '@/components/NewsletterForm.vue'
import PostCard from '@/components/PostCard.vue'
import SiteNavbar from '@/components/SiteNavbar.vue'
import { useI18n } from '@/composables/useI18n'

const { t, locale } = useI18n()

interface Profile {
  id: number
  name: string
  bio: string
  photo?: string | null
}

interface Post {
  id: string
  title: string
  slug: string
  excerpt: string
  image?: string
  read_time: number
  tags: string[]
  published_at: string
}

interface Video {
  videoId: string
  title: string
  url: string
  thumbnail: string
  published_at: string
}

const props = defineProps<{
  profile: Profile | null
  posts: Post[]
  videos: Video[]
}>()

const socials = [
  { label: 'YouTube', url: 'https://www.youtube.com/@devafora' },
  { label: 'Instagram', url: 'https://www.instagram.com/lucassahdo' },
  { label: 'TikTok', url: 'https://www.tiktok.com/@lucassahdo' },
  { label: 'GitHub', url: 'https://github.com/sahdoio' },
  { label: 'LinkedIn', url: 'https://www.linkedin.com/in/lucassahdo/' },
  { label: 'X', url: 'https://x.com/sahdoio' },
]

const visibleVideos = ref(6)
const shownVideos = computed(() => props.videos.slice(0, visibleVideos.value))
const hasMoreVideos = computed(() => visibleVideos.value < props.videos.length)

function loadMoreVideos() {
  visibleVideos.value += 6
}

function formatDate(date: string) {
  const tag = locale.value === 'en' ? 'en-US' : 'pt-BR'
  return new Date(date).toLocaleDateString(tag, { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <Head title="DevAfora — Lucas Sahdo, Engenheiro de Software" />

  <div class="min-h-screen bg-surface-primary font-sans text-text-primary">
    <SiteNavbar />

    <div class="mx-auto flex max-w-[1100px] flex-col items-center px-6 pb-12 pt-28">

      <!-- HERO / SOBRE -->
      <section class="flex flex-col items-center text-center">
        <img
          src="/images/profile.jpeg"
          alt="Lucas Sahdo"
          class="mb-5 h-28 w-28 rounded-full border-2 border-brand/40 object-cover shadow-lg shadow-brand/10 sm:h-32 sm:w-32"
        />

        <h1 class="font-heading text-3xl font-semibold tracking-tight md:text-4xl">
          Lucas Sahdo
        </h1>
        <p class="mt-2 text-base font-medium text-brand md:text-lg">
          {{ t('heroTitle') }}
        </p>
        <p class="mt-1 text-sm text-text-muted">
          {{ t('heroSubtitle') }}
        </p>

        <p
          class="mt-5 max-w-2xl text-[15px] leading-relaxed text-text-secondary"
          v-html="t('heroBio')"
        ></p>

        <div class="mt-5 rounded-full bg-green-900/20 px-4 py-1 text-sm text-green-400">
          {{ t('heroAvailable') }}
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-x-5 gap-y-2 text-[15px] text-text-secondary">
          <a
            v-for="social in socials"
            :key="social.label"
            :href="social.url"
            target="_blank"
            rel="noopener"
            class="transition-colors duration-150 hover:text-text-primary"
          >
            {{ social.label }}
          </a>
        </div>
      </section>

      <!-- NEWSLETTER -->
      <section class="mt-10 w-full max-w-2xl">
        <div class="rounded-xl border border-border-subtle bg-surface-secondary px-5 py-4 text-center">
          <h2 class="font-heading text-lg font-semibold tracking-tight">
            {{ t('newsletterTitle') }}
          </h2>
          <p class="mb-4 mt-1 text-sm text-text-muted">
            {{ t('newsletterText') }}
          </p>
          <NewsletterForm />
        </div>
      </section>

      <!-- POSTS -->
      <section class="mt-16 w-full">
        <div class="mb-6 flex items-end justify-between">
          <h2 class="font-heading text-2xl font-semibold tracking-tight">{{ t('latestPosts') }}</h2>
          <Link href="/posts" class="text-sm text-text-muted transition-colors hover:text-brand">
            {{ t('seeAll') }} &rarr;
          </Link>
        </div>

        <div v-if="posts.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <PostCard v-for="post in posts" :key="post.slug" :post="post" />
        </div>
        <div v-else class="rounded-xl border border-border-subtle bg-surface-secondary p-10 text-center text-text-muted">
          {{ t('noPosts') }}
        </div>
      </section>

      <!-- VÍDEOS DO YOUTUBE -->
      <section class="mt-16 w-full">
        <div class="mb-6 flex items-end justify-between">
          <h2 class="font-heading text-2xl font-semibold tracking-tight">{{ t('videosTitle') }}</h2>
          <a
            href="https://www.youtube.com/@devafora"
            target="_blank"
            rel="noopener"
            class="text-sm text-text-muted transition-colors hover:text-brand"
          >
            @devafora &rarr;
          </a>
        </div>

        <div v-if="videos.length > 0">
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <a
              v-for="video in shownVideos"
              :key="video.videoId"
              :href="video.url"
              target="_blank"
              rel="noopener"
              class="group flex flex-col overflow-hidden rounded-xl border border-border-subtle bg-surface-secondary transition-all duration-150 hover:border-brand/40 hover:bg-surface-tertiary"
            >
              <div class="relative aspect-video overflow-hidden">
                <img
                  :src="video.thumbnail"
                  :alt="video.title"
                  loading="lazy"
                  class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                />
                <div class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition-opacity group-hover:opacity-100">
                  <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand text-surface-primary">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>
                  </span>
                </div>
              </div>
              <div class="flex flex-1 flex-col p-4">
                <h3 class="mb-2 line-clamp-2 font-heading text-[16px] font-semibold leading-snug tracking-tight text-text-primary">
                  {{ video.title }}
                </h3>
                <span class="mt-auto text-[13px] text-text-muted">{{ formatDate(video.published_at) }}</span>
              </div>
            </a>
          </div>

          <div v-if="hasMoreVideos" class="mt-6 flex justify-center">
            <button
              @click="loadMoreVideos"
              class="rounded-lg bg-brand/10 px-6 py-2 text-sm font-medium text-brand transition-all duration-150 hover:bg-brand/20"
            >
              {{ t('loadMore') }}
            </button>
          </div>
        </div>
        <div v-else class="rounded-xl border border-border-subtle bg-surface-secondary p-10 text-center text-text-muted">
          {{ t('videosUnavailable') }}
          <a href="https://www.youtube.com/@devafora" target="_blank" rel="noopener" class="text-brand hover:underline">
            {{ t('seeOnChannel') }} &rarr;
          </a>
        </div>
      </section>

      <!-- FOOTER -->
      <footer class="mt-16 w-full border-t border-border-subtle pt-8 text-center text-sm text-text-muted">
        © {{ new Date().getFullYear() }} DevAfora — Lucas Sahdo. {{ t('rights') }}
      </footer>
    </div>
  </div>
</template>
