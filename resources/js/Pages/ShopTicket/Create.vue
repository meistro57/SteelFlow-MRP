<!-- resources/js/Pages/ShopTicket/Create.vue -->
<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    projects: Array,
    employees: Array,
    workAreas: Array,
});

const form = useForm({
    project_id: '',
    assembly_instance_id: '',
    part_instance_id: '',
    priority: 2,
    due_date: '',
    notes: '',
    assigned_to_employee_id: '',
    steps: [],
});

const selectedProject = computed(() => {
    return props.projects?.find(p => p.id === parseInt(form.project_id));
});

const availableAssemblies = computed(() => {
    return selectedProject.value?.assemblies ?? [];
});

const addStep = () => {
    form.steps.push({
        work_area_id: '',
        estimated_hours: 0,
    });
};

const removeStep = (index) => {
    form.steps.splice(index, 1);
};

const submit = () => {
    form.post(route('shop-tickets.store'));
};
</script>

<template>
  <AppLayout title="Create Shop Ticket">
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="font-semibold text-2xl text-white leading-tight">
              Create Shop Ticket
            </h2>
            <p class="text-text-secondary font-mono text-sm">
              Create a new digital work order for the shop floor.
            </p>
          </div>
          <Link
            :href="route('shop-tickets.index')"
            class="btn-secondary"
          >
            Cancel
          </Link>
        </div>
      </div>
    </template>

    <form
      class="space-y-6"
      @submit.prevent="submit"
    >
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="xl:col-span-2 space-y-6">
          <!-- Project Selection -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Project Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Project *
                </label>
                <select
                  v-model="form.project_id"
                  class="input-industrial w-full"
                  required
                >
                  <option value="">
                    Select Project
                  </option>
                  <option
                    v-for="project in projects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.job_number }} - {{ project.name }}
                  </option>
                </select>
                <div
                  v-if="form.errors.project_id"
                  class="mt-1 text-sm text-red-400"
                >
                  {{ form.errors.project_id }}
                </div>
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Assembly (Optional)
                </label>
                <select
                  v-model="form.assembly_instance_id"
                  class="input-industrial w-full"
                  :disabled="!form.project_id"
                >
                  <option value="">
                    Select Assembly
                  </option>
                  <option
                    v-for="assembly in availableAssemblies"
                    :key="assembly.id"
                    :value="assembly.id"
                  >
                    {{ assembly.mark }} - {{ assembly.description }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Workflow Steps -->
          <div class="card-industrial">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-lg font-semibold text-white">
                  Workflow Steps
                </h3>
                <p class="text-sm text-text-secondary">
                  Define the sequence of work areas this ticket will pass through.
                </p>
              </div>
              <button
                type="button"
                class="btn-secondary text-sm"
                @click="addStep"
              >
                Add Step
              </button>
            </div>

            <div
              v-if="form.steps.length"
              class="space-y-3"
            >
              <div
                v-for="(step, index) in form.steps"
                :key="index"
                class="flex items-center gap-4 p-4 bg-steel-900/60 border border-steel-700 rounded-sm"
              >
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-steel-700 flex items-center justify-center text-sm font-bold text-text-secondary">
                  {{ index + 1 }}
                </div>

                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs text-text-tertiary mb-1">Work Area</label>
                    <select
                      v-model="step.work_area_id"
                      class="input-industrial w-full"
                      required
                    >
                      <option value="">
                        Select Work Area
                      </option>
                      <option
                        v-for="area in workAreas"
                        :key="area.id"
                        :value="area.id"
                      >
                        {{ area.name }} ({{ area.department?.name ?? 'No Dept' }})
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs text-text-tertiary mb-1">Estimated Hours</label>
                    <input
                      v-model="step.estimated_hours"
                      type="number"
                      step="0.25"
                      min="0"
                      class="input-industrial w-full"
                    >
                  </div>
                </div>

                <button
                  type="button"
                  class="text-red-400 hover:text-red-300"
                  @click="removeStep(index)"
                >
                  Remove
                </button>
              </div>
            </div>

            <div
              v-else
              class="text-center text-sm text-text-secondary py-8 border border-dashed border-steel-700 rounded-sm"
            >
              No workflow steps added. Click "Add Step" to define the production workflow.
            </div>
          </div>

          <!-- Notes -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Notes
            </h3>
            <textarea
              v-model="form.notes"
              rows="4"
              class="input-industrial w-full"
              placeholder="Additional instructions or notes for this ticket..."
            />
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Priority & Assignment -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Priority & Assignment
            </h3>

            <div class="space-y-4">
              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Priority *
                </label>
                <select
                  v-model="form.priority"
                  class="input-industrial w-full"
                  required
                >
                  <option :value="1">
                    Low
                  </option>
                  <option :value="2">
                    Normal
                  </option>
                  <option :value="3">
                    Medium
                  </option>
                  <option :value="4">
                    High
                  </option>
                  <option :value="5">
                    Critical
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Due Date
                </label>
                <input
                  v-model="form.due_date"
                  type="date"
                  class="input-industrial w-full"
                >
              </div>

              <div>
                <label class="block text-xs uppercase tracking-wider text-text-tertiary font-semibold mb-2">
                  Assign To
                </label>
                <select
                  v-model="form.assigned_to_employee_id"
                  class="input-industrial w-full"
                >
                  <option value="">
                    Unassigned
                  </option>
                  <option
                    v-for="employee in employees"
                    :key="employee.id"
                    :value="employee.id"
                  >
                    {{ employee.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Summary Preview -->
          <div class="card-industrial">
            <h3 class="text-lg font-semibold text-white mb-4">
              Summary
            </h3>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-text-tertiary">
                  Project
                </dt>
                <dd class="text-white">
                  {{ selectedProject?.job_number ?? 'Not selected' }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-text-tertiary">
                  Workflow Steps
                </dt>
                <dd class="text-white">
                  {{ form.steps.length }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-text-tertiary">
                  Total Est. Hours
                </dt>
                <dd class="text-white">
                  {{ form.steps.reduce((sum, s) => sum + (parseFloat(s.estimated_hours) || 0), 0).toFixed(2) }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Submit -->
          <div class="card-industrial">
            <button
              type="submit"
              class="btn-primary w-full"
              :disabled="form.processing"
            >
              <span v-if="form.processing">Creating...</span>
              <span v-else>Create Shop Ticket</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>
