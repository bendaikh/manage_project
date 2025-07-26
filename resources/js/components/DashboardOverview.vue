<template>
  <div class="p-6">
    <!-- Date Filter -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
      <h2 class="font-semibold text-lg mb-4">Date Range Filter</h2>
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
        <div class="md:col-span-5">
          <label class="block text-sm font-medium mb-1">Start Date</label>
          <input v-model="startDate" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="md:col-span-5">
          <label class="block text-sm font-medium mb-1">End Date</label>
          <input v-model="endDate" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="md:col-span-2 flex gap-2">
          <button @click="applyFilter" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filter</button>
          <button @click="resetFilter" class="flex-1 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Reset</button>
        </div>
      </div>
      <div class="flex flex-wrap gap-2 mt-4">
        <button v-for="opt in quickRanges" :key="opt.label" @click="setQuickRange(opt)" class="px-3 py-1 text-sm rounded border hover:bg-gray-100" :class="{ 'bg-blue-50 text-blue-600': activeRange === opt.label }">
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Confirmation Status Block -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-blue-600">Confirmation Status</h2>
        <button @click="toggleConfirmation" class="text-blue-600 hover:text-blue-800 transition-transform" :class="{ 'rotate-180': showConfirmation }">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>
      
      <div v-if="showConfirmation" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="status in confirmationStatuses" :key="status.name" class="bg-gray-50 p-4 rounded-lg">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="status.bgColor">
              <span class="text-white text-sm font-bold">{{ status.icon }}</span>
            </div>
            <div>
              <div class="font-medium">{{ status.name }}</div>
              <div class="text-2xl font-bold">{{ status.count }}</div>
            </div>
          </div>
          <div class="mb-2">
            <div class="flex justify-between text-sm mb-1">
              <span>Progress</span>
              <span>{{ status.percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full transition-all duration-300" :class="status.barColor" :style="{ width: status.percentage + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delivery Status Block -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-green-600">Delivery Status</h2>
        <button @click="toggleDelivery" class="text-green-600 hover:text-green-800 transition-transform" :class="{ 'rotate-180': showDelivery }">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>
      
      <div v-if="showDelivery" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="status in deliveryStatuses" :key="status.name" class="bg-gray-50 p-4 rounded-lg">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="status.bgColor">
              <span class="text-white text-sm font-bold">{{ status.icon }}</span>
            </div>
            <div>
              <div class="font-medium">{{ status.name }}</div>
              <div class="text-2xl font-bold">{{ status.count }}</div>
            </div>
          </div>
          <div class="mb-2">
            <div class="flex justify-between text-sm mb-1">
              <span>Progress</span>
              <span>{{ status.percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full transition-all duration-300" :class="status.barColor" :style="{ width: status.percentage + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-purple-600">Recent Activity</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="history in recentHistories" :key="history.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ history.title }} — <span class="text-gray-600">{{ history.user?.name || 'System' }}</span></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ history.description }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(history.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-4 text-right">
        <button @click="$emit('show-full-history')" class="text-blue-600 hover:underline font-semibold text-sm">
          See more
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const emit = defineEmits(['show-full-history'])

const startDate = ref(new Date().toISOString().substr(0, 10))
const endDate = ref(new Date().toISOString().substr(0, 10))
const activeRange = ref('Today')

const quickRanges = [
  { label: 'Last 7 Days', days: 7 },
  { label: 'Last 30 Days', days: 30 },
  { label: 'Last Quarter', days: 90 },
  { label: 'Last Year', days: 365 },
  { label: 'Today', days: 0 }
]

const allOrders = ref([])
const confirmationOrders = ref([])
const deliveryOrders = ref([])

// Expandable sections
const showConfirmation = ref(true)
const showDelivery = ref(true)

// Status configurations with icons and colors based on your database
const confirmationStatuses = computed(() => {
  const total = allOrders.value.length  // Use total orders from database
  return [
    { 
      name: 'All Orders', 
      count: total, 
      percentage: total > 0 ? 100 : 0, 
      icon: '📦', 
      bgColor: 'bg-gray-600', 
      barColor: 'bg-gray-600' 
    },
    { 
      name: 'New Order', 
      count: confirmationOrders.value.filter(o => o.status === 'New Order').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'New Order').length / total) * 100) : 0, 
      icon: '🆕', 
      bgColor: 'bg-blue-500', 
      barColor: 'bg-blue-500' 
    },
    { 
      name: 'Confirmed', 
      count: deliveryOrders.value.length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.length / total) * 100) : 0, 
      icon: '✅', 
      bgColor: 'bg-green-600', 
      barColor: 'bg-green-600' 
    },
    { 
      name: 'Confirmed on Date', 
      count: confirmationOrders.value.filter(o => o.status === 'Confirmed on Date').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'Confirmed on Date').length / total) * 100) : 0, 
      icon: '📅✅', 
      bgColor: 'bg-green-600', 
      barColor: 'bg-green-600' 
    },
    { 
      name: 'Unreachable', 
      count: confirmationOrders.value.filter(o => o.status === 'Unreachable').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'Unreachable').length / total) * 100) : 0, 
      icon: '📞❌', 
      bgColor: 'bg-red-600', 
      barColor: 'bg-red-600' 
    },
    { 
      name: 'Postponed', 
      count: confirmationOrders.value.filter(o => o.status === 'Postponed').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'Postponed').length / total) * 100) : 0, 
      icon: '⏰❌', 
      bgColor: 'bg-red-600', 
      barColor: 'bg-red-600' 
    },
    { 
      name: 'Cancelled', 
      count: confirmationOrders.value.filter(o => o.status === 'Cancelled').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'Cancelled').length / total) * 100) : 0, 
      icon: '❌', 
      bgColor: 'bg-red-600', 
      barColor: 'bg-red-600' 
    },
    { 
      name: 'Out of Stock', 
      count: confirmationOrders.value.filter(o => o.status === 'Out of Stock').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'Out of Stock').length / total) * 100) : 0, 
      icon: '📦❌', 
      bgColor: 'bg-red-600', 
      barColor: 'bg-red-600' 
    },
    { 
      name: 'Blacklisted', 
      count: confirmationOrders.value.filter(o => o.status === 'Blacklisted').length, 
      percentage: total > 0 ? Math.round((confirmationOrders.value.filter(o => o.status === 'Blacklisted').length / total) * 100) : 0, 
      icon: '🚫', 
      bgColor: 'bg-red-600', 
      barColor: 'bg-red-600' 
    }
  ]
})

const deliveryStatuses = computed(() => {
  const total = deliveryOrders.value.length
  return [
    { 
      name: 'All Orders', 
      count: total, 
      percentage: total > 0 ? 100 : 0, 
      icon: '📦', 
      bgColor: 'bg-gray-600', 
      barColor: 'bg-gray-600' 
    },
    { 
      name: 'Processing', 
      count: deliveryOrders.value.filter(o => o.status === 'Processing').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Processing').length / total) * 100) : 0, 
      icon: '⚙️', 
      bgColor: 'bg-yellow-500', 
      barColor: 'bg-yellow-500' 
    },
    { 
      name: 'Shipped', 
      count: deliveryOrders.value.filter(o => o.status === 'Shipped').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Shipped').length / total) * 100) : 0, 
      icon: '🚚', 
      bgColor: 'bg-purple-600', 
      barColor: 'bg-purple-600' 
    },
    { 
      name: 'Unreachable', 
      count: deliveryOrders.value.filter(o => o.status === 'Unreachable').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Unreachable').length / total) * 100) : 0, 
      icon: '📞❌', 
      bgColor: 'bg-gray-500', 
      barColor: 'bg-gray-500' 
    },
    { 
      name: 'Postponed', 
      count: deliveryOrders.value.filter(o => o.status === 'Postponed').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Postponed').length / total) * 100) : 0, 
      icon: '⏰❌', 
      bgColor: 'bg-yellow-600', 
      barColor: 'bg-yellow-600' 
    },
    { 
      name: 'Cancelled', 
      count: deliveryOrders.value.filter(o => o.status === 'Cancelled').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Cancelled').length / total) * 100) : 0, 
      icon: '❌', 
      bgColor: 'bg-red-600', 
      barColor: 'bg-red-600' 
    },
    { 
      name: 'Delivered', 
      count: deliveryOrders.value.filter(o => o.status === 'Delivered').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Delivered').length / total) * 100) : 0, 
      icon: '📦✅', 
      bgColor: 'bg-green-600', 
      barColor: 'bg-green-600' 
    },
    { 
      name: 'Out of Stock', 
      count: deliveryOrders.value.filter(o => o.status === 'Out of Stock').length, 
      percentage: total > 0 ? Math.round((deliveryOrders.value.filter(o => o.status === 'Out of Stock').length / total) * 100) : 0, 
      icon: '📦❌', 
      bgColor: 'bg-gray-500', 
      barColor: 'bg-gray-500' 
    }
  ]
})

const recentHistories = ref([])

const fetchData = async () => {
  try {
    const url = `/dashboard/overview-data?start_date=${startDate.value}&end_date=${endDate.value}`
    const res = await fetch(url)
    const data = await res.json()
    allOrders.value = data.orders || []
    
    // Separate orders by belongs_to field
    confirmationOrders.value = allOrders.value.filter(order => order.belongs_to === 'confirmation')
    deliveryOrders.value = allOrders.value.filter(order => order.belongs_to === 'delivery')
  } catch (err) {
    console.error('Failed to load overview data', err)
  }
}

const toggleConfirmation = () => {
  showConfirmation.value = !showConfirmation.value
}

const toggleDelivery = () => {
  showDelivery.value = !showDelivery.value
}

const applyFilter = () => {
  activeRange.value = ''
  fetchData()
}

const resetFilter = () => {
  const today = new Date().toISOString().substr(0, 10)
  startDate.value = today
  endDate.value = today
  activeRange.value = 'Today'
  fetchData()
}

const setQuickRange = ({ label, days }) => {
  activeRange.value = label
  const end = new Date()
  const start = new Date()
  if (days > 0) {
    start.setDate(end.getDate() - days)
  }
  startDate.value = start.toISOString().substr(0, 10)
  endDate.value = end.toISOString().substr(0, 10)
  fetchData()
}

onMounted(async () => {
  await fetchData()
  await fetchRecentHistories()
})

const fetchRecentHistories = async () => {
  try {
    const res = await fetch('/api/history/latest')
    if (res.ok) {
      recentHistories.value = await res.json()
    }
  } catch (e) { console.error('Failed to load history', e) }
}
</script> 