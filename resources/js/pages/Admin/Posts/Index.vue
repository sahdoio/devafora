<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

interface PostItem {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    author: string;
    is_published: boolean;
    published_at: string | null;
    profile: {
        id: number;
        name: string;
    };
    tags: string[];
}

const props = defineProps<{
    posts: PostItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Posts', href: '/admin/posts' },
];

const deletePost = (id: number) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(`/admin/posts/${id}`);
    }
};

const formatDate = (date: string | null) => {
    if (!date) return 'Not published';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Posts Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Posts Management</h1>
                <Link
                    href="/admin/posts/create"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Create Post
                </Link>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full">
                    <thead class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Author</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Published</th>
                            <th class="px-4 py-3 text-left">Tags</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="post in posts"
                            :key="post.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ post.title }}</div>
                                <div class="text-xs text-gray-500">{{ post.excerpt?.substring(0, 80) || 'No excerpt' }}...</div>
                            </td>
                            <td class="px-4 py-3">{{ post.author }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs',
                                        post.is_published
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    ]"
                                >
                                    {{ post.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ formatDate(post.published_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in post.tags?.slice(0, 3)"
                                        :key="tag"
                                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                    >
                                        {{ tag }}
                                    </span>
                                    <span v-if="post.tags?.length > 3" class="text-xs text-gray-500">
                                        +{{ post.tags.length - 3 }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Link
                                        :href="`/admin/posts/${post.id}/preview`"
                                        class="text-purple-600 hover:underline"
                                    >
                                        Preview
                                    </Link>
                                    <Link
                                        :href="`/admin/posts/${post.id}/edit`"
                                        class="text-blue-600 hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deletePost(post.id)"
                                        class="text-red-600 hover:underline"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="posts.length === 0" class="p-8 text-center text-gray-500">
                    No posts found. Create your first post!
                </div>
            </div>
        </div>
    </AppLayout>
</template>
