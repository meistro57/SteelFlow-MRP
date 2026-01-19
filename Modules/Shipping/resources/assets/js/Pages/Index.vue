<!-- resources/js/Pages/Shipping/Index.vue -->
<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    loads: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const formatDate = (value) => {
    if (!value) return '—';
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime())
        ? '—'
        : parsed.toLocaleDateString();
};

const statusStyles = {
    pending: 'text-amber-300 bg-amber-900/40 border border-amber-700/40',
    in_transit: 'text-blue-200 bg-blue-900/40 border border-blue-700/40',
    delivered: 'text-green-200 bg-green-900/40 border border-green-700/40',
    cancelled: 'text-red-200 bg-red-900/40 border border-red-700/40',
};

const statusLabel = (status) => {
    if (!status) return 'Pending';
    return status
        .replace('_', ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
};

const nextDirection = (column) => {
    if (props.filters.sort_by === column) {
        return props.filters.sort_direction === 'asc' ? 'desc' : 'asc';
    }
    return 'asc';
};

const sortSymbol = (column) => {
    if (props.filters.sort_by !== column) return '';
    return props.filters.sort_direction === 'asc' ? '↑' : '↓';
};
</script>

<template>
  <AppLayout title="Shipping">
    <Head title="Shipping" />

    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">
            Shipping
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Loads, carriers, and delivery tracking
          </p>
        </div>
        <div class="flex gap-3">
          <button
            class="hidden sm:inline-flex btn-secondary"
            type="button"
          >
            <svg
              class="w-5 h-5 mr-2 inline-block"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 7h16M4 12h16M4 17h16"
              />
            </svg>
            Manage Loads
          </button>
          <Link
            :href="route('shipping.create')"
            class="btn-primary"
          >
            <svg
              class="w-5 h-5 mr-2 inline-block"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              />
            </svg>
            New Load
          </Link>
        </div>
      </div>
    </template>

    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="card-industrial bg-steel-900 border-steel-700">
        <div class="metric-label text-text-secondary">
          Total Loads
        </div>
        <div class="text-3xl font-bold font-mono text-white mt-2">
          {{ stats.totalLoads || 0 }}
        </div>
        <div class="text-xs text-text-tertiary mt-1">
          Next ship date: {{ formatDate(stats.nextShipDate) }}
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-amber-900">
        <div class="metric-label text-amber-300">
          Pending / Building
        </div>
        <div class="text-3xl font-bold font-mono text-amber-300 mt-2">
          {{ stats.pendingLoads || 0 }}
        </div>
        <div class="text-xs text-text-tertiary mt-1">
          Awaiting dispatch
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-blue-900">
        <div class="metric-label text-blue-300">
          In Transit
        </div>
        <div class="text-3xl font-bold font-mono text-blue-300 mt-2">
          {{ stats.inTransit || 0 }}
        </div>
        <div class="text-xs text-text-tertiary mt-1">
          Rolling down the highway
        </div>
      </div>

      <div class="card-industrial bg-steel-900 border-green-900">
        <div class="metric-label text-green-300">
          Delivered
        </div>
        <div class="text-3xl font-bold font-mono text-green-300 mt-2">
          {{ stats.delivered || 0 }}
        </div>
        <div class="text-xs text-text-tertiary mt-1">
          Signed and sealed
        </div>
      </div>
    </div>

    <!-- Detail cards -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 card-industrial">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-xl font-bold text-white">
              Active Loads
            </h2>
            <p class="text-sm text-text-tertiary">
              Sort by clicking a column header.
            </p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-steel-700">
            <thead>
              <tr class="text-left text-xs uppercase tracking-wider text-text-tertiary">
                <th class="px-4 py-3">
                  <Link
                    :href="route('shipping.index', { sort_by: 'load_number', sort_direction: nextDirection('load_number') })"
                    class="hover:text-white"
                  >
                    Load # {{ sortSymbol('load_number') }}
                  </Link>
                </th>
                <th class="px-4 py-3">
                  Project
                </th>
                <th class="px-4 py-3">
                  <Link
                    :href="route('shipping.index', { sort_by: 'destination', sort_direction: nextDirection('destination') })"
                    class="hover:text-white"
                  >
                    Destination {{ sortSymbol('destination') }}
                  </Link>
                </th>
                <th class="px-4 py-3">
                  <Link
                    :href="route('shipping.index', { sort_by: 'ship_date', sort_direction: nextDirection('ship_date') })"
                    class="hover:text-white"
                  >
                    Ship Date {{ sortSymbol('ship_date') }}
                  </Link>
                </th>
                <th class="px-4 py-3">
                  Status
                </th>
                <th class="px-4 py-3 hidden lg:table-cell">
                  Carrier
                </th>
                <th class="px-4 py-3 text-right hidden md:table-cell">
                  <Link
                    :href="route('shipping.index', { sort_by: 'pieces', sort_direction: nextDirection('pieces') })"
                    class="hover:text-white"
                  >
                    Pieces {{ sortSymbol('pieces') }}
                  </Link>
                </th>
                <th class="px-4 py-3 text-right hidden md:table-cell">
                  <Link
                    :href="route('shipping.index', { sort_by: 'weight', sort_direction: nextDirection('weight') })"
                    class="hover:text-white"
                  >
                    Weight (lbs) {{ sortSymbol('weight') }}
                  </Link>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-steel-800">
              <tr v-if="!loads.data.length">
                <td
                  class="px-4 py-6 text-text-secondary text-center"
                  colspan="8"
                >
                  No loads yet. Once you start building loads they'll appear here faster than a lorry on the M1.
                </td>
              </tr>
              <tr
                v-for="load in loads.data"
                :key="load.id"
                class="hover:bg-steel-800/60 transition-colors"
              >
                <td class="px-4 py-3 font-mono">
                  <Link
                    :href="route('shipping.show', load.id)"
                    class="text-forge-500 hover:text-forge-400 font-bold"
                  >
                    {{ load.load_number || `L-${load.id}` }}
                  </Link>
                </td>
                <td class="px-4 py-3">
                  <div class="text-white font-semibold">
                    {{ load.project?.job_number || 'Unassigned' }}
                  </div>
                  <div class="text-xs text-text-tertiary truncate max-w-xs">
                    {{ load.project?.name || 'No project linked' }}
                  </div>
                </td>
                <td class="px-4 py-3 text-text-secondary">
                  {{ load.destination || 'TBD' }}
                </td>
                <td class="px-4 py-3 text-text-secondary">
                  {{ formatDate(load.ship_date) }}
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold"
                    :class="statusStyles[load.status] || statusStyles.pending"
                  >
                    {{ statusLabel(load.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-text-secondary">
                  {{ load.carrier || 'Not assigned' }}
                </td>
                <td class="px-4 py-3 text-right hidden md:table-cell text-text-secondary font-mono">
                  {{ load.total_pieces || load.item_count || 0 }}
                </td>
                <td class="px-4 py-3 text-right hidden md:table-cell text-text-secondary font-mono">
                  {{ (load.total_weight_lbs || 0).toLocaleString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-between items-center mt-4 text-sm text-text-tertiary">
          <div>
            Showing {{ loads.from || 0 }}-{{ loads.to || 0 }} of {{ loads.total || 0 }}
          </div>
          <div class="flex gap-2">
            <Link
              v-if="loads.prev_page_url"
              :href="loads.prev_page_url"
              class="btn-secondary py-2 px-3"
            >
              Previous
            </Link>
            <Link
              v-if="loads.next_page_url"
              :href="loads.next_page_url"
              class="btn-secondary py-2 px-3"
            >
              Next
            </Link>
          </div>
        </div>
      </div>

      <div class="card-industrial space-y-4">
        <div>
          <h2 class="text-xl font-bold text-white">
            Load Health
          </h2>
          <p class="text-sm text-text-tertiary">
            Totals across all loads
          </p>
        </div>
        <div class="bg-steel-800 border border-steel-700 rounded p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-text-secondary">
                Total Pieces
              </div>
              <div class="text-2xl font-mono text-white">
                {{ (stats.totalPieces?.toLocaleString?.() ?? stats.totalPieces) || 0 }}
              </div>
            </div>
            <div class="text-xs text-text-tertiary">
              Across every load on file
            </div>
          </div>
        </div>
        <div class="bg-steel-800 border border-steel-700 rounded p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-text-secondary">
                Total Weight
              </div>
              <div class="text-2xl font-mono text-white">
                {{ (stats.totalWeightLbs?.toLocaleString?.(undefined, { maximumFractionDigits: 1 }) ?? stats.totalWeightLbs) || 0 }} lbs
              </div>
            </div>
            <div class="text-xs text-text-tertiary">
              Reported weight in pounds
            </div>
          </div>
        </div>
        <div class="bg-steel-800 border border-steel-700 rounded p-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-text-secondary">
                Upcoming Ship Date
              </div>
              <div class="text-2xl font-mono text-white">
                {{ formatDate(stats.nextShipDate) }}
              </div>
            </div>
            <div class="text-xs text-text-tertiary">
              Earliest scheduled departure
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
