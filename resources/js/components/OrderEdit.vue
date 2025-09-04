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
          <input 
            v-model="form.price" 
            type="number" 
            min="0" 
            step="0.01" 
            :class="[
              'w-full border rounded px-3 py-2',
              canEditPrice ? '' : 'bg-gray-100'
            ]"
            :readonly="!canEditPrice"
          />
        </div>
        <div v-if="!canEditPrice" class="text-xs text-gray-500 mt-1">Price can only be modified by superadmin in delivery section.</div>
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
    <!-- Warehouse Selection Modal for confirmation flow -->
    <div v-if="showWarehouseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeWarehouseModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Select Warehouse for Order Confirmation</h3>
        <p class="text-sm text-gray-600 mb-4">Please select which warehouse this order will take products from:</p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Warehouse</label>
          <select v-model="selectedWarehouseId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select a warehouse</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
              {{ warehouse.name }} - {{ warehouse.location }}
            </option>
          </select>
        </div>
        <div class="flex justify-end space-x-3">
          <button @click="closeWarehouseModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="confirmOrderWithWarehouse" :disabled="!selectedWarehouseId" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
            Confirm Order
          </button>
        </div>
      </div>
    </div>
    
    <!-- Confirmation Date Modal -->
    <div v-if="showConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeConfirmationModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Confirm Order on Date</h3>
        <p class="text-sm text-gray-600 mb-4">Please set the confirmation date and add any comments:</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Date Confirmed with Client</label>
          <input v-model="confirmationForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
          <textarea v-model="confirmationForm.comment" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any comments about the confirmation..."></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closeConfirmationModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="submitConfirmation" :disabled="!confirmationForm.date" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
            Confirm Order
          </button>
        </div>
      </div>
    </div>
    
    <!-- Postponed Modal -->
    <div v-if="showPostponedModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closePostponedModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Postpone Order</h3>
        <p class="text-sm text-gray-600 mb-4">Please set the postponed date and add any comments:</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Postponed Date</label>
          <input v-model="postponedForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
          <textarea v-model="postponedForm.comment" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any comments about the postponement..."></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closePostponedModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="submitPostponed" :disabled="!postponedForm.date" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 disabled:opacity-50">
            Postpone Order
          </button>
        </div>
      </div>
    </div>
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

// Check if user is superadmin
const isSuperadmin = computed(() => {
  const roles = window.Laravel?.user?.roles || []
  return roles.includes('superadmin') || roles.some(role => typeof role === 'object' && role.name === 'superadmin')
})

// Check if price can be edited (superadmin in delivery section)
const canEditPrice = computed(() => {
  return isSuperadmin.value && props.delivery
})

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
    // If in confirmation section and selected status is Confirmed, show warehouse selection flow
    const selectedStatus = allStatuses.value.find(s => s.id === form.value.order_status_id)
    const selectedStatusName = selectedStatus?.name || ''
    if (props.confirmation && selectedStatusName === 'Confirmed') {
      await openWarehouseSelection()
      return
    }
    
    // If status is "Confirmed on Date" in confirmation section, show confirmation modal
    if (selectedStatusName === 'Confirmed on Date' && props.confirmation) {
      openConfirmationModal()
      return
    }
    
    // If status is "Postponed", show postponed modal
    if (selectedStatusName === 'Postponed') {
      openPostponedModal()
      return
    }

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

// Warehouse selection modal state and logic for confirmation -> Confirmed
const showWarehouseModal = ref(false)
const warehouses = ref([])
const selectedWarehouseId = ref('')

// Confirmation modal refs
const showConfirmationModal = ref(false)
const confirmationForm = ref({
  date: '',
  comment: ''
})

// Postponed modal refs
const showPostponedModal = ref(false)
const postponedForm = ref({
  date: '',
  comment: ''
})

const openWarehouseSelection = async () => {
  selectedWarehouseId.value = ''
  try {
    const res = await fetch('/warehouses')
    if (res.ok) {
      const data = await res.json()
      warehouses.value = data.data || []
    }
  } catch (e) {
    // swallow; error will surface on confirm attempt
  }
  showWarehouseModal.value = true
}

const closeWarehouseModal = () => {
  showWarehouseModal.value = false
  selectedWarehouseId.value = ''
}

const confirmOrderWithWarehouse = async () => {
  if (!selectedWarehouseId.value) return
  error.value = ''
  success.value = false
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/orders/${orderId}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({
        status: 'Confirmed',
        warehouse_id: selectedWarehouseId.value
      })
    })
    if (!res.ok) {
      let message = 'Failed to confirm order'
      try {
        const data = await res.json()
        message = data.message || message
      } catch (_) {
        const text = await res.text()
        message = text || message
      }
      throw new Error(message)
    }
    await res.json()
    success.value = true
    showWarehouseModal.value = false
    emit('updated')
  } catch (e) {
    error.value = e.message || 'Failed to confirm order'
  } finally {
    selectedWarehouseId.value = ''
  }
}

// Confirmation modal methods
const openConfirmationModal = () => {
  confirmationForm.value = {
    date: '',
    comment: ''
  }
  showConfirmationModal.value = true
}

const closeConfirmationModal = () => {
  showConfirmationModal.value = false
  confirmationForm.value = {
    date: '',
    comment: ''
  }
}

const submitConfirmation = async () => {
  if (!confirmationForm.value.date) return
  
  error.value = ''
  success.value = false
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    
    // Update the form with confirmation data
    const updatedForm = {
      ...form.value,
      confirmed_date: confirmationForm.value.date,
      confirmation_comment: confirmationForm.value.comment
    }
    
    const response = await fetch(`/orders/${orderId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(updatedForm),
      credentials: 'same-origin'
    })
    
    if (!response.ok) {
      const data = await response.json()
      error.value = data.message || 'Failed to confirm order on date.'
      return
    }
    
    success.value = true
    showConfirmationModal.value = false
    emit('updated')
  } catch (e) {
    error.value = 'Failed to confirm order on date.'
  }
}

// Postponed modal methods
const openPostponedModal = () => {
  postponedForm.value = {
    date: '',
    comment: ''
  }
  showPostponedModal.value = true
}

const closePostponedModal = () => {
  showPostponedModal.value = false
  postponedForm.value = {
    date: '',
    comment: ''
  }
}

const submitPostponed = async () => {
  if (!postponedForm.value.date) return
  
  error.value = ''
  success.value = false
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    
    // Update the form with postponed data
    const updatedForm = {
      ...form.value,
      postponed_date: postponedForm.value.date,
      postponed_comment: postponedForm.value.comment
    }
    
    const response = await fetch(`/orders/${orderId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(updatedForm),
      credentials: 'same-origin'
    })
    
    if (!response.ok) {
      const data = await response.json()
      error.value = data.message || 'Failed to postpone order.'
      return
    }
    
    success.value = true
    showPostponedModal.value = false
    emit('updated')
  } catch (e) {
    error.value = 'Failed to postpone order.'
  }
}
</script>