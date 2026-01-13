<!-- resources/js/Pages/Reports/BatchCompletion.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    batches: Array,
    average_duration: Number,
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const formatDuration = (hours) => {
    if (hours === null || hours === undefined) return '-';
    if (hours < 24) {
        return `${hours.toFixed(1)} hrs`;
    }
    const days = Math.floor(hours / 24);
    const remainingHours = hours % 24;
    return `${days}d ${remainingHours.toFixed(0)}h`;
};

const getDurationColor = (hours) => {
    if (hours === null || hours === undefined) return 'text-text-secondary';
    if (hours <= 24) return 'text-green-400';
    if (hours <= 72) return 'text-amber-400';
    return 'text-red-400';
};

const getStatusColor = (status) => {
    const colors = {
        created: 'bg-steel-600 text-steel-200',
        released: 'bg-blue-600/20 text-blue-400',
        in_progress: 'bg-amber-600/20 text-amber-400',
        complete: 'bg-green-600/20 text-green-400',
    };
    return colors[status] || 'bg-steel-600 text-steel-200';
};

const completedBatches = () => {
    return (props.batches ?? []).filter(b => b.status === 'complete').length;
};

const fastestBatch = () => {
    const completed = (props.batches ?? []).filter(b => b.duration_hours !== null);
    if (completed.length === 0) return null;
    return completed.reduce((min, b) => b.duration_hours < min.duration_hours ? b : min);
};

const slowestBatch = () => {
    const completed = (props.batches ?? []).filter(b => b.duration_hours !== null);
    if (completed.length === 0) return null;
    return completed.reduce((max, b) => b.duration_hours > max.duration_hours ? b : max);
};
</script>

<template>
  <AppLayout title="Batch Completion Timeline">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Batch Completion Timeline
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Batch durations, completion history, and performance benchmarks.
            </p>
          </div>
          <Link
            href="/reports"
            class="btn-secondary"
          >
            Back to Reports
          </Link>
        </div>
      </div>
    </template>

    <!-- Summary cards -->
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Completed Batches
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ completedBatches() }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Total batches shown.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Average Duration
        </div>
        <div
          class="mt-4 text-3xl font-bold"
          :class="getDurationColor(average_duration)"
        >
          {{ formatDuration(average_duration) }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Across all completed batches.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Fastest Completion
        </div>
        <div
          class="mt-4 text-3xl font-bold text-green-400"
        >
          {{ fastestBatch() ? formatDuration(fastestBatch().duration_hours) : '-' }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          {{ fastestBatch()?.batch_number ?? 'No data' }}
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Slowest Completion
        </div>
        <div
          class="mt-4 text-3xl font-bold text-red-400"
        >
          {{ slowestBatch() ? formatDuration(slowestBatch().duration_hours) : '-' }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          {{ slowestBatch()?.batch_number ?? 'No data' }}
        </p>
      </div>
    </section>

    <!-- Timeline visualization -->
    <section class="mt-10 card-industrial">
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-white">
          Completion Timeline
        </h3>
        <p class="text-sm text-text-secondary">
          Recent batch completions with duration comparison.
        </p>
      </div>

      <div class="space-y-4">
        <div
          v-for="batch in batches ?? []"
          :key="batch.batch_number"
          class="flex items-center gap-4 p-4 bg-steel-900/60 border border-steel-700 rounded-sm"
        >
          <div class="flex-shrink-0 w-32">
            <div class="font-mono text-white font-semibold">
              {{ batch.batch_number ?? 'N/A' }}
            </div>
            <div class="text-xs text-text-tertiary">
              {{ batch.project ?? 'No project' }}
            </div>
          </div>

          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <div class="text-xs text-text-tertiary">
                {{ formatDate(batch.created_at) }}
              </div>
              <div class="flex-1 h-2 bg-steel-800 rounded-full relative">
                <div
                  class="absolute inset-y-0 left-0 rounded-full transition-all"
                  :class="batch.duration_hours <= 24 ? 'bg-green-500' : batch.duration_hours <= 72 ? 'bg-amber-500' : 'bg-red-500'"
                  :style="{ width: `${Math.min((batch.duration_hours / (average_duration * 2)) * 100, 100)}%` }"
                />
              </div>
              <div class="text-xs text-text-tertiary">
                {{ formatDate(batch.completed_at) }}
              </div>
            </div>
          </div>

          <div class="flex-shrink-0 w-24 text-right">
            <div
              class="font-bold"
              :class="getDurationColor(batch.duration_hours)"
            >
              {{ formatDuration(batch.duration_hours) }}
            </div>
          </div>

          <div class="flex-shrink-0">
            <span
              class="px-2 py-1 text-xs rounded-sm capitalize"
              :class="getStatusColor(batch.status)"
            >
              {{ batch.status?.replace('_', ' ') ?? 'unknown' }}
            </span>
          </div>
        </div>

        <div
          v-if="(batches ?? []).length === 0"
          class="text-center text-sm text-text-secondary py-8"
        >
          No completed batches found. Batches will appear here once they are marked as complete.
        </div>
      </div>
    </section>

    <!-- Batch details table -->
    <section class="mt-10 card-industrial">
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-white">
          Batch Details
        </h3>
        <p class="text-sm text-text-secondary">
          Detailed view of completed batch timings.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Batch Number
              </th>
              <th class="py-3 text-left">
                Project
              </th>
              <th class="py-3 text-left">
                Status
              </th>
              <th class="py-3 text-left">
                Started
              </th>
              <th class="py-3 text-left">
                Completed
              </th>
              <th class="py-3 text-right">
                Duration
              </th>
              <th class="py-3 text-right">
                vs Avg
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="batch in batches ?? []"
              :key="batch.batch_number"
              class="border-b border-steel-800/80 text-text-secondary"
            >
              <td class="py-3 text-white font-mono font-semibold">
                {{ batch.batch_number ?? 'N/A' }}
              </td>
              <td class="py-3">
                {{ batch.project ?? 'No project' }}
              </td>
              <td class="py-3">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(batch.status)"
                >
                  {{ batch.status?.replace('_', ' ') ?? 'unknown' }}
                </span>
              </td>
              <td class="py-3">
                {{ formatDateTime(batch.created_at) }}
              </td>
              <td class="py-3">
                {{ formatDateTime(batch.completed_at) }}
              </td>
              <td
                class="py-3 text-right font-bold"
                :class="getDurationColor(batch.duration_hours)"
              >
                {{ formatDuration(batch.duration_hours) }}
              </td>
              <td class="py-3 text-right">
                <span
                  v-if="batch.duration_hours !== null && average_duration"
                  :class="batch.duration_hours <= average_duration ? 'text-green-400' : 'text-red-400'"
                >
                  {{ batch.duration_hours <= average_duration ? '-' : '+' }}{{ Math.abs(batch.duration_hours - average_duration).toFixed(1) }}h
                </span>
                <span
                  v-else
                  class="text-text-tertiary"
                >-</span>
              </td>
            </tr>
            <tr v-if="(batches ?? []).length === 0">
              <td
                colspan="7"
                class="py-6 text-center text-sm text-text-secondary"
              >
                No batch completion data available yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Legend -->
    <section class="mt-6">
      <div class="flex items-center gap-6 text-sm text-text-secondary">
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-green-500" />
          <span>Fast (&lt;24 hours)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-amber-500" />
          <span>Normal (24-72 hours)</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full bg-red-500" />
          <span>Slow (&gt;72 hours)</span>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
