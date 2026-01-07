<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    stockItems: Object,
    filters: Object,
    statuses: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

watch([search, statusFilter], ([searchVal, statusVal]) => {
    router.get('/inventory', { search: searchVal, status: statusVal }, {
        preserveState: true,
        replace: true,
    });
});

const statusColors = {
    free: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    assigned: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    used: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    scrapped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

const formatLength = (length) => {
    if (!length) return '-';
    return `${length} ft`;
};
</script>

<template>
  <AppLayout title="Inventory">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Inventory
        </h2>
        <Link
          href="/inventory/create"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Add Stock
        </Link>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
      <!-- Filters -->
      <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Search by stock ID, heat #, PO #..."
          class="w-full md:w-1/3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
        <select
          v-model="statusFilter"
          class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">
            All Statuses
          </option>
          <option
            v-for="(label, value) in statuses"
            :key="value"
            :value="value"
          >
            {{ label }}
          </option>
        </select>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Stock ID
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Type / Size
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Grade
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Length
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Qty
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Location
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Reserved For
              </th>
              <th class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="item in stockItems.data"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                <Link
                  :href="`/inventory/${item.id}`"
                  class="text-blue-600 dark:text-blue-400 hover:underline"
                >
                  {{ item.stock_id }}
                </Link>
                <span
                  v-if="item.is_remnant"
                  class="ml-2 text-xs text-orange-600 dark:text-orange-400"
                >
                  (Remnant)
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ item.type }} / {{ item.size }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ item.grade }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatLength(item.length) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ item.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="statusColors[item.status] || 'bg-gray-100 text-gray-800'"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                  {{ item.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ item.stock_area?.replace('_', ' ') || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ item.reserved_project?.job_number || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link
                  :href="`/inventory/${item.id}/edit`"
                  class="text-blue-600 dark:text-blue-400 hover:text-blue-900 mr-3"
                >
                  Edit
                </Link>
              </td>
            </tr>
            <tr v-if="stockItems.data.length === 0">
              <td
                colspan="9"
                class="px-6 py-4 text-center text-gray-500 dark:text-gray-400"
              >
                No stock items found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="stockItems.last_page > 1"
        class="px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6"
      >
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing
              <span class="font-medium">{{ stockItems.from }}</span>
              to
              <span class="font-medium">{{ stockItems.to }}</span>
              of
              <span class="font-medium">{{ stockItems.total }}</span>
              results
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
