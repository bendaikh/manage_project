<template>
  <div class="p-6">
    <!-- Simple Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Dashboard Analytics</h1>
      <p class="text-gray-600">Analytics and reporting overview</p>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
      <h2 class="font-semibold text-lg mb-4">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-sm font-medium mb-1">Date From</label>
          <input v-model="startDate" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Date To</label>
          <input v-model="endDate" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Agent</label>
          <select v-model="selectedAgent" class="w-full border rounded px-3 py-2">
            <option value="">All Agents</option>
            <option v-for="agent in agents" :key="agent.id" :value="agent.name">{{ agent.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Seller</label>
          <select v-model="selectedSeller" class="w-full border rounded px-3 py-2">
            <option value="">All Sellers</option>
            <option v-for="seller in sellers" :key="seller" :value="seller">{{ seller }}</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium mb-1">Product</label>
          <select v-model="selectedProduct" class="w-full border rounded px-3 py-2">
            <option value="">All Products</option>
            <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
          </select>
        </div>
        <div class="flex gap-2 items-end">
          <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filters</button>
          <button @click="clearFilters" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Clear</button>
        </div>
      </div>
    </div>

    <!-- Simple Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-medium text-gray-600">Total Revenue</h3>
        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.revenue) }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-medium text-gray-600">Total Profit</h3>
        <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats.profit) }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-medium text-gray-600">Total Orders</h3>
        <p class="text-2xl font-bold text-purple-600">{{ stats.totalOrders }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-sm font-medium text-gray-600">Active Sellers</h3>
        <p class="text-2xl font-bold text-indigo-600">{{ stats.activeSellers }}</p>
      </div>
    </div>

    <!-- Simple Content Area -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-lg font-semibold mb-4">Analytics Overview</h2>
      <div class="text-gray-600">
        <p>Analytics content will be added here.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const stats = ref({
  revenue: 0,
  profit: 0,
  totalOrders: 0,
  activeSellers: 0
})

// Filter state
const startDate = ref(new Date().toISOString().substr(0, 10))
const endDate = ref(new Date().toISOString().substr(0, 10))
const selectedAgent = ref('')
const selectedSeller = ref('')
const selectedProduct = ref('')
const agents = ref([])
const sellers = ref([])
const products = ref([])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const fetchStats = async () => {
  try {
    // Build query parameters
    const params = new URLSearchParams({
      start_date: startDate.value,
      end_date: endDate.value
    })
    
    // Add filter parameters
    if (selectedAgent.value) params.append('agent', selectedAgent.value)
    if (selectedSeller.value) params.append('seller', selectedSeller.value)
    if (selectedProduct.value) params.append('product_id', selectedProduct.value)
    
    const response = await fetch(`/dashboard/analytics-data?${params.toString()}`)
    const data = await response.json()
    stats.value = data.summary || {
      revenue: 0,
      profit: 0,
      totalOrders: 0,
      activeSellers: 0
    }
  } catch (error) {
    console.error('Failed to fetch analytics stats:', error)
    // Set default values
    stats.value = {
      revenue: 0,
      profit: 0,
      totalOrders: 0,
      activeSellers: 0
    }
  }
}

const applyFilters = () => {
  fetchStats()
}

const clearFilters = () => {
  startDate.value = new Date().toISOString().substr(0, 10)
  endDate.value = new Date().toISOString().substr(0, 10)
  selectedAgent.value = ''
  selectedSeller.value = ''
  selectedProduct.value = ''
  fetchStats()
}

const fetchFilterData = async () => {
  try {
    // Fetch agents
    const agentsRes = await fetch('/api/dashboard/agents')
    if (agentsRes.ok) {
      agents.value = await agentsRes.json()
    }
    
    // Fetch sellers
    const sellersRes = await fetch('/api/dashboard/sellers')
    if (sellersRes.ok) {
      sellers.value = await sellersRes.json()
    }
    
    // Fetch products
    const productsRes = await fetch('/api/dashboard/products')
    if (productsRes.ok) {
      products.value = await productsRes.json()
    }
  } catch (err) {
    console.error('Failed to load filter data', err)
  }
}

onMounted(async () => {
  await fetchStats()
  await fetchFilterData()
})
</script> 