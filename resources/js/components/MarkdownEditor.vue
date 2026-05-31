<script setup lang="ts">
import { ref, computed, nextTick, watch } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/monokai.css';
import { Paperclip, Eye, Pencil } from 'lucide-vue-next';

const props = defineProps<{
    slug: string;
}>();

const model = defineModel<string>({ required: true });

const showPreview = ref(false);
const uploading = ref(false);
const uploadError = ref<string | null>(null);
const lastUploaded = ref<string | null>(null);
const textarea = ref<HTMLTextAreaElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const previewEl = ref<HTMLElement | null>(null);

marked.setOptions({ breaks: true, gfm: true });

// Resolve a bare file name to the post's asset route (matches the backend).
function resolveAssetUrls(html: string): string {
    if (!props.slug) return html;
    return html.replace(/\b(src|href)="([^"]*)"/gi, (full, attr: string, url: string) => {
        if (url === '' || /^([a-z][a-z0-9+.\-]*:|\/\/|\/|#)/i.test(url)) return full;
        return `${attr}="/posts/${props.slug}/assets/${url.replace(/^\.?\//, '')}"`;
    });
}

// Strip the YAML front matter before rendering the client-side preview.
const body = computed(() => model.value.replace(/^---\n[\s\S]*?\n---\n?/, ''));
const rendered = computed(() => {
    try {
        return resolveAssetUrls(marked.parse(body.value) as string);
    } catch {
        return '<p class="text-red-500">Erro ao renderizar markdown</p>';
    }
});

watch([showPreview, rendered], async () => {
    if (!showPreview.value) return;
    await nextTick();
    previewEl.value?.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block as HTMLElement);
    });
});

function readCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp('(^|;\\s*)' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[2]) : null;
}

function insertAtCursor(text: string) {
    const el = textarea.value;
    if (!el) {
        model.value += `\n${text}\n`;
        return;
    }
    const start = el.selectionStart;
    const end = el.selectionEnd;
    model.value = model.value.slice(0, start) + text + model.value.slice(end);
    nextTick(() => {
        el.focus();
        el.selectionStart = el.selectionEnd = start + text.length;
    });
}

function isImage(name: string): boolean {
    return /\.(png|jpe?g|gif|webp|svg)$/i.test(name);
}

async function onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    if (!props.slug) {
        uploadError.value = 'Defina o slug do post antes de enviar arquivos.';
        input.value = '';
        return;
    }

    uploading.value = true;
    uploadError.value = null;
    lastUploaded.value = null;

    try {
        const data = new FormData();
        data.append('file', file);

        const response = await fetch(`/admin/posts/${props.slug}/upload-asset`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': readCookie('XSRF-TOKEN') ?? '',
                Accept: 'application/json',
            },
            body: data,
        });

        if (!response.ok) {
            throw new Error('Falha no upload do arquivo.');
        }

        const json = (await response.json()) as { name: string };
        lastUploaded.value = json.name;
        const label = file.name.replace(/\.[^.]+$/, '');
        insertAtCursor(isImage(json.name) ? `![${label}](${json.name})` : `[📄 ${file.name}](${json.name})`);
    } catch (error) {
        uploadError.value = error instanceof Error ? error.message : 'Erro ao enviar arquivo.';
    } finally {
        uploading.value = false;
        input.value = '';
    }
}
</script>

<template>
    <div class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-2 border-b border-sidebar-border/70 p-2 dark:border-sidebar-border">
            <button
                type="button"
                @click="fileInput?.click()"
                :disabled="uploading"
                class="inline-flex items-center gap-1.5 rounded-md border border-sidebar-border/70 px-3 py-1.5 text-xs font-medium hover:bg-accent disabled:opacity-50 dark:border-sidebar-border"
            >
                <Paperclip :size="14" />
                <span>{{ uploading ? 'Enviando...' : 'Upload arquivo' }}</span>
            </button>
            <input
                ref="fileInput"
                type="file"
                accept="image/*,.pdf,.txt,.zip"
                class="hidden"
                @change="onFileSelected"
            />

            <div class="ml-auto flex overflow-hidden rounded-md border border-sidebar-border/70 dark:border-sidebar-border">
                <button
                    type="button"
                    @click="showPreview = false"
                    :class="['inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium', !showPreview ? 'bg-blue-600 text-white' : 'hover:bg-accent']"
                >
                    <Pencil :size="14" /> Editar
                </button>
                <button
                    type="button"
                    @click="showPreview = true"
                    :class="['inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium', showPreview ? 'bg-blue-600 text-white' : 'hover:bg-accent']"
                >
                    <Eye :size="14" /> Preview
                </button>
            </div>
        </div>

        <!-- Uploaded file hint -->
        <div v-if="lastUploaded" class="border-b border-sidebar-border/70 bg-green-50 px-3 py-2 text-xs dark:border-sidebar-border dark:bg-green-950/30">
            Arquivo salvo na pasta do post — referência inserida:
            <code class="select-all">{{ lastUploaded }}</code>
        </div>
        <div v-if="uploadError" class="border-b border-sidebar-border/70 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-sidebar-border dark:bg-red-950/30 dark:text-red-300">
            {{ uploadError }}
        </div>

        <!-- Editor -->
        <textarea
            v-show="!showPreview"
            ref="textarea"
            v-model="model"
            spellcheck="false"
            placeholder="---&#10;title: ...&#10;---&#10;&#10;## Conteúdo"
            class="block min-h-[28rem] w-full resize-y bg-background px-4 py-3 font-mono text-sm leading-relaxed outline-none"
        ></textarea>

        <!-- Preview -->
        <div
            v-show="showPreview"
            ref="previewEl"
            class="prose prose-sm dark:prose-invert min-h-[28rem] max-w-none px-4 py-3 prose-pre:bg-slate-900 prose-pre:p-4 prose-img:rounded-lg"
            v-html="rendered"
        ></div>
    </div>
</template>
