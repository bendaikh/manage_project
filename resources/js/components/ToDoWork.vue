<template>
  <div class="bg-white p-6 rounded-lg shadow mb-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold text-orange-600">To Do Work</h2>
      <button @click="toggleToDoWork" class="text-orange-600 hover:text-orange-800 transition-transform" :class="{ 'rotate-180': showToDoWork }">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
    </div>
    
    <div v-if="showToDoWork" class="space-y-6">
      <!-- Confirmation Section -->
      <div class="border-l-4 border-blue-500 pl-4">
        <h3 class="text-lg font-semibold text-blue-600 mb-3">Confirmation</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- New Order -->
          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center mb-3">
              <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                <span class="text-white text-sm font-bold">🆕</span>
              </div>
              <div>
                <div class="font-medium text-blue-800">New Order</div>
                <div class="text-2xl font-bold text-blue-600">{{ confirmationData.newOrder }}</div>
              </div>
            </div>
            <div class="text-sm text-blue-600">New orders of the day</div>
          </div>

          <!-- Confirm on Date -->
          <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="flex items-center mb-3">
              <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center mr-3">
                <span class="text-white text-sm font-bold">📅</span>
              </div>
              <div>
                <div class="font-medium text-green-800">Confirm on Date</div>
                <div class="text-2xl font-bold text-green-600">{{ confirmationData.confirmOnDate }}</div>
              </div>
            </div>
            <div class="text-sm text-green-600">Orders to confirm today</div>
          </div>

          <!-- Postponed -->
          <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <div class="flex items-center mb-3">
              <div class="w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center mr-3">
                <span class="text-white text-sm font-bold">⏰</span>
              </div>
              <div>
                <div class="font-medium text-yellow-800">Postponed</div>
                <div class="text-2xl font-bold text-yellow-600">{{ confirmationData.postponed }}</div>
              </div>
            </div>
            <div class="text-sm text-yellow-600">Postponed orders</div>
          </div>
        </div>
      </div>

      <!-- Delivery Section -->
      <div class="border-l-4 border-green-500 pl-4">
        <h3 class="text-lg font-semibold text-green-600 mb-3">Delivery</h3>
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
          <!-- Postponed -->
          <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <div class="flex items-center mb-3">
              <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center mr-3">
                <span class="text-white text-sm font-bold">🚚</span>
              </div>
              <div>
                <div class="font-medium text-orange-800">Postponed</div>
                <div class="text-2xl font-bold text-orange-600">{{ deliveryData.postponed }}</div>
              </div>
            </div>
            <div class="text-sm text-orange-600">Postponed orders scheduled for delivery today</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showToDoWork = ref(true)

const confirmationData = ref({
  newOrder: 0,
  confirmOnDate: 0,
  postponed: 0
})

const deliveryData = ref({
  postponed: 0
})

const fetchToDoWorkData = async () => {
  try {
    const response = await fetch('/api/dashboard/todo-work')
    if (response.ok) {
      const data = await response.json()
      confirmationData.value = data.confirmation || { newOrder: 0, confirmOnDate: 0, postponed: 0 }
      deliveryData.value = data.delivery || { postponed: 0 }
    }
  } catch (error) {
    console.error('Failed to fetch todo work data:', error)
  }
}

const toggleToDoWork = () => {
  showToDoWork.value = !showToDoWork.value
}

onMounted(() => {
  fetchToDoWorkData()
})
</script>
