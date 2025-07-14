<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Order Management</h2>
      <button @click="$emit('create-order')" class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold text-sm lg:text-base">
        + Create Order
      </button>
    </div>
    <!-- Filter Orders -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap gap-2 mb-4">
        <button v-for="range in dateRanges" :key="range" @click="setDateRange(range)" class="px-2 lg:px-3 py-1 border rounded text-xs lg:text-sm" :class="{ 'bg-gray-200': filters.dateRange === range }">{{ range }}</button>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <input v-model="filters.search" @keyup.enter="fetchOrders" type="text" placeholder="Search by Order ID, Product, Client, Phone..." class="w-full border rounded px-3 py-2 text-sm lg:text-base" />
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
        <select v-model="filters.seller" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Sellers</option>
          <option v-for="seller in sellers" :key="seller" :value="seller">{{ seller }}</option>
        </select>
        <select v-model="filters.status" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Statuses</option>
          <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
        </select>
        <select v-model="filters.agent" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Agents</option>
          <option v-for="agent in agents" :key="agent" :value="agent">{{ agent }}</option>
        </select>
        <select v-model="filters.zone" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Zones</option>
          <option v-for="zone in zones" :key="zone" :value="zone">{{ zone }}</option>
        </select>
        <div class="flex flex-col sm:flex-row gap-2">
          <button @click="fetchOrders" class="px-3 lg:px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center justify-center text-sm lg:text-base">
            <svg class="h-4 w-4 lg:h-5 lg:w-5 mr-1 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4h13M8 12h13M8 20h13M3 6h.01M3 18h.01"/></svg>
            <span class="hidden sm:inline">Apply Filters</span>
            <span class="sm:hidden">Apply</span>
          </button>
          <button @click="clearFilters" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">Reset</button>
        </div>
      </div>
    </div>
    <!-- Action bar -->
    <div v-if="selectedIds.size" class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-blue-50 border border-blue-200 rounded p-3 mb-3 gap-3">
      <div class="text-sm lg:text-base">{{ selectedIds.size }} selected</div>
      <div class="flex flex-col sm:flex-row gap-2">
        <!-- Assignment button for superadmin -->
        <button v-if="isSuperadmin" @click="openAssignmentModal" class="px-3 lg:px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm flex items-center gap-1">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          Assign to Agent
        </button>
        <button @click="downloadDeliveryNote" class="px-3 lg:px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Download Delivery Note</button>
        <button @click="downloadInvoices" class="px-3 lg:px-4 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 text-sm">Download Invoices</button>
      </div>
    </div>
    
    <!-- Invoice Delivery Button (only for delivery page) -->
    <div v-if="props.delivery" class="flex justify-end mb-4">
      <button @click="generateDeliveryInvoice" class="px-4 lg:px-6 py-2 lg:py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-semibold flex items-center gap-2 text-sm lg:text-base">
        <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="hidden sm:inline">Invoice Delivery</span>
        <span class="sm:hidden">Invoice</span>
      </button>
    </div>
    
    <!-- Orders Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-2 lg:px-3 py-2"><input type="checkbox" :checked="allSelected" @change="toggleSelectAll"></th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">ORDER ID</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden lg:table-cell">SELLER</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">PRODUCT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">PRICE</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden md:table-cell">CLIENT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden lg:table-cell">AGENT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">STATUS</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden lg:table-cell">DATE</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden xl:table-cell">ZONE</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden xl:table-cell">COMMENT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="border-b">
            <td class="px-2 lg:px-3 py-2 text-center"><input type="checkbox" :value="order.id" v-model="checked"></td>
            <td class="px-2 lg:px-3 py-2 font-mono text-xs">
              <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ order.id }}</span>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden lg:table-cell">{{ order.seller }}</td>
            <td class="px-2 lg:px-3 py-2 flex items-center gap-2">
              <img v-if="order.product && order.product.image_url" :src="order.product.image_url" class="w-8 h-8 lg:w-10 lg:h-10 object-cover rounded" />
              <div class="min-w-0 flex-1">
                <div class="font-semibold truncate text-sm lg:text-base">{{ order.product ? order.product.name : '' }}</div>
                <div class="text-xs text-gray-500">Qty: {{ order.quantity }}</div>
                <div class="text-xs text-gray-400 hidden sm:block">SKU: {{ order.product ? order.product.sku : '' }}</div>
              </div>
            </td>
            <td class="px-2 lg:px-3 py-2 font-bold text-sm lg:text-base">{{ order.price }} FCFA</td>
            <td class="px-2 lg:px-3 py-2 hidden md:table-cell">
              <div class="text-sm">{{ order.client_name }}</div>
              <div class="text-xs text-gray-500 truncate">{{ order.client_address }}</div>
              <div class="text-xs text-gray-400">{{ order.client_phone }}</div>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden lg:table-cell">
              <div class="text-sm">{{ order.agent || 'Mme' }}</div>
              <!-- Show assigned agent if available -->
              <div v-if="order.assignment && order.assignment.assigned_to" class="text-xs text-purple-600">
                Assigned to: {{ order.assignment.assigned_to.name }}
              </div>
            </td>
            <td class="px-2 lg:px-3 py-2">
              <button @click="openStatusModal(order)" :class="getStatusClass(order.status) + ' px-2 py-1 rounded text-xs focus:outline-none'">
                {{ order.status || 'Pending' }}
              </button>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden lg:table-cell">
              <div class="text-xs text-gray-600">Created: {{ formatDate(order.created_at) }}</div>
              <div class="text-xs text-blue-600">Updated: {{ formatDate(order.updated_at) }}</div>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden xl:table-cell">{{ order.zone }}</td>
            <td class="px-2 lg:px-3 py-2 truncate max-w-xs hidden xl:table-cell">{{ order.comment }}</td>
            <td class="px-2 lg:px-3 py-2 flex gap-1 lg:gap-2">
              <button class="text-blue-600 hover:text-blue-800 p-1" @click="openDetails(order)">
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button class="text-green-600 hover:text-green-800 p-1" @click="openEdit(order)">
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11V9a3 3 0 013-3h0a3 3 0 013 3v2m0 0v2a3 3 0 01-3 3h0a3 3 0 01-3-3v-2m6 0H9" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.232 4.232a2 2 0 112.828 2.828l-10 10a2 2 0 01-2.828 0l-2-2a2 2 0 010-2.828l10-10z" />
                </svg>
              </button>
              <button class="text-red-600 hover:text-red-800 p-1">
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a2 2 0 012 2v2H7V5a2 2 0 012-2z" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <OrderDetailsModal v-if="showDetails" :order="selectedOrder" @close="closeDetails" @edit="openEdit" />
    <OrderEdit v-if="showEdit" :order="editOrder" :products="productsList" @cancel="closeEdit" @updated="handleUpdated" />
    
    <!-- Assignment Modal -->
    <div v-if="showAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeAssignmentModal">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Assign Orders to Agent</h3>
          <button @click="closeAssignmentModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">
            Assigning <strong>{{ selectedIds.size }}</strong> order(s) to an agent
          </p>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Agent</label>
          <select v-model="assignmentForm.agentId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose an agent...</option>
            <option v-for="agent in availableAgents" :key="agent.id" :value="agent.id">
              {{ agent.name }} ({{ agent.email }})
            </option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea v-model="assignmentForm.notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any notes about this assignment..."></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closeAssignmentModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="assignOrders" :disabled="!assignmentForm.agentId || isAssigning" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50">
            {{ isAssigning ? 'Assigning...' : 'Assign Orders' }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Status Modal inline -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeStatusModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-sm max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Change Status</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <button v-for="st in availableStatuses" :key="st" @click="updateOrderStatus(st)" :class="getStatusClass(st)+' text-sm px-3 py-2 rounded text-center'">
            {{ st }}
          </button>
        </div>
        <button class="mt-4 w-full text-center px-4 py-2 bg-gray-200 rounded" @click="closeStatusModal">Cancel</button>
      </div>
    </div>
    <!-- Toast inline -->
    <div v-if="toastMessage" :class="'toast px-4 py-2 rounded text-white '+(toastType==='success'?'bg-green-600':'bg-red-600')">
      {{ toastMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, computed } from 'vue'
import OrderDetailsModal from './OrderDetailsModal.vue'
import OrderEdit from './OrderEdit.vue'

const props = defineProps({
  confirmation: Boolean,
  delivery: Boolean
})

const orders = ref([])
const sellers = ref([])
const statuses = ref(['Confirmed', 'Delivered', 'Cancelled', 'Postponed', 'Pending'])
const agents = ref(['Mme'])
const zones = ref([])
const dateRanges = ['Today', 'Yesterday', 'This Month', 'Last Month', 'Custom Date']
const filters = ref({ search: '', seller: '', status: '', agent: '', zone: '', dateRange: '' })
const showDetails = ref(false)
const selectedOrder = ref(null)
const showEdit = ref(false)
const editOrder = ref(null)
const productsList = ref([])
const checked = ref([])
const selectedIds = computed(() => new Set(checked.value))
const allSelected = computed(() => orders.value.length && checked.value.length === orders.value.length)
const availableStatuses = ref([])
const showStatusModal = ref(false)
const statusTargetOrder = ref(null)
const toastMessage = ref('')
const toastType = ref('success')

// Assignment related refs
const showAssignmentModal = ref(false)
const availableAgents = ref([])
const isAssigning = ref(false)
const assignmentForm = ref({
  agentId: '',
  notes: ''
})

// Check if user is superadmin
const isSuperadmin = computed(() => {
  const roles = window.Laravel?.user?.roles || []
  return roles.includes('superadmin') || roles.some(role => typeof role === 'object' && role.name === 'superadmin')
})

const fetchOrders = async () => {
  let url = '/orders/list?'
  if (filters.value.search) url += `search=${encodeURIComponent(filters.value.search)}&`
  if (filters.value.seller) url += `seller=${encodeURIComponent(filters.value.seller)}&`
  if (filters.value.status) url += `status=${encodeURIComponent(filters.value.status)}&`
  if (filters.value.agent) url += `agent=${encodeURIComponent(filters.value.agent)}&`
  if (filters.value.zone) url += `zone=${encodeURIComponent(filters.value.zone)}&`
  if (filters.value.dateRange) url += `dateRange=${encodeURIComponent(filters.value.dateRange)}&`
  if (props.confirmation) {
    url += `status=New Order,Pending,Unreachable&`
  } else if (props.delivery) {
    url += `exclude_status=New Order,Pending,Unreachable&`
  }
  const res = await fetch(url)
  const data = await res.json()
  orders.value = data.orders
  sellers.value = data.sellers
  zones.value = data.zones
}

const fetchAvailableAgents = async () => {
  try {
    const response = await fetch('/api/order-assignments/agents')
    if (response.ok) {
      availableAgents.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch agents:', error)
  }
}

const openAssignmentModal = async () => {
  if (selectedIds.value.size === 0) {
    toastType.value = 'error'
    toastMessage.value = 'Please select at least one order to assign'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  await fetchAvailableAgents()
  showAssignmentModal.value = true
}

const closeAssignmentModal = () => {
  showAssignmentModal.value = false
  assignmentForm.value = { agentId: '', notes: '' }
}

const assignOrders = async () => {
  if (!assignmentForm.value.agentId) {
    toastType.value = 'error'
    toastMessage.value = 'Please select an agent'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  isAssigning.value = true
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch('/api/order-assignments/assign', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify({
        order_ids: Array.from(selectedIds.value),
        agent_id: assignmentForm.value.agentId,
        notes: assignmentForm.value.notes
      })
    })
    
    if (response.ok) {
      const result = await response.json()
      toastType.value = 'success'
      toastMessage.value = result.message
      closeAssignmentModal()
      checked.value = [] // Clear selection
      await fetchOrders() // Refresh orders to show assignments
    } else {
      const error = await response.json()
      toastType.value = 'error'
      toastMessage.value = error.message || 'Failed to assign orders'
    }
  } catch (error) {
    console.error('Failed to assign orders:', error)
    toastType.value = 'error'
    toastMessage.value = 'Failed to assign orders'
  } finally {
    isAssigning.value = false
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

const clearFilters = () => {
  filters.value = { search: '', seller: '', status: '', agent: '', zone: '', dateRange: '' }
  fetchOrders()
}

const setDateRange = (range) => {
  filters.value.dateRange = range
  fetchOrders()
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleString()
}

const openDetails = (order) => { selectedOrder.value = order; showDetails.value = true }
const closeDetails = () => { showDetails.value = false; selectedOrder.value = null }

const openEdit = async (order) => {
  editOrder.value = order
  showEdit.value = true
  // fetch products for dropdown
  const res = await fetch('/products/list')
  const data = await res.json()
  productsList.value = data.products || []
}

const closeEdit = () => { showEdit.value = false; editOrder.value = null }
const handleUpdated = () => { showEdit.value = false; editOrder.value = null; fetchOrders() }

const toggleSelectAll = (e) => {
  if (e.target.checked) {
    checked.value = orders.value.map(o => o.id)
  } else {
    checked.value = []
  }
}

const downloadDeliveryNote = () => {
  if (!selectedIds.value.size) return
  const idsParam = Array.from(selectedIds.value).join(',')
  window.open(`/orders/delivery-note?ids=${idsParam}`, '_blank')
}

const downloadInvoices = () => {
  if (!selectedIds.value.size) return
  const idsParam = Array.from(selectedIds.value).join(',')
  window.open(`/orders/invoices?ids=${idsParam}`, '_blank')
}

const generateDeliveryInvoice = () => {
  if (!selectedIds.value.size) {
    alert('Please select at least one order first');
    return
  }
  const idsParam = Array.from(selectedIds.value).join(',')
  window.open(`/orders/delivery-invoice?ids=${idsParam}`, '_blank')
}

const getStatusClass = (status) => {
  switch (status) {
    case 'Confirmed': return 'bg-green-100 text-green-700'
    case 'Delivered': return 'bg-blue-100 text-blue-700'
    case 'Cancelled': return 'bg-red-100 text-red-700'
    case 'Postponed': return 'bg-yellow-100 text-yellow-700'
    case 'Shipped': return 'bg-purple-100 text-purple-700'
    case 'Processing': return 'bg-blue-100 text-blue-700'
    case 'Pending': return 'bg-yellow-100 text-yellow-700'
    case 'New Order': return 'bg-gray-100 text-gray-700'
    default: return 'bg-gray-100 text-gray-700'
  }
}

const openStatusModal = (order) => {
  statusTargetOrder.value = order
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
  statusTargetOrder.value = null
}

const fetchStatuses = async () => {
  const res = await fetch('/order-statuses/list')
  const data = await res.json()
  availableStatuses.value = data.map(s => s.name)
}

const updateOrderStatus = async (statusName) => {
  if (!statusTargetOrder.value) return
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/orders/${statusTargetOrder.value.id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ status: statusName })
    })
    if (!res.ok) throw new Error(await res.text())
    const data = await res.json()
    // Update local orders array
    const idx = orders.value.findIndex(o => o.id === statusTargetOrder.value.id)
    if (idx !== -1) {
      orders.value[idx].status = statusName
    }
    toastType.value = 'success'
    toastMessage.value = 'Status updated successfully'
  } catch (err) {
    toastType.value = 'error'
    toastMessage.value = 'Failed to update status'
    console.error(err)
  } finally {
    closeStatusModal()
    // auto clear toast after 3s
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

onMounted(() => { fetchOrders(); fetchStatuses() })
</script>

<style scoped>
.toast {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1000;
}
</style> 