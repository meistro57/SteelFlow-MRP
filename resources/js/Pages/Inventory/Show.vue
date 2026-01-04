<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stockItem: Object,
});

const statusColors = {
    free: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    assigned: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    used: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    scrapped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const formatDateTime = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};
</script>

<template>
  <AppLayout :title="`Stock: ${stockItem.stock_id}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            href="/inventory"
            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
          >
            &larr; Back
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
              {{ stockItem.stock_id }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ stockItem.type }} {{ stockItem.size }} - {{ stockItem.grade }}
            </p>
          </div>
        </div>
        <Link
          :href="`/inventory/${stockItem.id}/edit`"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Edit
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Stock Details -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Stock Details
          </h3>
          <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Status
              </dt>
              <dd class="mt-1">
                <span
                  :class="statusColors[stockItem.status]"
                  class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full"
                >
                  {{ stockItem.status }}
                </span>
                <span
                  v-if="stockItem.is_remnant"
                  class="ml-2 text-xs text-orange-600 dark:text-orange-400"
                >
                  (Remnant)
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Length
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ stockItem.length }} ft
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Quantity
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ stockItem.quantity }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Stock Area
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ stockItem.stock_area?.replace('_', ' ') || '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Reserved Project
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                <Link
                  v-if="stockItem.reserved_project"
                  :href="`/projects/${stockItem.reserved_project.id}`"
                  class="text-blue-600 dark:text-blue-400 hover:underline"
                >
                  {{ stockItem.reserved_project.job_number }}
                </Link>
                <span v-else>-</span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Heat Number
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ stockItem.heat_number || '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                PO Number
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ stockItem.po_number || '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Receive Date
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ formatDate(stockItem.receive_date) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Country of Origin
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ stockItem.country_of_origin || '-' }}
              </dd>
            </div>
          </dl>
          <div
            v-if="stockItem.notes"
            class="mt-4"
          >
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Notes
            </dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
              {{ stockItem.notes }}
            </dd>
          </div>
        </div>
      </div>

      <!-- Movement History -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Movement History
          </h3>
          <div
            v-if="stockItem.movements?.length > 0"
            class="overflow-x-auto"
          >
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Date
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Type
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    From
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    To
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Notes
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="movement in stockItem.movements"
                  :key="movement.id"
                >
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ formatDateTime(movement.created_at) }}
                  </td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ movement.movement_type }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ movement.from_status || movement.from_area || '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ movement.to_status || movement.to_area || '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ movement.notes || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p
            v-else
            class="text-gray-500 dark:text-gray-400"
          >
            No movement history recorded.
          </p>
        </div>
      </div>

      <!-- Remnants -->
      <div
        v-if="stockItem.remnants?.length > 0"
        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
      >
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Remnants Created
          </h3>
          <div class="space-y-2">
            <div
              v-for="remnant in stockItem.remnants"
              :key="remnant.id"
              class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded"
            >
              <Link
                :href="`/inventory/${remnant.id}`"
                class="text-blue-600 dark:text-blue-400 hover:underline"
              >
                {{ remnant.stock_id }}
              </Link>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ remnant.length }} ft
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
