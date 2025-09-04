<template>
  <div class="bg-white p-4 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Action History</h2>

    <!-- Filter Section -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-700">Filters</h3>
        <button 
          @click="clearFilters" 
          class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
          Clear All Filters
        </button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
          <input 
            v-model="filters.dateFrom" 
            type="date" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
          <input 
            v-model="filters.dateTo" 
            type="date" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        
        <!-- User Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
          <select 
            v-model="filters.userId" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Users</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
        
        <!-- Title Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
          <input 
            v-model="filters.title" 
            type="text" 
            placeholder="Search in title..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>
      
      <!-- Description Filter (Full Width) -->
      <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <input 
          v-model="filters.description" 
          type="text" 
          placeholder="Search in description..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
      
      <!-- Filter Actions -->
      <div class="mt-4 flex justify-end gap-2">
        <button 
          @click="applyFilters" 
          class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Apply Filters
        </button>
        <button 
          @click="clearFilters" 
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">Per page:</label>
          <select v-model="perPage" @change="changePerPage" class="px-2 py-1 border rounded text-sm">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
        <div class="text-sm text-gray-600">
          Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, pagination.total || 0) }} of {{ pagination.total || 0 }} entries
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600">Go to page:</span>
        <input 
          v-model="goToPage" 
          @keyup.enter="goToPageNumber"
          type="number" 
          :min="1" 
          :max="totalPages"
          class="w-16 px-2 py-1 border rounded text-sm text-center"
          placeholder="Page"
        />
        <button @click="goToPageNumber" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
          Go
        </button>
      </div>
    </div>

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

    <!-- Advanced Pagination -->
    <nav v-if="totalPages > 1" class="flex justify-center mt-6">
      <ul class="inline-flex">
        <li>
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage === 1" 
            class="px-3 py-2 border rounded-l-md text-sm font-medium"
            :class="currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'"
          >
            &laquo; Previous
          </button>
        </li>
        <li v-for="page in pageNumbers" :key="page">
          <button 
            v-if="page !== '...'"
            @click="changePage(page)" 
            class="px-3 py-2 border-t border-b text-sm font-medium"
            :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
          >
            {{ page }}
          </button>
          <span 
            v-else
            class="px-3 py-2 border-t border-b bg-white text-gray-700 text-sm"
          >
            ...
          </span>
        </li>
        <li>
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage === totalPages" 
            class="px-3 py-2 border rounded-r-md text-sm font-medium"
            :class="currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50'"
          >
            Next &raquo;
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const histories = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const users = ref([])

// Filter state
const filters = ref({
  dateFrom: '',
  dateTo: '',
  userId: '',
  title: '',
  description: ''
})

// Pagination state
const currentPage = ref(1)
const perPage = ref(10)
const totalPages = ref(1)
const goToPage = ref('')

// Computed properties
const pageNumbers = computed(() => {
  const pages = []
  const current = currentPage.value
  const total = totalPages.value
  
  // If we have 10 or fewer pages, show all
  if (total <= 10) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
    return pages
  }
  
  // Always show first page
  pages.push(1)
  
  // Calculate range around current page
  const start = Math.max(2, current - 2)
  const end = Math.min(total - 1, current + 2)
  
  // Add ellipsis if there's a gap after first page
  if (start > 2) {
    pages.push('...')
  }
  
  // Add pages around current page
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  // Add ellipsis if there's a gap before last page
  if (end < total - 1) {
    pages.push('...')
  }
  
  // Always show last page (if not already included)
  if (total > 1) {
    pages.push(total)
  }
  
  return pages
})

const fetchHistories = async (page = 1) => {
  try {
    // Build query parameters
    const params = new URLSearchParams({
      page: page,
      per_page: perPage.value
    })
    
    // Add filter parameters
    if (filters.value.dateFrom) params.append('date_from', filters.value.dateFrom)
    if (filters.value.dateTo) params.append('date_to', filters.value.dateTo)
    if (filters.value.userId) params.append('user_id', filters.value.userId)
    if (filters.value.title) params.append('title', filters.value.title)
    if (filters.value.description) params.append('description', filters.value.description)
    
    const res = await fetch(`/api/history?${params.toString()}`)
    if (res.ok) {
      const data = await res.json()
      histories.value = data.data
      pagination.value = data
      
      // Update pagination state
      totalPages.value = data.last_page
      currentPage.value = data.current_page
    }
  } catch (e) { 
    console.error('Failed to fetch histories', e) 
  }
}

const fetchUsers = async () => {
  try {
    const res = await fetch('/api/history/users')
    if (res.ok) {
      users.value = await res.json()
    }
  } catch (e) {
    console.error('Failed to fetch users', e)
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchHistories(1)
}

const clearFilters = () => {
  filters.value = {
    dateFrom: '',
    dateTo: '',
    userId: '',
    title: '',
    description: ''
  }
  currentPage.value = 1
  fetchHistories(1)
}

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  fetchHistories(page)
}

const changePerPage = () => {
  currentPage.value = 1
  fetchHistories(1)
}

const goToPageNumber = () => {
  const page = parseInt(goToPage.value)
  if (page >= 1 && page <= totalPages.value) {
    changePage(page)
    goToPage.value = ''
  }
}

onMounted(() => {
  fetchHistories()
  fetchUsers()
})
</script> 