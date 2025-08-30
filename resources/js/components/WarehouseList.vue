<template>
  <div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Manage Warehouses</h2>
        <button @click="$emit('add-warehouse')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          Add Warehouse
        </button>
      </div>
      
      <!-- Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search warehouses..." 
                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select v-model="filters.status" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        
        <div class="flex items-end">
          <button @click="applyFilters" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            Apply Filters
          </button>
        </div>
      </div>
      
      <!-- Warehouses Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="warehouse in warehouses" :key="warehouse.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ warehouse.name }}</div>
                <div class="text-sm text-gray-500">{{ warehouse.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ warehouse.location }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ warehouse.contact_person }}</div>
                <div class="text-sm text-gray-500">{{ warehouse.phone }}</div>
                <div class="text-sm text-gray-500">{{ warehouse.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="warehouse.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ warehouse.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="viewProducts(warehouse)" class="text-green-600 hover:text-green-900 mr-3" title="View Products">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
                <button @click="editWarehouse(warehouse)" class="text-blue-600 hover:text-blue-900 mr-3">
                  Edit
                </button>
                <button @click="deleteWarehouse(warehouse)" class="text-red-600 hover:text-red-900">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="warehouses.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                No warehouses found.
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const warehouses = ref([])
const pagination = ref({
  current_page: 1,
  total_pages: 1
})

const filters = ref({
  search: '',
  status: ''
})

const fetchWarehouses = async (page = 1) => {
  try {
    const params = new URLSearchParams({ page })
    
    if (filters.value.search) {
      params.append('search', filters.value.search)
    }
    if (filters.value.status) {
      params.append('status', filters.value.status)
    }
    
    const response = await fetch(`/warehouses?${params}`)
    if (response.ok) {
      const data = await response.json()
      warehouses.value = data.data || []
      pagination.value = {
        current_page: data.current_page || 1,
        total_pages: data.last_page || 1
      }
    }
  } catch (error) {
    console.error('Error fetching warehouses:', error)
  }
}

const applyFilters = () => {
  fetchWarehouses(1)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.total_pages) {
    fetchWarehouses(page)
  }
}

const editWarehouse = (warehouse) => {
  // TODO: Implement edit functionality
  console.log('Edit warehouse:', warehouse)
}

const viewProducts = async (warehouse) => {
  try {
    const response = await fetch(`/warehouses/${warehouse.id}/products`)
    if (response.ok) {
      const data = await response.json()
      showProductsModal(warehouse, data.stocks)
    }
  } catch (error) {
    console.error('Error fetching warehouse products:', error)
    alert('Error fetching warehouse products')
  }
}

const showProductsModal = (warehouse, stocks) => {
  const modal = document.createElement('div')
  modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
  modal.innerHTML = `
    <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Products in ${warehouse.name}</h3>
        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="this.closest('.fixed').remove()">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <div class="mb-4 text-sm text-gray-600">
        <p><strong>Location:</strong> ${warehouse.location}</p>
        <p><strong>Total Products:</strong> ${stocks.length}</p>
      </div>
      
      ${stocks.length > 0 ? `
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity in Warehouse</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              ${stocks.map(stock => `
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${stock.title}</div>
                    <div class="text-sm text-gray-500">${stock.description || 'No description'}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stock.reference}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stock.warehouse_quantity || 0}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${getStockStatusClass(stock.status)}">
                      ${stock.status}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${stock.seller?.name || 'N/A'}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      ` : `
        <div class="text-center py-8 text-gray-500">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
          <p class="mt-1 text-sm text-gray-500">This warehouse has no products yet.</p>
        </div>
      `}
      
      <div class="mt-6 flex justify-end">
        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50" onclick="this.closest('.fixed').remove()">
          Close
        </button>
      </div>
    </div>
  `
  
  document.body.appendChild(modal)
}

const getStockStatusClass = (status) => {
  switch (status) {
    case 'in_stock': return 'bg-green-100 text-green-800'
    case 'low_stock': return 'bg-yellow-100 text-yellow-800'
    case 'out_of_stock': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const deleteWarehouse = async (warehouse) => {
  if (!confirm(`Are you sure you want to delete warehouse "${warehouse.name}"?`)) {
    return
  }
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch(`/warehouses/${warehouse.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      alert('Warehouse deleted successfully!')
      fetchWarehouses(pagination.value.current_page)
    } else {
      const error = await response.json()
      alert('Error: ' + (error.message || 'Failed to delete warehouse'))
    }
  } catch (error) {
    console.error('Error deleting warehouse:', error)
    alert('Error deleting warehouse')
  }
}

onMounted(() => {
  fetchWarehouses()
})

const emit = defineEmits(['add-warehouse'])
</script>
