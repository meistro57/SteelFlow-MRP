<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const settings = computed(() => user.value?.settings || {});

// Initialize form with current settings
const form = useForm({
    theme: settings.value.theme || 'light',
    layout_density: settings.value.layout_density || 'comfortable',
    sidebar_collapsed: settings.value.sidebar_collapsed || false,
});

// Theme options
const themeOptions = [
    { value: 'light', label: 'Light Mode', icon: '☀️', description: 'Bright interface for well-lit environments' },
    { value: 'dark', label: 'Dark Mode', icon: '🌙', description: 'Reduced eye strain in low light' },
    { value: 'system', label: 'System', icon: '💻', description: 'Follow system preferences' },
];

// Layout density options
const densityOptions = [
    { value: 'compact', label: 'Compact', icon: '⊞', description: 'Maximum information density for estimators and power users' },
    { value: 'comfortable', label: 'Comfortable', icon: '▢', description: 'Balanced layout for general use' },
    { value: 'spacious', label: 'Spacious', icon: '□', description: 'Large touch targets for shop floor tablets' },
];

// Current selections
const currentTheme = computed(() => form.theme);
const currentDensity = computed(() => form.layout_density);
const sidebarCollapsed = computed(() => form.sidebar_collapsed);

// Update settings
const updateSetting = (key, value) => {
    form[key] = value;
    submitForm();
};

const submitForm = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Apply theme immediately
            if (form.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (form.theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (form.theme === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            // Apply layout density
            document.documentElement.setAttribute('data-density', form.layout_density);

            // Apply sidebar state
            if (form.sidebar_collapsed) {
                document.documentElement.setAttribute('data-sidebar', 'collapsed');
            } else {
                document.documentElement.removeAttribute('data-sidebar');
            }

            // Emit event for sidebar toggle
            if (window) {
                window.dispatchEvent(new CustomEvent('settings-updated', {
                    detail: {
                        sidebar_collapsed: form.sidebar_collapsed,
                        layout_density: form.layout_density,
                        theme: form.theme,
                    }
                }));
            }
        },
    });
};

// Toggle sidebar
const toggleSidebar = () => {
    updateSetting('sidebar_collapsed', !form.sidebar_collapsed);
};
</script>

<template>
  <div class="card-elevated space-y-8">
    <!-- Header -->
    <div class="border-b border-steel-700 pb-4">
      <h2 class="text-2xl font-bold text-steel-200">
        ⚙️ Interface Settings
      </h2>
      <p class="text-sm text-steel-400 mt-2">
        Customize your SteelFlow MRP experience to match your workflow and environment
      </p>
    </div>

    <!-- Theme Selection -->
    <div class="space-y-4">
      <div>
        <h3 class="text-lg font-semibold text-steel-300 mb-1">
          Theme
        </h3>
        <p class="text-sm text-steel-500 mb-4">
          Choose your preferred color scheme
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="option in themeOptions"
          :key="option.value"
          type="button"
          class="p-4 rounded-industrial border-2 transition-all duration-200 text-left"
          :class="currentTheme === option.value
            ? 'border-forge-500 bg-forge-500/10 shadow-glow-forge'
            : 'border-steel-700 bg-steel-800 hover:border-steel-600 hover:bg-steel-750'"
          @click="updateSetting('theme', option.value)"
        >
          <div class="flex items-start gap-3">
            <span class="text-3xl">{{ option.icon }}</span>
            <div class="flex-1">
              <div class="font-semibold text-steel-200 mb-1">
                {{ option.label }}
              </div>
              <div class="text-xs text-steel-500">
                {{ option.description }}
              </div>
            </div>
            <div
              v-if="currentTheme === option.value"
              class="w-5 h-5 rounded-full bg-forge-500 flex items-center justify-center"
            >
              <svg
                class="w-3 h-3 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="3"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
          </div>
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Layout Density -->
    <div class="space-y-4">
      <div>
        <h3 class="text-lg font-semibold text-steel-300 mb-1">
          Layout Density
        </h3>
        <p class="text-sm text-steel-500 mb-4">
          Adjust information density and spacing
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="option in densityOptions"
          :key="option.value"
          type="button"
          class="p-4 rounded-industrial border-2 transition-all duration-200 text-left"
          :class="currentDensity === option.value
            ? 'border-weld-500 bg-weld-500/10 shadow-glow-weld'
            : 'border-steel-700 bg-steel-800 hover:border-steel-600 hover:bg-steel-750'"
          @click="updateSetting('layout_density', option.value)"
        >
          <div class="flex items-start gap-3">
            <span class="text-3xl">{{ option.icon }}</span>
            <div class="flex-1">
              <div class="font-semibold text-steel-200 mb-1">
                {{ option.label }}
              </div>
              <div class="text-xs text-steel-500">
                {{ option.description }}
              </div>
            </div>
            <div
              v-if="currentDensity === option.value"
              class="w-5 h-5 rounded-full bg-weld-500 flex items-center justify-center"
            >
              <svg
                class="w-3 h-3 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="3"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
          </div>
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Sidebar Toggle -->
    <div class="space-y-4">
      <div>
        <h3 class="text-lg font-semibold text-steel-300 mb-1">
          Sidebar
        </h3>
        <p class="text-sm text-steel-500 mb-4">
          Show or hide the navigation sidebar to maximize workspace
        </p>
      </div>
      <div class="flex items-center justify-between p-4 bg-steel-800 rounded-industrial border border-steel-700">
        <div class="flex items-center gap-3">
          <span class="text-2xl">📋</span>
          <div>
            <div class="font-semibold text-steel-200">
              Navigation Sidebar
            </div>
            <div class="text-xs text-steel-500">
              {{ sidebarCollapsed ? 'Currently hidden' : 'Currently visible' }}
            </div>
          </div>
        </div>
        <button
          type="button"
          class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-weld-500 focus:ring-offset-2 focus:ring-offset-steel-900"
          :class="sidebarCollapsed ? 'bg-weld-600' : 'bg-steel-600'"
          @click="toggleSidebar"
        >
          <span
            class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform duration-200"
            :class="sidebarCollapsed ? 'translate-x-8' : 'translate-x-1'"
          />
        </button>
      </div>
    </div>

    <!-- Info Box -->
    <div class="alert-info">
      <svg
        class="w-5 h-5 flex-shrink-0"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
      <div>
        <div class="font-semibold mb-1">
          Saved Automatically
        </div>
        <div class="text-sm">
          Your preferences are saved to your account and will follow you across all devices and sessions.
        </div>
      </div>
    </div>
  </div>
</template>
