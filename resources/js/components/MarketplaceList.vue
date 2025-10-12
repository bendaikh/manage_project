<template>
  <div class="max-w-7xl mx-auto bg-white rounded-lg shadow p-8 mt-4">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">Marketplace</h2>
        <p class="text-gray-500">Products assigned to you for selling</p>
      </div>
      <div class="flex items-center space-x-4">
        <div class="text-sm text-gray-600">
          <span class="font-semibold">{{ stats.total_products }}</span> products assigned
        </div>
        <button @click="refreshData" class="flex items-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 text-gray-700">
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-blue-600">Total Products</p>
            <p class="text-2xl font-bold text-blue-900">{{ stats.total_products }}</p>
          </div>
        </div>
      </div>

      <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-green-600">In Stock</p>
            <p class="text-2xl font-bold text-green-900">{{ stats.in_stock_products }}</p>
          </div>
        </div>
      </div>

      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-yellow-600">Low Stock</p>
            <p class="text-2xl font-bold text-yellow-900">{{ stats.low_stock_products }}</p>
          </div>
        </div>
      </div>

      <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-purple-600">Total Value</p>
            <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(stats.total_value) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Search products..." class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <select v-model="filters.category" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="filters.status" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">All Statuses</option>
            <option value="In Stock">In Stock</option>
            <option value="Out of Stock">Out of Stock</option>
            <option value="Discontinued">Discontinued</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Warehouse</label>
          <select v-model="filters.warehouse_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">All Warehouses</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Sort By</label>
          <select v-model="filters.sort" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
            <option value="">Default</option>
            <option value="name_asc">Name (A-Z)</option>
            <option value="name_desc">Name (Z-A)</option>
            <option value="price_asc">Price (Low to High)</option>
            <option value="price_desc">Price (High to Low)</option>
            <option value="stock_asc">Stock (Low to High)</option>
            <option value="stock_desc">Stock (High to Low)</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button @click="clearFilters" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Clear Filters</button>
      </div>
    </div>

    <!-- View Type Selector -->
    <div class="flex items-center justify-between mb-4">
      <div class="text-gray-600">Showing {{ products.data?.length || 0 }} products</div>
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-500 mr-2">View:</span>
        <div class="flex bg-gray-100 rounded-lg p-1">
          <button 
            @click="viewType = 'card'" 
            :class="[
              'px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center gap-1.5',
              viewType === 'card' 
                ? 'bg-white text-blue-600 shadow-sm' 
                : 'text-gray-500 hover:text-gray-700'
            ]"
            title="Card View"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span class="hidden sm:inline">Cards</span>
          </button>
          <button 
            @click="viewType = 'table'" 
            :class="[
              'px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center gap-1.5',
              viewType === 'table' 
                ? 'bg-white text-blue-600 shadow-sm' 
                : 'text-gray-500 hover:text-gray-700'
            ]"
            title="Table View"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0V4a1 1 0 011-1h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1z"/>
            </svg>
            <span class="hidden sm:inline">Table</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Card View -->
    <div v-if="viewType === 'card'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
      <div v-for="product in products.data" :key="product.id" class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
        <!-- Large Product Image -->
        <div class="relative overflow-hidden cursor-pointer aspect-square" @click="viewProduct(product)">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
          <div v-else class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <!-- Hover overlay with zoom icon -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
            <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
              <svg class="h-12 w-12 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
          </div>
          <!-- Status Badge -->
          <div class="absolute top-3 right-3">
            <span :class="getStatusClass(product.status)" class="text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">
              {{ product.status }}
            </span>
          </div>
          <!-- Category Badge -->
          <div class="absolute top-3 left-3">
            <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">{{ product.category || 'General' }}</span>
          </div>
        </div>

        <!-- Card Content -->
        <div class="p-5">
          <!-- Product Name -->
          <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 min-h-[3.5rem]" :title="product.name">{{ product.name }}</h3>
          
          <!-- SKU -->
          <div class="flex items-center text-xs text-gray-500 mb-3">
            <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
            </svg>
            <span>{{ product.sku }}</span>
          </div>

          <!-- Stock Information -->
          <div class="mb-4 pb-4 border-b border-gray-100">
            <div v-if="product.warehouses && product.warehouses.length > 0" class="space-y-2">
              <div v-for="warehouse in product.warehouses.slice(0, 2)" :key="warehouse.id" class="flex items-center justify-between text-sm">
                <span class="flex items-center text-gray-600">
                  <svg class="h-4 w-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  {{ warehouse.name }}
                </span>
                <span class="font-semibold text-gray-900">{{ warehouse.pivot.quantity }}</span>
              </div>
              <div v-if="product.warehouses.length > 2" class="text-xs text-gray-400 text-center">
                +{{ product.warehouses.length - 2 }} more
              </div>
              <div class="pt-2 border-t border-gray-100">
                <div class="flex items-center justify-between font-semibold text-gray-900">
                  <span>Total Stock:</span>
                  <span class="text-blue-600">{{ product.stock_quantity }} units</span>
                </div>
              </div>
              <div v-if="product.stock_quantity <= 10" class="mt-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  ⚠️ Low Stock
                </span>
              </div>
            </div>
            <div v-else-if="product.warehouse" class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="flex items-center text-gray-600">
                  <svg class="h-4 w-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  {{ product.warehouse.name }}
                </span>
                <span class="font-semibold text-blue-600">{{ product.stock_quantity }} units</span>
              </div>
              <div v-if="product.stock_quantity <= 10">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  ⚠️ Low Stock
                </span>
              </div>
            </div>
            <div v-else class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-gray-600 text-sm">Stock:</span>
                <span class="font-semibold text-blue-600">{{ product.stock_quantity }} units</span>
              </div>
              <div v-if="product.stock_quantity <= 10">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  ⚠️ Low Stock
                </span>
              </div>
            </div>
          </div>

          <!-- Pricing -->
          <div class="mb-4">
            <div class="flex items-baseline justify-between">
              <span class="text-sm text-gray-500">Selling Price</span>
              <span class="text-2xl font-bold text-gray-900">{{ formatCurrency(product.selling_price) }}</span>
            </div>
          </div>

          <!-- Action Button -->
          <button @click="viewProduct(product)" class="w-full bg-blue-600 text-white hover:bg-blue-700 px-4 py-2.5 rounded-lg font-medium text-sm transition-colors duration-200 flex items-center justify-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            View Details
          </button>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div v-if="viewType === 'table'" class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-10 w-10 rounded-lg object-cover" />
                  <div v-else class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.sku }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.category || 'N/A' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(product.selling_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="product.warehouses && product.warehouses.length > 0" class="space-y-1">
                <div v-for="warehouse in product.warehouses" :key="warehouse.id" class="flex items-center justify-between">
                  <span class="bg-blue-100 text-blue-600 px-1 py-0.5 rounded text-xs">🏢 {{ warehouse.name }}</span>
                  <span class="font-medium text-sm">{{ warehouse.pivot.quantity }}</span>
                </div>
                <div class="text-center pt-1 border-t border-gray-200">
                  <span class="font-semibold text-gray-700 text-sm">Total: {{ product.stock_quantity }}</span>
                </div>
                <span v-if="product.stock_quantity <= 10" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Low Stock
                </span>
              </div>
              <div v-else-if="product.warehouse" class="flex items-center justify-between">
                <span class="bg-purple-100 text-purple-600 px-1 py-0.5 rounded text-xs">🏢 {{ product.warehouse.name }}</span>
                <span class="font-medium text-sm">{{ product.stock_quantity }}</span>
                <span v-if="product.stock_quantity <= 10" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Low Stock
                </span>
              </div>
              <div v-else>
                <span class="text-sm text-gray-900">{{ product.stock_quantity }}</span>
                <span v-if="product.stock_quantity <= 10" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  Low Stock
                </span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="product.status === 'In Stock'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ product.status }}
              </span>
              <span v-else-if="product.status === 'Low Stock'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                {{ product.status }}
              </span>
              <span v-else-if="product.status === 'Out of Stock'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                {{ product.status }}
              </span>
              <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                {{ product.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="viewProduct(product)" class="text-blue-600 hover:text-blue-900">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="products.last_page > 1" class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
      </div>
      <div class="flex space-x-2">
        <button @click="changePage(products.current_page - 1)" :disabled="products.current_page === 1" class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
        <span class="px-3 py-1 text-sm">Page {{ products.current_page }} of {{ products.last_page }}</span>
        <button @click="changePage(products.current_page + 1)" :disabled="products.current_page === products.last_page" class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading products...</p>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && products.data.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No products assigned</h3>
      <p class="mt-1 text-sm text-gray-500">You don't have any products assigned to you yet.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useCurrency } from '../composables/useCurrency'

const { formatCurrency } = useCurrency()

const emit = defineEmits(['view-product'])

const products = ref({ data: [] })
const categories = ref([])
const warehouses = ref([])
const stats = ref({
  total_products: 0,
  in_stock_products: 0,
  low_stock_products: 0,
  total_value: 0
})
const loading = ref(false)
const currentPage = ref(1)
const viewType = ref(localStorage.getItem('marketplaceViewType') || 'card') // Default to card view, with persistence

const filters = ref({
  search: '',
  category: '',
  status: '',
  warehouse_id: '',
  sort: ''
})

const fetchData = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: currentPage.value,
      ...filters.value
    })
    
    const [productsResponse, statsResponse] = await Promise.all([
      fetch(`/marketplace?${params}`),
      fetch('/marketplace/stats')
    ])
    
    if (productsResponse.ok) {
      const data = await productsResponse.json()
      products.value = data.products
      categories.value = data.categories
      warehouses.value = data.warehouses
    }
    
    if (statsResponse.ok) {
      const statsData = await statsResponse.json()
      stats.value = statsData
    }
  } catch (error) {
    console.error('Error fetching marketplace data:', error)
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  currentPage.value = 1
  fetchData()
}

const clearFilters = () => {
  filters.value = {
    search: '',
    category: '',
    status: '',
    warehouse_id: '',
    sort: ''
  }
  currentPage.value = 1
  fetchData()
}

const changePage = (page) => {
  if (page >= 1 && page <= products.value.last_page) {
    currentPage.value = page
    fetchData()
  }
}


const getStatusClass = (status) => {
  switch (status) {
    case 'In Stock':
      return 'bg-green-500'
    case 'Low Stock':
      return 'bg-yellow-500'
    case 'Out of Stock':
      return 'bg-red-500'
    case 'Discontinued':
      return 'bg-gray-500'
    default:
      return 'bg-gray-500'
  }
}

const viewProduct = (product) => {
  // Emit event to parent to show product details
  emit('view-product', product)
}

// Watch for filter changes
watch(filters, () => {
  currentPage.value = 1
  fetchData()
}, { deep: true })

// Watch for viewType changes and save to localStorage
watch(viewType, (newViewType) => {
  localStorage.setItem('marketplaceViewType', newViewType)
})

onMounted(() => {
  fetchData()
})
</script>
