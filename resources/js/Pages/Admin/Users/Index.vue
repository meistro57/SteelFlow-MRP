<!-- resources/js/Pages/Admin/Users/Index.vue -->
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    users: Array,
});

const showModal = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('admin.users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        useForm({}).delete(route('admin.users.destroy', user.id));
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};
</script>

<template>
  <AppLayout title="User Management">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">
            User Management
          </h1>
          <p class="text-text-secondary mt-1 font-mono text-sm">
            Manage system access and roles
          </p>
        </div>
        <button
          class="btn-primary"
          @click="openCreateModal"
        >
          <svg
            class="w-5 h-5 mr-2 inline-block"
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
          Add User
        </button>
      </div>
    </template>

    <div class="card-industrial">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-steel-700">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-text-tertiary font-mono">
              <th class="px-6 py-4">
                Name
              </th>
              <th class="px-6 py-4">
                Email
              </th>
              <th class="px-6 py-4">
                Role
              </th>
              <th class="px-6 py-4 text-right">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-steel-800">
            <tr
              v-for="user in users"
              :key="user.id"
              class="hover:bg-steel-800/60 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="text-white font-semibold italic">
                  {{ user.name }}
                </div>
                <div class="text-xs text-text-tertiary">
                  #{{ user.id.toString().padStart(3, '0') }}
                </div>
              </td>
              <td class="px-6 py-4 text-text-secondary">
                {{ user.email }}
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2 py-1 rounded text-xs font-bold uppercase tracking-widest"
                  :class="user.role === 'admin' ? 'bg-amber-900/40 text-amber-500 border border-amber-700/50' : 'bg-steel-700/50 text-steel-300 border border-steel-600/50'"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 text-right space-x-3">
                <button
                  class="text-blue-400 hover:text-blue-300 font-bold text-xs uppercase tracking-tighter transition-colors"
                  @click="openEditModal(user)"
                >
                  Edit
                </button>
                <button
                  v-if="$page.props.auth?.user?.id !== user.id"
                  class="text-red-400 hover:text-red-300 font-bold text-xs uppercase tracking-tighter transition-colors"
                  @click="deleteUser(user)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div
          class="absolute inset-0 bg-steel-950/80 transition-opacity"
          aria-hidden="true"
          @click="closeModal"
        />

        <span
          class="hidden sm:inline-block sm:align-middle sm:h-screen"
          aria-hidden="true"
        >&#8203;</span>

        <div class="relative z-10 inline-block align-middle bg-steel-900 border border-steel-700 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submit">
            <div class="bg-steel-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start text-steel-50">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3
                    id="modal-title"
                    class="text-xl font-bold font-mono text-white mb-6 border-b border-steel-700 pb-2"
                  >
                    {{ editingUser ? 'Modify Personnel' : 'Enlist New Personnel' }}
                  </h3>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-xs font-bold text-text-tertiary uppercase mb-1">Full Name</label>
                      <input
                        v-model="form.name"
                        type="text"
                        class="w-full bg-steel-800 border-steel-700 text-white focus:border-forge-500 focus:ring-forge-500 rounded-sm italic"
                        required
                      >
                      <div
                        v-if="form.errors.name"
                        class="text-red-400 text-xs mt-1"
                      >
                        {{ form.errors.name }}
                      </div>
                    </div>

                    <div>
                      <label class="block text-xs font-bold text-text-tertiary uppercase mb-1">Email Address</label>
                      <input
                        v-model="form.email"
                        type="email"
                        class="w-full bg-steel-800 border-steel-700 text-white focus:border-forge-500 focus:ring-forge-500 rounded-sm font-mono"
                        required
                      >
                      <div
                        v-if="form.errors.email"
                        class="text-red-400 text-xs mt-1"
                      >
                        {{ form.errors.email }}
                      </div>
                    </div>

                    <div>
                      <label class="block text-xs font-bold text-text-tertiary uppercase mb-1">Security Role</label>
                      <select
                        v-model="form.role"
                        class="w-full bg-steel-800 border-steel-700 text-white focus:border-forge-500 focus:ring-forge-500 rounded-sm"
                      >
                        <option value="user">
                          Standard User
                        </option>
                        <option value="manager">
                          Shop Manager
                        </option>
                        <option value="supervisor">
                          Supervisor
                        </option>
                        <option value="admin">
                          Administrator
                        </option>
                      </select>
                      <div
                        v-if="form.errors.role"
                        class="text-red-400 text-xs mt-1"
                      >
                        {{ form.errors.role }}
                      </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-bold text-text-tertiary uppercase mb-1">Credential</label>
                        <input
                          v-model="form.password"
                          type="password"
                          class="w-full bg-steel-800 border-steel-700 text-white focus:border-forge-500 focus:ring-forge-500 rounded-sm"
                          :required="!editingUser"
                          placeholder="••••••••"
                        >
                      </div>
                      <div>
                        <label class="block text-xs font-bold text-text-tertiary uppercase mb-1">Confirm</label>
                        <input
                          v-model="form.password_confirmation"
                          type="password"
                          class="w-full bg-steel-800 border-steel-700 text-white focus:border-forge-500 focus:ring-forge-500 rounded-sm"
                          :required="!editingUser"
                          placeholder="••••••••"
                        >
                      </div>
                    </div>
                    <p
                      v-if="editingUser"
                      class="text-[10px] text-text-tertiary uppercase italic"
                    >
                      Leave credentials blank to maintain existing access code.
                    </p>
                    <div
                      v-if="form.errors.password"
                      class="text-red-400 text-xs mt-1"
                    >
                      {{ form.errors.password }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-steel-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-x-reverse space-x-3">
              <button
                type="submit"
                class="btn-primary"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
              >
                {{ editingUser ? 'Update Records' : 'Commit to Staff' }}
              </button>
              <button
                type="button"
                class="btn-secondary"
                @click="closeModal"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
