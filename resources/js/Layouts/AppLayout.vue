<script setup>
import { ref } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="dark:bg-gray-900">
        <Head :title="title" />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="'/'" class="text-xl font-bold text-gray-800 dark:text-white">
                                    🏗️ SteelFlow
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="'/dashboard'" :active="true">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="'/projects'" :active="false">
                                    Projects
                                </NavLink>
                                <NavLink :href="'/inventory'" :active="false">
                                    Inventory
                                </NavLink>
                                <NavLink :href="'/production'" :active="false">
                                    Production
                                </NavLink>
                                <NavLink :href="'/shipping'" :active="false">
                                    Shipping
                                </NavLink>
                            </div>
                        </div>

                        <!-- Settings Dropdown / User Profile -->
                        <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                            <ThemeToggle />
                            <div class="ms-3 relative">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Steel Admin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
