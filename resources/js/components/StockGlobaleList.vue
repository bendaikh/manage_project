<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Stock Globale - Global Inventory Management</h2>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Products</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_products || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">In Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.in_stock || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Low Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.low_stock || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-red-100 rounded-lg">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Out of Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.out_of_stock || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">Global Stock Filters</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Search</label>
          <input v-model="filters.search" placeholder="Search title, reference, barcode..." 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Product</label>
          <select v-model="filters.product_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Products</option>
            <option v-for="product in availableProducts" :key="product.id" :value="product.id">
              {{ product.name }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Warehouse</label>
          <select v-model="filters.warehouse_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Warehouses</option>
            <option v-for="warehouse in availableWarehouses" :key="warehouse.id" :value="warehouse.id">
              {{ warehouse.name }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Seller</label>
          <select v-model="filters.seller_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Sellers</option>
            <option v-for="seller in availableSellers" :key="seller.id" :value="seller.id">
              {{ seller.name }}
            </option>
          </select>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="filters.status" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Status</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Warehouse Location</label>
          <input v-model="filters.warehouse_location" placeholder="Filter by location..." 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Price Range</label>
          <div class="flex gap-2">
            <input v-model.number="filters.min_price" type="number" placeholder="Min" 
                   class="w-full border rounded px-3 py-2 text-sm" />
            <input v-model.number="filters.max_price" type="number" placeholder="Max" 
                   class="w-full border rounded px-3 py-2 text-sm" />
          </div>
        </div>
      </div>
      
      <div class="flex gap-2 mt-4">
        <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
          Apply Filters
        </button>
        <button @click="clearFilters" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
          Clear Filters
        </button>
        <button @click="exportData" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
          Export Data
        </button>
      </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initial Qty</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="stock in stocks" :key="stock.id" 
                :class="getStatusRowClass(stock.status)">
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img v-if="stock.photo" :src="storageUrl(stock.photo)" 
                       class="w-10 h-10 object-cover rounded mr-3" />
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ stock.product ? stock.product.name : stock.title }}
                      <span v-if="stock.product" class="text-xs text-gray-500 ml-2">(Linked)</span>
                    </div>
                    <div class="text-sm text-gray-500">{{ stock.description || 'No description' }}</div>
                    <div v-if="stock.product" class="text-xs text-blue-600">
                      SKU: {{ stock.product.sku || 'N/A' }} | Category: {{ stock.product.category || 'N/A' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ stock.reference }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                    <span class="text-xs font-medium text-gray-600">
                      {{ stock.seller ? stock.seller.name.charAt(0).toUpperCase() : 'N/A' }}
                    </span>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ stock.seller ? stock.seller.name : 'N/A' }}</div>
                    <div class="text-xs text-gray-500">{{ stock.seller ? stock.seller.email : '' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div v-if="stock.warehouse">
                  <div class="text-sm font-medium text-gray-900">{{ stock.warehouse.name }}</div>
                  <div class="text-xs text-gray-500">{{ stock.warehouse_location || 'No location' }}</div>
                </div>
                <span v-else class="text-gray-400">No warehouse</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ stock.initial_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ stock.remaining_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span :class="getStatusClass(stock.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ formatStatus(stock.status) }}
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div v-if="stock.purchase_price || stock.selling_price">
                  <div v-if="stock.purchase_price">Cost: {{ formatCurrency(stock.purchase_price) }}</div>
                  <div v-if="stock.selling_price">Price: {{ formatCurrency(stock.selling_price) }}</div>
                </div>
                <span v-else class="text-gray-400">Not set</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>{{ formatDate(stock.last_updated_at) }}</div>
                <div class="text-xs text-gray-400">by {{ stock.last_updated_by }}</div>
              </td>
            </tr>
            <tr v-if="stocks.length === 0">
              <td colspan="9" class="text-center py-8 text-gray-400">
                No stock found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <nav v-if="pagination.total_pages > 1" class="flex justify-center mt-6">
      <ul class="inline-flex">
        <li>
          <button class="px-3 py-2 border rounded-l hover:bg-gray-50" 
                  :disabled="pagination.page === 1" 
                  @click="changePage(pagination.page - 1)">
            &laquo; Previous
          </button>
        </li>
        <li v-for="page in getPageNumbers()" :key="page">
          <button class="px-3 py-2 border-t border-b hover:bg-gray-50" 
                  :class="page === pagination.page ? 'bg-blue-600 text-white' : ''" 
                  @click="changePage(page)">
            {{ page }}
          </button>
        </li>
        <li>
          <button class="px-3 py-2 border rounded-r hover:bg-gray-50" 
                  :disabled="pagination.page === pagination.total_pages" 
                  @click="changePage(pagination.page + 1)">
            Next &raquo;
          </button>
        </li>
      </ul>
    </nav>


    <!-- Notification Toast -->
    <div v-if="notification.show" 
         :class="[
           'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300',
           notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
         ]">
      <div class="flex items-center gap-2">
        <svg v-if="notification.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ notification.message }}</span>
        <button @click="hideNotification" class="ml-auto text-white hover:text-gray-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

// Reactive data
const stocks = ref([])
const statistics = ref({})
const pagination = ref({ page: 1, total_pages: 1 })
const notification = ref({ show: false, message: '', type: 'success' })

// Filter options
const availableProducts = ref([])
const availableWarehouses = ref([])
const availableSellers = ref([])

// Enhanced filters
const filters = ref({
  search: '',
  product_id: '',
  warehouse_id: '',
  seller_id: '',
  status: '',
  warehouse_location: '',
  min_price: '',
  max_price: ''
})


// User roles
const rawRoles = window.Laravel?.user?.roles || []
const roleNames = rawRoles.map(r => {
  const n = typeof r === 'string' ? r : (r.name || '')
  return n.toLowerCase()
})
const isSeller = roleNames.includes('seller')
const isAdmin = roleNames.includes('admin') || roleNames.includes('superadmin')
const isAgent = roleNames.includes('agent')

// Helper functions

const storageUrl = (path) => path ? `/storage/${path}` : ''

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-CA')
}

const formatCurrency = (amount) => {
  if (!amount) return 'N/A'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatStatus = (status) => {
  const statusMap = {
    'in_stock': 'In Stock',
    'low_stock': 'Low Stock',
    'out_of_stock': 'Out of Stock'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    'in_stock': 'bg-green-100 text-green-800',
    'low_stock': 'bg-yellow-100 text-yellow-800',
    'out_of_stock': 'bg-red-100 text-red-800'
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

const getStatusRowClass = (status) => {
  if (status === 'low_stock') return 'bg-yellow-50'
  if (status === 'out_of_stock') return 'bg-red-50'
  return 'hover:bg-gray-50'
}

const getPageNumbers = () => {
  const pages = []
  const total = pagination.value.total_pages
  const current = pagination.value.page
  
  for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
    pages.push(i)
  }
  return pages
}

// API calls
const fetchStocks = async (page = 1) => {
  const params = new URLSearchParams({ page })
  
  // Add filters
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      params.append(key, value)
    }
  })
  
  const res = await fetch(`/stocks-globale?${params}`, { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (!res.ok) { 
    stocks.value = []
    return 
  }
  
  const data = await res.json()
  stocks.value = data.data || []
  pagination.value = { 
    page: data.current_page || 1, 
    total_pages: data.last_page || 1 
  }
}

const fetchStatistics = async () => {
  const res = await fetch('/stocks-globale/statistics', { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    const data = await res.json()
    statistics.value = data
  }
}

const fetchFilterOptions = async () => {
  // Fetch products
  const productsRes = await fetch('/stocks-globale/filter-options/products', {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (productsRes.ok) {
    availableProducts.value = await productsRes.json()
  }

  // Fetch warehouses
  const warehousesRes = await fetch('/stocks-globale/filter-options/warehouses', {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (warehousesRes.ok) {
    availableWarehouses.value = await warehousesRes.json()
  }

  // Fetch sellers
  const sellersRes = await fetch('/stocks-globale/filter-options/sellers', {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (sellersRes.ok) {
    availableSellers.value = await sellersRes.json()
  }
}

// Event handlers
const applyFilters = () => {
  fetchStocks(1)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    product_id: '',
    warehouse_id: '',
    seller_id: '',
    status: '',
    warehouse_location: '',
    min_price: '',
    max_price: ''
  }
  fetchStocks(1)
}

const exportData = async () => {
  const params = new URLSearchParams()
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      params.append(key, value)
    }
  })
  
  const res = await fetch(`/stocks-globale/export?${params}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  
  if (res.ok) {
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `stock-globale-${new Date().toISOString().split('T')[0]}.csv`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
    showNotification('Data exported successfully!', 'success')
  } else {
    showNotification('Failed to export data', 'error')
  }
}

const changePage = (p) => {
  if (p < 1 || p > pagination.value.total_pages) return
  fetchStocks(p)
}


const showNotification = (message, type = 'success') => {
  notification.value = { show: true, message, type }
  setTimeout(() => {
    hideNotification()
  }, 5000)
}

const hideNotification = () => {
  notification.value.show = false
}


// Initialize
onMounted(() => {
  fetchStocks()
  fetchStatistics()
  fetchFilterOptions()
})
</script>
