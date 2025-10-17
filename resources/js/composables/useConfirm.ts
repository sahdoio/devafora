import { ref } from 'vue';
import type { ConfirmDialogOptions } from '@/components/ConfirmDialog.vue';

interface ConfirmDialogInstance {
    show: (options: ConfirmDialogOptions) => Promise<boolean>;
}

const dialogRef = ref<ConfirmDialogInstance | null>(null);

export function useConfirm() {
    const setDialogRef = (instance: ConfirmDialogInstance | null) => {
        dialogRef.value = instance;
    };

    const confirm = async (options: ConfirmDialogOptions): Promise<boolean> => {
        if (!dialogRef.value) {
            console.warn('ConfirmDialog not mounted, falling back to native confirm');
            return window.confirm(options.message);
        }
        return dialogRef.value.show(options);
    };

    return {
        confirm,
        setDialogRef,
    };
}
