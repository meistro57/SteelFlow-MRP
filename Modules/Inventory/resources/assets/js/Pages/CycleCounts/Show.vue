<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    cycleCount: Object,
    summary: Object,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};

const getStatusColor = (status) => {
    const colors = {
        draft: 'badge',
        in_progress: 'badge-in-progress',
        pending_review: 'badge-assigned',
        completed: 'badge-complete',
        cancelled: 'badge-cancelled',
    };
    return colors[status] || 'badge';
};

const completeCycleCount = () => {
    router.post(`/cycle-counts/${props.cycleCount.id}/complete`);
};
</script>

<template>
  <AppLayout :title="`Cycle Count: ${cycleCount.count_number}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            href="/cycle-counts"
            class="text-text-secondary hover:text-white transition-colors mr-4"
          >
            &larr; Back to Cycle Counts
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center">
              {{ cycleCount.count_number }}
            </h1>
            <p class="text-text-secondary mt-1 font-mono text-sm">
              {{ cycleCount.stock_area ? cycleCount.stock_area.replace('_', ' ') : 'All Areas' }}
            </p>
          </div>
        </div>
        <div class="flex gap-3">
          <Link
            v-if="cycleCount.status === 'draft' || cycleCount.status === 'in_progress'"
            :href="`/cycle-counts/${cycleCount.id}/count`"
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
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
              />
            </svg>
            Continue Counting
          </Link>
          <button
            v-if="cycleCount.status === 'in_progress' && summary.counted_items === summary.total_items && summary.variance_items === summary.reconciled_items"
            class="btn-primary bg-green-600 hover:bg-green-700"
            @click="completeCycleCount"
          >
            Complete Count
          </button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Status
          </div>
          <div class="mt-2">
            <span
              :class="getStatusColor(cycleCount.status)"
              class="badge text-base capitalize"
            >
              {{ cycleCount.status.replace('_', ' ') }}
            </span>
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Total Items
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ summary.total_items }}
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-weld-900">
          <div class="metric-label text-weld-400">
            Counted
          </div>
          <div class="text-2xl font-bold font-mono text-weld-400 mt-2">
            {{ summary.counted_items }}
          </div>
        </div>

        <div
          class="card-industrial bg-steel-900"
          :class="summary.variance_items > 0 ? 'border-red-900' : 'border-steel-700'"
        >
          <div
            class="metric-label"
            :class="summary.variance_items > 0 ? 'text-red-400' : ''"
          >
            Variances
          </div>
          <div
            class="text-2xl font-bold font-mono mt-2"
            :class="summary.variance_items > 0 ? 'text-red-400' : 'text-text-tertiary'"
          >
            {{ summary.variance_items }}
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-green-900">
          <div class="metric-label text-green-400">
            Reconciled
          </div>
          <div class="text-2xl font-bold font-mono text-green-400 mt-2">
            {{ summary.reconciled_items }}
          </div>
        </div>
      </div>

      <!-- Details Card -->
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6">
          Count Details
        </h3>
        <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div>
            <dt class="metric-label">
              Scheduled Date
            </dt>
            <dd class="mt-1 text-base text-white">
              {{ formatDate(cycleCount.scheduled_date) }}
            </dd>
          </div>
          <div>
            <dt class="metric-label">
              Assigned To
            </dt>
            <dd class="mt-1 text-base text-white">
              {{ cycleCount.assigned_user?.name || '-' }}
            </dd>
          </div>
          <div>
            <dt class="metric-label">
              Started At
            </dt>
            <dd class="mt-1 text-base font-mono text-white">
              {{ formatDateTime(cycleCount.started_at) }}
            </dd>
          </div>
          <div>
            <dt class="metric-label">
              Completed At
            </dt>
            <dd class="mt-1 text-base font-mono text-white">
              {{ formatDateTime(cycleCount.completed_at) }}
            </dd>
          </div>
        </dl>
      </div>

      <!-- Items Table -->
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6">
          Count Items
        </h3>

        <div class="overflow-x-auto scrollbar-industrial">
          <table class="table-industrial">
            <thead>
              <tr>
                <th>Stock ID</th>
                <th>Type / Size</th>
                <th>Grade</th>
                <th class="text-right">
                  Length
                </th>
                <th class="text-right">
                  Expected
                </th>
                <th class="text-right">
                  Counted
                </th>
                <th class="text-right">
                  Variance
                </th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="line in cycleCount.lines"
                :key="line.id"
                :class="{ 'bg-red-900/20': line.counted_quantity !== null && line.counted_quantity !== line.expected_quantity }"
              >
                <td class="table-data-mono">
                  <Link
                    :href="`/inventory/${line.stock_item.id}`"
                    class="text-weld-400 hover:text-weld-300 font-semibold transition-colors"
                  >
                    {{ line.stock_item.stock_id }}
                  </Link>
                </td>
                <td class="table-data-mono">
                  {{ line.stock_item.type }} / {{ line.stock_item.size }}
                </td>
                <td class="table-data-mono">
                  {{ line.stock_item.grade }}
                </td>
                <td class="table-data-mono text-right">
                  {{ formatLength(line.stock_item.length) }}
                </td>
                <td class="table-data-mono text-right">
                  {{ line.expected_quantity }}
                </td>
                <td class="table-data-mono text-right font-bold">
                  {{ line.counted_quantity ?? '-' }}
                </td>
                <td class="table-data-mono text-right font-bold">
                  <span
                    v-if="line.counted_quantity !== null"
                    :class="{
                      'text-green-400': line.counted_quantity === line.expected_quantity,
                      'text-red-400': line.counted_quantity !== line.expected_quantity,
                    }"
                  >
                    {{ line.counted_quantity - line.expected_quantity }}
                  </span>
                  <span
                    v-else
                    class="text-text-tertiary"
                  >-</span>
                </td>
                <td>
                  <span
                    v-if="line.is_reconciled"
                    class="badge badge-complete"
                  >Reconciled</span>
                  <span
                    v-else-if="line.is_counted"
                    class="badge badge-assigned"
                  >Counted</span>
                  <span
                    v-else
                    class="badge"
                  >Pending</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
