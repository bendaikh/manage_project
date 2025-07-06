<template>
  <div>
    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
      <h2 class="font-semibold text-lg mb-4">Filters &amp; Search</h2>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Start Date</label>
          <input v-model="filters.start_date" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">End Date</label>
          <input v-model="filters.end_date" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Seller</label>
          <select v-model="filters.seller" class="w-full border rounded px-3 py-2">
            <option value="">All Sellers</option>
            <option v-for="s in sellers" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Agent</label>
          <select v-model="filters.agent" class="w-full border rounded px-3 py-2">
            <option value="">All Agents</option>
            <option v-for="a in agents" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Order Status</label>
          <select v-model="filters.order_status_id" class="w-full border rounded px-3 py-2">
            <option value="">All Statuses</option>
            <option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="lg:col-span-2">
          <label class="block text-xs font-medium mb-1">Product</label>
          <input v-model="filters.product" placeholder="Search products..." class="w-full border rounded px-3 py-2" />
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button @click="fetchData" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filters</button>
        <button @click="resetFilters" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Reset</button>
        <button @click="exportCSV" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Export</button>
        <div class="flex gap-2 ml-auto">
          <button v-for="opt in quickRanges" :key="opt.label" @click="setQuickRange(opt)" class="px-3 py-1 text-xs rounded border hover:bg-gray-100" :class="{ 'bg-blue-50 text-blue-600': activeRange===opt.label }">
            {{ opt.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
      <div v-for="w in widgets" :key="w.title" class="bg-white p-4 rounded-lg shadow relative">
        <div class="absolute top-4 right-4 text-xl" :class="w.color"><component :is="w.icon" /></div>
        <p class="text-xs text-gray-500">{{ w.title }}</p>
        <p class="text-xl font-bold">{{ w.value }}</p>
      </div>
    </div>

    <!-- Seller Performance Overview -->
    <h3 class="font-semibold text-sm mb-2">Seller Performance Overview</h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="text-xs font-bold mb-2">Revenue by Seller</h4>
        <canvas ref="sellerChart" height="200"></canvas>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="text-xs font-bold mb-2">Performance by Agent</h4>
        <canvas ref="agentChart" height="200"></canvas>
      </div>
      <div class="bg-white p-4 rounded-lg shadow lg:col-span-2">
        <h4 class="text-xs font-bold mb-2">Order Status Distribution</h4>
        <canvas ref="statusChart" height="200"></canvas>
      </div>
      <div class="bg-white p-4 rounded-lg shadow lg:col-span-2">
        <h4 class="text-xs font-bold mb-2">Revenue by Zone</h4>
        <canvas ref="zoneChart" height="200"></canvas>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b text-sm font-semibold">Orders Data ({{ orders.length }} orders)</div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-3 py-2 text-left">Order ID</th>
              <th class="px-3 py-2 text-left">Seller</th>
              <th class="px-3 py-2 text-left">Agent</th>
              <th class="px-3 py-2 text-left">Product</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-right">Qty</th>
              <th class="px-3 py-2 text-right">Unit Price</th>
              <th class="px-3 py-2 text-right">Total Value</th>
              <th class="px-3 py-2 text-right">Profit</th>
              <th class="px-3 py-2 text-left">Zone</th>
              <th class="px-3 py-2 text-left">Created</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in orders" :key="o.id" class="border-t">
              <td class="px-3 py-2">{{ o.id }}</td>
              <td class="px-3 py-2">{{ o.seller }}</td>
              <td class="px-3 py-2">{{ o.agent }}</td>
              <td class="px-3 py-2">{{ o.product?.name }}</td>
              <td class="px-3 py-2">{{ o.order_status?.name || o.status }}</td>
              <td class="px-3 py-2 text-right">{{ o.quantity }}</td>
              <td class="px-3 py-2 text-right">{{ format(o.price) }}</td>
              <td class="px-3 py-2 text-right">{{ format(o.price) }}</td>
              <td class="px-3 py-2 text-right">{{ format(o.price*0.2) }}</td>
              <td class="px-3 py-2">{{ o.zone }}</td>
              <td class="px-3 py-2">{{ new Date(o.created_at).toLocaleDateString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Chart, BarElement, CategoryScale, LinearScale, Tooltip, Legend, ArcElement } from 'chart.js'
Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend, ArcElement)

const filters = reactive({
  start_date: new Date().toISOString().substr(0,10),
  end_date: new Date().toISOString().substr(0,10),
  seller: '',
  agent: '',
  order_status_id: '',
  zone: '',
  product: ''
})

const quickRanges = [
  { label: 'Today', days: 0 },
  { label: 'Last 7 Days', days: 7 },
  { label: 'Last 30 Days', days: 30 },
  { label: 'Last Quarter', days: 90 },
  { label: 'Last Year', days: 365 }
]
const activeRange = ref('Today')

const summary = ref({ revenue:0, profit:0, total_orders:0, active_sellers:0, avg_order_value:0 })
const widgets = ref([])
const sellers = ref([])
const agents = ref([])
const statuses = ref([])
const orders = ref([])

const sellerChart = ref(null)
const agentChart = ref(null)
const statusChart = ref(null)
const zoneChart = ref(null)

const fetchData = async () => {
  const params = new URLSearchParams(filters).toString()
  const res = await fetch(`/dashboard/analytics-data?${params}`)
  const data = await res.json()
  summary.value = data.summary
  orders.value = data.orders
  buildWidgets()
  await nextTick()
  buildCharts(data)
}

const buildWidgets = () => {
  const s = summary.value
  widgets.value = [
    { title: 'Total Revenue', value: format(s.revenue)+' FCFA', icon: '💵', color: 'text-emerald-600' },
    { title: 'Total Profit', value: format(s.profit)+' FCFA', icon: '💰', color: 'text-purple-600' },
    { title: 'Total Orders', value: s.total_orders, icon: '📦', color: 'text-blue-600' },
    { title: 'Active Sellers', value: s.active_sellers, icon: '👤', color: 'text-indigo-600' },
    { title: 'Avg Order Value', value: format(s.avg_order_value)+' FCFA', icon: '💳', color: 'text-yellow-600' }
  ]
}

const buildCharts = (data) => {
  const destroy = (c)=>{ if(c?._chartInstance) c._chartInstance.destroy() }
  destroy(sellerChart.value); destroy(agentChart.value); destroy(statusChart.value); destroy(zoneChart.value)

  const sellerCtx = sellerChart.value.getContext('2d')
  sellerChart.value._chartInstance = new Chart(sellerCtx,{type:'bar',data:{labels:Object.keys(data.revenue_by_seller),datasets:[{label:'Revenue by Seller',data:Object.values(data.revenue_by_seller),backgroundColor:'#3b82f6'}]},options:{plugins:{legend:{display:false}}}})

  const agentCtx=agentChart.value.getContext('2d')
  agentChart.value._chartInstance=new Chart(agentCtx,{type:'bar',data:{labels:Object.keys(data.performance_by_agent),datasets:[{label:'Revenue',data:Object.values(data.performance_by_agent).map(a=>a.revenue),backgroundColor:'#10b981'},{label:'Profit',data:Object.values(data.performance_by_agent).map(a=>a.profit),backgroundColor:'#8b5cf6'}]},options:{responsive:true}})

  const statusCtx=statusChart.value.getContext('2d')
  statusChart.value._chartInstance=new Chart(statusCtx,{type:'pie',data:{labels:Object.keys(data.status_distribution),datasets:[{data:Object.values(data.status_distribution),backgroundColor:['#3b82f6','#10b981','#ef4444','#f59e0b','#8b5cf6']}]}})

  const zoneCtx=zoneChart.value.getContext('2d')
  zoneChart.value._chartInstance=new Chart(zoneCtx,{type:'bar',data:{labels:Object.keys(data.revenue_by_zone),datasets:[{label:'Revenue by Zone',data:Object.values(data.revenue_by_zone),backgroundColor:'#f59e0b'}]},options:{plugins:{legend:{display:false}}}})
}

const resetFilters = () => {
  const today=new Date().toISOString().substr(0,10)
  Object.assign(filters,{start_date:today,end_date:today,seller:'',agent:'',order_status_id:'',zone:'',product:''})
  activeRange.value='Today'
  fetchData()
}

const setQuickRange = ({label,days}) => {
  activeRange.value=label
  const end=new Date()
  const start=new Date()
  if(days>0) start.setDate(end.getDate()-days)
  filters.start_date=start.toISOString().substr(0,10)
  filters.end_date=end.toISOString().substr(0,10)
  fetchData()
}

const format = (n)=>Number(n).toLocaleString()

const exportCSV = () => {
  // simple CSV export of orders
  const rows = orders.value.map(o=>[o.id,o.seller,o.agent,o.product?.name,o.status,o.quantity,o.price,o.zone,o.created_at])
  const header=['ID','Seller','Agent','Product','Status','Qty','Price','Zone','Created']
  const csv=[header,...rows].map(r=>r.join(',')).join('\n')
  const blob=new Blob([csv],{type:'text/csv'})
  const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='analytics.csv'; a.click(); URL.revokeObjectURL(url)
}

onMounted(async()=>{
  // fetch dropdown data
  const sRes=await fetch('/order-statuses/list'); statuses.value=await sRes.json()
  // sellers and agents from orders endpoint for now
  fetchData()
})
</script> 