<template>
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-8 mt-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">{{ product.name }}</h2>
        <p class="text-gray-500">Product Details</p>
      </div>
      <button @click="$emit('back')" class="flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 text-gray-700">
        ← Back to Marketplace
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Product Image -->
      <div class="space-y-4">
        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
          <img 
            v-if="product.image_url" 
            :src="product.image_url" 
            :alt="product.name" 
            class="w-full h-full object-cover"
            @error="handleImageError"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
        
        <!-- Video if available -->
        <div v-if="product.video_url" class="mt-4">
          <h3 class="text-lg font-semibold mb-2">Product Video</h3>
          <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
            <video 
              :src="product.video_url" 
              controls 
              class="w-full h-full object-cover"
              @error="handleVideoError"
            >
              Your browser does not support the video tag.
            </video>
          </div>
          <p v-if="product.video_duration" class="text-sm text-gray-500 mt-1">
            Duration: {{ product.video_duration }}
          </p>
        </div>
      </div>

      <!-- Product Information -->
      <div class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Product Name</label>
              <p class="text-lg font-semibold">{{ product.name }}</p>
            </div>
            
            <div v-if="product.sku">
              <label class="text-sm font-medium text-gray-600">SKU</label>
              <p class="text-lg">{{ product.sku }}</p>
            </div>
            
            <div v-if="product.category">
              <label class="text-sm font-medium text-gray-600">Category</label>
              <p class="text-lg">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                  {{ product.category }}
                </span>
              </p>
            </div>
            
            
            <div v-if="product.warehouse">
              <label class="text-sm font-medium text-gray-600">Warehouse</label>
              <p class="text-lg">{{ product.warehouse.name }} - {{ product.warehouse.location }}</p>
            </div>
          </div>
        </div>

        <!-- Pricing & Stock -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4">Pricing & Stock</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-600">Selling Price</label>
              <p class="text-lg font-semibold text-green-600">{{ formatCurrency(product.selling_price) }}</p>
            </div>
            
            <div>
              <label class="text-sm font-medium text-gray-600">Stock Quantity</label>
              <p class="text-lg font-semibold">{{ product.stock_quantity }} units</p>
            </div>
            
            <div>
              <label class="text-sm font-medium text-gray-600">Status</label>
              <p class="text-lg">
                <span :class="getStatusClass(product.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium">
                  {{ product.status }}
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div v-if="product.description" class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4">Description</h3>
          <p class="text-gray-700 whitespace-pre-wrap">{{ product.description }}</p>
        </div>

        <!-- Additional Info -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4">Additional Information</h3>
          <div class="grid grid-cols-1 gap-4 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Created</span>
              <span>{{ formatDate(product.created_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Last Updated</span>
              <span>{{ formatDate(product.updated_at) }}</span>
            </div>
            <div v-if="product.is_company_product" class="flex justify-between">
              <span class="text-gray-600">Product Type</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                Company Product
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['back'])

const formatCurrency = (amount) => {
  if (!amount) return 'N/A'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  switch (status) {
    case 'In Stock':
      return 'bg-green-100 text-green-800'
    case 'Low Stock':
      return 'bg-yellow-100 text-yellow-800'
    case 'Out of Stock':
      return 'bg-red-100 text-red-800'
    case 'Discontinued':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

const handleVideoError = (event) => {
  console.warn('Video failed to load:', event.target.src)
}
</script>
