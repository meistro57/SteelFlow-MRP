<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const currentTheme = computed(() => page.props.auth?.user?.settings?.theme || 'light');

const form = useForm({
    theme: currentTheme.value === 'light' ? 'dark' : 'light',
});

const toggleTheme = () => {
    form.theme = currentTheme.value === 'light' ? 'dark' : 'light';
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            if (form.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
    });
};
</script>

<template>
    <button @click="toggleTheme" class="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
        <span v-if="currentTheme === 'light'">🌙 Dark</span>
        <span v-else>☀️ Light</span>
    </button>
</template>
