import { Eye, Lock, Mail, UserCog } from 'lucide-vue-next';
import { computed } from 'vue';

export function useUserTabs(userId: string) {
    return computed(() => ({
        detail: {
            name: 'View',
            icon: Eye,
        },
        update: {
            name: 'Update',
            icon: UserCog,
        },
        password: {
            name: 'Password',
            icon: Lock,
        },
        mail: {
            name: 'Mail',
            icon: Mail,
        },
    }));
}