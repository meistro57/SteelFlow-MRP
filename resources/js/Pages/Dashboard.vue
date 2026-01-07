<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

// Props received from Laravel via Inertia
const props = defineProps({
    metrics: Object,
});

// Use props for metrics with fallbacks
const activeProjects = props.metrics?.active_projects ?? 0;
const totalWeight = props.metrics?.total_weight_lbs ?? 0;
const productionProgress = props.metrics?.production_completion_percentage ?? 0;
const readyToShip = props.metrics?.ready_to_ship_pieces ?? 0;
const repositoryUrl = 'https://github.com/SteelFlow-MRP/SteelFlow-MRP'; // Default repo link; update if hosted elsewhere.

const recentActivity = [
    { id: 1, type: 'production', message: 'Batch B-2024-147 started cutting', time: '2 min ago', status: 'in_progress' },
    { id: 2, type: 'shipping', message: 'Load L-2024-089 departed for site', time: '15 min ago', status: 'complete' },
    { id: 3, type: 'inventory', message: 'Stock received: 15x W14x30 A992', time: '1 hour ago', status: 'complete' },
    { id: 4, type: 'warning', message: 'Low stock alert: C10x15.3', time: '2 hours ago', status: 'warning' },
];
</script>

<template>
  <AppLayout title="Dashboard">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-300 text-glow-forge">
          PRODUCTION DASHBOARD
        </h1>
        <p class="text-steel-500 mt-1">
          Real-time shop floor overview
        </p>
      </div>
      <div class="flex gap-3">
        <a
          :href="repositoryUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="btn-secondary"
        >
          <span>📂</span>
          <span>View Repo</span>
        </a>
        <button class="btn-ghost">
          <span>⚙️</span>
          <span>Settings</span>
        </button>
        <button class="btn-weld">
          <span>🔍</span>
          <span>Search</span>
        </button>
        <button class="btn-primary">
          <span>📋</span>
          <span>New Job</span>
        </button>
      </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Active Projects -->
      <div class="metric-card glow-forge hover:glow-forge-lg transition-shadow">
        <div class="metric-label">
          Active Projects
        </div>
        <div class="metric-value">
          <span class="number">{{ activeProjects }}</span>
        </div>
        <div class="metric-trend-up">
          ↑ 3 from last week
        </div>
        <div class="metric-icon">
          🏗️
        </div>
      </div>

      <!-- Total Weight -->
      <div class="metric-card hover:shadow-industrial-lg transition-shadow">
        <div class="metric-label">
          Total Weight
        </div>
        <div class="metric-value">
          <span class="number">{{ totalWeight.toLocaleString() }}</span>
          <span class="metric-unit">lbs</span>
        </div>
        <div class="text-xs text-steel-500 mt-2">
          {{ (totalWeight / 2000).toFixed(1) }} tons
        </div>
        <div class="metric-icon">
          ⚖️
        </div>
      </div>

      <!-- Production Progress -->
      <div class="metric-card glow-weld hover:glow-weld-lg transition-shadow">
        <div class="metric-label">
          Production Progress
        </div>
        <div class="metric-value">
          <span class="number">{{ productionProgress }}</span>
          <span class="metric-unit">%</span>
        </div>
        <div class="progress-bar mt-4">
          <div
            class="progress-fill"
            :style="`width: ${productionProgress}%`"
          />
        </div>
        <div class="metric-icon">
          ⚡
        </div>
      </div>

      <!-- Ready to Ship -->
      <div class="metric-card hover:shadow-industrial-lg transition-shadow">
        <div class="metric-label">
          Ready to Ship
        </div>
        <div class="metric-value">
          <span class="number">{{ readyToShip }}</span>
          <span class="metric-unit">loads</span>
        </div>
        <div class="metric-trend-up">
          ↑ 12% this week
        </div>
        <div class="metric-icon">
          🚚
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card-elevated mb-8">
      <h3 class="text-lg font-semibold text-steel-300 text-industrial mb-4">
        Quick Actions
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button class="btn-primary justify-start text-left h-auto py-4 flex-col items-start gap-2">
          <div class="flex items-center gap-3 w-full">
            <span class="text-2xl">📥</span>
            <div>
              <div class="font-bold">
                Import KISS File
              </div>
              <div class="text-xs normal-case font-normal opacity-75">
                Upload BOM from detailing
              </div>
            </div>
          </div>
        </button>

        <button class="btn-secondary justify-start text-left h-auto py-4 flex-col items-start gap-2">
          <div class="flex items-center gap-3 w-full">
            <span class="text-2xl">📦</span>
            <div>
              <div class="font-bold">
                Create Project
              </div>
              <div class="text-xs normal-case font-normal opacity-75">
                Start new fabrication job
              </div>
            </div>
          </div>
        </button>

        <button class="btn-secondary justify-start text-left h-auto py-4 flex-col items-start gap-2 border-glow-weld">
          <div class="flex items-center gap-3 w-full">
            <span class="text-2xl">📱</span>
            <div>
              <div class="font-bold">
                Scan Barcode
              </div>
              <div class="text-xs normal-case font-normal opacity-75">
                Mobile production tracking
              </div>
            </div>
          </div>
        </button>
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Activity (2 cols) -->
      <div class="lg:col-span-2">
        <div class="card-elevated">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-steel-300 text-industrial">
              Recent Activity
            </h3>
            <button class="btn-ghost text-xs">
              View All
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="activity in recentActivity"
              :key="activity.id"
              class="flex items-start gap-4 p-4 bg-steel-900 rounded-industrial border border-steel-800 hover:border-steel-700 transition-colors"
            >
              <div class="flex-shrink-0 mt-0.5">
                <span
                  v-if="activity.status === 'in_progress'"
                  class="badge-in-progress"
                >
                  ⚡
                </span>
                <span
                  v-else-if="activity.status === 'complete'"
                  class="badge-complete"
                >
                  ✓
                </span>
                <span
                  v-else-if="activity.status === 'warning'"
                  class="badge-warning"
                >
                  ⚠️
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-steel-300 text-sm font-medium">
                  {{ activity.message }}
                </p>
                <p class="text-steel-500 text-xs mt-1">
                  {{ activity.time }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Production Status (1 col) -->
      <div class="space-y-6">
        <!-- Work Areas -->
        <div class="card">
          <h3 class="text-sm font-semibold text-steel-400 text-industrial mb-4">
            Work Areas
          </h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-steel-300 text-sm">Cutting</span>
              <span class="badge-in-progress">3 Active</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-steel-300 text-sm">Welding</span>
              <span class="badge-in-progress">5 Active</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-steel-300 text-sm">Paint</span>
              <span class="badge-assigned">2 Active</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-steel-300 text-sm">Assembly</span>
              <span class="badge-in-progress">4 Active</span>
            </div>
          </div>
        </div>

        <!-- Alerts -->
        <div class="card">
          <h3 class="text-sm font-semibold text-steel-400 text-industrial mb-4">
            Alerts
          </h3>
          <div class="space-y-2">
            <div class="alert-warning">
              <span>⚠️</span>
              <div class="flex-1 text-xs">
                <div class="font-semibold">
                  Low Stock Alert
                </div>
                <div class="opacity-75">
                  C10x15.3 below minimum
                </div>
              </div>
            </div>
            <div class="alert-info">
              <span>ℹ️</span>
              <div class="flex-1 text-xs">
                <div class="font-semibold">
                  Delivery Scheduled
                </div>
                <div class="opacity-75">
                  PO-2024-089 tomorrow
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- System Status -->
        <div class="card">
          <h3 class="text-sm font-semibold text-steel-400 text-industrial mb-4">
            System Status
          </h3>
          <div class="space-y-3 text-xs">
            <div class="flex justify-between items-center">
              <span class="text-steel-400">Database</span>
              <span class="badge-complete">Online</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-steel-400">Barcode Scanner</span>
              <span class="badge-complete">Connected</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-steel-400">Last Sync</span>
              <span class="text-steel-500 font-mono">2 min ago</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
