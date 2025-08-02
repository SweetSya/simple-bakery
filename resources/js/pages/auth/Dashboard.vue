<script setup lang="ts">
import { useNotyf } from '@/composables/useNotyf';
import AuthLayout from '../layout/AuthLayout.vue';

const notyf = useNotyf();

defineOptions({ layout: AuthLayout });
</script>

<template>
    <h5>DASHBOARD HERE</h5>
</template>
