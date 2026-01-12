<!-- resources/js/Pages/Assemblies/Show.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    assembly: Object,
});

const formatWeight = (lbs) => {
    if (!lbs) return '-';
    return new Intl.NumberFormat().format(Math.round(lbs)) + ' lbs';
};

const formatLength = (length) => {
    if (!length) return '-';
    const feet = Math.floor(length);
    const inches = Math.round((length - feet) * 12);
    return inches > 0 ? `${feet}' ${inches}"` : `${feet}'`;
};
</script>

<template>
  <AppLayout title="Assembly Details">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link
            :href="route('projects.show', assembly.project_id)"
            class="text-steel-400 hover:text-white mr-4"
          >
            &larr; Project
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-white leading-tight">
              Assembly: {{ assembly.mark }}
            </h2>
            <p class="text-sm text-steel-400">
              Job #{{ assembly.project.job_number }} • {{ assembly.project.name }}
            </p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <Link
            :href="route('assemblies.edit', assembly.id)"
            class="btn-secondary"
          >
            Edit Assembly
          </Link>
          <Link
            :href="route('assemblies.parts.create', assembly.id)"
            class="btn-forge"
          >
            Add Part
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Assembly Details Card -->
      <div class="card-industrial">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div>
            <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
              Status
            </dt>
            <dd class="mt-1">
              <span class="px-2 py-0.5 rounded text-xs font-bold bg-steel-700 text-steel-300 uppercase">
                {{ assembly.instances?.[0]?.status || 'Not Started' }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
              Quantity
            </dt>
            <dd class="mt-1 text-sm text-white">
              {{ assembly.quantity }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
              Weight Each
            </dt>
            <dd class="mt-1 text-sm text-white">
              {{ formatWeight(assembly.weight_each_lbs) }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
              Total Weight
            </dt>
            <dd class="mt-1 text-sm text-white">
              {{ formatWeight(assembly.total_weight_lbs) }}
            </dd>
          </div>
          <div v-if="assembly.assembly_type">
            <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
              Type
            </dt>
            <dd class="mt-1 text-sm text-white">
              {{ assembly.assembly_type }}
            </dd>
          </div>
          <div v-if="assembly.drawing">
            <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
              Drawing
            </dt>
            <dd class="mt-1 text-sm text-forge-400">
              <Link :href="route('drawings.show', assembly.drawing_id)">
                {{ assembly.drawing.mark || assembly.drawing.filename }}
              </Link>
            </dd>
          </div>
        </div>

        <div
          v-if="assembly.description"
          class="mt-6 pt-6 border-t border-steel-700"
        >
          <dt class="text-xs font-bold text-steel-500 uppercase tracking-widest">
            Description
          </dt>
          <dd class="mt-1 text-sm text-steel-300">
            {{ assembly.description }}
          </dd>
        </div>
      </div>

      <!-- Parts List -->
      <div class="bg-steel-800/50 rounded-sm border border-steel-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-steel-700 bg-steel-800 flex justify-between items-center">
          <h3 class="text-sm font-bold text-white uppercase tracking-widest">
            Parts Component List ({{ assembly.parts?.length || 0 }})
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-steel-700">
            <thead class="bg-steel-800/80">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Part Mark
                </th>
                <th class="px-6 py-3 text-left text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Material
                </th>
                <th class="px-6 py-3 text-left text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Size
                </th>
                <th class="px-6 py-3 text-right text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Length
                </th>
                <th class="px-6 py-3 text-right text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Qty
                </th>
                <th class="px-6 py-3 text-right text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Weight (ea)
                </th>
                <th class="px-6 py-3 text-right text-xs font-bold text-steel-500 uppercase tracking-widest">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-steel-700 bg-steel-900/30">
              <tr
                v-for="part in assembly.parts"
                :key="part.id"
                class="hover:bg-steel-800/50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                  {{ part.part_mark }}
                  <span
                    v-if="part.is_main_member"
                    class="ml-2 text-[10px] bg-forge-900 text-forge-300 px-1 rounded border border-forge-700 uppercase"
                  >Main</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-400">
                  {{ part.grade || part.material?.grade || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-400">
                  {{ part.size_imperial || part.material?.size_imperial || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-400 text-right font-mono">
                  {{ formatLength(part.length) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-400 text-right">
                  {{ part.quantity }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-400 text-right">
                  {{ formatWeight(part.weight_each_lbs) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('parts.edit', part.id)"
                    class="text-forge-500 hover:text-forge-400 mr-3"
                  >
                    Edit
                  </Link>
                  <Link
                    :href="route('parts.destroy', part.id)"
                    method="delete"
                    as="button"
                    class="text-red-500 hover:text-red-400"
                    on-before="() => confirm('Are you sure you want to delete this part?')"
                  >
                    Delete
                  </Link>
                </td>
              </tr>
              <tr v-if="!assembly.parts?.length">
                <td
                  colspan="7"
                  class="px-6 py-12 text-center text-steel-500 italic"
                >
                  No parts defined for this assembly.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
