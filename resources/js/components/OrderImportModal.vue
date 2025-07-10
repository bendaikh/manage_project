<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 p-4">
    <div class="relative top-4 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            Import Orders from Spreadsheet
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 p-1">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Instructions -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
          <h4 class="font-medium text-blue-900 mb-2">Instructions:</h4>
          <ul class="text-sm text-blue-800 space-y-1">
            <li>• Upload an Excel (.xlsx, .xls) or CSV file</li>
            <li>• File should include headers: product_sku, seller_username, client_name, client_phone, client_address, quantity, price</li>
            <li>• Optional fields: notes</li>
            <li>• Maximum file size: 10MB</li>
            <li>• Invalid rows will be skipped and reported</li>
          </ul>
        </div>

        <!-- File Upload -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Select File
          </label>
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <input
              type="file"
              ref="fileInput"
              @change="handleFileSelect"
              accept=".xlsx,.xls,.csv"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            <button
              @click="downloadTemplate"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm whitespace-nowrap"
            >
              Download Template
            </button>
          </div>
          <p v-if="selectedFile" class="mt-2 text-sm text-gray-600">
            Selected: {{ selectedFile.name }} ({{ formatFileSize(selectedFile.size) }})
          </p>
        </div>

        <!-- Import Stats -->
        <div v-if="importStats" class="mb-6 p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-2">System Information:</h4>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-600">Total Products:</span>
              <span class="font-medium ml-2">{{ importStats.total_products }}</span>
            </div>
            <div>
              <span class="text-gray-600">Total Sellers:</span>
              <span class="font-medium ml-2">{{ importStats.total_sellers }}</span>
            </div>
            <div>
              <span class="text-gray-600">Total Orders:</span>
              <span class="font-medium ml-2">{{ importStats.total_orders }}</span>
            </div>
            <div>
              <span class="text-gray-600">Order Statuses:</span>
              <span class="font-medium ml-2">{{ importStats.order_statuses.join(', ') }}</span>
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div v-if="isImporting" class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Importing...</span>
            <span class="text-sm text-gray-500">{{ importProgress }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: importProgress + '%' }"></div>
          </div>
        </div>

        <!-- Results -->
        <div v-if="importResults" class="mb-6">
          <div class="p-4 rounded-lg" :class="importResults.success ? 'bg-green-50' : 'bg-red-50'">
            <h4 class="font-medium mb-2" :class="importResults.success ? 'text-green-900' : 'text-red-900'">
              {{ importResults.message }}
            </h4>
            
            <div class="text-sm space-y-2">
              <div class="flex justify-between">
                <span>Orders Added:</span>
                <span class="font-medium text-green-600">{{ importResults.results.success_count }}</span>
              </div>
              <div class="flex justify-between">
                <span>Rows Failed:</span>
                <span class="font-medium text-red-600">{{ importResults.results.error_count }}</span>
              </div>
            </div>

            <!-- Error Details -->
            <div v-if="importResults.results.errors && importResults.results.errors.length > 0" class="mt-4">
              <h5 class="font-medium text-red-900 mb-2">Error Details:</h5>
              <div class="max-h-32 overflow-y-auto">
                <ul class="text-sm text-red-800 space-y-1">
                  <li v-for="(error, index) in importResults.results.errors.slice(0, 10)" :key="index">
                    {{ error }}
                  </li>
                  <li v-if="importResults.results.errors.length > 10" class="text-gray-600">
                    ... and {{ importResults.results.errors.length - 10 }} more errors
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row justify-end gap-3">
          <button
            @click="closeModal"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 text-sm lg:text-base"
          >
            Close
          </button>
          <button
            @click="importOrders"
            :disabled="!selectedFile || isImporting"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm lg:text-base"
          >
            {{ isImporting ? 'Importing...' : 'Import Orders' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  show: Boolean
})

const emit = defineEmits(['close', 'orders-imported'])

const fileInput = ref(null)
const selectedFile = ref(null)
const isImporting = ref(false)
const importProgress = ref(0)
const importResults = ref(null)
const importStats = ref(null)

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
    importResults.value = null
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const downloadTemplate = () => {
  window.open('/orders/import/template', '_blank')
}

const importOrders = async () => {
  if (!selectedFile.value) return

  isImporting.value = true
  importProgress.value = 0
  importResults.value = null

  const formData = new FormData()
  formData.append('orders_file', selectedFile.value)

  try {
    // Simulate progress
    const progressInterval = setInterval(() => {
      if (importProgress.value < 90) {
        importProgress.value += 10
      }
    }, 200)

    const response = await fetch('/orders/import', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    clearInterval(progressInterval)
    importProgress.value = 100

    const result = await response.json()
    importResults.value = result

    if (result.success && result.results.success_count > 0) {
      emit('orders-imported', result.results.success_count)
    }

  } catch (error) {
    console.error('Import error:', error)
    importResults.value = {
      success: false,
      message: 'Import failed: ' + error.message,
      results: {
        success_count: 0,
        error_count: 1,
        errors: [error.message]
      }
    }
  } finally {
    isImporting.value = false
  }
}

const closeModal = () => {
  selectedFile.value = null
  importResults.value = null
  importProgress.value = 0
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  emit('close')
}

const fetchImportStats = async () => {
  try {
    const response = await fetch('/orders/import/stats')
    if (response.ok) {
      importStats.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch import stats:', error)
  }
}

onMounted(() => {
  fetchImportStats()
})
</script> 