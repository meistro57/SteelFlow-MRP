<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    projects: Array,
    loads: Array,
    selectedProjectId: [String, Number],
});

const form = useForm({
    load_id: null,
});

const selectedProject = ref(props.selectedProjectId || null);
const availableLoads = ref(props.loads || []);

watch(selectedProject, (newProjectId) => {
    if (newProjectId) {
        window.location.href = route('finance.invoices.create', { project_id: newProjectId });
    }
});

const submit = () => {
    form.post(route('finance.invoices.store'));
};

const formatWeight = (lbs) => {
    if (!lbs) return '0 lbs';
    return new Intl.NumberFormat().format(Math.round(lbs)) + ' lbs';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const breadcrumbItems = [
    { label: 'Finance' },
    { label: 'Invoices', href: route('finance.invoices.index') },
    { label: 'Create' },
];
</script>

<template>
  <AppLayout title="Create Invoice">
    <Breadcrumb :items="breadcrumbItems" />

    <div class="mb-8">
      <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
        Create Invoice
      </h1>
      <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
        Generate customer invoice from delivered load
      </p>
    </div>

    <div class="max-w-3xl">
      <form @submit.prevent="submit">
        <!-- Select Project -->
        <div class="card-glow mb-6">
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              1. Select Project
            </h3>
            <div>
              <label class="block text-sm font-bold text-steel-400 mb-2 font-mono">
                Project
              </label>
              <select
                v-model="selectedProject"
                class="input w-full"
                required
              >
                <option
                  :value="null"
                  disabled
                >
                  Choose a project...
                </option>
                <option
                  v-for="project in projects"
                  :key="project.id"
                  :value="project.id"
                >
                  {{ project.job_number }} - {{ project.name }} ({{ project.customer?.name }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Select Load -->
        <div
          v-if="selectedProject && availableLoads"
          class="card-glow mb-6"
        >
          <div class="p-6">
            <h3 class="text-lg font-bold text-white mb-4 uppercase tracking-wide">
              2. Select Delivered Load
            </h3>

            <div v-if="!availableLoads.length">
              <div class="text-center py-8">
                <div class="text-steel-400 mb-2">
                  No delivered loads available for invoicing
                </div>
                <div class="text-sm text-steel-500">
                  All loads for this project have been invoiced or are not yet delivered.
                </div>
              </div>
            </div>

            <div
              v-else
              class="space-y-3"
            >
              <label
                v-for="load in availableLoads"
                :key="load.id"
                class="flex items-start p-4 border border-steel-700 rounded hover:border-weld-600 hover:bg-steel-800/50 cursor-pointer transition-all"
                :class="{ 'border-weld-500 bg-steel-800/50': form.load_id === load.id }"
              >
                <input
                  v-model="form.load_id"
                  type="radio"
                  :value="load.id"
                  class="mt-1 mr-3"
                >
                <div class="flex-1">
                  <div class="flex items-center justify-between mb-2">
                    <div class="text-white font-bold font-mono">
                      Load: {{ load.load_number }}
                    </div>
                    <div class="text-weld-400 font-mono text-sm">
                      {{ formatDate(load.delivered_at) }}
                    </div>
                  </div>
                  <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                      <div class="text-steel-500 text-xs uppercase">
                        Pieces
                      </div>
                      <div class="text-steel-300 font-mono">
                        {{ load.total_pieces }}
                      </div>
                    </div>
                    <div>
                      <div class="text-steel-500 text-xs uppercase">
                        Weight
                      </div>
                      <div class="text-steel-300 font-mono">
                        {{ formatWeight(load.total_weight_lbs) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-steel-500 text-xs uppercase">
                        Destination
                      </div>
                      <div class="text-steel-300">
                        {{ load.destination || 'N/A' }}
                      </div>
                    </div>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <Link
            :href="route('finance.invoices.index')"
            class="btn-secondary"
          >
            Cancel
          </Link>
          <button
            type="submit"
            class="btn-primary"
            :disabled="!form.load_id || form.processing"
          >
            Create Invoice
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
