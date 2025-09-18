<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8 mt-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">Add New Product</h2>
        <p class="text-gray-500">Create a new product in your inventory</p>
      </div>
      <button @click="$emit('back')" class="flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 text-gray-700">
        ← Back to Products
      </button>
    </div>
    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Basic Information -->
      <div>
        <h3 class="font-semibold text-lg mb-4">Basic Information</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Product Name *</label>
          <input v-model="form.name" type="text" maxlength="255" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Enter product name (supports all characters: ☀️ 中文 عع)" />
          <div class="text-xs text-gray-400 mt-1">{{ form.name.length }}/255 characters</div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">SKU</label>
          <input v-model="form.sku" type="text" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Leave blank to auto-generate" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Category</label>
          <select v-model="form.category" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">Select a category</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Supplier</label>
          <input v-model="form.supplier" type="text" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
        </div>
        <!-- Warehouse and Stock Quantity fields -->
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Warehouse & Stock Quantity *</label>
          <div v-for="(warehouseStock, index) in form.warehouse_stocks" :key="index" class="flex items-center gap-2 mb-2">
            <select v-model="warehouseStock.warehouse_id" required class="flex-1 border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
              <option value="">Select a warehouse</option>
              <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }} - {{ warehouse.location }}</option>
            </select>
            <input v-model.number="warehouseStock.quantity" type="number" min="0" required class="w-24 border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Qty" />
            <button v-if="form.warehouse_stocks.length > 1" @click="removeWarehouseStock(index)" type="button" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
            <button @click="addWarehouseStock" type="button" class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Company Product Checkbox -->
        <div class="mb-4">
          <label class="flex items-center">
            <input v-model="form.is_company_product" type="checkbox" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <span class="text-sm font-medium">Company Product</span>
          </label>
          <p class="text-xs text-gray-500 mt-1">Check this if this product should be available to multiple sellers</p>
        </div>
        
        <!-- Seller field - Single select (when company product is NOT checked) -->
        <div class="mb-4" v-if="!isSeller && !form.is_company_product">
          <label class="block text-sm font-medium mb-1">Seller *</label>
          <select v-model="form.seller_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">Select a seller</option>
            <option v-for="s in sellers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        
        <!-- Seller field - Custom Multiselect (when company product IS checked) -->
        <div class="mb-4" v-if="!isSeller && form.is_company_product">
          <label class="block text-sm font-medium mb-1">Assigned Sellers *</label>
          
          <!-- Selected Sellers as Tabs/Chips -->
          <div v-if="selectedSellers.length > 0" class="mb-3">
            <div class="flex flex-wrap gap-2">
              <div v-for="seller in selectedSellers" :key="seller.id" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                <span>{{ seller.name }}</span>
                <button @click="removeSeller(seller.id)" type="button" class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:text-blue-600 hover:bg-blue-200">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Add Seller Dropdown -->
          <div class="relative">
            <select v-model="selectedSellerToAdd" @change="addSeller" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
              <option value="">Select a seller to add</option>
              <option v-for="seller in availableSellers" :key="seller.id" :value="seller.id">{{ seller.name }}</option>
            </select>
          </div>
          
          <p class="text-xs text-gray-500 mt-1">Select sellers from the dropdown to add them. Click the X on tabs to remove them.</p>
        </div>
        
        <!-- Seller field - Readonly (when current user is a seller) -->
        <div class="mb-4" v-if="isSeller">
          <label class="block text-sm font-medium mb-1">Seller</label>
          <input type="text" :value="getCurrentUserName()" class="w-full border rounded px-3 py-2 bg-gray-100" disabled />
        </div>
        <div class="mb-4 grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Purchase Price *</label>
            <div class="flex items-center">
              <span class="text-gray-400 mr-1">$</span>
              <input v-model.number="form.purchase_price" type="number" min="0" step="0.01" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Selling Price *</label>
            <div class="flex items-center">
              <span class="text-gray-400 mr-1">$</span>
              <input v-model.number="form.selling_price" type="number" min="0" step="0.01" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="form.status" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="In Stock">In Stock</option>
            <option value="Out of Stock">Out of Stock</option>
            <option value="Discontinued">Discontinued</option>
          </select>
        </div>
      </div>
      <!-- Additional Details -->
      <div>
        <h3 class="font-semibold text-lg mb-4">Additional Details</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Image URL</label>
          <input v-model="form.image_url" type="url" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="https://example.com/image.jpg" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Video URL</label>
          <input v-model="form.video_url" type="url" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="https://example.com/video.mp4" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Video Duration</label>
          <input v-model="form.video_duration" type="text" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="e.g., 2:30" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Description</label>
          <textarea v-model="form.description" rows="4" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Enter product description..."></textarea>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded p-4 text-sm text-blue-900">
          <div class="flex items-center mb-2">
            <svg class="h-5 w-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
            <span class="font-semibold">Universal Character Support</span>
          </div>
          <div>This form supports all types of characters including:</div>
          <ul class="list-disc ml-6 mt-1">
            <li>Emojis and symbols: 🎉 ★ ♥ ☀ ☺ © ® ¼ ½ ¾ α β γ</li>
            <li>International languages: 中文 العربية français español русский</li>
            <li>Special characters: áâãäå çñ øßÞ</li>
            <li>Mathematical symbols: ± × ÷ ∞ ≤ ≥ ≠</li>
          </ul>
        </div>
      </div>
      <div class="col-span-1 md:col-span-2 flex justify-end space-x-2 mt-6">
        <button type="button" @click="$emit('back')" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 text-gray-700">Cancel</button>
        <button type="submit" class="px-6 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 font-semibold">Save Product</button>
      </div>
    </form>
    <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>
    <div v-if="success" class="text-green-600 mt-4">Product created successfully!</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const form = ref({
  name: '',
  sku: '',
  category: '',
  supplier: '',
  seller_id: '',
  warehouse_stocks: [{ warehouse_id: '', quantity: 1 }],
  is_company_product: false,
  assigned_sellers: [],
  purchase_price: '',
  selling_price: '',
  status: 'In Stock',
  image_url: '',
  video_url: '',
  video_duration: '',
  description: ''
})

const error = ref('')
const success = ref(false)
const categories = ref([])
const sellers = ref([])
const warehouses = ref([])
const isSeller = ref(false)

// Custom multiselect state
const selectedSellers = ref([])
const selectedSellerToAdd = ref('')

// Computed property for available sellers (excluding already selected ones)
const availableSellers = computed(() => {
  const selectedIds = selectedSellers.value.map(s => s.id)
  return sellers.value.filter(seller => !selectedIds.includes(seller.id))
})

// Methods for custom multiselect
const addSeller = () => {
  if (selectedSellerToAdd.value) {
    const seller = sellers.value.find(s => s.id == selectedSellerToAdd.value)
    if (seller && !selectedSellers.value.find(s => s.id === seller.id)) {
      selectedSellers.value.push(seller)
      // Update the form data
      form.value.assigned_sellers = selectedSellers.value.map(s => s.id)
    }
    selectedSellerToAdd.value = '' // Reset dropdown
  }
}

const removeSeller = (sellerId) => {
  selectedSellers.value = selectedSellers.value.filter(s => s.id !== sellerId)
  // Update the form data
  form.value.assigned_sellers = selectedSellers.value.map(s => s.id)
}

// Methods for managing warehouse stocks
const addWarehouseStock = () => {
  form.value.warehouse_stocks.push({ warehouse_id: '', quantity: 1 })
}

const removeWarehouseStock = (index) => {
  if (form.value.warehouse_stocks.length > 1) {
    form.value.warehouse_stocks.splice(index, 1)
  }
}

const fetchCategories = async () => {
  const res = await fetch('/categories', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) return
  const data = await res.json()
  categories.value = data.categories || []
}

const fetchWarehouses = async () => {
  const res = await fetch('/warehouses', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) return
  const data = await res.json()
  warehouses.value = data.data || []
}

onMounted(fetchCategories)
onMounted(fetchWarehouses)

// Fetch sellers only if needed
const fetchSellers = async () => {
  if (isSeller.value) return
  const res = await fetch('/users?role=seller', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) return
  const data = await res.json()
  sellers.value = data.users || []
}

const getCurrentUserName = () => {
  try {
    return window.Laravel?.user?.name || 'Current User'
  } catch (error) {
    console.warn('Could not access user name:', error)
    return 'Current User'
  }
}

const getCurrentUserId = () => {
  try {
    return window.Laravel?.user?.id || ''
  } catch (error) {
    console.warn('Could not access user id:', error)
    return ''
  }
}

onMounted(() => {
  // Safely check if user is a seller
  try {
    const userRoles = window.Laravel?.user?.roles
    isSeller.value = Array.isArray(userRoles) && userRoles.includes('seller')
  if (isSeller.value) {
    form.value.seller_id = getCurrentUserId()
  }
  } catch (error) {
    console.warn('Could not access user roles:', error)
    isSeller.value = false
  }
  
  fetchSellers()
})

const submitForm = async () => {
  error.value = ''
  success.value = false
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch('/products', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value),
      credentials: 'same-origin'
    })
    if (!response.ok) {
      const data = await response.json()
      error.value = data.message || 'Failed to create product.'
      return
    }
    success.value = true
    form.value = {
      name: '', sku: '', category: '', supplier: '', seller_id: isSeller.value ? getCurrentUserId() : '', warehouse_stocks: [{ warehouse_id: '', quantity: 1 }], is_company_product: false, assigned_sellers: [], purchase_price: '', selling_price: '', status: 'In Stock', image_url: '', video_url: '', video_duration: '', description: ''
    }
    // Reset custom multiselect state
    selectedSellers.value = []
    selectedSellerToAdd.value = ''
    // Optionally emit event to parent
    // emit('product-saved')
  } catch (e) {
    error.value = 'Failed to create product.'
  }
}
</script> 