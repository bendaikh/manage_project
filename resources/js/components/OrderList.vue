<template>
  <div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold">Order Management</h2>
      <button @click="$emit('create-order')" class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold">
        + Create Order
      </button>
    </div>
    <!-- Filter Orders -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap gap-2 mb-4">
        <button v-for="range in dateRanges" :key="range" @click="setDateRange(range)" class="px-3 py-1 border rounded text-sm" :class="{ 'bg-gray-200': filters.dateRange === range }">{{ range }}</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <input v-model="filters.search" @keyup.enter="fetchOrders" type="text" placeholder="Search by Order ID, Product, Client, Phone..." class="w-full border rounded px-3 py-2" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
        <select v-model="filters.seller" class="w-full border rounded px-3 py-2">
          <option value="">All Sellers</option>
          <option v-for="seller in sellers" :key="seller" :value="seller">{{ seller }}</option>
        </select>
        <select v-model="filters.status" class="w-full border rounded px-3 py-2">
          <option value="">All Statuses</option>
          <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
        </select>
        <select v-model="filters.agent" class="w-full border rounded px-3 py-2">
          <option value="">All Agents</option>
          <option v-for="agent in agents" :key="agent" :value="agent">{{ agent }}</option>
        </select>
        <select v-model="filters.zone" class="w-full border rounded px-3 py-2">
          <option value="">All Zones</option>
          <option v-for="zone in zones" :key="zone" :value="zone">{{ zone }}</option>
        </select>
        <div class="flex gap-2">
          <button @click="fetchOrders" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center justify-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4h13M8 12h13M8 20h13M3 6h.01M3 18h.01"/></svg>
            Apply Filters
          </button>
          <button @click="clearFilters" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">Reset</button>
        </div>
      </div>
    </div>
    <!-- Action bar -->
    <div v-if="selectedIds.size" class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded p-3 mb-3">
      <div>{{ selectedIds.size }} selected</div>
      <div class="flex gap-2">
        <button @click="downloadDeliveryNote" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Download Delivery Note</button>
        <button @click="downloadInvoices" class="px-4 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 text-sm">Download Invoices</button>
      </div>
    </div>
    
    <!-- Invoice Delivery Button (only for delivery page) -->
    <div v-if="props.delivery" class="flex justify-end mb-4">
      <button @click="generateDeliveryInvoice" class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-semibold flex items-center gap-2">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Invoice Delivery
      </button>
    </div>
    
    <!-- Orders Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2"><input type="checkbox" :checked="allSelected" @change="toggleSelectAll"></th>
            <th class="px-3 py-2 text-left text-xs font-bold">ORDER ID</th>
            <th class="px-3 py-2 text-left text-xs font-bold">SELLER</th>
            <th class="px-3 py-2 text-left text-xs font-bold">PRODUCT</th>
            <th class="px-3 py-2 text-left text-xs font-bold">PRICE</th>
            <th class="px-3 py-2 text-left text-xs font-bold">CLIENT</th>
            <th class="px-3 py-2 text-left text-xs font-bold">AGENT</th>
            <th class="px-3 py-2 text-left text-xs font-bold">STATUS</th>
            <th class="px-3 py-2 text-left text-xs font-bold">DATE</th>
            <th class="px-3 py-2 text-left text-xs font-bold">ZONE</th>
            <th class="px-3 py-2 text-left text-xs font-bold">COMMENT</th>
            <th class="px-3 py-2 text-left text-xs font-bold">ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="border-b">
            <td class="px-3 py-2 text-center"><input type="checkbox" :value="order.id" v-model="checked"></td>
            <td class="px-3 py-2 font-mono text-xs">
              <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ order.id }}</span>
            </td>
            <td class="px-3 py-2">{{ order.seller }}</td>
            <td class="px-3 py-2 flex items-center gap-2">
              <img v-if="order.product && order.product.image_url" :src="order.product.image_url" class="w-10 h-10 object-cover rounded" />
              <div>
                <div class="font-semibold truncate">{{ order.product ? order.product.name : '' }}</div>
                <div class="text-xs text-gray-500">Qty: {{ order.quantity }}</div>
                <div class="text-xs text-gray-400">SKU: {{ order.product ? order.product.sku : '' }}</div>
              </div>
            </td>
            <td class="px-3 py-2 font-bold">{{ order.price }} FCFA</td>
            <td class="px-3 py-2">
              <div>{{ order.client_name }}</div>
              <div class="text-xs text-gray-500">{{ order.client_address }}</div>
              <div class="text-xs text-gray-400">{{ order.client_phone }}</div>
            </td>
            <td class="px-3 py-2">{{ order.agent || 'Mme' }}</td>
            <td class="px-3 py-2">
              <span v-if="order.status === 'Confirmed'" class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Confirmed</span>
              <span v-else-if="order.status === 'Delivered'" class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Delivered</span>
              <span v-else-if="order.status === 'Cancelled'" class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Cancelled</span>
              <span v-else-if="order.status === 'Postponed'" class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Postponed</span>
              <span v-else class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">{{ order.status || 'Pending' }}</span>
            </td>
            <td class="px-3 py-2">
              <div class="text-xs text-gray-600">Created: {{ formatDate(order.created_at) }}</div>
              <div class="text-xs text-blue-600">Updated: {{ formatDate(order.updated_at) }}</div>
            </td>
            <td class="px-3 py-2">{{ order.zone }}</td>
            <td class="px-3 py-2 truncate max-w-xs">{{ order.comment }}</td>
            <td class="px-3 py-2 flex gap-2">
              <button class="text-blue-600 hover:text-blue-800" @click="openDetails(order)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button class="text-green-600 hover:text-green-800" @click="openEdit(order)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11V9a3 3 0 013-3h0a3 3 0 013 3v2m0 0v2a3 3 0 01-3 3h0a3 3 0 01-3-3v-2m6 0H9" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.232 4.232a2 2 0 112.828 2.828l-10 10a2 2 0 01-2.828 0l-2-2a2 2 0 010-2.828l10-10z" />
                </svg>
              </button>
              <button class="text-red-600 hover:text-red-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

const fetchOrders = async () => {
  let url = '/orders/list?'
  if (filters.value.search) url += `search=${encodeURIComponent(filters.value.search)}&`
  if (filters.value.seller) url += `seller=${encodeURIComponent(filters.value.seller)}&`
  if (filters.value.status) url += `status=${encodeURIComponent(filters.value.status)}&`
  if (filters.value.agent) url += `agent=${encodeURIComponent(filters.value.agent)}&`
  if (filters.value.zone) url += `zone=${encodeURIComponent(filters.value.zone)}&`
  if (filters.value.dateRange) url += `dateRange=${encodeURIComponent(filters.value.dateRange)}&`
  if (props.confirmation) {
    url += `exclude_status=Confirmed,Delivered&`
  } else if (props.delivery) {
    url += `status=Confirmed,Delivered&`
  }
  const res = await fetch(url)
  const data = await res.json()
  orders.value = data.orders
  sellers.value = data.sellers
  zones.value = data.zones
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
  window.open('/orders/delivery-invoice', '_blank')
}

onMounted(fetchOrders)
</script> 