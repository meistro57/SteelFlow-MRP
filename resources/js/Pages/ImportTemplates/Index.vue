<!-- resources/js/Pages/ImportTemplates/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    templates: Object,
});

const importLinks = {
    customers: { route: 'customers.import', label: 'Import Customers' },
    upf: { route: 'upf-import.index', label: 'Import UPF Data' },
};

const templateDetails = {
    customers: {
        icon: 'users',
        color: 'text-blue-400',
        fields: ['code', 'name', 'address_1', 'address_2', 'city', 'state', 'zip', 'country', 'phone', 'email', 'notes', 'is_active'],
        notes: 'Rows matched by code or email will update existing records. Rows with missing names are skipped.',
    },
    kiss: {
        icon: 'file-code',
        color: 'text-green-400',
        fields: ['SHP records: Mark, Qty, Description, WeightEach, WeightTotal, Remark, Drawing', 'DET records: AssemblyMark, PartMark, QtyEach, Description, Material, Size, Length, WeightEach, WeightTotal'],
        notes: 'SHP lines define assemblies. DET lines define parts linked to assemblies. Length format: feet-inches-sixteenths (e.g., 20-0-0).',
    },
    xsr: {
        icon: 'table',
        color: 'text-yellow-400',
        fields: ['AssemblyMark (or Assembly)', 'PartMark (or Mark)', 'Quantity (or Qty)', 'Material (or Grade)', 'Shape (or Size)', 'Length', 'Weight'],
        notes: 'Header-based CSV format with flexible column names. Parts without an assembly mark are assigned to DETACHED.',
    },
    upf: {
        icon: 'database',
        color: 'text-purple-400',
        fields: ['TYPE', 'TYPEDESC', 'GRADE', 'SIZE', 'PRICE', 'PUNIT', 'THICK', 'WTFT', 'PKEY', 'FILEKEY', 'ORDERKEY'],
        notes: 'Material catalog import from FabTrol DBF exports. Creates material types, grades, and pricing records.',
    },
};
</script>

<template>
  <AppLayout title="Import Templates">
    <Head title="Import Templates - SteelFlow MRP" />

    <template #header>
      <div class="flex flex-col gap-2">
        <h2 class="font-semibold text-2xl text-white leading-tight">
          Import Templates
        </h2>
        <p class="text-text-secondary font-mono text-sm">
          Download template CSV files to understand the required format for each import type.
        </p>
      </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="(template, key) in templates"
        :key="key"
        class="card-industrial"
      >
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-white">
              {{ template.name }}
            </h3>
            <p class="text-sm text-text-secondary mt-1">
              {{ template.description }}
            </p>
          </div>
          <a
            :href="route('import-templates.download', { type: key })"
            class="btn-primary shrink-0"
          >
            Download
          </a>
        </div>

        <div class="bg-steel-900/60 border border-steel-700 rounded-sm p-4 mb-4">
          <div class="text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
            Expected Fields
          </div>
          <div class="space-y-1">
            <div
              v-for="field in templateDetails[key]?.fields ?? []"
              :key="field"
              class="text-sm text-text-secondary font-mono"
            >
              {{ field }}
            </div>
          </div>
        </div>

        <div
          v-if="templateDetails[key]?.notes"
          class="text-xs text-text-tertiary"
        >
          <span class="font-semibold">Note:</span> {{ templateDetails[key].notes }}
        </div>

        <div class="mt-4 pt-4 border-t border-steel-700 flex items-center justify-between">
          <div class="text-xs text-text-tertiary">
            <span class="font-semibold">Filename:</span>
            <span class="font-mono ml-1">{{ template.filename }}</span>
          </div>
          <Link
            v-if="importLinks[key]"
            :href="route(importLinks[key].route)"
            class="btn-secondary text-sm"
          >
            {{ importLinks[key].label }}
          </Link>
        </div>
      </div>
    </div>

    <section class="mt-10 card-industrial">
      <h3 class="text-lg font-semibold text-white mb-4">
        Import Guidelines
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-text-secondary">
        <div>
          <h4 class="font-semibold text-white mb-2">
            General Tips
          </h4>
          <ul class="space-y-1 list-disc list-inside">
            <li>Use UTF-8 encoding for all CSV files</li>
            <li>Headers are case-insensitive and normalized to slugs</li>
            <li>Empty rows are skipped automatically</li>
            <li>Existing records matched by key fields will be updated</li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold text-white mb-2">
            Data Integrity
          </h4>
          <ul class="space-y-1 list-disc list-inside">
            <li>All imports run within database transactions</li>
            <li>Validation errors are reported with row numbers</li>
            <li>Weight calculations support dual units (lbs/kg)</li>
            <li>BOM imports trigger automatic weight recalculation</li>
          </ul>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
