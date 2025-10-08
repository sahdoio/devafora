<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import LinkCard from '@/components/LinkCard.vue'
import NewsletterForm from '@/components/NewsletterForm.vue'

interface Profile {
  id: number
  name: string
  bio: string
  photo?: string | null
}

interface Link {
  id: number
  title: string
  description: string
  url: string
  icon: string
}

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

const props = defineProps<{
  profile: Profile | null
  links: Link[]
  posts: Post[]
}>()

// Debug temporário
if (props.profile) {
  console.log('Profile received:', props.profile)
  console.log('Bio value:', props.profile.bio)
  console.log('Bio type:', typeof props.profile.bio)
}
</script>

<template>
  <Head title="DevAfora - Links e Conteúdo" />

  <!-- Container principal com gradiente único e contínuo -->
  <div class="min-h-screen bg-gradient-to-b from-[#0a1628] via-[#0f2847] to-[#1e3a8a]">

    <!-- HEADER/PROFILE - Seção única sem separação visual -->
    <section class="px-4 py-6">
      <div class="mx-auto max-w-4xl">
        <div class="flex flex-col items-center text-center">

          <!-- Profile Photo -->
          <div class="mb-6">
            <div class="h-40 w-40 rounded-full bg-gradient-to-br from-blue-300 to-blue-400 p-1 shadow-2xl shadow-blue-400/30">
              <img
                :src="profile?.photo || '/images/profile.jpeg'"
                :alt="profile?.name || 'DevAfora'"
                class="h-full w-full rounded-full object-cover"
              />
            </div>
          </div>

          <!-- Logo -->
          <div class="mb-4">
            <img
              src="/images/logo.png"
              :alt="profile?.name || 'DevAfora'"
              class="h-20 w-auto md:h-24"
            />
          </div>

          <!-- Bio COMPLETA -->
          <p class="mx-auto max-w-3xl text-lg leading-relaxed text-gray-300">
            {{ profile?.bio ?? '' }}
          </p>
        </div>
      </div>
    </section>

    <!-- LINKS - Cards LARGOS sem separação visual -->
    <section class="px-4 py-3">
      <div class="mx-auto max-w-2xl">
        <div class="space-y-3">
          <LinkCard v-for="link in links" :key="link.id" :link="link" />
        </div>
      </div>
    </section>

    <!-- NEWSLETTER - Sem mudança brusca de background -->
    <section class="px-4 py-12">
      <div class="mx-auto max-w-2xl">
        <div class="rounded-2xl border border-blue-500/10 bg-white/5 p-8 backdrop-blur-sm">
          <div class="text-center">
            <h2 class="mb-3 text-3xl font-bold text-white">
              Inscreva-se na Newsletter
            </h2>
            <p class="mb-8 text-gray-400">
              Receba conteúdos exclusivos, dicas e novidades diretamente no seu e-mail
            </p>
            <NewsletterForm />
          </div>
        </div>
      </div>
    </section>

    <!-- POSTS - Temporarily hidden for launch -->
    <!-- <section class="px-4 py-12">
      <div class="mx-auto max-w-5xl">
        <h2 class="mb-8 text-center text-3xl font-bold text-white md:text-4xl">
          Últimos Posts
        </h2>

        <div v-if="posts && posts.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <PostCard v-for="post in posts" :key="post.id" :post="post" />
        </div>
      </div>
    </section> -->

    <!-- FOOTER - Minimalista -->
    <footer class="border-t border-white/5 px-4 py-8">
      <div class="mx-auto max-w-6xl text-center text-sm text-gray-500">
        <p>© {{ new Date().getFullYear() }} DevAfora. Todos os direitos reservados.</p>
      </div>
    </footer>
  </div>
</template>
