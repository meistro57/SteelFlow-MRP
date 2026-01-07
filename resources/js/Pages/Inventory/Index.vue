<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    stockItems: Object,
    filters: Object,
    statuses: Object,
    locations: Object,
    grades: Array,
    statusCounts: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const locationFilter = ref(props.filters?.location || '');
const gradeFilter = ref(props.filters?.grade || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');

watch([search, statusFilter, locationFilter, gradeFilter], () => {
    router.get('/inventory', {
        search: search.value,
        status: statusFilter.value,
        location: locationFilter.value,
        grade: gradeFilter.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const formatLength = (length) => {
    if (!length) return '-';
    return `${length}"`;
};

const handleSort = (column) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc';
    }

    router.get('/inventory', {
        search: search.value,
        status: statusFilter.value,
        location: locationFilter.value,
        grade: gradeFilter.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const getSortIcon = (column) => {
    if (sortBy.value !== column) return '';
    return sortDirection.value === 'asc' ? '↑' : '↓';
};

const isSortedBy = (column) => {
    return sortBy.value === column;
};

const getStatusCount = (status) => {
    if (!props.statusCounts) {
        return 0;
    }

    return props.statusCounts[status] ?? 0;
};
</script>

<template>
  <AppLayout title="Inventory">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-white">
            Stock Inventory
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Material tracking & yard management
          </p>
        </div>
        <Link
          href="/inventory/create"
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
          Add Stock
        </Link>
      </div>
    </template>

    <div class="card-industrial">
      <!-- Search & Filters -->
      <div class="mb-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Search Inventory
            </label>
            <div class="relative">
              <input
                v-model="search"
                type="text"
                placeholder="Stock ID, heat #, PO #, material..."
                class="input-industrial w-full pl-10"
              >
              <svg
                class="w-5 h-5 text-steel-500 absolute left-3 top-1/2 transform -translate-y-1/2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Status
            </label>
            <select
              v-model="statusFilter"
              class="input-industrial w-full"
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

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Location
            </label>
            <select
              v-model="locationFilter"
              class="input-industrial w-full"
            >
              <option value="">
                All Locations
              </option>
              <option
                v-for="(label, value) in locations"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
              Grade
            </label>
            <select
              v-model="gradeFilter"
              class="input-industrial w-full"
            >
              <option value="">
                All Grades
              </option>
              <option
                v-for="grade in grades"
                :key="grade"
                :value="grade"
              >
                {{ grade }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Industrial Table -->
      <div class="overflow-x-auto scrollbar-industrial">
        <table class="table-industrial">
          <thead>
            <tr>
              <th
                class="cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('stock_id')"
              >
                <div class="flex items-center justify-between">
                  <span>Stock ID</span>
                  <span
                    v-if="isSortedBy('stock_id')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('stock_id') }}
                  </span>
                </div>
              </th>
              <th
                class="cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('type')"
              >
                <div class="flex items-center justify-between">
                  <span>Type / Size</span>
                  <span
                    v-if="isSortedBy('type')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('type') }}
                  </span>
                </div>
              </th>
              <th
                class="cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('grade')"
              >
                <div class="flex items-center justify-between">
                  <span>Grade</span>
                  <span
                    v-if="isSortedBy('grade')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('grade') }}
                  </span>
                </div>
              </th>
              <th
                class="text-right cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('length')"
              >
                <div class="flex items-center justify-end">
                  <span>Length</span>
                  <span
                    v-if="isSortedBy('length')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('length') }}
                  </span>
                </div>
              </th>
              <th
                class="text-right cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('quantity')"
              >
                <div class="flex items-center justify-end">
                  <span>Qty</span>
                  <span
                    v-if="isSortedBy('quantity')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('quantity') }}
                  </span>
                </div>
              </th>
              <th
                class="cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('status')"
              >
                <div class="flex items-center justify-between">
                  <span>Status</span>
                  <span
                    v-if="isSortedBy('status')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('status') }}
                  </span>
                </div>
              </th>
              <th
                class="cursor-pointer hover:bg-steel-700 transition-colors"
                @click="handleSort('location')"
              >
                <div class="flex items-center justify-between">
                  <span>Location</span>
                  <span
                    v-if="isSortedBy('location')"
                    class="ml-2 text-weld-400 font-bold"
                  >
                    {{ getSortIcon('location') }}
                  </span>
                </div>
              </th>
              <th>Heat #</th>
              <th>Reserved</th>
              <th class="text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in stockItems.data"
              :key="item.id"
            >
              <td class="table-data-mono">
                <Link
                  :href="`/inventory/${item.id}`"
                  class="text-weld-400 hover:text-weld-300 font-semibold transition-colors"
                >
                  {{ item.stock_id }}
                </Link>
                <span
                  v-if="item.is_remnant"
                  class="ml-2 text-xs text-safety-400 font-mono"
                >
                  [REM]
                </span>
              </td>
              <td class="table-data-mono font-semibold">
                {{ item.type }} / {{ item.size }}
              </td>
              <td class="table-data-mono">
                <span class="bg-steel-700 px-2 py-1 rounded text-xs font-bold uppercase">
                  {{ item.grade }}
                </span>
              </td>
              <td class="table-data-mono text-right">
                {{ formatLength(item.length) }}
              </td>
              <td class="table-data-mono text-right font-bold">
                {{ item.quantity }}
              </td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'badge-free': item.status === 'free',
                    'badge-assigned': item.status === 'assigned',
                    'badge-in-progress': item.status === 'committed',
                    'badge-complete': item.status === 'used'
                  }"
                >
                  {{ item.status }}
                </span>
              </td>
              <td class="text-text-secondary">
                {{ item.stock_area?.replace('_', ' ') || '-' }}
              </td>
              <td class="table-data-mono text-xs text-text-tertiary">
                {{ item.heat_number || '-' }}
              </td>
              <td class="text-text-secondary text-sm">
                <span v-if="item.reserved_project">
                  <Link
                    :href="`/projects/${item.reserved_project.id}`"
                    class="text-weld-400 hover:text-weld-300 font-mono"
                  >
                    {{ item.reserved_project.job_number }}
                  </Link>
                </span>
                <span v-else>-</span>
              </td>
              <td class="text-right">
                <Link
                  :href="`/inventory/${item.id}/edit`"
                  class="text-forge-400 hover:text-forge-300 font-semibold text-sm transition-colors"
                >
                  Edit
                </Link>
              </td>
            </tr>
            <tr v-if="stockItems.data.length === 0">
              <td
                colspan="10"
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
                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    />
                  </svg>
                  <p class="text-text-secondary text-lg">
                    No stock items found
                  </p>
                  <p class="text-text-tertiary text-sm mt-2">
                    Try adjusting your search or filters
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="stockItems.last_page > 1"
        class="mt-6 pt-6 border-t border-steel-700 flex items-center justify-between"
      >
        <div class="text-sm text-text-tertiary font-mono">
          Showing
          <span class="font-bold text-white">{{ stockItems.from }}</span>
          to
          <span class="font-bold text-white">{{ stockItems.to }}</span>
          of
          <span class="font-bold text-white">{{ stockItems.total }}</span>
          items
        </div>
        <div class="flex space-x-2">
          <Link
            v-if="stockItems.prev_page_url"
            :href="stockItems.prev_page_url"
            class="btn-ghost text-sm py-2 px-4"
          >
            ← Previous
          </Link>
          <Link
            v-if="stockItems.next_page_url"
            :href="stockItems.next_page_url"
            class="btn-ghost text-sm py-2 px-4"
          >
            Next →
          </Link>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
      <div class="card-industrial bg-steel-900 border-steel-700">
        <div class="metric-label">
          Total Items
        </div>
        <div class="text-2xl font-bold font-mono text-white mt-2">
          {{ stockItems.total }}
        </div>
      </div>
      <div class="card-industrial bg-steel-900 border-green-900">
        <div class="metric-label text-green-400">
          Free Stock
        </div>
        <div class="text-2xl font-bold font-mono text-green-400 mt-2">
          {{ getStatusCount('free') }}
        </div>
      </div>
      <div class="card-industrial bg-steel-900 border-weld-900">
        <div class="metric-label text-weld-400">
          Assigned
        </div>
        <div class="text-2xl font-bold font-mono text-weld-400 mt-2">
          {{ getStatusCount('assigned') }}
        </div>
      </div>
      <div class="card-industrial bg-steel-900 border-steel-700">
        <div class="metric-label text-text-tertiary">
          Used
        </div>
        <div class="text-2xl font-bold font-mono text-text-tertiary mt-2">
          {{ getStatusCount('used') }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>
