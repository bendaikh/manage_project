<template>
  <div class="min-h-screen bg-white">
    <!-- Top Navigation -->
    <div class="fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50" style="height: 60px;">
      <div class="flex items-center h-full">
        <!-- Left section - Logo (positioned above sidebar area) -->
        <div class="navbar-logo sidebar-logo" style="width: 256px; height: 60px; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; background-color: white; border-right: 1px solid #e5e7eb;">
          <div v-if="appLogo" class="w-full" style="width: 100%; padding: 0 8px; margin: 0;">
            <img :src="appLogo" alt="Application Logo" class="w-full h-auto block" style="width: 100%; height: auto; object-fit: contain; display: block;">
          </div>
          <div v-else class="w-full text-center text-gray-800 text-xl font-bold" style="width: 100%; padding: 0 8px; margin: 0;">
            {{ appName }}
          </div>
        </div>
        
        <!-- Right section - Profile (in remaining navbar space) -->
        <div class="flex-1 flex items-center justify-end pr-4">
          <!-- Profile Icon -->
          <div class="relative">
            <button @click="toggleProfileMenu" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50">
              <div class="h-8 w-8 bg-gray-300 rounded-full flex items-center justify-center">
                <UserIcon class="h-5 w-5 text-gray-600" />
              </div>
            </button>
            
            <!-- Profile Dropdown -->
            <div v-show="isProfileMenuOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
              <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
              <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
              <hr class="my-2">
              <button @click="signOut" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Sign out</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-blue-900" style="padding-top: 60px;">
      <!-- Navigation -->
      <nav class="px-3 py-4">
        <div class="space-y-1">
          <!-- Dashboard -->
          <div>
            <button @click="toggleDashboardMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <HomeIcon class="h-5 w-5 text-white" />
                <span>Dashboard</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isDashboardMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Dashboard Sub-menu -->
            <div v-show="isDashboardMenuOpen" class="mt-1 ml-4 space-y-1">
              <button type="button" @click="handleShowOverview" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ChartBarIcon class="h-4 w-4" />
                <span>Overview</span>
              </button>
              <button type="button" @click="handleShowAnalytics" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ChartPieIcon class="h-4 w-4" />
                <span>Analytics</span>
              </button>
            </div>
          </div>

          <!-- Orders -->
          <div>
            <button @click="toggleOrdersMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <ShoppingCartIcon class="h-5 w-5 text-white" />
                <span>Orders</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isOrdersMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Orders Sub-menu -->
            <div v-show="isOrdersMenuOpen" class="mt-1 ml-4 space-y-1">
              <button type="button" @click="handleShowAddOrder" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <PlusIcon class="h-4 w-4" />
                <span>Create Order</span>
              </button>
              <button type="button" @click="handleShowOrderList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ListIcon class="h-4 w-4" />
                <span>All Orders</span>
              </button>
              <button type="button" @click="handleShowConfirmationOrders" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <CheckCircleIcon class="h-4 w-4" />
                <span>Confirmation</span>
              </button>
              <button type="button" @click="handleShowDeliveryOrders" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <TruckIcon class="h-4 w-4" />
                <span>Delivery</span>
              </button>
              <button type="button" @click="handleShowInvoices" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <DocumentTextIcon class="h-4 w-4" />
                <span>Invoices</span>
              </button>
            </div>
          </div>

          <!-- Products -->
          <div>
            <button @click="toggleProductsMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <BoxIcon class="h-5 w-5 text-white" />
                <span>Products</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isProductsMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Products Sub-menu -->
            <div v-show="isProductsMenuOpen" class="mt-1 ml-4 space-y-1">
              <button type="button" @click="handleShowProductList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ListIcon class="h-4 w-4" />
                <span>All Products</span>
              </button>
              <button type="button" @click="handleShowAddProduct" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <PlusIcon class="h-4 w-4" />
                <span>Add Product</span>
              </button>
              <button type="button" @click="handleShowCategories" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <TagIcon class="h-4 w-4" />
                <span>Categories</span>
              </button>
            </div>
          </div>

          <!-- Users -->
          <div>
            <button @click="toggleUsersMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <UserIcon class="h-5 w-5 text-white" />
                <span>Users</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isUsersMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Users Sub-menu -->
            <div v-show="isUsersMenuOpen" class="mt-1 ml-4 space-y-1">
              <button type="button" @click="handleShowAddUser" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UserPlusIcon class="h-4 w-4" />
                <span>Add User</span>
              </button>
              <button type="button" @click="handleShowAgentsList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Agents</span>
              </button>
              <button type="button" @click="handleShowManagersList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Managers</span>
              </button>
              <button type="button" @click="handleShowAdminsList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Admins</span>
              </button>
              <button type="button" @click="handleShowSellersList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Sellers</span>
              </button>
            </div>
          </div>

          <!-- Clients -->
          <div>
            <a href="/clients" class="flex items-center space-x-3 px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <UsersIcon class="h-5 w-5 text-white" />
              <span>Clients</span>
            </a>
          </div>

          <!-- History -->
          <div>
            <a href="/history" class="flex items-center space-x-3 px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <ClockIcon class="h-5 w-5 text-white" />
              <span>History</span>
            </a>
          </div>

          <!-- Settings -->
          <div>
            <button type="button" @click="handleShowSettings" class="w-full flex items-center space-x-3 px-4 py-3 text-left text-white rounded-lg hover:bg-blue-800">
              <CogIcon class="h-5 w-5 text-white" />
              <span>Settings</span>
            </button>
          </div>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64" style="padding-top: 60px;">
      <main class="p-8">
        <ProductCreate v-if="showAddProduct" @back="handleBackFromProduct" />
        <OrderCreate v-else-if="showAddOrder" @back="handleBackFromOrder" />
        <ProductList v-else-if="showProductList" @add-product="handleShowAddProduct" />
        <OrderList v-else-if="showOrderList" @create-order="handleShowAddOrder" />
        <OrderList v-else-if="showConfirmationOrders" confirmation @create-order="handleShowAddOrder" />
        <OrderList v-else-if="showDeliveryOrders" delivery @create-order="handleShowAddOrder" />
        <InvoicesList v-else-if="showInvoices" />
        <DashboardOverview v-else-if="showOverview" />
        <DashboardAnalytics v-else-if="showAnalytics" />
        <AddUser v-else-if="showAddUser" @back="handleBackFromAddUser" />
        <UserList v-else-if="showAgentsList" role="agent" />
        <UserList v-else-if="showManagersList" role="manager" />
        <UserList v-else-if="showAdminsList" role="admin" />
        <UserList v-else-if="showSellersList" role="seller" />
        <CategoryList v-else-if="showCategories" />
        <SettingsForm v-else-if="showSettings" />
        <slot v-else></slot>
      </main>
    </div>

    <!-- Click outside handler for profile menu -->
    <div v-if="isProfileMenuOpen" class="fixed inset-0 z-40" @click="closeProfileMenu"></div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ProductCreate from './ProductCreate.vue'
import ProductList from './ProductList.vue'
import OrderCreate from './OrderCreate.vue'
import OrderList from './OrderList.vue'
import DashboardOverview from './DashboardOverview.vue'
import DashboardAnalytics from './DashboardAnalytics.vue'
import AddUser from './AddUser.vue'
import UserList from './UserList.vue'
import CategoryList from './CategoryList.vue'
import InvoicesList from './InvoicesList.vue'
import SettingsForm from './SettingsForm.vue'

// Component state
const isDashboardMenuOpen = ref(false)
const isOrdersMenuOpen = ref(false)
const isProductsMenuOpen = ref(false)
const isUsersMenuOpen = ref(false)
const isProfileMenuOpen = ref(false)

// App settings
const appLogo = ref(null)
const appName = ref('Laravel App')
const appDescription = ref('')

// New state for showing Add Product form or Product List
const showAddProduct = ref(false)
const showProductList = ref(false)
const showAddOrder = ref(false)
const showOrderList = ref(false)
const showOverview = ref(true)
const showAnalytics = ref(false)
const showConfirmationOrders = ref(false)
const showDeliveryOrders = ref(false)
const showAddUser = ref(false)
const showAgentsList = ref(false)
const showManagersList = ref(false)
const showAdminsList = ref(false)
const showSellersList = ref(false)
const showCategories = ref(false)
const showInvoices = ref(false)
const showSettings = ref(false)

const toggleDashboardMenu = () => {
  isDashboardMenuOpen.value = !isDashboardMenuOpen.value
}

const toggleOrdersMenu = () => {
  isOrdersMenuOpen.value = !isOrdersMenuOpen.value
}

const toggleProductsMenu = () => {
  isProductsMenuOpen.value = !isProductsMenuOpen.value
}

const toggleUsersMenu = () => {
  isUsersMenuOpen.value = !isUsersMenuOpen.value
}

const toggleProfileMenu = () => {
  isProfileMenuOpen.value = !isProfileMenuOpen.value
}

const closeProfileMenu = () => {
  isProfileMenuOpen.value = false
}

const signOut = async () => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    // After successful logout, redirect to login page
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout failed', error)
  }
}

const clearViews = () => {
  showAddProduct.value = false
  showProductList.value = false
  showAddOrder.value = false
  showOrderList.value = false
  showOverview.value = false
  showAnalytics.value = false
  showConfirmationOrders.value = false
  showDeliveryOrders.value = false
  showAddUser.value = false
  showAgentsList.value = false
  showManagersList.value = false
  showAdminsList.value = false
  showSellersList.value = false
  showCategories.value = false
  showInvoices.value = false
  showSettings.value = false
}

const handleShowAddProduct = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showAddProduct.value = true
}

const handleShowProductList = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showProductList.value = true
}

const handleBackFromProduct = () => {
  showAddProduct.value = false
  showProductList.value = true
}

const handleShowAddOrder = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showAddOrder.value = true
}

const handleBackFromOrder = () => {
  showAddOrder.value = false
  showProductList.value = true
}

const handleShowOrderList = (e) => {
  if (e) e.preventDefault()
  showOrderList.value = true
  showAddOrder.value = false
  showAddProduct.value = false
  showProductList.value = false
}

const handleShowConfirmationOrders = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showConfirmationOrders.value = true
}

const handleShowDeliveryOrders = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showDeliveryOrders.value = true
}

const handleShowInvoices = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showInvoices.value = true
}

const handleShowOverview = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showOverview.value = true
}

const handleShowAnalytics = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showAnalytics.value = true
}

const handleShowAddUser = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showAddUser.value = true
}

const handleBackFromAddUser = () => {
  showAddUser.value = false
  showOverview.value = true
}

const handleShowAgentsList = (e)=>{ if(e) e.preventDefault(); clearViews(); showAgentsList.value=true }
const handleShowManagersList = (e)=>{ if(e) e.preventDefault(); clearViews(); showManagersList.value=true }
const handleShowAdminsList = (e)=>{ if(e) e.preventDefault(); clearViews(); showAdminsList.value=true }
const handleShowSellersList = (e)=>{ if(e) e.preventDefault(); clearViews(); showSellersList.value=true }

const handleShowCategories = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showCategories.value = true
}

const handleShowSettings = (e) => {
  if (e) e.preventDefault()
  clearViews()
  showSettings.value = true
}

// Fetch app settings
const fetchAppSettings = async () => {
  try {
    const response = await fetch('/settings/get')
    if (response.ok) {
      const settings = await response.json()
      appLogo.value = settings.app_logo
      appName.value = settings.app_name || 'Laravel App'
      appDescription.value = settings.app_description || ''
    }
  } catch (error) {
    console.error('Failed to fetch app settings:', error)
  }
}

// Fetch settings on component mount
fetchAppSettings()
</script>

<script>
// Icons
const HomeIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  `
}

const BoxIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
    </svg>
  `
}

const ShoppingCartIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  `
}

const UsersIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
  `
}

const ClockIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  `
}

const CogIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  `
}

const ChevronDownIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
    </svg>
  `
}

const UserIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
  `
}

const ChartBarIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
  `
}

const ChartPieIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
    </svg>
  `
}

const ListIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
    </svg>
  `
}

const CheckCircleIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4" />
    </svg>
  `
}

const TruckIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17l4 4m-4-4l-4 4m12-3a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  `
}

const PlusIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
    </svg>
  `
}

const TagIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M11 7h.01M11 11h.01M11 15h.01M11 19h.01M15 7h.01M15 11h.01M15 15h.01M15 19h.01" />
    </svg>
  `
}

const UserPlusIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
    </svg>
  `
}

const SellerIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
  `
}

export default {
  components: {
    HomeIcon,
    BoxIcon,
    ShoppingCartIcon,
    UsersIcon,
    ClockIcon,
    CogIcon,
    ChevronDownIcon,
    UserIcon,
    ChartBarIcon,
    ChartPieIcon,
    ListIcon,
    CheckCircleIcon,
    TruckIcon,
    PlusIcon,
    TagIcon,
    UserPlusIcon,
    SellerIcon
  }
}
</script>

<style>
.router-link-active {
  @apply bg-gray-50 text-gray-900;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 