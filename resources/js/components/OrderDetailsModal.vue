<template>
  <div v-if="order" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg relative">
      <div class="bg-blue-900 text-white rounded-t-xl px-6 py-4 flex items-center justify-between">
        <span class="text-lg font-bold">Order Details</span>
        <button @click="$emit('close')" class="text-white text-2xl leading-none">&times;</button>
      </div>
      <div class="p-6">
        <div class="flex items-center justify-between mb-2">
          <div class="font-bold text-lg">Order #CM{{ order.id }}</div>
          <span v-if="order.status" :class="statusClass(order.status)">{{ order.status }}</span>
        </div>
        <div class="flex items-center text-xs text-gray-500 mb-4 gap-4">
          <span><svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Created: {{ formatDate(order.created_at) }}</span>
          <span><svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12h.01M12 15h.01M9 12h.01M12 9h.01M12 12a9 9 0 110-18 9 9 0 010 18z"/></svg> Updated: {{ formatDate(order.updated_at) }}</span>
        </div>
        <hr class="my-3">
        <div class="grid grid-cols-2 gap-4 mb-2">
          <div>
            <div class="font-semibold text-gray-600 mb-1">Order Information</div>
            <div><span class="font-medium">Seller</span>: {{ order.seller }}</div>
            <div><span class="font-medium">Product</span>: {{ order.product ? order.product.name : '' }}</div>
            <div><span class="font-medium">Price</span>: <span class="font-bold">{{ order.price }} FCFA</span></div>
            <div><span class="font-medium">Agent</span>: {{ order.agent || 'Mme' }}</div>
          </div>
          <div>
            <div class="font-semibold text-gray-600 mb-1">Client Information</div>
            <div><span class="font-medium">Name</span>: {{ order.client_name }}</div>
            <div><span class="font-medium">Address</span>: {{ order.client_address }}</div>
            <div><span class="font-medium">Phone</span>: {{ order.client_phone }}</div>
          </div>
        </div>
        <div class="mb-2">
          <div class="font-semibold text-gray-600 mb-1">Comment</div>
          <div class="bg-gray-100 rounded px-3 py-2 text-gray-700">{{ order.comment }}</div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="$emit('close')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Close</button>
          <button @click="$emit('edit', order)" class="px-6 py-2 bg-blue-900 text-white rounded hover:bg-blue-800 font-semibold">Edit Order</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ order: Object })
const emit = defineEmits(['close', 'edit'])

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString()
}
function statusClass(status) {
  if (status === 'Confirmed') return 'bg-green-100 text-green-700 px-2 py-1 rounded text-xs'
  if (status === 'Delivered') return 'bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs'
  if (status === 'Cancelled') return 'bg-red-100 text-red-700 px-2 py-1 rounded text-xs'
  if (status === 'Postponed') return 'bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs'
  return 'bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs'
}
</script> 