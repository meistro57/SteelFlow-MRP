<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    nestings: Object,
});
</script>

<template>
    <AppLayout title="Nesting Index">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nesting
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">List of Nestings</h3>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="nesting in nestings.data" :key="nesting.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ nesting.nesting_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ nesting.project?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ nesting.status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Link :href="route('nesting.show', nesting.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
