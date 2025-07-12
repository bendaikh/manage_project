<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Expenses Overview</h2>
        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Expense
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search expenses..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @keyup.enter="loadExpenses"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="filters.category_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="loadExpenses"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>
          <select
            v-model="filters.account_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @change="loadExpenses"
          >
            <option value="">All Accounts</option>
            <option v-for="account in accounts" :key="account.id" :value="account.id">
              {{ account.name }}
            </option>
          </select>
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
              Description
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Amount
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Category
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Account
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Refundable
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ expense.title }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">
              {{ formatCurrency(expense.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ expense.category ? expense.category.name : 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ expense.account ? expense.account.name : 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(expense.date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="expense.refundable ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                {{ expense.refundable ? 'Yes' : 'No' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(expense)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteExpense(expense)"
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Expense</h3>
          <form @submit.prevent="createExpense">
            <div class="mb-4">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <input
                v-model="createForm.description"
                type="text"
                id="description"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
              <input
                v-model="createForm.amount"
                type="number"
                step="0.01"
                min="0"
                id="amount"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select
                v-model="createForm.expense_category_id"
                id="category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label for="account" class="block text-sm font-medium text-gray-700 mb-2">Account</label>
              <select
                v-model="createForm.account_id"
                id="account"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Account</option>
                <option v-for="account in accounts" :key="account.id" :value="account.id">
                  {{ account.name }}
                </option>
              </select>
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
            <div class="mb-4">
              <label class="flex items-center">
                <input
                  v-model="createForm.refundable"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700">Refundable</span>
              </label>
            </div>
            <div class="mb-4">
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
              <textarea
                v-model="createForm.notes"
                id="notes"
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
                {{ loading ? 'Creating...' : 'Create Expense' }}
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
          <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Expense</h3>
          <form @submit.prevent="updateExpense">
            <div class="mb-4">
              <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <input
                v-model="editForm.description"
                type="text"
                id="edit-description"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="edit-amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
              <input
                v-model="editForm.amount"
                type="number"
                step="0.01"
                min="0"
                id="edit-amount"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="mb-4">
              <label for="edit-category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select
                v-model="editForm.expense_category_id"
                id="edit-category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label for="edit-account" class="block text-sm font-medium text-gray-700 mb-2">Account</label>
              <select
                v-model="editForm.account_id"
                id="edit-account"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select Account</option>
                <option v-for="account in accounts" :key="account.id" :value="account.id">
                  {{ account.name }}
                </option>
              </select>
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
            <div class="mb-4">
              <label class="flex items-center">
                <input
                  v-model="editForm.refundable"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700">Refundable</span>
              </label>
            </div>
            <div class="mb-4">
              <label for="edit-notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
              <textarea
                v-model="editForm.notes"
                id="edit-notes"
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
                {{ loading ? 'Updating...' : 'Update Expense' }}
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

const expenses = ref([])
const categories = ref([])
const accounts = ref([])
const pagination = ref(null)
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const filters = ref({
  search: '',
  category_id: '',
  account_id: '',
  date_from: '',
  date_to: '',
  amount_min: '',
  amount_max: ''
})
const createForm = ref({
  description: '',
  amount: '',
  expense_category_id: '',
  account_id: '',
  date: '',
  refundable: false,
  notes: ''
})
const editForm = ref({
  id: null,
  description: '',
  amount: '',
  expense_category_id: '',
  account_id: '',
  date: '',
  refundable: false,
  notes: ''
})

const loadExpenses = async (page = 1) => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    params.append('page', page)
    
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.category_id) params.append('category_id', filters.value.category_id)
    if (filters.value.account_id) params.append('account_id', filters.value.account_id)
    if (filters.value.date_from) params.append('date_from', filters.value.date_from)
    if (filters.value.date_to) params.append('date_to', filters.value.date_to)
    if (filters.value.amount_min) params.append('amount_min', filters.value.amount_min)
    if (filters.value.amount_max) params.append('amount_max', filters.value.amount_max)
    
    const response = await fetch(`/accounting/expenses?${params.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      expenses.value = data.data
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
    console.error('Error loading expenses:', error)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await fetch('/accounting/expenses/categories', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      categories.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading categories:', error)
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

const loadPage = (page) => {
  loadExpenses(page)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    account_id: '',
    date_from: '',
    date_to: '',
    amount_min: '',
    amount_max: ''
  }
  loadExpenses()
}

const openCreateModal = () => {
  createForm.value = {
    description: '',
    amount: '',
    expense_category_id: '',
    account_id: '',
    date: new Date().toISOString().split('T')[0],
    refundable: false,
    notes: ''
  }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    description: '',
    amount: '',
    expense_category_id: '',
    account_id: '',
    date: '',
    refundable: false,
    notes: ''
  }
}

const openEditModal = (expense) => {
  editForm.value = {
    id: expense.id,
    description: expense.title,
    amount: expense.amount,
    expense_category_id: expense.expense_category_id,
    account_id: expense.account_id,
    date: expense.date,
    refundable: expense.refundable,
    notes: expense.notes || ''
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = {
    id: null,
    description: '',
    amount: '',
    expense_category_id: '',
    account_id: '',
    date: '',
    refundable: false,
    notes: ''
  }
}

const createExpense = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch('/accounting/expenses', {
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
      loadExpenses()
    } else {
      const error = await response.json()
      alert(error.message || 'Error creating expense')
    }
  } catch (error) {
    console.error('Error creating expense:', error)
    alert('Error creating expense')
  } finally {
    loading.value = false
  }
}

const updateExpense = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/expenses/${editForm.value.id}`, {
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
      loadExpenses()
    } else {
      const error = await response.json()
      alert(error.message || 'Error updating expense')
    }
  } catch (error) {
    console.error('Error updating expense:', error)
    alert('Error updating expense')
  } finally {
    loading.value = false
  }
}

const deleteExpense = async (expense) => {
  if (!confirm('Are you sure you want to delete this expense?')) {
    return
  }
  
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/expenses/${expense.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      loadExpenses()
    } else {
      const error = await response.json()
      alert(error.message || 'Error deleting expense')
    }
  } catch (error) {
    console.error('Error deleting expense:', error)
    alert('Error deleting expense')
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
  loadExpenses()
  loadCategories()
  loadAccounts()
})
</script> 