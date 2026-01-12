<!-- resources/js/Pages/Production/Routing.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    workAreas: Array,
});
</script>

<template>
  <AppLayout title="Production Routing">
    <template #header>
      <h2 class="font-semibold text-xl text-white leading-tight">
        Shop Floor Routing
      </h2>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="area in workAreas"
        :key="area.id"
        class="card-industrial"
      >
        <h3 class="text-lg font-bold text-white mb-2">
          {{ area.name }}
        </h3>
        <p class="text-xs text-steel-500 uppercase font-bold">
          {{ area.department?.name }}
        </p>
        <div class="mt-4 flex justify-between items-center">
          <span class="text-forge-500 font-mono text-xl">{{ area.badge_code }}</span>
          <Link
            :href="route('production.scan')"
            class="btn-secondary text-xs"
          >
            Open Scanner
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
