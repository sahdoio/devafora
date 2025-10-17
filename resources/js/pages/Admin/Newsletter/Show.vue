<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

interface NewsletterSubscription {
    id: number;
    email: string;
    name: string | null;
    is_active: boolean;
    subscribed_at: string;
    unsubscribed_at: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    subscription: NewsletterSubscription;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Newsletter', href: '/admin/newsletter' },
    { title: props.subscription.email, href: `/admin/newsletter/${props.subscription.id}` },
];

const deleteSubscription = () => {
    if (confirm('Are you sure you want to delete this subscription?')) {
        router.delete(`/admin/newsletter/${props.subscription.id}`);
    }
};

const toggleStatus = () => {
    if (confirm('Are you sure you want to change the status of this subscription?')) {
        router.post(`/admin/newsletter/${props.subscription.id}/toggle`);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Newsletter - ${subscription.email}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Newsletter Subscription Details</h1>
                <div class="flex gap-2">
                    <Link
                        href="/admin/newsletter"
                        class="rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                    >
                        Back to List
                    </Link>
                    <button
                        @click="toggleStatus"
                        class="rounded-md px-4 py-2 text-white"
                        :class="subscription.is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700'"
                    >
                        {{ subscription.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                        @click="deleteSubscription"
                        class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Email -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Email</label>
                        <p class="text-lg mt-1">{{ subscription.email }}</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Name</label>
                        <p class="text-lg mt-1">{{ subscription.name || '-' }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Status</label>
                        <div class="mt-1">
                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-sm font-medium inline-block',
                                    subscription.is_active
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                                ]"
                            >
                                {{ subscription.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <!-- ID -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">ID</label>
                        <p class="text-lg mt-1 font-mono">{{ subscription.id }}</p>
                    </div>

                    <!-- Subscribed At -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Subscribed At</label>
                        <p class="text-lg mt-1">{{ formatDate(subscription.subscribed_at) }}</p>
                    </div>

                    <!-- Unsubscribed At -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Unsubscribed At</label>
                        <p class="text-lg mt-1">
                            {{ subscription.unsubscribed_at ? formatDate(subscription.unsubscribed_at) : '-' }}
                        </p>
                    </div>

                    <!-- Created At -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Created At</label>
                        <p class="text-lg mt-1">{{ formatDate(subscription.created_at) }}</p>
                    </div>

                    <!-- Updated At -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Updated At</label>
                        <p class="text-lg mt-1">{{ formatDate(subscription.updated_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Subscription History Card -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h2 class="text-xl font-semibold mb-4">Subscription History</h2>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                        <div>
                            <p class="font-medium">Subscription Created</p>
                            <p class="text-sm text-muted-foreground">{{ formatDate(subscription.created_at) }}</p>
                        </div>
                    </div>

                    <div v-if="subscription.subscribed_at" class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                        <div>
                            <p class="font-medium">Subscribed</p>
                            <p class="text-sm text-muted-foreground">{{ formatDate(subscription.subscribed_at) }}</p>
                        </div>
                    </div>

                    <div v-if="subscription.unsubscribed_at" class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-red-500 mt-2"></div>
                        <div>
                            <p class="font-medium">Unsubscribed</p>
                            <p class="text-sm text-muted-foreground">{{ formatDate(subscription.unsubscribed_at) }}</p>
                        </div>
                    </div>

                    <div v-if="subscription.updated_at !== subscription.created_at" class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-gray-500 mt-2"></div>
                        <div>
                            <p class="font-medium">Last Updated</p>
                            <p class="text-sm text-muted-foreground">{{ formatDate(subscription.updated_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
