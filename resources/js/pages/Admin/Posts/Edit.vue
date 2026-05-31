<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import MarkdownEditor from '@/components/MarkdownEditor.vue';

const props = defineProps<{
    slug: string;
    locale: string;
    exists: boolean;
    existingLocales: string[];
    locales: string[];
    markdown: string;
}>();

const localeLabels: Record<string, string> = { pt: 'Português', en: 'English' };

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Posts', href: '/admin/posts' },
    { title: 'Editar', href: `/admin/posts/${props.slug}/edit` },
];

const form = useForm({
    slug: props.slug,
    locale: props.locale,
    markdown: props.markdown,
});

const submit = () => {
    form.put(`/admin/posts/${props.slug}`);
};

const switchLocale = (locale: string) => {
    if (locale === props.locale) return;
    router.get(`/admin/posts/${props.slug}/edit`, { lang: locale });
};
</script>

<template>
    <Head :title="`Editar: ${slug} (${locale})`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold">
                    {{ exists ? 'Editar Post' : 'Nova tradução' }}
                </h1>
                <Link
                    v-if="exists"
                    :href="`/admin/posts/${slug}/preview?lang=${locale}`"
                    class="rounded-md border border-sidebar-border/70 px-4 py-2 text-sm hover:bg-accent dark:border-sidebar-border"
                >
                    Preview
                </Link>
            </div>

            <!-- Language tabs -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Idioma:</span>
                <button
                    v-for="l in locales"
                    :key="l"
                    type="button"
                    @click="switchLocale(l)"
                    :class="[
                        'inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-sm transition-colors',
                        l === locale
                            ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
                            : 'border-sidebar-border/70 hover:bg-accent dark:border-sidebar-border',
                    ]"
                >
                    {{ localeLabels[l] ?? l }}
                    <span
                        v-if="existingLocales.includes(l)"
                        class="rounded-full bg-green-100 px-1.5 text-[10px] text-green-800 dark:bg-green-900 dark:text-green-200"
                    >✓</span>
                    <span
                        v-else
                        class="rounded-full bg-gray-100 px-1.5 text-[10px] text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    >+</span>
                </button>
            </div>

            <div
                v-if="!exists"
                class="rounded-md border border-yellow-300 bg-yellow-50 px-4 py-2 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950/30 dark:text-yellow-200"
            >
                Esta tradução ({{ localeLabels[locale] ?? locale }}) ainda não existe — ao salvar, ela será criada.
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="max-w-md">
                    <label for="slug" class="mb-2 block text-sm font-medium">
                        Slug <span class="text-gray-500">(renomeia a pasta — afeta todos os idiomas)</span>
                    </label>
                    <input
                        id="slug"
                        v-model="form.slug"
                        type="text"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 font-mono text-sm dark:border-sidebar-border"
                    />
                    <InputError :message="form.errors.slug" />
                    <p class="mt-1 text-xs text-gray-500">
                        URL final: <code>/posts/{{ form.slug || 'slug' }}</code>
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Conteúdo (Markdown)</label>
                    <MarkdownEditor v-model="form.markdown" :slug="form.slug" />
                    <InputError :message="form.errors.markdown" />
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ exists ? 'Salvar alterações' : 'Criar tradução' }}
                    </button>
                    <a
                        href="/admin/posts"
                        class="rounded-md border border-sidebar-border/70 px-4 py-2 hover:bg-accent dark:border-sidebar-border"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
