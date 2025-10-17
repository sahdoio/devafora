<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Trash2 } from 'lucide-vue-next';
import { useConfirm } from '@/composables/useConfirm';

interface LinkItem {
    id: number;
    title: string;
    description: string;
    url: string;
    icon: string;
    order: number;
    is_active: boolean;
    profile: {
        id: number;
        name: string;
    };
}

defineProps<{
    links: LinkItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Links', href: '/admin/links' },
];

const { confirm } = useConfirm();

const deleteLink = async (id: number) => {
    const confirmed = await confirm({
        title: 'Delete Link',
        message: 'Are you sure you want to delete this link? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger',
    });

    if (confirmed) {
        router.delete(`/admin/links/${id}`);
    }
};
</script>

<template>
    <Head title="Links Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Debug: {{ links.length }} links found -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Links Management</h1>
                <Link
                    href="/admin/links/create"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Create Link
                </Link>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full">
                    <thead class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="px-4 py-3 text-left">Order</th>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">URL</th>
                            <th class="px-4 py-3 text-left">Profile</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="link in links"
                            :key="link.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                        >
                            <td class="px-4 py-3">{{ link.order }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span v-if="link.icon">{{ link.icon }}</span>
                                    {{ link.title }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a
                                    :href="link.url"
                                    target="_blank"
                                    class="text-blue-600 hover:underline"
                                >
                                    {{ link.url }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ link.profile?.name || 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs',
                                        link.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    ]"
                                >
                                    {{ link.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1.5 flex-wrap">
                                    <!-- Edit Button -->
                                    <Link
                                        :href="`/admin/links/${link.id}/edit`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                                        title="Edit link"
                                    >
                                        <Edit :size="14" />
                                        <span>Edit</span>
                                    </Link>

                                    <!-- Delete Button -->
                                    <button
                                        @click="deleteLink(link.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/30 hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
                                        title="Delete link"
                                    >
                                        <Trash2 :size="14" />
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="links.length === 0" class="p-8 text-center text-gray-500">
                    No links found. Create your first link!
                </div>
            </div>
        </div>
    </AppLayout>
</template>
