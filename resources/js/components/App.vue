<template>
  <div class="min-h-screen bg-white">
    <!-- Top Navigation -->
    <div class="fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50" style="height: 60px;">
      <div class="flex items-center h-full">
        <!-- Mobile Menu Button -->
        <button @click="toggleMobileMenu" class="lg:hidden ml-4 p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        
        <!-- Left section - Logo (responsive) -->
        <div class="navbar-logo sidebar-logo lg:w-64 w-auto flex-1 lg:flex-none" style="height: 60px; padding: 0 8px; margin: 0; display: flex; align-items: center; justify-content: center; background-color: white; border-right: 1px solid #e5e7eb;">
          <div class="w-full text-center text-gray-800 text-lg lg:text-xl font-bold truncate">
            {{ appName }}
          </div>
        </div>
        
        <!-- Right section - Profile (responsive) -->
        <div class="flex-1 flex items-center justify-end pr-4">
          <!-- Profile Icon -->
          <div class="relative">
            <button @click="toggleProfileMenu" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50">
              <div class="h-8 w-8 bg-gray-300 rounded-full flex items-center justify-center">
                <UserIcon class="h-5 w-5 text-gray-600" />
              </div>
            </button>
            
            <!-- Profile Dropdown -->
            <div v-show="isProfileMenuOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
              <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
              <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
              <hr class="my-2">
              <button @click="signOut" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Sign out</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" @click="closeMobileMenu"></div>
    
    <!-- Sidebar -->
    <div :class="['fixed inset-y-0 left-0 bg-blue-900 transition-transform duration-300 ease-in-out z-50', 
                   isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', 
                   'w-64 lg:w-64']" style="padding-top: 60px;">
      <!-- Navigation -->
      <nav class="px-3 py-4 h-full overflow-y-auto">
        <!-- Brand Name -->
        <div class="mb-6 text-center select-none">
          <span class="text-white text-2xl lg:text-3xl font-extrabold tracking-wide">
            {{ appName }}
          </span>
        </div>
 
        <div class="space-y-1">
          <!-- Dashboard -->
          <div>
            <button @click="toggleDashboardMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <HomeIcon class="h-5 w-5 text-white" />
                <span>Dashboard</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isDashboardMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Dashboard Sub-menu -->
            <div v-show="isDashboardMenuOpen" class="mt-1 ml-4 space-y-1">
              <button type="button" @click="handleShowOverview" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ChartBarIcon class="h-4 w-4" />
                <span>Overview</span>
              </button>
              <button type="button" @click="handleShowAnalytics" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ChartPieIcon class="h-4 w-4" />
                <span>Analytics</span>
              </button>
            </div>
          </div>

          <!-- Orders -->
          <div v-if="hasOrdersPermission">
            <button @click="toggleOrdersMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <ShoppingCartIcon class="h-5 w-5 text-white" />
                <span>Orders</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isOrdersMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Orders Sub-menu -->
            <div v-show="isOrdersMenuOpen" class="mt-1 ml-4 space-y-1">
              <button v-if="hasCreateOrdersPermission" type="button" @click="handleShowAddOrder" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <PlusIcon class="h-4 w-4" />
                <span>Create Order</span>
              </button>
              <button v-if="hasOrdersPermission" type="button" @click="handleShowOrderList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ListIcon class="h-4 w-4" />
                <span>All Orders</span>
              </button>
              <button v-if="hasViewConfirmationOrdersPermission" type="button" @click="handleShowConfirmationOrders" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <CheckCircleIcon class="h-4 w-4" />
                <span>Confirmation</span>
              </button>
              <button v-if="hasViewDeliveryOrdersPermission" type="button" @click="handleShowDeliveryOrders" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <TruckIcon class="h-4 w-4" />
                <span>Delivery</span>
              </button>
              <button v-if="hasInvoicesPermission" type="button" @click="handleShowInvoices" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <DocumentTextIcon class="h-4 w-4" />
                <span>Invoices</span>
              </button>
              <button type="button" @click="handleShowSellerInvoices" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <DocumentTextIcon class="h-4 w-4" />
                <span>Sellers Invoices</span>
              </button>
            </div>
          </div>

          <!-- Shipments -->
          <div>
            <button type="button" @click="handleShowShipments" class="w-full flex items-center space-x-3 px-4 py-3 text-left text-white rounded-lg hover:bg-blue-800">
              <TruckIcon class="h-5 w-5 text-white" />
              <span>Shipments</span>
            </button>
          </div>

          <!-- Stock -->
          <div>
            <button type="button" @click="handleShowStock" class="w-full flex items-center space-x-3 px-4 py-3 text-left text-white rounded-lg hover:bg-blue-800">
              <BoxIcon class="h-5 w-5 text-white" />
              <span>Stock</span>
            </button>
          </div>

          <!-- Products -->
          <div v-if="hasProductsPermission">
            <button @click="toggleProductsMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <BoxIcon class="h-5 w-5 text-white" />
                <span>Products</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isProductsMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Products Sub-menu -->
            <div v-show="isProductsMenuOpen" class="mt-1 ml-4 space-y-1">
              <button v-if="hasProductsPermission" type="button" @click="handleShowProductList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ListIcon class="h-4 w-4" />
                <span>All Products</span>
              </button>
              <button v-if="hasCreateProductsPermission" type="button" @click="handleShowAddProduct" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <PlusIcon class="h-4 w-4" />
                <span>Add Product</span>
              </button>
              <button v-if="hasCategoriesPermission" type="button" @click="handleShowCategories" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <TagIcon class="h-4 w-4" />
                <span>Categories</span>
              </button>
            </div>
          </div>

          <!-- Accounting -->
          <div v-if="hasAccountingPermission">
            <button @click="toggleAccountingMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <CalculatorIcon class="h-5 w-5 text-white" />
                <span>Accounting</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isAccountingMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Accounting Sub-menu -->
            <div v-show="isAccountingMenuOpen" class="mt-1 ml-4 space-y-1">
              <!-- Incomes Submenu -->
              <div v-if="hasIncomesPermission">
                <button @click="toggleIncomesMenu" class="w-full flex items-center justify-between px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                  <div class="flex items-center space-x-3">
                    <CurrencyDollarIcon class="h-4 w-4" />
                    <span>Incomes</span>
                  </div>
                  <ChevronDownIcon :class="['h-3 w-3 text-blue-200 transition-transform', isIncomesMenuOpen ? 'rotate-180' : '']" />
                </button>
                
                <!-- Incomes Sub-submenu -->
                <div v-show="isIncomesMenuOpen" class="mt-1 ml-4 space-y-1">
                  <button v-if="hasIncomesPermission" type="button" @click="handleShowIncomes" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-100 rounded-lg hover:bg-blue-800 hover:text-white">
                    <ChartBarIcon class="h-3 w-3" />
                    <span>Overview</span>
                  </button>
                  <button v-if="hasIncomeCategoriesPermission" type="button" @click="handleShowIncomeCategories" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-100 rounded-lg hover:bg-blue-800 hover:text-white">
                    <TagIcon class="h-3 w-3" />
                    <span>Income Categories</span>
                  </button>
                </div>
              </div>

              <!-- Expenses Submenu -->
              <div v-if="hasExpensesPermission">
                <button @click="toggleExpensesMenu" class="w-full flex items-center justify-between px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                  <div class="flex items-center space-x-3">
                    <CreditCardIcon class="h-4 w-4" />
                    <span>Expenses</span>
                  </div>
                  <ChevronDownIcon :class="['h-3 w-3 text-blue-200 transition-transform', isExpensesMenuOpen ? 'rotate-180' : '']" />
                </button>
                
                <!-- Expenses Sub-submenu -->
                <div v-show="isExpensesMenuOpen" class="mt-1 ml-4 space-y-1">
                  <button v-if="hasExpensesPermission" type="button" @click="handleShowExpenses" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-100 rounded-lg hover:bg-blue-800 hover:text-white">
                    <ChartBarIcon class="h-3 w-3" />
                    <span>Overview</span>
                  </button>
                  <button v-if="hasExpenseCategoriesPermission" type="button" @click="handleShowExpenseCategories" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-100 rounded-lg hover:bg-blue-800 hover:text-white">
                    <TagIcon class="h-3 w-3" />
                    <span>Expense Categories</span>
                  </button>
                  <button v-if="hasRefundsPermission" type="button" @click="handleShowRefunds" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-100 rounded-lg hover:bg-blue-800 hover:text-white">
                    <ArrowUturnLeftIcon class="h-3 w-3" />
                    <span>Refunds</span>
                  </button>
                </div>
              </div>

              <!-- Transfers (Regular subitem) -->
              <button v-if="hasTransfersPermission" type="button" @click="handleShowTransfers" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ArrowsRightLeftIcon class="h-4 w-4" />
                <span>Transfers</span>
              </button>

              <!-- Accounts (Regular subitem) -->
              <button v-if="hasAccountsPermission" type="button" @click="handleShowAccounts" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <BanknotesIcon class="h-4 w-4" />
                <span>Accounts</span>
              </button>
            </div>
          </div>

          <!-- Users -->
          <div v-if="hasUsersPermission">
            <button @click="toggleUsersMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <UserIcon class="h-5 w-5 text-white" />
                <span>Users</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isUsersMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Users Sub-menu -->
            <div v-show="isUsersMenuOpen" class="mt-1 ml-4 space-y-1">
              <button v-if="hasCreateUsersPermission" type="button" @click="handleShowAddUser" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UserPlusIcon class="h-4 w-4" />
                <span>Add User</span>
              </button>
              <button v-if="hasUsersPermission" type="button" @click="handleShowAgentsList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Agents</span>
              </button>
              <button v-if="hasUsersPermission" type="button" @click="handleShowManagersList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Managers</span>
              </button>
              <button v-if="hasUsersPermission" type="button" @click="handleShowAdminsList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Admins</span>
              </button>
              <button v-if="hasUsersPermission" type="button" @click="handleShowSellersList" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <UsersIcon class="h-4 w-4" />
                <span>Sellers</span>
              </button>
            </div>
          </div>

          <!-- Clients -->
          <div v-if="hasClientsPermission">
            <a href="/clients" class="flex items-center space-x-3 px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <UsersIcon class="h-5 w-5 text-white" />
              <span>Clients</span>
            </a>
          </div>

          <!-- History -->
          <div v-if="hasHistoryPermission">
            <button type="button" @click="handleShowHistory" class="flex items-center space-x-3 px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <ClockIcon class="h-5 w-5 text-white" />
              <span>History</span>
            </button>
          </div>

          <!-- Settings -->
          <div v-if="hasSettingsPermission">
            <button @click="toggleSettingsMenu" class="w-full flex items-center justify-between px-4 py-3 text-white rounded-lg hover:bg-blue-800">
              <div class="flex items-center space-x-3">
                <CogIcon class="h-5 w-5 text-white" />
                <span>Settings</span>
              </div>
              <ChevronDownIcon :class="['h-4 w-4 text-white transition-transform', isSettingsMenuOpen ? 'rotate-180' : '']" />
            </button>
            
            <!-- Settings Sub-menu -->
            <div v-show="isSettingsMenuOpen" class="mt-1 ml-4 space-y-1">
              <button type="button" @click="handleShowGeneralSettings" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <CogIcon class="h-4 w-4" />
                <span>General Settings</span>
              </button>
              <button type="button" @click="handleShowAccessRights" class="w-full flex items-center space-x-3 px-4 py-2 text-left text-blue-200 rounded-lg hover:bg-blue-800 hover:text-white">
                <ShieldCheckIcon class="h-4 w-4" />
                <span>Access Rights</span>
              </button>
            </div>
          </div>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="lg:ml-64" style="padding-top: 60px;">
      <main class="p-4 lg:p-8">
        <ProductCreate v-if="showAddProduct" @back="handleBackFromProduct" />
        <ProductEdit v-else-if="showEditProduct" :product-id="editingProduct?.id" @back="handleBackFromEditProduct" @product-updated="handleProductUpdated" />
        <OrderCreate v-else-if="showAddOrder" @back="handleBackFromOrder" />
        <ProductList v-else-if="showProductList" @add-product="handleShowAddProduct" @edit-product="handleShowEditProduct" />
        <OrderList v-else-if="showOrderList" @create-order="handleShowAddOrder" />
        <OrderList v-else-if="showConfirmationOrders" confirmation @create-order="handleShowAddOrder" />
        <OrderList v-else-if="showDeliveryOrders" delivery @create-order="handleShowAddOrder" />
        <InvoicesList v-else-if="showInvoices" />
        <SellerInvoicesList v-else-if="showSellerInvoices" />
        <DashboardOverview v-else-if="showOverview" @show-full-history="handleShowHistory" />
        <DashboardAnalytics v-else-if="showAnalytics" />
        <AddUser v-else-if="showAddUser" @back="handleBackFromAddUser" />
        <UserList v-else-if="showAgentsList" role="agent" />
        <UserList v-else-if="showManagersList" role="manager" />
        <UserList v-else-if="showAdminsList" role="admin" />
        <UserList v-else-if="showSellersList" role="seller" />
        <CategoryList v-else-if="showCategories" />
        <IncomeCategoriesList v-else-if="showIncomeCategories" />
        <IncomesList v-else-if="showIncomes" />
        <ExpenseCategoriesList v-else-if="showExpenseCategories" />
        <ExpensesList v-else-if="showExpenses" />
        <RefundsList v-else-if="showRefunds" />
        <TransfersList v-else-if="showTransfers" />
        <AccountsList v-else-if="showAccounts" />
        <SettingsForm v-else-if="showSettings" />
        <AccessRights v-else-if="showAccessRights" />
        <ShipmentsList v-else-if="showShipments" />
        <StockList v-else-if="showStock" />
        <HistoryList v-else-if="showHistory" />
        <slot v-else></slot>
      </main>
    </div>

    <!-- Click outside handler for profile menu -->
    <div v-if="isProfileMenuOpen" class="fixed inset-0 z-40" @click="closeProfileMenu"></div>
    
    <!-- AI Chatbot Component -->
    <AiChatbot />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ProductCreate from './ProductCreate.vue'
import ProductList from './ProductList.vue'
import ProductEdit from './ProductEdit.vue'
import OrderCreate from './OrderCreate.vue'
import OrderList from './OrderList.vue'
import DashboardOverview from './DashboardOverview.vue'
import DashboardAnalytics from './DashboardAnalytics.vue'
import AddUser from './AddUser.vue'
import UserList from './UserList.vue'
import CategoryList from './CategoryList.vue'
import InvoicesList from './InvoicesList.vue'
import SettingsForm from './SettingsForm.vue'
import IncomeCategoriesList from './IncomeCategoriesList.vue'
import IncomesList from './IncomesList.vue'
import AccountsList from './AccountsList.vue'
import ExpenseCategoriesList from './ExpenseCategoriesList.vue'
import ExpensesList from './ExpensesList.vue'
import RefundsList from './RefundsList.vue'
import TransfersList from './TransfersList.vue'
import AccessRights from './AccessRights.vue'
import AiChatbot from './AiChatbot.vue'
import ShipmentsList from './ShipmentsList.vue'
import StockList from './StockList.vue'
import SellerInvoicesList from './SellerInvoicesList.vue'
import HistoryList from './HistoryList.vue'

// Component state
const isDashboardMenuOpen = ref(false)
const isOrdersMenuOpen = ref(false)
const isProductsMenuOpen = ref(false)
const isUsersMenuOpen = ref(false)
const isAccountingMenuOpen = ref(false)
const isIncomesMenuOpen = ref(false)
const isExpensesMenuOpen = ref(false)
const isProfileMenuOpen = ref(false)
const isMobileMenuOpen = ref(false)
const isSettingsMenuOpen = ref(false)

// App settings
const appLogo = ref(null)
const appName = ref('Laravel App')
const appDescription = ref('')

// New state for showing Add Product form or Product List
const showAddProduct = ref(false)
const showProductList = ref(false)
const showEditProduct = ref(false)
const editingProduct = ref(null)
const showAddOrder = ref(false)
const showOrderList = ref(false)
const showOverview = ref(true)
const showAnalytics = ref(false)
const showConfirmationOrders = ref(false)
const showDeliveryOrders = ref(false)
const showAddUser = ref(false)
const showAgentsList = ref(false)
const showManagersList = ref(false)
const showAdminsList = ref(false)
const showSellersList = ref(false)
const showCategories = ref(false)
const showInvoices = ref(false)
const showSettings = ref(false)
const showIncomeCategories = ref(false)
const showIncomes = ref(false)
const showAccounts = ref(false)
const showExpenseCategories = ref(false)
const showExpenses = ref(false)
const showRefunds = ref(false)
const showTransfers = ref(false)
const showGeneralSettings = ref(false)
const showAccessRights = ref(false)
const showShipments = ref(false)
const showStock = ref(false)
const showSellerInvoices = ref(false)
const showHistory = ref(false)

const toggleDashboardMenu = () => {
  isDashboardMenuOpen.value = !isDashboardMenuOpen.value
}

const toggleOrdersMenu = () => {
  isOrdersMenuOpen.value = !isOrdersMenuOpen.value
}

const toggleProductsMenu = () => {
  isProductsMenuOpen.value = !isProductsMenuOpen.value
}

const toggleUsersMenu = () => {
  isUsersMenuOpen.value = !isUsersMenuOpen.value
}

const toggleAccountingMenu = () => {
  isAccountingMenuOpen.value = !isAccountingMenuOpen.value
}

const toggleIncomesMenu = () => {
  isIncomesMenuOpen.value = !isIncomesMenuOpen.value
}

const toggleExpensesMenu = () => {
  isExpensesMenuOpen.value = !isExpensesMenuOpen.value
}

const toggleProfileMenu = () => {
  isProfileMenuOpen.value = !isProfileMenuOpen.value
}

const toggleSettingsMenu = () => {
  isSettingsMenuOpen.value = !isSettingsMenuOpen.value
}

const closeProfileMenu = () => {
  isProfileMenuOpen.value = false
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const signOut = async () => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    // After successful logout, redirect to login page
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout failed', error)
  }
}

const resetViews = () => {
  showAddProduct.value = false
  showProductList.value = false
  showEditProduct.value = false
  editingProduct.value = null
  showAddOrder.value = false
  showOrderList.value = false
  showOverview.value = false
  showAnalytics.value = false
  showConfirmationOrders.value = false
  showDeliveryOrders.value = false
  showAddUser.value = false
  showAgentsList.value = false
  showManagersList.value = false
  showAdminsList.value = false
  showSellersList.value = false
  showCategories.value = false
  showInvoices.value = false
  showSettings.value = false
  showIncomeCategories.value = false
  showIncomes.value = false
  showAccounts.value = false
  showExpenseCategories.value = false
  showExpenses.value = false
  showRefunds.value = false
  showGeneralSettings.value = false
  showAccessRights.value = false
  showShipments.value = false
  showStock.value = false
  showSellerInvoices.value = false
  showTransfers.value = false
  showHistory.value = false
}

const handleShowAddProduct = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showAddProduct.value = true
}

const handleShowProductList = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showProductList.value = true
}

const handleBackFromProduct = () => {
  showAddProduct.value = false
  showProductList.value = true
}

const handleShowEditProduct = (product) => {
  editingProduct.value = product
  showProductList.value = false
  showEditProduct.value = true
}

const handleBackFromEditProduct = () => {
  showEditProduct.value = false
  editingProduct.value = null
  showProductList.value = true
}

const handleProductUpdated = () => {
  // Product was updated, we can show a success message or refresh the list
  // For now, just go back to the product list
  handleBackFromEditProduct()
}

const handleShowAddOrder = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showAddOrder.value = true
}

const handleBackFromOrder = () => {
  showAddOrder.value = false
  showProductList.value = true
}

const handleShowOrderList = (e) => {
  if (e) e.preventDefault()
  showOrderList.value = true
  showAddOrder.value = false
  showAddProduct.value = false
  showProductList.value = false
}

const handleShowConfirmationOrders = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showConfirmationOrders.value = true
}

const handleShowDeliveryOrders = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showDeliveryOrders.value = true
}

const handleShowInvoices = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showInvoices.value = true
}

const handleShowSellerInvoices = () => {
  resetViews()
  showSellerInvoices.value = true
}

const handleShowOverview = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showOverview.value = true
}

const handleShowAnalytics = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showAnalytics.value = true
}

const handleShowAddUser = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showAddUser.value = true
}

const handleBackFromAddUser = () => {
  showAddUser.value = false
  showOverview.value = true
}

const handleShowAgentsList = (e)=>{ if(e) e.preventDefault(); resetViews(); showAgentsList.value=true }
const handleShowManagersList = (e)=>{ if(e) e.preventDefault(); resetViews(); showManagersList.value=true }
const handleShowAdminsList = (e)=>{ if(e) e.preventDefault(); resetViews(); showAdminsList.value=true }
const handleShowSellersList = (e)=>{ if(e) e.preventDefault(); resetViews(); showSellersList.value=true }

const handleShowCategories = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showCategories.value = true
}

const handleShowIncomeCategories = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showIncomeCategories.value = true
}

const handleShowIncomes = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showIncomes.value = true
}

const handleShowAccounts = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showAccounts.value = true
}

const handleShowExpenseCategories = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showExpenseCategories.value = true
}

const handleShowExpenses = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showExpenses.value = true
}

const handleShowRefunds = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showRefunds.value = true
}

const handleShowTransfers = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showTransfers.value = true
}

const handleShowGeneralSettings = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showSettings.value = true
}

const handleShowAccessRights = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showAccessRights.value = true
}

const handleShowShipments = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showShipments.value = true
}

const handleShowStock = (e) => {
  if (e) e.preventDefault()
  resetViews()
  showStock.value = true
}

const handleShowHistory = () => {
  resetViews()
  showHistory.value = true
  closeMobileMenu()
}

// Fetch app settings
const fetchAppSettings = async () => {
  try {
    const response = await fetch('/settings/get')
    if (response.ok) {
      const settings = await response.json()
      appLogo.value = settings.app_logo
      appName.value = settings.app_name || 'Laravel App'
      appDescription.value = settings.app_description || ''
    }
  } catch (error) {
    console.error('Failed to fetch app settings:', error)
  }
}

// Helper function to check permissions
const hasPermission = (permission) => {
  return window.Laravel?.user?.permissions?.includes(permission) || false
}

// Dashboard permissions
const hasDashboardPermission = computed(() => hasPermission('view_dashboard'))
const hasDashboardOverviewPermission = computed(() => hasPermission('view_dashboard_overview'))
const hasDashboardAnalyticsPermission = computed(() => hasPermission('view_dashboard_analytics'))

// User management permissions
const hasUsersPermission = computed(() => hasPermission('view_users'))
const hasCreateUsersPermission = computed(() => hasPermission('create_users'))
const hasEditUsersPermission = computed(() => hasPermission('edit_users'))
const hasDeleteUsersPermission = computed(() => hasPermission('delete_users'))
const hasManageUsersPermission = computed(() => hasPermission('manage_users'))

// Role and permission management
const hasRolesPermission = computed(() => hasPermission('view_roles'))
const hasCreateRolesPermission = computed(() => hasPermission('create_roles'))
const hasEditRolesPermission = computed(() => hasPermission('edit_roles'))
const hasDeleteRolesPermission = computed(() => hasPermission('delete_roles'))
const hasManageRolesPermission = computed(() => hasPermission('manage_roles'))

const hasPermissionsPermission = computed(() => hasPermission('view_permissions'))
const hasCreatePermissionsPermission = computed(() => hasPermission('create_permissions'))
const hasEditPermissionsPermission = computed(() => hasPermission('edit_permissions'))
const hasDeletePermissionsPermission = computed(() => hasPermission('delete_permissions'))
const hasManagePermissionsPermission = computed(() => hasPermission('manage_permissions'))

// Order permissions (granular)
const hasOrdersPermission = computed(() => hasPermission('view_orders'))
const hasCreateOrdersPermission = computed(() => hasPermission('create_orders'))
const hasEditOrdersPermission = computed(() => hasPermission('edit_orders'))
const hasDeleteOrdersPermission = computed(() => hasPermission('delete_orders'))
const hasViewOrderDetailsPermission = computed(() => hasPermission('view_order_details'))
const hasUpdateOrderStatusPermission = computed(() => hasPermission('update_order_status'))
const hasViewConfirmationOrdersPermission = computed(() => hasPermission('view_confirmation_orders'))
const hasConfirmOrdersPermission = computed(() => hasPermission('confirm_orders'))
const hasViewDeliveryOrdersPermission = computed(() => hasPermission('view_delivery_orders'))
const hasProcessDeliveryOrdersPermission = computed(() => hasPermission('process_delivery_orders'))
const hasImportOrdersPermission = computed(() => hasPermission('import_orders'))
const hasExportOrdersPermission = computed(() => hasPermission('export_orders'))
const hasManageOrdersPermission = computed(() => hasPermission('manage_orders'))

// Product permissions (granular)
const hasProductsPermission = computed(() => hasPermission('view_products'))
const hasCreateProductsPermission = computed(() => hasPermission('create_products'))
const hasEditProductsPermission = computed(() => hasPermission('edit_products'))
const hasDeleteProductsPermission = computed(() => hasPermission('delete_products'))
const hasViewProductDetailsPermission = computed(() => hasPermission('view_product_details'))
const hasViewProductCatalogPermission = computed(() => hasPermission('view_product_catalog'))
const hasManageProductsPermission = computed(() => hasPermission('manage_products'))

// Category permissions
const hasCategoriesPermission = computed(() => hasPermission('view_categories'))
const hasCreateCategoriesPermission = computed(() => hasPermission('create_categories'))
const hasEditCategoriesPermission = computed(() => hasPermission('edit_categories'))
const hasDeleteCategoriesPermission = computed(() => hasPermission('delete_categories'))
const hasManageCategoriesPermission = computed(() => hasPermission('manage_categories'))

// Invoice and PDF permissions
const hasInvoicesPermission = computed(() => hasPermission('view_invoices'))
const hasCreateInvoicesPermission = computed(() => hasPermission('create_invoices'))
const hasDownloadInvoicesPermission = computed(() => hasPermission('download_invoices'))
const hasDeliveryNotesPermission = computed(() => hasPermission('view_delivery_notes'))
const hasCreateDeliveryNotesPermission = computed(() => hasPermission('create_delivery_notes'))
const hasDownloadDeliveryNotesPermission = computed(() => hasPermission('download_delivery_notes'))
const hasDeliveryInvoicesPermission = computed(() => hasPermission('view_delivery_invoices'))
const hasCreateDeliveryInvoicesPermission = computed(() => hasPermission('create_delivery_invoices'))
const hasDownloadDeliveryInvoicesPermission = computed(() => hasPermission('download_delivery_invoices'))

// Accounting permissions (granular)
const hasAccountingPermission = computed(() => hasPermission('view_accounting'))
const hasAccountingOverviewPermission = computed(() => hasPermission('view_accounting_overview'))

// Income permissions
const hasIncomesPermission = computed(() => hasPermission('view_incomes'))
const hasCreateIncomesPermission = computed(() => hasPermission('create_incomes'))
const hasEditIncomesPermission = computed(() => hasPermission('edit_incomes'))
const hasDeleteIncomesPermission = computed(() => hasPermission('delete_incomes'))
const hasManageIncomesPermission = computed(() => hasPermission('manage_incomes'))

// Income category permissions
const hasIncomeCategoriesPermission = computed(() => hasPermission('view_income_categories'))
const hasCreateIncomeCategoriesPermission = computed(() => hasPermission('create_income_categories'))
const hasEditIncomeCategoriesPermission = computed(() => hasPermission('edit_income_categories'))
const hasDeleteIncomeCategoriesPermission = computed(() => hasPermission('delete_income_categories'))
const hasManageIncomeCategoriesPermission = computed(() => hasPermission('manage_income_categories'))

// Expense permissions
const hasExpensesPermission = computed(() => hasPermission('view_expenses'))
const hasCreateExpensesPermission = computed(() => hasPermission('create_expenses'))
const hasEditExpensesPermission = computed(() => hasPermission('edit_expenses'))
const hasDeleteExpensesPermission = computed(() => hasPermission('delete_expenses'))
const hasManageExpensesPermission = computed(() => hasPermission('manage_expenses'))

// Expense category permissions
const hasExpenseCategoriesPermission = computed(() => hasPermission('view_expense_categories'))
const hasCreateExpenseCategoriesPermission = computed(() => hasPermission('create_expense_categories'))
const hasEditExpenseCategoriesPermission = computed(() => hasPermission('edit_expense_categories'))
const hasDeleteExpenseCategoriesPermission = computed(() => hasPermission('delete_expense_categories'))
const hasManageExpenseCategoriesPermission = computed(() => hasPermission('manage_expense_categories'))

// Refund permissions
const hasRefundsPermission = computed(() => hasPermission('view_refunds'))
const hasCreateRefundsPermission = computed(() => hasPermission('create_refunds'))
const hasEditRefundsPermission = computed(() => hasPermission('edit_refunds'))
const hasDeleteRefundsPermission = computed(() => hasPermission('delete_refunds'))
const hasManageRefundsPermission = computed(() => hasPermission('manage_refunds'))

// Transfer permissions
const hasTransfersPermission = computed(() => hasPermission('view_transfers'))
const hasCreateTransfersPermission = computed(() => hasPermission('create_transfers'))
const hasEditTransfersPermission = computed(() => hasPermission('edit_transfers'))
const hasDeleteTransfersPermission = computed(() => hasPermission('delete_transfers'))
const hasManageTransfersPermission = computed(() => hasPermission('manage_transfers'))

// User transfer permissions
const hasUserTransfersPermission = computed(() => hasPermission('view_user_transfers'))
const hasCreateUserTransfersPermission = computed(() => hasPermission('create_user_transfers'))
const hasEditUserTransfersPermission = computed(() => hasPermission('edit_user_transfers'))
const hasDeleteUserTransfersPermission = computed(() => hasPermission('delete_user_transfers'))
const hasManageUserTransfersPermission = computed(() => hasPermission('manage_user_transfers'))

// Account permissions
const hasAccountsPermission = computed(() => hasPermission('view_accounts'))
const hasCreateAccountsPermission = computed(() => hasPermission('create_accounts'))
const hasEditAccountsPermission = computed(() => hasPermission('edit_accounts'))
const hasDeleteAccountsPermission = computed(() => hasPermission('delete_accounts'))
const hasManageAccountsPermission = computed(() => hasPermission('manage_accounts'))

// Settings permissions
const hasSettingsPermission = computed(() => hasPermission('view_settings'))
const hasEditSettingsPermission = computed(() => hasPermission('edit_settings'))
const hasManageSettingsPermission = computed(() => hasPermission('manage_settings'))

// Reports permissions
const hasReportsPermission = computed(() => hasPermission('view_reports'))
const hasGenerateReportsPermission = computed(() => hasPermission('generate_reports'))
const hasExportReportsPermission = computed(() => hasPermission('export_reports'))
const hasManageReportsPermission = computed(() => hasPermission('manage_reports'))

// Support permissions
const hasSupportTicketsPermission = computed(() => hasPermission('view_support_tickets'))
const hasCreateSupportTicketsPermission = computed(() => hasPermission('create_support_tickets'))
const hasRespondToTicketsPermission = computed(() => hasPermission('respond_to_tickets'))
const hasManageSupportTicketsPermission = computed(() => hasPermission('manage_support_tickets'))

// Legacy permissions for backward compatibility
const hasClientsPermission = computed(() => false)
const hasHistoryPermission = computed(() => {
  // Show history to superadmins or if user has explicit permission
  const roles = window.Laravel?.user?.roles || []
  const isSuperadmin = roles.includes('superadmin') || roles.some(r => typeof r === 'object' && r.name === 'superadmin')
  return isSuperadmin || hasPermission('view_history')
})

// Fetch settings on component mount
fetchAppSettings()
</script>

<script>
// Icons
const HomeIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  `
}

const BoxIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
    </svg>
  `
}

const ShoppingCartIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  `
}

const UsersIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
  `
}

const ClockIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  `
}

const CogIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  `
}

const ShieldCheckIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
    </svg>
  `
}

const CalculatorIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 15.75V18a2.25 2.25 0 01-2.25 2.25h-3A2.25 2.25 0 018.25 18v-.75m6-6V9a2.25 2.25 0 00-2.25-2.25h-3A2.25 2.25 0 008.25 9v.75m6 6h-3m3 0h-3m-9-3h9M9 9h.008M9 12h.008M9 15h.008M9 18h.008M9 21h.008M12 9h.008M12 12h.008M12 15h.008M12 18h.008M12 21h.008M15 9h.008M15 12h.008M15 15h.008M15 18h.008M15 21h.008M18 9h.008M18 12h.008M18 15h.008M18 18h.008M18 21h.008" />
    </svg>
  `
}

const CurrencyDollarIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.314-.488-1.314-1.314 0-.726.589-1.314 1.314-1.314.725 0 1.314.588 1.314 1.314 0 .826-.589 1.314-1.314 1.314z" />
    </svg>
  `
}

const CreditCardIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
    </svg>
  `
}

const ArrowUturnLeftIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
    </svg>
  `
}

const ArrowsRightLeftIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
    </svg>
  `
}

const BanknotesIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
    </svg>
  `
}

const ChevronDownIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
    </svg>
  `
}

const UserIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
  `
}

const ChartBarIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
  `
}

const ChartPieIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
    </svg>
  `
}

const ListIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
    </svg>
  `
}

const CheckCircleIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4" />
    </svg>
  `
}

const TruckIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17l4 4m-4-4l-4 4m12-3a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  `
}

const PlusIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
    </svg>
  `
}

const TagIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 11h.01M7 15h.01M7 19h.01M11 7h.01M11 11h.01M11 15h.01M11 19h.01M15 7h.01M15 11h.01M15 15h.01M15 19h.01" />
    </svg>
  `
}

const UserPlusIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
    </svg>
  `
}

const SellerIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
  `
}

export default {
  components: {
    HomeIcon,
    BoxIcon,
    ShoppingCartIcon,
    UsersIcon,
    ClockIcon,
    CogIcon,
    ChevronDownIcon,
    UserIcon,
    ChartBarIcon,
    ChartPieIcon,
    ListIcon,
    CheckCircleIcon,
    TruckIcon,
    PlusIcon,
    TagIcon,
    UserPlusIcon,
    SellerIcon,
    CalculatorIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
    ArrowUturnLeftIcon,
    ArrowsRightLeftIcon,
    BanknotesIcon,
    ShieldCheckIcon
  }
}
</script>

<style>
.router-link-active {
  @apply bg-gray-50 text-gray-900;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 