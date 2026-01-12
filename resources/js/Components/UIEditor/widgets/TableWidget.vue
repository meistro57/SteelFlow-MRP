<script setup>
import { computed } from 'vue';

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
    data: {
        type: Object,
        default: () => ({}),
    },
    config: {
        type: Object,
        default: () => ({}),
    },
});

const columns = computed(() => props.data?.columns || ['ID', 'Name', 'Status']);
const rows = computed(() => props.data?.rows || [
    ['001', 'Sample Item 1', 'Active'],
    ['002', 'Sample Item 2', 'Pending'],
    ['003', 'Sample Item 3', 'Complete'],
]);
</script>

<template>
  <div class="h-full overflow-auto">
    <table class="w-full text-sm">
      <thead class="sticky top-0 bg-steel-800">
        <tr>
          <th
            v-for="column in columns"
            :key="column"
            class="px-3 py-2 text-left text-xs font-semibold text-steel-400 uppercase tracking-wider border-b border-steel-700"
          >
            {{ column }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-steel-800">
        <tr
          v-for="(row, rowIndex) in rows"
          :key="rowIndex"
          class="hover:bg-steel-800/50"
        >
          <td
            v-for="(cell, cellIndex) in row"
            :key="cellIndex"
            class="px-3 py-2 text-steel-300 whitespace-nowrap"
          >
            {{ cell }}
          </td>
        </tr>
        <tr v-if="rows.length === 0">
          <td
            :colspan="columns.length"
            class="px-3 py-4 text-center text-steel-500"
          >
            No data available
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
