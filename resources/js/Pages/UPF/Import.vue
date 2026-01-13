<!-- resources/js/Pages/UPF/Import.vue -->
<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    stats: Object,
});

const page = usePage();

const form = useForm({
    file: null,
});

const submit = () => {
    form.post(route('upf-import.store'), {
        forceFormData: true,
    });
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>

<template>
  <AppLayout title="Import UPF Material Catalog">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Import UPF Material Catalog
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Upload FabTrol Universal Product File data to populate the material catalog.
            </p>
          </div>
          <Link
            href="/import-templates"
            class="btn-secondary"
          >
            Back to Templates
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
    <div
      v-if="page.props.flash?.warning"
      class="mb-6 p-4 bg-amber-600/20 border border-amber-500 rounded text-amber-400"
    >
      {{ page.props.flash.warning }}
    </div>
    <div
      v-if="page.props.flash?.error"
      class="mb-6 p-4 bg-red-600/20 border border-red-500 rounded text-red-400"
    >
      {{ page.props.flash.error }}
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Import Form -->
      <div class="xl:col-span-2">
        <form
          class="card-industrial"
          @submit.prevent="submit"
        >
          <div class="mb-6">
            <h3 class="text-lg font-semibold text-white mb-2">
              Upload UPF CSV File
            </h3>
            <p class="text-sm text-text-secondary">
              Select a CSV file exported from FabTrol containing material types, grades, and pricing data.
            </p>
          </div>

          <div class="space-y-6">
            <div>
              <label
                for="file"
                class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2"
              >
                UPF CSV File
              </label>
              <input
                id="file"
                type="file"
                accept=".csv,.txt"
                class="input-industrial w-full"
                required
                @change="form.file = $event.target.files[0]"
              >
              <div
                v-if="form.errors.file"
                class="mt-2 text-sm text-red-400"
              >
                {{ form.errors.file }}
              </div>
            </div>

            <!-- Expected Format -->
            <div class="bg-steel-900/60 border border-steel-700 rounded-sm p-4">
              <h4 class="text-sm font-semibold text-white mb-3">
                Expected CSV Format
              </h4>
              <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                  <thead class="text-text-tertiary">
                    <tr class="border-b border-steel-700">
                      <th class="py-2 text-left">
                        Column
                      </th>
                      <th class="py-2 text-left">
                        Description
                      </th>
                      <th class="py-2 text-left">
                        Required
                      </th>
                    </tr>
                  </thead>
                  <tbody class="text-text-secondary">
                    <tr class="border-b border-steel-800">
                      <td class="py-2 text-white">
                        TYPE / TYPE_NAME
                      </td>
                      <td class="py-2">
                        Material type (e.g., W, HSS, L)
                      </td>
                      <td class="py-2 text-green-400">
                        Yes
                      </td>
                    </tr>
                    <tr class="border-b border-steel-800">
                      <td class="py-2 text-white">
                        TYPEDESC / DESCRIPTION
                      </td>
                      <td class="py-2">
                        Type description
                      </td>
                      <td class="py-2 text-text-tertiary">
                        No
                      </td>
                    </tr>
                    <tr class="border-b border-steel-800">
                      <td class="py-2 text-white">
                        GRADE / GRADE_NAME
                      </td>
                      <td class="py-2">
                        Material grade (e.g., A992, A36)
                      </td>
                      <td class="py-2 text-text-tertiary">
                        No
                      </td>
                    </tr>
                    <tr class="border-b border-steel-800">
                      <td class="py-2 text-white">
                        SIZE
                      </td>
                      <td class="py-2">
                        Section size (e.g., W14X30)
                      </td>
                      <td class="py-2 text-green-400">
                        Yes
                      </td>
                    </tr>
                    <tr class="border-b border-steel-800">
                      <td class="py-2 text-white">
                        PRICE / UNIT_PRICE
                      </td>
                      <td class="py-2">
                        Price per unit
                      </td>
                      <td class="py-2 text-text-tertiary">
                        No
                      </td>
                    </tr>
                    <tr class="border-b border-steel-800">
                      <td class="py-2 text-white">
                        WTFT / WEIGHT_FT
                      </td>
                      <td class="py-2">
                        Weight per foot (lbs)
                      </td>
                      <td class="py-2 text-text-tertiary">
                        No
                      </td>
                    </tr>
                    <tr>
                      <td class="py-2 text-white">
                        THICK / THICKNESS
                      </td>
                      <td class="py-2">
                        Nominal thickness
                      </td>
                      <td class="py-2 text-text-tertiary">
                        No
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Download Template -->
            <div class="flex items-center gap-4">
              <a
                :href="route('import-templates.upf')"
                class="text-sm text-forge-400 hover:text-forge-300 font-mono underline"
              >
                Download Sample UPF Template
              </a>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-4 border-t border-steel-700">
              <Link
                href="/import-templates"
                class="btn-secondary"
              >
                Cancel
              </Link>
              <button
                type="submit"
                class="btn-primary"
                :disabled="form.processing || !form.file"
              >
                <span v-if="form.processing">Importing...</span>
                <span v-else>Import UPF Data</span>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Current Catalog Stats -->
      <div class="space-y-6">
        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Current Catalog
          </h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-text-secondary">Material Types</span>
              <span class="text-white font-bold text-xl">{{ stats?.material_types ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-text-secondary">Price Items</span>
              <span class="text-white font-bold text-xl">{{ stats?.upf_prices ?? 0 }}</span>
            </div>
            <div class="pt-4 border-t border-steel-700">
              <div class="text-xs text-text-tertiary uppercase tracking-wider mb-1">
                Last Updated
              </div>
              <div class="text-sm text-white">
                {{ formatDate(stats?.last_import) }}
              </div>
            </div>
          </div>
        </div>

        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Import Notes
          </h3>
          <ul class="space-y-2 text-sm text-text-secondary">
            <li class="flex items-start gap-2">
              <span class="text-forge-400 mt-1">•</span>
              <span>Existing records with matching TYPE/SIZE/GRADE will be updated</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-forge-400 mt-1">•</span>
              <span>New material types and grades are created automatically</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-forge-400 mt-1">•</span>
              <span>PKEY and FILEKEY are generated if not provided</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-forge-400 mt-1">•</span>
              <span>Invalid rows are skipped and reported in the results</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-forge-400 mt-1">•</span>
              <span>Maximum file size: 10MB</span>
            </li>
          </ul>
        </div>

        <!-- Quick Links -->
        <div class="card-industrial">
          <h3 class="text-lg font-semibold text-white mb-4">
            Related Pages
          </h3>
          <div class="space-y-2">
            <a
              href="/admin/upf-prices"
              class="block p-3 bg-steel-900/60 border border-steel-700 rounded hover:border-forge-500 transition-colors"
            >
              <div class="text-sm font-semibold text-white">
                Material Catalog
              </div>
              <div class="text-xs text-text-secondary">
                View and manage UPF price items
              </div>
            </a>
            <a
              href="/admin/material-types"
              class="block p-3 bg-steel-900/60 border border-steel-700 rounded hover:border-forge-500 transition-colors"
            >
              <div class="text-sm font-semibold text-white">
                Material Types
              </div>
              <div class="text-xs text-text-secondary">
                Manage material type definitions
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
