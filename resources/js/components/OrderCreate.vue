<template>
  <div class="flex flex-col items-center justify-center min-h-[80vh] bg-gray-50 px-4 lg:px-0">
    <div class="w-full max-w-2xl bg-white rounded-xl shadow p-4 lg:p-8 mt-4 lg:mt-8">
      <div class="bg-violet-800 rounded-t-lg -mt-4 lg:-mt-8 mb-6 lg:mb-8 px-4 lg:px-6 py-3">
        <h2 class="text-white text-lg font-bold">Create New Order</h2>
      </div>
      
      <!-- Import Button -->
      <div class="mb-6 flex justify-end">
        <button 
          @click="showImportModal = true"
          class="px-3 lg:px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold flex items-center gap-2 text-sm lg:text-base"
        >
          <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <span class="hidden sm:inline">Import Sheet Orders</span>
          <span class="sm:hidden">Import</span>
        </button>
      </div>
      
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Seller</label>
          <select v-model="form.seller" class="w-full border rounded px-3 py-2">
            <option value="">Select a seller</option>
            <option v-for="s in sellers" :key="s.id" :value="s.name">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Product</label>
          <select v-model="form.product_id" @change="onProductChange" class="w-full border rounded px-3 py-2">
            <option value="">Select a product</option>
            <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Quantity</label>
          <input v-model.number="form.quantity" @input="onQuantityChange" type="number" min="1" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Client Name</label>
            <input v-model="form.client_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter client name" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Price</label>
            <div class="flex items-center">
              <span class="text-gray-400 mr-1">FCFA</span>
              <input v-model="form.price" @input="onPriceChange" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2" />
            </div>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Client Address</label>
          <input v-model="form.client_address" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter client address" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Zone (Optional)</label>
          <input v-model="form.zone" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter zone" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Client Phone (Optional)</label>
          <input v-model="form.client_phone" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter client phone number" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Comment</label>
          <textarea v-model="form.comment" class="w-full border rounded px-3 py-2" placeholder="Add any additional notes"></textarea>
        </div>
        <div class="flex flex-col sm:flex-row justify-end gap-2 mt-6">
          <button type="button" @click="resetForm" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm lg:text-base">Reset</button>
          <button type="submit" class="px-6 py-2 bg-violet-800 text-white rounded hover:bg-violet-900 font-semibold text-sm lg:text-base">Create Order</button>
        </div>
      </form>
      <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>
      <div v-if="success" class="text-green-600 mt-4">Order created successfully!</div>
    </div>
    
    <!-- Import Modal -->
    <OrderImportModal 
      :show="showImportModal" 
      @close="showImportModal = false"
      @orders-imported="handleOrdersImported"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import OrderImportModal from './OrderImportModal.vue'

const products = ref([])
const sellers = ref([])
const showImportModal = ref(false)
const form = ref({
  seller: '',
  product_id: '',
  quantity: 1,
  client_name: '',
  price: 0,
  client_address: '',
  zone: '',
  client_phone: '',
  comment: ''
})
const error = ref('')
const success = ref(false)
const unitPrice = ref(0)
const priceManuallyModified = ref(false)

const fetchProducts = async () => {
  const res = await fetch('/products/list')
  const data = await res.json()
  products.value = data.products || []
}

const fetchSellers = async () => {
  const res = await fetch('/users?role=seller', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) return
  const data = await res.json()
  sellers.value = data.users || []
}

const onProductChange = () => {
  const selected = products.value.find(p => p.id === form.value.product_id)
  if (selected) {
    unitPrice.value = selected.selling_price
    // Only update price if it hasn't been manually modified
    if (!priceManuallyModified.value) {
      form.value.price = unitPrice.value * form.value.quantity
    }
  } else {
    unitPrice.value = 0
    if (!priceManuallyModified.value) {
      form.value.price = 0
    }
  }
}

const onQuantityChange = () => {
  // Always update price based on current unit price when quantity changes
  if (unitPrice.value > 0) {
    form.value.price = unitPrice.value * form.value.quantity
  }
}

const onPriceChange = () => {
  // When user manually changes price, calculate the new unit price
  if (form.value.quantity > 0) {
    unitPrice.value = form.value.price / form.value.quantity
  }
  // Mark price as manually modified when user changes it
  priceManuallyModified.value = true
}

const resetForm = () => {
  form.value = { seller: '', product_id: '', quantity: 1, client_name: '', price: 0, client_address: '', zone: '', client_phone: '', comment: '' }
  unitPrice.value = 0
  priceManuallyModified.value = false
}

const submitForm = async () => {
  error.value = ''
  success.value = false
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    const response = await fetch('/orders', {
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
      error.value = data.message || 'Failed to create order.'
      return
    }
    success.value = true
    resetForm()
  } catch (e) {
    error.value = 'Failed to create order.'
  }
}

const handleOrdersImported = (count) => {
  success.value = true
  error.value = ''
  // You could also refresh the orders list if needed
  console.log(`${count} orders imported successfully`)
}

onMounted(() => { fetchProducts(); fetchSellers(); })
</script> 