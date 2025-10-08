<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';

interface Profile {
    id: number;
    name: string;
}

const props = defineProps<{
    profiles: Profile[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Links', href: '/admin/links' },
    { title: 'Create', href: '/admin/links/create' },
];

const form = useForm({
    profile_id: props.profiles[0]?.id || null,
    title: '',
    description: '',
    url: '',
    icon: '',
    order: 0,
    is_active: true,
});

const submit = () => {
    form.post('/admin/links');
};
</script>

<template>
    <Head title="Create Link" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1 class="text-2xl font-bold">Create Link</h1>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div>
                    <label for="profile_id" class="block text-sm font-medium mb-2">Profile</label>
                    <select
                        id="profile_id"
                        v-model="form.profile_id"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                    >
                        <option v-for="profile in profiles" :key="profile.id" :value="profile.id">
                            {{ profile.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.profile_id" />
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium mb-2">Title</label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                        required
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium mb-2">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                    ></textarea>
                    <InputError :message="form.errors.description" />
                </div>

                <div>
                    <label for="url" class="block text-sm font-medium mb-2">URL</label>
                    <input
                        id="url"
                        v-model="form.url"
                        type="url"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                        required
                    />
                    <InputError :message="form.errors.url" />
                </div>

                <div>
                    <label for="icon" class="block text-sm font-medium mb-2">Icon (emoji or text)</label>
                    <input
                        id="icon"
                        v-model="form.icon"
                        type="text"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                    />
                    <InputError :message="form.errors.icon" />
                </div>

                <div>
                    <label for="order" class="block text-sm font-medium mb-2">Order</label>
                    <input
                        id="order"
                        v-model.number="form.order"
                        type="number"
                        min="0"
                        class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                    />
                    <InputError :message="form.errors.order" />
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-sidebar-border/70 dark:border-sidebar-border"
                    />
                    <label for="is_active" class="text-sm font-medium">Active</label>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        Create Link
                    </button>
                    <a
                        href="/admin/links"
                        class="rounded-md border border-sidebar-border/70 px-4 py-2 hover:bg-sidebar-accent dark:border-sidebar-border"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
