<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Stock Globale - Global Inventory Management</h2>
      <button @click="openTransferModal" 
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Create Transfer
      </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Products</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_products || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">In Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.in_stock || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Low Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.low_stock || 0 }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
          <div class="p-2 bg-red-100 rounded-lg">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Out of Stock</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.out_of_stock || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">Global Stock Filters</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Search</label>
          <input v-model="filters.search" placeholder="Search title, reference, barcode..." 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Product</label>
          <select v-model="filters.product_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Products</option>
            <option v-for="product in availableProducts" :key="product.id" :value="product.id">
              {{ product.name }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Warehouse</label>
          <select v-model="filters.warehouse_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Warehouses</option>
            <option v-for="warehouse in availableWarehouses" :key="warehouse.id" :value="warehouse.id">
              {{ warehouse.name }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Seller</label>
          <select v-model="filters.seller_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Sellers</option>
            <option v-for="seller in availableSellers" :key="seller.id" :value="seller.id">
              {{ seller.name }}
            </option>
          </select>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium mb-1">Status</label>
          <select v-model="filters.status" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">All Status</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Warehouse Location</label>
          <input v-model="filters.warehouse_location" placeholder="Filter by location..." 
                 class="w-full border rounded px-3 py-2 text-sm" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">Price Range</label>
          <div class="flex gap-2">
            <input v-model.number="filters.min_price" type="number" placeholder="Min" 
                   class="w-full border rounded px-3 py-2 text-sm" />
            <input v-model.number="filters.max_price" type="number" placeholder="Max" 
                   class="w-full border rounded px-3 py-2 text-sm" />
          </div>
        </div>
      </div>
      
      <div class="flex gap-2 mt-4">
        <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
          Apply Filters
        </button>
        <button @click="clearFilters" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-sm">
          Clear Filters
        </button>
        <button @click="exportData" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
          Export Data
        </button>
      </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse Distribution</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initial Qty</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Delivered</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Damaged</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total In Progress</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upsells</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="stock in stocks" :key="stock.id" 
                :class="getStatusRowClass(stock.status)">
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img v-if="stock.photo" :src="storageUrl(stock.photo)" 
                       class="w-10 h-10 object-cover rounded mr-3" />
                  <div>
                    <div class="text-sm font-medium text-gray-900">
                      {{ stock.product ? stock.product.name : stock.title }}
                    </div>
                    <div class="text-sm text-gray-500">{{ stock.description || 'No description' }}</div>
                    <div v-if="stock.product" class="text-xs text-blue-600">
                      SKU: {{ stock.product.sku || 'N/A' }} | Category: {{ stock.product.category || 'N/A' }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ stock.reference }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                    <span class="text-xs font-medium text-gray-600">
                      {{ stock.seller ? stock.seller.name.charAt(0).toUpperCase() : 'N/A' }}
                    </span>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ stock.seller ? stock.seller.name : 'N/A' }}</div>
                    <div class="text-xs text-gray-500">{{ stock.seller ? stock.seller.email : '' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div v-if="stock.warehouse_distribution && stock.warehouse_distribution.length > 0">
                  <div v-for="warehouse in stock.warehouse_distribution" :key="warehouse.warehouse_id" 
                       class="mb-2 p-2 bg-gray-50 rounded border">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ warehouse.warehouse_name }}</div>
                        <div class="text-xs text-gray-500">{{ warehouse.warehouse_location || 'No location' }}</div>
                      </div>
                      <div class="flex items-center gap-2">
                        <div class="text-right">
                          <div class="text-sm font-semibold text-blue-600">{{ warehouse.remaining_quantity }}</div>
                          <div class="text-xs text-gray-400">remaining</div>
                        </div>
                        <button @click="openWarehouseEditModal(stock, warehouse)" 
                                class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                title="Edit warehouse quantity">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                    <div v-if="warehouse.stocks && warehouse.stocks.length > 1" class="mt-1">
                      <div class="text-xs text-gray-500">
                        {{ warehouse.stocks.length }} stock{{ warehouse.stocks.length > 1 ? 's' : '' }} 
                        (Total: {{ warehouse.total_quantity }})
                      </div>
                    </div>
                  </div>
                </div>
                <span v-else class="text-gray-400">No warehouse</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ stock.initial_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ stock.remaining_quantity }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.total_delivered_quantity || 0 }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.total_damaged_quantity || 0 }}</td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ stock.total_in_progress_quantity || 0 }}</td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span :class="getStatusClass(stock.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ formatStatus(stock.status) }}
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div v-if="stock.purchase_price || stock.selling_price">
                  <div v-if="stock.purchase_price">Cost: {{ formatCurrency(stock.purchase_price) }}</div>
                  <div v-if="stock.selling_price">Price: {{ formatCurrency(stock.selling_price) }}</div>
                </div>
                <span v-else class="text-gray-400">Not set</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div v-if="stock.upsells && stock.upsells.length > 0" class="space-y-1">
                  <div v-for="(upsell, index) in stock.upsells" :key="upsell.id" 
                       class="flex items-center justify-between bg-blue-50 px-2 py-1 rounded text-xs">
                    <span class="font-medium">{{ upsell.quantity }}x</span>
                    <span class="font-semibold text-blue-700">{{ formatCurrency(upsell.price) }}</span>
                  </div>
                </div>
                <span v-else class="text-gray-400 text-xs">No upsells</span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>{{ formatDate(stock.last_updated_at) }}</div>
                <div class="text-xs text-gray-400">by {{ stock.last_updated_by }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex gap-2">
                  <button @click="openUpsellModal(stock)" 
                          class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors"
                          title="Add Upsell">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Upsell
                  </button>
                  <button @click="openModifyUpsellsModal(stock)" 
                          class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors"
                          title="Modify Upsells">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modify Upsells
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="stocks.length === 0">
              <td colspan="14" class="text-center py-8 text-gray-400">
                No stock found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <nav v-if="pagination.total_pages > 1" class="flex justify-center mt-6">
      <ul class="inline-flex">
        <li>
          <button class="px-3 py-2 border rounded-l hover:bg-gray-50" 
                  :disabled="pagination.page === 1" 
                  @click="changePage(pagination.page - 1)">
            &laquo; Previous
          </button>
        </li>
        <li v-for="page in getPageNumbers()" :key="page">
          <button class="px-3 py-2 border-t border-b hover:bg-gray-50" 
                  :class="page === pagination.page ? 'bg-blue-600 text-white' : ''" 
                  @click="changePage(page)">
            {{ page }}
          </button>
        </li>
        <li>
          <button class="px-3 py-2 border rounded-r hover:bg-gray-50" 
                  :disabled="pagination.page === pagination.total_pages" 
                  @click="changePage(pagination.page + 1)">
            Next &raquo;
          </button>
        </li>
      </ul>
    </nav>


    <!-- Warehouse Edit Modal -->
    <div v-if="warehouseEditModal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Edit Warehouse Quantity</h3>
            <button @click="closeWarehouseEditModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="mb-4">
            <div class="text-sm text-gray-600 mb-2">
              <strong>Product:</strong> {{ warehouseEditModal.stock?.product?.name || warehouseEditModal.stock?.title }}
            </div>
            <div class="text-sm text-gray-600 mb-2">
              <strong>Reference:</strong> {{ warehouseEditModal.stock?.reference }}
            </div>
            <div class="text-sm text-gray-600 mb-4">
              <strong>Warehouse:</strong> {{ warehouseEditModal.warehouse?.warehouse_name }}
            </div>
          </div>
          
          <form @submit.prevent="updateWarehouseQuantity">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                New Quantity
              </label>
              <input v-model.number="warehouseEditModal.newQuantity" 
                     type="number" 
                     min="0" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                     required>
              <div class="text-xs text-gray-500 mt-1">
                Current quantity: {{ warehouseEditModal.warehouse?.remaining_quantity }}
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notes (Optional)
              </label>
              <textarea v-model="warehouseEditModal.notes" 
                        rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Add a note about this quantity change..."></textarea>
            </div>
            
            <div class="flex gap-3">
              <button type="button" 
                      @click="closeWarehouseEditModal"
                      class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit" 
                      :disabled="warehouseEditModal.loading"
                      class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="warehouseEditModal.loading">Updating...</span>
                <span v-else>Update Quantity</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Transfer Modal -->
    <div v-if="transferModal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Create Warehouse Transfer</h3>
            <button @click="closeTransferModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="createTransfer">
            <!-- Step 1: Select Stock -->
            <div class="mb-6">
              <h4 class="text-lg font-medium text-gray-900 mb-4">Step 1: Select Stock</h4>
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Select Stock Item
                </label>
                <select v-model="transferModal.selectedStockId" 
                        @change="onStockSelected"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                  <option value="">Choose a stock item...</option>
                  <option v-for="stock in availableStocks" :key="stock.id" :value="stock.id">
                    {{ stock.product ? stock.product.name : stock.title }} ({{ stock.reference }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Step 2: Principal Warehouse Info -->
            <div v-if="transferModal.selectedStockId && transferModal.principalWarehouse" class="mb-6">
              <h4 class="text-lg font-medium text-gray-900 mb-4">Step 2: Principal Warehouse</h4>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <h5 class="font-medium text-blue-900">{{ transferModal.principalWarehouse.name }}</h5>
                    <p class="text-sm text-blue-700">{{ transferModal.principalWarehouse.location }}</p>
                  </div>
                  <div class="text-right">
                    <div class="text-2xl font-bold text-blue-900">{{ transferModal.principalQuantity }}</div>
                    <div class="text-sm text-blue-700">Available Quantity</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 3: Transfer Details -->
            <div v-if="transferModal.selectedStockId && transferModal.principalWarehouse" class="mb-6">
              <h4 class="text-lg font-medium text-gray-900 mb-4">Step 3: Transfer Details</h4>
              
              <!-- Transfer Items -->
              <div class="space-y-4">
                <div v-for="(transfer, index) in transferModal.transfers" :key="index" 
                     class="border border-gray-200 rounded-lg p-4">
                  <div class="flex items-center justify-between mb-3">
                    <h5 class="font-medium text-gray-900">Transfer {{ index + 1 }}</h5>
                    <button v-if="transferModal.transfers.length > 1" 
                            @click="removeTransfer(index)"
                            type="button"
                            class="text-red-600 hover:text-red-800">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Destination Warehouse
                      </label>
                      <select v-model="transfer.to_warehouse_id" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              required>
                        <option value="">Select warehouse...</option>
                        <option v-for="warehouse in availableWarehouses" 
                                :key="warehouse.id" 
                                :value="warehouse.id"
                                :disabled="warehouse.id === transferModal.principalWarehouse?.id">
                          {{ warehouse.name }} ({{ warehouse.location }})
                        </option>
                      </select>
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity to Transfer
                      </label>
                      <input v-model.number="transfer.quantity" 
                             type="number" 
                             min="1" 
                             :max="transferModal.principalQuantity"
                             class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                             required>
                      <div class="text-xs text-gray-500 mt-1">
                        Max: {{ transferModal.principalQuantity }} ({{ transferModal.principalQuantity - getTotalTransferredQuantity() }} remaining)
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Add Transfer Button -->
              <button v-if="getTotalTransferredQuantity() < transferModal.principalQuantity"
                      @click="addTransfer"
                      type="button"
                      class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Another Transfer
              </button>
              
              <!-- All Quantity Allocated Message -->
              <div v-if="getTotalTransferredQuantity() === transferModal.principalQuantity" 
                   class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-sm font-medium text-green-800">
                    All available quantity ({{ transferModal.principalQuantity }}) has been allocated for transfer.
                  </span>
                </div>
              </div>
            </div>

            <!-- Step 4: Notes -->
            <div v-if="transferModal.selectedStockId && transferModal.principalWarehouse" class="mb-6">
              <h4 class="text-lg font-medium text-gray-900 mb-4">Step 4: Notes (Optional)</h4>
              <textarea v-model="transferModal.notes" 
                        rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Add notes about this transfer..."></textarea>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t">
              <button type="button" 
                      @click="closeTransferModal"
                      class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit" 
                      :disabled="transferModal.loading || !canCreateTransfer"
                      class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="transferModal.loading">Creating Transfer...</span>
                <span v-else>Create Transfer</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add Upsell Modal -->
    <div v-if="upsellModal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">{{ upsellModal.editingUpsellId ? 'Edit Upsell' : 'Add Upsell' }}</h3>
            <button @click="closeUpsellModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="mb-4">
            <div class="text-sm text-gray-600 mb-2">
              <strong>Product:</strong> {{ upsellModal.stock?.product?.name || upsellModal.stock?.title }}
            </div>
            <div class="text-sm text-gray-600 mb-4">
              <strong>Reference:</strong> {{ upsellModal.stock?.reference }}
            </div>
          </div>
          
          <form @submit.prevent="saveUpsells">
            <!-- Upsell Items List -->
            <div class="space-y-4">
              <div v-for="(upsell, index) in upsellModal.upsells" :key="index" 
                   class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h5 class="font-medium text-gray-900">Upsell {{ index + 1 }}</h5>
                  <button v-if="upsellModal.upsells.length > 1" 
                          @click="removeUpsellItem(index)"
                          type="button"
                          class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Quantity *
                    </label>
                    <input v-model.number="upsell.quantity" 
                           type="number" 
                           min="1"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Price *
                    </label>
                    <input v-model.number="upsell.price" 
                           type="number" 
                           step="0.01"
                           min="0"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                  </div>
                </div>
              </div>
              
              <!-- Add Upsell Button -->
              <button @click="addUpsellItem"
                      type="button"
                      class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Another Upsell
              </button>
            </div>
            
            <div class="flex gap-3 mt-6">
              <button type="button" 
                      @click="closeUpsellModal"
                      class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button type="submit" 
                      :disabled="upsellModal.loading"
                      class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="upsellModal.loading">Saving...</span>
                <span v-else>Save Upsells</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modify Upsells Modal -->
    <div v-if="modifyUpsellsModal.show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Modify Upsells</h3>
            <button @click="closeModifyUpsellsModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="mb-4">
            <div class="text-sm text-gray-600 mb-2">
              <strong>Product:</strong> {{ modifyUpsellsModal.stock?.product?.name || modifyUpsellsModal.stock?.title }}
            </div>
            <div class="text-sm text-gray-600 mb-4">
              <strong>Reference:</strong> {{ modifyUpsellsModal.stock?.reference }}
            </div>
          </div>
          
          <!-- Existing Upsells -->
          <div v-if="modifyUpsellsModal.upsells.length > 0" class="mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Existing Upsells</h4>
            <div class="space-y-4">
              <div v-for="(upsell, index) in modifyUpsellsModal.upsells" :key="upsell.id" 
                   class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h5 class="font-medium text-gray-900">Upsell #{{ index + 1 }}</h5>
                  <div class="flex gap-2">
                    <button @click="deleteUpsell(upsell.id)" 
                            class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                      Delete
                    </button>
                  </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-600">Quantity:</span>
                    <div class="text-gray-900 text-lg font-semibold">{{ upsell.quantity }}</div>
                  </div>
                  <div>
                    <span class="font-medium text-gray-600">Price:</span>
                    <div class="text-gray-900 text-lg font-semibold">{{ formatCurrency(upsell.price) }}</div>
                  </div>
                </div>
                <div class="mt-2">
                  <span :class="upsell.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ upsell.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          
          <!-- No Upsells Message -->
          <div v-if="modifyUpsellsModal.upsells.length === 0" class="text-center py-8 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="text-lg font-medium mb-2">No upsells found</p>
            <p class="text-sm">This product doesn't have any upsells yet.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Toast -->
    <div v-if="notification.show" 
         :class="[
           'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300',
           notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
         ]">
      <div class="flex items-center gap-2">
        <svg v-if="notification.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ notification.message }}</span>
        <button @click="hideNotification" class="ml-auto text-white hover:text-gray-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'

// Reactive data
const stocks = ref([])
const statistics = ref({})
const pagination = ref({ page: 1, total_pages: 1 })
const notification = ref({ show: false, message: '', type: 'success' })

// Warehouse edit modal
const warehouseEditModal = ref({
  show: false,
  stock: null,
  warehouse: null,
  newQuantity: 0,
  notes: '',
  loading: false
})

// Transfer modal
const transferModal = ref({
  show: false,
  selectedStockId: '',
  principalWarehouse: null,
  principalQuantity: 0,
  transfers: [{ to_warehouse_id: '', quantity: 1 }],
  notes: '',
  loading: false
})

// Upsell modal
const upsellModal = ref({
  show: false,
  stock: null,
  upsells: [{ quantity: 1, price: 0 }],
  loading: false
})

// Modify upsells modal
const modifyUpsellsModal = ref({
  show: false,
  stock: null,
  upsells: [],
  loading: false
})

// Available stocks for transfer
const availableStocks = ref([])

// Filter options
const availableProducts = ref([])
const availableWarehouses = ref([])
const availableSellers = ref([])

// Enhanced filters
const filters = ref({
  search: '',
  product_id: '',
  warehouse_id: '',
  seller_id: '',
  status: '',
  warehouse_location: '',
  min_price: '',
  max_price: ''
})


// User roles
const rawRoles = window.Laravel?.user?.roles || []
const roleNames = rawRoles.map(r => {
  const n = typeof r === 'string' ? r : (r.name || '')
  return n.toLowerCase()
})
const isSeller = roleNames.includes('seller')
const isAdmin = roleNames.includes('admin') || roleNames.includes('superadmin')
const isAgent = roleNames.includes('agent')

// Helper functions

const storageUrl = (path) => path ? `/storage/${path}` : ''

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-CA')
}

const formatCurrency = (amount) => {
  if (!amount) return 'N/A'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

const formatStatus = (status) => {
  const statusMap = {
    'in_stock': 'In Stock',
    'low_stock': 'Low Stock',
    'out_of_stock': 'Out of Stock'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    'in_stock': 'bg-green-100 text-green-800',
    'low_stock': 'bg-yellow-100 text-yellow-800',
    'out_of_stock': 'bg-red-100 text-red-800'
  }
  return classMap[status] || 'bg-gray-100 text-gray-800'
}

const getStatusRowClass = (status) => {
  if (status === 'low_stock') return 'bg-yellow-50'
  if (status === 'out_of_stock') return 'bg-red-50'
  return 'hover:bg-gray-50'
}

const getPageNumbers = () => {
  const pages = []
  const total = pagination.value.total_pages
  const current = pagination.value.page
  
  for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
    pages.push(i)
  }
  return pages
}

// Transfer modal computed properties
const canCreateTransfer = computed(() => {
  return transferModal.value.selectedStockId && 
         transferModal.value.principalWarehouse && 
         transferModal.value.transfers.length > 0 &&
         transferModal.value.transfers.every(t => t.to_warehouse_id && t.quantity > 0) &&
         getTotalTransferredQuantity() <= transferModal.value.principalQuantity &&
         getTotalTransferredQuantity() > 0
})

const getTotalTransferredQuantity = () => {
  return transferModal.value.transfers.reduce((total, transfer) => {
    return total + (transfer.quantity || 0)
  }, 0)
}

const getRemainingQuantity = () => {
  return transferModal.value.principalQuantity - getTotalTransferredQuantity()
}

// API calls
const fetchStocks = async (page = 1) => {
  const params = new URLSearchParams({ page })
  
  // Add filters
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      params.append(key, value)
    }
  })
  
  const res = await fetch(`/stocks-globale?${params}`, { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (!res.ok) { 
    stocks.value = []
    return 
  }
  
  const data = await res.json()
  stocks.value = data.data || []
  pagination.value = { 
    page: data.current_page || 1, 
    total_pages: data.last_page || 1 
  }
}

const fetchStatistics = async () => {
  const res = await fetch('/stocks-globale/statistics', { 
    headers: { 'Accept': 'application/json' }, 
    credentials: 'same-origin' 
  })
  
  if (res.ok) {
    const data = await res.json()
    statistics.value = data
  }
}

const fetchFilterOptions = async () => {
  // Fetch products
  const productsRes = await fetch('/stocks-globale/filter-options/products', {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (productsRes.ok) {
    availableProducts.value = await productsRes.json()
  }

  // Fetch warehouses
  const warehousesRes = await fetch('/stocks-globale/filter-options/warehouses', {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (warehousesRes.ok) {
    availableWarehouses.value = await warehousesRes.json()
  }

  // Fetch sellers
  const sellersRes = await fetch('/stocks-globale/filter-options/sellers', {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (sellersRes.ok) {
    availableSellers.value = await sellersRes.json()
  }
}

// Event handlers
const applyFilters = () => {
  fetchStocks(1)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    product_id: '',
    warehouse_id: '',
    seller_id: '',
    status: '',
    warehouse_location: '',
    min_price: '',
    max_price: ''
  }
  fetchStocks(1)
}

const exportData = async () => {
  const params = new URLSearchParams()
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      params.append(key, value)
    }
  })
  
  const res = await fetch(`/stocks-globale/export?${params}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  
  if (res.ok) {
    const blob = await res.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `stock-globale-${new Date().toISOString().split('T')[0]}.csv`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
    showNotification('Data exported successfully!', 'success')
  } else {
    showNotification('Failed to export data', 'error')
  }
}

const changePage = (p) => {
  if (p < 1 || p > pagination.value.total_pages) return
  fetchStocks(p)
}


const showNotification = (message, type = 'success') => {
  notification.value = { show: true, message, type }
  setTimeout(() => {
    hideNotification()
  }, 5000)
}

const hideNotification = () => {
  notification.value.show = false
}

// Warehouse edit modal methods
const openWarehouseEditModal = (stock, warehouse) => {
  warehouseEditModal.value = {
    show: true,
    stock: stock,
    warehouse: warehouse,
    newQuantity: warehouse.remaining_quantity,
    notes: '',
    loading: false
  }
}

const closeWarehouseEditModal = () => {
  warehouseEditModal.value = {
    show: false,
    stock: null,
    warehouse: null,
    newQuantity: 0,
    notes: '',
    loading: false
  }
}

const updateWarehouseQuantity = async () => {
  if (!warehouseEditModal.value.stock || !warehouseEditModal.value.warehouse) {
    return
  }

  warehouseEditModal.value.loading = true

  try {
    const response = await fetch(`/stocks-globale/${warehouseEditModal.value.stock.id}/warehouse-quantity`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        warehouse_id: warehouseEditModal.value.warehouse.warehouse_id,
        quantity: warehouseEditModal.value.newQuantity,
        notes: warehouseEditModal.value.notes
      })
    })

    if (response.ok) {
      const data = await response.json()
      showNotification('Warehouse quantity updated successfully!', 'success')
      closeWarehouseEditModal()
      // Refresh the stocks data
      fetchStocks(pagination.value.page)
    } else {
      const errorData = await response.json()
      showNotification(errorData.message || 'Failed to update warehouse quantity', 'error')
    }
  } catch (error) {
    console.error('Error updating warehouse quantity:', error)
    showNotification('An error occurred while updating warehouse quantity', 'error')
  } finally {
    warehouseEditModal.value.loading = false
  }
}

// Transfer modal methods
const openTransferModal = async () => {
  transferModal.value.show = true
  await fetchAvailableStocks()
}

const closeTransferModal = () => {
  transferModal.value = {
    show: false,
    selectedStockId: '',
    principalWarehouse: null,
    principalQuantity: 0,
    transfers: [{ to_warehouse_id: '', quantity: 1 }],
    notes: '',
    loading: false
  }
}

const fetchAvailableStocks = async () => {
  try {
    const response = await fetch('/stocks-globale/available-stocks', {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      availableStocks.value = await response.json()
    }
  } catch (error) {
    console.error('Error fetching available stocks:', error)
    showNotification('Failed to load available stocks', 'error')
  }
}

const onStockSelected = async () => {
  if (!transferModal.value.selectedStockId) {
    transferModal.value.principalWarehouse = null
    transferModal.value.principalQuantity = 0
    return
  }

  try {
    const response = await fetch(`/stocks-globale/${transferModal.value.selectedStockId}/principal-warehouse`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const data = await response.json()
      transferModal.value.principalWarehouse = data.warehouse
      transferModal.value.principalQuantity = data.quantity
      
      // Reset transfers
      transferModal.value.transfers = [{ to_warehouse_id: '', quantity: 1 }]
    } else {
      const errorData = await response.json()
      showNotification(errorData.message || 'Failed to load principal warehouse', 'error')
    }
  } catch (error) {
    console.error('Error fetching principal warehouse:', error)
    showNotification('Failed to load principal warehouse', 'error')
  }
}

const addTransfer = () => {
  transferModal.value.transfers.push({ to_warehouse_id: '', quantity: 1 })
}

const removeTransfer = (index) => {
  transferModal.value.transfers.splice(index, 1)
}

const createTransfer = async () => {
  if (!canCreateTransfer.value) {
    return
  }

  transferModal.value.loading = true

  try {
    const response = await fetch('/stocks-globale/transfers', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        stock_id: transferModal.value.selectedStockId,
        from_warehouse_id: transferModal.value.principalWarehouse.id,
        transfers: transferModal.value.transfers,
        notes: transferModal.value.notes
      })
    })

    if (response.ok) {
      const data = await response.json()
      showNotification('Transfer created successfully!', 'success')
      closeTransferModal()
      // Refresh the stocks data
      fetchStocks(pagination.value.page)
    } else {
      const errorData = await response.json()
      showNotification(errorData.message || 'Failed to create transfer', 'error')
    }
  } catch (error) {
    console.error('Error creating transfer:', error)
    showNotification('An error occurred while creating transfer', 'error')
  } finally {
    transferModal.value.loading = false
  }
}

// Upsell modal methods
const openUpsellModal = (stock) => {
  upsellModal.value = {
    show: true,
    stock: stock,
    upsells: [{ quantity: 1, price: 0 }],
    loading: false
  }
}

const closeUpsellModal = () => {
  upsellModal.value = {
    show: false,
    stock: null,
    upsells: [{ quantity: 1, price: 0 }],
    loading: false
  }
}

const addUpsellItem = () => {
  upsellModal.value.upsells.push({ quantity: 1, price: 0 })
}

const removeUpsellItem = (index) => {
  if (upsellModal.value.upsells.length > 1) {
    upsellModal.value.upsells.splice(index, 1)
  }
}

const saveUpsells = async () => {
  if (!upsellModal.value.stock) {
    return
  }

  // Validate that all upsells have quantity and price
  const validUpsells = upsellModal.value.upsells.filter(upsell => 
    upsell.quantity > 0 && upsell.price >= 0
  )

  if (validUpsells.length === 0) {
    showNotification('Please add at least one valid upsell', 'error')
    return
  }

  upsellModal.value.loading = true

  try {
    const response = await fetch(`/stocks-globale/${upsellModal.value.stock.id}/upsells/bulk`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({ upsells: validUpsells })
    })

    if (response.ok) {
      const data = await response.json()
      showNotification(`Successfully created ${validUpsells.length} upsell${validUpsells.length > 1 ? 's' : ''}!`, 'success')
      closeUpsellModal()
      // Refresh the main stocks data to show new upsells immediately
      await fetchStocks(pagination.value.page)
      // If modify modal is open, refresh its data
      if (modifyUpsellsModal.value.show) {
        await fetchUpsells(upsellModal.value.stock.id)
      }
    } else {
      const errorData = await response.json()
      showNotification(errorData.message || 'Failed to create upsells', 'error')
    }
  } catch (error) {
    console.error('Error creating upsells:', error)
    showNotification('An error occurred while creating upsells', 'error')
  } finally {
    upsellModal.value.loading = false
  }
}

// Modify upsells modal methods
const openModifyUpsellsModal = async (stock) => {
  modifyUpsellsModal.value = {
    show: true,
    stock: stock,
    upsells: [],
    loading: false
  }
  
  await fetchUpsells(stock.id)
}

const closeModifyUpsellsModal = () => {
  modifyUpsellsModal.value = {
    show: false,
    stock: null,
    upsells: [],
    loading: false
  }
}

const fetchUpsells = async (stockId) => {
  try {
    const response = await fetch(`/stocks-globale/${stockId}/upsells`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      modifyUpsellsModal.value.upsells = await response.json()
    } else {
      showNotification('Failed to load upsells', 'error')
    }
  } catch (error) {
    console.error('Error fetching upsells:', error)
    showNotification('Failed to load upsells', 'error')
  }
}


const deleteUpsell = async (upsellId) => {
  if (!confirm('Are you sure you want to delete this upsell?')) {
    return
  }

  try {
    const response = await fetch(`/stocks-globale/${modifyUpsellsModal.value.stock.id}/upsells/${upsellId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    if (response.ok) {
      showNotification('Upsell deleted successfully!', 'success')
      // Refresh the main stocks data to reflect upsell deletion immediately
      await fetchStocks(pagination.value.page)
      await fetchUpsells(modifyUpsellsModal.value.stock.id)
    } else {
      const errorData = await response.json()
      showNotification(errorData.message || 'Failed to delete upsell', 'error')
    }
  } catch (error) {
    console.error('Error deleting upsell:', error)
    showNotification('An error occurred while deleting upsell', 'error')
  }
}

// Initialize
onMounted(() => {
  fetchStocks()
  fetchStatistics()
  fetchFilterOptions()
})
</script>
