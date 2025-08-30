<template>
  <div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Warehouse Transfers</h2>
        <button @click="showCreateForm = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          Create Transfer
        </button>
      </div>
      
      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search transfers..." 
                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_transit">In Transit</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        
        <div class="flex items-end">
          <button @click="applyFilters" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            Apply Filters
          </button>
        </div>
      </div>
      
      <!-- Transfers Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Transfer Details
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                From Warehouse
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                To Warehouse
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Quantity
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transfer in transfers" :key="transfer.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">#{{ transfer.id }}</div>
                <div class="text-sm text-gray-500">{{ transfer.notes || 'No notes' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transfer.from_warehouse?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transfer.to_warehouse?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ transfer.stock?.title }}</div>
                <div class="text-sm text-gray-500">{{ transfer.stock?.reference }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ transfer.quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(transfer.status)"
                      class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ transfer.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(transfer.transfer_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="editTransfer(transfer)" class="text-blue-600 hover:text-blue-900 mr-3">
                  Edit
                </button>
                <button @click="deleteTransfer(transfer)" class="text-red-600 hover:text-red-900">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="transfers.length === 0">
              <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                No transfers found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination.total_pages > 1" class="mt-6 flex justify-center">
        <nav class="flex space-x-2">
          <button @click="changePage(pagination.current_page - 1)" 
                  :disabled="pagination.current_page === 1"
                  class="px-3 py-2 border rounded-md hover:bg-gray-50 disabled:opacity-50">
            Previous
          </button>
          <span class="px-3 py-2 border rounded-md">
            Page {{ pagination.current_page }} of {{ pagination.total_pages }}
          </span>
          <button @click="changePage(pagination.current_page + 1)" 
                  :disabled="pagination.current_page === pagination.total_pages"
                  class="px-3 py-2 border rounded-md hover:bg-gray-50 disabled:opacity-50">
            Next
          </button>
        </nav>
      </div>
    </div>

    <!-- Create/Edit Transfer Modal -->
    <div v-if="showCreateForm || showEditForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showEditForm ? 'Edit Transfer' : 'Create Transfer' }}
        </h3>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">From Warehouse</label>
              <select v-model="form.from_warehouse_id" required
                      @change="loadStocksFromWarehouse"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Warehouse</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }} - {{ warehouse.location }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">To Warehouse</label>
              <select v-model="form.to_warehouse_id" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Warehouse</option>
                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                  {{ warehouse.name }} - {{ warehouse.location }}
                </option>
              </select>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
            <select v-model="form.stock_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Select Product</option>
              <option v-for="stock in availableStocks" :key="stock.id" :value="stock.id">
                {{ stock.title }} ({{ stock.reference }}) - Available: {{ stock.warehouse_quantity }}
              </option>
            </select>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
              <input v-model="form.quantity" type="number" min="1" 
                     :max="availableStocks.find(s => s.id == form.stock_id)?.warehouse_quantity || 1" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Transfer Date</label>
              <input v-model="form.transfer_date" type="date" required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
          
          <div v-if="showEditForm">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="form.status" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="pending">Pending</option>
              <option value="in_transit">In Transit</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea v-model="form.notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" @click="closeForm" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              {{ showEditForm ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const transfers = ref([])
const warehouses = ref([])
const availableStocks = ref([])
const pagination = ref({
  current_page: 1,
  total_pages: 1
})

const filters = ref({
  search: '',
  status: ''
})

const showCreateForm = ref(false)
const showEditForm = ref(false)
const editingTransfer = ref(null)

const form = ref({
  from_warehouse_id: '',
  to_warehouse_id: '',
  stock_id: '',
  quantity: 1,
  transfer_date: '',
  status: 'pending',
  notes: ''
})

const fetchTransfers = async (page = 1) => {
  try {
    const params = new URLSearchParams({ page })
    
    if (filters.value.search) {
      params.append('search', filters.value.search)
    }
    if (filters.value.status) {
      params.append('status', filters.value.status)
    }
    
    const response = await fetch(`/warehouse-transfers?${params}`)
    if (response.ok) {
      const data = await response.json()
      transfers.value = data.data || []
      pagination.value = {
        current_page: data.current_page || 1,
        total_pages: data.last_page || 1
      }
    }
  } catch (error) {
    console.error('Error fetching transfers:', error)
  }
}

const fetchWarehouses = async () => {
  try {
    const response = await fetch('/warehouses')
    if (response.ok) {
      const data = await response.json()
      warehouses.value = data.data || []
    }
  } catch (error) {
    console.error('Error fetching warehouses:', error)
  }
}

const loadStocksFromWarehouse = async () => {
  if (!form.value.from_warehouse_id) {
    availableStocks.value = []
    return
  }
  
  try {
    const response = await fetch(`/warehouse-transfers/stocks-by-warehouse?warehouse_id=${form.value.from_warehouse_id}`)
    if (response.ok) {
      const data = await response.json()
      availableStocks.value = data
    }
  } catch (error) {
    console.error('Error fetching stocks:', error)
  }
}

const applyFilters = () => {
  fetchTransfers(1)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.total_pages) {
    fetchTransfers(page)
  }
}

const editTransfer = (transfer) => {
  editingTransfer.value = transfer
  form.value = {
    from_warehouse_id: transfer.from_warehouse_id,
    to_warehouse_id: transfer.to_warehouse_id,
    stock_id: transfer.stock_id,
    quantity: transfer.quantity,
    transfer_date: transfer.transfer_date,
    status: transfer.status,
    notes: transfer.notes || ''
  }
  showEditForm.value = true
  loadStocksFromWarehouse()
}

const deleteTransfer = async (transfer) => {
  if (!confirm(`Are you sure you want to delete transfer #${transfer.id}?`)) {
    return
  }
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch(`/warehouse-transfers/${transfer.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      alert('Transfer deleted successfully!')
      fetchTransfers(pagination.value.current_page)
    } else {
      const error = await response.json()
      alert('Error: ' + (error.message || 'Failed to delete transfer'))
    }
  } catch (error) {
    console.error('Error deleting transfer:', error)
    alert('Error deleting transfer')
  }
}

const submitForm = async () => {
  // Validate quantity against available stock
  const selectedStock = availableStocks.value.find(stock => stock.id == form.value.stock_id)
  if (selectedStock && form.value.quantity > selectedStock.warehouse_quantity) {
    alert(`Cannot transfer ${form.value.quantity} units. Only ${selectedStock.warehouse_quantity} units available in this warehouse.`)
    return
  }
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const url = showEditForm.value ? `/warehouse-transfers/${editingTransfer.value.id}` : '/warehouse-transfers'
    const method = showEditForm.value ? 'PUT' : 'POST'
    
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      alert(showEditForm.value ? 'Transfer updated successfully!' : 'Transfer created successfully!')
      closeForm()
      fetchTransfers(pagination.value.current_page)
    } else {
      const error = await response.json()
      alert('Error: ' + (error.message || 'Failed to save transfer'))
    }
  } catch (error) {
    console.error('Error saving transfer:', error)
    alert('Error saving transfer')
  }
}

const closeForm = () => {
  showCreateForm.value = false
  showEditForm.value = false
  editingTransfer.value = null
  form.value = {
    from_warehouse_id: '',
    to_warehouse_id: '',
    stock_id: '',
    quantity: 1,
    transfer_date: '',
    status: 'pending',
    notes: ''
  }
  availableStocks.value = []
}

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'in_transit': return 'bg-blue-100 text-blue-800'
    case 'completed': return 'bg-green-100 text-green-800'
    case 'cancelled': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-CA')
}

onMounted(() => {
  fetchTransfers()
  fetchWarehouses()
})
</script>
