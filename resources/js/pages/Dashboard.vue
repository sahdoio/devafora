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

const chartHeight = 200;
const chartPadding = 40;

// Generate SVG path for the area chart
const areaPath = computed(() => {
    if (props.stats.newsletter.growth.length === 0) return '';

    const data = props.stats.newsletter.growth;
    const width = 100; // percentage
    const step = width / (data.length - 1 || 1);

    const points = data.map((item, index) => {
        const x = index * step;
        const y = chartHeight - ((item.count / maxGrowthValue.value) * (chartHeight - chartPadding));
        return { x, y, count: item.count };
    });

    // Create the area path
    let path = `M 0 ${chartHeight}`;

    // Line to first point
    if (points.length > 0) {
        path += ` L 0 ${points[0].y}`;
    }

    // Smooth curve through points using quadratic bezier curves
    for (let i = 0; i < points.length - 1; i++) {
        const current = points[i];
        const next = points[i + 1];
        const midX = (current.x + next.x) / 2;

        path += ` Q ${current.x} ${current.y}, ${midX} ${(current.y + next.y) / 2}`;
    }

    // Last point
    if (points.length > 0) {
        const last = points[points.length - 1];
        path += ` Q ${last.x} ${last.y}, ${last.x} ${last.y}`;
        path += ` L ${last.x} ${chartHeight}`;
    }

    path += ' Z';
    return path;
});

// Generate SVG path for the line
const linePath = computed(() => {
    if (props.stats.newsletter.growth.length === 0) return '';

    const data = props.stats.newsletter.growth;
    const width = 100;
    const step = width / (data.length - 1 || 1);

    const points = data.map((item, index) => {
        const x = index * step;
        const y = chartHeight - ((item.count / maxGrowthValue.value) * (chartHeight - chartPadding));
        return { x, y };
    });

    let path = `M ${points[0].x} ${points[0].y}`;

    for (let i = 0; i < points.length - 1; i++) {
        const current = points[i];
        const next = points[i + 1];
        const midX = (current.x + next.x) / 2;

        path += ` Q ${current.x} ${current.y}, ${midX} ${(current.y + next.y) / 2}`;
    }

    const last = points[points.length - 1];
    path += ` Q ${last.x} ${last.y}, ${last.x} ${last.y}`;

    return path;
});

// Points for circles
const chartPoints = computed(() => {
    if (props.stats.newsletter.growth.length === 0) return [];

    const data = props.stats.newsletter.growth;
    const width = 100;
    const step = width / (data.length - 1 || 1);

    return data.map((item, index) => ({
        x: index * step,
        y: chartHeight - ((item.count / maxGrowthValue.value) * (chartHeight - chartPadding)),
        count: item.count,
        date: item.date,
    }));
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
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/30 dark:to-transparent">
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
                        <div class="rounded-full bg-blue-500 dark:bg-blue-600 p-3 shadow-lg shadow-blue-500/30">
                            <Mail class="text-white" :size="24" />
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
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/30 dark:to-transparent">
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
                        <div class="rounded-full bg-purple-500 dark:bg-purple-600 p-3 shadow-lg shadow-purple-500/30">
                            <FileText class="text-white" :size="24" />
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
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 bg-gradient-to-br from-green-50 to-white dark:from-green-950/30 dark:to-transparent">
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
                        <div class="rounded-full bg-green-500 dark:bg-green-600 p-3 shadow-lg shadow-green-500/30">
                            <Link2 class="text-white" :size="24" />
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
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 bg-gradient-to-br from-slate-50 to-white dark:from-slate-950/30 dark:to-transparent">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold">Newsletter Growth</h3>
                        <p class="text-sm text-muted-foreground">New subscriptions in the last 30 days</p>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800">
                        <Users :size="16" class="text-blue-600 dark:text-blue-400" />
                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                            {{ stats.newsletter.growth.reduce((sum, g) => sum + g.count, 0) }}
                        </span>
                        <span class="text-sm text-muted-foreground">total</span>
                    </div>
                </div>

                <div v-if="stats.newsletter.growth.length > 0" class="relative" style="height: 280px;">
                    <!-- Y-axis labels -->
                    <div class="absolute left-0 top-0 bottom-8 flex flex-col justify-between text-xs text-muted-foreground pr-3 pt-4">
                        <span class="font-medium">{{ maxGrowthValue }}</span>
                        <span>{{ Math.floor(maxGrowthValue / 2) }}</span>
                        <span>0</span>
                    </div>

                    <!-- SVG Chart -->
                    <div class="ml-10 h-full relative">
                        <svg class="w-full h-full" viewBox="0 0 100 200" preserveAspectRatio="none">
                            <!-- Grid lines -->
                            <line x1="0" y1="40" x2="100" y2="40" stroke="currentColor" class="text-gray-200 dark:text-gray-800" stroke-width="0.2" opacity="0.5" />
                            <line x1="0" y1="120" x2="100" y2="120" stroke="currentColor" class="text-gray-200 dark:text-gray-800" stroke-width="0.2" opacity="0.5" />
                            <line x1="0" y1="200" x2="100" y2="200" stroke="currentColor" class="text-gray-200 dark:text-gray-800" stroke-width="0.3" />

                            <!-- Gradient definition -->
                            <defs>
                                <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.4" />
                                    <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:0.05" />
                                </linearGradient>
                            </defs>

                            <!-- Area under the curve -->
                            <path
                                :d="areaPath"
                                fill="url(#areaGradient)"
                                class="transition-all duration-300"
                            />

                            <!-- Line -->
                            <path
                                :d="linePath"
                                fill="none"
                                stroke="currentColor"
                                class="text-blue-500 dark:text-blue-400"
                                stroke-width="0.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <!-- Data points -->
                            <g v-for="(point, index) in chartPoints" :key="index">
                                <circle
                                    :cx="point.x"
                                    :cy="point.y"
                                    r="1.2"
                                    class="text-blue-500 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors cursor-pointer"
                                    fill="currentColor"
                                    stroke="white"
                                    stroke-width="0.5"
                                >
                                    <title>{{ formatDate(point.date) }}: {{ point.count }} subscription{{ point.count !== 1 ? 's' : '' }}</title>
                                </circle>
                            </g>
                        </svg>

                        <!-- X-axis labels -->
                        <div class="flex justify-between mt-2 px-1">
                            <span
                                v-for="(item, index) in stats.newsletter.growth.filter((_, i) => i % Math.ceil(stats.newsletter.growth.length / 6) === 0)"
                                :key="index"
                                class="text-xs text-muted-foreground font-medium"
                            >
                                {{ formatDate(item.date) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="h-64 flex items-center justify-center text-muted-foreground">
                    <div class="text-center">
                        <Users :size="48" class="mx-auto mb-3 opacity-20" />
                        <p>No subscription data available for the last 30 days</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
