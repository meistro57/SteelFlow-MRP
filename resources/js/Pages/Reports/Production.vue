<!-- resources/js/Pages/Reports/Production.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    summary: Object,
    batches: Array,
    workAreaUtilization: Array,
});

const startDate = ref(props.summary?.date_range?.start?.split('T')[0] ?? '');
const endDate = ref(props.summary?.date_range?.end?.split('T')[0] ?? '');

const applyFilters = () => {
    router.get(route('reports.production'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        created: 'bg-steel-600 text-steel-200',
        released: 'bg-blue-600/20 text-blue-400',
        in_progress: 'bg-amber-600/20 text-amber-400',
        complete: 'bg-green-600/20 text-green-400',
    };
    return colors[status] || 'bg-steel-600 text-steel-200';
};
</script>

<template>
  <AppLayout title="Production Report">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Production Report
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Batch status, labor hours, and parts completed across the shop floor.
            </p>
          </div>
          <Link
            href="/reports"
            class="btn-secondary"
          >
            Back to Reports
          </Link>
        </div>
      </div>
    </template>

    <!-- Date filters -->
    <section class="card-industrial mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label
            for="start_date"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Start Date
          </label>
          <input
            id="start_date"
            v-model="startDate"
            type="date"
            class="input-industrial"
          >
        </div>
        <div>
          <label
            for="end_date"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            End Date
          </label>
          <input
            id="end_date"
            v-model="endDate"
            type="date"
            class="input-industrial"
          >
        </div>
        <button
          type="button"
          class="btn-primary"
          @click="applyFilters"
        >
          Apply Filters
        </button>
      </div>
    </section>

    <!-- Summary cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Total Batches
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ summary?.total_batches ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          In selected date range.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Active Batches
        </div>
        <div class="mt-4 text-3xl font-bold text-amber-400">
          {{ summary?.active_batches ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Currently in progress.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Completed Batches
        </div>
        <div class="mt-4 text-3xl font-bold text-green-400">
          {{ summary?.completed_batches ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Finished in period.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Labor Hours
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ summary?.labor_hours?.toLocaleString() ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Tracked time entries.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Parts Completed
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ summary?.parts_completed?.toLocaleString() ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Routing steps finished.
        </p>
      </div>
    </section>

    <!-- Work Area Utilization -->
    <section class="mt-10 card-industrial">
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-white">
          Work Area Utilization
        </h3>
        <p class="text-sm text-text-secondary">
          Completion rate by work area during selected period.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div
          v-for="area in workAreaUtilization ?? []"
          :key="area.name"
          class="bg-steel-900/60 border border-steel-700 rounded-sm p-5"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="font-semibold text-white">
              {{ area.name }}
            </div>
            <div
              v-if="area.badge_code"
              class="text-xs px-2 py-1 bg-steel-800 rounded text-text-tertiary font-mono"
            >
              {{ area.badge_code }}
            </div>
          </div>
          <div class="mb-2">
            <div class="flex items-center justify-between text-sm mb-1">
              <span class="text-text-secondary">Utilization</span>
              <span class="text-white font-semibold">{{ area.utilization_percentage ?? 0 }}%</span>
            </div>
            <div class="w-full bg-steel-800 rounded-full h-2">
              <div
                class="bg-forge-500 h-2 rounded-full transition-all"
                :style="{ width: `${area.utilization_percentage ?? 0}%` }"
              />
            </div>
          </div>
          <div class="text-xs text-text-tertiary">
            {{ area.completed_steps ?? 0 }} / {{ area.total_steps ?? 0 }} steps completed
          </div>
        </div>
        <div
          v-if="(workAreaUtilization ?? []).length === 0"
          class="col-span-full text-center text-sm text-text-secondary py-8"
        >
          No work areas configured yet. Add work areas to track production utilization.
        </div>
      </div>
    </section>

    <!-- Batch listing -->
    <section class="mt-10 card-industrial">
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-white">
          Production Batches
        </h3>
        <p class="text-sm text-text-secondary">
          All batches within the selected date range.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Batch Number
              </th>
              <th class="py-3 text-left">
                Project
              </th>
              <th class="py-3 text-left">
                Status
              </th>
              <th class="py-3 text-left">
                Created
              </th>
              <th class="py-3 text-left">
                Completed
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="batch in batches ?? []"
              :key="batch.id"
              class="border-b border-steel-800/80 text-text-secondary"
            >
              <td class="py-3 text-white font-mono">
                {{ batch.batch_number ?? 'N/A' }}
              </td>
              <td class="py-3">
                {{ batch.project?.job_number ?? 'No project' }}
              </td>
              <td class="py-3">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(batch.status)"
                >
                  {{ batch.status?.replace('_', ' ') ?? 'unknown' }}
                </span>
              </td>
              <td class="py-3">
                {{ formatDate(batch.created_at) }}
              </td>
              <td class="py-3">
                {{ formatDate(batch.completed_at) }}
              </td>
            </tr>
            <tr v-if="(batches ?? []).length === 0">
              <td
                colspan="5"
                class="py-6 text-center text-sm text-text-secondary"
              >
                No batches found in the selected date range.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </AppLayout>
</template>
