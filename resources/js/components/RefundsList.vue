<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Refunds Management</h2>
        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Refund
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
            placeholder="Search refunds..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @keyup.enter="loadRefunds"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="loadRefunds"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="loadRefunds"
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
              Order SKU
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Reason
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="refund in refunds" :key="refund.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ refund.order_sku || 'N/A' }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              <div class="max-w-xs truncate" :title="refund.reason">
                {{ refund.reason || 'N/A' }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(refund.date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(refund)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteRefund(refund)"
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Refund</h3>
          <form @submit.prevent="createRefund">
            <div class="mb-4">
              <label for="order_sku" class="block text-sm font-medium text-gray-700 mb-2">Order SKU</label>
              <input
                v-model="createForm.order_sku"
                type="text"
                id="order_sku"
                placeholder="Enter order SKU"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason of Refund (Optional)</label>
              <textarea
                v-model="createForm.reason"
                id="reason"
                rows="3"
                placeholder="Enter refund reason"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Refund</h3>
          <form @submit.prevent="updateRefund">
            <div class="mb-4">
              <label for="edit-order_sku" class="block text-sm font-medium text-gray-700 mb-2">Order SKU</label>
              <input
                v-model="editForm.order_sku"
                type="text"
                id="edit-order_sku"
                placeholder="Enter order SKU"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="edit-reason" class="block text-sm font-medium text-gray-700 mb-2">Reason of Refund (Optional)</label>
              <textarea
                v-model="editForm.reason"
                id="edit-reason"
                rows="3"
                placeholder="Enter refund reason"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
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
                {{ loading ? 'Updating...' : 'Update Refund' }}
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

const refunds = ref([])
const accounts = ref([])
const expenses = ref([])
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
  order_sku: '',
  reason: '',
  date: new Date().toISOString().split('T')[0]
})
const editForm = ref({
  id: null,
  order_sku: '',
  reason: '',
  date: ''
})

const loadRefunds = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    params.append('page', page)
    
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.date_from) params.append('date_from', filters.value.date_from)
    if (filters.value.date_to) params.append('date_to', filters.value.date_to)
    
    const response = await fetch(`/accounting/expenses/refunds?${params.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      refunds.value = data.data
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
    console.error('Error loading refunds:', error)
  } finally {
    loading.value = false
  }
}

const loadAccounts = async () => {
  try {
    const response = await fetch('/accounting/accounts', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      accounts.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading accounts:', error)
  }
}

const loadExpenses = async () => {
  try {
    const response = await fetch('/accounting/expenses', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      expenses.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading expenses:', error)
  }
}

const loadPage = (page) => {
  loadRefunds(page)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    date_from: '',
    date_to: ''
  }
  loadRefunds()
}

const openCreateModal = () => {
  createForm.value = {
    order_sku: '',
    reason: '',
    date: new Date().toISOString().split('T')[0]
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    order_sku: '',
    reason: '',
    date: new Date().toISOString().split('T')[0]
  }
}

const openEditModal = (refund) => {
  // Format the date to YYYY-MM-DD for the date input
  const formattedDate = refund.date ? new Date(refund.date).toISOString().split('T')[0] : ''
  
  editForm.value = {
    id: refund.id,
    order_sku: refund.order_sku || '',
    reason: refund.reason || '',
    date: formattedDate
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = {
    id: null,
    order_sku: '',
    reason: '',
    date: ''
  }
}

const createRefund = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    // Prepare the data with default values for required fields
    const refundData = {
      description: `Refund for Order SKU: ${createForm.value.order_sku}`,
      amount: 0, // Default amount
      account_id: accounts.value.length > 0 ? accounts.value[0].id : null, // Use first account as default
      date: createForm.value.date,
      order_sku: createForm.value.order_sku,
      reason: createForm.value.reason
    }
    
    const response = await fetch('/accounting/expenses/refunds', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(refundData)
    })
    
    if (response.ok) {
      closeCreateModal()
      loadRefunds()
    } else {
      const error = await response.json()
      alert(error.message || 'Error creating refund')
    }
  } catch (error) {
    console.error('Error creating refund:', error)
    alert('Error creating refund')
  } finally {
    loading.value = false
  }
}

const updateRefund = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    // Only send the fields we want to update
    const updateData = {
      order_sku: editForm.value.order_sku,
      reason: editForm.value.reason,
      date: editForm.value.date
    }
    
    const response = await fetch(`/accounting/expenses/refunds/${editForm.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(updateData)
    })
    
    if (response.ok) {
      closeEditModal()
      loadRefunds()
    } else {
      const error = await response.json()
      alert(error.message || 'Error updating refund')
    }
  } catch (error) {
    console.error('Error updating refund:', error)
    alert('Error updating refund')
  } finally {
    loading.value = false
  }
}

const deleteRefund = async (refund) => {
  if (!confirm('Are you sure you want to delete this refund?')) {
    return
  }
  
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/expenses/refunds/${refund.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      loadRefunds()
    } else {
      const error = await response.json()
      alert(error.message || 'Error deleting refund')
    }
  } catch (error) {
    console.error('Error deleting refund:', error)
    alert('Error deleting refund')
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
  loadRefunds()
  loadAccounts()
  loadExpenses()
})
</script> 