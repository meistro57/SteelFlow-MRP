<!-- resources/js/Pages/GasBottles/Index.vue -->
<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    rentals: Object,
    stats: Object,
    filters: Object,
    customers: Array,
});

const page = usePage();

const localFilters = ref({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    customer_id: props.filters?.customer_id ?? '',
});

const applyFilters = () => {
    router.get(route('gas-bottles.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    localFilters.value = { search: '', status: '', customer_id: '' };
    router.get(route('gas-bottles.index'));
};

const getStatusColor = (status) => {
    const colors = {
        reserved: 'bg-blue-600/20 text-blue-400',
        active: 'bg-green-600/20 text-green-400',
        closed: 'bg-steel-600 text-steel-300',
        lost: 'bg-red-600/20 text-red-400',
        damaged: 'bg-amber-600/20 text-amber-400',
    };
    return colors[status] || 'bg-steel-600 text-steel-200';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const isOverdue = (rental) => {
    if (!rental.due_back_at || rental.status !== 'active') return false;
    return new Date(rental.due_back_at) < new Date();
};

const formatCurrency = (cents) => {
    if (!cents) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
};
</script>

<template>
  <AppLayout title="Gas Bottle Rentals">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Gas Bottle Rentals
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Track gas bottle inventory, rentals, and customer assignments.
            </p>
          </div>
          <Link
            :href="route('gas-bottles.create')"
            class="btn-primary"
          >
            New Rental
          </Link>
        </div>
      </div>
    </template>

    <!-- Flash Messages -->
    <div
      v-if="page.props.flash?.success"
      class="mb-6 p-4 bg-green-600/20 border border-green-500 rounded text-green-400"
    >
      {{ page.props.flash.success }}
    </div>

    <!-- Stats cards -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Total Rentals
        </div>
        <div class="mt-2 text-3xl font-bold text-white">
          {{ stats?.total ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Active
        </div>
        <div class="mt-2 text-3xl font-bold text-green-400">
          {{ stats?.active ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Reserved
        </div>
        <div class="mt-2 text-3xl font-bold text-blue-400">
          {{ stats?.reserved ?? 0 }}
        </div>
      </div>
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Overdue
        </div>
        <div class="mt-2 text-3xl font-bold text-red-400">
          {{ stats?.overdue ?? 0 }}
        </div>
      </div>
    </section>

    <!-- Filters -->
    <section class="card-industrial mb-6">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label
            for="search"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Search
          </label>
          <input
            id="search"
            v-model="localFilters.search"
            type="text"
            placeholder="Bottle code..."
            class="input-industrial"
            @keyup.enter="applyFilters"
          >
        </div>
        <div>
          <label
            for="status"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Status
          </label>
          <select
            id="status"
            v-model="localFilters.status"
            class="input-industrial"
          >
            <option value="">
              All Statuses
            </option>
            <option value="reserved">
              Reserved
            </option>
            <option value="active">
              Active
            </option>
            <option value="closed">
              Closed
            </option>
            <option value="lost">
              Lost
            </option>
            <option value="damaged">
              Damaged
            </option>
          </select>
        </div>
        <div>
          <label
            for="customer"
            class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
          >
            Customer
          </label>
          <select
            id="customer"
            v-model="localFilters.customer_id"
            class="input-industrial"
          >
            <option value="">
              All Customers
            </option>
            <option
              v-for="customer in customers"
              :key="customer.id"
              :value="customer.id"
            >
              {{ customer.code }} - {{ customer.name }}
            </option>
          </select>
        </div>
        <button
          type="button"
          class="btn-primary"
          @click="applyFilters"
        >
          Apply
        </button>
        <button
          type="button"
          class="btn-secondary"
          @click="clearFilters"
        >
          Clear
        </button>
      </div>
    </section>

    <!-- Rentals Table -->
    <section class="card-industrial">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Bottle
              </th>
              <th class="py-3 text-left">
                Customer
              </th>
              <th class="py-3 text-left">
                Status
              </th>
              <th class="py-3 text-left">
                Checked Out
              </th>
              <th class="py-3 text-left">
                Due Back
              </th>
              <th class="py-3 text-left">
                Deposit
              </th>
              <th class="py-3 text-left">
                Rental Rate
              </th>
              <th class="py-3 text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="rental in rentals.data ?? []"
              :key="rental.id"
              class="border-b border-steel-800/80 text-text-secondary"
              :class="{ 'bg-red-900/10': isOverdue(rental) }"
            >
              <td class="py-3">
                <div class="text-white font-mono font-semibold">
                  {{ rental.stock_item?.internal_code ?? 'N/A' }}
                </div>
                <div class="text-xs text-text-tertiary">
                  {{ rental.stock_item?.material?.description ?? '' }}
                </div>
              </td>
              <td class="py-3">
                <div class="text-white">
                  {{ rental.customer?.name ?? 'N/A' }}
                </div>
                <div class="text-xs text-text-tertiary">
                  {{ rental.customer?.code ?? '' }}
                </div>
              </td>
              <td class="py-3">
                <span
                  class="px-2 py-1 text-xs rounded-sm capitalize"
                  :class="getStatusColor(rental.status)"
                >
                  {{ rental.status?.replace('_', ' ') ?? 'unknown' }}
                </span>
              </td>
              <td class="py-3">
                {{ formatDate(rental.checked_out_at) }}
              </td>
              <td class="py-3">
                <span :class="{ 'text-red-400': isOverdue(rental) }">
                  {{ formatDate(rental.due_back_at) }}
                </span>
              </td>
              <td class="py-3">
                {{ formatCurrency(rental.deposit_cents) }}
              </td>
              <td class="py-3">
                <span v-if="rental.rental_rate_cents">
                  {{ formatCurrency(rental.rental_rate_cents) }}/{{ rental.rental_rate_period }}
                </span>
                <span v-else>-</span>
              </td>
              <td class="py-3 text-right">
                <Link
                  :href="route('gas-bottles.show', rental.id)"
                  class="btn-secondary text-sm"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="(rentals.data ?? []).length === 0">
              <td
                colspan="8"
                class="py-8 text-center text-sm text-text-secondary"
              >
                No gas bottle rentals found. Create your first rental to get started.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="rentals.links?.length > 3"
        class="mt-6 flex items-center justify-between border-t border-steel-700 pt-4"
      >
        <div class="text-sm text-text-secondary">
          Showing {{ rentals.from ?? 0 }} to {{ rentals.to ?? 0 }} of {{ rentals.total ?? 0 }} rentals
        </div>
        <div class="flex gap-2">
          <Link
            v-for="link in rentals.links"
            :key="link.label"
            :href="link.url ?? '#'"
            class="px-3 py-1 text-sm rounded"
            :class="link.active ? 'bg-forge-500 text-white' : 'bg-steel-800 text-text-secondary hover:bg-steel-700'"
          >
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span v-html="link.label" />
          </Link>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
