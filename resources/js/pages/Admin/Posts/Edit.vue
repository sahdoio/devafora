<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { ref, computed } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/monokai.css';

interface Profile {
    id: number;
    name: string;
}

interface PostItem {
    id: number;
    profile_id: number;
    title: string;
    excerpt: string;
    content: string;
    author: string;
    image: string;
    read_time: number;
    tags: string[];
    is_published: boolean;
}

const props = defineProps<{
    post: PostItem;
    profiles: Profile[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Posts', href: '/admin/posts' },
    { title: 'Edit', href: `/admin/posts/${props.post.id}/edit` },
];

// Configure marked to allow HTML
marked.setOptions({
    highlight: (code, lang) => {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value;
            } catch (err) {
                console.error(err);
            }
        }
        return hljs.highlightAuto(code).value;
    },
    breaks: true,
    gfm: true,
});

// Allow raw HTML in markdown
marked.use({
    mangle: false,
    headerIds: false,
});

const form = useForm({
    profile_id: props.post.profile_id,
    title: props.post.title,
    excerpt: props.post.excerpt,
    content: props.post.content,
    author: props.post.author,
    image: props.post.image || '',
    read_time: props.post.read_time || 5,
    tags: props.post.tags || [],
    is_published: props.post.is_published,
});

const tagInput = ref('');
const showPreview = ref(true);
const contentTextarea = ref<HTMLTextAreaElement>();
const selectedColor = ref('#3b82f6');
const processing = ref(false);
const errors = ref<Record<string, string>>({});

const htmlPreview = computed(() => {
    try {
        const content = form.content || '';
        // If content contains HTML tags, return it directly
        // Otherwise, parse it as markdown
        if (content.includes('<') && content.includes('>')) {
            return content;
        }
        return marked(content);
    } catch (error) {
        return '<p class="text-red-500">Error parsing markdown</p>';
    }
});

const addTag = () => {
    if (tagInput.value && !form.tags.includes(tagInput.value)) {
        form.tags.push(tagInput.value);
        tagInput.value = '';
    }
};

const removeTag = (tag: string) => {
    form.tags = form.tags.filter((t) => t !== tag);
};

const insertColoredText = () => {
    const textarea = contentTextarea.value;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = form.content.substring(start, end) || 'colored text';

    const insertion = `<span style="color: ${selectedColor.value}">${selectedText}</span>`;

    form.content =
        form.content.substring(0, start) +
        insertion +
        form.content.substring(end);

    setTimeout(() => {
        textarea.focus();
        const newPos = start + insertion.length;
        textarea.setSelectionRange(newPos, newPos);
    }, 0);
};

const quickColorInsert = (color: string, colorName: string) => {
    const textarea = contentTextarea.value;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = form.content.substring(start, end) || colorName;

    const insertion = `<span style="color: ${color}">${selectedText}</span>`;

    form.content =
        form.content.substring(0, start) +
        insertion +
        form.content.substring(end);

    setTimeout(() => {
        textarea.focus();
        const newPos = start + insertion.length;
        textarea.setSelectionRange(newPos, newPos);
    }, 0);
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('profile_id', String(form.profile_id));
    formData.append('title', form.title);
    formData.append('author', form.author);
    formData.append('excerpt', form.excerpt);
    formData.append('content', form.content);

    // Only append image if it's a File object (new upload)
    if (form.image && typeof form.image !== 'string') {
        formData.append('image', form.image);
    }

    formData.append('read_time', String(form.read_time));
    form.tags.forEach((tag, index) => {
        formData.append(`tags[${index}]`, tag);
    });
    formData.append('is_published', form.is_published ? '1' : '0');
    formData.append('_method', 'PUT');

    router.post(`/admin/posts/${props.post.id}`, formData, {
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onSuccess: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Edit Post" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 gap-4 overflow-x-auto p-4">
            <!-- Left Column - Form -->
            <div class="flex-1 flex flex-col gap-4 overflow-y-auto">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Edit Post</h1>
                    <button
                        type="button"
                        @click="showPreview = !showPreview"
                        class="rounded-md bg-gray-600 px-4 py-2 text-sm text-white hover:bg-gray-700 lg:hidden"
                    >
                        {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6 pb-8">
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
                        <label for="author" class="block text-sm font-medium mb-2">Author</label>
                        <input
                            id="author"
                            v-model="form.author"
                            type="text"
                            class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                            required
                        />
                        <InputError :message="form.errors.author" />
                    </div>

                    <div>
                        <label for="excerpt" class="block text-sm font-medium mb-2">Excerpt</label>
                        <textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                            required
                        ></textarea>
                        <InputError :message="form.errors.excerpt" />
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium mb-2">Content (Markdown)</label>

                        <!-- Color Picker Toolbar -->
                        <div class="mb-2 p-3 rounded-lg border border-sidebar-border/70 bg-sidebar-accent/30 space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-medium">Quick Colors:</span>
                                <button
                                    type="button"
                                    @click="quickColorInsert('#3b82f6', 'Primary')"
                                    class="px-3 py-1 text-xs rounded"
                                    style="background-color: #3b82f6; color: white"
                                    title="Primary Blue"
                                >
                                    Primary
                                </button>
                                <button
                                    type="button"
                                    @click="quickColorInsert('#10b981', 'Success')"
                                    class="px-3 py-1 text-xs rounded"
                                    style="background-color: #10b981; color: white"
                                    title="Success Green"
                                >
                                    Success
                                </button>
                                <button
                                    type="button"
                                    @click="quickColorInsert('#f59e0b', 'Warning')"
                                    class="px-3 py-1 text-xs rounded"
                                    style="background-color: #f59e0b; color: white"
                                    title="Warning Orange"
                                >
                                    Warning
                                </button>
                                <button
                                    type="button"
                                    @click="quickColorInsert('#ef4444', 'Danger')"
                                    class="px-3 py-1 text-xs rounded"
                                    style="background-color: #ef4444; color: white"
                                    title="Danger Red"
                                >
                                    Danger
                                </button>
                                <button
                                    type="button"
                                    @click="quickColorInsert('#8b5cf6', 'Purple')"
                                    class="px-3 py-1 text-xs rounded"
                                    style="background-color: #8b5cf6; color: white"
                                    title="Purple"
                                >
                                    Purple
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium">Custom:</span>
                                <input
                                    type="color"
                                    v-model="selectedColor"
                                    class="h-8 w-16 rounded border border-sidebar-border/70 cursor-pointer"
                                />
                                <input
                                    type="text"
                                    v-model="selectedColor"
                                    class="w-24 rounded border border-sidebar-border/70 bg-background px-2 py-1 text-xs font-mono"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                />
                                <button
                                    type="button"
                                    @click="insertColoredText"
                                    class="px-3 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700"
                                >
                                    Insert Colored Text
                                </button>
                                <span class="text-xs text-gray-500">Select text in editor, then click to color it</span>
                            </div>
                        </div>

                        <textarea
                            ref="contentTextarea"
                            id="content"
                            v-model="form.content"
                            rows="25"
                            class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 font-mono text-sm dark:border-sidebar-border"
                            required
                            placeholder="Write your content in Markdown...&#10;&#10;## Heading 2&#10;&#10;**Bold text** and *italic text*&#10;&#10;```javascript&#10;const code = 'here';&#10;```"
                        ></textarea>
                        <InputError :message="form.errors.content" />
                        <p class="mt-1 text-xs text-gray-500">
                            Use Markdown syntax. For colored text, select text and use color buttons above.
                        </p>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium mb-2">Image URL</label>
                        <input
                            id="image"
                            v-model="form.image"
                            type="url"
                            class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                        />
                        <InputError :message="form.errors.image" />
                    </div>

                    <div>
                        <label for="read_time" class="block text-sm font-medium mb-2">Read Time (minutes)</label>
                        <input
                            id="read_time"
                            v-model.number="form.read_time"
                            type="number"
                            min="1"
                            class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                        />
                        <InputError :message="form.errors.read_time" />
                    </div>

                    <div>
                        <label for="tags" class="block text-sm font-medium mb-2">Tags</label>
                        <div class="flex gap-2 mb-2">
                            <input
                                v-model="tagInput"
                                type="text"
                                placeholder="Add a tag..."
                                @keyup.enter.prevent="addTag"
                                class="flex-1 rounded-md border border-sidebar-border/70 bg-background px-3 py-2 dark:border-sidebar-border"
                            />
                            <button
                                type="button"
                                @click="addTag"
                                class="rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                            >
                                Add
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in form.tags"
                                :key="tag"
                                class="flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                {{ tag }}
                                <button
                                    type="button"
                                    @click="removeTag(tag)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-100"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                        <InputError :message="form.errors.tags" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_published"
                            v-model="form.is_published"
                            type="checkbox"
                            class="h-4 w-4 rounded border-sidebar-border/70 dark:border-sidebar-border"
                        />
                        <label for="is_published" class="text-sm font-medium">Published</label>
                    </div>

                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            Update Post
                        </button>
                        <a
                            href="/admin/posts"
                            class="rounded-md border border-sidebar-border/70 px-4 py-2 hover:bg-sidebar-accent dark:border-sidebar-border"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Right Column - Live Preview -->
            <div
                v-show="showPreview"
                class="hidden lg:flex lg:w-[500px] xl:w-[600px] flex-col gap-4 sticky top-4 h-[calc(100vh-8rem)] overflow-y-auto"
            >
                <div class="rounded-xl border border-sidebar-border/70 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 p-6 dark:border-sidebar-border">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-700 pb-3">
                        <h2 class="text-lg font-semibold text-white">Live Preview</h2>
                        <span class="rounded-full bg-green-500/20 px-3 py-1 text-xs text-green-400">Live</span>
                    </div>

                    <div v-if="form.title || form.excerpt || form.content" class="space-y-4">
                        <!-- Post Header -->
                        <div v-if="form.title" class="space-y-2">
                            <h1 class="text-3xl font-bold text-white">{{ form.title }}</h1>
                            <div class="flex items-center gap-3 text-sm text-gray-400">
                                <span v-if="form.author">{{ form.author }}</span>
                                <span v-if="form.read_time">• {{ form.read_time }} min read</span>
                            </div>
                        </div>

                        <!-- Banner Image -->
                        <div v-if="form.image" class="rounded-xl overflow-hidden">
                            <img :src="`/storage/${form.image}`" alt="Banner" class="w-full" />
                        </div>

                        <!-- Excerpt -->
                        <p v-if="form.excerpt" class="text-lg italic text-gray-300 border-l-4 border-blue-500 pl-4">
                            {{ form.excerpt }}
                        </p>

                        <!-- Tags -->
                        <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in form.tags"
                                :key="tag"
                                class="rounded-full bg-blue-100 px-3 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                {{ tag }}
                            </span>
                        </div>

                        <!-- Markdown Content -->
                        <div
                            v-if="form.content"
                            class="prose prose-invert prose-sm max-w-none prose-headings:text-white prose-p:text-gray-300 prose-a:text-blue-400 prose-strong:text-white prose-code:rounded prose-code:bg-slate-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-blue-400 prose-pre:overflow-x-auto prose-pre:rounded-xl prose-pre:bg-slate-900 prose-pre:p-4 prose-img:rounded-xl prose-blockquote:border-l-blue-500 prose-blockquote:bg-slate-800/50 prose-blockquote:text-gray-300"
                            v-html="htmlPreview"
                        ></div>
                    </div>

                    <div v-else class="flex h-64 items-center justify-center text-gray-500">
                        <p class="text-center">
                            Start writing to see your post preview here<br />
                            <span class="text-sm">All changes appear in real-time</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
