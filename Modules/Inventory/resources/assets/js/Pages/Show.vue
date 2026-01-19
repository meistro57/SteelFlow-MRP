<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    stockItem: Object,
});

const showActionMenu = ref(false);

const quickAction = (action) => {
    showActionMenu.value = false;
    router.post(`/inventory/${props.stockItem.id}/${action}`);
};

const canDoAction = (action) => {
    const status = props.stockItem.status;
    const transitions = {
        free: ['assigned', 'committed', 'used', 'scrapped'],
        assigned: ['free', 'committed', 'used', 'scrapped'],
        committed: ['free', 'assigned', 'used', 'scrapped'],
        used: ['free', 'scrapped'],
        scrapped: [],
    };

    const actionToStatus = {
        assign: 'assigned',
        release: 'free',
        commit: 'committed',
        'mark-used': 'used',
        return: 'free',
        scrap: 'scrapped',
    };

    const targetStatus = actionToStatus[action];
    return transitions[status]?.includes(targetStatus) ?? false;
};

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
        free: 'badge-free',
        assigned: 'badge-assigned',
        committed: 'badge-in-progress',
        used: 'badge-complete',
        scrapped: 'badge-cancelled',
    };
    return colors[status] || 'badge';
};

const getMovementTypeColor = (type) => {
    const colors = {
        receive: 'text-green-400',
        assign: 'text-blue-400',
        commit: 'text-weld-400',
        use: 'text-forge-400',
        return: 'text-safety-400',
        adjust: 'text-yellow-400',
        transfer: 'text-purple-400',
    };
    return colors[type] || 'text-text-secondary';
};
</script>

<template>
  <AppLayout :title="`Stock: ${stockItem.stock_id}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            href="/inventory"
            class="text-text-secondary hover:text-white transition-colors mr-4"
          >
            &larr; Back to Inventory
          </Link>
          <div>
            <h1 class="text-3xl font-bold text-white flex items-center">
              {{ stockItem.stock_id }}
              <span
                v-if="stockItem.is_remnant"
                class="ml-3 text-sm font-mono text-safety-400 bg-safety-900 px-2 py-1 rounded"
              >
                REMNANT
              </span>
            </h1>
            <p class="text-text-secondary mt-1 font-mono text-sm">
              {{ stockItem.type }} {{ stockItem.size }} - {{ stockItem.grade }}
            </p>
          </div>
        </div>
        <div class="flex gap-3">
          <!-- Quick Actions Dropdown -->
          <div
            v-if="stockItem.status !== 'scrapped'"
            class="relative"
          >
            <button
              class="btn-ghost flex items-center"
              @click="showActionMenu = !showActionMenu"
            >
              <svg
                class="w-5 h-5 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
              Actions
              <svg
                class="w-4 h-4 ml-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <div
              v-if="showActionMenu"
              class="absolute right-0 mt-2 w-56 bg-steel-800 border border-steel-700 rounded-lg shadow-xl z-50"
            >
              <div class="py-1">
                <!-- Location Actions -->
                <div class="px-3 py-2 text-xs font-semibold text-text-tertiary uppercase tracking-wider border-b border-steel-700">
                  Location
                </div>
                <Link
                  :href="`/inventory/${stockItem.id}/transfer`"
                  class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-purple-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                    />
                  </svg>
                  Transfer Location
                </Link>

                <!-- Status Actions -->
                <div class="px-3 py-2 text-xs font-semibold text-text-tertiary uppercase tracking-wider border-b border-steel-700 mt-1">
                  Status Changes
                </div>
                <Link
                  v-if="canDoAction('assign')"
                  :href="`/inventory/${stockItem.id}/assign`"
                  class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-blue-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                  Assign to Project
                </Link>
                <button
                  v-if="canDoAction('release')"
                  class="w-full flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                  @click="quickAction('release')"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-green-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  Release from Project
                </button>
                <button
                  v-if="canDoAction('commit')"
                  class="w-full flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                  @click="quickAction('commit')"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-weld-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                  </svg>
                  Commit to Production
                </button>
                <button
                  v-if="canDoAction('mark-used')"
                  class="w-full flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                  @click="quickAction('mark-used')"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-forge-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  Mark as Used
                </button>
                <button
                  v-if="canDoAction('return')"
                  class="w-full flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                  @click="quickAction('return')"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-safety-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                    />
                  </svg>
                  Return to Free
                </button>

                <!-- Material Actions -->
                <div class="px-3 py-2 text-xs font-semibold text-text-tertiary uppercase tracking-wider border-b border-steel-700 mt-1">
                  Material
                </div>
                <Link
                  :href="`/inventory/${stockItem.id}/adjust`"
                  class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-yellow-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                    />
                  </svg>
                  Adjust Quantity
                </Link>
                <Link
                  :href="`/inventory/${stockItem.id}/remnant`"
                  class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-steel-700 hover:text-white transition-colors"
                >
                  <svg
                    class="w-4 h-4 mr-3 text-safety-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                    />
                  </svg>
                  Create Remnant
                </Link>

                <!-- Destructive Actions -->
                <div class="border-t border-steel-700 mt-1" />
                <Link
                  v-if="canDoAction('scrap')"
                  :href="`/inventory/${stockItem.id}/scrap`"
                  class="flex items-center px-4 py-2 text-sm text-red-400 hover:bg-red-900/50 transition-colors"
                >
                  <svg
                    class="w-4 h-4 mr-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                  Scrap Item
                </Link>
              </div>
            </div>

            <!-- Backdrop to close menu -->
            <div
              v-if="showActionMenu"
              class="fixed inset-0 z-40"
              @click="showActionMenu = false"
            />
          </div>

          <Link
            :href="`/inventory/${stockItem.id}/edit`"
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
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              />
            </svg>
            Edit
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Status
          </div>
          <div class="mt-2">
            <span
              :class="getStatusColor(stockItem.status)"
              class="badge text-base"
            >
              {{ stockItem.status }}
            </span>
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Length
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ formatLength(stockItem.length) }}
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Quantity
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ stockItem.quantity }}
          </div>
        </div>

        <div class="card-industrial bg-steel-900 border-steel-700">
          <div class="metric-label">
            Stock Area
          </div>
          <div class="text-lg font-semibold font-mono text-weld-400 mt-2 uppercase">
            {{ stockItem.stock_area?.replace('_', ' ') || 'Not Set' }}
          </div>
        </div>
      </div>

      <!-- Main Details -->
      <div class="card-industrial">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-weld-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Stock Details
        </h3>

        <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div>
            <dt class="metric-label">
              Material Type
            </dt>
            <dd class="mt-1 text-base font-semibold text-white font-mono">
              {{ stockItem.type }}
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              Size
            </dt>
            <dd class="mt-1 text-base font-semibold text-white font-mono">
              {{ stockItem.size }}
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              Grade
            </dt>
            <dd class="mt-1">
              <span class="bg-steel-700 px-3 py-1 rounded text-sm font-bold uppercase text-weld-400">
                {{ stockItem.grade }}
              </span>
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              Reserved Project
            </dt>
            <dd class="mt-1 text-base text-white">
              <Link
                v-if="stockItem.reserved_project"
                :href="`/projects/${stockItem.reserved_project.id}`"
                class="text-weld-400 hover:text-weld-300 font-mono font-semibold transition-colors"
              >
                {{ stockItem.reserved_project.job_number }}
              </Link>
              <span
                v-else
                class="text-text-tertiary"
              >-</span>
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              Heat Number
            </dt>
            <dd class="mt-1 text-base font-mono text-white">
              {{ stockItem.heat_number || '-' }}
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              PO Number
            </dt>
            <dd class="mt-1 text-base font-mono text-white">
              {{ stockItem.po_number || '-' }}
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              Receive Date
            </dt>
            <dd class="mt-1 text-base font-mono text-white">
              {{ formatDate(stockItem.receive_date) }}
            </dd>
          </div>

          <div>
            <dt class="metric-label">
              Country of Origin
            </dt>
            <dd class="mt-1 text-base text-white">
              {{ stockItem.country_of_origin || '-' }}
            </dd>
          </div>

          <div v-if="stockItem.parent_stock">
            <dt class="metric-label">
              Parent Stock
            </dt>
            <dd class="mt-1">
              <Link
                :href="`/inventory/${stockItem.parent_stock.id}`"
                class="text-weld-400 hover:text-weld-300 font-mono font-semibold transition-colors"
              >
                {{ stockItem.parent_stock.stock_id }}
              </Link>
            </dd>
          </div>
        </dl>

        <div
          v-if="stockItem.notes"
          class="mt-6 pt-6 border-t border-steel-700"
        >
          <dt class="metric-label mb-2">
            Notes
          </dt>
          <dd class="text-base text-text-secondary whitespace-pre-wrap font-mono bg-steel-900 p-4 rounded border border-steel-700">
            {{ stockItem.notes }}
          </dd>
        </div>
      </div>

      <!-- Movement History -->
      <div class="card-industrial">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-white flex items-center">
            <svg
              class="w-5 h-5 mr-2 text-forge-400"
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
            Movement History
            <span class="ml-2 text-sm font-mono text-text-tertiary font-normal">
              ({{ stockItem.movements?.length || 0 }} movements)
            </span>
          </h3>
        </div>

        <div
          v-if="stockItem.movements?.length > 0"
          class="space-y-3"
        >
          <div
            v-for="movement in stockItem.movements"
            :key="movement.id"
            class="bg-steel-900 border border-steel-700 rounded-lg p-4 hover:border-steel-600 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span
                    :class="getMovementTypeColor(movement.movement_type)"
                    class="font-bold uppercase text-sm tracking-wider"
                  >
                    {{ movement.movement_type }}
                  </span>
                  <span class="text-text-tertiary text-xs font-mono">
                    {{ formatDateTime(movement.created_at) }}
                  </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div v-if="movement.from_status || movement.to_status">
                    <span class="text-text-tertiary text-xs uppercase tracking-wider">Status:</span>
                    <div class="mt-1 font-mono">
                      <span class="text-text-secondary">{{ movement.from_status || '-' }}</span>
                      <span class="text-weld-400 mx-2">→</span>
                      <span class="text-white font-semibold">{{ movement.to_status || '-' }}</span>
                    </div>
                  </div>

                  <div v-if="movement.from_area || movement.to_area">
                    <span class="text-text-tertiary text-xs uppercase tracking-wider">Location:</span>
                    <div class="mt-1 font-mono">
                      <span class="text-text-secondary">{{ movement.from_area || '-' }}</span>
                      <span class="text-weld-400 mx-2">→</span>
                      <span class="text-white font-semibold">{{ movement.to_area || '-' }}</span>
                    </div>
                  </div>

                  <div>
                    <span class="text-text-tertiary text-xs uppercase tracking-wider">Quantity:</span>
                    <div class="mt-1 font-mono text-white font-bold">
                      {{ movement.quantity }}
                    </div>
                  </div>

                  <div v-if="movement.notes">
                    <span class="text-text-tertiary text-xs uppercase tracking-wider">Notes:</span>
                    <div class="mt-1 text-text-secondary text-xs font-mono">
                      {{ movement.notes }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-else
          class="text-center py-12"
        >
          <svg
            class="w-16 h-16 text-steel-600 mx-auto mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            />
          </svg>
          <p class="text-text-secondary text-lg">
            No movement history recorded
          </p>
          <p class="text-text-tertiary text-sm mt-2">
            Movements will appear here when stock is received, assigned, or used
          </p>
        </div>
      </div>

      <!-- Remnants -->
      <div
        v-if="stockItem.remnants?.length > 0"
        class="card-industrial"
      >
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
          <svg
            class="w-5 h-5 mr-2 text-safety-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Remnants Created
          <span class="ml-2 text-sm font-mono text-text-tertiary font-normal">
            ({{ stockItem.remnants.length }})
          </span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link
            v-for="remnant in stockItem.remnants"
            :key="remnant.id"
            :href="`/inventory/${remnant.id}`"
            class="bg-steel-900 border border-steel-700 rounded-lg p-4 hover:border-weld-600 transition-colors group"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="text-weld-400 group-hover:text-weld-300 font-mono font-bold transition-colors">
                  {{ remnant.stock_id }}
                </div>
                <div class="text-text-tertiary text-sm mt-1 font-mono">
                  {{ remnant.type }} {{ remnant.size }}
                </div>
              </div>
              <div class="text-right">
                <div class="text-white font-bold font-mono">
                  {{ formatLength(remnant.length) }}
                </div>
                <div class="text-text-tertiary text-xs mt-1">
                  Qty: {{ remnant.quantity }}
                </div>
              </div>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
