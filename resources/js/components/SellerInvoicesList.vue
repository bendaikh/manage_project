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
            <td class="px-3 py-2 font-bold">{{ formatAmount(invoice.total_amount) }} {{ getCurrency() }}</td>
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
                <button v-if="canMarkAsPaid && !invoice.is_paid" @click="markAsPaid(invoice, 'daily')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  Mark as Paid
                </button>
                <button v-if="canMarkAsPaid && invoice.is_paid" @click="revokePayment(invoice, 'daily')" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-sm flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  Revoke Payment
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
          <select v-if="!isSeller" v-model="weeklySellerFilter" @change="fetchWeeklyInvoices" class="px-3 py-2 border rounded-md text-sm">
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
            <td class="px-3 py-2">
              <div class="font-bold">{{ formatAmount(invoice.total_amount) }} {{ getCurrency() }}</div>
              <div v-if="invoice.advances && invoice.advances.length > 0" class="text-sm text-gray-600 mt-1">
                <!-- Show single advance directly -->
                <div v-if="invoice.advances.length === 1">
                  <div class="text-red-600">- Advance: {{ formatAmount(invoice.advances[0].amount) }} {{ getCurrency() }}</div>
                  <div class="font-semibold text-blue-600">Adjusted: {{ formatAmount(getTotalAfterAdvances(invoice)) }} {{ getCurrency() }}</div>
                </div>
                <!-- Show eye icon for multiple advances -->
                <div v-else class="flex items-center gap-2">
                  <span class="text-red-600">- Advances: {{ formatAmount(getTotalAdvances(invoice)) }} {{ getCurrency() }}</span>
                  <button 
                    @click="openAdvancesModal(invoice)" 
                    class="p-1 hover:bg-gray-200 rounded transition-colors"
                    title="View all advances"
                  >
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <div class="font-semibold text-blue-600">Adjusted: {{ formatAmount(getTotalAfterAdvances(invoice)) }} {{ getCurrency() }}</div>
                </div>
              </div>
            </td>
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
                <button v-if="canMarkAsPaid && invoice.status === 'approved' && !invoice.is_paid" @click="markAsPaid(invoice, 'weekly')" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Mark as Paid
                </button>
                <button v-if="canMarkAsPaid && invoice.status === 'approved' && invoice.is_paid" @click="revokePayment(invoice, 'weekly')" class="px-3 py-1 bg-orange-600 text-white rounded hover:bg-orange-700 text-xs flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                  Revoke Payment
                </button>
                <button v-if="canMarkAsPaid && invoice.status === 'approved'" @click="openChargeAdvanceModal(invoice)" class="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-700 text-xs flex items-center gap-1" title="Charge Advance">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                  </svg>
                  Charge Advance
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

  <!-- Charge Advance Modal -->
  <div v-if="showChargeAdvanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeChargeAdvanceModal">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Charge Advance</h3>
        <button @click="closeChargeAdvanceModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <div v-if="selectedInvoiceForAdvance" class="mb-4 p-3 bg-gray-50 rounded">
        <p class="text-sm text-gray-600">
          <strong>Seller:</strong> {{ selectedInvoiceForAdvance.seller }}<br>
          <strong>Week:</strong> {{ selectedInvoiceForAdvance.week_period }}<br>
          <strong>Total Amount:</strong> {{ formatAmount(selectedInvoiceForAdvance.total_amount) }} {{ getCurrency() }}<br>
          <span v-if="getTotalAdvances(selectedInvoiceForAdvance) > 0">
            <strong>Existing Advances:</strong> <span class="text-red-600">{{ formatAmount(getTotalAdvances(selectedInvoiceForAdvance)) }} {{ getCurrency() }}</span><br>
            <strong>Remaining Available:</strong> <span class="text-blue-600">{{ formatAmount(getTotalAfterAdvances(selectedInvoiceForAdvance)) }} {{ getCurrency() }}</span>
          </span>
        </p>
      </div>

      <form @submit.prevent="submitChargeAdvance">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Advance Amount *</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">{{ getCurrency() }}</span>
            <input 
              v-model="chargeAdvanceForm.advance_amount" 
              type="number" 
              step="0.01" 
              min="0.01" 
              :max="getTotalAfterAdvances(selectedInvoiceForAdvance)"
              required 
              class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="0.00"
            />
          </div>
          <p class="text-xs text-gray-500 mt-1">
            Maximum available: {{ formatAmount(getTotalAfterAdvances(selectedInvoiceForAdvance)) }} {{ getCurrency() }}
          </p>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Note (Optional)</label>
          <textarea 
            v-model="chargeAdvanceForm.advance_note" 
            rows="3" 
            maxlength="500"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter a note for this advance charge..."
          ></textarea>
          <p class="text-xs text-gray-500 mt-1">{{ chargeAdvanceForm.advance_note?.length || 0 }}/500 characters</p>
        </div>

        <div class="flex justify-end gap-3">
          <button 
            type="button" 
            @click="closeChargeAdvanceModal" 
            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            :disabled="isSubmittingChargeAdvance"
            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
          >
            <svg v-if="isSubmittingChargeAdvance" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isSubmittingChargeAdvance ? 'Applying...' : 'Apply Advance' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Advances Modal -->
  <div v-if="showAdvancesModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeAdvancesModal">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Advance Charges</h3>
        <button @click="closeAdvancesModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <div v-if="selectedInvoiceForAdvances" class="mb-4 p-3 bg-gray-50 rounded">
        <p class="text-sm text-gray-600">
          <strong>Seller:</strong> {{ selectedInvoiceForAdvances.seller }}<br>
          <strong>Week:</strong> {{ selectedInvoiceForAdvances.week_period }}<br>
          <strong>Total Amount:</strong> {{ formatAmount(selectedInvoiceForAdvances.total_amount) }} {{ getCurrency() }}
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-bold">#</th>
              <th class="px-4 py-2 text-left text-xs font-bold">Amount</th>
              <th class="px-4 py-2 text-left text-xs font-bold">Date</th>
              <th class="px-4 py-2 text-left text-xs font-bold">Note</th>
              <th class="px-4 py-2 text-left text-xs font-bold">Created By</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(advance, index) in selectedInvoiceForAdvances?.advances || []" :key="advance.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-2 text-sm">{{ index + 1 }}</td>
              <td class="px-4 py-2 text-sm font-semibold text-red-600">
                {{ formatAmount(advance.amount) }} {{ getCurrency() }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-600">
                {{ formatDate(advance.created_at) }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ advance.note || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-600">
                {{ advance.creator?.name || 'N/A' }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50 font-bold">
            <tr>
              <td colspan="1" class="px-4 py-3 text-right text-sm">Total Advances:</td>
              <td colspan="4" class="px-4 py-3 text-sm text-red-600">
                {{ formatAmount(getTotalAdvances(selectedInvoiceForAdvances)) }} {{ getCurrency() }}
              </td>
            </tr>
            <tr>
              <td colspan="1" class="px-4 py-3 text-right text-sm">Final Amount Due:</td>
              <td colspan="4" class="px-4 py-3 text-sm text-blue-600">
                {{ formatAmount(getTotalAfterAdvances(selectedInvoiceForAdvances)) }} {{ getCurrency() }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="flex justify-end mt-6">
        <button 
          @click="closeAdvancesModal" 
          class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useCurrency } from '../composables/useCurrency'

const { getCurrency } = useCurrency()

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

// Check if current user has permission to mark invoices as paid
const canMarkAsPaid = computed(() => {
  const permissions = window.Laravel?.user?.permissions || []
  return permissions.includes('mark_seller_invoices_paid') || 
         permissions.some(p => typeof p === 'object' && p.name === 'mark_seller_invoices_paid')
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

// Charge Advance Modal
const showChargeAdvanceModal = ref(false)
const selectedInvoiceForAdvance = ref(null)
const isSubmittingChargeAdvance = ref(false)
const chargeAdvanceForm = ref({
  advance_amount: '',
  advance_note: ''
})

// View Advances Modal
const showAdvancesModal = ref(false)
const selectedInvoiceForAdvances = ref(null)

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

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-GB', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTotalAdvances = (invoice) => {
  if (!invoice.advances || invoice.advances.length === 0) return 0
  return invoice.advances.reduce((sum, advance) => sum + parseFloat(advance.amount), 0)
}

const getTotalAfterAdvances = (invoice) => {
  return invoice.total_amount - getTotalAdvances(invoice)
}

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

const revokePayment = async (invoice, type) => {
  if (!confirm(`Are you sure you want to revoke the payment status for this ${type} invoice? This will mark it as unpaid.`)) {
    return
  }

  try {
    const endpoint = type === 'weekly' 
      ? `/weekly-seller-invoices/${invoice.id}/revoke-payment`
      : `/seller-invoices/${invoice.id}/revoke-payment`
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    if (response.ok) {
      alert(`${type.charAt(0).toUpperCase() + type.slice(1)} invoice payment status revoked successfully`)
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
    console.error('Revoke payment error:', error)
    alert(`Failed to revoke payment status for ${type} invoice`)
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

// View Advances Modal Functions
const openAdvancesModal = (invoice) => {
  selectedInvoiceForAdvances.value = invoice
  showAdvancesModal.value = true
}

const closeAdvancesModal = () => {
  showAdvancesModal.value = false
  selectedInvoiceForAdvances.value = null
}

// Charge Advance Functions
const openChargeAdvanceModal = (invoice) => {
  selectedInvoiceForAdvance.value = invoice
  chargeAdvanceForm.value = {
    advance_amount: '',
    advance_note: ''
  }
  showChargeAdvanceModal.value = true
}

const closeChargeAdvanceModal = () => {
  showChargeAdvanceModal.value = false
  selectedInvoiceForAdvance.value = null
  chargeAdvanceForm.value = {
    advance_amount: '',
    advance_note: ''
  }
}

const submitChargeAdvance = async () => {
  if (!selectedInvoiceForAdvance.value) return

  isSubmittingChargeAdvance.value = true

  try {
    const response = await fetch(`/weekly-seller-invoices/${selectedInvoiceForAdvance.value.id}/charge-advance`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        advance_amount: parseFloat(chargeAdvanceForm.value.advance_amount),
        advance_note: chargeAdvanceForm.value.advance_note
      })
    })

    if (response.ok) {
      const data = await response.json()
      alert('Charge advance applied successfully!')
      closeChargeAdvanceModal()
      fetchWeeklyInvoices() // Refresh the invoices list
    } else {
      const errorData = await response.json()
      alert(`Error: ${errorData.error || 'Failed to apply charge advance'}`)
    }
  } catch (error) {
    console.error('Charge advance error:', error)
    alert('Failed to apply charge advance')
  } finally {
    isSubmittingChargeAdvance.value = false
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