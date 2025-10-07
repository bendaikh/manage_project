<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Access Rights Management</h1>
      <p class="text-gray-600">Manage roles and permissions for your application</p>
    </div>

    <!-- Success/Error Message -->
    <div v-if="message" :class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4' : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'">
      {{ message }}
    </div>

    <!-- Roles List -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-800">Roles</h2>
          <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Create New Role
          </button>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in roles" :key="role.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-blue-600">{{ role.name.charAt(0).toUpperCase() }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ role.description || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ role.users_count || 0 }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="showPermissionsPopup(role)" class="text-blue-600 hover:text-blue-900">View Permissions</button>
                  <button @click="editRole(role)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                  <button @click="deleteRole(role)" :disabled="role.name === 'superadmin'" class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- View Permissions Modal -->
    <div v-if="showPermissionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-[800px] max-w-full shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Permissions for {{ selectedRole?.name }}
          </h3>
          
          <div class="max-h-96 overflow-y-auto">
            <div class="space-y-4">
              <div v-for="permission in selectedRole?.permissions || []" :key="permission.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ permission.name }}</div>
                  <div class="text-xs text-gray-500">{{ permission.description }}</div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Allowed
                </span>
              </div>
            </div>
          </div>
          
          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-600">
              Total permissions: {{ selectedRole?.permissions.length || 0 }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Role Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-[800px] max-w-full shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Edit Role Permissions
          </h3>
          <form @submit.prevent="updateRole()">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Role Name
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter role name"
                required
                :disabled="editingRole?.name === 'superadmin'"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="2"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter role description"
              ></textarea>
            </div>
            <div class="mb-4">
              <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium text-gray-700">
                  Permissions
                </label>
                <div class="flex space-x-2">
                  <button 
                    @click="expandAllSections($event)" 
                    type="button"
                    class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                  >
                    Expand All
                  </button>
                  <button 
                    @click="collapseAllSections($event)" 
                    type="button"
                    class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors"
                  >
                    Collapse All
                  </button>
                </div>
              </div>
              <div class="space-y-3 max-h-96 overflow-y-auto border border-gray-200 rounded-md p-4">
                <!-- Dashboard Section -->
                <div class="permission-section border border-gray-200 rounded-lg">
                  <button 
                    type="button"
                    @click="toggleSection('dashboard', $event)" 
                    class="w-full px-4 py-3 text-left flex items-center justify-between hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors"
                  >
                    <div class="flex items-center">
                      <span class="text-lg mr-3">{{ getSectionIcon('dashboard') }}</span>
                      <h4 class="text-sm font-semibold text-gray-800">Dashboard</h4>
                      <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        {{ getPermissionsBySection('dashboard').length }}
                      </span>
                    </div>
                    <svg 
                      class="w-5 h-5 text-gray-500 transition-transform duration-200" 
                      :class="{ 'rotate-180': !collapsedSections.dashboard }"
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div v-show="!collapsedSections.dashboard" class="px-4 pb-3 border-t border-gray-100">
                    <div class="grid grid-cols-1 gap-2 mt-3">
                      <div v-for="permission in getPermissionsBySection('dashboard')" :key="permission.id" class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md">
                        <div class="flex-1">
                          <label :for="'perm-' + permission.id" class="text-sm text-gray-700 cursor-pointer">
                            {{ permission.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                          </label>
                          <p class="text-xs text-gray-500">{{ permission.description }}</p>
                        </div>
                        <input
                          type="checkbox"
                          :id="'perm-' + permission.id"
                          v-model="form.permissions"
                          :value="permission.id"
                          :disabled="editingRole?.name === 'superadmin' && permission.name === 'manage_roles'"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- User Management Section -->
                <div class="permission-section border border-gray-200 rounded-lg">
                  <button 
                    type="button"
                    @click="toggleSection('users', $event)" 
                    class="w-full px-4 py-3 text-left flex items-center justify-between hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors"
                  >
                    <div class="flex items-center">
                      <span class="text-lg mr-3">{{ getSectionIcon('users') }}</span>
                      <h4 class="text-sm font-semibold text-gray-800">User Management</h4>
                      <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                        {{ getPermissionsBySection('users').length }}
                      </span>
                    </div>
                    <svg 
                      class="w-5 h-5 text-gray-500 transition-transform duration-200" 
                      :class="{ 'rotate-180': !collapsedSections.users }"
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div v-show="!collapsedSections.users" class="px-4 pb-3 border-t border-gray-100">
                    <div class="grid grid-cols-1 gap-2 mt-3">
                      <div v-for="permission in getPermissionsBySection('users')" :key="permission.id" class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md">
                        <div class="flex-1">
                          <label :for="'perm-' + permission.id" class="text-sm text-gray-700 cursor-pointer">
                            {{ permission.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                          </label>
                          <p class="text-xs text-gray-500">{{ permission.description }}</p>
                        </div>
                        <input
                          type="checkbox"
                          :id="'perm-' + permission.id"
                          v-model="form.permissions"
                          :value="permission.id"
                          :disabled="editingRole?.name === 'superadmin' && permission.name === 'manage_roles'"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Dynamic Sections -->
                <div 
                  v-for="section in ['roles', 'orders', 'products', 'categories', 'warehouses', 'stock', 'shipments', 'invoices', 'seller_invoices', 'product_offers', 'accounting', 'reports', 'history', 'settings']" 
                  :key="section"
                  class="permission-section border border-gray-200 rounded-lg"
                >
                  <button 
                    type="button"
                    @click="toggleSection(section, $event)" 
                    class="w-full px-4 py-3 text-left flex items-center justify-between hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors"
                  >
                    <div class="flex items-center">
                      <span class="text-lg mr-3">{{ getSectionIcon(section) }}</span>
                      <h4 class="text-sm font-semibold text-gray-800">
                        {{ section.charAt(0).toUpperCase() + section.slice(1).replace(/([A-Z])/g, ' $1') }} Management
                      </h4>
                      <span class="ml-2 text-xs px-2 py-1 rounded-full" :class="getSectionBadgeClass(section)">
                        {{ getPermissionsBySection(section).length }}
                      </span>
                    </div>
                    <svg 
                      class="w-5 h-5 text-gray-500 transition-transform duration-200" 
                      :class="{ 'rotate-180': !collapsedSections[section] }"
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </button>
                  <div v-show="!collapsedSections[section]" class="px-4 pb-3 border-t border-gray-100">
                    <div class="grid grid-cols-1 gap-2 mt-3">
                      <div v-for="permission in getPermissionsBySection(section)" :key="permission.id" class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md">
                        <div class="flex-1">
                          <label :for="'perm-' + permission.id" class="text-sm text-gray-700 cursor-pointer">
                            {{ permission.name.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                          </label>
                          <p class="text-xs text-gray-500">{{ permission.description }}</p>
                        </div>
                        <input
                          type="checkbox"
                          :id="'perm-' + permission.id"
                          v-model="form.permissions"
                          :value="permission.id"
                          :disabled="editingRole?.name === 'superadmin' && permission.name === 'manage_roles'"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Permission Summary -->
              <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                  <span class="font-medium">{{ form.permissions.length }}</span> permissions selected
                </div>
              </div>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                {{ isLoading ? 'Updating...' : 'Update Role' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const roles = ref([])
const permissions = ref([])
const isLoading = ref(false)
const message = ref('')
const messageType = ref('success')

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showPermissionsModal = ref(false)
const selectedRole = ref(null)
const editingRole = ref(null)

// Form data
const form = ref({
  name: '',
  description: '',
  permissions: []
})

// Collapsible sections state
const collapsedSections = ref({
  dashboard: false,
  users: false,
  roles: false,
  orders: false,
  products: false,
  categories: false,
  warehouses: false,
  stock: false,
  shipments: false,
  invoices: false,
  seller_invoices: false,
  product_offers: false,
  accounting: false,
  reports: false,
  history: false,
  settings: false
})

const fetchRoles = async () => {
  try {
    const response = await fetch('/api/roles', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    roles.value = data
  } catch (error) {
    console.error('Failed to fetch roles:', error)
    showMessage('Failed to load roles. Please check your permissions.', 'error')
  }
}

const fetchPermissions = async () => {
  try {
    const response = await fetch('/api/permissions', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    permissions.value = data
  } catch (error) {
    console.error('Failed to fetch permissions:', error)
    showMessage('Failed to load permissions. Please check your permissions.', 'error')
  }
}

const showPermissionsPopup = (role) => {
  selectedRole.value = role
  showPermissionsModal.value = true
}

const closePermissionsModal = () => {
  showPermissionsModal.value = false
  selectedRole.value = null
}

const editRole = (role) => {
  editingRole.value = role
  form.value = {
    name: role.name,
    description: role.description || '',
    permissions: role.permissions.map(p => p.id)
  }
  fetchPermissions() // Fetch permissions before showing modal
  showEditModal.value = true
}

const deleteRole = async (role) => {
  if (role.name === 'superadmin') {
    showMessage('Cannot delete the superadmin role', 'error')
    return
  }
  
  if (!confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
    return
  }
  
  try {
    const response = await fetch(`/roles/${role.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      showMessage('Role deleted successfully', 'success')
      await fetchRoles()
    } else {
      const error = await response.json()
      showMessage(error.message || 'Failed to delete role', 'error')
    }
  } catch (error) {
    console.error('Failed to delete role:', error)
    showMessage('Failed to delete role', 'error')
  }
}

const updateRole = async () => {
  isLoading.value = true
  try {
    const response = await fetch(`/roles/${editingRole.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      showMessage('Role updated successfully', 'success')
      await fetchRoles()
      closeModal()
    } else {
      const error = await response.json()
      showMessage(error.message || 'Failed to update role', 'error')
    }
  } catch (error) {
    console.error('Failed to update role:', error)
    showMessage('Failed to update role', 'error')
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  showEditModal.value = false
  editingRole.value = null
  form.value = {
    name: '',
    description: '',
    permissions: []
  }
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

// Helper function to get permissions by section
const getPermissionsBySection = (section) => {
  const sectionMappings = {
    'dashboard': ['view_dashboard', 'view_dashboard_overview', 'view_dashboard_analytics'],
    'users': ['view_users', 'create_users', 'edit_users', 'delete_users', 'manage_users'],
    'roles': ['view_roles', 'create_roles', 'edit_roles', 'delete_roles', 'manage_roles', 'view_permissions', 'create_permissions', 'edit_permissions', 'delete_permissions', 'manage_permissions'],
    'orders': ['view_orders', 'create_orders', 'edit_orders', 'delete_orders', 'view_order_details', 'update_order_status', 'view_confirmation_orders', 'confirm_orders', 'view_delivery_orders', 'process_delivery_orders', 'import_orders', 'export_orders', 'manage_orders', 'assign_orders_to_agents'],
    'products': ['view_products', 'create_products', 'edit_products', 'delete_products', 'view_product_details', 'view_product_catalog', 'manage_products'],
    'categories': ['view_categories', 'create_categories', 'edit_categories', 'delete_categories', 'manage_categories'],
    'warehouses': ['view_warehouses', 'create_warehouses', 'edit_warehouses', 'delete_warehouses', 'view_warehouse_details', 'manage_warehouse_stock', 'manage_warehouses'],
    'stock': ['view_stock', 'manage_stock', 'view_stock_global', 'manage_stock_global'],
    'shipments': ['view_shipments', 'create_shipments', 'edit_shipments', 'delete_shipments', 'validate_shipments', 'manage_shipments'],
    'invoices': ['view_invoices', 'create_invoices', 'download_invoices', 'view_delivery_notes', 'create_delivery_notes', 'download_delivery_notes', 'view_delivery_invoices', 'create_delivery_invoices', 'download_delivery_invoices'],
    'seller_invoices': ['view_seller_invoices', 'create_seller_invoices', 'edit_seller_invoices', 'delete_seller_invoices', 'download_seller_invoices', 'approve_seller_invoices', 'reject_seller_invoices', 'mark_seller_invoices_paid', 'generate_seller_invoices', 'manage_seller_invoices'],
    'product_offers': ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'],
    'accounting': ['view_accounting', 'view_accounting_overview', 'view_incomes', 'create_incomes', 'edit_incomes', 'delete_incomes', 'manage_incomes', 'view_income_categories', 'create_income_categories', 'edit_income_categories', 'delete_income_categories', 'manage_income_categories', 'view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses', 'manage_expenses', 'view_expense_categories', 'create_expense_categories', 'edit_expense_categories', 'delete_expense_categories', 'manage_expense_categories', 'view_refunds', 'create_refunds', 'edit_refunds', 'delete_refunds', 'manage_refunds', 'view_accounts', 'create_accounts', 'edit_accounts', 'delete_accounts', 'manage_accounts'],
    'reports': ['view_reports', 'generate_reports', 'export_reports', 'manage_reports'],
    'history': ['view_history', 'manage_history'],
    'settings': ['view_settings', 'edit_settings', 'manage_settings']
  }
  
  const sectionPermissions = sectionMappings[section] || []
  return permissions.value.filter(permission => sectionPermissions.includes(permission.name))
}

// Toggle section collapse
const toggleSection = (section, event) => {
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  collapsedSections.value[section] = !collapsedSections.value[section]
}

// Expand all sections
const expandAllSections = (event) => {
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  Object.keys(collapsedSections.value).forEach(section => {
    collapsedSections.value[section] = false
  })
}

// Collapse all sections
const collapseAllSections = (event) => {
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  Object.keys(collapsedSections.value).forEach(section => {
    collapsedSections.value[section] = true
  })
}

// Get section icon
const getSectionIcon = (section) => {
  const icons = {
    'dashboard': '📊',
    'users': '👥',
    'roles': '🔐',
    'orders': '📦',
    'products': '🛍️',
    'categories': '📂',
    'warehouses': '🏪',
    'stock': '📋',
    'shipments': '🚚',
    'invoices': '🧾',
    'seller_invoices': '💼',
    'product_offers': '🎯',
    'accounting': '💰',
    'reports': '📈',
    'support': '🎧',
    'settings': '⚙️'
  }
  return icons[section] || '📁'
}

// Get section badge class
const getSectionBadgeClass = (section) => {
  const badgeClasses = {
    'dashboard': 'bg-blue-100 text-blue-800',
    'users': 'bg-green-100 text-green-800',
    'roles': 'bg-purple-100 text-purple-800',
    'orders': 'bg-orange-100 text-orange-800',
    'products': 'bg-indigo-100 text-indigo-800',
    'categories': 'bg-pink-100 text-pink-800',
    'warehouses': 'bg-yellow-100 text-yellow-800',
    'stock': 'bg-teal-100 text-teal-800',
    'shipments': 'bg-red-100 text-red-800',
    'invoices': 'bg-cyan-100 text-cyan-800',
    'seller_invoices': 'bg-amber-100 text-amber-800',
    'product_offers': 'bg-purple-100 text-purple-800',
    'accounting': 'bg-emerald-100 text-emerald-800',
    'reports': 'bg-violet-100 text-violet-800',
    'support': 'bg-rose-100 text-rose-800',
    'settings': 'bg-gray-100 text-gray-800'
  }
  return badgeClasses[section] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchRoles()
  fetchPermissions()
})
</script>
