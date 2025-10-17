<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Edit, Mail, Trash2, CheckCircle } from 'lucide-vue-next';
import { useConfirm } from '@/composables/useConfirm';

interface PostItem {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    author: string;
    is_published: boolean;
    published_at: string | null;
    newsletter_sent_at: string | null;
    profile: {
        id: number;
        name: string;
    };
    tags: string[];
}

defineProps<{
    posts: PostItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Posts', href: '/admin/posts' },
];

const { confirm } = useConfirm();

const deletePost = async (id: number) => {
    const confirmed = await confirm({
        title: 'Delete Post',
        message: 'Are you sure you want to delete this post? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger',
    });

    if (confirmed) {
        router.delete(`/admin/posts/${id}`);
    }
};

const sendNewsletter = async (post: PostItem) => {
    if (!post.is_published) {
        await confirm({
            title: 'Post Not Published',
            message: 'Post must be published before sending newsletter.',
            confirmText: 'OK',
            cancelText: 'Close',
            variant: 'warning',
        });
        return;
    }

    let confirmed = false;

    if (post.newsletter_sent_at) {
        confirmed = await confirm({
            title: 'Newsletter Already Sent',
            message: `Newsletter was already sent on ${formatDate(post.newsletter_sent_at)}. Are you sure you want to send it again?`,
            confirmText: 'Send Again',
            cancelText: 'Cancel',
            variant: 'warning',
        });
    } else {
        confirmed = await confirm({
            title: 'Send Newsletter',
            message: 'Are you sure you want to send this post to all active newsletter subscribers? This action cannot be undone.',
            confirmText: 'Send Newsletter',
            cancelText: 'Cancel',
            variant: 'info',
        });
    }

    if (confirmed) {
        router.post(`/admin/posts/${post.id}/send-newsletter`);
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
                                <div class="flex gap-1.5 flex-wrap">
                                    <!-- Preview Button -->
                                    <Link
                                        :href="`/admin/posts/${post.id}/preview`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors"
                                        title="Preview post"
                                    >
                                        <Eye :size="14" />
                                        <span>Preview</span>
                                    </Link>

                                    <!-- Edit Button -->
                                    <Link
                                        :href="`/admin/posts/${post.id}/edit`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                                        title="Edit post"
                                    >
                                        <Edit :size="14" />
                                        <span>Edit</span>
                                    </Link>

                                    <!-- Send Newsletter Button -->
                                    <button
                                        v-if="post.is_published"
                                        @click="sendNewsletter(post)"
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors',
                                            post.newsletter_sent_at
                                                ? 'border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-950/30 hover:bg-green-100 dark:hover:bg-green-900/50'
                                                : 'border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-950/30 hover:bg-orange-100 dark:hover:bg-orange-900/50'
                                        ]"
                                        :title="post.newsletter_sent_at ? `Sent on ${formatDate(post.newsletter_sent_at)}` : 'Send newsletter'"
                                    >
                                        <component :is="post.newsletter_sent_at ? CheckCircle : Mail" :size="14" />
                                        <span>{{ post.newsletter_sent_at ? 'Sent' : 'Newsletter' }}</span>
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        @click="deletePost(post.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/30 hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
                                        title="Delete post"
                                    >
                                        <Trash2 :size="14" />
                                        <span>Delete</span>
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
