<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsManager from '@/Components/SettingsManager.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <AppLayout title="Settings">
    <Head title="Settings - SteelFlow MRP" />

    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-steel-200">
            Settings
          </h1>
          <p class="text-steel-400 mt-1">
            Manage your interface preferences and customization options
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <SettingsManager />
    </div>
  </AppLayout>
</template>
