<template>
  <div class="p-6">
    <!-- Simple Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Dashboard Analytics</h1>
      <p class="text-gray-600">Analytics and reporting overview</p>
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

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const fetchStats = async () => {
  try {
    const response = await fetch('/dashboard/analytics-stats')
    const data = await response.json()
    stats.value = data
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

onMounted(() => {
  fetchStats()
})
</script> 