<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <div :class="[
      'fixed inset-y-0 left-0 z-30 w-64 bg-gray-800 transition-transform duration-300 ease-in-out transform',
      isCollapsed ? '-translate-x-full' : 'translate-x-0'
    ]">
      <div class="flex items-center justify-between h-16 px-4 bg-gray-900">
        <div class="text-xl font-semibold text-white">Dashboard</div>
        <button @click="toggleSidebar" class="text-gray-300 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <nav class="mt-5 px-2">
        <a v-for="(item, index) in menuItems" 
           :key="index"
           :href="item.route"
           :class="[
             'group flex items-center px-2 py-2 text-base font-medium rounded-md transition-colors duration-150',
             isCurrentRoute(item.route) 
               ? 'bg-gray-900 text-white' 
               : 'text-gray-300 hover:bg-gray-700 hover:text-white'
           ]">
          <component :is="item.icon" class="mr-4 h-6 w-6" />
          {{ item.name }}
        </a>
      </nav>
    </div>

    <!-- Mobile sidebar overlay -->
    <div v-if="!isCollapsed" 
         class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"
         @click="toggleSidebar"></div>

    <!-- Toggle button for mobile -->
    <div class="fixed top-4 left-4 z-40 lg:hidden">
      <button @click="toggleSidebar" 
              class="p-2 rounded-md bg-gray-800 text-gray-300 hover:text-white focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="isCollapsed" 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M4 6h16M4 12h16M4 18h16" />
          <path v-else 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Main content -->
    <div :class="[
      'flex-1 transition-margin duration-300 ease-in-out',
      isCollapsed ? 'ml-0' : 'ml-64'
    ]">
      <main class="py-6 px-4 sm:px-6 lg:px-8">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const isCollapsed = ref(false)
const currentRoute = ref(window.location.pathname)

const menuItems = computed(() => {
  const items = [
    {
      name: 'Dashboard',
      route: '/dashboard',
      icon: 'HomeIcon',
      permissions: ['view_dashboard']
    }
  ]

  // Only add these items if user has required permissions
  if (window.Laravel?.user?.permissions?.includes('manage_users')) {
    items.push({
      name: 'Users Management',
      route: '/users',
      icon: 'UsersIcon',
      permissions: ['manage_users']
    })
  }

  if (window.Laravel?.user?.permissions?.includes('view_orders')) {
    items.push({
      name: 'Orders',
      route: '/orders',
      icon: 'ShoppingCartIcon',
      permissions: ['view_orders']
    })
  }

  if (window.Laravel?.user?.permissions?.includes('view_products')) {
    items.push({
      name: 'Products',
      route: '/products',
      icon: 'CubeIcon',
      permissions: ['view_products']
    })
  }

  return items
})

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value
}

const isCurrentRoute = (route) => {
  return currentRoute.value.startsWith(route)
}
</script>

<script>
// Define icons as components
const HomeIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  `
}

const UsersIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
  `
}

const ShoppingCartIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  `
}

const CubeIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
    </svg>
  `
}

export default {
  components: {
    HomeIcon,
    UsersIcon,
    ShoppingCartIcon,
    CubeIcon
  }
}
</script> 