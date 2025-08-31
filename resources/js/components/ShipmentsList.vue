<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Shipments Management</h2>
      <button v-if="isSeller || isAdmin" @click="openCreate" class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm lg:text-base">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Create Shipment
      </button>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">Filters</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Keyword Search -->
        <div>
          <label class="block text-sm font-medium mb-1">Search</label>
          <input v-model="filters.search" placeholder="Search title, reference, description..." 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <!-- Validation Status -->
        <div>
          <label class="block text-sm font-medium mb-1">Validation Status</label>
          <select v-model="filters.validated" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All</option>
            <option value="1">Validated</option>
            <option value="0">Not Validated</option>
          </select>
        </div>
        
        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium mb-1">Date From</label>
          <input v-model="filters.date_from" type="date" class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Date To</label>
          <input v-model="filters.date_to" type="date" class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <!-- Quantity Range -->
        <div>
          <label class="block text-sm font-medium mb-1">Quantity Min</label>
          <input v-model.number="filters.quantity_min" type="number" min="0" 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Quantity Max</label>
          <input v-model.number="filters.quantity_max" type="number" min="0" 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <!-- Fees Range -->
        <div>
          <label class="block text-sm font-medium mb-1">Fees Min</label>
          <input v-model.number="filters.fees_min" type="number" step="0.01" min="0" 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Fees Max</label>
          <input v-model.number="filters.fees_max" type="number" step="0.01" min="0" 
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

    <!-- Shipments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Link</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photos</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipping Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipping Cost</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transport Cost</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customs Fee</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validation</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="shipment in shipments" :key="shipment.id" 
                :class="!shipment.validated ? 'bg-orange-50' : 'hover:bg-gray-50'">
              <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ shipment.title }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ shipment.reference }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ shipment.quantity }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <span v-if="shipment.seller" class="font-medium text-gray-900">
                  {{ shipment.seller.name }}
                </span>
                <span v-else class="text-gray-400">N/A</span>
              </td>
              <td class="px-4 py-4 text-sm text-gray-500 max-w-xs truncate">
                {{ shipment.description || 'N/A' }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <a :href="shipment.link" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                  View Link
                </a>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <img v-if="shipment.photo" :src="storageUrl(shipment.photo)" 
                     class="w-12 h-12 object-cover rounded cursor-pointer" 
                     @click="viewPhoto(shipment.photo)" />
                <span v-else class="text-gray-400">No photo</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(shipment.shipment_date) }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(shipment.shipping_cost || 0) }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(shipment.transport_cost || 0) }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatCurrency(shipment.customs_fees || 0) }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span :class="shipment.validated ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ shipment.validated ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                  <button v-if="canValidate" @click="toggleValidate(shipment)" 
                          :class="shipment.validated ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ shipment.validated ? 'Revoke' : 'Validate' }}
                  </button>
                  <button v-if="canEdit(shipment)" @click="openEdit(shipment)" 
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                  </button>
                  <button v-if="canDelete(shipment)" @click="deleteShipment(shipment)" 
                          class="flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-red-600 hover:text-red-800 hover:bg-red-50 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                  </button>
                  <!-- Debug info - remove this in production -->
                  <span v-if="!canEdit(shipment) && !canDelete(shipment) && !canValidate" class="text-xs text-gray-400">
                    No actions available
                  </span>
                </div>
              </td>
            </tr>
            <tr v-if="shipments.length === 0">
              <td colspan="13" class="text-center py-8 text-gray-400">
                No shipments found.
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

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white w-full max-w-2xl rounded-lg p-6 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">{{ editMode ? 'Edit Shipment' : 'Create Shipment' }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Stock Selection -->
          <div v-if="stocks.length > 0">
            <label class="block text-sm font-medium mb-1">Select from Existing Stock (Optional)</label>
            <select v-model="form.stock_id" class="w-full border rounded px-3 py-2 text-sm">
              <option value="">Create new shipment</option>
              <option v-for="stock in stocks" :key="stock.id" :value="stock.id">
                {{ stock.title }} (Qty: {{ stock.quantity }})
              </option>
            </select>
          </div>
          
                     <!-- Seller Selection -->
           <div>
             <label class="block text-sm font-medium mb-1">Seller *</label>
             <!-- Debug info -->
             <div v-if="!isSeller" class="text-xs text-gray-500 mb-1">
               Debug: {{ sellers.length }} sellers loaded, isSeller: {{ isSeller }}, currentUser: {{ currentUser.name }}
             </div>
             <!-- For admins/managers/agents: show dropdown to select seller -->
             <select v-if="!isSeller" v-model="form.seller_id" required class="w-full border rounded px-3 py-2 text-sm">
               <option value="">Select a seller ({{ sellers.length }} available)</option>
               <option v-for="seller in sellers" :key="seller.id" :value="seller.id">
                 {{ seller.name }} ({{ seller.email }})
               </option>
             </select>
             <!-- For sellers: show read-only field with their info -->
             <div v-else class="w-full border rounded px-3 py-2 text-sm bg-gray-50 text-gray-700">
               {{ currentUser.name }} ({{ currentUser.email }}) - You
             </div>
           </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              <label class="block text-sm font-medium mb-1">Quantity *</label>
              <input v-model.number="form.quantity" required type="number" min="1" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Product Link *</label>
              <input v-model="form.link" required type="url" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Shipping Date *</label>
              <input v-model="form.shipment_date" required type="date" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Customs Clearance Fee</label>
              <input v-model.number="form.customs_fees" type="number" step="0.01" min="0" 
                     class="w-full border rounded px-3 py-2 text-sm" />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea v-model="form.description" rows="3" 
                      class="w-full border rounded px-3 py-2 text-sm"></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Product Photos</label>
            <input @change="onPhotoChange" type="file" accept="image/*" 
                   class="w-full border rounded px-3 py-2 text-sm" />
            <p class="text-xs text-gray-500 mt-1">Upload product photos (max 4MB)</p>
          </div>
          
          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="closeModal" 
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">
              Cancel
            </button>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
              {{ editMode ? 'Update Shipment' : 'Create Shipment' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Photo Modal -->
    <div v-if="showPhotoModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4" @click="closePhotoModal">
      <div class="max-w-2xl max-h-full">
        <img :src="storageUrl(selectedPhoto)" class="max-w-full max-h-full object-contain" />
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
const shipments = ref([])
const stocks = ref([])
const sellers = ref([])
const pagination = ref({ page: 1, total_pages: 1 })
const showModal = ref(false)
const showPhotoModal = ref(false)
const editMode = ref(false)
const editingShipmentId = ref(null)
const selectedPhoto = ref('')
const notification = ref({ show: false, message: '', type: 'success' })

// Current user data
const currentUser = ref({
  id: window.Laravel?.user?.id || null,
  name: window.Laravel?.user?.name || '',
  email: window.Laravel?.user?.email || ''
})

// Filters
const filters = ref({
  search: '',
  validated: '',
  date_from: '',
  date_to: '',
  quantity_min: null,
  quantity_max: null,
  fees_min: null,
  fees_max: null
})

// Form data
const form = ref({
  title: '',
  reference: '',
  quantity: 1,
  description: '',
  link: '',
  photo: null,
  shipment_date: '',
  customs_fees: '',
  stock_id: '',
  seller_id: ''
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
const canValidate = isAdmin || isAgent

// Helper functions
const canEdit = (s) => {
  // Admins and managers can edit any shipment
  if (isAdmin || roleNames.includes('manager')) return true
  // Sellers can only edit their own unvalidated shipments
  return isSeller && !s.validated && s.seller_id === window.Laravel?.user?.id
}

const canDelete = (s) => {
  // Admins and managers can delete any shipment
  if (isAdmin || roleNames.includes('manager')) return true
  // Sellers can only delete their own unvalidated shipments
  return isSeller && !s.validated && s.seller_id === window.Laravel?.user?.id
}
const storageUrl = (path) => path ? `/storage/${path}` : ''

const formatDate = (date) => {
  if (!date) return 'N/A'
  // Ensure consistent date formatting without timezone issues
  const d = new Date(date + 'T00:00:00') // Force local timezone
  return d.toLocaleDateString('en-CA') // Use YYYY-MM-DD format
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
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
const fetchShipments = async (page = 1) => {
  const params = new URLSearchParams({ page })
  
  // Add filters
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      params.append(key, value)
    }
  })
  
  // Add cache-busting parameter to prevent caching
  params.append('_t', Date.now())
  
  const res = await fetch(`/shipments?${params}`, { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (!res.ok) { 
    shipments.value = []
    return 
  }
  
  const data = await res.json()
  shipments.value = data.data || []
  pagination.value = { 
    page: data.current_page || 1, 
    total_pages: data.last_page || 1 
  }
}

const fetchStocks = async () => {
  const res = await fetch('/shipments/stocks', { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    const data = await res.json()
    stocks.value = data || []
  }
}

const fetchSellers = async () => {
  console.log('Fetching sellers...')
  try {
    // Get CSRF token
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const res = await fetch('/users/sellers', { 
      headers: { 
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf || ''
      }, 
      credentials: 'same-origin' 
    })
    
    console.log('Sellers response status:', res.status)
    console.log('Sellers response headers:', res.headers)
    
    if (res.ok) {
      const data = await res.json()
      console.log('Sellers data:', data)
      sellers.value = data || []
    } else {
      console.error('Failed to fetch sellers:', res.status, res.statusText)
      const errorText = await res.text()
      console.error('Error response:', errorText)
    }
  } catch (error) {
    console.error('Exception while fetching sellers:', error)
  }
}

// Event handlers
const applyFilters = () => {
  fetchShipments(1)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    validated: '',
    date_from: '',
    date_to: '',
    quantity_min: null,
    quantity_max: null,
    fees_min: null,
    fees_max: null
  }
  fetchShipments(1)
}

const changePage = (p) => {
  if (p < 1 || p > pagination.value.total_pages) return
  fetchShipments(p)
}

const openCreate = () => {
  editMode.value = false
  editingShipmentId.value = null
  form.value = {
    title: '',
    reference: '',
    quantity: 1,
    description: '',
    link: '',
    photo: null,
    shipment_date: '',
    customs_fees: '',
    stock_id: '',
    seller_id: isSeller ? currentUser.value.id : ''
  }
  showModal.value = true
}

const openEdit = (s) => {
  editMode.value = true
  editingShipmentId.value = s.id
  
  // Format the date properly for the HTML date input (YYYY-MM-DD)
  // Ensure no timezone issues by using the date string directly
  const formattedDate = s.shipment_date ? s.shipment_date : ''
  
  form.value = { 
    ...s, 
    shipment_date: formattedDate,
    photo: null,
    stock_id: '',
    seller_id: s.seller_id || (isSeller ? currentUser.value.id : '')
  }
  showModal.value = true
}

const closeModal = () => { 
  showModal.value = false 
}

const closePhotoModal = () => {
  showPhotoModal.value = false
  selectedPhoto.value = ''
}

const onPhotoChange = (e) => { 
  form.value.photo = e.target.files[0] 
}

const viewPhoto = (photo) => {
  selectedPhoto.value = photo
  showPhotoModal.value = true
}

const showNotification = (message, type = 'success') => {
  notification.value = { show: true, message, type }
  setTimeout(() => {
    hideNotification()
  }, 5000) // Auto hide after 5 seconds
}

const hideNotification = () => {
  notification.value.show = false
}

const submitForm = async () => {
  const fd = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    // Always include required fields, even if empty
    if (k === 'shipment_date' || k === 'title' || k === 'reference' || k === 'quantity' || k === 'link' || k === 'seller_id') {
      fd.append(k, v || '')
    }
    // Include other fields only if they have values
    else if (v !== null && v !== undefined && v !== '') {
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
  
  let url = '/shipments'
  if (editMode.value) {
    fd.append('_method', 'PUT')
    url = `/shipments/${editingShipmentId.value}`
  }
  
  const res = await fetch(url, opts)
  if (res.ok) {
    const message = editMode.value ? 'Shipment updated successfully!' : 'Shipment created successfully!'
    showNotification(message, 'success')
    closeModal()
    
    // Force a complete refresh of the shipments list
    shipments.value = [] // Clear the current data first
    await fetchShipments(1) // Always go to first page to ensure we see the updated data
  } else {
    const error = await res.json()
    showNotification(error.message || 'An error occurred', 'error')
  }
}

const deleteShipment = async (s) => {
  if (!confirm(`Are you sure you want to delete shipment "${s.title}"? This action cannot be undone.`)) return
  
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(`/shipments/${s.id}`, { 
    method: 'DELETE', 
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    showNotification('Shipment deleted successfully!', 'success')
    shipments.value = [] // Clear the current data first
    fetchShipments(1) // Refresh from first page
  } else {
    const error = await res.json()
    showNotification(error.message || 'Failed to delete shipment', 'error')
  }
}

const toggleValidate = async (s) => {
  const action = s.validated ? 'revoke validation from' : 'validate'
  
  if (!s.validated) {
    // Show warehouse selection popup for validation
    showWarehouseSelection(s)
    return
  }
  
  if (!confirm(`Are you sure you want to ${action} shipment "${s.title}"?`)) return
  
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(`/shipments/${s.id}/validate`, { 
    method: 'POST', 
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    const message = s.validated ? 'Shipment validation revoked!' : 'Shipment validated successfully!'
    showNotification(message, 'success')
    shipments.value = [] // Clear the current data first
    fetchShipments(1) // Refresh from first page
  } else {
    const error = await res.json()
    showNotification(error.message || 'An error occurred', 'error')
  }
}

const showWarehouseSelection = async (shipment) => {
  try {
    const response = await fetch('/shipments/warehouses')
    if (response.ok) {
      const warehouses = await response.json()
      
      // Create a modal-like dialog
      const modal = document.createElement('div')
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50'
      modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Validate Shipment</h3>
          <p class="text-sm text-gray-600 mb-4">Complete validation for shipment: "${shipment.title}"</p>
          
          <!-- Warehouse Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Warehouse *</label>
            <div class="space-y-2 max-h-40 overflow-y-auto border rounded-lg p-3">
              ${warehouses.map(w => `
                <label class="flex items-center p-2 border rounded-lg hover:bg-gray-50 cursor-pointer">
                  <input type="radio" name="warehouse" value="${w.id}" class="mr-3" required>
                  <div>
                    <div class="font-medium">${w.name}</div>
                    <div class="text-sm text-gray-500">${w.location}</div>
                  </div>
                </label>
              `).join('')}
            </div>
          </div>
          
          <!-- Cost Inputs -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Cost (China to Africa) *</label>
              <input type="number" name="shipping_cost" step="0.01" min="0" required 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="0.00">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Transport Cost (Airport to Warehouse) *</label>
              <input type="number" name="transport_cost" step="0.01" min="0" required 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                     placeholder="0.00">
            </div>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50" onclick="this.closest('.fixed').remove()">
              Cancel
            </button>
            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="window.validateWithSelectedWarehouse(${shipment.id})">
              Validate Shipment
            </button>
          </div>
        </div>
      `
      
      document.body.appendChild(modal)
      
      // Add global function to handle validation
      window.validateWithSelectedWarehouse = async (shipmentId) => {
        const selectedWarehouse = document.querySelector('input[name="warehouse"]:checked')
        const shippingCost = document.querySelector('input[name="shipping_cost"]').value
        const transportCost = document.querySelector('input[name="transport_cost"]').value
        
        if (!selectedWarehouse) {
          alert('Please select a warehouse')
          return
        }
        
        if (!shippingCost || !transportCost) {
          alert('Please enter both shipping and transport costs')
          return
        }
        
        const warehouseId = parseInt(selectedWarehouse.value)
        modal.remove()
        await validateShipmentWithWarehouse(shipment, warehouseId, parseFloat(shippingCost), parseFloat(transportCost))
      }
    }
  } catch (error) {
    console.error('Error fetching warehouses:', error)
    showNotification('Error fetching warehouses', 'error')
  }
}

const validateShipmentWithWarehouse = async (s, warehouseId, shippingCost, transportCost) => {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(`/shipments/${s.id}/validate`, { 
    method: 'POST', 
    headers: { 
      'X-CSRF-TOKEN': csrf, 
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ 
      warehouse_id: warehouseId,
      shipping_cost: shippingCost,
      transport_cost: transportCost
    }),
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    showNotification('Shipment validated successfully!', 'success')
    shipments.value = [] // Clear the current data first
    fetchShipments(1) // Refresh from first page
  } else {
    const error = await res.json()
    showNotification(error.message || 'An error occurred', 'error')
  }
}

// Watch for stock selection changes
watch(() => form.value.stock_id, (newStockId) => {
  if (newStockId) {
    const selectedStock = stocks.value.find(s => s.id == newStockId)
    if (selectedStock) {
      form.value.title = selectedStock.title
      form.value.description = selectedStock.description
      // Keep the quantity from the form as it might be different
    }
  }
})

// Initialize
onMounted(() => {
  fetchShipments()
  fetchStocks()
  fetchSellers()
})
</script> 