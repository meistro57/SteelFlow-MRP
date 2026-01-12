<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { PlusIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    ncrs: Object,
    stats: Object,
    filters: Object,
    statuses: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'open': 'bg-red-500',
        'under_review': 'bg-yellow-500',
        'dispositioned': 'bg-blue-500',
        'closed': 'bg-green-500',
    };
    return badges[status] || 'bg-gray-500';
};

const breadcrumbItems = [
    { label: 'Quality' },
    { label: 'NCR' },
];
</script>

<template>
  <AppLayout title="Non-Conformance Reports">
    <Breadcrumb :items="breadcrumbItems" />

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Non-Conformance Reports
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Quality Management & NCR Tracking
        </p>
      </div>
      <div class="mt-4 md:mt-0">
        <Link
          :href="route('ncr.create')"
          class="btn-primary"
        >
          <PlusIcon class="h-5 w-5" />
          Report NCR
        </Link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Total NCRs
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ stats.total }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Open
          </div>
          <div class="text-2xl font-bold font-mono text-red-400 mt-2">
            {{ stats.open }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Under Review
          </div>
          <div class="text-2xl font-bold font-mono text-yellow-400 mt-2">
            {{ stats.under_review }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Dispositioned
          </div>
          <div class="text-2xl font-bold font-mono text-blue-400 mt-2">
            {{ stats.dispositioned }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Closed
          </div>
          <div class="text-2xl font-bold font-mono text-green-400 mt-2">
            {{ stats.closed }}
          </div>
        </div>
      </div>
    </div>

    <!-- NCRs Table -->
    <div class="card-glow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-steel-700">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-steel-400">
              <th class="px-6 py-3 font-mono">
                NCR #
              </th>
              <th class="px-6 py-3 font-mono">
                Type
              </th>
              <th class="px-6 py-3 font-mono">
                Failure Reason
              </th>
              <th class="px-6 py-3 font-mono">
                Reported By
              </th>
              <th class="px-6 py-3 font-mono">
                Date
              </th>
              <th class="px-6 py-3 font-mono text-center">
                Status
              </th>
              <th class="px-6 py-3 font-mono text-center">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-steel-800">
            <tr
              v-if="!ncrs.data || !ncrs.data.length"
              class="hover:bg-steel-800/50 transition-colors"
            >
              <td
                colspan="7"
                class="px-6 py-12 text-center"
              >
                <ExclamationTriangleIcon class="w-12 h-12 mx-auto text-steel-600 mb-3" />
                <div class="text-steel-400 uppercase tracking-wider font-mono">
                  No NCRs found
                </div>
                <Link
                  :href="route('ncr.create')"
                  class="btn-primary mt-4 inline-flex items-center gap-2"
                >
                  <PlusIcon class="h-5 w-5" />
                  Report First NCR
                </Link>
              </td>
            </tr>
            <tr
              v-for="ncr in ncrs.data"
              :key="ncr.id"
              class="hover:bg-steel-800/50 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="text-white font-bold font-mono">
                  {{ ncr.number }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div
                  v-if="ncr.part_instance"
                  class="text-white"
                >
                  Part
                  <div class="text-xs text-steel-400">
                    {{ ncr.part_instance.part?.part_mark }}
                  </div>
                </div>
                <div
                  v-else-if="ncr.production_item"
                  class="text-white"
                >
                  Production
                </div>
                <div
                  v-else-if="ncr.stock_item"
                  class="text-white"
                >
                  Material
                  <div class="text-xs text-steel-400">
                    {{ ncr.stock_item.stock_id }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-steel-300">
                {{ ncr.failure_reason }}
              </td>
              <td class="px-6 py-4 text-steel-300">
                {{ ncr.reporter?.name }}
              </td>
              <td class="px-6 py-4 text-steel-300 font-mono">
                {{ formatDate(ncr.created_at) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  class="px-3 py-1 rounded-full text-xs uppercase font-bold text-white"
                  :class="getStatusBadge(ncr.status)"
                >
                  {{ statuses[ncr.status] || ncr.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <Link
                  :href="route('ncr.show', ncr.id)"
                  class="text-weld-400 hover:text-weld-300 font-mono text-sm"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="ncrs.data && ncrs.data.length"
        class="px-6 py-4 border-t border-steel-700 flex items-center justify-between"
      >
        <div class="text-sm text-steel-400">
          Showing {{ ncrs.from }} to {{ ncrs.to }} of {{ ncrs.total }} NCRs
        </div>
        <div class="flex gap-2">
          <Link
            v-if="ncrs.prev_page_url"
            :href="ncrs.prev_page_url"
            class="btn-secondary py-1 px-3 text-sm"
          >
            Previous
          </Link>
          <Link
            v-if="ncrs.next_page_url"
            :href="ncrs.next_page_url"
            class="btn-secondary py-1 px-3 text-sm"
          >
            Next
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
