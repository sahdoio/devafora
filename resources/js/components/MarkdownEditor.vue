<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/monokai.css';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const activeTab = ref<'write' | 'preview'>('write');
const textareaRef = ref<HTMLTextAreaElement>();
const containerRef = ref<HTMLDivElement>();
const editorWidth = ref(50); // percentage
const isDragging = ref(false);

// Configure marked
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

const content = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value),
});

const htmlPreview = computed(() => {
    try {
        return marked(content.value || '');
    } catch (error) {
        return '<p class="text-red-500">Error parsing markdown</p>';
    }
});

const insertMarkdown = (type: string) => {
    const textarea = textareaRef.value;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = content.value.substring(start, end) || 'text';
    let insertion = '';
    let cursorOffset = 0;

    switch (type) {
        case 'h1':
            insertion = `# ${selectedText}`;
            cursorOffset = insertion.length;
            break;
        case 'h2':
            insertion = `## ${selectedText}`;
            cursorOffset = insertion.length;
            break;
        case 'h3':
            insertion = `### ${selectedText}`;
            cursorOffset = insertion.length;
            break;
        case 'bold':
            insertion = `**${selectedText}**`;
            cursorOffset = selectedText === 'text' ? 2 : insertion.length;
            break;
        case 'italic':
            insertion = `*${selectedText}*`;
            cursorOffset = selectedText === 'text' ? 1 : insertion.length;
            break;
        case 'code':
            insertion = `\`${selectedText}\``;
            cursorOffset = selectedText === 'text' ? 1 : insertion.length;
            break;
        case 'link':
            insertion = `[${selectedText}](url)`;
            cursorOffset = insertion.length - 4;
            break;
        case 'image':
            insertion = `![${selectedText}](image-url)`;
            cursorOffset = insertion.length - 11;
            break;
        case 'list':
            insertion = `- ${selectedText}`;
            cursorOffset = insertion.length;
            break;
        case 'quote':
            insertion = `> ${selectedText}`;
            cursorOffset = insertion.length;
            break;
        case 'codeblock':
            insertion = `\n\`\`\`javascript\n${selectedText}\n\`\`\`\n`;
            cursorOffset = insertion.indexOf(selectedText) + selectedText.length;
            break;
    }

    content.value =
        content.value.substring(0, start) +
        insertion +
        content.value.substring(end);

    nextTick(() => {
        textarea.focus();
        textarea.setSelectionRange(start + cursorOffset, start + cursorOffset);
    });
};

// Resizable divider functionality
const startDragging = (e: MouseEvent) => {
    isDragging.value = true;
    e.preventDefault();
};

const onDrag = (e: MouseEvent) => {
    if (!isDragging.value || !containerRef.value) return;

    const container = containerRef.value;
    const containerRect = container.getBoundingClientRect();
    const newWidth = ((e.clientX - containerRect.left) / containerRect.width) * 100;

    // Constrain between 20% and 80%
    editorWidth.value = Math.min(Math.max(newWidth, 20), 80);
};

const stopDragging = () => {
    isDragging.value = false;
};

onMounted(() => {
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDragging);
});

onUnmounted(() => {
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDragging);
});
</script>

<template>
    <div class="flex flex-col h-full">
        <!-- Toolbar -->
        <div class="flex items-center justify-between border-b border-sidebar-border/70 bg-sidebar-accent/50 px-3 py-2 dark:border-sidebar-border">
            <div class="flex flex-wrap gap-1">
                <button
                    type="button"
                    @click="insertMarkdown('h1')"
                    class="rounded px-2 py-1 text-xs font-semibold hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Heading 1"
                >
                    H1
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('h2')"
                    class="rounded px-2 py-1 text-xs font-semibold hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Heading 2"
                >
                    H2
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('h3')"
                    class="rounded px-2 py-1 text-xs font-semibold hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Heading 3"
                >
                    H3
                </button>
                <div class="mx-1 w-px bg-sidebar-border"></div>
                <button
                    type="button"
                    @click="insertMarkdown('bold')"
                    class="rounded px-2 py-1 text-xs font-bold hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Bold"
                >
                    B
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('italic')"
                    class="rounded px-2 py-1 text-xs italic hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Italic"
                >
                    I
                </button>
                <div class="mx-1 w-px bg-sidebar-border"></div>
                <button
                    type="button"
                    @click="insertMarkdown('link')"
                    class="rounded px-2 py-1 text-xs hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Link"
                >
                    🔗
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('image')"
                    class="rounded px-2 py-1 text-xs hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Image"
                >
                    🖼️
                </button>
                <div class="mx-1 w-px bg-sidebar-border"></div>
                <button
                    type="button"
                    @click="insertMarkdown('list')"
                    class="rounded px-2 py-1 text-xs hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="List"
                >
                    ≡
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('quote')"
                    class="rounded px-2 py-1 text-xs hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Quote"
                >
                    "
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('code')"
                    class="rounded px-2 py-1 text-xs hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Inline Code"
                >
                    &lt;/&gt;
                </button>
                <button
                    type="button"
                    @click="insertMarkdown('codeblock')"
                    class="rounded px-2 py-1 text-xs hover:bg-sidebar-accent dark:hover:bg-sidebar-border"
                    title="Code Block"
                >
                    ```
                </button>
            </div>

            <!-- Tab switcher for mobile -->
            <div class="flex gap-1 lg:hidden">
                <button
                    type="button"
                    @click="activeTab = 'write'"
                    :class="[
                        'rounded px-3 py-1 text-xs font-medium',
                        activeTab === 'write'
                            ? 'bg-blue-600 text-white'
                            : 'hover:bg-sidebar-accent dark:hover:bg-sidebar-border',
                    ]"
                >
                    Write
                </button>
                <button
                    type="button"
                    @click="activeTab = 'preview'"
                    :class="[
                        'rounded px-3 py-1 text-xs font-medium',
                        activeTab === 'preview'
                            ? 'bg-blue-600 text-white'
                            : 'hover:bg-sidebar-accent dark:hover:bg-sidebar-border',
                    ]"
                >
                    Preview
                </button>
            </div>
        </div>

        <!-- Editor and Preview -->
        <div ref="containerRef" class="flex flex-1 overflow-hidden">
            <!-- Editor Panel -->
            <div
                :class="[
                    'flex flex-col',
                    activeTab === 'preview' ? 'hidden lg:flex' : 'flex',
                ]"
                :style="{ width: activeTab === 'write' ? '100%' : `${editorWidth}%` }"
            >
                <textarea
                    ref="textareaRef"
                    v-model="content"
                    class="flex-1 resize-none border-0 bg-background px-4 py-3 font-mono text-sm outline-none focus:ring-0"
                    placeholder="Write your content in Markdown...&#10;&#10;## Heading 2&#10;&#10;**Bold text** and *italic text*&#10;&#10;```javascript&#10;const code = 'here';&#10;```"
                ></textarea>
            </div>

            <!-- Resizable Divider -->
            <div
                class="hidden lg:flex w-1 cursor-col-resize bg-sidebar-border/70 hover:bg-blue-500 transition-colors relative group"
                @mousedown="startDragging"
            >
                <div class="absolute inset-y-0 -left-1 -right-1"></div>
            </div>

            <!-- Preview Panel -->
            <div
                :class="[
                    'flex flex-col border-l border-sidebar-border/70 dark:border-sidebar-border overflow-hidden',
                    activeTab === 'write' ? 'hidden lg:flex' : 'flex',
                ]"
                :style="{ width: activeTab === 'preview' ? '100%' : `${100 - editorWidth}%` }"
            >
                <div class="flex-1 overflow-y-auto bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 px-4 py-3">
                    <div
                        v-if="content"
                        class="prose prose-invert prose-sm max-w-none prose-headings:text-white prose-p:text-gray-300 prose-a:text-blue-400 prose-strong:text-white prose-code:rounded prose-code:bg-slate-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:text-blue-400 prose-pre:overflow-x-auto prose-pre:rounded-xl prose-pre:bg-slate-900 prose-pre:p-4 prose-img:rounded-xl prose-blockquote:border-l-blue-500 prose-blockquote:bg-slate-800/50 prose-blockquote:text-gray-300"
                        v-html="htmlPreview"
                    ></div>
                    <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">
                        Preview will appear here...
                    </div>
                </div>
            </div>
        </div>

        <!-- Helper text -->
        <div class="border-t border-sidebar-border/70 bg-sidebar-accent/30 px-3 py-1.5 text-xs text-gray-500 dark:border-sidebar-border">
            Markdown supported • Code blocks: ```language ... ``` • Images: ![alt](url)
        </div>
    </div>
</template>

<style scoped>
textarea {
    scrollbar-width: thin;
}
</style>
