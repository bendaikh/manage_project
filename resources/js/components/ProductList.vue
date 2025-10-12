<template>
  <div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold">Products</h2>
        <p class="text-gray-500">Manage your inventory and product catalog</p>
      </div>
      <button v-if="canCreate" @click="$emit('add-product')" class="flex items-center px-4 py-2 bg-violet-600 text-white rounded hover:bg-violet-700 font-semibold">
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
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
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
          <label class="block text-sm font-medium mb-1">Warehouse</label>
          <select v-model="filters.warehouse_id" class="w-full border rounded px-3 py-2">
            <option value="">All Warehouses</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }} - {{ warehouse.location }}</option>
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
    <!-- View Type Selector -->
    <div class="flex items-center justify-between mb-4">
      <div class="text-gray-600">Showing {{ products.length }} products</div>
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-500 mr-2">View:</span>
        <div class="flex bg-gray-100 rounded-lg p-1">
          <button 
            @click="viewType = 'card'" 
            :class="[
              'px-3 py-1.5 rounded-md text-sm font-medium transition-colors flex items-center gap-1.5',
              viewType === 'card' 
                ? 'bg-white text-violet-600 shadow-sm' 
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
                ? 'bg-white text-violet-600 shadow-sm' 
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
    <div v-if="viewType === 'card'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="product in products" :key="product.id" class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden group">
        <!-- Large Product Image -->
        <div class="relative overflow-hidden cursor-pointer aspect-square" @mouseenter="showImagePreview(product)" @mouseleave="hideImagePreview">
          <img v-if="product.image_url" :src="product.image_url" alt="Product image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
          <div v-else class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
            <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <!-- Hover overlay with zoom icon -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
            <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
              <svg class="h-12 w-12 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
              </svg>
            </div>
          </div>
          <!-- Status Badge -->
          <div class="absolute top-3 right-3">
            <span v-if="product.status === 'In Stock'" class="bg-green-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">In Stock</span>
            <span v-else-if="product.status === 'Low Stock'" class="bg-yellow-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">Low Stock</span>
            <span v-else-if="product.status === 'Out of Stock'" class="bg-red-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">Out of Stock</span>
            <span v-else class="bg-gray-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">{{ product.status }}</span>
          </div>
          <!-- Category Badge -->
          <div class="absolute top-3 left-3">
            <span class="bg-violet-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-lg">{{ product.category || 'General' }}</span>
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
            <span>{{ product.sku || 'N/A' }}</span>
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
                  <span class="text-violet-600">{{ product.stock_quantity }} units</span>
                </div>
              </div>
            </div>
            <div v-else-if="product.warehouse" class="flex items-center justify-between text-sm">
              <span class="flex items-center text-gray-600">
                <svg class="h-4 w-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ product.warehouse.name }}
              </span>
              <span class="font-semibold text-violet-600">{{ product.stock_quantity }} units</span>
            </div>
            <div v-else class="flex items-center justify-between">
              <span class="text-gray-600 text-sm">Stock:</span>
              <span class="font-semibold text-violet-600">{{ product.stock_quantity }} units</span>
            </div>
          </div>

          <!-- Pricing -->
          <div class="mb-4">
            <div class="flex items-baseline justify-between mb-1">
              <span class="text-sm text-gray-500">Selling Price</span>
              <span class="text-2xl font-bold text-gray-900">{{ product.selling_price }} <span class="text-sm font-normal text-gray-500">{{ getCurrency() }}</span></span>
            </div>
            <div class="flex items-baseline justify-between">
              <span class="text-xs text-gray-400">Cost Price</span>
              <span class="text-sm text-gray-600">{{ product.purchase_price }} {{ getCurrency() }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2">
            <button @click="viewProduct(product)" class="flex-1 bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded-lg font-medium text-sm transition-colors duration-200 flex items-center justify-center gap-1.5" title="View Details">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View
            </button>
            <button @click="editProduct(product)" class="flex-1 bg-violet-600 text-white hover:bg-violet-700 px-3 py-2 rounded-lg font-medium text-sm transition-colors duration-200 flex items-center justify-center gap-1.5" title="Edit Product">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit
            </button>
            <button @click="deleteProduct(product)" class="bg-red-50 text-red-600 hover:bg-red-100 p-2 rounded-lg transition-colors duration-200" title="Delete Product">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div v-else-if="viewType === 'table'" class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock & Warehouses</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-12 w-12 relative overflow-hidden rounded-lg group cursor-pointer" @mouseenter="showImagePreview(product)" @mouseleave="hideImagePreview">
                    <img v-if="product.image_url" :src="product.image_url" alt="Product" class="h-12 w-12 object-cover transition-transform duration-300 group-hover:scale-110">
                    <div v-else class="h-12 w-12 bg-gray-200 flex items-center justify-center">
                      <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <!-- Hover overlay for table view -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                      <svg class="h-4 w-4 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-sm text-gray-500">{{ product.supplier || 'No supplier' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.sku || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded">{{ product.category || 'General' }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="product.status === 'In Stock'" class="bg-green-100 text-green-600 text-xs px-2 py-1 rounded">In Stock</span>
                <span v-else-if="product.status === 'Low Stock'" class="bg-yellow-100 text-yellow-600 text-xs px-2 py-1 rounded">Low Stock</span>
                <span v-else-if="product.status === 'Out of Stock'" class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded">Out of Stock</span>
                <span v-else class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">{{ product.status }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div v-if="product.warehouses && product.warehouses.length > 0" class="space-y-1">
                  <div v-for="warehouse in product.warehouses" :key="warehouse.id" class="flex items-center justify-between">
                    <span class="bg-blue-100 text-blue-600 px-1 py-0.5 rounded text-xs">🏢 {{ warehouse.name }}</span>
                    <span class="font-medium text-sm">{{ warehouse.pivot.quantity }}</span>
                  </div>
                  <div class="text-center pt-1 border-t border-gray-200">
                    <span class="font-semibold text-gray-700 text-sm">Total: {{ product.stock_quantity }}</span>
                  </div>
                </div>
                <div v-else-if="product.warehouse" class="flex items-center justify-between">
                  <span class="bg-purple-100 text-purple-600 px-1 py-0.5 rounded text-xs">🏢 {{ product.warehouse.name }}</span>
                  <span class="font-medium text-sm">{{ product.stock_quantity }}</span>
                </div>
                <div v-else class="text-gray-400">
                  {{ product.stock_quantity }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ product.selling_price }} {{ getCurrency() }}</div>
                <div class="text-xs text-gray-500">Cost: {{ product.purchase_price }} {{ getCurrency() }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="viewProduct(product)" class="text-blue-500 hover:text-blue-700" title="View Details">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 11 3 12a9 9 0 0118 0z"/>
                    </svg>
                  </button>
                  <button @click="editProduct(product)" class="text-violet-500 hover:text-violet-700" title="Edit Product">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="deleteProduct(product)" class="text-red-500 hover:text-red-700" title="Delete Product">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Image Preview Lightbox -->
    <div v-if="previewImage" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 transition-opacity duration-300" @click="hideImagePreview" @mouseenter="clearHideTimeout" @mouseleave="hideImagePreview">
      <div class="relative max-w-4xl max-h-full p-4" @mouseenter="clearHideTimeout" @mouseleave="hideImagePreview">
        <img :src="previewImage" :alt="previewProduct?.name || 'Product image'" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" />
        <div class="absolute top-4 right-4">
          <button @click="hideImagePreview" class="bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75 transition-all duration-200">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div v-if="previewProduct" class="absolute bottom-4 left-4 right-4 bg-black bg-opacity-50 text-white p-4 rounded-lg">
          <h3 class="text-lg font-semibold">{{ previewProduct.name }}</h3>
          <p class="text-sm opacity-90">{{ previewProduct.category || 'General' }} • SKU: {{ previewProduct.sku || 'N/A' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useCurrency } from '../composables/useCurrency'

const { getCurrency } = useCurrency()

const emit = defineEmits(['add-product', 'edit-product'])

const products = ref([])
const categories = ref([])
const warehouses = ref([])
const summary = ref({ total: 0, inStock: 0, lowStock: 0, outOfStock: 0 })
const filters = ref({ category: '', status: '', warehouse_id: '', sort: 'name_asc', search: '' })
const viewType = ref(localStorage.getItem('productViewType') || 'card') // Default to card view, with persistence
const previewImage = ref(null)
const previewProduct = ref(null)
const hoverTimeout = ref(null)
const hideTimeout = ref(null)

// Permissions from backend
const userPermissions = window.Laravel?.user?.permissions || []
const canCreate = computed(() => userPermissions.includes('create_products') || userPermissions.includes('manage_products'))

const fetchWarehouses = async () => {
  try {
    const res = await fetch('/warehouses', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
    if (!res.ok) return
    const data = await res.json()
    warehouses.value = data.data || []
  } catch (e) {
    console.error('Failed to fetch warehouses:', e)
  }
}

const fetchProducts = async () => {
  let url = '/products/list'
  const params = new URLSearchParams()
  if (filters.value.category) params.append('category', filters.value.category)
  if (filters.value.status) params.append('status', filters.value.status)
  if (filters.value.warehouse_id) params.append('warehouse_id', filters.value.warehouse_id)
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
  filters.value = { category: '', status: '', warehouse_id: '', sort: 'name_asc', search: '' }
  fetchProducts()
}

const viewProduct = (product) => {
  // Show a detailed modal with product information
  const details = `
Product Details:
• Name: ${product.name}
• SKU: ${product.sku || 'N/A'}
• Category: ${product.category || 'General'}
• Supplier: ${product.supplier || 'N/A'}
• Warehouse: ${product.warehouse ? `${product.warehouse.name} - ${product.warehouse.location}` : 'N/A'}
• Purchase Price: ${product.purchase_price} ${getCurrency()}
• Selling Price: ${product.selling_price} ${getCurrency()}
• Stock Quantity: ${product.stock_quantity} units
• Status: ${product.status}
• Description: ${product.description || 'No description available'}
  `.trim()
  
  alert(details)
}

const editProduct = (product) => {
  // Emit event to parent to handle editing
  emit('edit-product', product)
}

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete "${product.name}"?`)) {
    return
  }
  
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch(`/products/${product.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      // Remove the product from the list
      products.value = products.value.filter(p => p.id !== product.id)
      // Update summary
      summary.value.total--
      if (product.status === 'In Stock') summary.value.inStock--
      else if (product.status === 'Low Stock') summary.value.lowStock--
      else if (product.status === 'Out of Stock') summary.value.outOfStock--
    } else {
      const data = await response.json()
      alert(data.message || 'Failed to delete product')
    }
  } catch (error) {
    console.error('Error deleting product:', error)
    alert('Failed to delete product')
  }
}

const showImagePreview = (product) => {
  // Clear any existing timeout
  if (hoverTimeout.value) {
    clearTimeout(hoverTimeout.value)
    hoverTimeout.value = null
  }
  
  if (product.image_url) {
    // Add a small delay to prevent flickering
    hoverTimeout.value = setTimeout(() => {
      previewImage.value = product.image_url
      previewProduct.value = product
    }, 200) // 200ms delay
  }
}

const hideImagePreview = () => {
  // Clear any pending show timeout
  if (hoverTimeout.value) {
    clearTimeout(hoverTimeout.value)
    hoverTimeout.value = null
  }
  
  // Clear any existing hide timeout
  if (hideTimeout.value) {
    clearTimeout(hideTimeout.value)
  }
  
  // Add a small delay before hiding to prevent flickering
  hideTimeout.value = setTimeout(() => {
    previewImage.value = null
    previewProduct.value = null
    hideTimeout.value = null
  }, 100) // 100ms delay
}

const clearHideTimeout = () => {
  if (hideTimeout.value) {
    clearTimeout(hideTimeout.value)
    hideTimeout.value = null
  }
}

// Watch for viewType changes and save to localStorage
watch(viewType, (newViewType) => {
  localStorage.setItem('productViewType', newViewType)
})

// Handle escape key to close lightbox
const handleKeydown = (event) => {
  if (event.key === 'Escape' && previewImage.value) {
    hideImagePreview()
  }
}

onMounted(() => {
  fetchWarehouses()
  fetchProducts()
  document.addEventListener('keydown', handleKeydown)
})

// Cleanup event listener and timeouts
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  if (hoverTimeout.value) {
    clearTimeout(hoverTimeout.value)
  }
  if (hideTimeout.value) {
    clearTimeout(hideTimeout.value)
  }
})
</script> 