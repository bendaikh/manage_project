<template>
  <div class="bg-white p-4 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Action History</h2>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="history in histories" :key="history.id">
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ new Date(history.created_at).toLocaleString() }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ history.user?.name || 'System' }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold">{{ history.title }}</td>
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ history.description }}</td>
          </tr>
          <tr v-if="histories.length === 0">
            <td colspan="4" class="px-4 py-4 text-center text-gray-500">No history records found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="mt-4 flex justify-center gap-2">
      <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">Prev</button>
      <button v-for="page in pagination.last_page" :key="page" @click="changePage(page)" :class="['px-3 py-1 rounded', page===pagination.current_page ? 'bg-blue-600 text-white':'bg-gray-100']">{{ page }}</button>
      <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const histories = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })

const fetchHistories = async (page = 1) => {
  try {
    const res = await fetch(`/api/history?page=${page}`)
    if (res.ok) {
      const data = await res.json()
      histories.value = data.data
      pagination.value = data
    }
  } catch (e) { console.error('Failed to fetch histories', e) }
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  fetchHistories(page)
}

onMounted(() => fetchHistories())
</script> 