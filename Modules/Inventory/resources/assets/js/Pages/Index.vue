<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    stockItems: Object,
    totalPieces: Number,
    filters: Object,
    statuses: Object,
    locations: Object,
    grades: Array,
    statusCounts: Object,
    projects: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const locationFilter = ref(props.filters?.location || '');
const gradeFilter = ref(props.filters?.grade || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');

// Bulk selection state
const selectedItems = ref([]);
const showBulkModal = ref(false);
const bulkAction = ref('');

const bulkForm = useForm({
    item_ids: [],
    action: '',
    stock_area: '',
    project_id: '',
    reason: '',
    notes: '',
});

const allSelected = computed(() => {
    if (!props.stockItems?.data?.length) return false;
    return props.stockItems.data.every(item => selectedItems.value.includes(item.id));
});

const someSelected = computed(() => {
    return selectedItems.value.length > 0 && !allSelected.value;
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.stockItems.data.map(item => item.id);
    }
};

const toggleSelect = (itemId) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(itemId);
    }
};

const openBulkModal = (action) => {
    bulkAction.value = action;
    bulkForm.action = action;
    bulkForm.item_ids = [...selectedItems.value];
    showBulkModal.value = true;
};

const closeBulkModal = () => {
    showBulkModal.value = false;
    bulkAction.value = '';
    bulkForm.reset();
};

const submitBulkAction = () => {
    bulkForm.post('/inventory-bulk-action', {
        onSuccess: () => {
            closeBulkModal();
            selectedItems.value = [];
        },
    });
};

const clearSelection = () => {
    selectedItems.value = [];
};

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
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
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

const bulkActionLabels = {
    transfer: 'Transfer Location',
    assign: 'Assign to Project',
    release: 'Release from Project',
    commit: 'Commit to Production',
    use: 'Mark as Used',
    scrap: 'Scrap Items',
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

      <!-- Bulk Action Bar -->
      <div
        v-if="selectedItems.length > 0"
        class="mb-4 p-4 bg-weld-900/30 border border-weld-700 rounded-lg flex items-center justify-between"
      >
        <div class="flex items-center gap-4">
          <span class="text-weld-400 font-semibold">
            {{ selectedItems.length }} item{{ selectedItems.length > 1 ? 's' : '' }} selected
          </span>
          <button
            class="text-text-tertiary hover:text-white text-sm"
            @click="clearSelection"
          >
            Clear
          </button>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="btn-ghost text-sm py-2 px-3"
            @click="openBulkModal('transfer')"
          >
            Transfer
          </button>
          <button
            class="btn-ghost text-sm py-2 px-3"
            @click="openBulkModal('assign')"
          >
            Assign
          </button>
          <button
            class="btn-ghost text-sm py-2 px-3"
            @click="openBulkModal('release')"
          >
            Release
          </button>
          <button
            class="btn-ghost text-sm py-2 px-3"
            @click="openBulkModal('commit')"
          >
            Commit
          </button>
          <button
            class="btn-ghost text-sm py-2 px-3"
            @click="openBulkModal('use')"
          >
            Use
          </button>
          <button
            class="text-red-400 hover:text-red-300 text-sm py-2 px-3"
            @click="openBulkModal('scrap')"
          >
            Scrap
          </button>
        </div>
      </div>

      <!-- Industrial Table -->
      <div class="overflow-x-auto scrollbar-industrial">
        <table class="table-industrial">
          <thead>
            <tr>
              <th class="w-12">
                <input
                  type="checkbox"
                  class="form-checkbox rounded bg-steel-700 border-steel-600 text-weld-500 focus:ring-weld-500 focus:ring-offset-0"
                  :checked="allSelected"
                  :indeterminate="someSelected"
                  @change="toggleSelectAll"
                >
              </th>
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
              :class="{ 'bg-weld-900/20': selectedItems.includes(item.id) }"
            >
              <td class="w-12">
                <input
                  type="checkbox"
                  class="form-checkbox rounded bg-steel-700 border-steel-600 text-weld-500 focus:ring-weld-500 focus:ring-offset-0"
                  :checked="selectedItems.includes(item.id)"
                  @change="toggleSelect(item.id)"
                >
              </td>
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
                colspan="11"
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
          Total Pieces
        </div>
        <div class="text-2xl font-bold font-mono text-white mt-2">
          {{ totalPieces }}
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

    <!-- Bulk Action Modal -->
    <Teleport to="body">
      <div
        v-if="showBulkModal"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
          <!-- Backdrop -->
          <div
            class="fixed inset-0 bg-black/70 transition-opacity"
            @click="closeBulkModal"
          />

          <!-- Modal -->
          <div class="relative bg-steel-800 border border-steel-700 rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-white mb-4">
                {{ bulkActionLabels[bulkAction] }}
              </h3>
              <p class="text-text-secondary text-sm mb-6">
                Apply action to {{ selectedItems.length }} selected item{{ selectedItems.length > 1 ? 's' : '' }}.
              </p>

              <form @submit.prevent="submitBulkAction">
                <!-- Transfer fields -->
                <div
                  v-if="bulkAction === 'transfer'"
                  class="space-y-4"
                >
                  <div>
                    <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                      Destination Location *
                    </label>
                    <select
                      v-model="bulkForm.stock_area"
                      class="input-industrial w-full"
                    >
                      <option value="">
                        Select Location
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
                </div>

                <!-- Assign fields -->
                <div
                  v-if="bulkAction === 'assign'"
                  class="space-y-4"
                >
                  <div>
                    <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                      Project *
                    </label>
                    <select
                      v-model="bulkForm.project_id"
                      class="input-industrial w-full"
                    >
                      <option value="">
                        Select Project
                      </option>
                      <option
                        v-for="project in projects"
                        :key="project.id"
                        :value="project.id"
                      >
                        {{ project.job_number }} - {{ project.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Scrap fields -->
                <div
                  v-if="bulkAction === 'scrap'"
                  class="space-y-4"
                >
                  <div>
                    <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                      Reason *
                    </label>
                    <select
                      v-model="bulkForm.reason"
                      class="input-industrial w-full"
                    >
                      <option value="">
                        Select Reason
                      </option>
                      <option value="Damaged beyond repair">
                        Damaged beyond repair
                      </option>
                      <option value="Corrosion/rust">
                        Corrosion/rust
                      </option>
                      <option value="Wrong specification">
                        Wrong specification
                      </option>
                      <option value="End of life">
                        End of life
                      </option>
                      <option value="Other">
                        Other
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Notes for all actions -->
                <div class="mt-4">
                  <label class="block text-xs text-text-tertiary uppercase tracking-wider mb-2 font-semibold">
                    Notes (Optional)
                  </label>
                  <textarea
                    v-model="bulkForm.notes"
                    class="input-industrial w-full"
                    rows="2"
                    placeholder="Add notes..."
                  />
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-steel-700">
                  <button
                    type="button"
                    class="btn-ghost"
                    @click="closeBulkModal"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="btn-primary"
                    :class="{ 'bg-red-600 hover:bg-red-700': bulkAction === 'scrap' }"
                    :disabled="bulkForm.processing || (bulkAction === 'transfer' && !bulkForm.stock_area) || (bulkAction === 'assign' && !bulkForm.project_id) || (bulkAction === 'scrap' && !bulkForm.reason)"
                  >
                    <svg
                      v-if="bulkForm.processing"
                      class="w-5 h-5 mr-2 animate-spin"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      />
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </svg>
                    Confirm
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
