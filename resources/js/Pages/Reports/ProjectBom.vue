<!-- resources/js/Pages/Reports/ProjectBom.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    project: Object,
    assemblies: Array,
});

const formatWeight = (value) => {
    if (value === null || value === undefined) return '-';
    return `${Number(value).toFixed(2)} lbs`;
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};
</script>

<template>
  <AppLayout title="Project BOM Report">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              BOM Report: {{ project?.name ?? 'Project' }}
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Job {{ project?.job_number ?? 'N/A' }} • {{ project?.customer?.name ?? 'No customer' }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <a
              :href="route('reports.bom.csv', project.id)"
              target="_blank"
              class="btn-secondary"
            >
              Export CSV
            </a>
            <a
              :href="route('reports.bom.pdf', project.id)"
              target="_blank"
              class="btn-secondary"
            >
              Export PDF
            </a>
            <button
              type="button"
              class="btn-secondary"
              @click="window.print()"
            >
              Print BOM
            </button>
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

    <!-- Assembly breakdown -->
    <section class="space-y-8">
      <div
        v-for="assembly in assemblies ?? []"
        :key="assembly.id"
        class="card-industrial"
      >
        <div class="mb-4">
          <h3 class="text-lg font-semibold text-white">
            Assembly: {{ assembly.mark ?? 'Mark' }} — {{ assembly.description ?? 'No description' }}
          </h3>
          <p class="text-sm text-text-secondary">
            Quantity: {{ assembly.quantity ?? 0 }}
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-text-tertiary">
              <tr class="border-b border-steel-700">
                <th class="py-3 text-left">
                  Part Mark
                </th>
                <th class="py-3 text-right">
                  Qty
                </th>
                <th class="py-3 text-left">
                  Material
                </th>
                <th class="py-3 text-left">
                  Size
                </th>
                <th class="py-3 text-right">
                  Length
                </th>
                <th class="py-3 text-right">
                  Weight (ea)
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="part in assembly.parts ?? []"
                :key="part.id"
                class="border-b border-steel-800/80 text-text-secondary"
              >
                <td class="py-3 text-white">
                  {{ part.part_mark ?? 'N/A' }}
                </td>
                <td class="py-3 text-right">
                  {{ part.quantity ?? 0 }}
                </td>
                <td class="py-3">
                  {{ part.grade ?? part.material?.grade ?? 'N/A' }}
                </td>
                <td class="py-3">
                  {{ part.size_imperial ?? part.material?.size_imperial ?? 'N/A' }}
                </td>
                <td class="py-3 text-right">
                  {{ formatLength(part.length) }}
                </td>
                <td class="py-3 text-right">
                  {{ formatWeight(part.weight_each_lbs) }}
                </td>
              </tr>
              <tr v-if="(assembly.parts ?? []).length === 0">
                <td
                  colspan="6"
                  class="py-6 text-center text-sm text-text-secondary"
                >
                  No parts recorded for this assembly yet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div
        v-if="(assemblies ?? []).length === 0"
        class="card-industrial text-sm text-text-secondary"
      >
        No assemblies found for this project. Upload a KISS file to generate the BOM report.
      </div>
    </section>
  </AppLayout>
</template>
