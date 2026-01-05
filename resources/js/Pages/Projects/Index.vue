<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    projects: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

watch(search, (value) => {
    router.get('/projects', { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const formatWeight = (lbs) => {
    if (!lbs) return '-';
    return new Intl.NumberFormat().format(Math.round(lbs)) + ' lbs';
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
  <div class="min-h-screen bg-steel-950">
    <!-- Page Header -->
    <div class="border-b border-steel-800 bg-gradient-steel">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
              Projects
            </h1>
            <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
              Active Fabrication Jobs
            </p>
          </div>
          <div class="mt-4 md:mt-0">
            <Link
              href="/projects/create"
              class="btn-primary"
            >
              <svg
                class="h-5 w-5 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              New Project
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Search Filter -->
      <div class="card mb-6">
        <div class="p-4">
          <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono">
            Search Projects
          </label>
          <input
            v-model="search"
            type="text"
            placeholder="Job #, Name, Customer..."
            class="input w-full md:w-1/2"
          >
        </div>
      </div>

      <!-- Projects Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
        <div
          v-for="project in projects.data.slice(0, 6)"
          :key="project.id"
          class="card-glow group"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="text-xs uppercase tracking-wider text-steel-500 font-mono mb-1">
                  Job Number
                </div>
                <div class="text-2xl font-bold font-mono text-forge-400 glow-forge">
                  {{ project.job_number }}
                </div>
              </div>
              <div>
                <span
                  v-if="project.status === 'active'"
                  class="badge-in-progress"
                >
                  Active
                </span>
                <span
                  v-else-if="project.status === 'complete'"
                  class="badge-complete"
                >
                  Complete
                </span>
                <span
                  v-else
                  class="badge-free"
                >
                  {{ project.status }}
                </span>
              </div>
            </div>

            <Link
              :href="'/projects/' + project.id"
              class="block mb-4 group-hover:text-forge-400 transition-colors"
            >
              <h3 class="text-lg font-bold text-steel-100 uppercase tracking-wide line-clamp-2">
                {{ project.name }}
              </h3>
            </Link>

            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-steel-500 uppercase tracking-wider font-mono text-xs">Customer</span>
                <span class="text-steel-300 font-mono">{{ project.customer?.name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-steel-500 uppercase tracking-wider font-mono text-xs">Weight</span>
                <span class="text-weld-400 font-mono font-bold">{{ formatWeight(project.contract_weight_lbs) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-steel-500 uppercase tracking-wider font-mono text-xs">Ship Date</span>
                <span class="text-steel-300 font-mono">{{ formatDate(project.ship_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-steel-500 uppercase tracking-wider font-mono text-xs">Parts</span>
                <span class="text-forge-400 font-mono font-bold">{{ project.parts_count || 0 }}</span>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-steel-800">
              <Link
                :href="'/projects/' + project.id"
                class="text-weld-400 hover:text-weld-300 text-sm uppercase tracking-wider font-mono glow-weld"
              >
                View Details →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Full Projects Table -->
      <div class="card-elevated">
        <div class="p-4 border-b border-steel-800">
          <h2 class="text-lg font-bold text-steel-200 uppercase tracking-wider">
            All Projects
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="table-industrial">
            <thead>
              <tr>
                <th>Job #</th>
                <th>Project Name</th>
                <th>Customer</th>
                <th>Status</th>
                <th class="text-right">
                  Weight
                </th>
                <th>Ship Date</th>
                <th class="text-right">
                  Parts
                </th>
                <th class="text-right">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="project in projects.data"
                :key="project.id"
              >
                <td class="cell-id">
                  {{ project.job_number }}
                </td>
                <td>
                  <Link
                    :href="'/projects/' + project.id"
                    class="text-forge-400 hover:text-forge-300 font-bold glow-forge"
                  >
                    {{ project.name }}
                  </Link>
                </td>
                <td class="text-steel-400 font-mono text-sm">
                  {{ project.customer?.name || '-' }}
                </td>
                <td class="cell-status">
                  <span
                    v-if="project.status === 'active'"
                    class="badge-in-progress"
                  >
                    Active
                  </span>
                  <span
                    v-else-if="project.status === 'complete'"
                    class="badge-complete"
                  >
                    Complete
                  </span>
                  <span
                    v-else-if="project.status === 'production'"
                    class="badge-warning"
                  >
                    Production
                  </span>
                  <span
                    v-else
                    class="badge-free"
                  >
                    {{ project.status }}
                  </span>
                </td>
                <td class="cell-measurement text-right">
                  {{ formatWeight(project.contract_weight_lbs) }}
                </td>
                <td class="font-mono text-sm text-steel-400">
                  {{ formatDate(project.ship_date) }}
                </td>
                <td class="cell-measurement text-right">
                  {{ project.parts_count || 0 }}
                </td>
                <td class="text-right">
                  <Link
                    :href="'/projects/' + project.id + '/edit'"
                    class="text-weld-400 hover:text-weld-300 text-sm uppercase tracking-wider font-mono glow-weld mr-3"
                  >
                    Edit
                  </Link>
                </td>
              </tr>
              <tr v-if="projects.data.length === 0">
                <td
                  colspan="8"
                  class="text-center py-12 text-steel-500"
                >
                  <div class="flex flex-col items-center">
                    <svg
                      class="h-16 w-16 mb-4 text-steel-700"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                      />
                    </svg>
                    <p class="text-lg uppercase tracking-wider font-mono">
                      No Projects Found
                    </p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="projects.last_page > 1"
          class="border-t border-steel-800 px-6 py-4"
        >
          <div class="flex items-center justify-between">
            <div class="text-sm text-steel-400 font-mono">
              Showing
              <span class="text-forge-400 font-bold">{{ projects.from }}</span>
              to
              <span class="text-forge-400 font-bold">{{ projects.to }}</span>
              of
              <span class="text-forge-400 font-bold">{{ projects.total }}</span>
              projects
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
