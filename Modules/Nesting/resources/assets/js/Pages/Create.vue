<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
});

const form = useForm({
    project_id: props.project.id,
    type: 'linear',
    kerf_allowance: 0.125,
});

const submit = () => {
    form.post(route('nesting.store'));
};
</script>

<template>
  <AppLayout title="Create Nesting">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight text-glow-forge uppercase tracking-wide">
        Run Nesting Optimizer
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border-t-4 border-weld-500">
          <div class="p-6 bg-gray-50 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-bold text-gray-700">
                  Optimizer Configuration
                </h3>
                <p class="text-sm text-steel-500">
                  Project: <span class="font-bold text-steel-700">{{ project.job_number }} - {{ project.name }}</span>
                </p>
              </div>
              <div class="text-right">
                <span class="bg-steel-100 text-steel-800 text-xs font-mono px-2 py-1 rounded">V1-FFD Algorithm</span>
              </div>
            </div>
          </div>

          <form
            class="p-8"
            @submit.prevent="submit"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div>
                <label class="block text-sm font-bold text-steel-700 mb-2 uppercase tracking-wider">Optimizer Type</label>
                <select
                  v-model="form.type"
                  class="block w-full border-steel-300 rounded-md shadow-sm focus:ring-weld-500 focus:border-weld-500 bg-white"
                >
                  <option value="linear">
                    1D Linear Length (Bars/Beams)
                  </option>
                  <option value="plate">
                    2D Plate Nesting (Planned)
                  </option>
                </select>
                <p class="mt-2 text-xs text-steel-500">
                  Linear nesting optimizes for long members like beams, angles, and tubes.
                </p>
              </div>

              <div>
                <label class="block text-sm font-bold text-steel-700 mb-2 uppercase tracking-wider">Kerf Allowance (Inches)</label>
                <div class="relative">
                  <input 
                    v-model="form.kerf_allowance" 
                    type="number" 
                    step="0.001" 
                    class="block w-full border-steel-300 rounded-md shadow-sm focus:ring-weld-500 focus:border-weld-500 pr-12" 
                  >
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-steel-400 sm:text-sm">in</span>
                  </div>
                </div>
                <p class="mt-2 text-xs text-steel-500">
                  Width of the saw blade or cutting torch material removal.
                </p>
              </div>
            </div>

            <div class="mt-8 p-4 bg-blue-50 border border-blue-100 rounded-md">
              <h4 class="text-sm font-bold text-blue-800 mb-1 flex items-center">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 mr-1"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                Optimizer Logic
              </h4>
              <p class="text-xs text-blue-700">
                This process will scan all un-nested part instances in Project {{ project.job_number }}. It will attempt to find matching material in your 'Free' stock first. If no matching stock is found, it will suggest a purchase bar of standard 20' length.
              </p>
            </div>

            <div class="flex items-center justify-between mt-10 pt-6 border-t border-gray-100">
              <Link
                :href="route('projects.show', project.id)"
                class="text-steel-500 hover:text-steel-700 font-medium text-sm"
              >
                Back to Project
              </Link>

              <button 
                class="btn-primary px-10 py-3 shadow-lg flex items-center gap-2" 
                :disabled="form.processing"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
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
                <span>{{ form.processing ? 'Calculating...' : 'Run Optimizer' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
