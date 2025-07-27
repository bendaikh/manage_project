<template>
  <div>
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Balance Overview</h2>

    <div class="overflow-x-auto bg-white shadow rounded-lg p-6">
      <table class="min-w-full">
        <tbody class="divide-y divide-gray-200">
          <tr>
            <td class="py-3 font-medium text-gray-600">Total Revenue</td>
            <td class="py-3 text-right text-green-700 font-semibold">{{ format(totalIncome) }}</td>
          </tr>
          <tr>
            <td class="py-3 font-medium text-gray-600">Total Expenses</td>
            <td class="py-3 text-right text-red-700 font-semibold">{{ format(totalExpense) }}</td>
          </tr>
          <tr>
            <td class="py-3 font-medium text-gray-800">Balance</td>
            <td
              class="py-3 text-right font-bold"
              :class="balance >= 0 ? 'text-green-800' : 'text-red-800'"
            >
              {{ format(balance) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const totalIncome = ref(0)
const totalExpense = ref(0)
const balance = ref(0)

const format = (value) => {
  const number = Number(value) || 0
  return number.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

onMounted(async () => {
  try {
    const res = await fetch('/accounting/balance-data')
    if (res.ok) {
      const data = await res.json()
      totalIncome.value = data.total_income
      totalExpense.value = data.total_expense
      balance.value = data.balance
    }
  } catch (err) {
    console.error('Failed to load balance data', err)
  }
})
</script> 