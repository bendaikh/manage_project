<template>
  <div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">Products</h2>
        <p class="text-gray-500">Manage your inventory and product catalog</p>
      </div>
      <button @click="$emit('add-product')" class="flex items-center px-4 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 font-semibold">
        + Add Product
      </button>
    </div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4 flex items-center">
        <div class="bg-violet-100 text-violet-600 rounded-full p-2 mr-3">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/></svg>
        </div>
        <div>
          <div class="text-lg font-bold">{{ summary.total }}</div>
          <div class="text-xs text-gray-500">Total Products</div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-4 flex items-center">
        <div class="bg-green-100 text-green-600 rounded-full p-2 mr-3">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
        </div>
        <div>
          <div class="text-lg font-bold">{{ summary.inStock }}</div>
          <div class="text-xs text-gray-500">In Stock</div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-4 flex items-center">
        <div class="bg-yellow-100 text-yellow-600 rounded-full p-2 mr-3">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
        </div>
        <div>
          <div class="text-lg font-bold">{{ summary.lowStock }}</div>
          <div class="text-xs text-gray-500">Low Stock</div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-4 flex items-center">
        <div class="bg-red-100 text-red-600 rounded-full p-2 mr-3">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-6 6M9 9l6 6"/></svg>
        </div>
        <div>
          <div class="text-lg font-bold">{{ summary.outOfStock }}</div>
          <div class="text-xs text-gray-500">Out of Stock</div>
        </div>
      </div>
    </div>
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <select v-model="filters.category" class="w-full border rounded px-3 py-2">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="filters.status" class="w-full border rounded px-3 py-2">
            <option value="">All Status</option>
            <option value="In Stock">In Stock</option>
            <option value="Low Stock">Low Stock</option>
            <option value="Out of Stock">Out of Stock</option>
            <option value="Discontinued">Discontinued</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Sort By</label>
          <select v-model="filters.sort" class="w-full border rounded px-3 py-2">
            <option value="name_asc">Name (A-Z)</option>
            <option value="name_desc">Name (Z-A)</option>
            <option value="price_asc">Price (Low-High)</option>
            <option value="price_desc">Price (High-Low)</option>
            <option value="stock_asc">Stock (Low-High)</option>
            <option value="stock_desc">Stock (High-Low)</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="fetchProducts" class="w-full px-4 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 font-semibold flex items-center justify-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4h13M8 12h13M8 20h13M3 6h.01M3 18h.01"/></svg>
            Apply Filters
          </button>
        </div>
      </div>
      <div class="flex justify-between items-center mt-4">
        <div class="flex-1">
          <input v-model="filters.search" @keyup.enter="fetchProducts" type="text" placeholder="Search products..." class="w-full border rounded px-3 py-2" />
        </div>
        <button @click="clearFilters" class="ml-4 px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">Clear Filters</button>
      </div>
    </div>
    <!-- Product List -->
    <div class="mb-2 text-gray-600">Showing {{ products.length }} products</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-for="product in products" :key="product.id" class="bg-white rounded-lg shadow p-4 relative">
        <div class="flex items-center justify-between mb-2">
          <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded">{{ product.category || 'General' }}</span>
          <span v-if="product.status === 'In Stock'" class="bg-green-100 text-green-600 text-xs px-2 py-1 rounded">In Stock</span>
          <span v-else-if="product.status === 'Low Stock'" class="bg-yellow-100 text-yellow-600 text-xs px-2 py-1 rounded">Low Stock</span>
          <span v-else-if="product.status === 'Out of Stock'" class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded">Out of Stock</span>
          <span v-else class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">{{ product.status }}</span>
        </div>
        <img v-if="product.image_url" :src="product.image_url" alt="Product image" class="w-full h-32 object-cover rounded mb-2" />
        <div class="font-semibold truncate mb-1">{{ product.name }}</div>
        <div class="text-xs text-gray-500 mb-1">SKU: {{ product.sku }}</div>
        <div class="text-xs text-gray-500 mb-1">{{ product.stock_quantity }} units</div>
        <div class="text-lg font-bold text-gray-800 mb-1">Price<br><span class="text-black">FCFA{{ product.selling_price }}</span></div>
        <div class="text-xs text-gray-400">Cost: FCFA{{ product.purchase_price }}</div>
        <div class="flex space-x-2 mt-2">
          <button class="text-blue-500 hover:text-blue-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 11 3 12a9 9 0 0118 0z"/></svg></button>
          <button class="text-violet-500 hover:text-violet-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11V9a3 3 0 013-3h0a3 3 0 013 3v2m0 0v2a3 3 0 01-3 3h0a3 3 0 01-3-3v-2m6 0H9"/></svg></button>
          <button class="text-red-500 hover:text-red-700"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const products = ref([])
const categories = ref([])
const summary = ref({ total: 0, inStock: 0, lowStock: 0, outOfStock: 0 })
const filters = ref({ category: '', status: '', sort: 'name_asc', search: '' })

const fetchProducts = async () => {
  let url = '/products/list'
  const params = new URLSearchParams()
  if (filters.value.category) params.append('category', filters.value.category)
  if (filters.value.status) params.append('status', filters.value.status)
  if (filters.value.sort) params.append('sort', filters.value.sort)
  if (filters.value.search) params.append('search', filters.value.search)
  if ([...params].length) url += `?${params.toString()}`

  try {
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
    if (!res.ok) {
      console.error('Failed to fetch products')
      products.value = []
      return
    }
    const data = await res.json()
    products.value = data.products || []
    categories.value = data.categories || []
    summary.value = data.summary || { total: 0, inStock: 0, lowStock: 0, outOfStock: 0 }
  } catch (e) {
    console.error(e)
    products.value = []
  }
}

const clearFilters = () => {
  filters.value = { category: '', status: '', sort: 'name_asc', search: '' }
  fetchProducts()
}

onMounted(fetchProducts)
</script> 