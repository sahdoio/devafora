import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export type Locale = 'pt' | 'en'

type Messages = Record<string, string>

const messages: Record<Locale, Messages> = {
  pt: {
    // Hero
    heroTitle: 'Engenheiro de Software Sênior',
    heroSubtitle: 'Especialista Backend & Arquiteto de Sistemas',
    heroBio:
      'Engenheiro de Software Sênior com mais de uma década de experiência desde 2010. ' +
      'Especialista em APIs REST e soluções backend com <strong class="font-medium text-text-primary">PHP</strong>, ' +
      '<strong class="font-medium text-text-primary">Laravel</strong>, ' +
      '<strong class="font-medium text-text-primary">Symfony</strong> e ' +
      '<strong class="font-medium text-text-primary">Node.js</strong>. ' +
      'Experiência em frontend com <strong class="font-medium text-text-primary">Vue.js</strong> e ' +
      '<strong class="font-medium text-text-primary">React</strong>, DevOps com ' +
      '<strong class="font-medium text-text-primary">Docker</strong> e ' +
      '<strong class="font-medium text-text-primary">AWS</strong>, e educador de tecnologia no ' +
      '<a href="https://www.youtube.com/@devafora" class="font-medium text-brand hover:underline">DevAfora</a>.',
    heroAvailable: 'Disponível para freelance, consultoria e colaboração',

    // Sections
    latestPosts: 'Últimos Posts',
    seeAll: 'Ver todos',
    noPosts: 'Nenhum post publicado ainda.',
    videosTitle: 'Vídeos no YouTube',
    loadMore: 'Carregar mais',
    videosUnavailable: 'Não foi possível carregar os vídeos agora.',
    seeOnChannel: 'Veja no canal',

    // Newsletter
    newsletterTitle: 'Inscreva-se na Newsletter',
    newsletterText: 'Receba os novos posts e conteúdos exclusivos direto no seu e-mail.',
    nlPlaceholder: 'Seu melhor e-mail',
    nlSubscribe: 'Inscrever',
    nlSending: 'Enviando...',
    nlSuccess: '✓ Inscrição realizada com sucesso! Verifique seu e-mail.',
    nlConsent: 'Ao se inscrever, você concorda em receber e-mails com novidades e conteúdos.',
    nlNeedEmail: 'Por favor, insira seu e-mail.',
    nlServerError: 'Erro ao conectar com o servidor. Tente novamente.',

    // Posts pages
    allPosts: 'Todos os Posts',
    allPostsSubtitle: 'Explore nossos conteúdos sobre desenvolvimento web',
    minRead: 'min de leitura',
    back: 'Voltar',
    backHome: 'Voltar para Home',
    comments: 'Comentários',

    // Footer / nav
    rights: 'Todos os direitos reservados.',
    navPosts: 'Posts',
  },
  en: {
    // Hero
    heroTitle: 'Senior Software Engineer',
    heroSubtitle: 'Backend Specialist & System Architect',
    heroBio:
      'Senior Software Engineer with over a decade of experience since 2010. ' +
      'Specialist in REST APIs and backend solutions with <strong class="font-medium text-text-primary">PHP</strong>, ' +
      '<strong class="font-medium text-text-primary">Laravel</strong>, ' +
      '<strong class="font-medium text-text-primary">Symfony</strong> and ' +
      '<strong class="font-medium text-text-primary">Node.js</strong>. ' +
      'Experienced in frontend with <strong class="font-medium text-text-primary">Vue.js</strong> and ' +
      '<strong class="font-medium text-text-primary">React</strong>, DevOps with ' +
      '<strong class="font-medium text-text-primary">Docker</strong> and ' +
      '<strong class="font-medium text-text-primary">AWS</strong>, and tech educator at ' +
      '<a href="https://www.youtube.com/@devafora" class="font-medium text-brand hover:underline">DevAfora</a>.',
    heroAvailable: 'Available for freelance, consulting & collaboration',

    // Sections
    latestPosts: 'Latest Posts',
    seeAll: 'See all',
    noPosts: 'No posts published yet.',
    videosTitle: 'Videos on YouTube',
    loadMore: 'Load more',
    videosUnavailable: "Couldn't load the videos right now.",
    seeOnChannel: 'See on the channel',

    // Newsletter
    newsletterTitle: 'Subscribe to the Newsletter',
    newsletterText: 'Get new posts and exclusive content straight to your inbox.',
    nlPlaceholder: 'Your best email',
    nlSubscribe: 'Subscribe',
    nlSending: 'Sending...',
    nlSuccess: '✓ Subscription successful! Check your email.',
    nlConsent: 'By subscribing, you agree to receive emails with news and content.',
    nlNeedEmail: 'Please enter your email.',
    nlServerError: 'Failed to connect to the server. Please try again.',

    // Posts pages
    allPosts: 'All Posts',
    allPostsSubtitle: 'Explore our content about web development',
    minRead: 'min read',
    back: 'Back',
    backHome: 'Back to Home',
    comments: 'Comments',

    // Footer / nav
    rights: 'All rights reserved.',
    navPosts: 'Posts',
  },
}

export function useI18n() {
  const page = usePage()

  const locale = computed<Locale>(() => {
    const l = (page.props.locale as string) ?? 'pt'
    return l === 'en' ? 'en' : 'pt'
  })

  function t(key: string): string {
    return messages[locale.value]?.[key] ?? messages.pt[key] ?? key
  }

  return { locale, t }
}
