<!-- resources/js/Layouts/AppLayout.vue -->
<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const settings = computed(() => user.value?.settings || {});
const sidebarCollapsed = ref(settings.value.sidebar_collapsed || false);
const mobileMenuOpen = ref(false);

import {
    ChartBarIcon,
    RectangleGroupIcon,
    DocumentTextIcon,
    UsersIcon,
    CubeIcon,
    ShoppingCartIcon,
    Squares2X2Icon,
    CogIcon,
    TruckIcon,
    HomeIcon,
} from '@heroicons/vue/24/outline';

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
    { name: 'Reports', href: '/reports', icon: ChartBarIcon },
    { name: 'Projects', href: '/projects', icon: RectangleGroupIcon },
    { name: 'Drawings', href: '/drawings', icon: DocumentTextIcon },
    { name: 'Customers', href: '/customers', icon: UsersIcon },
    { name: 'Inventory', href: '/inventory', icon: CubeIcon },
    { name: 'Procurement', href: '/purchase-orders', icon: ShoppingCartIcon },
    { name: 'Nesting', href: '/nesting', icon: Squares2X2Icon },
    { name: 'Production', href: '/production', icon: CogIcon },
    { name: 'Shipping', href: '/shipping', icon: TruckIcon },
];

const adminNavigation = [
    { name: 'Users', href: '/admin/users', icon: UsersIcon },
    { name: 'System', href: '/admin/system', icon: CogIcon },
];

// Listen for settings updates
onMounted(() => {
    window.addEventListener('settings-updated', (event) => {
        sidebarCollapsed.value = event.detail.sidebar_collapsed;
    });

    // Apply layout density on mount
    const density = settings.value.layout_density || 'comfortable';
    document.documentElement.setAttribute('data-density', density);
});
</script>

<template>
  <div class="min-h-screen bg-steel-900 text-steel-50">
    <Head :title="title" />

    <!-- Industrial Navigation Bar -->
    <nav class="nav-industrial sticky top-0 z-50 border-b border-steel-700 bg-steel-900/80 backdrop-blur-md">
      <div class="max-w-full mx-auto px-6">
        <div class="flex justify-between items-center h-16">
          <!-- Logo & Branding -->
          <div class="flex items-center space-x-8 text-neutral-50">
            <Link
              href="/"
              class="flex items-center space-x-3 group"
            >
              <div class="w-10 h-10 bg-forge-500 rounded flex items-center justify-center shadow-glow-forge">
                <svg
                  class="w-6 h-6 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.5"
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
              </div>
              <div class="flex flex-col">
                <span class="text-white font-bold text-xl tracking-tight">SteelFlow</span>
                <span class="text-xs text-steel-500 uppercase tracking-widest font-mono">MRP v0.1.0-alpha</span>
              </div>
            </Link>

            <!-- Primary Navigation -->
            <div class="hidden md:flex items-center space-x-1">
              <Link
                v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                class="nav-link group"
                :class="{ 'nav-link-active': $page.url === item.href || $page.url.startsWith(item.href + '/') }"
              >
                <component
                  :is="item.icon"
                  class="w-4 h-4 inline-block mr-1.5 transition-transform group-hover:scale-110"
                />
                {{ item.name }}
              </Link>

              <!-- Admin Navigation -->
              <template v-if="$page.props.auth.user.role === 'admin'">
                <div class="h-6 w-px bg-steel-700 mx-2" />
                <Link
                  v-for="item in adminNavigation"
                  :key="item.name"
                  :href="item.href"
                  class="px-3 py-2 rounded-md text-sm font-medium transition-all text-amber-500 hover:text-amber-400 hover:bg-amber-900/20 flex items-center gap-2 group"
                  :class="{ 'bg-amber-900/30 text-amber-300 ring-1 ring-amber-700/50': $page.url.startsWith('/admin') }"
                >
                  <component
                    :is="item.icon"
                    class="w-4 h-4 transition-transform group-hover:scale-110"
                  />
                  Admin: {{ item.name }}
                </Link>
              </template>
            </div>
          </div>

          <!-- Right Side Actions -->
          <div class="flex items-center space-x-2 md:space-x-4">
            <!-- Search (placeholder) -->
            <button
              class="hidden sm:block p-2 text-steel-400 hover:text-white transition-colors rounded-sm hover:bg-steel-800"
              title="Search"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </button>

            <!-- Notifications -->
            <button
              class="hidden sm:block p-2 text-steel-400 hover:text-white transition-colors rounded-sm hover:bg-steel-800 relative"
              title="Notifications"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
              </svg>
              <span class="absolute top-1 right-1 w-2 h-2 bg-forge-500 rounded-full" />
            </button>

            <!-- Settings -->
            <Link
              :href="route('settings.index')"
              class="hidden sm:block p-2 text-steel-400 hover:text-white transition-colors rounded-sm hover:bg-steel-800"
              :class="{ 'text-forge-500 bg-steel-800': $page.url === '/settings' }"
              title="Settings"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
            </Link>

            <!-- User Menu -->
            <div class="flex items-center space-x-2 sm:space-x-3 sm:pl-4 sm:border-l sm:border-steel-700">
              <div class="hidden sm:block text-right">
                <div class="text-sm font-medium text-white">
                  {{ $page.props.auth.user.name }}
                </div>
                <div class="text-xs text-steel-500 font-mono">
                  Role: {{ $page.props.auth.user.role }}
                </div>
              </div>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-9 h-9 bg-steel-700 rounded-sm flex items-center justify-center text-white font-bold hover:bg-steel-600 transition-colors"
                title="Log Out"
              >
                {{ $page.props.auth.user.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
              </Link>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
              <button
                class="inline-flex items-center justify-center p-2 rounded-md text-steel-400 hover:text-white hover:bg-steel-800 focus:outline-none"
                @click="mobileMenuOpen = !mobileMenuOpen"
              >
                <span class="sr-only">Open main menu</span>
                <svg
                  v-if="!mobileMenuOpen"
                  class="block h-6 w-6"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                </svg>
                <svg
                  v-else
                  class="block h-6 w-6"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile menu, show/hide based on menu state. -->
      <div
        v-if="mobileMenuOpen"
        class="md:hidden bg-steel-900 border-b border-steel-700"
      >
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium text-steel-300 hover:text-white hover:bg-steel-800 transition-all"
            :class="{ 'text-forge-500 bg-steel-800': $page.url === item.href }"
            @click="mobileMenuOpen = false"
          >
            <component
              :is="item.icon"
              class="w-5 h-5"
            />
            {{ item.name }}
          </Link>

          <template v-if="$page.props.auth.user.role === 'admin'">
            <div class="border-t border-steel-800 my-2 pt-2 px-3 text-xs font-bold text-steel-500 uppercase">
              Admin
            </div>
            <Link
              v-for="item in adminNavigation"
              :key="item.name"
              :href="item.href"
              class="flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium text-amber-500 hover:text-amber-400 hover:bg-amber-900/20 transition-all"
              @click="mobileMenuOpen = false"
            >
              <component
                :is="item.icon"
                class="w-5 h-5"
              />
              {{ item.name }}
            </Link>
          </template>

          <div class="border-t border-steel-800 my-2 pt-2">
            <Link
              :href="route('settings.index')"
              class="flex items-center gap-3 px-3 py-2 rounded-md text-base font-medium text-steel-300 hover:text-white hover:bg-steel-800 transition-all"
              @click="mobileMenuOpen = false"
            >
              <CogIcon class="w-5 h-5" />
              Settings
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Header (if provided) -->
    <header
      v-if="$slots.header"
      class="bg-steel-800 border-b border-steel-700"
    >
      <div class="max-w-full mx-auto px-6 py-6">
        <slot name="header" />
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-full mx-auto px-6 py-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-steel-900 border-t border-steel-700 mt-16">
      <div class="max-w-full mx-auto px-6 py-6">
        <div class="flex justify-between items-center text-sm text-steel-500">
          <div class="font-mono">
            SteelFlow MRP © 2026 • Built for Steel Fabricators
          </div>
          <div class="flex items-center space-x-6">
            <span class="font-mono">v0.1.0-alpha</span>
            <span>•</span>
            <a
              href="#"
              class="hover:text-weld-400 transition-colors"
            >Documentation</a>
            <span>•</span>
            <a
              href="#"
              class="hover:text-weld-400 transition-colors"
            >Support</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Additional component-specific styles if needed */
</style>
