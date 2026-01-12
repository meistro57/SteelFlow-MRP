<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    invoices: Object,
    stats: Object,
    filters: Object,
    statuses: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

watch([search, statusFilter], ([newSearch, newStatus]) => {
    router.get(route('finance.invoices.index'), {
        search: newSearch,
        status: newStatus,
    }, {
        preserveState: true,
        replace: true,
    });
});

const formatCurrency = (amount) => {
    if (!amount) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'draft': 'bg-gray-500',
        'sent': 'bg-blue-500',
        'partial': 'bg-yellow-500',
        'paid': 'bg-green-500',
        'cancelled': 'bg-red-500',
    };
    return badges[status] || 'bg-gray-500';
};

const breadcrumbItems = [
    { label: 'Finance' },
    { label: 'Invoices' },
];
</script>

<template>
  <AppLayout title="Invoices">
    <Breadcrumb :items="breadcrumbItems" />

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Invoices
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Customer Invoicing & Payments
        </p>
      </div>
      <div class="mt-4 md:mt-0">
        <Link
          :href="route('finance.invoices.create')"
          class="btn-primary"
        >
          <PlusIcon class="h-5 w-5" />
          New Invoice
        </Link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Total Invoices
          </div>
          <div class="text-2xl font-bold font-mono text-white mt-2">
            {{ stats.total_invoices }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Draft
          </div>
          <div class="text-2xl font-bold font-mono text-gray-400 mt-2">
            {{ stats.draft }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Sent
          </div>
          <div class="text-2xl font-bold font-mono text-blue-400 mt-2">
            {{ stats.sent }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Partial
          </div>
          <div class="text-2xl font-bold font-mono text-yellow-400 mt-2">
            {{ stats.partial }}
          </div>
        </div>
      </div>

      <div class="card-glow">
        <div class="p-4">
          <div class="text-xs uppercase tracking-wider text-steel-500 font-mono">
            Outstanding
          </div>
          <div class="text-xl font-bold font-mono text-forge-400 mt-2">
            {{ formatCurrency(stats.total_outstanding) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono flex items-center gap-2">
              <MagnifyingGlassIcon class="w-4 h-4" />
              Search Invoices
            </label>
            <input
              v-model="search"
              type="text"
              placeholder="Invoice #, Project, Customer..."
              class="input w-full"
            >
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono">
              Status Filter
            </label>
            <select
              v-model="statusFilter"
              class="input w-full"
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
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="card-glow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-steel-700">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-steel-400">
              <th class="px-6 py-3 font-mono">
                Invoice #
              </th>
              <th class="px-6 py-3 font-mono">
                Project
              </th>
              <th class="px-6 py-3 font-mono">
                Customer
              </th>
              <th class="px-6 py-3 font-mono">
                Date
              </th>
              <th class="px-6 py-3 font-mono text-right">
                Amount
              </th>
              <th class="px-6 py-3 font-mono text-right">
                Paid
              </th>
              <th class="px-6 py-3 font-mono text-center">
                Status
              </th>
              <th class="px-6 py-3 font-mono text-center">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-steel-800">
            <tr
              v-if="!invoices.data || !invoices.data.length"
              class="hover:bg-steel-800/50 transition-colors"
            >
              <td
                colspan="8"
                class="px-6 py-12 text-center"
              >
                <DocumentTextIcon class="w-12 h-12 mx-auto text-steel-600 mb-3" />
                <div class="text-steel-400 uppercase tracking-wider font-mono">
                  No invoices found
                </div>
                <Link
                  :href="route('finance.invoices.create')"
                  class="btn-primary mt-4 inline-flex items-center gap-2"
                >
                  <PlusIcon class="h-5 w-5" />
                  Create First Invoice
                </Link>
              </td>
            </tr>
            <tr
              v-for="invoice in invoices.data"
              :key="invoice.id"
              class="hover:bg-steel-800/50 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="text-white font-bold font-mono">
                  {{ invoice.invoice_number }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-white font-mono">
                  {{ invoice.project?.job_number }}
                </div>
                <div class="text-xs text-steel-400">
                  {{ invoice.project?.name }}
                </div>
              </td>
              <td class="px-6 py-4 text-steel-300">
                {{ invoice.customer?.name }}
              </td>
              <td class="px-6 py-4 text-steel-300 font-mono">
                {{ formatDate(invoice.invoice_date) }}
              </td>
              <td class="px-6 py-4 text-right text-white font-mono">
                {{ formatCurrency(invoice.total_amount) }}
              </td>
              <td class="px-6 py-4 text-right text-forge-400 font-mono">
                {{ formatCurrency(invoice.paid_amount) }}
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  class="px-3 py-1 rounded-full text-xs uppercase font-bold text-white"
                  :class="getStatusBadge(invoice.status)"
                >
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <Link
                  :href="route('finance.invoices.show', invoice.id)"
                  class="text-weld-400 hover:text-weld-300 font-mono text-sm"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="invoices.data && invoices.data.length"
        class="px-6 py-4 border-t border-steel-700 flex items-center justify-between"
      >
        <div class="text-sm text-steel-400">
          Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} invoices
        </div>
        <div class="flex gap-2">
          <Link
            v-if="invoices.prev_page_url"
            :href="invoices.prev_page_url"
            class="btn-secondary py-1 px-3 text-sm"
          >
            Previous
          </Link>
          <Link
            v-if="invoices.next_page_url"
            :href="invoices.next_page_url"
            class="btn-secondary py-1 px-3 text-sm"
          >
            Next
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
