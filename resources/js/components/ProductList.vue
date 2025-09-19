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
            @click="viewType = 'grid'" 
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewType === 'grid' 
                ? 'bg-white text-violet-600 shadow-sm' 
                : 'text-gray-500 hover:text-gray-700'
            ]"
            title="Grid View"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
          </button>
          <button 
            @click="viewType = 'table'" 
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewType === 'table' 
                ? 'bg-white text-violet-600 shadow-sm' 
                : 'text-gray-500 hover:text-gray-700'
            ]"
            title="Table View"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0V4a1 1 0 011-1h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1z"/>
            </svg>
          </button>
          <button 
            @click="viewType = 'list'" 
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewType === 'list' 
                ? 'bg-white text-violet-600 shadow-sm' 
                : 'text-gray-500 hover:text-gray-700'
            ]"
            title="List View"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Grid View -->
    <div v-if="viewType === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
      <div v-for="product in products" :key="product.id" class="bg-white rounded-lg shadow p-4 relative">
        <div class="flex items-center justify-between mb-2">
          <span class="bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded">{{ product.category || 'General' }}</span>
          <span v-if="product.status === 'In Stock'" class="bg-green-100 text-green-600 text-xs px-2 py-1 rounded">In Stock</span>
          <span v-else-if="product.status === 'Low Stock'" class="bg-yellow-100 text-yellow-600 text-xs px-2 py-1 rounded">Low Stock</span>
          <span v-else-if="product.status === 'Out of Stock'" class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded">Out of Stock</span>
          <span v-else class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">{{ product.status }}</span>
        </div>
        <div class="relative overflow-hidden rounded mb-3 group cursor-pointer" @mouseenter="showImagePreview(product)" @mouseleave="hideImagePreview">
          <img v-if="product.image_url" :src="product.image_url" alt="Product image" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" />
          <div v-else class="w-full h-48 bg-gray-100 flex items-center justify-center">
            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <!-- Hover overlay -->
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
            <svg class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
            </svg>
          </div>
        </div>
        <div class="font-semibold truncate mb-1">{{ product.name }}</div>
        <div class="text-xs text-gray-500 mb-1">SKU: {{ product.sku }}</div>
        
        <!-- Stock quantities display -->
        <div class="text-xs text-gray-500 mb-1">
          <div v-if="product.warehouses && product.warehouses.length > 0" class="space-y-1">
            <div v-for="warehouse in product.warehouses" :key="warehouse.id" class="flex items-center justify-between">
              <span class="bg-blue-100 text-blue-600 px-1 py-0.5 rounded text-xs">🏢 {{ warehouse.name }}</span>
              <span class="font-medium">{{ warehouse.pivot.quantity }} units</span>
            </div>
            <div class="text-center pt-1 border-t border-gray-200">
              <span class="font-semibold text-gray-700">Total: {{ product.stock_quantity }} units</span>
            </div>
          </div>
          <div v-else-if="product.warehouse" class="flex items-center justify-between">
            <span class="bg-purple-100 text-purple-600 px-1 py-0.5 rounded text-xs">🏢 {{ product.warehouse.name }}</span>
            <span class="font-medium">{{ product.stock_quantity }} units</span>
          </div>
          <div v-else class="text-gray-400">
            {{ product.stock_quantity }} units
          </div>
        </div>
        <div class="text-lg font-bold text-gray-800 mb-1">Price<br><span class="text-black">FCFA{{ product.selling_price }}</span></div>
        <div class="text-xs text-gray-400">Cost: FCFA{{ product.purchase_price }}</div>
        <div class="flex space-x-2 mt-2">
          <button @click="viewProduct(product)" class="text-blue-500 hover:text-blue-700" title="View Details">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 11 3 12a9 9 0 0118 0z"/>
            </svg>
          </button>
          <button @click="editProduct(product)" class="text-violet-500 hover:text-violet-700" title="Edit Product">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <button @click="deleteProduct(product)" class="text-red-500 hover:text-red-700" title="Delete Product">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
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
                <div class="text-sm font-medium text-gray-900">FCFA{{ product.selling_price }}</div>
                <div class="text-xs text-gray-500">Cost: FCFA{{ product.purchase_price }}</div>
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

    <!-- List View -->
    <div v-else-if="viewType === 'list'" class="space-y-4">
      <div v-for="product in products" :key="product.id" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start space-x-6">
          <!-- Product Image -->
          <div class="flex-shrink-0 relative overflow-hidden rounded-lg group cursor-pointer" @mouseenter="showImagePreview(product)" @mouseleave="hideImagePreview">
            <img v-if="product.image_url" :src="product.image_url" alt="Product image" class="h-32 w-32 object-cover transition-transform duration-300 group-hover:scale-105" />
            <div v-else class="h-32 w-32 bg-gray-100 flex items-center justify-center">
              <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <!-- Hover overlay for list view -->
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
              <svg class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
              </svg>
            </div>
          </div>
          
          <!-- Product Details -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ product.name }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div>
                    <span class="text-gray-500">SKU:</span>
                    <span class="ml-1 font-medium">{{ product.sku || 'N/A' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Category:</span>
                    <span class="ml-1 bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs">{{ product.category || 'General' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Stock:</span>
                    <div class="ml-1">
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
                        {{ product.stock_quantity }} units
                      </div>
                    </div>
                  </div>
                  <div>
                    <span class="text-gray-500">Supplier:</span>
                    <span class="ml-1 font-medium">{{ product.supplier || 'N/A' }}</span>
                  </div>
                </div>
                <div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-gray-500">Selling Price:</span>
                    <span class="ml-1 font-bold text-lg text-gray-900">FCFA{{ product.selling_price }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Purchase Price:</span>
                    <span class="ml-1 font-medium">FCFA{{ product.purchase_price }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Warehouse:</span>
                    <span v-if="product.warehouse" class="ml-1 bg-purple-100 text-purple-600 px-2 py-1 rounded text-xs">🏢 {{ product.warehouse.name }}</span>
                    <span v-else class="ml-1 text-gray-400">No warehouse</span>
                  </div>
                </div>
                <div v-if="product.description" class="mt-3">
                  <span class="text-gray-500">Description:</span>
                  <p class="mt-1 text-sm text-gray-700">{{ product.description }}</p>
                </div>
              </div>
              
              <!-- Status and Actions -->
              <div class="flex flex-col items-end space-y-3">
                <div>
                  <span v-if="product.status === 'In Stock'" class="bg-green-100 text-green-600 text-sm px-3 py-1 rounded-full">In Stock</span>
                  <span v-else-if="product.status === 'Low Stock'" class="bg-yellow-100 text-yellow-600 text-sm px-3 py-1 rounded-full">Low Stock</span>
                  <span v-else-if="product.status === 'Out of Stock'" class="bg-red-100 text-red-600 text-sm px-3 py-1 rounded-full">Out of Stock</span>
                  <span v-else class="bg-gray-100 text-gray-600 text-sm px-3 py-1 rounded-full">{{ product.status }}</span>
                </div>
                <div class="flex space-x-2">
                  <button @click="viewProduct(product)" class="text-blue-500 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50" title="View Details">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 11 3 12a9 9 0 0118 0z"/>
                    </svg>
                  </button>
                  <button @click="editProduct(product)" class="text-violet-500 hover:text-violet-700 p-2 rounded-lg hover:bg-violet-50" title="Edit Product">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="deleteProduct(product)" class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50" title="Delete Product">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
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

const emit = defineEmits(['add-product', 'edit-product'])

const products = ref([])
const categories = ref([])
const warehouses = ref([])
const summary = ref({ total: 0, inStock: 0, lowStock: 0, outOfStock: 0 })
const filters = ref({ category: '', status: '', warehouse_id: '', sort: 'name_asc', search: '' })
const viewType = ref(localStorage.getItem('productViewType') || 'grid') // Default to grid view, with persistence
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
• Purchase Price: FCFA${product.purchase_price}
• Selling Price: FCFA${product.selling_price}
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