<!-- resources/js/Pages/Drawings/Index.vue -->
<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    drawings: Object,
    projects: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const projectId = ref(props.filters?.project_id || '');
const hasFile = ref(props.filters?.has_file ?? '');

const applyFilters = () => {
    router.get('/drawings', {
        search: search.value || undefined,
        project_id: projectId.value || undefined,
        has_file: hasFile.value === '' ? undefined : hasFile.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([search, projectId, hasFile], applyFilters);

const clearFilters = () => {
    search.value = '';
    projectId.value = '';
    hasFile.value = '';
    router.get('/drawings', {}, { replace: true });
};

const confirmDelete = (drawing) => {
    if (confirm(`Delete drawing ${drawing.number}? This cannot be undone.`)) {
        router.delete(`/drawings/${drawing.id}`);
    }
};

const formatDate = (date) => (date ? new Date(date).toLocaleDateString() : '—');
</script>

<template>
  <AppLayout title="Drawings">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-steel-100 uppercase tracking-wide text-glow-forge">
          Drawing Manager
        </h1>
        <p class="mt-1 text-sm text-steel-400 uppercase tracking-wider font-mono">
          Catalogue project drawings, revisions, and files.
        </p>
      </div>
      <Link
        href="/drawings/create"
        class="btn-primary"
      >
        New Drawing
      </Link>
    </div>

    <div class="card mb-6">
      <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono">
            Search
          </label>
          <input
            v-model="search"
            type="text"
            placeholder="Number, title, revision"
            class="input w-full"
          >
        </div>
        <div>
          <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono">
            Project
          </label>
          <select
            v-model="projectId"
            class="input w-full"
          >
            <option value="">
              All Projects
            </option>
            <option
              v-for="project in projects"
              :key="project.id"
              :value="project.id"
            >
              {{ project.job_number ? `${project.job_number} — ${project.name}` : project.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs uppercase tracking-wider text-steel-400 mb-2 font-mono">
            File Status
          </label>
          <select
            v-model="hasFile"
            class="input w-full"
          >
            <option value="">
              All Drawings
            </option>
            <option value="true">
              With Files
            </option>
            <option value="false">
              Missing Files
            </option>
          </select>
        </div>
        <div class="md:col-span-4 flex justify-end">
          <button
            type="button"
            class="btn-secondary"
            @click="clearFilters"
          >
            Reset Filters
          </button>
        </div>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-steel-800">
          <thead class="bg-steel-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">
                Drawing
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">
                Project
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">
                Title
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">
                File
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-steel-400 uppercase tracking-wider">
                Revised
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-steel-400 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-steel-900 divide-y divide-steel-800">
            <tr
              v-for="drawing in drawings.data"
              :key="drawing.id"
              class="hover:bg-steel-800/60 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-semibold">
                {{ drawing.number }}
                <span
                  v-if="drawing.revision"
                  class="ml-2 text-xs text-steel-300"
                >
                  Rev {{ drawing.revision }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-200">
                <div class="font-semibold text-white">
                  {{ drawing.project?.name || '—' }}
                </div>
                <div
                  v-if="drawing.project?.job_number"
                  class="text-xs text-steel-400"
                >
                  Job #{{ drawing.project.job_number }}
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-steel-200">
                {{ drawing.title || '—' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="drawing.file_path && drawing.url"
                  class="badge-complete"
                >
                  <a
                    :href="drawing.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hover:text-white"
                  >
                    View File
                  </a>
                </span>
                <span
                  v-else
                  class="badge-warning"
                >
                  Missing
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-steel-200">
                {{ formatDate(drawing.revised_at || drawing.updated_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <Link
                  :href="`/drawings/${drawing.id}/edit`"
                  class="text-weld-300 hover:text-weld-200"
                >
                  Edit
                </Link>
                <button
                  type="button"
                  class="text-forge-300 hover:text-forge-200"
                  @click="confirmDelete(drawing)"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="drawings.data.length === 0">
              <td
                class="px-6 py-4 text-center text-sm text-steel-400"
                colspan="6"
              >
                No drawings found. Try adjusting filters or create a new record.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
