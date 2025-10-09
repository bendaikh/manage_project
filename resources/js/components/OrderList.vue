<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Order Management</h2>
      <button @click="$emit('create-order')" class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold text-sm lg:text-base">
        + Create Order
      </button>
    </div>
    <!-- Filter Orders -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <!-- Date Range Filter -->
      <div class="mb-6">
        <h3 class="font-semibold text-lg mb-4">Date Range Filter</h3>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <div class="md:col-span-5">
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <input v-model="startDate" type="date" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="md:col-span-5">
            <label class="block text-sm font-medium mb-1">End Date</label>
            <input v-model="endDate" type="date" class="w-full border rounded px-3 py-2" />
          </div>
          <div class="md:col-span-2 flex gap-2">
            <button @click="applyDateFilter" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filter</button>
            <button @click="resetDateFilter" class="flex-1 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Reset</button>
          </div>
        </div>
        <div class="flex flex-wrap gap-2 mt-4">
          <button v-for="opt in quickRanges" :key="opt.label" @click="setQuickRange(opt)" class="px-3 py-1 text-sm rounded border hover:bg-gray-100" :class="{ 'bg-blue-50 text-blue-600': activeRange === opt.label }">
            {{ opt.label }}
          </button>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <input v-model="filters.search" @keyup.enter="fetchOrders" type="text" placeholder="Search by Order ID, Product, Client, Phone..." class="w-full border rounded px-3 py-2 text-sm lg:text-base" />
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
        <!-- Seller filter - hidden for sellers -->
        <select v-if="!isSeller" v-model="filters.seller" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Sellers</option>
          <option v-for="seller in sellers" :key="seller" :value="seller">{{ seller }}</option>
        </select>
        
        <!-- Status filter - always shown -->
        <select v-model="filters.status" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Statuses</option>
          <option v-for="status in allowedStatuses" :key="status" :value="status">{{ status }}</option>
        </select>
        
        <!-- Agent filter - hidden for sellers -->
        <select v-if="!isSeller" v-model="filters.agent" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Agents</option>
          <option v-for="agent in agents" :key="agent" :value="agent">{{ agent }}</option>
        </select>
        
        <!-- Zone filter - hidden for sellers -->
        <select v-if="!isSeller" v-model="filters.zone" class="w-full border rounded px-3 py-2 text-sm lg:text-base">
          <option value="">All Zones</option>
          <option v-for="zone in zones" :key="zone" :value="zone">{{ zone }}</option>
        </select>
        
        <div class="flex flex-col sm:flex-row gap-2">
          <button @click="fetchOrders" class="px-3 lg:px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center justify-center text-sm lg:text-base">
            <svg class="h-4 w-4 lg:h-5 lg:w-5 mr-1 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4h13M8 12h13M8 20h13M3 6h.01M3 18h.01"/></svg>
            <span class="hidden sm:inline">Apply Filters</span>
            <span class="sm:hidden">Apply</span>
          </button>
          <button @click="clearFilters" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 text-sm">Reset</button>
        </div>
      </div>
    </div>
    <!-- Status Summary (Confirmation section) -->
    <div v-if="props.confirmation" class="mb-4">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm font-medium text-gray-700">Today's Orders Status Summary</div>
        <button 
          @click="applyTodayWorkFilter" 
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2"
        >
          <span>See Today Work</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="status in confirmationStatusBlocks" :key="status.name" 
             :class="['flex flex-col items-center justify-center rounded-lg shadow p-3 border', getStatusColor(status.name)]">
          <span class="text-xs font-semibold">{{ status.name }}</span>
          <span class="text-lg font-bold">{{ status.count }}</span>
        </div>
      </div>
    </div>
    
    <!-- Status Summary (Delivery section) -->
    <div v-if="props.delivery" class="mb-4">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm font-medium text-gray-700">Today's Orders Status Summary</div>
        <button 
          @click="applyTodayWorkFilter" 
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2"
        >
          <span>See Today Work</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="status in deliveryStatusBlocks" :key="status.name" 
             :class="['flex flex-col items-center justify-center rounded-lg shadow p-3 border', getStatusColor(status.name)]">
          <span class="text-xs font-semibold">{{ status.name }}</span>
          <span class="text-lg font-bold">{{ status.count }}</span>
        </div>
      </div>
    </div>
    <!-- Action bar -->
    <div v-if="showActionBar" class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-blue-50 border border-blue-200 rounded p-3 mb-3 gap-3">
      <div class="text-sm lg:text-base">{{ selectedIds.size }} selected</div>
      <div class="flex flex-col sm:flex-row gap-2">
        <!-- Assignment button -->
        <button v-if="showAssignButton" @click="openAssignmentModal" class="px-3 lg:px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm flex items-center gap-1">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          Assign to Agent
        </button>
        <button v-if="showDeliveryNoteButton" @click="downloadDeliveryNote" class="px-3 lg:px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Download Delivery Note</button>
        <button v-if="showInvoiceButton"
          @click="downloadInvoices" 
          :disabled="!canDownloadInvoices"
          :class="[
            'px-3 lg:px-4 py-2 rounded text-sm relative',
            canDownloadInvoices 
              ? 'bg-violet-600 text-white hover:bg-violet-700' 
              : 'bg-gray-400 text-gray-200 cursor-not-allowed'
          ]"
          :title="!canDownloadInvoices ? 'Download delivery notes first before downloading invoices' : ''"
        >
          Download Invoices
          <span v-if="!canDownloadInvoices" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">!</span>
        </button>
        <button v-if="showMarkShippedButton"
          @click="markAsShipped" 
          :disabled="!canMarkAsShipped"
          :class="[
            'px-3 lg:px-4 py-2 rounded text-sm relative',
            canMarkAsShipped 
              ? 'bg-blue-600 text-white hover:bg-blue-700' 
              : 'bg-gray-400 text-gray-200 cursor-not-allowed'
          ]"
          :title="!canMarkAsShipped ? 'Download both delivery notes and invoices first' : 'Mark orders as shipped'"
        >
          Mark as Shipped
          <span v-if="!canMarkAsShipped" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">!</span>
        </button>
      </div>
    </div>
    
    <!-- Invoice Delivery Button (only for delivery page and not for sellers) -->
    <div v-if="props.delivery && !isSeller" class="flex justify-end mb-4">
      <button 
        @click="generateDeliveryInvoice" 
        :disabled="!hasDeliveredOrdersToday"
        :class="[
          'px-4 lg:px-6 py-2 lg:py-3 font-semibold flex items-center gap-2 text-sm lg:text-base rounded-lg',
          hasDeliveredOrdersToday 
            ? 'bg-orange-600 text-white hover:bg-orange-700' 
            : 'bg-gray-400 text-gray-600 cursor-not-allowed'
        ]"
      >
        <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="hidden sm:inline">
          {{ hasDeliveredOrdersToday ? 'Invoice Delivered Yaoundi' : 'No Delivered Orders Today' }}
        </span>
        <span class="sm:hidden">
          {{ hasDeliveredOrdersToday ? 'Delivered' : 'No Orders' }}
        </span>
        <span v-if="deliveredOrdersTodayCount" class="ml-2 bg-white text-orange-600 font-bold px-2 py-0.5 rounded-full text-xs">
          {{ deliveredOrdersTodayCount }}
        </span>
      </button>
    </div>
    
    <!-- Orders Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-2 lg:px-3 py-2"><input type="checkbox" :checked="allSelected" @change="toggleSelectAll"></th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">ORDER ID</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden lg:table-cell">SELLER</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">PRODUCT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">PRICE</th>
            <th v-if="props.confirmation" class="px-2 lg:px-3 py-2 text-left text-xs font-bold">UPSELLS</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden md:table-cell">CLIENT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden lg:table-cell">AGENT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">STATUS</th>
            <th v-if="props.delivery" class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden xl:table-cell">WAREHOUSE</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden lg:table-cell">DATE</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden xl:table-cell">ZONE</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold hidden xl:table-cell">COMMENT</th>
            <th class="px-2 lg:px-3 py-2 text-left text-xs font-bold">ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="border-b">
            <td class="px-2 lg:px-3 py-2 text-center"><input type="checkbox" :value="order.id" v-model="checked"></td>
            <td class="px-2 lg:px-3 py-2 font-mono text-xs">
              <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ order.id }}</span>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden lg:table-cell">{{ order.seller }}</td>
            <td class="px-2 lg:px-3 py-2 flex items-center gap-2">
              <img v-if="order.product && order.product.image_url" :src="order.product.image_url" class="w-8 h-8 lg:w-10 lg:h-10 object-cover rounded" />
              <div class="min-w-0 flex-1">
                <div class="font-semibold truncate text-sm lg:text-base">{{ order.product ? order.product.name : '' }}</div>
                <div class="text-xs text-gray-500">Qty: {{ order.quantity }}</div>
                <div class="text-xs text-gray-400 hidden sm:block">SKU: {{ order.product ? order.product.sku : '' }}</div>
              </div>
              <button 
                v-if="order.product" 
                @click="showProductDetails(order)"
                class="p-1 text-gray-400 hover:text-blue-600 transition-colors duration-200"
                title="View product details"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </td>
            <td class="px-2 lg:px-3 py-2 font-bold text-sm lg:text-base">{{ formatCurrency(order.price) }}</td>
            <td v-if="props.confirmation" class="px-2 lg:px-3 py-2">
              <div v-if="order.upsells && order.upsells.length > 0" class="space-y-1">
                <div v-for="upsell in order.upsells" :key="upsell.id" 
                     class="flex items-center justify-between bg-green-50 px-2 py-1 rounded text-xs">
                  <span class="font-medium text-green-700">{{ upsell.quantity }}x</span>
                  <span class="font-semibold text-green-800">{{ formatCurrency(upsell.price) }}</span>
                </div>
              </div>
              <span v-else class="text-gray-400 text-xs">No upsells</span>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden md:table-cell">
              <div class="text-sm">{{ order.client_name }}</div>
              <div class="text-xs text-gray-500 truncate">{{ order.client_address }}</div>
              <div class="text-xs text-gray-400">{{ order.client_phone }}</div>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden lg:table-cell">
              <div class="text-sm">{{ order.agent || 'Mme' }}</div>
              <!-- Show assigned agent if available -->
              <div v-if="order.assignment && order.assignment.assigned_to" class="text-xs text-purple-600">
                Assigned to: {{ order.assignment.assigned_to.name }}
              </div>
            </td>
            <td class="px-2 lg:px-3 py-2">
              <button @click="openStatusModal(order)" :class="getStatusClass(order.status) + ' px-2 py-1 rounded text-xs focus:outline-none'">
                {{ order.status }}
              </button>
            </td>
            <td v-if="props.delivery" class="px-2 lg:px-3 py-2 hidden xl:table-cell">
              <div v-if="order.warehouse" class="text-xs text-green-600 font-medium">
                {{ order.warehouse.name }}
              </div>
              <div v-else class="text-xs text-gray-400">
                Not assigned
              </div>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden lg:table-cell">
              <div class="text-xs text-gray-600">Created: {{ formatDate(order.created_at) }}</div>
              <div class="text-xs text-blue-600">Updated: {{ formatDate(order.updated_at) }}</div>
            </td>
            <td class="px-2 lg:px-3 py-2 hidden xl:table-cell">{{ order.zone }}</td>
            <td class="px-2 lg:px-3 py-2 truncate max-w-xs hidden xl:table-cell">{{ order.comment }}</td>
            <td class="px-2 lg:px-3 py-2 flex gap-1 lg:gap-2">
              <button class="text-blue-600 hover:text-blue-800 p-1" @click="openDetails(order)">
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button class="text-green-600 hover:text-green-800 p-1" @click="openEdit(order)">
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11V9a3 3 0 013-3h0a3 3 0 013 3v2m0 0v2a3 3 0 01-3 3h0a3 3 0 01-3-3v-2m6 0H9" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.232 4.232a2 2 0 112.828 2.828l-10 10a2 2 0 01-2.828 0l-2-2a2 2 0 010-2.828l10-10z" />
                </svg>
              </button>
              <button class="text-red-600 hover:text-red-800 p-1">
                <svg class="h-4 w-4 lg:h-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a2 2 0 012 2v2H7V5a2 2 0 012-2z" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Global Actions Bar (Confirmation Section Only) -->
    <div v-if="props.confirmation && selectedIds.size > 0" class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg shadow-lg p-4 mt-4">
      <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <!-- Selection Info -->
        <div class="flex items-center gap-3">
          <div class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold text-lg">
            {{ selectedIds.size }}
          </div>
          <div>
            <div class="font-semibold text-gray-800 text-lg">
              {{ selectedIds.size }} Order{{ selectedIds.size > 1 ? 's' : '' }} Selected
            </div>
            <div class="text-sm text-gray-600">Choose an action to apply to all selected orders</div>
          </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
          <!-- Status Change Dropdown -->
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Change Status To:</label>
            <select 
              v-model="bulkStatusChange" 
              class="flex-1 sm:flex-none px-4 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium"
            >
              <option value="">-- Select Status --</option>
              <option v-for="status in availableStatuses" :key="status" :value="status">
                {{ status }}
              </option>
            </select>
          </div>
          
          <!-- Apply Button -->
          <button 
            @click="applyBulkStatusChange" 
            :disabled="!bulkStatusChange || isBulkUpdating"
            :class="[
              'px-6 py-2 rounded-lg font-semibold text-white transition-all duration-200 flex items-center justify-center gap-2',
              bulkStatusChange && !isBulkUpdating
                ? 'bg-green-600 hover:bg-green-700 shadow-md hover:shadow-lg transform hover:-translate-y-0.5' 
                : 'bg-gray-400 cursor-not-allowed'
            ]"
          >
            <svg v-if="!isBulkUpdating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>{{ isBulkUpdating ? 'Applying...' : 'Apply to Selected' }}</span>
          </button>
          
          <!-- Clear Selection Button -->
          <button 
            @click="clearSelection" 
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors duration-200"
          >
            Clear Selection
          </button>
        </div>
      </div>
    </div>
    <!-- Pagination -->
    <nav v-if="totalPages > 1 || orders.length > 0" class="flex flex-col items-center mt-4 space-y-2">
      <!-- Items per page selector -->
      <div class="flex items-center space-x-4 text-sm text-gray-600">
        <span>Show:</span>
        <select 
          v-model="perPage" 
          @change="changePerPage"
          class="px-3 py-1 border rounded bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>items per page</span>
        <span class="ml-4 text-gray-500">Total: {{ orders.length > 0 ? (totalPages * perPage) : 0 }} items</span>
      </div>
      
      <!-- Page Navigation -->
      <ul v-if="totalPages > 1" class="inline-flex">
        <li>
          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1 border rounded-l"
            :class="currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'"
          >
            &laquo;
          </button>
        </li>
        <li v-for="page in pageNumbers" :key="page">
          <button
            v-if="page !== '...'"
            @click="changePage(page)"
            class="px-3 py-1 border-t border-b"
            :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100'"
          >
            {{ page }}
          </button>
          <span
            v-else
            class="px-3 py-1 border-t border-b bg-gray-50 text-gray-500"
          >
            {{ page }}
          </span>
        </li>
        <li>
          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 border rounded-r"
            :class="currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-100'"
          >
            &raquo;
          </button>
        </li>
      </ul>
      
      <!-- Page Info and Go to Page (for large datasets) -->
      <div v-if="totalPages > 10" class="flex items-center space-x-4 text-sm text-gray-600">
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <div class="flex items-center space-x-2">
          <span>Go to:</span>
          <input
            v-model="goToPage"
            type="number"
            min="1"
            :max="totalPages"
            class="w-16 px-2 py-1 border rounded text-center"
            @keyup.enter="goToPageNumber"
          />
          <button
            @click="goToPageNumber"
            class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs"
          >
            Go
          </button>
        </div>
      </div>
    </nav>

    <OrderDetailsModal v-if="showDetails" :order="selectedOrder" :confirmation="confirmation" :delivery="delivery" @close="closeDetails" @edit="openEdit" />
    
    <!-- Order Edit Modal -->
    <div v-if="showEdit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeEdit">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <OrderEdit :order="editOrder" :products="productsList" :confirmation="confirmation" :delivery="delivery" @cancel="closeEdit" @updated="handleUpdated" />
      </div>
    </div>
    
    <!-- Assignment Modal -->
    <div v-if="showAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeAssignmentModal">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Assign Orders to Agent</h3>
          <button @click="closeAssignmentModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">
            Assigning <strong>{{ selectedIds.size }}</strong> order(s) to an agent
          </p>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Agent</label>
          <select v-model="assignmentForm.agentId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose an agent...</option>
            <option v-for="agent in availableAgents" :key="agent.id" :value="agent.id">
              {{ agent.name }} ({{ agent.email }})
            </option>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea v-model="assignmentForm.notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any notes about this assignment..."></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closeAssignmentModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="assignOrders" :disabled="!assignmentForm.agentId || isAssigning" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50">
            {{ isAssigning ? 'Assigning...' : 'Assign Orders' }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Status Modal inline -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeStatusModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-sm max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Change Status</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <button v-for="st in availableStatuses" :key="st" @click="updateOrderStatus(st)" :class="getStatusClass(st)+' text-sm px-3 py-2 rounded text-center'">
            {{ st }}
          </button>
        </div>
        <button class="mt-4 w-full text-center px-4 py-2 bg-gray-200 rounded" @click="closeStatusModal">Cancel</button>
      </div>
    </div>
    
    <!-- Warehouse Selection Modal -->
    <div v-if="showWarehouseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeWarehouseModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Select Warehouse for Order Confirmation</h3>
        <p class="text-sm text-gray-600 mb-4">Please select which warehouse this order will take products from:</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Warehouse</label>
          <select v-model="selectedWarehouseId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select a warehouse</option>
            <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
              {{ warehouse.name }} - {{ warehouse.location }}
            </option>
          </select>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closeWarehouseModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="confirmOrderWithWarehouse" :disabled="!selectedWarehouseId" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
            Confirm Order
          </button>
        </div>
      </div>
    </div>
    <!-- Confirmation Date Modal -->
    <div v-if="showConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeConfirmationModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Confirm Order on Date</h3>
        <p class="text-sm text-gray-600 mb-4">Please set the confirmation date and add any comments:</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Date Confirmed with Client</label>
          <input v-model="confirmationForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
          <textarea v-model="confirmationForm.comment" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any comments about the confirmation..."></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closeConfirmationModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="submitConfirmation" :disabled="!confirmationForm.date" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
            Confirm Order
          </button>
        </div>
      </div>
    </div>
    
    <!-- Postponed Modal -->
    <div v-if="showPostponedModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closePostponedModal">
      <div class="bg-white rounded-lg shadow-lg p-4 lg:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Postpone Order</h3>
        <p class="text-sm text-gray-600 mb-4">Please set the postponed date and add any comments:</p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Postponed Date</label>
          <input v-model="postponedForm.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
          <textarea v-model="postponedForm.comment" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Add any comments about the postponement..."></textarea>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button @click="closePostponedModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Cancel
          </button>
          <button @click="submitPostponed" :disabled="!postponedForm.date" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 disabled:opacity-50">
            Postpone Order
          </button>
        </div>
      </div>
    </div>

    <!-- Product Details Modal -->
    <div v-if="showProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeProductModal">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold">Product Details</h3>
          <button @click="closeProductModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div v-if="selectedProduct" class="space-y-6">
          <!-- Product Image and Basic Info -->
          <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <div class="w-full md:w-64 h-64 bg-gray-100 rounded-lg overflow-hidden">
                <img 
                  v-if="selectedProduct.image_url" 
                  :src="selectedProduct.image_url" 
                  :alt="selectedProduct.name" 
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
            </div>
            
            <div class="flex-1 space-y-4">
              <div>
                <h4 class="text-2xl font-bold text-gray-900">{{ selectedProduct.name }}</h4>
                <p class="text-gray-600">SKU: {{ selectedProduct.sku }}</p>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="font-semibold text-gray-700">Category:</span>
                  <p class="text-gray-600">{{ selectedProduct.category || 'N/A' }}</p>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Supplier:</span>
                  <p class="text-gray-600">{{ selectedProduct.supplier || 'N/A' }}</p>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Purchase Price:</span>
                  <p class="text-gray-600">{{ selectedProduct.purchase_price ? selectedProduct.purchase_price + ' ' + getCurrency() : 'N/A' }}</p>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Selling Price:</span>
                  <p class="text-gray-600">{{ selectedProduct.selling_price ? selectedProduct.selling_price + ' ' + getCurrency() : 'N/A' }}</p>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Stock Quantity:</span>
                  <p class="text-gray-600">{{ selectedProduct.stock_quantity || 'N/A' }}</p>
                </div>
                <div>
                  <span class="font-semibold text-gray-700">Status:</span>
                  <span :class="getStatusClass(selectedProduct.status)" class="px-2 py-1 rounded text-xs">
                    {{ selectedProduct.status || 'N/A' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Product Description -->
          <div v-if="selectedProduct.description">
            <h5 class="font-semibold text-gray-700 mb-2">Description:</h5>
            <p class="text-gray-600">{{ selectedProduct.description }}</p>
          </div>
          
          <!-- Product Link -->
          <div v-if="selectedProduct.product_link">
            <h5 class="font-semibold text-gray-700 mb-2">Product Link:</h5>
            <a 
              :href="selectedProduct.product_link" 
              target="_blank" 
              rel="noopener noreferrer"
              class="text-blue-600 hover:text-blue-800 underline break-all"
            >
              {{ selectedProduct.product_link }}
            </a>
            <p class="text-xs text-gray-500 mt-1">Click to view product details</p>
          </div>
          
          <!-- Video if available -->
          <div v-if="selectedProduct.video_url">
            <h5 class="font-semibold text-gray-700 mb-2">Product Video:</h5>
            <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
              <video 
                :src="selectedProduct.video_url" 
                controls 
                class="w-full h-full"
                v-if="selectedProduct.video_url"
              >
                Your browser does not support the video tag.
              </video>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end mt-6">
          <button @click="closeProductModal" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Toast inline -->
    <div v-if="toastMessage" :class="'toast px-4 py-2 rounded text-white '+(toastType==='success'?'bg-green-600':'bg-red-600')">
      {{ toastMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, computed, watch } from 'vue'
import OrderDetailsModal from './OrderDetailsModal.vue'
import OrderEdit from './OrderEdit.vue'
import { useCurrency } from '../composables/useCurrency'

const { formatCurrency, getCurrency } = useCurrency()

const props = defineProps({
  confirmation: Boolean,
  delivery: Boolean
})

const orders = ref([])
// NEW: global count of today delivered orders (comes from backend)
const deliveredOrdersTodayCount = ref(0)
// Holds counts of orders per status across confirmation section
const statusCounts = ref({})
// Pagination state
const currentPage = ref(1)
const perPage = ref(10) // You can adjust default items per page here
const totalPages = ref(1)
const goToPage = ref('')

const pageNumbers = computed(() => {
  const pages = []
  const current = currentPage.value
  const total = totalPages.value
  
  // If we have 10 or fewer pages, show all
  if (total <= 10) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
    return pages
  }
  
  // Always show first page
  pages.push(1)
  
  // Calculate range around current page
  const start = Math.max(2, current - 2)
  const end = Math.min(total - 1, current + 2)
  
  // Add ellipsis if there's a gap after first page
  if (start > 2) {
    pages.push('...')
  }
  
  // Add pages around current page
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  // Add ellipsis if there's a gap before last page
  if (end < total - 1) {
    pages.push('...')
  }
  
  // Always show last page (if not already included)
  if (total > 1) {
    pages.push(total)
  }
  
  return pages
})

const sellers = ref([])
// Full list for filter dropdown (can still show all)
const statuses = ref(['New Order','Confirmed','Confirmed on Date','Unreachable','Postponed','Cancelled','Blacklisted','Out of Stock','Processing','Shipped','Delivered','Expired'])

// Allowed statuses per section
const statusConfig = {
  all: ['New Order'],
  confirmation: ['New Order', 'Confirmed', 'Confirmed on Date', 'Unreachable', 'Postponed', 'Cancelled', 'Blacklisted', 'Out of Stock', 'Expired'],
  delivery: ['Processing', 'Shipped', 'Unreachable', 'Postponed', 'Cancelled', 'Delivered', 'Out of Stock', 'Expired']
}

const allowedStatuses = computed(() => {
  if (props.confirmation) return statusConfig.confirmation
  if (props.delivery) return statusConfig.delivery
  return statusConfig.all
})
const agents = ref([])
const zones = ref([])
const filters = ref({ search: '', seller: '', status: '', agent: '', zone: '' })

// Date range filter variables
const startDate = ref('')
const endDate = ref('')
const activeRange = ref('')
const quickRanges = [
  { label: 'Today', days: 0 },
  { label: 'Yesterday', days: 1 },
  { label: 'Last 7 days', days: 7 },
  { label: 'Last 30 days', days: 30 },
  { label: 'This Month', days: null },
  { label: 'Last Month', days: null }
]
const showDetails = ref(false)
const selectedOrder = ref(null)
const showEdit = ref(false)
const editOrder = ref(null)
const productsList = ref([])
const checked = ref([])
const selectedIds = computed(() => new Set(checked.value))
const allSelected = computed(() => orders.value.length && checked.value.length === orders.value.length)

// Check if selected orders have delivery notes downloaded
const canDownloadInvoices = computed(() => {
  if (selectedIds.value.size === 0) return false
  
  // Check if all selected orders have had their delivery notes downloaded
  return Array.from(selectedIds.value).every(orderId => 
    deliveryNotesDownloaded.value.has(orderId)
  )
})

// Check if orders are ready to be marked as shipped (both delivery notes and invoices downloaded)
const canMarkAsShipped = computed(() => {
  if (selectedIds.value.size === 0) return false
  
  // Check if all selected orders have had both delivery notes and invoices downloaded
  // AND are in Processing, Confirmed, or Delivered status (not already Shipped)
  return Array.from(selectedIds.value).every(orderId => {
    const order = orders.value.find(o => o.id === orderId)
    return order && 
           (order.status === 'Processing' || order.status === 'Confirmed' || order.status === 'Delivered') &&
           deliveryNotesDownloaded.value.has(orderId) && 
           invoicesDownloaded.value.has(orderId)
  })
})
// Statuses shown in change-status modal (restricted per section)
const availableStatuses = computed(() => allowedStatuses.value)
const showStatusModal = ref(false)
const statusTargetOrder = ref(null)
const showWarehouseModal = ref(false)
const warehouseTargetOrder = ref(null)
const warehouses = ref([])
const selectedWarehouseId = ref('')
const toastMessage = ref('')
const toastType = ref('success')

// Confirmation modal refs
const showConfirmationModal = ref(false)
const confirmationTargetOrder = ref(null)
const confirmationForm = ref({
  date: '',
  comment: ''
})

// Postponed modal refs
const showPostponedModal = ref(false)
const postponedTargetOrder = ref(null)
const postponedForm = ref({
  date: '',
  comment: ''
})

// Assignment related refs
const showAssignmentModal = ref(false)
const availableAgents = ref([])
const isAssigning = ref(false)
const assignmentForm = ref({
  agentId: '',
  notes: ''
})

// Product details modal refs
const showProductModal = ref(false)
const selectedProduct = ref(null)

// Delivery note tracking for invoice download restriction
const deliveryNotesDownloaded = ref(new Set())
const invoicesDownloaded = ref(new Set())

// Bulk status change refs
const bulkStatusChange = ref('')
const isBulkUpdating = ref(false)

// Check if user is superadmin
const isSuperadmin = computed(() => {
  const roles = window.Laravel?.user?.roles || []
  return roles.includes('superadmin') || roles.some(role => typeof role === 'object' && role.name === 'superadmin')
})

// Check if user is a seller
const isSeller = computed(() => {
  const roles = window.Laravel?.user?.roles || []
  return roles.includes('seller') || roles.some(r => typeof r === 'object' && r.name === 'seller')
})

const permissions = window.Laravel?.user?.permissions || []

// Permission-based assignment availability (regardless of section)
const baseCanAssignToAgent = computed(() => isSuperadmin.value || permissions.includes('assign_orders_to_agents') || permissions.includes('manage_orders'))

// Section-specific button visibility
const showAssignButton = computed(() => !props.confirmation && !props.delivery && baseCanAssignToAgent.value)
const showDeliveryNoteButton = computed(() => props.delivery)
const showInvoiceButton = computed(() => props.delivery)
const showMarkShippedButton = computed(() => props.delivery)

// Determine if action bar should render at all
const showActionBar = computed(() => selectedIds.value.size && (showAssignButton.value || showDeliveryNoteButton.value || showInvoiceButton.value || showMarkShippedButton.value))

// Check if there are delivered orders today for invoice generation (global count)
const hasDeliveredOrdersToday = computed(() => props.delivery && deliveredOrdersTodayCount.value > 0)

const fetchOrders = async () => {
  let url = `/orders/list?page=${currentPage.value}&per_page=${perPage.value}`
  if (filters.value.search) url += `&search=${encodeURIComponent(filters.value.search)}`
  if (filters.value.seller) url += `&seller=${encodeURIComponent(filters.value.seller)}`
  if (filters.value.status) {
    url += `&status=${encodeURIComponent(filters.value.status)}`
  } else {
    // Apply default status set for this section
    url += `&status=${encodeURIComponent(allowedStatuses.value.join(','))}`
  }
  if (filters.value.agent) url += `&agent=${encodeURIComponent(filters.value.agent)}`
  if (filters.value.zone) url += `&zone=${encodeURIComponent(filters.value.zone)}`
  if (startDate.value && endDate.value) {
    url += `&start_date=${encodeURIComponent(startDate.value)}`
    url += `&end_date=${encodeURIComponent(endDate.value)}`
  }
  if (props.confirmation) {
    // Show confirmation section orders
    url += `&belongs_to=confirmation&assigned_only=1`
  } else if (props.delivery) {
    // Show delivery section orders
    url += `&belongs_to=delivery`
  } else {
    // Default "All Orders" view - show only unassigned orders (New Order status)
    url += `&unassigned_only=1`
  }
  const res = await fetch(url)
  const data = await res.json()
  // Backend now handles belongs_to filtering, so no client-side filtering needed
  orders.value = data.orders.data
  
  // Handle pagination - backend now handles all filtering
  if (orders.value.length === 0 && data.orders.current_page > 1) {
    // If we have no orders on current page, go to page 1
    currentPage.value = 1
    await fetchOrders()
    return
  }
  
  // Use backend pagination for all sections
  totalPages.value = data.orders.last_page
  currentPage.value = data.orders.current_page
  
  sellers.value = data.sellers
  zones.value = data.zones

  // Update global delivered-today count coming from backend
  if (typeof data.delivered_orders_today_count !== 'undefined') {
    deliveredOrdersTodayCount.value = data.delivered_orders_today_count
  }

  // Update status counts (for confirmation section summary)
  if (typeof data.status_counts !== 'undefined') {
    statusCounts.value = data.status_counts
  }

  // Clean up delivery note tracking for orders no longer in the list
  clearDeliveryNoteTracking()
}

// Change page helper
const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  fetchOrders()
}

// Change per page size
const changePerPage = () => {
  currentPage.value = 1 // Reset to first page when changing page size
  fetchOrders()
}

// Go to specific page number
const goToPageNumber = () => {
  const page = parseInt(goToPage.value)
  if (page && page >= 1 && page <= totalPages.value) {
    changePage(page)
    goToPage.value = '' // Clear input after navigation
  }
}

const fetchAvailableAgents = async () => {
  try {
    const response = await fetch('/api/order-assignments/agents')
    if (response.ok) {
      availableAgents.value = await response.json()
    }
  } catch (error) {
    console.error('Failed to fetch agents:', error)
  }
}

const fetchAgents = async () => {
  try {
    // Only fetch agents for non-sellers
    if (!isSeller.value) {
      const response = await fetch('/api/dashboard/agents')
      if (response.ok) {
        const agentsData = await response.json()
        agents.value = agentsData.map(agent => agent.name)
      }
    }
  } catch (error) {
    console.error('Failed to fetch agents:', error)
  }
}

const openAssignmentModal = async () => {
  if (selectedIds.value.size === 0) {
    toastType.value = 'error'
    toastMessage.value = 'Please select at least one order to assign'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  await fetchAvailableAgents()
  showAssignmentModal.value = true
}

const closeAssignmentModal = () => {
  showAssignmentModal.value = false
  assignmentForm.value = { agentId: '', notes: '' }
}

const assignOrders = async () => {
  if (!assignmentForm.value.agentId) {
    toastType.value = 'error'
    toastMessage.value = 'Please select an agent'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  isAssigning.value = true
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch('/api/order-assignments/assign', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify({
        order_ids: Array.from(selectedIds.value),
        agent_id: assignmentForm.value.agentId,
        notes: assignmentForm.value.notes
      })
    })
    
    if (response.ok) {
      const result = await response.json()
      toastType.value = 'success'
      toastMessage.value = result.message
      closeAssignmentModal()
      checked.value = [] // Clear selection
      await fetchOrders() // Refresh orders to show assignments
    } else {
      const error = await response.json()
      toastType.value = 'error'
      toastMessage.value = error.message || 'Failed to assign orders'
    }
  } catch (error) {
    console.error('Failed to assign orders:', error)
    toastType.value = 'error'
    toastMessage.value = 'Failed to assign orders'
  } finally {
    isAssigning.value = false
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

const clearFilters = () => {
  filters.value = { search: '', seller: '', status: '', agent: '', zone: '' }
  currentPage.value = 1 // Reset to first page
  fetchOrders()
}

// Date range filter methods
const applyDateFilter = () => {
  if (startDate.value && endDate.value) {
    activeRange.value = '' // Clear active range when using custom dates
    currentPage.value = 1
    fetchOrders()
  }
}

const resetDateFilter = () => {
  const today = new Date()
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(today.getDate() - 30)
  
  startDate.value = thirtyDaysAgo.toISOString().substr(0, 10)
  endDate.value = today.toISOString().substr(0, 10)
  activeRange.value = 'Last 30 days'
  currentPage.value = 1
  fetchOrders()
}

const setQuickRange = ({ label, days }) => {
  activeRange.value = label
  const end = new Date()
  const start = new Date()
  
  if (days !== null) {
    if (days > 0) {
      start.setDate(end.getDate() - days)
    }
    startDate.value = start.toISOString().substr(0, 10)
    endDate.value = end.toISOString().substr(0, 10)
  } else {
    // Handle special cases for "This Month" and "Last Month"
    if (label === 'This Month') {
      start.setDate(1) // First day of current month
      end.setMonth(end.getMonth() + 1, 0) // Last day of current month
    } else if (label === 'Last Month') {
      start.setMonth(start.getMonth() - 1, 1) // First day of last month
      end.setDate(0) // Last day of last month
    }
    startDate.value = start.toISOString().substr(0, 10)
    endDate.value = end.toISOString().substr(0, 10)
  }
  
  currentPage.value = 1
  fetchOrders()
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleString()
}

const openDetails = (order) => { selectedOrder.value = order; showDetails.value = true }
const closeDetails = () => { showDetails.value = false; selectedOrder.value = null }

const openEdit = async (order) => {
  editOrder.value = order
  showEdit.value = true
  // fetch products for dropdown
  const res = await fetch('/products/list')
  const data = await res.json()
  productsList.value = data.products || []
}

const closeEdit = () => { showEdit.value = false; editOrder.value = null }
const handleUpdated = () => { showEdit.value = false; editOrder.value = null; fetchOrders() }

const toggleSelectAll = (e) => {
  if (e.target.checked) {
    checked.value = orders.value.map(o => o.id)
  } else {
    checked.value = []
  }
}

// Clear delivery note and invoice tracking when orders are deselected
const clearDeliveryNoteTracking = () => {
  // Only keep tracking for orders that are still in the current list
  const currentOrderIds = new Set(orders.value.map(o => o.id))
  const filteredDeliveryTracking = new Set()
  const filteredInvoiceTracking = new Set()
  
  deliveryNotesDownloaded.value.forEach(orderId => {
    if (currentOrderIds.has(orderId)) {
      filteredDeliveryTracking.add(orderId)
    }
  })
  
  invoicesDownloaded.value.forEach(orderId => {
    if (currentOrderIds.has(orderId)) {
      filteredInvoiceTracking.add(orderId)
    }
  })
  
  deliveryNotesDownloaded.value = filteredDeliveryTracking
  invoicesDownloaded.value = filteredInvoiceTracking
}

const downloadDeliveryNote = () => {
  if (!selectedIds.value.size) return
  const idsParam = Array.from(selectedIds.value).join(',')
  
  // Track that delivery notes have been downloaded for these orders
  Array.from(selectedIds.value).forEach(orderId => {
    deliveryNotesDownloaded.value.add(orderId)
  })
  
  window.open(`/orders/delivery-note?ids=${idsParam}`, '_blank')
}

const downloadInvoices = () => {
  if (!selectedIds.value.size) return
  const idsParam = Array.from(selectedIds.value).join(',')
  
  // Track that invoices have been downloaded for these orders
  Array.from(selectedIds.value).forEach(orderId => {
    invoicesDownloaded.value.add(orderId)
  })
  
  window.open(`/orders/invoices?ids=${idsParam}`, '_blank')
}

const generateDeliveryInvoice = () => {
  // Check if there are delivered orders today
  if (!hasDeliveredOrdersToday.value) {
    toastType.value = 'error'
    toastMessage.value = 'No delivered orders found for today'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  // For delivery section, automatically generate invoice for all delivered orders of today
  // No need to select orders manually - the backend will handle filtering by date and status
  window.open(`/orders/delivery-invoice`, '_blank')
}

const markAsShipped = async () => {
  if (!canMarkAsShipped.value) {
    toastType.value = 'error'
    toastMessage.value = 'Please download both delivery notes and invoices first'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    // Update each selected order to "Shipped" status
    for (const orderId of selectedIds.value) {
      const response = await fetch(`/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: { 
          'Content-Type': 'application/json', 
          'Accept': 'application/json', 
          'X-CSRF-TOKEN': csrf 
        },
        body: JSON.stringify({ status: 'Shipped' })
      })
      
      if (!response.ok) {
        throw new Error(`Failed to update order ${orderId}`)
      }
    }
    
    toastType.value = 'success'
    toastMessage.value = `${selectedIds.value.size} order(s) marked as shipped successfully!`
    
    // Clear selection and refresh orders
    checked.value = []
    await fetchOrders()
    
  } catch (error) {
    console.error('Error marking orders as shipped:', error)
    toastType.value = 'error'
    toastMessage.value = 'Failed to mark orders as shipped'
  } finally {
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

const getStatusClass = (status) => {
  switch (status) {
    case 'Confirmed': return 'bg-green-100 text-green-700'
    case 'Delivered': return 'bg-blue-100 text-blue-700'
    case 'Cancelled': return 'bg-red-100 text-red-700'
    case 'Postponed': return 'bg-yellow-100 text-yellow-700'
    case 'Shipped': return 'bg-purple-100 text-purple-700'
    case 'Processing': return 'bg-blue-100 text-blue-700'
    case 'New Order': return 'bg-gray-100 text-gray-700'
    default: return 'bg-gray-100 text-gray-700'
  }
}

const openStatusModal = (order) => {
  statusTargetOrder.value = order
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
  statusTargetOrder.value = null
}

const showWarehouseSelectionModal = async (order) => {
  warehouseTargetOrder.value = order
  selectedWarehouseId.value = ''
  
  // Fetch warehouses
  try {
    const response = await fetch('/warehouses')
    if (response.ok) {
      const data = await response.json()
      warehouses.value = data.data || []
    }
  } catch (error) {
    console.error('Error fetching warehouses:', error)
  }
  
  showWarehouseModal.value = true
}

const closeWarehouseModal = () => {
  showWarehouseModal.value = false
  warehouseTargetOrder.value = null
  selectedWarehouseId.value = ''
}

// Confirmation modal methods
const openConfirmationModal = (order) => {
  confirmationTargetOrder.value = order
  confirmationForm.value = {
    date: '',
    comment: ''
  }
  showConfirmationModal.value = true
}

const closeConfirmationModal = () => {
  showConfirmationModal.value = false
  confirmationTargetOrder.value = null
  confirmationForm.value = {
    date: '',
    comment: ''
  }
}

const submitConfirmation = async () => {
  if (!confirmationTargetOrder.value || !confirmationForm.value.date) return
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/orders/${confirmationTargetOrder.value.id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ 
        status: 'Confirmed on Date',
        confirmed_date: confirmationForm.value.date,
        confirmation_comment: confirmationForm.value.comment
      })
    })
    
    if (!res.ok) throw new Error(await res.text())
    const data = await res.json()
    
    toastType.value = 'success'
    toastMessage.value = data.message || 'Order confirmed on date successfully'
    
    // Refresh orders to show the updated list
    await fetchOrders()
  } catch (err) {
    toastType.value = 'error'
    toastMessage.value = 'Failed to confirm order on date'
    console.error(err)
  } finally {
    closeConfirmationModal()
    closeStatusModal() // Also close the status modal
    // auto clear toast after 3s
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

// Postponed modal methods
const openPostponedModal = (order) => {
  postponedTargetOrder.value = order
  postponedForm.value = {
    date: '',
    comment: ''
  }
  showPostponedModal.value = true
}

const closePostponedModal = () => {
  showPostponedModal.value = false
  postponedTargetOrder.value = null
  postponedForm.value = {
    date: '',
    comment: ''
  }
}

const submitPostponed = async () => {
  if (!postponedTargetOrder.value || !postponedForm.value.date) return
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/orders/${postponedTargetOrder.value.id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ 
        status: 'Postponed',
        postponed_date: postponedForm.value.date,
        postponed_comment: postponedForm.value.comment
      })
    })
    
    if (!res.ok) throw new Error(await res.text())
    const data = await res.json()
    
    toastType.value = 'success'
    toastMessage.value = data.message || 'Order postponed successfully'
    
    // Refresh orders to show the updated list
    await fetchOrders()
  } catch (err) {
    toastType.value = 'error'
    toastMessage.value = 'Failed to postpone order'
    console.error(err)
  } finally {
    closePostponedModal()
    closeStatusModal() // Also close the status modal
    // auto clear toast after 3s
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

const confirmOrderWithWarehouse = async () => {
  if (!warehouseTargetOrder.value || !selectedWarehouseId.value) return
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/orders/${warehouseTargetOrder.value.id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ 
        status: 'Confirmed',
        warehouse_id: selectedWarehouseId.value
      })
    })
    
    if (!res.ok) {
      console.log('Response status:', res.status)
      console.log('Response headers:', res.headers)
      
      let errorMessage = 'Failed to confirm order'
      try {
        const errorData = await res.json()
        console.log('Error data:', errorData)
        errorMessage = errorData.message || errorMessage
      } catch (e) {
        console.log('JSON parsing failed:', e)
        // If JSON parsing fails, try to get text
        const errorText = await res.text()
        console.log('Error text:', errorText)
        errorMessage = errorText || errorMessage
      }
      throw new Error(errorMessage)
    }
    
    const data = await res.json()
    
    toastType.value = 'success'
    toastMessage.value = data.message || 'Order confirmed successfully'
    
    // Refresh orders to show the updated list
    await fetchOrders()
  } catch (err) {
    toastType.value = 'error'
    toastMessage.value = err.message || 'Failed to confirm order'
    console.error(err)
  } finally {
    closeWarehouseModal()
    closeStatusModal() // Also close the status modal
    // auto clear toast after 3s
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

// (Removed fetchStatuses; availableStatuses derived from allowedStatuses)

const updateOrderStatus = async (statusName) => {
  if (!statusTargetOrder.value) return
  
  // If status is "Confirmed", show warehouse selection first
  if (statusName === 'Confirmed') {
    showWarehouseSelectionModal(statusTargetOrder.value)
    return
  }
  
  // If status is "Confirmed on Date" in confirmation section, show confirmation modal
  if (statusName === 'Confirmed on Date' && props.confirmation) {
    openConfirmationModal(statusTargetOrder.value)
    return
  }
  
  // If status is "Postponed", show postponed modal
  if (statusName === 'Postponed') {
    openPostponedModal(statusTargetOrder.value)
    return
  }
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const res = await fetch(`/orders/${statusTargetOrder.value.id}/status`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({ status: statusName })
    })
    if (!res.ok) throw new Error(await res.text())
    const data = await res.json()
    
    toastType.value = 'success'
    toastMessage.value = data.message || 'Status updated successfully'
    
    // Refresh orders to show the updated list (orders may move between sections)
    await fetchOrders()
  } catch (err) {
    toastType.value = 'error'
    toastMessage.value = 'Failed to update status'
    console.error(err)
  } finally {
    closeStatusModal()
    // auto clear toast after 3s
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

const confirmationStatusBlocks = computed(() => {
  return statusConfig.confirmation.map(name => ({ name, count: statusCounts.value[name] || 0 }))
})

const deliveryStatusBlocks = computed(() => {
  return statusConfig.delivery.map(name => ({ name, count: statusCounts.value[name] || 0 }))
})


// Function to get status color classes
const getStatusColor = (statusName) => {
  const colors = {
    'New Order': 'bg-blue-100 text-blue-800 border-blue-200',
    'Confirmed': 'bg-green-100 text-green-800 border-green-200',
    'Confirmed on Date': 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'Unreachable': 'bg-red-100 text-red-800 border-red-200',
    'Postponed': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'Cancelled': 'bg-gray-100 text-gray-800 border-gray-200',
    'Blacklisted': 'bg-red-200 text-red-900 border-red-300',
    'Out of Stock': 'bg-orange-100 text-orange-800 border-orange-200',
    'Processing': 'bg-purple-100 text-purple-800 border-purple-200',
    'Shipped': 'bg-indigo-100 text-indigo-800 border-indigo-200',
    'Delivered': 'bg-green-200 text-green-900 border-green-300',
    'Expired': 'bg-red-300 text-red-900 border-red-400'
  }
  return colors[statusName] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const applyTodayWorkFilter = () => {
  // Set date range to Today
  const today = new Date().toISOString().substr(0, 10)
  startDate.value = today
  endDate.value = today
  activeRange.value = 'Today'
  
  // Set specific statuses based on the section
  if (props.confirmation) {
    filters.value.status = 'New Order,Confirmed on Date,Postponed'
  } else if (props.delivery) {
    filters.value.status = 'Postponed'
  }
  
  // Reset to first page and fetch orders
  currentPage.value = 1
  fetchOrders()
}

onMounted(() => { 
  // Initialize date range with last 30 days to show more data by default
  const today = new Date()
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(today.getDate() - 30)
  
  startDate.value = thirtyDaysAgo.toISOString().substr(0, 10)
  endDate.value = today.toISOString().substr(0, 10)
  activeRange.value = 'Last 30 days'
  
  // Check if there's a date filter from navigation
  const storedDateFilter = localStorage.getItem('orderListDateFilter')
  if (storedDateFilter) {
    // Apply the stored date filter to the new date range system
    if (storedDateFilter === 'Today') {
      activeRange.value = 'Today'
    }
    // Clear the stored filter after using it
    localStorage.removeItem('orderListDateFilter')
  }
  
  // Check if there's a status filter from navigation
  const storedStatusFilter = localStorage.getItem('orderListStatusFilter')
  if (storedStatusFilter) {
    filters.value.status = storedStatusFilter
    // Clear the stored filter after using it
    localStorage.removeItem('orderListStatusFilter')
  }
  
  fetchOrders()
  fetchAgents()
})

// Watch for current page changes to clear goToPage input
watch(currentPage, () => {
  goToPage.value = ''
})

// Product modal functions
const showProductDetails = (order) => {
  const product = order?.product || {}
  const merged = { ...product }
  if (!merged.product_link && order?.stock && order.stock.product_link) {
    merged.product_link = order.stock.product_link
  }
  if (!merged.description && order?.stock && order.stock.description) {
    merged.description = order.stock.description
  }
  selectedProduct.value = merged
  showProductModal.value = true
}

const closeProductModal = () => {
  showProductModal.value = false
  selectedProduct.value = null
}


const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.nextElementSibling.style.display = 'flex'
}

// Bulk status change function
const applyBulkStatusChange = async () => {
  if (!bulkStatusChange.value || selectedIds.value.size === 0) {
    toastType.value = 'error'
    toastMessage.value = 'Please select a status and at least one order'
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  // Special handling for statuses that require additional data
  const selectedStatus = bulkStatusChange.value
  
  // For now, we'll handle statuses that don't require modals
  // Statuses like "Confirmed", "Confirmed on Date", and "Postponed" need special handling
  if (selectedStatus === 'Confirmed' || selectedStatus === 'Confirmed on Date' || selectedStatus === 'Postponed') {
    toastType.value = 'error'
    toastMessage.value = `Bulk update not available for "${selectedStatus}". Please update orders individually.`
    setTimeout(() => { toastMessage.value = '' }, 3000)
    return
  }
  
  isBulkUpdating.value = true
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    let successCount = 0
    let failCount = 0
    
    // Update each selected order
    for (const orderId of selectedIds.value) {
      try {
        const response = await fetch(`/orders/${orderId}/status`, {
          method: 'PATCH',
          headers: { 
            'Content-Type': 'application/json', 
            'Accept': 'application/json', 
            'X-CSRF-TOKEN': csrf 
          },
          body: JSON.stringify({ status: selectedStatus })
        })
        
        if (response.ok) {
          successCount++
        } else {
          failCount++
        }
      } catch (error) {
        failCount++
        console.error(`Failed to update order ${orderId}:`, error)
      }
    }
    
    // Show result message
    if (successCount > 0) {
      toastType.value = 'success'
      toastMessage.value = `Successfully updated ${successCount} order(s) to "${selectedStatus}"${failCount > 0 ? `. ${failCount} failed.` : ''}`
    } else {
      toastType.value = 'error'
      toastMessage.value = `Failed to update orders. Please try again.`
    }
    
    // Clear selection and reset
    checked.value = []
    bulkStatusChange.value = ''
    
    // Refresh orders to show the updated list
    await fetchOrders()
    
  } catch (error) {
    console.error('Error during bulk status update:', error)
    toastType.value = 'error'
    toastMessage.value = 'An error occurred while updating orders'
  } finally {
    isBulkUpdating.value = false
    setTimeout(() => { toastMessage.value = '' }, 3000)
  }
}

// Clear selection function
const clearSelection = () => {
  checked.value = []
  bulkStatusChange.value = ''
}

</script>

<style scoped>
.toast {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1000;
}
</style> 