<!-- resources/js/Layouts/AppLayout.vue -->
<script setup>
import { Link, Head } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: 'chart' },
    { name: 'Reports', href: '/reports', icon: 'chart' },
    { name: 'Projects', href: '/projects', icon: 'folder' },
    { name: 'Drawings', href: '/drawings', icon: 'document' },
    { name: 'Customers', href: '/customers', icon: 'users' },
    { name: 'Inventory', href: '/inventory', icon: 'cube' },
    { name: 'Production', href: '/production', icon: 'cog' },
    { name: 'Shipping', href: '/shipping', icon: 'truck' },
];

const adminNavigation = [
    { name: 'Users', href: '/admin/users', icon: 'users' },
    { name: 'System', href: '/admin/system', icon: 'cog' },
];
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
                class="nav-link"
                :class="{ 'nav-link-active': $page.url === item.href || $page.url.startsWith(item.href + '/') }"
              >
                {{ item.name }}
              </Link>
              
              <!-- Admin Navigation -->
              <template v-if="$page.props.auth.user.role === 'admin'">
                <div class="h-6 w-px bg-steel-700 mx-2" />
                <Link
                  v-for="item in adminNavigation"
                  :key="item.name"
                  :href="item.href"
                  class="px-3 py-2 rounded-md text-sm font-medium transition-colors text-amber-500 hover:text-amber-400 hover:bg-amber-900/20"
                  :class="{ 'bg-amber-900/30 text-amber-300 ring-1 ring-amber-700/50': $page.url.startsWith('/admin') }"
                >
                  Admin: {{ item.name }}
                </Link>
              </template>
            </div>
          </div>

          <!-- Right Side Actions -->
          <div class="flex items-center space-x-4">
            <!-- Search (placeholder) -->
            <button
              class="p-2 text-steel-400 hover:text-white transition-colors rounded-sm hover:bg-steel-800"
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
              class="p-2 text-steel-400 hover:text-white transition-colors rounded-sm hover:bg-steel-800 relative"
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

            <!-- User Menu -->
            <div class="flex items-center space-x-3 pl-4 border-l border-steel-700">
              <div class="text-right">
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
