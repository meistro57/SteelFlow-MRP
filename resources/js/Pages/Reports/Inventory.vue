<!-- resources/js/Pages/Reports/Inventory.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    totalItems: Number,
    valuation: Number,
    byType: Array,
});

const formatCurrency = (value) => {
    if (!value && value !== 0) return '$0';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(value);
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};
</script>

<template>
  <AppLayout title="Inventory Report">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Inventory Report
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              A tidy summary of available stock and valuation totals.
            </p>
          </div>
          <div class="flex gap-3">
            <a
              :href="route('reports.inventory.csv')"
              target="_blank"
              class="btn-secondary"
            >
              Export CSV
            </a>
            <a
              :href="route('reports.inventory.pdf')"
              target="_blank"
              class="btn-secondary"
            >
              Export PDF
            </a>
            <Link
              href="/reports"
              class="btn-secondary"
            >
              Back to Reports
            </Link>
          </div>
        </div>
      </div>
    </template>

    <!-- Summary cards -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Usable Stock Items
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ totalItems ?? 0 }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Items not marked as used.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Estimated Valuation
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ formatCurrency(valuation ?? 0) }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Based on length multiplied by unit cost.
        </p>
      </div>

      <div class="card-industrial">
        <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold">
          Material Types
        </div>
        <div class="mt-4 text-3xl font-bold text-white">
          {{ (byType ?? []).length }}
        </div>
        <p class="mt-2 text-sm text-text-secondary">
          Distinct types with available stock.
        </p>
      </div>
    </section>

    <!-- Breakdown table -->
    <section class="mt-10 card-industrial">
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-white">
          Inventory Breakdown by Type
        </h3>
        <p class="text-sm text-text-secondary">
          Counts and total lengths by material type.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-xs uppercase tracking-wider text-text-tertiary">
            <tr class="border-b border-steel-700">
              <th class="py-3 text-left">
                Type
              </th>
              <th class="py-3 text-right">
                Item Count
              </th>
              <th class="py-3 text-right">
                Total Length
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="type in byType ?? []"
              :key="type.type"
              class="border-b border-steel-800/80 text-text-secondary"
            >
              <td class="py-3 text-white">
                {{ type.type || 'Unknown' }}
              </td>
              <td class="py-3 text-right">
                {{ type.count ?? 0 }}
              </td>
              <td class="py-3 text-right">
                {{ formatLength(type.total_length) }}
              </td>
            </tr>
            <tr v-if="(byType ?? []).length === 0">
              <td
                colspan="3"
                class="py-6 text-center text-sm text-text-secondary"
              >
                No inventory data yet. Add stock items to populate this report.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </AppLayout>
</template>
