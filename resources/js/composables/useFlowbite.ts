import { onMounted } from 'vue';

export function useFlowbite() {
    onMounted(async () => {
        // Dynamic import to avoid SSR issues
        const { initFlowbite } = await import('flowbite');
        initFlowbite();
    });
}
