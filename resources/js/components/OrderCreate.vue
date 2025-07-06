<template>
  <div class="flex flex-col items-center justify-center min-h-[80vh] bg-gray-50">
    <div class="w-full max-w-2xl bg-white rounded-xl shadow p-8 mt-8">
      <div class="bg-violet-800 rounded-t-lg -mt-8 mb-8 px-6 py-3">
        <h2 class="text-white text-lg font-bold">Create New Order</h2>
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
          <input v-model.number="form.quantity" type="number" min="1" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Client Name</label>
            <input v-model="form.client_name" type="text" class="w-full border rounded px-3 py-2" placeholder="Enter client name" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Price</label>
            <div class="flex items-center">
              <span class="text-gray-400 mr-1">$</span>
              <input v-model="form.price" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2" readonly />
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
        <div class="flex justify-end space-x-2 mt-6">
          <button type="button" @click="resetForm" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Reset</button>
          <button type="submit" class="px-6 py-2 bg-violet-800 text-white rounded hover:bg-violet-900 font-semibold">Create Order</button>
        </div>
      </form>
      <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>
      <div v-if="success" class="text-green-600 mt-4">Order created successfully!</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const products = ref([])
const sellers = ref([])
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
  form.value.price = selected ? selected.selling_price : 0
}

const resetForm = () => {
  form.value = { seller: '', product_id: '', quantity: 1, client_name: '', price: 0, client_address: '', zone: '', client_phone: '', comment: '' }
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

onMounted(() => { fetchProducts(); fetchSellers(); })
</script> 