import type { PageProps } from '@inertiajs/core';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useNotyf } from './useNotyf';

// Define the flash message type
interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

// Create a type that extends PageProps
interface AppPageProps extends PageProps {
    flash?: FlashMessages;
}

export function useFlashMessages() {
    const page = usePage<AppPageProps>();
    const notyf = useNotyf();

    watch(
        () => page.props.flash,
        (flash: FlashMessages | undefined) => {
            if (flash?.error) {
                notyf.error(flash.error);
            }
            if (flash?.success) {
                notyf.success(flash.success);
            }
            if (flash?.warning) {
                notyf.warning(flash.warning);
            }
            if (flash?.info) {
                notyf.info(flash.info);
            }
        },
        { immediate: true },
    );
}
