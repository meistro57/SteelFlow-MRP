<!-- resources/js/Pages/Reports/LaborEfficiency.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    work_areas: Array,
    date_range: Object,
});

const startDate = ref(props.date_range?.start?.split('T')[0] ?? '');
const endDate = ref(props.date_range?.end?.split('T')[0] ?? '');

const applyFilters = () => {
    router.get(route('reports.labor-efficiency'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getEfficiencyColor = (partsPerHour) => {
    if (partsPerHour >= 5) return 'text-green-400';
    if (partsPerHour >= 2) return 'text-amber-400';
    return 'text-red-400';
};

const totalHours = () => {
    return (props.work_areas ?? []).reduce((sum, area) => sum + (area.total_hours ?? 0), 0);
};

const totalPartsCompleted = () => {
    return (props.work_areas ?? []).reduce((sum, area) => sum + (area.parts_completed ?? 0), 0);
};

const overallEfficiency = () => {
    const hours = totalHours();
    if (hours === 0) return 0;
    return (totalPartsCompleted() / hours).toFixed(2);
};
</script>

<template>
  <AppLayout title="Labor Efficiency Report">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Labor Efficiency Report
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Parts per hour by work area and department performance.
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
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Total Labor Hours
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ totalHours().toLocaleString() }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Tracked across all work areas.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Parts Completed
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ totalPartsCompleted().toLocaleString() }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Total routing steps finished.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Overall Efficiency
        </div>
        <div
          class="mt-4 text-3xl font-bold"
          :class="getEfficiencyColor(overallEfficiency())"
        >
          {{ overallEfficiency() }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Parts per labor hour.
        </p>
      </div>
    </section>

    <!-- Work Area breakdown -->
    <section class="mt-10 card-industrial">
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-white">
          Efficiency by Work Area
        </h3>
        <p class="text-sm text-text-secondary">
          Performance breakdown showing labor hours, parts completed, and efficiency rate.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Work Area
              </th>
              <th class="py-3 text-left">
                Department
              </th>
              <th class="py-3 text-right">
                Labor Hours
              </th>
              <th class="py-3 text-right">
                Parts Completed
              </th>
              <th class="py-3 text-right">
                Parts/Hour
              </th>
              <th class="py-3 text-right">
                Performance
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="area in work_areas ?? []"
              :key="area.work_area"
              class="border-b border-steel-800/80 text-text-secondary"
            >
              <td class="py-3 text-white font-semibold">
                {{ area.work_area ?? 'Unknown' }}
              </td>
              <td class="py-3">
                {{ area.department ?? 'N/A' }}
              </td>
              <td class="py-3 text-right">
                {{ (area.total_hours ?? 0).toLocaleString() }}
              </td>
              <td class="py-3 text-right">
                {{ (area.parts_completed ?? 0).toLocaleString() }}
              </td>
              <td
                class="py-3 text-right font-bold"
                :class="getEfficiencyColor(area.parts_per_hour)"
              >
                {{ area.parts_per_hour ?? 0 }}
              </td>
              <td class="py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <div class="w-20 bg-steel-800 rounded-full h-2">
                    <div
                      class="h-2 rounded-full transition-all"
                      :class="area.parts_per_hour >= 5 ? 'bg-green-500' : area.parts_per_hour >= 2 ? 'bg-amber-500' : 'bg-red-500'"
                      :style="{ width: `${Math.min((area.parts_per_hour / 10) * 100, 100)}%` }"
                    />
                  </div>
                </div>
              </td>
            </tr>
            <tr v-if="(work_areas ?? []).length === 0">
              <td
                colspan="6"
                class="py-6 text-center text-sm text-text-secondary"
              >
                No work area data available. Add time entries and complete routing steps to populate this report.
              </td>
            </tr>
          </tbody>
          <tfoot v-if="(work_areas ?? []).length > 0">
            <tr class="border-t-2 border-steel-600 text-white font-semibold">
              <td
                colspan="2"
                class="py-3"
              >
                TOTAL
              </td>
              <td class="py-3 text-right">
                {{ totalHours().toLocaleString() }}
              </td>
              <td class="py-3 text-right">
                {{ totalPartsCompleted().toLocaleString() }}
              </td>
              <td
                class="py-3 text-right"
                :class="getEfficiencyColor(overallEfficiency())"
              >
                {{ overallEfficiency() }}
              </td>
              <td />
            </tr>
          </tfoot>
        </table>
      </div>
    </section>

    <!-- Legend -->
    <section class="mt-6">
      <div class="flex items-center gap-6 text-sm text-text-secondary">
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-green-500" />
          <span>High Efficiency (5+ parts/hr)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-amber-500" />
          <span>Moderate (2-5 parts/hr)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-red-500" />
          <span>Below Target (&lt;2 parts/hr)</span>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
