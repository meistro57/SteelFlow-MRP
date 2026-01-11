<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    title: { type: String, default: 'Production' },
    stats: { type: Object, required: true },
});
</script>

<template>
  <AppLayout title="Production">
    <Head title="Production - SteelFlow MRP" />

    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-white">
            Production Dashboard
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Shop floor management & tracking
          </p>
        </div>
        <Link
          :href="route('production.scan')"
          class="btn-primary"
        >
          <svg
            class="w-5 h-5 inline-block mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
            />
          </svg>
          Barcode Scanner
        </Link>
      </div>
    </template>

    <!-- Production Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="card-industrial bg-steel-900 border-forge-900">
        <div class="metric-label text-forge-400">
          Active Batches
        </div>
        <div class="text-3xl font-bold font-mono text-forge-400 mt-2">
          {{ stats.activeBatches || 0 }}
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          Current work in progress
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-weld-900">
        <div class="metric-label text-weld-400">
          Parts Completed
        </div>
        <div class="text-3xl font-bold font-mono text-weld-400 mt-2">
          {{ stats.partsCompletedToday || 0 }}
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          Operations recorded today
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-green-900">
        <div class="metric-label text-green-400">
          On Schedule
        </div>
        <div class="text-3xl font-bold font-mono text-green-400 mt-2">
          {{ stats.onSchedulePercentage || 0 }}%
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          Overall project timeliness
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-steel-700">
        <div class="metric-label">
          Shop Efficiency
        </div>
        <div class="text-3xl font-bold font-mono text-white mt-2">
          {{ stats.shopEfficiency || 0 }}%
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          Standard vs Actual labor
        </div>
      </div>
    </div>

    <!-- Quick Actions & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Quick Actions -->
      <div class="card-industrial">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center">
          <svg
            class="w-6 h-6 mr-2 text-forge-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"
            />
          </svg>
          Quick Actions
        </h2>
        <div class="space-y-3">
          <Link
            :href="route('production.scan')"
            class="block p-4 bg-steel-800 border border-steel-700 rounded hover:border-forge-500 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="font-semibold text-white">
                  Barcode Scanner
                </div>
                <div class="text-sm text-text-tertiary">
                  Scan parts and track progress
                </div>
              </div>
              <svg
                class="w-5 h-5 text-forge-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </div>
          </Link>

          <button
            class="block w-full p-4 bg-steel-800 border border-steel-700 rounded hover:border-weld-500 transition-colors text-left"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="font-semibold text-white">
                  Start New Batch
                </div>
                <div class="text-sm text-text-tertiary">
                  Begin production on a new batch
                </div>
              </div>
              <svg
                class="w-5 h-5 text-weld-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </div>
          </button>

          <button
            class="block w-full p-4 bg-steel-800 border border-steel-700 rounded hover:border-green-500 transition-colors text-left"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="font-semibold text-white">
                  View Work Orders
                </div>
                <div class="text-sm text-text-tertiary">
                  See all active production orders
                </div>
              </div>
              <svg
                class="w-5 h-5 text-green-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </div>
          </button>
        </div>
      </div>

      <!-- Production Status -->
      <div class="card-industrial">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center">
          <svg
            class="w-6 h-6 mr-2 text-weld-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          Work Area Status
        </h2>
        <div class="space-y-4">
          <div
            v-for="area in stats.workAreaStats"
            :key="area.name"
            class="flex items-center justify-between"
          >
            <span class="text-text-secondary">{{ area.name }}</span>
            <span
              class="badge"
              :class="area.active > 0 ? 'badge-in-progress' : 'badge-complete'"
            >
              {{ area.active }} Active
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Production Activity -->
    <div class="mt-6 card-industrial">
      <h2 class="text-xl font-bold text-white mb-4 flex items-center">
        <svg
          class="w-6 h-6 mr-2 text-forge-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        Recent Activity
      </h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-steel-700">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-text-tertiary">
              <th class="px-4 py-3">
                Event
              </th>
              <th class="px-4 py-3">
                Time
              </th>
              <th class="px-4 py-3">
                Status
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-steel-800">
            <tr
              v-for="activity in stats.recentActivity"
              :key="activity.id"
              class="hover:bg-steel-800/60"
            >
              <td class="px-4 py-3 text-sm text-white">
                {{ activity.message }}
              </td>
              <td class="px-4 py-3 text-sm text-text-secondary">
                {{ activity.time }}
              </td>
              <td class="px-4 py-3">
                <span
                  class="badge text-xs"
                  :class="activity.status === 'complete' ? 'badge-complete' : 'badge-in-progress'"
                >
                  {{ activity.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
