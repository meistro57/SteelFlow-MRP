<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    load: { type: Object, required: true },
});

const formatDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString();
};

const formatWeight = (lbs) => {
    if (!lbs) return '0 lbs';
    return new Intl.NumberFormat().format(Math.round(lbs)) + ' lbs';
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
</script>

<template>
  <AppLayout :title="`Load: ${load.load_number}`">
    <Head :title="`Load ${load.load_number} - SteelFlow MRP`" />

    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            href="/shipping"
            class="text-text-secondary hover:text-white transition-colors mr-4"
          >
            &larr; Back
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-white">
              Load: {{ load.load_number }}
            </h1>
            <p class="text-text-secondary mt-1 font-mono text-sm">
              Project: {{ load.project?.job_number }} - {{ load.project?.name }}
            </p>
          </div>
        </div>
        <div class="flex gap-3">
          <button class="btn-secondary">
            Print BOL
          </button>
          <button class="btn-primary">
            Edit Load
          </button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Grid Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Status
          </div>
          <div class="mt-2">
            <span
              class="inline-flex items-center px-3 py-1.5 rounded text-sm font-bold uppercase tracking-wide"
              :class="statusStyles[load.status] || statusStyles.pending"
            >
              {{ statusLabel(load.status) }}
            </span>
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Total Weight
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ formatWeight(load.total_weight_lbs) }}
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Pieces
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ load.total_pieces }}
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Ship Date
          </div>
          <div class="text-xl font-bold font-mono text-weld-400 mt-2">
            {{ formatDate(load.ship_date) }}
          </div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Load Details -->
        <div class="card-industrial lg:col-span-1 h-fit">
          <h2 class="text-xl font-bold text-white mb-6 flex items-center">
            <svg
              class="w-5 h-5 mr-2 text-weld-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"
              />
            </svg>
            Carrier Info
          </h2>
          <dl class="space-y-4">
            <div>
              <dt class="text-xs uppercase font-bold text-text-tertiary">
                Carrier
              </dt>
              <dd class="text-white mt-0.5">
                {{ load.carrier || 'TBD' }}
              </dd>
            </div>
            <div>
              <dt class="text-xs uppercase font-bold text-text-tertiary">
                Driver
              </dt>
              <dd class="text-white mt-0.5">
                {{ load.driver_name || 'TBD' }}
              </dd>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <dt class="text-xs uppercase font-bold text-text-tertiary">
                  Truck #
                </dt>
                <dd class="text-white font-mono mt-0.5">
                  {{ load.truck_number || '-' }}
                </dd>
              </div>
              <div>
                <dt class="text-xs uppercase font-bold text-text-tertiary">
                  Trailer #
                </dt>
                <dd class="text-white font-mono mt-0.5">
                  {{ load.trailer_number || '-' }}
                </dd>
              </div>
            </div>
            <div>
              <dt class="text-xs uppercase font-bold text-text-tertiary">
                Destination
              </dt>
              <dd class="text-white mt-0.5">
                {{ load.destination || 'TBD' }}
              </dd>
            </div>
          </dl>

          <div
            v-if="load.notes"
            class="mt-6 pt-6 border-t border-steel-700"
          >
            <h3 class="text-xs uppercase font-bold text-text-tertiary mb-2">
              Notes
            </h3>
            <p class="text-sm text-text-secondary whitespace-pre-wrap font-mono">
              {{ load.notes }}
            </p>
          </div>
        </div>

        <!-- Load Items -->
        <div class="card-industrial lg:col-span-2">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg
                class="w-5 h-5 mr-2 text-forge-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                />
              </svg>
              Included Assemblies
            </h2>
            <button class="btn-secondary py-1.5 px-3 text-xs">
              Manage Items
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-steel-700">
              <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-text-tertiary">
                  <th class="px-4 py-3">
                    Mark
                  </th>
                  <th class="px-4 py-3">
                    Type / Size
                  </th>
                  <th class="px-4 py-3 text-right">
                    Qty
                  </th>
                  <th class="px-4 py-3 text-right">
                    Weight
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-steel-800">
                <tr
                  v-if="!load.items || !load.items.length"
                >
                  <td
                    colspan="4"
                    class="px-4 py-8 text-center text-text-tertiary"
                  >
                    No items on this load yet. Start building your payload.
                  </td>
                </tr>
                <tr
                  v-for="item in load.items"
                  :key="item.id"
                  class="hover:bg-steel-800/60"
                >
                  <td class="px-4 py-3">
                    <div class="text-white font-bold font-mono">
                      {{ item.assembly_instance?.assembly?.mark }}
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-xs text-text-secondary">
                      {{ item.assembly_instance?.assembly?.assembly_type }}
                    </div>
                    <div class="text-xs text-text-tertiary font-mono">
                      {{ item.assembly_instance?.assembly?.main_member_size }}
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right text-white font-mono">
                    {{ item.quantity || 1 }}
                  </td>
                  <td class="px-4 py-3 text-right text-text-secondary font-mono">
                    {{ formatWeight(item.weight_lbs) }}
                  </td>
                </tr>
              </tbody>
              <tfoot
                v-if="load.items && load.items.length"
                class="bg-steel-800/50"
              >
                <tr>
                  <td
                    colspan="2"
                    class="px-4 py-3 font-bold text-white text-right uppercase text-xs"
                  >
                    Total
                  </td>
                  <td class="px-4 py-3 text-right font-bold text-white font-mono">
                    {{ load.total_pieces }}
                  </td>
                  <td class="px-4 py-3 text-right font-bold text-forge-400 font-mono">
                    {{ formatWeight(load.total_weight_lbs) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
