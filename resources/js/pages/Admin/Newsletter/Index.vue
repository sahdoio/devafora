<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, ToggleLeft, ToggleRight, Trash2 } from 'lucide-vue-next';
import { useConfirm } from '@/composables/useConfirm';
import { computed } from 'vue';

interface NewsletterSubscription {
    id: number;
    email: string;
    name: string | null;
    is_active: boolean;
    subscribed_at: string;
    unsubscribed_at: string | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedData {
    data: NewsletterSubscription[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    subscriptions: PaginatedData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Newsletter', href: '/admin/newsletter' },
];

const { confirm } = useConfirm();

const activeCount = computed(() => {
    return props.subscriptions.data.filter(sub => sub.is_active).length;
});

const inactiveCount = computed(() => {
    return props.subscriptions.data.filter(sub => !sub.is_active).length;
});

const deleteSubscription = async (id: number) => {
    const confirmed = await confirm({
        title: 'Delete Subscription',
        message: 'Are you sure you want to delete this subscription? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger',
    });

    if (confirmed) {
        router.delete(`/admin/newsletter/${id}`);
    }
};

const toggleStatus = async (id: number, isActive: boolean) => {
    const confirmed = await confirm({
        title: isActive ? 'Deactivate Subscription' : 'Activate Subscription',
        message: isActive
            ? 'This subscriber will stop receiving newsletter emails.'
            : 'This subscriber will start receiving newsletter emails again.',
        confirmText: isActive ? 'Deactivate' : 'Activate',
        cancelText: 'Cancel',
        variant: isActive ? 'warning' : 'success',
    });

    if (confirmed) {
        router.post(`/admin/newsletter/${id}/toggle`);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Newsletter Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Newsletter Subscriptions</h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Total: {{ subscriptions.total }} | Active: {{ activeCount }} | Inactive: {{ inactiveCount }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="w-full">
                    <thead class="border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Subscribed At</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="subscription in subscriptions.data"
                            :key="subscription.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border hover:bg-sidebar/30 cursor-pointer"
                            @click="router.visit(`/admin/newsletter/${subscription.id}`)"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ subscription.email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                {{ subscription.name || '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs font-medium',
                                        subscription.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                    ]"
                                >
                                    {{ subscription.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">
                                {{ formatDate(subscription.subscribed_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1.5 flex-wrap" @click.stop>
                                    <!-- View Button -->
                                    <Link
                                        :href="`/admin/newsletter/${subscription.id}`"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                                        title="View subscription details"
                                    >
                                        <Eye :size="14" />
                                        <span>View</span>
                                    </Link>

                                    <!-- Toggle Status Button -->
                                    <button
                                        @click="toggleStatus(subscription.id, subscription.is_active)"
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors',
                                            subscription.is_active
                                                ? 'border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-950/30 hover:bg-orange-100 dark:hover:bg-orange-900/50'
                                                : 'border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-950/30 hover:bg-green-100 dark:hover:bg-green-900/50'
                                        ]"
                                        :title="subscription.is_active ? 'Deactivate subscription' : 'Activate subscription'"
                                    >
                                        <component :is="subscription.is_active ? ToggleLeft : ToggleRight" :size="14" />
                                        <span>{{ subscription.is_active ? 'Deactivate' : 'Activate' }}</span>
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        @click="deleteSubscription(subscription.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/30 hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors"
                                        title="Delete subscription"
                                    >
                                        <Trash2 :size="14" />
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="subscriptions.data.length === 0" class="p-8 text-center text-gray-500">
                    No newsletter subscriptions found.
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="subscriptions.last_page > 1" class="flex items-center justify-center gap-2 mt-4">
                <Link
                    v-for="(link, index) in subscriptions.links"
                    :key="index"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-1 rounded',
                        link.active
                            ? 'bg-blue-600 text-white'
                            : link.url
                                ? 'bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700'
                                : 'bg-gray-100 dark:bg-gray-900 text-gray-400 cursor-not-allowed',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>
