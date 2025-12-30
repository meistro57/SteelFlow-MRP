<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    stats: Array,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Overview
            </h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Project Stats Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 truncate">Active Projects</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">12</div>
            </div>

            <!-- Tonnage Stats Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 truncate">Total Weight (Lbs)</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">450,200</div>
            </div>

            <!-- Production Stats Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 truncate">In Production</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">84%</div>
            </div>

            <!-- Shipping Stats Card -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500 truncate">Ready to Ship</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">8</div>
            </div>
        </div>

        <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                <div class="mt-4 text-gray-600">
                    Welcome to SteelFlow MRP. Start by importing your first KISS file or viewing active projects.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
