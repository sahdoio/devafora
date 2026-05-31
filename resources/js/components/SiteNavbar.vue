<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { useI18n, type Locale } from '@/composables/useI18n'

const { t, locale } = useI18n()

const links = [
  { label: 'YouTube', url: 'https://www.youtube.com/@devafora' },
  { label: 'Instagram', url: 'https://www.instagram.com/lucassahdo' },
]

const languages: { code: Locale; flag: string; label: string }[] = [
  { code: 'pt', flag: '🇧🇷', label: 'PT' },
  { code: 'en', flag: '🇺🇸', label: 'EN' },
]

function switchLocale(code: Locale) {
  if (code === locale.value) return
  router.get(`/locale/${code}`, {}, { preserveScroll: true })
}
</script>

<template>
  <nav class="fixed inset-x-0 top-0 z-50 border-b border-border-subtle/60 bg-surface-primary/85 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-[1100px] items-center px-6">
      <!-- Logo -->
      <Link href="/" class="flex items-center">
        <img src="/images/logo.png" alt="DevAfora" class="h-9 w-auto" />
      </Link>

      <!-- Links -->
      <div class="ml-auto flex items-center gap-5 text-[15px]">
        <Link
          href="/posts"
          class="text-text-secondary transition-colors duration-150 hover:text-brand"
        >
          {{ t('navPosts') }}
        </Link>
        <a
          v-for="link in links"
          :key="link.label"
          :href="link.url"
          target="_blank"
          rel="noopener"
          class="hidden text-text-secondary transition-colors duration-150 hover:text-brand sm:inline"
        >
          {{ link.label }}
        </a>

        <!-- Language switcher -->
        <div class="ml-1 flex items-center overflow-hidden rounded-lg border border-border-subtle">
          <button
            v-for="lang in languages"
            :key="lang.code"
            type="button"
            @click="switchLocale(lang.code)"
            :class="[
              'px-2.5 py-1 text-xs font-medium transition-colors duration-150',
              locale === lang.code
                ? 'bg-surface-tertiary text-text-primary'
                : 'text-text-muted hover:text-text-primary',
            ]"
            :aria-pressed="locale === lang.code"
          >
            <span class="mr-1">{{ lang.flag }}</span>{{ lang.label }}
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>
