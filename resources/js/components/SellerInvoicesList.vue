<template>
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Sellers Invoices</h2>
      <div v-if="!isSeller" class="flex gap-2">
        <button @click="activeTab = 'daily'" :class="['px-4 py-2 rounded', activeTab === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Daily Invoices
        </button>
        <button @click="activeTab = 'weekly'" :class="['px-4 py-2 rounded', activeTab === 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']">
          Weekly Invoices
        </button>
      </div>
    </div>

    <!-- Daily Invoices Tab (only for non-sellers) -->
    <div v-if="!isSeller && activeTab === 'daily'" class="bg-white rounded-lg shadow p-4 overflow-x-auto">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Daily Invoices</h3>
        <div class="flex gap-2">
          <select v-model="dailySellerFilter" @change="fetchDailyInvoices" class="px-3 py-2 border rounded-md text-sm">
            <option value="">All Sellers</option>
            <option v-for="seller in dailySellers" :key="seller" :value="seller">{{ seller }}</option>
          </select>
        </div>
      </div>
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">Seller</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Invoice Date</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Delivered Orders</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Total Amount</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Status</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in dailyInvoices" :key="invoice.id" class="border-b">
            <td class="px-3 py-2">{{ invoice.seller }}</td>
            <td class="px-3 py-2">{{ invoice.invoice_date }}</td>
            <td class="px-3 py-2">{{ invoice.order_count }}</td>
            <td class="px-3 py-2 font-bold">{{ formatAmount(invoice.total_amount) }} FCFA</td>
            <td class="px-3 py-2">
              <span :class="invoice.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ invoice.is_paid ? 'Paid' : 'Unpaid' }}
              </span>
            </td>
            <td class="px-3 py-2">
              <div class="flex gap-2">
                <button @click="downloadInvoice(invoice, 'daily')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  Download/View
                </button>
                <button v-if="!invoice.is_paid" @click="markAsPaid(invoice, 'daily')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  Mark as Paid
                </button>
                <button v-if="isSuperadmin" @click="deleteInvoice(invoice, 'daily')" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm flex items-center gap-1" title="Delete Invoice">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="dailyInvoices.length === 0">
            <td colspan="6" class="text-center py-6 text-gray-400">No daily invoices found.</td>
          </tr>
        </tbody>
      </table>
      <!-- Pagination -->
      <nav v-if="dailyTotalPages > 1" class="flex justify-center mt-4">
        <ul class="inline-flex">
          <li>
            <button @click="changeDailyPage(dailyCurrentPage-1)" :disabled="dailyCurrentPage===1" class="px-3 py-1 border rounded-l" :class="dailyCurrentPage===1?'bg-gray-200 text-gray-400 cursor-not-allowed':'bg-white hover:bg-gray-100'">&laquo;</button>
          </li>
          <li v-for="page in dailyPageNumbers" :key="page">
            <button @click="changeDailyPage(page)" class="px-3 py-1 border-t border-b" :class="page===dailyCurrentPage?'bg-blue-600 text-white':'bg-white hover:bg-gray-100'">{{ page }}</button>
          </li>
          <li>
            <button @click="changeDailyPage(dailyCurrentPage+1)" :disabled="dailyCurrentPage===dailyTotalPages" class="px-3 py-1 border rounded-r" :class="dailyCurrentPage===dailyTotalPages?'bg-gray-200 text-gray-400 cursor-not-allowed':'bg-white hover:bg-gray-100'">&raquo;</button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Weekly Invoices Tab -->
    <div v-if="activeTab === 'weekly'" class="bg-white rounded-lg shadow p-4 overflow-x-auto">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Weekly Invoices</h3>
        <div class="flex gap-2">
          <select v-model="weeklySellerFilter" @change="fetchWeeklyInvoices" class="px-3 py-2 border rounded-md text-sm">
            <option value="">All Sellers</option>
            <option v-for="seller in weeklySellers" :key="seller" :value="seller">{{ seller }}</option>
          </select>
          <button v-if="!isSeller" @click="showGenerateModal = true" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
            Generate Weekly Invoices
          </button>
        </div>
      </div>
      
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">Seller</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Week Period</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Orders</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Total Amount</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Status</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Payment</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in weeklyInvoices" :key="invoice.id" class="border-b">
            <td class="px-3 py-2">{{ invoice.seller }}</td>
            <td class="px-3 py-2">{{ invoice.week_period }}</td>
            <td class="px-3 py-2">{{ invoice.order_count }}</td>
            <td class="px-3 py-2 font-bold">{{ formatAmount(invoice.total_amount) }} FCFA</td>
            <td class="px-3 py-2">
              <span :class="getStatusClass(invoice.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ invoice.status }}
              </span>
            </td>
            <td class="px-3 py-2">
              <span :class="invoice.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ invoice.is_paid ? 'Paid' : 'Unpaid' }}
              </span>
            </td>
            <td class="px-3 py-2">
              <div class="flex gap-2">
                <button v-if="invoice.status === 'pending'" @click="downloadInvoice(invoice, 'weekly')" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Preview PDF
                </button>
                <button v-if="invoice.status === 'pending'" @click="approveInvoice(invoice)" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs">
                  Approve
                </button>
                <button v-if="invoice.status === 'pending'" @click="rejectInvoice(invoice)" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                  Reject
                </button>
                <button v-if="invoice.status === 'approved'" @click="downloadInvoice(invoice, 'weekly')" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Download PDF
                </button>
                <button v-if="invoice.status === 'approved' && !invoice.is_paid" @click="markAsPaid(invoice, 'weekly')" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Mark as Paid
                </button>
                <button v-if="isSuperadmin" @click="deleteInvoice(invoice, 'weekly')" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs flex items-center gap-1" title="Delete Invoice">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="weeklyInvoices.length === 0">
            <td colspan="7" class="text-center py-6 text-gray-400">No weekly invoices found.</td>
          </tr>
        </tbody>
      </table>
      
      <!-- Weekly Pagination -->
      <nav v-if="weeklyTotalPages > 1" class="flex justify-center mt-4">
        <ul class="inline-flex">
          <li>
            <button @click="changeWeeklyPage(weeklyCurrentPage-1)" :disabled="weeklyCurrentPage===1" class="px-3 py-1 border rounded-l" :class="weeklyCurrentPage===1?'bg-gray-200 text-gray-400 cursor-not-allowed':'bg-white hover:bg-gray-100'">&laquo;</button>
          </li>
          <li v-for="page in weeklyPageNumbers" :key="page">
            <button @click="changeWeeklyPage(page)" class="px-3 py-1 border-t border-b" :class="page===weeklyCurrentPage?'bg-blue-600 text-white':'bg-white hover:bg-gray-100'">{{ page }}</button>
          </li>
          <li>
            <button @click="changeWeeklyPage(weeklyCurrentPage+1)" :disabled="weeklyCurrentPage===weeklyTotalPages" class="px-3 py-1 border rounded-r" :class="weeklyCurrentPage===weeklyTotalPages?'bg-gray-200 text-gray-400 cursor-not-allowed':'bg-white hover:bg-gray-100'">&raquo;</button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Generate Weekly Invoices Modal -->
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Generate Weekly Invoices</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Week Start Date</label>
          <input v-model="generateForm.weekStart" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Week End Date</label>
          <input v-model="generateForm.weekEnd" type="date" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="flex justify-end gap-2">
          <button @click="showGenerateModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
            Cancel
          </button>
          <button @click="generateWeeklyInvoices" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Generate
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'

// State
const activeTab = ref('daily')

// Check if current user is a seller
const isSeller = computed(() => {
  const roles = window.Laravel?.user?.roles || []
  return roles.includes('seller') || roles.some(r => typeof r === 'object' && r.name === 'seller')
})

// Check if current user is superadmin
const isSuperadmin = computed(() => {
  const roles = window.Laravel?.user?.roles || []
  return roles.includes('superadmin') || roles.some(r => typeof r === 'object' && r.name === 'superadmin')
})
const dailyInvoices = ref([])
const weeklyInvoices = ref([])
const dailySellers = ref([])
const weeklySellers = ref([])
const dailySellerFilter = ref('')
const weeklySellerFilter = ref('')
const showGenerateModal = ref(false)
const generateForm = ref({
  weekStart: '',
  weekEnd: ''
})

// Daily invoices pagination
const dailyCurrentPage = ref(1)
const dailyTotalPages = ref(1)
const perPage = ref(10)

// Weekly invoices pagination
const weeklyCurrentPage = ref(1)
const weeklyTotalPages = ref(1)

// Computed properties
const dailyPageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= dailyTotalPages.value; i++) pages.push(i)
  return pages
})

const weeklyPageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= weeklyTotalPages.value; i++) pages.push(i)
  return pages
})

// Methods
const fetchDailyInvoices = async () => {
  let url = `/seller-invoices?page=${dailyCurrentPage.value}&per_page=${perPage.value}`
  if (dailySellerFilter.value) {
    url += `&seller=${encodeURIComponent(dailySellerFilter.value)}`
  }
  const res = await fetch(url)
  if (res.ok) {
    const data = await res.json()
    dailyInvoices.value = data.data
    dailyTotalPages.value = data.last_page
    dailyCurrentPage.value = data.current_page
  }
}

const fetchWeeklyInvoices = async () => {
  let url = `/weekly-seller-invoices?page=${weeklyCurrentPage.value}&per_page=${perPage.value}`
  if (weeklySellerFilter.value) {
    url += `&seller=${encodeURIComponent(weeklySellerFilter.value)}`
  }
  const res = await fetch(url)
  if (res.ok) {
    const data = await res.json()
    weeklyInvoices.value = data.data
    weeklyTotalPages.value = data.last_page
    weeklyCurrentPage.value = data.current_page
  }
}

const changeDailyPage = (page) => {
  if (page < 1 || page > dailyTotalPages.value) return
  dailyCurrentPage.value = page
  fetchDailyInvoices()
}

const changeWeeklyPage = (page) => {
  if (page < 1 || page > weeklyTotalPages.value) return
  weeklyCurrentPage.value = page
  fetchWeeklyInvoices()
}

const formatAmount = (n) => Number(n).toLocaleString()

const fetchDailySellers = async () => {
  const res = await fetch('/seller-invoices/sellers')
  if (res.ok) {
    dailySellers.value = await res.json()
  }
}

const fetchWeeklySellers = async () => {
  const res = await fetch('/weekly-seller-invoices/sellers')
  if (res.ok) {
    weeklySellers.value = await res.json()
  }
}

const markAsPaid = async (invoice, type) => {
  if (!confirm(`Are you sure you want to mark this ${type} invoice as paid?`)) {
    return
  }

  try {
    const endpoint = type === 'weekly' 
      ? `/weekly-seller-invoices/${invoice.id}/mark-paid`
      : `/seller-invoices/${invoice.id}/mark-paid`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    if (response.ok) {
      alert(`${type.charAt(0).toUpperCase() + type.slice(1)} invoice marked as paid successfully`)
      if (type === 'weekly') {
        fetchWeeklyInvoices()
      } else {
        fetchDailyInvoices()
      }
    } else {
      const errorData = await response.json()
      alert(`Error: ${errorData.error}`)
    }
  } catch (error) {
    console.error('Mark as paid error:', error)
    alert(`Failed to mark ${type} invoice as paid`)
  }
}

const getStatusClass = (status) => {
  switch (status) {
    case 'approved':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const downloadInvoice = async (invoice, type) => {
  try {
    const url = type === 'weekly' 
      ? `/weekly-seller-invoices/${invoice.id}/download`
      : `/seller-invoices/${invoice.id}/download`
    
    window.open(url, '_blank')
  } catch (error) {
    console.error('Download error:', error)
    alert('Failed to download invoice')
  }
}

const generateWeeklyInvoices = async () => {
  if (!generateForm.value.weekStart || !generateForm.value.weekEnd) {
    alert('Please select both start and end dates')
    return
  }

  try {
    const response = await fetch('/weekly-seller-invoices/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        week_start: generateForm.value.weekStart,
        week_end: generateForm.value.weekEnd
      })
    })

    if (response.ok) {
      const data = await response.json()
      alert(`Successfully generated ${data.count} weekly invoices`)
      showGenerateModal.value = false
      generateForm.value = { weekStart: '', weekEnd: '' }
      fetchWeeklyInvoices()
    } else {
      const errorData = await response.json()
      alert(`Error: ${errorData.error}`)
    }
  } catch (error) {
    console.error('Generate error:', error)
    alert('Failed to generate weekly invoices')
  }
}

const approveInvoice = async (invoice) => {
  if (!confirm(`Are you sure you want to approve the weekly invoice for ${invoice.seller}?`)) {
    return
  }

  try {
    const response = await fetch(`/weekly-seller-invoices/${invoice.id}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    if (response.ok) {
      alert('Weekly invoice approved successfully')
      fetchWeeklyInvoices()
    } else {
      const errorData = await response.json()
      alert(`Error: ${errorData.error}`)
    }
  } catch (error) {
    console.error('Approve error:', error)
    alert('Failed to approve weekly invoice')
  }
}

const rejectInvoice = async (invoice) => {
  const notes = prompt('Please provide a reason for rejection (optional):')
  if (notes === null) return // User cancelled

  try {
    const response = await fetch(`/weekly-seller-invoices/${invoice.id}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ notes })
    })

    if (response.ok) {
      alert('Weekly invoice rejected')
      fetchWeeklyInvoices()
    } else {
      const errorData = await response.json()
      alert(`Error: ${errorData.error}`)
    }
  } catch (error) {
    console.error('Reject error:', error)
    alert('Failed to reject weekly invoice')
  }
}

const deleteInvoice = async (invoice, type) => {
  const invoiceType = type === 'weekly' ? 'weekly invoice' : 'daily invoice'
  const period = type === 'weekly' ? `(${invoice.week_period})` : `(${invoice.invoice_date})`
  
  if (!confirm(`Are you sure you want to delete the ${invoiceType} for ${invoice.seller} ${period}? This action cannot be undone.`)) {
    return
  }

  try {
    const url = type === 'weekly' 
      ? `/weekly-seller-invoices/${invoice.id}`
      : `/seller-invoices/${invoice.id}`
    
    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    if (response.ok) {
      alert(`${invoiceType.charAt(0).toUpperCase() + invoiceType.slice(1)} deleted successfully`)
      if (type === 'weekly') {
        fetchWeeklyInvoices()
      } else {
        fetchDailyInvoices()
      }
    } else {
      const errorData = await response.json()
      alert(`Error: ${errorData.error}`)
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert(`Failed to delete ${invoiceType}`)
  }
}

// Watch for tab changes
watch(activeTab, (newTab) => {
  if (newTab === 'daily') {
    fetchDailyInvoices()
  } else if (newTab === 'weekly') {
    fetchWeeklyInvoices()
  }
})

onMounted(() => {
  // Set default tab based on user role
  if (isSeller.value) {
    activeTab.value = 'weekly'
    fetchWeeklyInvoices()
  } else {
    fetchDailyInvoices()
    fetchDailySellers()
  }
  fetchWeeklySellers()
})
</script> 