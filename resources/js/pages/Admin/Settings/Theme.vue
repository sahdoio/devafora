<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

interface Setting {
    id: number;
    key: string;
    value: string;
    type: string;
    group: string;
}

const props = defineProps<{
    settings: Setting[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Theme Settings', href: '/admin/settings/theme' },
];

// Group settings by category
const colorCategories = {
    'Theme Colors': ['color_primary', 'color_secondary', 'color_accent', 'color_success', 'color_warning', 'color_danger'],
    'Background Colors': ['color_bg_primary', 'color_bg_secondary', 'color_bg_card'],
    'Text Colors': ['color_text_primary', 'color_text_secondary', 'color_text_muted'],
    'Border Colors': ['color_border', 'color_border_light'],
};

const form = useForm({
    settings: props.settings.map(s => ({ key: s.key, value: s.value })),
});

const getColorLabel = (key: string) => {
    return key
        .replace('color_', '')
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const getSettingValue = (key: string) => {
    const setting = form.settings.find(s => s.key === key);
    return setting?.value || '#000000';
};

const updateColor = (key: string, value: string) => {
    const index = form.settings.findIndex(s => s.key === key);
    if (index !== -1) {
        form.settings[index].value = value;
    }
};

const submit = () => {
    form.put('/admin/settings/theme');
};
</script>

<template>
    <Head title="Theme Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <h1 class="text-2xl font-bold">Theme Settings</h1>

            <form @submit.prevent="submit" class="max-w-4xl space-y-8">
                <div v-for="(keys, category) in colorCategories" :key="category" class="space-y-4">
                    <h2 class="text-lg font-semibold border-b border-sidebar-border/70 pb-2">{{ category }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="key in keys" :key="key" class="space-y-2">
                            <label :for="key" class="block text-sm font-medium">
                                {{ getColorLabel(key) }}
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    :id="key"
                                    type="color"
                                    :value="getSettingValue(key)"
                                    @input="(e) => updateColor(key, (e.target as HTMLInputElement).value)"
                                    class="h-10 w-20 rounded border border-sidebar-border/70 cursor-pointer"
                                />
                                <input
                                    type="text"
                                    :value="getSettingValue(key)"
                                    @input="(e) => updateColor(key, (e.target as HTMLInputElement).value)"
                                    class="flex-1 rounded-md border border-sidebar-border/70 bg-background px-3 py-2 font-mono text-sm"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                    placeholder="#000000"
                                />
                            </div>
                            <InputError :message="form.errors[`settings.${key}`]" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 border-t border-sidebar-border/70 pt-6">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <a
                        href="/admin/dashboard"
                        class="rounded-md border border-sidebar-border/70 px-6 py-2 hover:bg-sidebar-accent"
                    >
                        Cancel
                    </a>
                </div>
            </form>

            <!-- Preview Section -->
            <div class="mt-8 space-y-4">
                <h2 class="text-xl font-bold border-b border-sidebar-border/70 pb-2">Preview</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        v-for="setting in form.settings"
                        :key="setting.key"
                        class="p-4 rounded-lg border border-sidebar-border/70 space-y-2"
                    >
                        <div
                            class="w-full h-16 rounded"
                            :style="{ backgroundColor: setting.value }"
                        ></div>
                        <p class="text-xs font-mono">{{ setting.key }}</p>
                        <p class="text-xs font-mono text-gray-500">{{ setting.value }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
