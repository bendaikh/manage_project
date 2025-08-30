<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Inventory Management</h2>
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

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">Filters</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Search</label>
          <input v-model="filters.search" placeholder="Search title, reference, barcode..." 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
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
      </div>
      
      <div class="flex gap-2 mt-4">
        <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
          Apply Filters
        </button>
        <button @click="clearFilters" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
          Clear Filters
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
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initial Qty</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Progress</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.barcode || 'N/A' }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ stock.initial_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ stock.remaining_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.today_delivered_quantity || 0 }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.damaged_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.today_in_progress_quantity || 0 }}</td>
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
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.warehouse_location || 'N/A' }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>{{ formatDate(stock.last_updated_at) }}</div>
                <div class="text-xs text-gray-400">by {{ stock.last_updated_by }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                  <button v-if="canEdit(stock)" @click="openEdit(stock)" 
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                  </button>
                  <button v-if="canEdit(stock)" @click="openQuantityUpdate(stock)" 
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-green-600 hover:text-green-800 hover:bg-green-50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Update Qty
                  </button>
                  <button v-if="canEdit(stock) && stock.product_id" @click="syncWithProduct(stock)" 
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-purple-600 hover:text-purple-800 hover:bg-purple-50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Sync
                  </button>
                  <button v-if="canDelete(stock)" @click="deleteStock(stock)" 
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-red-600 hover:text-red-800 hover:bg-red-50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="stocks.length === 0">
              <td colspan="13" class="text-center py-8 text-gray-400">
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white w-full max-w-4xl rounded-lg p-6 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">{{ editMode ? 'Edit Stock' : 'Add Stock' }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Product Link (Optional)</label>
              <div class="relative">
                <input v-model="productSearchQuery" 
                       @focus="showProductSearch = true"
                       @input="searchProducts"
                       placeholder="Search products to link..." 
                       class="w-full border rounded px-3 py-2 text-sm" />
                <div v-if="showProductSearch && availableProducts.length > 0" 
                     class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                  <div v-for="product in availableProducts" :key="product.id"
                       @click="selectProduct(product)"
                       class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                    <div class="font-medium">{{ product.name }}</div>
                    <div class="text-gray-500 text-xs">SKU: {{ product.sku || 'N/A' }} | Category: {{ product.category || 'N/A' }}</div>
                  </div>
                </div>
              </div>
              <div v-if="form.product_id" class="mt-2 p-2 bg-green-50 rounded text-sm">
                <span class="text-green-700">✓ Product linked</span>
                <button @click="clearProductLink" class="ml-2 text-red-600 hover:text-red-800 text-xs">Remove</button>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Title *</label>
              <input v-model="form.title" required type="text" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Reference *</label>
              <input v-model="form.reference" required type="text" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Barcode</label>
              <input v-model="form.barcode" type="text" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Initial Quantity *</label>
              <input v-model.number="form.initial_quantity" required type="number" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Purchase Price</label>
              <input v-model.number="form.purchase_price" type="number" step="0.01" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Selling Price</label>
              <input v-model.number="form.selling_price" type="number" step="0.01" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Warehouse Location</label>
              <input v-model="form.warehouse_location" type="text" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Product Link</label>
              <input v-model="form.product_link" type="url" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea v-model="form.description" rows="3" 
                      class="w-full border rounded px-3 py-2 text-sm"></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Product Photo</label>
            <input @change="onPhotoChange" type="file" accept="image/*" 
                   class="w-full border rounded px-3 py-2 text-sm" />
            <p class="text-xs text-gray-500 mt-1">Upload product photo (max 4MB)</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Notes</label>
            <textarea v-model="form.notes" rows="2" 
                      class="w-full border rounded px-3 py-2 text-sm"></textarea>
          </div>
          
          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="closeModal" 
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">
              Cancel
            </button>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
              {{ editMode ? 'Update Stock' : 'Create Stock' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Quantity Update Modal -->
    <div v-if="showQuantityModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeQuantityModal">
      <div class="bg-white w-full max-w-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Update Quantities</h3>
          <button @click="closeQuantityModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="submitQuantityUpdate" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Product</label>
            <p class="text-sm text-gray-600">{{ selectedStock?.title }}</p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Delivered Quantity</label>
              <input v-model.number="quantityForm.delivered_quantity" type="number" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Damaged Quantity</label>
              <input v-model.number="quantityForm.damaged_quantity" type="number" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">In Progress Quantity</label>
              <input v-model.number="quantityForm.in_progress_quantity" type="number" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Notes</label>
            <textarea v-model="quantityForm.notes" rows="2" 
                      class="w-full border rounded px-3 py-2 text-sm" placeholder="Reason for update..."></textarea>
          </div>
          
          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="closeQuantityModal" 
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">
              Cancel
            </button>
            <button type="submit" 
                    class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
              Update Quantities
            </button>
          </div>
        </form>
      </div>
    </div>

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
const showModal = ref(false)
const showQuantityModal = ref(false)
const editMode = ref(false)
const editingStockId = ref(null)
const selectedStock = ref(null)
const notification = ref({ show: false, message: '', type: 'success' })

// Filters
const filters = ref({
  search: '',
  status: '',
  warehouse_location: ''
})

// Form data
const form = ref({
  product_id: '',
  title: '',
  reference: '',
  barcode: '',
  initial_quantity: 0,
  description: '',
  purchase_price: '',
  selling_price: '',
  warehouse_location: '',
  product_link: '',
  photo: null,
  notes: ''
})

// Available products for linking
const availableProducts = ref([])
const showProductSearch = ref(false)
const productSearchQuery = ref('')

// Quantity update form
const quantityForm = ref({
  delivered_quantity: 0,
  damaged_quantity: 0,
  in_progress_quantity: 0,
  notes: ''
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
const canEdit = (stock) => {
  if (isAdmin || roleNames.includes('manager')) return true
  return isSeller && stock.seller_id === window.Laravel?.user?.id
}

const canDelete = (stock) => {
  if (isAdmin || roleNames.includes('manager')) return true
  return isSeller && stock.seller_id === window.Laravel?.user?.id
}

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
  
  const res = await fetch(`/stocks?${params}`, { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (!res.ok) { 
    stocks.value = []
    return 
  }
  
  const data = await res.json()
  console.log('Stocks API response:', data)
  stocks.value = data.data || []
  console.log('Stocks data after assignment:', stocks.value)
  pagination.value = { 
    page: data.current_page || 1, 
    total_pages: data.last_page || 1 
  }
  
  // If statistics are still 0, calculate from stocks data
  if (statistics.value.total_products === 0 && stocks.value.length > 0) {
    console.log('Statistics are 0, calculating from stocks data')
    calculateStatisticsFromStocks()
  }
}

const searchProducts = async () => {
  if (productSearchQuery.value.length < 2) {
    availableProducts.value = []
    return
  }
  
  const params = new URLSearchParams({ search: productSearchQuery.value })
  const res = await fetch(`/stocks/products/available?${params}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  
  if (res.ok) {
    const data = await res.json()
    availableProducts.value = data
  }
}

const selectProduct = (product) => {
  form.value.product_id = product.id
  form.value.title = product.name
  productSearchQuery.value = product.name
  showProductSearch.value = false
  availableProducts.value = []
}

const clearProductLink = () => {
  form.value.product_id = ''
  productSearchQuery.value = ''
  availableProducts.value = []
}

const fetchStatistics = async () => {
  console.log('Fetching statistics...')
  const res = await fetch('/stocks/statistics', { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  console.log('Statistics response status:', res.status)
  
  if (res.ok) {
    const data = await res.json()
    console.log('Statistics data:', data)
    statistics.value = data
  } else {
    console.error('Statistics API error:', res.status, res.statusText)
    const errorText = await res.text()
    console.error('Error response:', errorText)
    
    // Fallback: calculate statistics from stocks data
    console.log('Using fallback statistics calculation')
    calculateStatisticsFromStocks()
  }
}

const calculateStatisticsFromStocks = () => {
  if (stocks.value.length === 0) return
  
  const stats = {
    total_products: stocks.value.length,
    in_stock: stocks.value.filter(s => s.status === 'in_stock').length,
    low_stock: stocks.value.filter(s => s.status === 'low_stock').length,
    out_of_stock: stocks.value.filter(s => s.status === 'out_of_stock').length,
    total_initial_quantity: stocks.value.reduce((sum, s) => sum + (s.initial_quantity || 0), 0),
    total_remaining_quantity: stocks.value.reduce((sum, s) => sum + (s.remaining_quantity || 0), 0),
    total_delivered_quantity_today: stocks.value.reduce((sum, s) => sum + (s.today_delivered_quantity || 0), 0),
    total_in_progress_quantity_today: stocks.value.reduce((sum, s) => sum + (s.today_in_progress_quantity || 0), 0),
    total_damaged_quantity: stocks.value.reduce((sum, s) => sum + (s.damaged_quantity || 0), 0),
  }
  
  console.log('Fallback statistics:', stats)
  statistics.value = stats
}

// Event handlers
const applyFilters = () => {
  fetchStocks(1)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    warehouse_location: ''
  }
  fetchStocks(1)
}

const changePage = (p) => {
  if (p < 1 || p > pagination.value.total_pages) return
  fetchStocks(p)
}

const openCreate = () => {
  editMode.value = false
  editingStockId.value = null
  form.value = {
    product_id: '',
    title: '',
    reference: '',
    barcode: '',
    initial_quantity: 0,
    description: '',
    purchase_price: '',
    selling_price: '',
    warehouse_location: '',
    product_link: '',
    photo: null,
    notes: ''
  }
  showModal.value = true
}

const openEdit = (stock) => {
  editMode.value = true
  editingStockId.value = stock.id
  form.value = { 
    ...stock, 
    photo: null
  }
  
  // Set product search query if product is linked
  if (stock.product) {
    productSearchQuery.value = stock.product.name
  }
  
  showModal.value = true
}

const openQuantityUpdate = (stock) => {
  selectedStock.value = stock
  quantityForm.value = {
    delivered_quantity: stock.delivered_quantity || 0,
    damaged_quantity: stock.damaged_quantity || 0,
    in_progress_quantity: stock.in_progress_quantity || 0,
    notes: ''
  }
  showQuantityModal.value = true
}

const closeModal = () => { 
  showModal.value = false 
}

const closeQuantityModal = () => {
  showQuantityModal.value = false
  selectedStock.value = null
}

const onPhotoChange = (e) => { 
  form.value.photo = e.target.files[0] 
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

const submitForm = async () => {
  const fd = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (v !== null && v !== undefined && v !== '') {
      fd.append(k, v)
    }
  })
  
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const opts = { 
    method: 'POST', 
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, 
    body: fd, 
    credentials: 'same-origin' 
  }
  
  let url = '/stocks'
  if (editMode.value) {
    fd.append('_method', 'PUT')
    url = `/stocks/${editingStockId.value}`
  }
  
  const res = await fetch(url, opts)
  if (res.ok) {
    const message = editMode.value ? 'Stock updated successfully!' : 'Stock created successfully!'
    showNotification(message, 'success')
    closeModal()
    stocks.value = []
    await fetchStocks(1)
    await fetchStatistics()
  } else {
    const error = await res.json()
    showNotification(error.message || 'An error occurred', 'error')
  }
}

const submitQuantityUpdate = async () => {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(`/stocks/${selectedStock.value.id}/quantities`, { 
    method: 'PATCH', 
    headers: { 
      'X-CSRF-TOKEN': csrf, 
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }, 
    body: JSON.stringify(quantityForm.value),
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    showNotification('Quantities updated successfully!', 'success')
    closeQuantityModal()
    stocks.value = []
    await fetchStocks(1)
    await fetchStatistics()
  } else {
    const error = await res.json()
    showNotification(error.message || 'An error occurred', 'error')
  }
}

const deleteStock = async (stock) => {
  if (!confirm(`Are you sure you want to delete stock "${stock.title}"? This action cannot be undone.`)) return
  
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(`/stocks/${stock.id}`, { 
    method: 'DELETE', 
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    showNotification('Stock deleted successfully!', 'success')
    stocks.value = []
    fetchStocks(1)
    fetchStatistics()
  } else {
    const error = await res.json()
    showNotification(error.message || 'Failed to delete stock', 'error')
  }
}

const syncWithProduct = async (stock) => {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(`/stocks/${stock.id}/sync-product`, { 
    method: 'POST', 
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    showNotification('Stock synced with product successfully!', 'success')
    stocks.value = []
    fetchStocks(1)
  } else {
    const error = await res.json()
    showNotification(error.message || 'Failed to sync stock', 'error')
  }
}

// Initialize
onMounted(() => {
  console.log('StockList component mounted')
  console.log('Current user roles:', roleNames)
  console.log('Is admin:', isAdmin)
  console.log('Is seller:', isSeller)
  
  fetchStocks()
  fetchStatistics()
  
  // Close product search dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      showProductSearch.value = false
    }
  })
})
</script> 