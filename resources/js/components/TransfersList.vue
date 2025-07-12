<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Transfers Management</h2>
        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Transfer
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search transfers..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @keyup.enter="loadTransfers"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="loadTransfers"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="loadTransfers"
          />
        </div>
        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Reason
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              From User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              To User
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Amount
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="transfer in transfers" :key="transfer.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(transfer.date) }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              <div class="max-w-xs truncate" :title="transfer.reason">
                {{ transfer.reason || 'N/A' }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="flex items-center">
                <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                <span>{{ transfer.from_user ? transfer.from_user.name : 'N/A' }}</span>
                <span class="ml-1 text-xs text-gray-400">({{ transfer.from_user ? transfer.from_user.roles[0]?.name : 'N/A' }})</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="flex items-center">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                <span>{{ transfer.to_user ? transfer.to_user.name : 'N/A' }}</span>
                <span class="ml-1 text-xs text-gray-400">({{ transfer.to_user ? transfer.to_user.roles[0]?.name : 'N/A' }})</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">
              {{ formatCurrency(transfer.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(transfer)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteTransfer(transfer)"
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Transfer</h3>
          <form @submit.prevent="createTransfer">
            <div class="mb-4">
              <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Transfer Reason</label>
              <textarea
                v-model="createForm.reason"
                id="reason"
                rows="3"
                placeholder="Enter transfer reason"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              ></textarea>
            </div>
            <div class="mb-4">
              <label for="from_user_type" class="block text-sm font-medium text-gray-700 mb-2">From User Type</label>
              <select
                v-model="createForm.from_user_type"
                id="from_user_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="loadUsersByType('from')"
                required
              >
                <option value="">Select User Type</option>
                <option value="agent">Agent</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="from_user_id" class="block text-sm font-medium text-gray-700 mb-2">From User</label>
              <select
                v-model="createForm.from_user_id"
                id="from_user_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select User</option>
                <option v-for="user in fromUsers" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label for="to_user_type" class="block text-sm font-medium text-gray-700 mb-2">To User Type</label>
              <select
                v-model="createForm.to_user_type"
                id="to_user_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="loadUsersByType('to')"
                required
              >
                <option value="">Select User Type</option>
                <option value="agent">Agent</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="to_user_id" class="block text-sm font-medium text-gray-700 mb-2">To User</label>
              <select
                v-model="createForm.to_user_id"
                id="to_user_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select User</option>
                <option v-for="user in toUsers" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
              <input
                v-model="createForm.amount"
                type="number"
                step="0.01"
                min="0"
                id="amount"
                placeholder="Enter amount"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
              <input
                v-model="createForm.date"
                type="date"
                id="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
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
                {{ loading ? 'Saving...' : 'Save' }}
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Transfer</h3>
          <form @submit.prevent="updateTransfer">
            <div class="mb-4">
              <label for="edit-reason" class="block text-sm font-medium text-gray-700 mb-2">Transfer Reason</label>
              <textarea
                v-model="editForm.reason"
                id="edit-reason"
                rows="3"
                placeholder="Enter transfer reason"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              ></textarea>
            </div>
            <div class="mb-4">
              <label for="edit-from_user_type" class="block text-sm font-medium text-gray-700 mb-2">From User Type</label>
              <select
                v-model="editForm.from_user_type"
                id="edit-from_user_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="loadUsersByType('from')"
                required
              >
                <option value="">Select User Type</option>
                <option value="agent">Agent</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-from_user_id" class="block text-sm font-medium text-gray-700 mb-2">From User</label>
              <select
                v-model="editForm.from_user_id"
                id="edit-from_user_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select User</option>
                <option v-for="user in fromUsers" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-to_user_type" class="block text-sm font-medium text-gray-700 mb-2">To User Type</label>
              <select
                v-model="editForm.to_user_type"
                id="edit-to_user_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="loadUsersByType('to')"
                required
              >
                <option value="">Select User Type</option>
                <option value="agent">Agent</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-to_user_id" class="block text-sm font-medium text-gray-700 mb-2">To User</label>
              <select
                v-model="editForm.to_user_id"
                id="edit-to_user_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select User</option>
                <option v-for="user in toUsers" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.email }})
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
              <input
                v-model="editForm.amount"
                type="number"
                step="0.01"
                min="0"
                id="edit-amount"
                placeholder="Enter amount"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="edit-date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
              <input
                v-model="editForm.date"
                type="date"
                id="edit-date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
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
                {{ loading ? 'Updating...' : 'Update Transfer' }}
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

const transfers = ref([])
const fromUsers = ref([])
const toUsers = ref([])
const pagination = ref(null)
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const filters = ref({
  search: '',
  date_from: '',
  date_to: ''
})
const createForm = ref({
  reason: '',
  from_user_type: '',
  from_user_id: '',
  to_user_type: '',
  to_user_id: '',
  amount: '',
  date: new Date().toISOString().split('T')[0]
})
const editForm = ref({
  id: null,
  reason: '',
  from_user_type: '',
  from_user_id: '',
  to_user_type: '',
  to_user_id: '',
  amount: '',
  date: ''
})

const loadTransfers = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    params.append('page', page)
    
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.date_from) params.append('date_from', filters.value.date_from)
    if (filters.value.date_to) params.append('date_to', filters.value.date_to)
    
    const response = await fetch(`/accounting/user-transfers?${params.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      transfers.value = data.data
      pagination.value = {
        current_page: data.current_page,
        from: data.from,
        to: data.to,
        total: data.total,
        prev_page_url: data.prev_page_url,
        next_page_url: data.next_page_url
      }
    }
  } catch (error) {
    console.error('Error loading transfers:', error)
  } finally {
    loading.value = false
  }
}

const loadUsersByType = async (type, formType = 'create') => {
  try {
    const form = formType === 'create' ? createForm.value : editForm.value
    const userType = type === 'from' ? form.from_user_type : form.to_user_type
    if (!userType) return

    console.log(`Loading users for type: ${userType} (form: ${formType})`)
    
    // Clear the users list first
    if (type === 'from') {
      fromUsers.value = []
    } else {
      toUsers.value = []
    }
    
    const response = await fetch(`/api/users/by-role/${userType}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin' // Include cookies for authentication
    })
    
    console.log('Response status:', response.status)
    
    if (response.ok) {
      const data = await response.json()
      console.log('Users data:', data)
      
      // Check if there's an error in the response
      if (data.error) {
        console.error('API Error:', data.error)
        if (data.available_roles) {
          console.log('Available roles:', data.available_roles)
        }
        return
      }
      
      if (type === 'from') {
        fromUsers.value = data
        console.log('From users updated:', fromUsers.value)
      } else {
        toUsers.value = data
        console.log('To users updated:', toUsers.value)
      }
    } else {
      console.error('Response not ok:', response.status, response.statusText)
      const errorText = await response.text()
      console.error('Error response:', errorText)
      
      // Show user-friendly error message
      if (response.status === 401) {
        alert('Authentication required. Please refresh the page and try again.')
      } else {
        alert(`Error loading users: ${response.status} ${response.statusText}`)
      }
    }
  } catch (error) {
    console.error('Error loading users by type:', error)
    alert('Network error while loading users. Please check your connection.')
  }
}

const loadPage = (page) => {
  loadTransfers(page)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    date_from: '',
    date_to: ''
  }
  loadTransfers()
}

const openCreateModal = () => {
  createForm.value = {
    reason: '',
    from_user_type: '',
    from_user_id: '',
    to_user_type: '',
    to_user_id: '',
    amount: '',
    date: new Date().toISOString().split('T')[0]
  }
  fromUsers.value = []
  toUsers.value = []
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    reason: '',
    from_user_type: '',
    from_user_id: '',
    to_user_type: '',
    to_user_id: '',
    amount: '',
    date: new Date().toISOString().split('T')[0]
  }
  fromUsers.value = []
  toUsers.value = []
}

const openEditModal = (transfer) => {
  // Format the date to YYYY-MM-DD for the date input
  const formattedDate = transfer.date ? new Date(transfer.date).toISOString().split('T')[0] : ''
  
  editForm.value = {
    id: transfer.id,
    reason: transfer.reason || '',
    from_user_type: transfer.from_user ? transfer.from_user.roles[0]?.name || '' : '',
    from_user_id: transfer.from_user_id || '',
    to_user_type: transfer.to_user ? transfer.to_user.roles[0]?.name || '' : '',
    to_user_id: transfer.to_user_id || '',
    amount: transfer.amount || '',
    date: formattedDate
  }
  
  // Load users for the current types
  if (editForm.value.from_user_type) {
    loadUsersByType('from', 'edit')
  }
  if (editForm.value.to_user_type) {
    loadUsersByType('to', 'edit')
  }
  
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = {
    id: null,
    reason: '',
    from_user_type: '',
    from_user_id: '',
    to_user_type: '',
    to_user_id: '',
    amount: '',
    date: ''
  }
  fromUsers.value = []
  toUsers.value = []
}

const createTransfer = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch('/accounting/user-transfers', {
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
      loadTransfers()
    } else {
      const error = await response.json()
      alert(error.message || 'Error creating transfer')
    }
  } catch (error) {
    console.error('Error creating transfer:', error)
    alert('Error creating transfer')
  } finally {
    loading.value = false
  }
}

const updateTransfer = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/user-transfers/${editForm.value.id}`, {
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
      loadTransfers()
    } else {
      const error = await response.json()
      alert(error.message || 'Error updating transfer')
    }
  } catch (error) {
    console.error('Error updating transfer:', error)
    alert('Error updating transfer')
  } finally {
    loading.value = false
  }
}

const deleteTransfer = async (transfer) => {
  if (!confirm('Are you sure you want to delete this transfer?')) {
    return
  }
  
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/user-transfers/${transfer.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      loadTransfers()
    } else {
      const error = await response.json()
      alert(error.message || 'Error deleting transfer')
    }
  } catch (error) {
    console.error('Error deleting transfer:', error)
    alert('Error deleting transfer')
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

onMounted(() => {
  loadTransfers()
})
</script> 