import Swal from 'sweetalert2';
import { useAppearance } from '@/composables/useAppearance';
import 'sweetalert2/src/sweetalert2.scss';

interface Options {
    theme?: 'light' | 'dark' | 'auto';
    title?: string;
    text?: string;
    icon?: 'success' | 'error' | 'warning' | 'info' | 'question';
    showCancelButton?: boolean;
    confirmButtonColor?: string;
    cancelButtonColor?: string;
    confirmButtonText?: string;
    cancelButtonText?: string;
    onConfirm?: () => Promise<any> | any;
    onCancel?: () => Promise<any> | any;
}

export function useSwal() {
    // Use reactive appearance from global composable
    const { appearance } = useAppearance();

    return {
        confirmation: (options: Options = {}) => {
            return Swal.fire({
                theme: options.theme || appearance.value || 'auto',
                title: options.title || 'Are you sure?',
                text: options.text || "You won't be able to revert this!",
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: options.confirmButtonColor || '#3085d6',
                cancelButtonColor: options.cancelButtonColor || '#d33',
                confirmButtonText: options.confirmButtonText || 'Yes!',
                cancelButtonText: options.cancelButtonText || 'No, cancel!',
            }).then((result: any) => {
                if (result.isConfirmed) {
                    return options.onConfirm ? options.onConfirm() : true;
                } else {
                    return options.onCancel ? options.onCancel() : false;
                }
            });
        },
    };
}