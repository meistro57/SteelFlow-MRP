<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    title: String,
});
</script>

<template>
  <AppLayout title="Production">
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
          12
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          3 cutting, 6 welding, 3 finishing
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-weld-900">
        <div class="metric-label text-weld-400">
          Parts Completed
        </div>
        <div class="text-3xl font-bold font-mono text-weld-400 mt-2">
          1,247
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          Today: 89 parts
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-green-900">
        <div class="metric-label text-green-400">
          On Schedule
        </div>
        <div class="text-3xl font-bold font-mono text-green-400 mt-2">
          94%
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          18 of 19 jobs on track
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-steel-700">
        <div class="metric-label">
          Shop Efficiency
        </div>
        <div class="text-3xl font-bold font-mono text-white mt-2">
          87%
        </div>
        <div class="text-xs text-text-tertiary mt-2">
          vs. 85% last week
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
          Production Status
        </h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-text-secondary">Cutting Operations</span>
            <span class="badge badge-in-progress">3 Active</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-text-secondary">Welding Operations</span>
            <span class="badge badge-in-progress">6 Active</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-text-secondary">Finishing Operations</span>
            <span class="badge badge-in-progress">3 Active</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-text-secondary">Quality Control</span>
            <span class="badge badge-complete">2 Complete</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-text-secondary">Ready for Shipping</span>
            <span class="badge badge-complete">8 Ready</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Coming Soon Notice -->
    <div class="mt-6 card-industrial bg-steel-900 border-forge-900">
      <div class="flex items-center">
        <svg
          class="w-6 h-6 text-forge-400 mr-3"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <div>
          <div class="font-semibold text-white">
            Production Module Under Development
          </div>
          <div class="text-sm text-text-tertiary mt-1">
            Full work order management, batch tracking, and real-time production metrics coming soon. Use the Barcode Scanner for mobile shop floor operations.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
