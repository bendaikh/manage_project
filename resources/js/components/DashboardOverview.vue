<template>
  <div>
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div v-for="card in cards" :key="card.title" class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-medium">{{ card.title }}</h3>
          <span class="text-xl font-bold">{{ card.value }}</span>
        </div>
        <div class="text-sm" :class="card.color">{{ card.percentage }}%</div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="font-semibold mb-2">Order Status Distribution</h3>
        <canvas ref="distChart" height="300"></canvas>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="font-semibold mb-2">Order Trend</h3>
        <canvas ref="trendChart" height="300"></canvas>
      </div>
    </div>

    <!-- Detailed Breakdown -->
    <div class="bg-white p-4 rounded-lg shadow">
      <h3 class="font-semibold mb-4">Detailed Status Breakdown</h3>
      <table class="min-w-full">
        <thead>
          <tr class="text-left text-sm font-semibold text-gray-700">
            <th class="py-2">Status</th>
            <th class="py-2">Count</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(cnt, status) in distribution" :key="status" class="border-t">
            <td class="py-2">{{ status }}</td>
            <td class="py-2">{{ cnt }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue'
import { Chart, ArcElement, BarElement, CategoryScale, LinearScale, LineElement, PointElement, Tooltip, Legend } from 'chart.js'
Chart.register(ArcElement, BarElement, CategoryScale, LinearScale, LineElement, PointElement, Tooltip, Legend)

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

const summary = ref({ total: 0, confirmed: 0, cancelled: 0, pending: 0, total_change: null })
const distribution = ref({})
const trend = ref([])

const cards = ref([])
const distChart = ref(null)
const trendChart = ref(null)

const fetchData = async () => {
  try {
    const url = `/dashboard/overview-data?start_date=${startDate.value}&end_date=${endDate.value}`
    const res = await fetch(url)
    const data = await res.json()
    summary.value = data.summary
    distribution.value = data.distribution
    trend.value = data.trend
    buildCards()
    buildCharts()
  } catch (err) {
    console.error('Failed to load overview data', err)
  }
}

const buildCards = () => {
  const s = summary.value
  cards.value = [
    { title: 'Total Orders', value: s.total, percentage: s.total_change ?? '-', color: 'text-green-600' },
    { title: 'Confirmed Orders', value: s.confirmed, percentage: s.total ? ((s.confirmed / s.total) * 100).toFixed(0) : 0, color: 'text-green-600' },
    { title: 'Cancelled Orders', value: s.cancelled, percentage: s.total ? ((s.cancelled / s.total) * 100).toFixed(0) : 0, color: 'text-red-600' },
    { title: 'Pending Orders', value: s.pending, percentage: s.total ? ((s.pending / s.total) * 100).toFixed(0) : 0, color: 'text-yellow-600' }
  ]
}

const buildCharts = () => {
  nextTick(() => {
    // Distribution pie
    if (distChart.value?._chartInstance) distChart.value._chartInstance.destroy()
    const ctx1 = distChart.value.getContext('2d')
    distChart.value._chartInstance = new Chart(ctx1, {
      type: 'pie',
      data: {
        labels: Object.keys(distribution.value),
        datasets: [{ data: Object.values(distribution.value), backgroundColor: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6'] }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    })

    // Trend line
    if (trendChart.value?._chartInstance) trendChart.value._chartInstance.destroy()
    const ctx2 = trendChart.value.getContext('2d')
    const dates = trend.value.map(t => t.date)
    trendChart.value._chartInstance = new Chart(ctx2, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          { label: 'All Orders', data: trend.value.map(t => t.all), borderColor: '#3b82f6', fill: false },
          { label: 'Confirmed', data: trend.value.map(t => t.confirmed), borderColor: '#10b981', fill: false },
          { label: 'Cancelled', data: trend.value.map(t => t.cancelled), borderColor: '#ef4444', fill: false }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    })
  })
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

onMounted(fetchData)
</script> 