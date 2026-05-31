<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Edit, Mail, Trash2, CheckCircle, Plus } from 'lucide-vue-next';
import { useConfirm } from '@/composables/useConfirm';

interface Translation {
    locale: string;
    is_published: boolean;
    published_at: string | null;
    newsletter_sent_at: string | null;
    title: string;
    excerpt: string;
}

interface PostItem {
    slug: string;
    title: string;
    excerpt: string;
    available_locales: string[];
    translations: Translation[];
}

defineProps<{
    posts: PostItem[];
    locales: string[];
}>();

const flag: Record<string, string> = { pt: '🇧🇷', en: '🇺🇸' };

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Posts', href: '/admin/posts' },
];

const { confirm } = useConfirm();

const translationFor = (post: PostItem, locale: string): Translation | undefined =>
    post.translations.find((t) => t.locale === locale);

const deletePost = async (post: PostItem) => {
    const confirmed = await confirm({
        title: 'Excluir Post',
        message: `Excluir definitivamente "${post.slug}" e TODOS os idiomas (${post.available_locales.map((l) => l.toUpperCase()).join(', ')}) e mídias? Esta ação não pode ser desfeita.`,
        confirmText: 'Excluir tudo',
        cancelText: 'Cancelar',
        variant: 'danger',
    });

    if (confirmed) {
        router.delete(`/admin/posts/${post.slug}`);
    }
};

const sendNewsletter = async (slug: string, t: Translation) => {
    let confirmed = false;

    if (t.newsletter_sent_at) {
        confirmed = await confirm({
            title: 'Newsletter já enviada',
            message: `A newsletter (${t.locale.toUpperCase()}) já foi enviada em ${formatDate(t.newsletter_sent_at)}. Deseja enviar novamente?`,
            confirmText: 'Enviar de novo',
            cancelText: 'Cancelar',
            variant: 'warning',
        });
    } else {
        confirmed = await confirm({
            title: 'Enviar newsletter',
            message: `Enviar este post (${t.locale.toUpperCase()}) para todos os inscritos ativos?`,
            confirmText: 'Enviar newsletter',
            cancelText: 'Cancelar',
            variant: 'info',
        });
    }

    if (confirmed) {
        router.post(`/admin/posts/${slug}/send-newsletter?lang=${t.locale}`);
    }
};

const formatDate = (date: string | null) => {
    if (!date) return 'Rascunho';
    return new Date(date).toLocaleDateString('pt-BR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Gerenciar Posts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Gerenciar Posts</h1>
                <Link
                    href="/admin/posts/create"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Novo Post
                </Link>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full">
                    <thead class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="px-4 py-3 text-left">Título</th>
                            <th class="px-4 py-3 text-left">Idiomas</th>
                            <th class="px-4 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="post in posts"
                            :key="post.slug"
                            class="border-b border-sidebar-border/70 align-top last:border-b-0 dark:border-sidebar-border"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ post.title }}</div>
                                <div class="text-xs text-gray-500">{{ post.excerpt?.substring(0, 90) || 'Sem resumo' }}...</div>
                                <div class="mt-0.5 font-mono text-xs text-gray-400">{{ post.slug }}</div>
                            </td>

                            <!-- Language chips: existing -> edit, missing -> create translation -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    <template v-for="locale in locales" :key="locale">
                                        <Link
                                            v-if="translationFor(post, locale)"
                                            :href="`/admin/posts/${post.slug}/edit?lang=${locale}`"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-sidebar-border/70 px-2.5 py-1 text-xs hover:bg-accent dark:border-sidebar-border"
                                            :title="`Editar ${locale.toUpperCase()} — ${translationFor(post, locale)!.is_published ? 'Publicado' : 'Rascunho'}`"
                                        >
                                            <span
                                                :class="[
                                                    'h-2 w-2 rounded-full',
                                                    translationFor(post, locale)!.is_published ? 'bg-green-500' : 'bg-yellow-500',
                                                ]"
                                            ></span>
                                            {{ flag[locale] }} {{ locale.toUpperCase() }}
                                        </Link>
                                        <Link
                                            v-else
                                            :href="`/admin/posts/create?slug=${post.slug}&lang=${locale}`"
                                            class="inline-flex items-center gap-1 rounded-lg border border-dashed border-sidebar-border/70 px-2.5 py-1 text-xs text-gray-500 hover:bg-accent dark:border-sidebar-border"
                                            :title="`Criar tradução ${locale.toUpperCase()}`"
                                        >
                                            <Plus :size="12" />
                                            {{ flag[locale] }} {{ locale.toUpperCase() }}
                                        </Link>
                                    </template>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    <!-- Edit per existing language -->
                                    <Link
                                        v-for="t in post.translations"
                                        :key="`edit-${t.locale}`"
                                        :href="`/admin/posts/${post.slug}/edit?lang=${t.locale}`"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-300 dark:hover:bg-blue-900/50"
                                        :title="`Editar ${t.locale.toUpperCase()}`"
                                    >
                                        <Edit :size="14" />
                                        <span>Editar {{ flag[t.locale] }}</span>
                                    </Link>

                                    <!-- Preview per existing language -->
                                    <Link
                                        v-for="t in post.translations"
                                        :key="`prev-${t.locale}`"
                                        :href="`/admin/posts/${post.slug}/preview?lang=${t.locale}`"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-purple-200 bg-purple-50 px-3 py-1.5 text-xs font-medium text-purple-700 transition-colors hover:bg-purple-100 dark:border-purple-800 dark:bg-purple-950/30 dark:text-purple-300 dark:hover:bg-purple-900/50"
                                        :title="`Preview ${t.locale.toUpperCase()}`"
                                    >
                                        <Eye :size="14" />
                                        <span>{{ flag[t.locale] }}</span>
                                    </Link>

                                    <!-- Newsletter per published language -->
                                    <template v-for="t in post.translations" :key="`nl-${t.locale}`">
                                        <button
                                            v-if="t.is_published"
                                            @click="sendNewsletter(post.slug, t)"
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors',
                                                t.newsletter_sent_at
                                                    ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100 dark:border-green-800 dark:bg-green-950/30 dark:text-green-300 dark:hover:bg-green-900/50'
                                                    : 'border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-950/30 dark:text-orange-300 dark:hover:bg-orange-900/50',
                                            ]"
                                            :title="t.newsletter_sent_at ? `Enviada em ${formatDate(t.newsletter_sent_at)} (${t.locale.toUpperCase()})` : `Enviar newsletter (${t.locale.toUpperCase()})`"
                                        >
                                            <component :is="t.newsletter_sent_at ? CheckCircle : Mail" :size="14" />
                                            <span>{{ flag[t.locale] }}</span>
                                        </button>
                                    </template>

                                    <!-- Delete whole post -->
                                    <button
                                        @click="deletePost(post)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 dark:border-red-800 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-900/50"
                                        title="Excluir post (todos os idiomas)"
                                    >
                                        <Trash2 :size="14" />
                                        <span>Excluir</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="posts.length === 0" class="p-8 text-center text-gray-500">
                    Nenhum post encontrado. Crie o seu primeiro post!
                </div>
            </div>
        </div>
    </AppLayout>
</template>
