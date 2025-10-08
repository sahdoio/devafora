<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { onMounted, nextTick } from 'vue';
import hljs from 'highlight.js';
import 'highlight.js/styles/monokai.css';

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    author: string;
    image: string;
    read_time: number;
    tags: string[];
    published_at: string | null;
    is_published: boolean;
}

const props = defineProps<{
    post: Post;
}>();

onMounted(() => {
    nextTick(() => {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block as HTMLElement);
        });
    });
});

const formatDate = (date: string | null) => {
    if (!date) return 'Not published';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Preview: ${post.title}`" />

    <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
        <!-- Preview Banner -->
        <div class="bg-yellow-600 px-4 py-2 text-center text-sm font-medium text-white">
            PREVIEW MODE - This is how your post will appear to readers
        </div>

        <!-- Header -->
        <header class="border-b border-slate-800 bg-slate-900/50">
            <div class="container mx-auto max-w-4xl px-4 py-6">
                <div class="flex items-center justify-between">
                    <Link
                        href="/admin/posts"
                        class="inline-flex items-center gap-2 text-gray-400 transition-colors hover:text-white"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        <span>Back to Posts</span>
                    </Link>
                    <div class="flex gap-2">
                        <Link
                            :href="`/admin/posts/${post.id}/edit`"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                        >
                            Edit Post
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Post Content -->
        <article class="py-12">
            <div class="container mx-auto max-w-4xl px-4">
                <!-- Status Badge -->
                <div class="mb-4 flex items-center gap-2">
                    <span
                        :class="[
                            'rounded-full px-3 py-1 text-sm font-medium',
                            post.is_published
                                ? 'bg-green-500/20 text-green-400'
                                : 'bg-yellow-500/20 text-yellow-400',
                        ]"
                    >
                        {{ post.is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>

                <!-- Featured Image -->
                <div v-if="post.image" class="mb-8 overflow-hidden rounded-2xl">
                    <img :src="post.image" :alt="post.title" class="h-auto w-full" />
                </div>

                <!-- Title -->
                <h1 class="mb-6 text-4xl font-bold text-white md:text-5xl">
                    {{ post.title }}
                </h1>

                <!-- Meta Info -->
                <div class="mb-8 flex flex-wrap items-center gap-4 text-sm text-gray-400">
                    <div v-if="post.author" class="flex items-center gap-2">
                        <span class="font-medium text-white">{{ post.author }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <ClockIcon class="h-4 w-4" />
                        <span>{{ post.read_time }} min read</span>
                    </div>
                    <div>{{ formatDate(post.published_at) }}</div>
                </div>

                <!-- Tags -->
                <div v-if="post.tags && post.tags.length > 0" class="mb-8 flex flex-wrap gap-2">
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
                    class="prose prose-invert prose-lg max-w-none prose-headings:text-white prose-p:text-gray-300 prose-a:text-blue-400 prose-strong:text-white prose-code:rounded prose-code:bg-slate-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-blue-400 prose-pre:overflow-x-auto prose-pre:rounded-xl prose-pre:bg-slate-900 prose-pre:p-0 prose-img:rounded-xl"
                    v-html="post.content"
                ></div>

                <!-- Action Buttons -->
                <div class="mt-12 flex justify-center gap-4">
                    <Link
                        href="/admin/posts"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-800 px-6 py-3 font-semibold text-white transition-all hover:bg-slate-700"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        <span>Back to Posts</span>
                    </Link>
                    <Link
                        :href="`/admin/posts/${post.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-3 font-semibold text-white transition-all hover:from-blue-700 hover:to-purple-700"
                    >
                        <span>Edit Post</span>
                    </Link>
                </div>
            </div>
        </article>
    </div>
</template>
