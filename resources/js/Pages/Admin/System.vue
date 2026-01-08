<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const clearCache = (type = 'all') => {
    router.post(route('admin.system.clear-cache'), { type }, {
        onSuccess: () => {
            // Optional: notifications
        }
    });
};
</script>

<template>
  <AppLayout title="System Management">
    <template #header>
      <h2 class="font-semibold text-2xl text-white leading-tight">
        System Management
      </h2>
      <p class="text-text-secondary font-mono text-sm mt-1">
        Utility tools for maintenance and performance optimization.
      </p>
    </template>

    <div class="space-y-6">
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-4">
          Cache Management
        </h3>
        <p class="text-text-secondary text-sm mb-6">
          Clearing the system cache can resolve issues with outdated routes, configuration, or views.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <button
            class="btn-secondary flex flex-col items-center justify-center p-6 gap-2 h-auto"
            @click="clearCache('route')"
          >
            <span class="text-2xl">🛤️</span>
            <span class="font-bold">Clear Routes</span>
            <span class="text-xs font-normal opacity-70">artisan route:clear</span>
          </button>

          <button
            class="btn-secondary flex flex-col items-center justify-center p-6 gap-2 h-auto"
            @click="clearCache('config')"
          >
            <span class="text-2xl">⚙️</span>
            <span class="font-bold">Clear Config</span>
            <span class="text-xs font-normal opacity-70">artisan config:clear</span>
          </button>

          <button
            class="btn-secondary flex flex-col items-center justify-center p-6 gap-2 h-auto"
            @click="clearCache('view')"
          >
            <span class="text-2xl">🖼️</span>
            <span class="font-bold">Clear Views</span>
            <span class="text-xs font-normal opacity-70">artisan view:clear</span>
          </button>

          <button
            class="btn-primary flex flex-col items-center justify-center p-6 gap-2 h-auto"
            @click="clearCache('all')"
          >
            <span class="text-2xl">🔥</span>
            <span class="font-bold">Clear All</span>
            <span class="text-xs font-normal opacity-70">artisan optimize:clear</span>
          </button>
        </div>
      </div>

      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-4">
          System Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
          <div class="flex justify-between items-center border-b border-steel-800 pb-2">
            <span class="text-text-tertiary uppercase tracking-wider">Application version</span>
            <span class="text-white font-mono">v0.1.0-alpha (SteelFlow MRP)</span>
          </div>
          <div class="flex justify-between items-center border-b border-steel-800 pb-2">
            <span class="text-text-tertiary uppercase tracking-wider">Environment</span>
            <span class="text-weld-400 font-mono">local</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
