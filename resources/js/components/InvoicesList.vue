<template>
  <div class="max-w-4xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Delivery Invoices</h2>
    <div class="bg-white rounded-lg shadow p-4">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">Invoice Date</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Delivered Orders</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Total Amount</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in invoices" :key="invoice.id" class="border-b">
            <td class="px-3 py-2">{{ formatDate(invoice.invoice_date) }}</td>
            <td class="px-3 py-2">{{ invoice.order_count }}</td>
            <td class="px-3 py-2 font-bold">{{ formatAmount(invoice.total_amount) }} FCFA</td>
            <td class="px-3 py-2">
              <button @click="downloadInvoice(invoice)" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Download/View
              </button>
            </td>
          </tr>
          <tr v-if="invoices.length === 0">
            <td colspan="4" class="text-center py-6 text-gray-400">No invoices found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const invoices = ref([])

const fetchInvoices = async () => {
  const res = await fetch('/delivery-invoices')
  if (res.ok) {
    invoices.value = await res.json()
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString()
}
const formatAmount = (n) => Number(n).toLocaleString()

const downloadInvoice = async (invoice) => {
  try {
    const response = await fetch(`/delivery-invoices/${invoice.id}/download`);
    
    if (response.ok) {
      // If it's a successful download, open in new window
      window.open(`/delivery-invoices/${invoice.id}/download`, '_blank');
    } else {
      // Handle error responses
      const errorData = await response.json();
      alert(`Error: ${errorData.error}\n\n${errorData.message}`);
    }
  } catch (error) {
    console.error('Error downloading invoice:', error);
    alert('An error occurred while trying to download the invoice. Please try again.');
  }
}

onMounted(fetchInvoices)
</script> 