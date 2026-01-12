<!-- resources/js/Pages/Production/TimeEntries.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    timeEntries: Object,
});

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};
</script>

<template>
  <AppLayout title="Time Entries">
    <template #header>
      <h2 class="font-semibold text-xl text-white leading-tight">
        Labor Tracking / Time Entries
      </h2>
    </template>

    <div class="card-industrial">
      <div class="overflow-x-auto">
        <table class="table-industrial text-sm">
          <thead>
            <tr>
              <th class="text-left">
                Employee
              </th>
              <th class="text-left">
                Job / Project
              </th>
              <th class="text-left">
                Work Area
              </th>
              <th class="text-left">
                Start
              </th>
              <th class="text-left">
                End
              </th>
              <th class="text-right">
                Hours
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="entry in timeEntries.data"
              :key="entry.id"
            >
              <td class="font-bold text-white">
                {{ entry.employee?.name }}
              </td>
              <td>{{ entry.project?.job_number }}</td>
              <td class="font-mono text-xs">
                {{ entry.work_area?.name }}
              </td>
              <td class="text-xs text-steel-400">
                {{ formatDateTime(entry.start_time) }}
              </td>
              <td class="text-xs text-steel-400">
                {{ formatDateTime(entry.end_time) }}
              </td>
              <td class="text-right font-bold text-forge-400 font-mono">
                {{ entry.hours || 'RUNNING' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
