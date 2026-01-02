<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    metrics: Object,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard Overview
            </h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Projects</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ metrics?.active_projects ?? 0 }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Weight (Lbs)</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ metrics?.total_weight_lbs?.toLocaleString() ?? '0' }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Production Progress</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ metrics?.production_completion_percentage ?? 0 }}%
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Ready to Ship</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ metrics?.ready_to_ship_pieces ?? 0 }}
                </div>
            </div>
        </div>

        <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Activity</h3>
                <div class="mt-4 text-gray-600 dark:text-gray-400">
                    Welcome to SteelFlow MRP. Start by importing your first KISS file or viewing active projects.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
