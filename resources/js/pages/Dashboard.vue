<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Mail, FileText, Link2, TrendingUp, Users, CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface DashboardStats {
    posts: {
        total: number;
        published: number;
        draft: number;
    };
    links: {
        total: number;
        active: number;
    };
    newsletter: {
        total: number;
        active: number;
        recent: number;
        growth: Array<{ date: string; count: number }>;
    };
}

const props = defineProps<{
    stats: DashboardStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const maxGrowthValue = computed(() => {
    if (props.stats.newsletter.growth.length === 0) return 10;
    return Math.max(...props.stats.newsletter.growth.map(g => g.count), 5);
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Newsletter Card -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Newsletter Subscribers</p>
                            <h3 class="text-3xl font-bold mt-2">{{ stats.newsletter.total }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                    <TrendingUp :size="14" />
                                    {{ stats.newsletter.recent }} this week
                                </span>
                            </div>
                        </div>
                        <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3">
                            <Mail class="text-blue-600 dark:text-blue-400" :size="24" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-sidebar-border/70 dark:border-sidebar-border">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Active</span>
                            <span class="font-medium">{{ stats.newsletter.active }}</span>
                        </div>
                    </div>
                </div>

                <!-- Posts Card -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Posts</p>
                            <h3 class="text-3xl font-bold mt-2">{{ stats.posts.total }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs text-muted-foreground">
                                    {{ stats.posts.published }} published
                                </span>
                            </div>
                        </div>
                        <div class="rounded-full bg-purple-100 dark:bg-purple-900 p-3">
                            <FileText class="text-purple-600 dark:text-purple-400" :size="24" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-sidebar-border/70 dark:border-sidebar-border">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Drafts</span>
                            <span class="font-medium">{{ stats.posts.draft }}</span>
                        </div>
                    </div>
                </div>

                <!-- Links Card -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Links</p>
                            <h3 class="text-3xl font-bold mt-2">{{ stats.links.total }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                    <CheckCircle :size="14" />
                                    {{ stats.links.active }} active
                                </span>
                            </div>
                        </div>
                        <div class="rounded-full bg-green-100 dark:bg-green-900 p-3">
                            <Link2 class="text-green-600 dark:text-green-400" :size="24" />
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-sidebar-border/70 dark:border-sidebar-border">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Inactive</span>
                            <span class="font-medium">{{ stats.links.total - stats.links.active }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter Growth Chart -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold">Newsletter Growth</h3>
                        <p class="text-sm text-muted-foreground">New subscriptions in the last 30 days</p>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <Users :size="16" class="text-muted-foreground" />
                        <span class="font-medium">{{ stats.newsletter.growth.reduce((sum, g) => sum + g.count, 0) }} total</span>
                    </div>
                </div>

                <div v-if="stats.newsletter.growth.length > 0" class="relative h-64">
                    <!-- Y-axis labels -->
                    <div class="absolute left-0 top-0 bottom-0 flex flex-col justify-between text-xs text-muted-foreground pr-2">
                        <span>{{ maxGrowthValue }}</span>
                        <span>{{ Math.floor(maxGrowthValue / 2) }}</span>
                        <span>0</span>
                    </div>

                    <!-- Chart area -->
                    <div class="ml-8 h-full flex items-end justify-between gap-1">
                        <div
                            v-for="(item, index) in stats.newsletter.growth"
                            :key="index"
                            class="flex-1 flex flex-col items-center gap-2"
                        >
                            <!-- Bar -->
                            <div class="w-full flex items-end justify-center" style="height: 200px;">
                                <div
                                    class="w-full bg-blue-500 dark:bg-blue-600 rounded-t hover:bg-blue-600 dark:hover:bg-blue-500 transition-colors cursor-pointer"
                                    :style="{
                                        height: `${(item.count / maxGrowthValue) * 100}%`,
                                        minHeight: item.count > 0 ? '4px' : '0'
                                    }"
                                    :title="`${formatDate(item.date)}: ${item.count} subscription${item.count !== 1 ? 's' : ''}`"
                                >
                                    <div v-if="item.count > 0" class="text-xs text-white text-center -mt-5">
                                        {{ item.count }}
                                    </div>
                                </div>
                            </div>
                            <!-- Date label -->
                            <span class="text-xs text-muted-foreground whitespace-nowrap">
                                {{ formatDate(item.date) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="h-64 flex items-center justify-center text-muted-foreground">
                    No subscription data available for the last 30 days
                </div>
            </div>
        </div>
    </AppLayout>
</template>
