<script setup lang="ts">
import { onMounted, watch, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from '@/composables/useI18n'

const props = defineProps<{ slug: string }>()

const page = usePage()
const { t, locale } = useI18n()

const container = ref<HTMLElement | null>(null)

interface GiscusConfig {
  repo?: string
  repoId?: string
  category?: string
  categoryId?: string
}

const giscus = (page.props.giscus ?? {}) as GiscusConfig
const configured = !!(giscus.repo && giscus.repoId && giscus.categoryId)

// Map the site locale to a giscus UI language.
const giscusLang = () => (locale.value === 'en' ? 'en' : 'pt')

function render() {
  if (!configured || !container.value) return

  // Recreate the embed on each (slug, locale) change.
  container.value.innerHTML = ''

  const script = document.createElement('script')
  script.src = 'https://giscus.app/client.js'
  script.async = true
  script.crossOrigin = 'anonymous'
  script.setAttribute('data-repo', giscus.repo!)
  script.setAttribute('data-repo-id', giscus.repoId!)
  script.setAttribute('data-category', giscus.category || 'Announcements')
  script.setAttribute('data-category-id', giscus.categoryId!)
  // One discussion thread per post slug — shared across PT/EN.
  script.setAttribute('data-mapping', 'specific')
  script.setAttribute('data-term', props.slug)
  script.setAttribute('data-strict', '1')
  script.setAttribute('data-reactions-enabled', '1')
  script.setAttribute('data-emit-metadata', '0')
  script.setAttribute('data-input-position', 'top')
  script.setAttribute('data-theme', 'noborder_dark')
  script.setAttribute('data-lang', giscusLang())
  script.setAttribute('data-loading', 'lazy')

  container.value.appendChild(script)
}

onMounted(render)
watch(() => [props.slug, locale.value], render)
</script>

<template>
  <section v-if="configured" class="mt-16 border-t border-slate-800 pt-10">
    <h2 class="mb-6 font-heading text-2xl font-semibold tracking-tight text-white">
      {{ t('comments') }}
    </h2>
    <div ref="container" class="giscus"></div>
  </section>
</template>
