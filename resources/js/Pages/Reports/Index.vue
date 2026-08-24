<!-- resources/js/Pages/Reports/Index.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    metrics: Object,
    inventorySnapshot: Object,
    projects: Array,
});

const formatCurrency = (value) => {
    if (!value && value !== 0) return '$0';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(value);
};

const inventorySummary = () => props.inventorySnapshot ?? { totalItems: 0, valuation: 0, byType: [] };

const breadcrumbItems = [
    { label: 'Reports' },
];
</script>

<template>
  <AppLayout title="Reports">
    <!-- Breadcrumb -->
    <Breadcrumb :items="breadcrumbItems" />

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Reports Command Centre
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Operational Performance & Analytics
        </p>
      </div>
    </div>

    <!-- Key metrics overview -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
      <div class="card-glow p-6">
        <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
          Active Projects
        </div>
        <div class="mt-2 text-3xl font-bold font-mono text-forge-400 glow-forge">
          {{ metrics?.active_projects ?? 0 }}
        </div>
        <p class="mt-3 text-sm text-steel-400">
          Live jobs across production and active stages
        </p>
      </div>

      <div class="card-glow p-6">
        <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
          Total Weight
        </div>
        <div class="mt-2 text-3xl font-bold font-mono text-weld-400 glow-weld">
          {{ metrics?.total_weight_lbs?.toLocaleString() ?? '0' }} <span class="text-xl text-steel-400">lbs</span>
        </div>
        <p class="mt-3 text-sm text-steel-400">
          Sum of assembly weights across the yard
        </p>
      </div>

      <div class="card-glow p-6">
        <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
          Production Progress
        </div>
        <div class="mt-2 text-3xl font-bold font-mono text-forge-400 glow-forge">
          {{ metrics?.production_completion_percentage ?? 0 }}<span class="text-xl text-steel-400">%</span>
        </div>
        <p class="mt-3 text-sm text-steel-400">
          Parts cleared as complete versus total scope
        </p>
      </div>

      <div class="card-glow p-6">
        <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
          Ready to Ship
        </div>
        <div class="mt-2 text-3xl font-bold font-mono text-weld-400 glow-weld">
          {{ metrics?.ready_to_ship_pieces ?? 0 }}
        </div>
        <p class="mt-3 text-sm text-steel-400">
          Completed assemblies awaiting logistics
        </p>
      </div>
    </section>

    <!-- Available reports -->
    <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 card-elevated">
        <div class="p-6 border-b border-steel-800">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-steel-100 uppercase tracking-wider">
                Inventory Valuation Snapshot
              </h3>
              <p class="mt-1 text-sm text-steel-400">
                A rapid read on current stock value and material mix
              </p>
            </div>
            <Link
              href="/reports/inventory"
              class="btn-primary"
            >
              View Details
            </Link>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="card p-5">
              <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                Usable Stock Items
              </div>
              <div class="mt-2 text-2xl font-bold font-mono text-forge-400 glow-forge">
                {{ inventorySummary().totalItems ?? 0 }}
              </div>
              <p class="mt-2 text-sm text-steel-400">
                Items not yet marked as used
              </p>
            </div>
            <div class="card p-5">
              <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                Estimated Valuation
              </div>
              <div class="mt-2 text-2xl font-bold font-mono text-weld-400 glow-weld">
                {{ formatCurrency(inventorySummary().valuation ?? 0) }}
              </div>
              <p class="mt-2 text-sm text-steel-400">
                Based on length × cost per unit
              </p>
            </div>
          </div>

          <div>
            <h4 class="text-sm uppercase tracking-wider text-steel-500 font-mono mb-3">
              Top Material Types
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div
                v-for="type in (inventorySummary().byType ?? []).slice(0, 3)"
                :key="type.type"
                class="card p-4"
              >
                <div class="text-sm font-bold text-steel-100 uppercase tracking-wider">
                  {{ type.type || 'Unknown' }}
                </div>
                <div class="mt-2 text-xs text-steel-400 font-mono">
                  {{ type.count }} items • {{ type.total_length ?? 0 }}" total length
                </div>
              </div>
              <div
                v-if="(inventorySummary().byType ?? []).length === 0"
                class="card p-4 text-sm text-steel-500"
              >
                No inventory data yet. Add stock to build the valuation report.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-elevated">
        <div class="p-6 border-b border-steel-800">
          <h3 class="text-lg font-bold text-steel-100 uppercase tracking-wider">
            Report Library
          </h3>
          <p class="mt-1 text-sm text-steel-400">
            Quick access to frequently used reporting views
          </p>
        </div>

        <div class="p-6 space-y-4">
          <Link
            href="/reports/inventory"
            class="block card p-4 hover:border-forge-500 transition-colors group"
          >
            <div class="text-sm font-bold text-steel-100 uppercase tracking-wider group-hover:text-forge-400 transition-colors">
              Inventory Snapshot
            </div>
            <div class="mt-1 text-xs text-steel-400">
              Stock valuation, usage, and type totals
            </div>
          </Link>

          <Link
            href="/projects"
            class="block card p-4 hover:border-forge-500 transition-colors group"
          >
            <div class="text-sm font-bold text-steel-100 uppercase tracking-wider group-hover:text-forge-400 transition-colors">
              Project BOMs
            </div>
            <div class="mt-1 text-xs text-steel-400">
              Select a project to open its Bill of Materials report
            </div>
          </Link>

          <Link
            href="/reports/production"
            class="block card p-4 hover:border-forge-500 transition-colors group"
          >
            <div class="text-sm font-bold text-steel-100 uppercase tracking-wider group-hover:text-forge-400 transition-colors">
              Production Summary
            </div>
            <div class="mt-1 text-xs text-steel-400">
              Batch status, labor hours, and parts completed
            </div>
          </Link>

          <Link
            href="/reports/labor-efficiency"
            class="block card p-4 hover:border-forge-500 transition-colors group"
          >
            <div class="text-sm font-bold text-steel-100 uppercase tracking-wider group-hover:text-forge-400 transition-colors">
              Labor Efficiency
            </div>
            <div class="mt-1 text-xs text-steel-400">
              Parts per hour by work area and department
            </div>
          </Link>

          <Link
            href="/reports/batch-completion"
            class="block card p-4 hover:border-forge-500 transition-colors group"
          >
            <div class="text-sm font-bold text-steel-100 uppercase tracking-wider group-hover:text-forge-400 transition-colors">
              Batch Completion Timeline
            </div>
            <div class="mt-1 text-xs text-steel-400">
              Batch durations and completion history
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Recent projects for BOM access -->
    <section class="mt-8 card-elevated">
      <div class="p-6 border-b border-steel-800">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-bold text-steel-100 uppercase tracking-wider">
              Recent Project BOMs
            </h3>
            <p class="mt-1 text-sm text-steel-400">
              Jump straight into the most recently updated jobs
            </p>
          </div>
          <Link
            href="/projects"
            class="btn-primary"
          >
            View All Projects
          </Link>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="table-industrial">
          <thead>
            <tr>
              <th>Job Number</th>
              <th>Project Name</th>
              <th>Customer</th>
              <th>Status</th>
              <th class="text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="project in projects ?? []"
              :key="project.id"
            >
              <td class="cell-id">
                {{ project.job_number ?? 'No job number' }}
              </td>
              <td>
                <Link
                  :href="`/projects/${project.id}`"
                  class="text-forge-400 hover:text-forge-300 font-bold glow-forge"
                >
                  {{ project.name ?? 'Untitled Project' }}
                </Link>
              </td>
              <td class="text-steel-400 font-mono text-sm">
                {{ project.customer?.name ?? 'No customer' }}
              </td>
              <td class="cell-status">
                <span
                  v-if="project.status === 'active'"
                  class="badge-in-progress"
                >
                  Active
                </span>
                <span
                  v-else-if="project.status === 'complete'"
                  class="badge-complete"
                >
                  Complete
                </span>
                <span
                  v-else-if="project.status === 'production'"
                  class="badge-warning"
                >
                  Production
                </span>
                <span
                  v-else
                  class="badge-free"
                >
                  {{ project.status ?? 'unknown' }}
                </span>
              </td>
              <td class="text-right">
                <Link
                  :href="`/reports/project/${project.id}/bom`"
                  class="text-weld-400 hover:text-weld-300 text-sm uppercase tracking-wider font-mono glow-weld"
                >
                  View BOM
                </Link>
              </td>
            </tr>
            <tr v-if="(projects ?? []).length === 0">
              <td
                colspan="5"
                class="text-center py-12 text-steel-500"
              >
                No projects yet. Add a project to unlock BOM reporting.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </AppLayout>
</template>
