<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Accounts Management</h2>
        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Account
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Account Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Initial Balance
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Current Balance
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Created
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="account in accounts" :key="account.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ account.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatCurrency(account.initial_balance) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" :class="getBalanceColor(account.current_balance)">
              {{ formatCurrency(account.current_balance) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ account.type || 'Bank' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusClass(account.status)">
                {{ account.status || 'Active' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(account.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(account)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteAccount(account)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="px-6 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex space-x-2">
          <button
            v-if="pagination.prev_page_url"
            @click="loadPage(pagination.current_page - 1)"
            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Previous
          </button>
          <button
            v-if="pagination.next_page_url"
            @click="loadPage(pagination.current_page + 1)"
            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Account</h3>
          <form @submit.prevent="createAccount">
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
              <input
                v-model="createForm.name"
                type="text"
                id="name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="initial_balance" class="block text-sm font-medium text-gray-700 mb-2">Initial Balance</label>
              <input
                v-model="createForm.initial_balance"
                type="number"
                step="0.01"
                min="0"
                id="initial_balance"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
              <select
                v-model="createForm.type"
                id="type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="Bank">Bank</option>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Savings">Savings</option>
                <option value="Investment">Investment</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="createForm.status"
                id="status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Suspended">Suspended</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
              <textarea
                v-model="createForm.description"
                id="description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                :disabled="loading"
              >
                {{ loading ? 'Creating...' : 'Create Account' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Account</h3>
          <form @submit.prevent="updateAccount">
            <div class="mb-4">
              <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
              <input
                v-model="editForm.name"
                type="text"
                id="edit-name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="edit-initial_balance" class="block text-sm font-medium text-gray-700 mb-2">Initial Balance</label>
              <input
                v-model="editForm.initial_balance"
                type="number"
                step="0.01"
                min="0"
                id="edit-initial_balance"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="edit-type" class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
              <select
                v-model="editForm.type"
                id="edit-type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="Bank">Bank</option>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Savings">Savings</option>
                <option value="Investment">Investment</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="editForm.status"
                id="edit-status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="Suspended">Suspended</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
              <textarea
                v-model="editForm.description"
                id="edit-description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeEditModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                :disabled="loading"
              >
                {{ loading ? 'Updating...' : 'Update Account' }}
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

const accounts = ref([])
const pagination = ref(null)
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const createForm = ref({
  name: '',
  initial_balance: 0,
  type: 'Bank',
  status: 'Active',
  description: ''
})
const editForm = ref({
  id: null,
  name: '',
  initial_balance: 0,
  type: 'Bank',
  status: 'Active',
  description: ''
})

const loadAccounts = async (page = 1) => {
  try {
    loading.value = true
    const response = await fetch(`/accounting/accounts?page=${page}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      accounts.value = data.data || data
      if (data.current_page) {
        pagination.value = {
          current_page: data.current_page,
          from: data.from,
          to: data.to,
          total: data.total,
          prev_page_url: data.prev_page_url,
          next_page_url: data.next_page_url
        }
      }
    }
  } catch (error) {
    console.error('Error loading accounts:', error)
  } finally {
    loading.value = false
  }
}

const loadPage = (page) => {
  loadAccounts(page)
}

const openCreateModal = () => {
  createForm.value = {
    name: '',
    initial_balance: 0,
    type: 'Bank',
    status: 'Active',
    description: ''
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    name: '',
    initial_balance: 0,
    type: 'Bank',
    status: 'Active',
    description: ''
  }
}

const openEditModal = (account) => {
  editForm.value = {
    id: account.id,
    name: account.name,
    initial_balance: account.initial_balance,
    type: account.type || 'Bank',
    status: account.status || 'Active',
    description: account.description || ''
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = {
    id: null,
    name: '',
    initial_balance: 0,
    type: 'Bank',
    status: 'Active',
    description: ''
  }
}

const createAccount = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch('/accounting/accounts', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(createForm.value)
    })
    
    if (response.ok) {
      closeCreateModal()
      loadAccounts()
    } else {
      const error = await response.json()
      alert(error.message || 'Error creating account')
    }
  } catch (error) {
    console.error('Error creating account:', error)
    alert('Error creating account')
  } finally {
    loading.value = false
  }
}

const updateAccount = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/accounts/${editForm.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(editForm.value)
    })
    
    if (response.ok) {
      closeEditModal()
      loadAccounts()
    } else {
      const error = await response.json()
      alert(error.message || 'Error updating account')
    }
  } catch (error) {
    console.error('Error updating account:', error)
    alert('Error updating account')
  } finally {
    loading.value = false
  }
}

const deleteAccount = async (account) => {
  if (!confirm('Are you sure you want to delete this account?')) {
    return
  }
  
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/accounts/${account.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      loadAccounts()
    } else {
      const error = await response.json()
      alert(error.message || 'Error deleting account')
    }
  } catch (error) {
    console.error('Error deleting account:', error)
    alert('Error deleting account')
  } finally {
    loading.value = false
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const getBalanceColor = (balance) => {
  if (balance > 0) return 'text-green-600'
  if (balance < 0) return 'text-red-600'
  return 'text-gray-600'
}

const getStatusClass = (status) => {
  switch (status) {
    case 'Active':
      return 'bg-green-100 text-green-800'
    case 'Inactive':
      return 'bg-gray-100 text-gray-800'
    case 'Suspended':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-blue-100 text-blue-800'
  }
}

onMounted(() => {
  loadAccounts()
})
</script> 