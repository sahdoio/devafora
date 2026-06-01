<script setup lang="ts">
import { ref, shallowRef, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { Bars3BottomLeftIcon, ChevronLeftIcon, ArrowUpIcon } from '@heroicons/vue/24/outline'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

interface TocItem {
  id: string
  text: string
  level: number
}

const props = defineProps<{
  // The rendered post content container whose headings drive the TOC.
  target: HTMLElement | null
}>()

const items = shallowRef<TocItem[]>([])
const activeId = ref<string>('')
const collapsed = ref(false)
const showToTop = ref(false)

let observer: IntersectionObserver | null = null

function slugify(text: string): string {
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
}

function buildToc() {
  const root = props.target
  if (!root) {
    items.value = []
    return
  }

  const headings = Array.from(root.querySelectorAll('h2, h3')) as HTMLElement[]
  const seen = new Map<string, number>()
  const list: TocItem[] = []

  for (const el of headings) {
    const text = (el.textContent ?? '').trim()
    if (!text) continue

    let id = el.id || slugify(text) || 'section'
    // Guarantee uniqueness when two headings share a slug.
    const count = seen.get(id) ?? 0
    seen.set(id, count + 1)
    if (count > 0) id = `${id}-${count}`

    el.id = id
    // Offset anchor jumps so the fixed navbar doesn't cover the heading.
    el.style.scrollMarginTop = '6rem'

    list.push({ id, text, level: el.tagName === 'H3' ? 3 : 2 })
  }

  items.value = list
  setupObserver(headings)
}

function setupObserver(headings: HTMLElement[]) {
  observer?.disconnect()
  observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          activeId.value = entry.target.id
        }
      }
    },
    { rootMargin: '-80px 0px -70% 0px', threshold: 0 },
  )
  headings.forEach((h) => observer?.observe(h))
}

function jumpTo(id: string) {
  const el = document.getElementById(id)
  if (!el) return
  el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  activeId.value = id
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function onScroll() {
  showToTop.value = window.scrollY > 600
}

onMounted(() => {
  nextTick(buildToc)
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

watch(() => props.target, () => nextTick(buildToc))

onBeforeUnmount(() => {
  observer?.disconnect()
  window.removeEventListener('scroll', onScroll)
})
</script>

<template>
  <!-- Left jump navigation (desktop only) -->
  <aside
    v-if="items.length"
    class="fixed left-6 top-40 z-30 hidden xl:block"
  >
    <!-- Collapsed: thin button to re-open -->
    <button
      v-if="collapsed"
      type="button"
      :title="t('tocTitle')"
      class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-slate-900/60 text-gray-400 backdrop-blur-md transition-colors hover:border-brand/40 hover:text-white"
      @click="collapsed = false"
    >
      <Bars3BottomLeftIcon class="h-[18px] w-[18px]" />
    </button>

    <!-- Expanded panel -->
    <nav
      v-else
      class="toc-scroll max-h-[calc(100vh-13rem)] w-64 overflow-y-auto rounded-2xl border border-white/10 bg-slate-900/60 p-5 backdrop-blur-md"
    >
      <div class="mb-4 flex items-center justify-between">
        <span class="font-heading text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">
          {{ t('tocTitle') }}
        </span>
        <button
          type="button"
          :title="t('tocTitle')"
          class="-mr-1 rounded-md p-1 text-gray-600 transition-colors hover:bg-white/5 hover:text-gray-300"
          @click="collapsed = true"
        >
          <ChevronLeftIcon class="h-4 w-4" />
        </button>
      </div>

      <ul class="space-y-0.5">
        <li v-for="item in items" :key="item.id">
          <button
            type="button"
            class="group flex w-full items-center gap-2.5 rounded-lg py-1.5 pr-2 text-left text-[13px] leading-snug tracking-tight transition-colors"
            :class="[
              item.level === 3 ? 'pl-6' : 'pl-3',
              activeId === item.id
                ? 'font-medium text-brand'
                : 'text-gray-400 hover:text-gray-200',
            ]"
            @click="jumpTo(item.id)"
          >
            <span
              class="h-1.5 w-1.5 shrink-0 rounded-full transition-colors"
              :class="
                activeId === item.id
                  ? 'bg-brand'
                  : 'bg-transparent group-hover:bg-slate-600'
              "
            />
            <span class="truncate">{{ item.text }}</span>
          </button>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- Back to top (all screen sizes) -->
  <transition
    enter-active-class="transition duration-200"
    enter-from-class="translate-y-2 opacity-0"
    leave-active-class="transition duration-200"
    leave-to-class="translate-y-2 opacity-0"
  >
    <button
      v-if="showToTop"
      type="button"
      :title="t('backToTop')"
      class="fixed bottom-6 right-6 z-30 flex h-11 w-11 items-center justify-center rounded-full border border-white/10 bg-slate-900/70 text-gray-300 shadow-lg backdrop-blur-md transition-colors hover:border-brand/40 hover:text-white"
      @click="scrollToTop"
    >
      <ArrowUpIcon class="h-5 w-5" />
    </button>
  </transition>
</template>

<style scoped>
/* Slim, modern scrollbar for the TOC panel. */
.toc-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgb(71 85 105 / 0.5) transparent;
}
.toc-scroll::-webkit-scrollbar {
  width: 6px;
}
.toc-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.toc-scroll::-webkit-scrollbar-thumb {
  background-color: rgb(71 85 105 / 0.45);
  border-radius: 9999px;
}
.toc-scroll::-webkit-scrollbar-thumb:hover {
  background-color: rgb(100 116 139 / 0.7);
}
</style>
