<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
});

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    on_hold: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

const formatWeight = (lbs) => {
    if (!lbs) return '-';
    return new Intl.NumberFormat().format(Math.round(lbs)) + ' lbs';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
  <AppLayout :title="`Project: ${project.job_number}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            href="/projects"
            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
          >
            &larr; Back
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
              {{ project.name }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Job #{{ project.job_number }}
            </p>
          </div>
        </div>
        <Link
          :href="`/projects/${project.id}/edit`"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Edit Project
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Project Details -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Project Details
          </h3>
          <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Status
              </dt>
              <dd class="mt-1">
                <span
                  :class="statusColors[project.status] || 'bg-gray-100 text-gray-800'"
                  class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full"
                >
                  {{ project.status }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Customer
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ project.customer?.name || '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Job Type
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ project.job_type?.replace('_', ' ') || '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                PO Number
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ project.po_number || '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Contract Weight
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ formatWeight(project.contract_weight_lbs) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Ship Date
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ formatDate(project.ship_date) }}
              </dd>
            </div>
          </dl>
          <div
            v-if="project.notes"
            class="mt-4"
          >
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Notes
            </dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
              {{ project.notes }}
            </dd>
          </div>
        </div>
      </div>

      <!-- Assemblies -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Assemblies ({{ project.assemblies?.length || 0 }})
          </h3>
          <div
            v-if="project.assemblies?.length > 0"
            class="overflow-x-auto"
          >
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Assembly Mark
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Quantity
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Parts
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Weight Each
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                    Total Weight
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="assembly in project.assemblies"
                  :key="assembly.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ assembly.assembly_mark }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ assembly.quantity }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ assembly.parts_count || 0 }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ formatWeight(assembly.weight_each_lbs) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ formatWeight(assembly.total_weight_lbs) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p
            v-else
            class="text-gray-500 dark:text-gray-400"
          >
            No assemblies found. Import a KISS file to add assemblies.
          </p>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Total Assemblies
          </div>
          <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
            {{ project.assemblies?.length || 0 }}
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Phases
          </div>
          <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
            {{ project.phases?.length || 0 }}
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Lots
          </div>
          <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">
            {{ project.lots?.length || 0 }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
