<template>
  <div class="max-w-7xl mx-auto bg-white rounded-lg shadow p-6 mt-4">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-xl font-bold">Product Offers</h2>
        <p class="text-gray-500 text-sm">Products validated from shipments</p>
      </div>
      <div class="flex items-center space-x-2">
        <input v-model="search" @keyup.enter="fetchData" placeholder="Search name, sku, seller" class="border rounded px-3 py-2 text-sm" />
        <button @click="fetchData" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Search</button>
        <button @click="refreshData" class="px-3 py-2 bg-green-600 text-white rounded text-sm flex items-center gap-1" title="Refresh data">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Details</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">Toggle</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="p in products.data" :key="p.id">
            <!-- Product Details Column -->
            <td class="px-4 py-3">
              <div class="flex items-start space-x-4">
                <!-- Product Image -->
                <div class="flex-shrink-0">
                  <img 
                    v-if="p.image_url" 
                    :src="p.image_url" 
                    :alt="p.name"
                    class="w-16 h-16 object-cover rounded-lg border"
                  />
                  <div 
                    v-else 
                    class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center"
                  >
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                </div>
                
                <!-- Product Info -->
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 truncate">{{ p.name }}</div>
                  <div class="text-sm text-gray-500">
                    <span class="font-medium">SKU:</span> {{ p.sku }}
                  </div>
                  <div class="text-sm text-gray-500" v-if="p.description">
                    <span class="font-medium">Description:</span> 
                    <span class="truncate">{{ p.description.length > 100 ? p.description.substring(0, 100) + '...' : p.description }}</span>
                  </div>
                  <div class="text-sm text-gray-500">
                    <span class="font-medium">Seller:</span> {{ p.seller_name || 'N/A' }}
                  </div>
                  <div class="text-sm text-gray-500" v-if="p.stock_quantity !== null">
                    <span class="font-medium">Stock:</span> {{ p.stock_quantity }}
                  </div>
                </div>
              </div>
            </td>
            
            <!-- Toggle Column -->
            <td class="px-4 py-3 text-center">
              <div class="flex flex-col items-center space-y-2">
                <!-- Toggle Switch/Slider -->
                <input 
                  type="range" 
                  min="0" 
                  max="1" 
                  :value="p.is_company_product ? 1 : 0" 
                  :disabled="!p.can_toggle" 
                  @input="onToggle(p, $event)" 
                  class="w-16 h-6 appearance-none bg-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                  :class="p.is_company_product ? 'bg-green-400' : 'bg-gray-300'"
                />
                
                <!-- Status indicator -->
                <div class="w-3 h-3 rounded-full" :class="p.is_company_product ? 'bg-green-500' : 'bg-gray-400'"></div>
              </div>
            </td>
            
            <!-- Status Column -->
            <td class="px-4 py-3">
              <div class="flex flex-col space-y-2">
                <span 
                  :class="p.is_company_product ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" 
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ p.is_company_product ? 'Active' : 'Inactive' }}
                </span>
                
                <span class="text-xs text-gray-500">
                  {{ p.is_company_product ? 'Visible in marketplace' : 'Hidden from marketplace' }}
                </span>
                
                <span v-if="p.assigned_sellers_count" class="text-xs text-blue-600">
                  👥 {{ p.assigned_sellers_count }} sellers can see this
                </span>
              </div>
            </td>
          </tr>
          <tr v-if="products.data.length === 0">
            <td colspan="3" class="text-center py-8 text-gray-400">No products found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <nav v-if="products.last_page > 1" class="flex justify-center mt-4">
      <ul class="inline-flex">
        <li>
          <button class="px-3 py-2 border rounded-l hover:bg-gray-50" :disabled="page === 1" @click="changePage(page - 1)">&laquo; Prev</button>
        </li>
        <li v-for="n in products.last_page" :key="n">
          <button class="px-3 py-2 border-t border-b hover:bg-gray-50" :class="n === page ? 'bg-blue-600 text-white' : ''" @click="changePage(n)">{{ n }}</button>
        </li>
        <li>
          <button class="px-3 py-2 border rounded-r hover:bg-gray-50" :disabled="page === products.last_page" @click="changePage(page + 1)">Next &raquo;</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { ref, onMounted, onActivated } from 'vue'

const products = ref({ data: [], last_page: 1 })
const page = ref(1)
const search = ref('')

const fetchData = async () => {
  const params = new URLSearchParams()
  if (page.value > 1) params.append('page', page.value)
  if (search.value && search.value.trim() !== '') params.append('search', search.value)
  
  const queryString = params.toString()
  const url = queryString ? `/product-offers?${queryString}` : '/product-offers'
  
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const res = await fetch(url, {
    headers: { 
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrf
    },
    credentials: 'same-origin'
  })
  if (res.ok) {
    products.value = await res.json()
  }
}

const changePage = (p) => {
  page.value = p
  fetchData()
}

const refreshData = () => {
  showNotification('Refreshing product offers...', 'info')
  fetchData()
}

const onToggle = async (product, event) => {
  const activate = event.target.value === '1'
  const action = activate ? 'activating' : 'deactivating'
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/product-offers/${product.id}/toggle`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: new URLSearchParams({ activate: activate ? '1' : '0' })
    })
    
    if (res.ok) {
      const data = await res.json()
      
      // Update product status
      product.is_company_product = activate
      product.assigned_sellers_count = data.assigned_sellers_count || 0
      
      // Show success message
      showNotification(data.message || `Product ${action} successful!`, 'success')
      
    } else {
      const error = await res.json()
      showNotification(error.message || `Error ${action} product`, 'error')
      
      // Reset toggle to previous state on error
      event.target.value = product.is_company_product ? '1' : '0'
    }
  } catch (error) {
    console.error('Toggle error:', error)
    showNotification(`Network error while ${action} product`, 'error')
    
    // Reset toggle to previous state on error
    event.target.value = product.is_company_product ? '1' : '0'
  }
}

// Simple notification function
const showNotification = (message, type = 'info') => {
  // You can replace this with a proper notification system
  const color = type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6'
  
  const notification = document.createElement('div')
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${color};
    color: white;
    padding: 12px 20px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    font-size: 14px;
    max-width: 400px;
  `
  notification.textContent = message
  
  document.body.appendChild(notification)
  
  setTimeout(() => {
    document.body.removeChild(notification)
  }, 5000)
}

onMounted(() => {
  fetchData()
})

// Auto-refresh when component becomes active (e.g., switching from shipments)
onActivated(() => {
  fetchData()
})

// Add global event listener for shipment validation updates
const handleShipmentValidated = () => {
  // Refresh product offers when a shipment is validated
  fetchData()
}

// Listen for custom events from shipment validation
if (typeof window !== 'undefined') {
  window.addEventListener('shipment-validated', handleShipmentValidated)
}
</script>

<style scoped>
/* Custom toggle switch styling */
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  width: 50px;
  height: 24px;
  border-radius: 12px;
  outline: none;
  transition: background-color 0.3s;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s;
}

input[type="range"]::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  border: none;
}

input[type="range"]:checked::-webkit-slider-thumb {
  transform: translateX(26px);
}

input[type="range"]:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

input[type="range"]:disabled::-webkit-slider-thumb {
  cursor: not-allowed;
}
</style>


