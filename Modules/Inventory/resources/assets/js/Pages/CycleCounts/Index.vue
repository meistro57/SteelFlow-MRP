<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    cycleCounts: Object,
    statusOptions: Object,
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
</script>

<template>
  <AppLayout title="Cycle Counts">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-white">
            Cycle Counts
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Physical inventory verification workflow
          </p>
        </div>
        <Link
          href="/cycle-counts/create"
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
              d="M12 4v16m8-8H4"
            />
          </svg>
          New Cycle Count
        </Link>
      </div>
    </template>

    <div class="card-industrial">
      <div class="overflow-x-auto scrollbar-industrial">
        <table class="table-industrial">
          <thead>
            <tr>
              <th>Count #</th>
              <th>Status</th>
              <th>Area</th>
              <th class="text-right">
                Items
              </th>
              <th>Scheduled</th>
              <th>Assigned To</th>
              <th>Started</th>
              <th>Completed</th>
              <th class="text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="count in cycleCounts.data"
              :key="count.id"
            >
              <td class="table-data-mono">
                <Link
                  :href="`/cycle-counts/${count.id}`"
                  class="text-weld-400 hover:text-weld-300 font-semibold transition-colors"
                >
                  {{ count.count_number }}
                </Link>
              </td>
              <td>
                <span
                  :class="getStatusColor(count.status)"
                  class="badge capitalize"
                >
                  {{ count.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="text-text-secondary capitalize">
                {{ count.stock_area?.replace('_', ' ') || 'All Areas' }}
              </td>
              <td class="table-data-mono text-right font-bold">
                {{ count.lines_count }}
              </td>
              <td class="text-text-secondary text-sm">
                {{ formatDate(count.scheduled_date) }}
              </td>
              <td class="text-text-secondary text-sm">
                {{ count.assigned_user?.name || '-' }}
              </td>
              <td class="text-text-secondary text-xs font-mono">
                {{ formatDateTime(count.started_at) }}
              </td>
              <td class="text-text-secondary text-xs font-mono">
                {{ formatDateTime(count.completed_at) }}
              </td>
              <td class="text-right">
                <Link
                  v-if="count.status === 'draft' || count.status === 'in_progress'"
                  :href="`/cycle-counts/${count.id}/count`"
                  class="text-weld-400 hover:text-weld-300 font-semibold text-sm transition-colors"
                >
                  Count
                </Link>
                <Link
                  v-else
                  :href="`/cycle-counts/${count.id}`"
                  class="text-forge-400 hover:text-forge-300 font-semibold text-sm transition-colors"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="cycleCounts.data.length === 0">
              <td
                colspan="9"
                class="text-center py-12"
              >
                <div class="flex flex-col items-center justify-center">
                  <svg
                    class="w-16 h-16 text-steel-600 mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                    />
                  </svg>
                  <p class="text-text-secondary text-lg">
                    No cycle counts found
                  </p>
                  <p class="text-text-tertiary text-sm mt-2">
                    Create your first cycle count to verify inventory
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="cycleCounts.last_page > 1"
        class="mt-6 pt-6 border-t border-steel-700 flex items-center justify-between"
      >
        <div class="text-sm text-text-tertiary font-mono">
          Showing
          <span class="font-bold text-white">{{ cycleCounts.from }}</span>
          to
          <span class="font-bold text-white">{{ cycleCounts.to }}</span>
          of
          <span class="font-bold text-white">{{ cycleCounts.total }}</span>
          counts
        </div>
        <div class="flex space-x-2">
          <Link
            v-if="cycleCounts.prev_page_url"
            :href="cycleCounts.prev_page_url"
            class="btn-ghost text-sm py-2 px-4"
          >
            Previous
          </Link>
          <Link
            v-if="cycleCounts.next_page_url"
            :href="cycleCounts.next_page_url"
            class="btn-ghost text-sm py-2 px-4"
          >
            Next
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
