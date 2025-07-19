<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-bold">Edit Order #{{ orderId }}</h2>
      <button @click="$emit('cancel')" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Seller</label>
        <input v-model="form.seller" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Product</label>
        <select v-model="form.product_id" class="w-full border rounded px-3 py-2 bg-gray-100" disabled>
          <option v-for="product in products" :key="product.id" :value="product.id">
            {{ product.name }} - {{ product.selling_price.toLocaleString() }}
          </option>
        </select>
        <div class="text-xs text-gray-500 mt-1">Product cannot be changed for existing orders.</div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Quantity</label>
        <input v-model.number="form.quantity" type="number" min="1" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Price</label>
        <div class="flex items-center">
          <span class="text-gray-400 mr-1">$</span>
          <input v-model="form.price" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2 bg-gray-100" readonly />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Client Name</label>
        <input v-model="form.client_name" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Client Address</label>
        <input v-model="form.client_address" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Zone (Optional)</label>
        <input v-model="form.zone" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Client Phone (Optional)</label>
        <input v-model="form.client_phone" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Agent Username</label>
        <input v-model="form.agent" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Order Status</label>
        <select v-model="form.order_status_id" class="w-full border rounded px-3 py-2">
          <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Comment</label>
        <textarea v-model="form.comment" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 mt-4">
        <button type="button" @click="$emit('cancel')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-6 py-2 bg-violet-700 text-white rounded hover:bg-violet-800 font-semibold text-sm">Update Order</button>
      </div>
    </form>
    <div v-if="error" class="text-red-600 mt-3 text-sm">{{ error }}</div>
    <div v-if="success" class="text-green-600 mt-3 text-sm">Order updated successfully!</div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
const props = defineProps({ 
  order: Object, 
  products: Array, 
  confirmation: Boolean, 
  delivery: Boolean 
})
const emit = defineEmits(['cancel', 'updated'])

const orderId = props.order?.id
const form = ref({ ...props.order })
const products = ref(props.products || [])
const error = ref('')
const success = ref(false)
const allStatuses = ref([])

// Status configuration for different sections
const statusConfig = {
  all: ['New Order'],
  confirmation: ['New Order', 'Confirmed', 'Confirmed on Date', 'Unreachable', 'Postponed', 'Cancelled', 'Blacklisted', 'Out of Stock'],
  delivery: ['Processing', 'Shipped', 'Unreachable', 'Postponed', 'Cancelled', 'Delivered', 'Out of Stock']
}

// Filter statuses based on section
const statuses = computed(() => {
  let allowedStatusNames = []
  if (props.confirmation) {
    allowedStatusNames = statusConfig.confirmation
  } else if (props.delivery) {
    allowedStatusNames = statusConfig.delivery
  } else {
    allowedStatusNames = statusConfig.all
  }
  
  return allStatuses.value.filter(status => allowedStatusNames.includes(status.name))
})

const fetchStatuses = async () => {
  const res = await fetch('/order-statuses/list')
  const data = await res.json()
  allStatuses.value = data
}

const submitForm = async () => {
  error.value = ''
  success.value = false
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    const response = await fetch(`/orders/${orderId}`, {
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
      error.value = data.message || 'Failed to update order.'
      return
    }
    success.value = true
    emit('updated')
  } catch (e) {
    error.value = 'Failed to update order.'
  }
}

onMounted(() => {
  fetchStatuses()
})
</script> 