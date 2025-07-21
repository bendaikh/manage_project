<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <h2 class="text-xl lg:text-2xl font-bold mb-6">Stock</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">Title</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Qty</th>
            <th class="px-3 py-2 text-left text-xs font-bold">From Shipment</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="st in stocks" :key="st.id" class="border-b">
            <td class="px-3 py-2">{{ st.title }}</td>
            <td class="px-3 py-2">{{ st.quantity }}</td>
            <td class="px-3 py-2">#{{ st.shipment_id }}</td>
          </tr>
          <tr v-if="stocks.length===0"><td colspan="3" class="text-center py-6 text-gray-400">No stock.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
const stocks = ref([])
const fetchStocks = async () => {
  const res = await fetch('/stocks')
  const data = await res.json()
  stocks.value = data.data || []
}
onMounted(fetchStocks)
</script> 