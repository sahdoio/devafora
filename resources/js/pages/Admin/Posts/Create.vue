<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import MarkdownEditor from '@/components/MarkdownEditor.vue';

const props = defineProps<{
    template: string;
    slug: string;
    locale: string;
    locales: string[];
}>();

const localeLabels: Record<string, string> = { pt: 'Português', en: 'English' };

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Posts', href: '/admin/posts' },
    { title: 'Novo', href: '/admin/posts/create' },
];

const form = useForm({
    slug: props.slug,
    locale: props.locale,
    markdown: props.template,
});

const submit = () => {
    form.post('/admin/posts');
};
</script>

<template>
    <Head title="Novo Post" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Novo Post</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="flex flex-wrap gap-4">
                    <div class="max-w-md flex-1">
                        <label for="slug" class="mb-2 block text-sm font-medium">
                            Slug <span class="text-gray-500">(pasta / URL)</span>
                        </label>
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            placeholder="meu-primeiro-post"
                            class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 font-mono text-sm dark:border-sidebar-border"
                        />
                        <InputError :message="form.errors.slug" />
                    </div>

                    <div>
                        <label for="locale" class="mb-2 block text-sm font-medium">Idioma</label>
                        <select
                            id="locale"
                            v-model="form.locale"
                            class="rounded-md border border-sidebar-border/70 bg-background px-3 py-2 text-sm dark:border-sidebar-border"
                        >
                            <option v-for="l in locales" :key="l" :value="l">{{ localeLabels[l] ?? l }}</option>
                        </select>
                        <InputError :message="form.errors.locale" />
                    </div>
                </div>
                <p class="text-xs text-gray-500">
                    URL final: <code>/posts/{{ form.slug || 'slug' }}</code> — o idioma é gravado em
                    <code>{{ form.locale === 'pt' ? 'index.md' : `index.${form.locale}.md` }}</code>.
                    Use o mesmo slug para criar a tradução de um post existente.
                </p>

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
                        Salvar
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
