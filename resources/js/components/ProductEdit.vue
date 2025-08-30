<template>
  <div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-8 mt-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">Edit Product</h2>
        <p class="text-gray-500">Update product information</p>
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
          <input v-model="form.name" type="text" maxlength="255" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Enter product name" />
          <div class="text-xs text-gray-400 mt-1">{{ form.name.length }}/255 characters</div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">SKU</label>
          <input v-model="form.sku" type="text" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Product SKU" />
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
        <!-- Warehouse field -->
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Warehouse *</label>
          <select v-model="form.warehouse_id" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">Select a warehouse</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }} - {{ warehouse.location }}</option>
          </select>
        </div>
        <!-- Seller field -->
        <div class="mb-4" v-if="!isSeller">
          <label class="block text-sm font-medium mb-1">Seller *</label>
          <select v-model="form.seller_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">Select a seller</option>
            <option v-for="s in sellers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="mb-4" v-else>
          <label class="block text-sm font-medium mb-1">Seller</label>
          <input type="text" :value="window.Laravel?.user?.name" class="w-full border rounded px-3 py-2 bg-gray-100" disabled />
        </div>
        <div class="mb-4 grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Purchase Price *</label>
            <div class="flex items-center">
              <span class="text-gray-400 mr-1">FCFA</span>
              <input v-model.number="form.purchase_price" type="number" min="0" step="0.01" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Selling Price *</label>
            <div class="flex items-center">
              <span class="text-gray-400 mr-1">FCFA</span>
              <input v-model.number="form.selling_price" type="number" min="0" step="0.01" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
            </div>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Stock Quantity *</label>
          <input v-model.number="form.stock_quantity" type="number" min="0" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="form.status" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="In Stock">In Stock</option>
            <option value="Low Stock">Low Stock</option>
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
          <input v-model="form.image_url" type="url" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
        </div>
        <div class="mb-4 grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Video URL</label>
            <input v-model="form.video_url" type="url" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Video Duration</label>
            <input v-model="form.video_duration" type="text" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="e.g. 2:30" />
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Description</label>
          <textarea v-model="form.description" maxlength="2000" rows="5" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" placeholder="Enter product description"></textarea>
          <div class="text-xs text-gray-400 mt-1">{{ form.description.length }}/2000 characters</div>
        </div>
      </div>
      <div class="col-span-1 md:col-span-2 flex justify-end space-x-2 mt-6">
        <button type="button" @click="$emit('back')" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 text-gray-700">Cancel</button>
        <button type="submit" class="px-6 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 font-semibold">Update Product</button>
      </div>
    </form>
    <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>
    <div v-if="success" class="text-green-600 mt-4">Product updated successfully!</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  productId: {
    type: [String, Number],
    required: true
  }
})

const form = ref({
  name: '',
  sku: '',
  category: '',
  supplier: '',
  seller_id: '',
  warehouse_id: '',
  purchase_price: '',
  selling_price: '',
  stock_quantity: 1,
  status: 'In Stock',
  image_url: '',
  video_url: '',
  video_duration: '',
  description: ''
})

const error = ref('')
const success = ref(false)
const categories = ref([])
const warehouses = ref([])
const sellers = ref([])
const isSeller = ref(false)

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

const fetchSellers = async () => {
  if (isSeller.value) return
  const res = await fetch('/users?role=seller', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) return
  const data = await res.json()
  sellers.value = data.users || []
}

const fetchProduct = async () => {
  try {
    const res = await fetch(`/products/${props.productId}`, { 
      headers: { 'Accept': 'application/json' }, 
      credentials: 'same-origin' 
    })
    if (!res.ok) {
      error.value = 'Failed to load product'
      return
    }
    const product = await res.json()
    form.value = {
      name: product.name || '',
      sku: product.sku || '',
      category: product.category || '',
      supplier: product.supplier || '',
      seller_id: product.seller_id || '',
      warehouse_id: product.warehouse_id || '',
      purchase_price: product.purchase_price || '',
      selling_price: product.selling_price || '',
      stock_quantity: product.stock_quantity || 1,
      status: product.status || 'In Stock',
      image_url: product.image_url || '',
      video_url: product.video_url || '',
      video_duration: product.video_duration || '',
      description: product.description || ''
    }
  } catch (e) {
    error.value = 'Failed to load product'
  }
}

onMounted(() => {
  isSeller.value = Array.isArray(window.Laravel?.user?.roles) && window.Laravel.user.roles.includes('seller')
  if (isSeller.value) {
    form.value.seller_id = window.Laravel?.user?.id || ''
  }
  fetchCategories()
  fetchWarehouses()
  fetchSellers()
  fetchProduct()
})

const submitForm = async () => {
  error.value = ''
  success.value = false
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch(`/products/${props.productId}`, {
      method: 'PUT',
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
      error.value = data.message || 'Failed to update product.'
      return
    }
    success.value = true
    // Emit event to parent to refresh the product list
    emit('product-updated')
  } catch (e) {
    error.value = 'Failed to update product.'
  }
}

const emit = defineEmits(['back', 'product-updated'])
</script> 