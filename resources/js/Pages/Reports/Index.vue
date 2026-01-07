<!-- resources/js/Pages/Reports/Index.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    metrics: Object,
    inventorySnapshot: Object,
    projects: Array,
});

const formatCurrency = (value) => {
    if (!value && value !== 0) return '£0';
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        maximumFractionDigits: 0,
    }).format(value);
};

const inventorySummary = () => props.inventorySnapshot ?? { total_items: 0, valuation: 0, by_type: [] };
</script>

<template>
  <AppLayout title="Reports">
    <template #header>
      <div class="flex flex-col gap-2">
        <h2 class="font-semibold text-2xl text-white leading-tight">
          Reports Command Centre
        </h2>
        <p class="text-text-secondary font-mono text-sm">
          A clean overview of operational performance, inventory health, and project BOM detail.
        </p>
      </div>
    </template>

    <!-- Key metrics overview -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Active Projects
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ metrics?.active_projects ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Live jobs across production and active stages.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Total Weight
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ metrics?.total_weight_lbs?.toLocaleString() ?? '0' }} lbs
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Sum of assembly weights across the yard.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Production Progress
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ metrics?.production_completion_percentage ?? 0 }}%
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Parts cleared as complete versus total scope.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Ready to Ship
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ metrics?.ready_to_ship_pieces ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Completed assemblies awaiting logistics.
        </p>
      </div>
    </section>

    <!-- Available reports -->
    <section class="mt-10 grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 card-industrial">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg font-semibold text-white">
              Inventory Valuation Snapshot
            </h3>
            <p class="text-sm text-text-secondary">
              A rapid read on current stock value and material mix.
            </p>
          </div>
          <Link
            href="/reports/inventory"
            class="btn-secondary"
          >
            View Inventory Report
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-steel-900/60 border border-steel-700 rounded-sm p-5">
            <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
              Usable Stock Items
            </div>
            <div class="mt-3 text-2xl font-semibold text-white">
              {{ inventorySummary().total_items ?? 0 }}
            </div>
            <p class="mt-2 text-sm text-text-secondary">
              Items not yet marked as used.
            </p>
          </div>
          <div class="bg-steel-900/60 border border-steel-700 rounded-sm p-5">
            <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
              Estimated Valuation
            </div>
            <div class="mt-3 text-2xl font-semibold text-white">
              {{ formatCurrency(inventorySummary().valuation ?? 0) }}
            </div>
            <p class="mt-2 text-sm text-text-secondary">
              Based on length × cost per unit.
            </p>
          </div>
        </div>

        <div class="mt-6">
          <h4 class="text-sm uppercase tracking-wider text-text-tertiary font-semibold">
            Top Material Types
          </h4>
          <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              v-for="type in (inventorySummary().by_type ?? []).slice(0, 3)"
              :key="type.type"
              class="bg-steel-900/40 border border-steel-700 rounded-sm p-4"
            >
              <div class="text-sm font-semibold text-white">
                {{ type.type || 'Unknown' }}
              </div>
              <div class="mt-2 text-xs text-text-secondary">
                {{ type.count }} items • {{ type.total_length ?? 0 }}" total length
              </div>
            </div>
            <div
              v-if="(inventorySummary().by_type ?? []).length === 0"
              class="bg-steel-900/40 border border-steel-700 rounded-sm p-4 text-sm text-text-secondary"
            >
              No inventory data yet. Add stock to build the valuation report.
            </div>
          </div>
        </div>
      </div>

      <div class="card-industrial">
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-white">
            Report Library
          </h3>
          <p class="text-sm text-text-secondary">
            Quick access to frequently used reporting views.
          </p>
        </div>

        <div class="space-y-4">
          <Link
            href="/reports/inventory"
            class="block border border-steel-700 rounded-sm p-4 hover:border-forge-500 transition-colors"
          >
            <div class="text-sm font-semibold text-white">Inventory Snapshot</div>
            <div class="mt-1 text-xs text-text-secondary">
              Stock valuation, usage, and type totals.
            </div>
          </Link>

          <Link
            href="/projects"
            class="block border border-steel-700 rounded-sm p-4 hover:border-forge-500 transition-colors"
          >
            <div class="text-sm font-semibold text-white">Project BOMs</div>
            <div class="mt-1 text-xs text-text-secondary">
              Select a project to open its Bill of Materials report.
            </div>
          </Link>

          <div class="border border-steel-700 rounded-sm p-4 text-xs text-text-secondary">
            More reports coming soon. Suggestions welcome.
          </div>
        </div>
      </div>
    </section>

    <!-- Recent projects for BOM access -->
    <section class="mt-10 card-industrial">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold text-white">
            Recent Project BOMs
          </h3>
          <p class="text-sm text-text-secondary">
            Jump straight into the most recently updated jobs.
          </p>
        </div>
        <Link
          href="/projects"
          class="btn-secondary"
        >
          View All Projects
        </Link>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">Job</th>
              <th class="py-3 text-left">Customer</th>
              <th class="py-3 text-left">Status</th>
              <th class="py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="project in projects ?? []"
              :key="project.id"
              class="border-b border-steel-800/80 text-text-secondary"
            >
              <td class="py-3 text-white">
                <div class="font-semibold">
                  {{ project.name ?? 'Untitled Project' }}
                </div>
                <div class="text-xs text-text-tertiary">
                  {{ project.job_number ?? 'No job number' }}
                </div>
              </td>
              <td class="py-3">
                {{ project.customer?.name ?? 'No customer' }}
              </td>
              <td class="py-3 capitalize">
                {{ project.status ?? 'unknown' }}
              </td>
              <td class="py-3 text-right">
                <Link
                  :href="`/reports/project/${project.id}/bom`"
                  class="btn-primary"
                >
                  View BOM
                </Link>
              </td>
            </tr>
            <tr v-if="(projects ?? []).length === 0">
              <td
                colspan="4"
                class="py-6 text-center text-sm text-text-secondary"
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
