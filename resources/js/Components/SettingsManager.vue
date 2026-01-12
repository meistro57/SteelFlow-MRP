<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    SunIcon,
    MoonIcon,
    ComputerDesktopIcon,
    Squares2X2Icon,
    ViewColumnsIcon,
    RectangleStackIcon,
    Bars3Icon,
    CheckCircleIcon,
    InformationCircleIcon,
    CogIcon,
    SwatchIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const settings = computed(() => user.value?.settings || {});

// Initialize form with current settings
const form = useForm({
    theme: settings.value.theme || 'light',
    layout_density: settings.value.layout_density || 'comfortable',
    sidebar_collapsed: settings.value.sidebar_collapsed || false,
    accent_color: settings.value.accent_color || 'forge',
});

// Theme options
const themeOptions = [
    { value: 'light', label: 'Light Mode', icon: SunIcon, description: 'Bright interface for well-lit environments' },
    { value: 'dark', label: 'Dark Mode', icon: MoonIcon, description: 'Reduced eye strain in low light' },
    { value: 'system', label: 'System', icon: ComputerDesktopIcon, description: 'Follow system preferences' },
];

// Accent color options
const accentOptions = [
    { value: 'forge', label: 'Forge Orange', color: 'bg-forge-500', hex: '#ff5722', description: 'High-visibility industrial orange' },
    { value: 'weld', label: 'Arc Blue', color: 'bg-weld-500', hex: '#0096c7', description: 'Electric arc welding blue' },
    { value: 'plasma', label: 'Plasma Purple', color: 'bg-plasma-500', hex: '#7e22ce', description: 'Precision plasma cutting purple' },
    { value: 'safety', label: 'Safety Yellow', color: 'bg-safety-500', hex: '#d97706', description: 'Caution-ready safety yellow' },
];

// Layout density options
const densityOptions = [
    { value: 'compact', label: 'Compact', icon: Squares2X2Icon, description: 'Maximum information density for estimators and power users' },
    { value: 'comfortable', label: 'Comfortable', icon: ViewColumnsIcon, description: 'Balanced layout for general use' },
    { value: 'spacious', label: 'Spacious', icon: RectangleStackIcon, description: 'Large touch targets for shop floor tablets' },
];

// Current selections
const currentTheme = computed(() => form.theme);
const currentDensity = computed(() => form.layout_density);
const sidebarCollapsed = computed(() => form.sidebar_collapsed);
const currentAccent = computed(() => form.accent_color);

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

            // Apply accent color
            document.documentElement.setAttribute('data-accent', form.accent_color);

            // Apply sidebar state
            if (form.sidebar_collapsed) {
                document.documentElement.setAttribute('data-sidebar', 'collapsed');
            } else {
                document.documentElement.removeAttribute('data-sidebar');
            }

            // Emit event for settings changes
            if (window) {
                window.dispatchEvent(new CustomEvent('settings-updated', {
                    detail: {
                        sidebar_collapsed: form.sidebar_collapsed,
                        layout_density: form.layout_density,
                        theme: form.theme,
                        accent_color: form.accent_color,
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
      <div class="flex items-center gap-3 mb-2">
        <div class="w-12 h-12 rounded-full bg-forge-500/10 border border-forge-500 flex items-center justify-center">
          <CogIcon class="w-6 h-6 text-forge-400" />
        </div>
        <h2 class="text-2xl font-bold text-steel-200">
          Interface Settings
        </h2>
      </div>
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
          class="p-4 rounded-industrial border-2 transition-all duration-200 text-left group"
          :class="currentTheme === option.value
            ? 'border-forge-500 bg-forge-500/10 shadow-glow-forge'
            : 'border-steel-700 bg-steel-800 hover:border-steel-600 hover:bg-steel-750'"
          @click="updateSetting('theme', option.value)"
        >
          <div class="flex items-start gap-3">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
              :class="currentTheme === option.value ? 'bg-forge-500/20 text-forge-400' : 'bg-steel-700 text-steel-400 group-hover:bg-steel-600'"
            >
              <component
                :is="option.icon"
                class="w-6 h-6"
              />
            </div>
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
              class="w-5 h-5 rounded-full bg-forge-500 flex items-center justify-center flex-shrink-0"
            >
              <CheckCircleIcon class="w-3 h-3 text-white" />
            </div>
          </div>
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Accent Color -->
    <div class="space-y-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <SwatchIcon class="w-5 h-5 text-steel-400" />
          <h3 class="text-lg font-semibold text-steel-300">
            Accent Color
          </h3>
        </div>
        <p class="text-sm text-steel-500 mb-4">
          Choose the primary highlighting color for the interface
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <button
          v-for="option in accentOptions"
          :key="option.value"
          type="button"
          class="p-4 rounded-industrial border-2 transition-all duration-200 text-left group relative overflow-hidden"
          :class="currentAccent === option.value
            ? `border-steel-400 bg-steel-750 shadow-industrial`
            : 'border-steel-700 bg-steel-800 hover:border-steel-600 hover:bg-steel-750'"
          @click="updateSetting('accent_color', option.value)"
        >
          <div class="relative z-10">
            <div class="flex items-center gap-3 mb-3">
              <div
                class="w-10 h-10 rounded-full border-2 border-white/20 shadow-lg"
                :class="option.color"
              />
              <div class="flex-1">
                <div class="font-semibold text-steel-200 text-sm">
                  {{ option.label }}
                </div>
                <div class="text-xs text-steel-500 font-mono">
                  {{ option.hex }}
                </div>
              </div>
            </div>
            <div class="text-xs text-steel-500">
              {{ option.description }}
            </div>
          </div>
          <div
            v-if="currentAccent === option.value"
            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-steel-200 flex items-center justify-center"
          >
            <CheckCircleIcon class="w-4 h-4 text-steel-900" />
          </div>
          <!-- Preview gradient -->
          <div
            class="absolute inset-0 opacity-5 group-hover:opacity-10 transition-opacity"
            :class="option.color"
          />
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Layout Density -->
    <div class="space-y-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <ViewColumnsIcon class="w-5 h-5 text-steel-400" />
          <h3 class="text-lg font-semibold text-steel-300">
            Layout Density
          </h3>
        </div>
        <p class="text-sm text-steel-500 mb-4">
          Adjust information density and spacing
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          v-for="option in densityOptions"
          :key="option.value"
          type="button"
          class="p-4 rounded-industrial border-2 transition-all duration-200 text-left group"
          :class="currentDensity === option.value
            ? 'border-weld-500 bg-weld-500/10 shadow-glow-weld'
            : 'border-steel-700 bg-steel-800 hover:border-steel-600 hover:bg-steel-750'"
          @click="updateSetting('layout_density', option.value)"
        >
          <div class="flex items-start gap-3">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
              :class="currentDensity === option.value ? 'bg-weld-500/20 text-weld-400' : 'bg-steel-700 text-steel-400 group-hover:bg-steel-600'"
            >
              <component
                :is="option.icon"
                class="w-6 h-6"
              />
            </div>
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
              class="w-5 h-5 rounded-full bg-weld-500 flex items-center justify-center flex-shrink-0"
            >
              <CheckCircleIcon class="w-3 h-3 text-white" />
            </div>
          </div>
        </button>
      </div>
    </div>

    <div class="divider" />

    <!-- Sidebar Toggle -->
    <div class="space-y-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <Bars3Icon class="w-5 h-5 text-steel-400" />
          <h3 class="text-lg font-semibold text-steel-300">
            Sidebar
          </h3>
        </div>
        <p class="text-sm text-steel-500 mb-4">
          Show or hide the navigation sidebar to maximize workspace
        </p>
      </div>
      <div class="flex items-center justify-between p-4 bg-steel-800 rounded-industrial border border-steel-700 hover:border-steel-600 transition-colors">
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center"
            :class="sidebarCollapsed ? 'bg-weld-500/20 text-weld-400' : 'bg-steel-700 text-steel-400'"
          >
            <Bars3Icon class="w-6 h-6" />
          </div>
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
            class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform duration-200 shadow-lg"
            :class="sidebarCollapsed ? 'translate-x-8' : 'translate-x-1'"
          />
        </button>
      </div>
    </div>

    <!-- Info Box -->
    <div class="alert-info">
      <InformationCircleIcon class="w-5 h-5 flex-shrink-0" />
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
