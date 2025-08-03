<!-- filepath: d:\Programming\WebDev\simple-bakery\resources\js\components\SelectComponent.vue -->
<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

interface Option {
    id?: string | number;
    value?: string | number;
    label?: string;
    text?: string;
    [key: string]: any;
}

interface Props {
    modelValue?: any;
    options?: Option[] | string[];
    placeholder?: string;
    searchable?: boolean;
    clearable?: boolean;
    multiple?: boolean;
    disabled?: boolean;
    loading?: boolean;
    optionLabel?: string;
    optionValue?: string;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    options: () => [],
    placeholder: 'Select an option',
    searchable: true,
    clearable: true,
    multiple: false,
    disabled: false,
    loading: false,
    optionLabel: 'label',
    optionValue: 'value',
    class: '',
});

const emit = defineEmits<{
    'update:modelValue': [value: any];
    change: [value: any];
    search: [query: string];
}>();

const selectedValue = ref(props.modelValue);

// Format options to work with vue-select
const formattedOptions = computed(() => {
    return props.options.map((option) => {
        if (typeof option === 'string') {
            return { label: option, value: option };
        }

        // Handle different option formats
        if (option.text && !option.label) {
            return { ...option, label: option.text };
        }

        if (option.name && !option.label) {
            return { ...option, label: option.name };
        }

        return option;
    });
});

// Watch for external value changes
watch(
    () => props.modelValue,
    (newValue) => {
        selectedValue.value = newValue;
    },
);

// Handle value changes
watch(selectedValue, (newValue) => {
    emit('update:modelValue', newValue);
    emit('change', newValue);
});

const handleSearch = (query: string) => {
    emit('search', query);
};
</script>

<template>
    <v-select
        v-model="selectedValue"
        :options="formattedOptions"
        :placeholder="placeholder"
        :searchable="searchable"
        :clearable="clearable"
        :multiple="multiple"
        :disabled="disabled"
        :loading="loading"
        :label="optionLabel"
        :reduce="(option: any) => option[optionValue] || option.value"
        :class="['vue-select-custom']"
        @search="handleSearch"
    >
        <template #no-options>
            <div class="p-2 text-sm text-gray-500">No options available</div>
        </template>
        <template #spinner="{ loading }">
            <div v-if="loading" class="animate-spin">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                    />
                </svg>
            </div>
        </template>
    </v-select>
</template>
