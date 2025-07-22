<template>
  <div class="max-w-4xl mx-auto">
    <div class="mb-8">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Application Settings</h3>
      
      <!-- Success/Error Messages -->
      <div v-if="message" class="mb-4 p-4 rounded-md" :class="messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
        {{ message }}
      </div>
    </div>

    <form @submit.prevent="saveSettings" class="space-y-8">
      <!-- Appearance Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-4">Appearance</h4>
        
        <!-- Logo Upload -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Application Logo
            </label>
            <div class="flex items-center space-x-4">
              <!-- Current Logo Preview -->
              <div v-if="settings.app_logo" class="flex-shrink-0">
                <img :src="settings.app_logo" alt="Current Logo" class="h-16 w-auto object-contain border rounded">
              </div>
              
              <!-- Upload Area -->
              <div class="flex-1">
                <div class="flex items-center space-x-4">
                  <input
                    type="file"
                    ref="logoInput"
                    @change="handleLogoSelect"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  />
                  <button
                    v-if="settings.app_logo"
                    @click="deleteLogo"
                    type="button"
                    class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                  >
                    Remove
                  </button>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                  Upload a logo image (PNG, JPG, GIF, SVG). Max size: 2MB. Recommended size: 200x50px.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- General Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-4">General Information</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Application Name
            </label>
            <input
              v-model="form.app_name"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter application name"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Application Description
            </label>
            <input
              v-model="form.app_description"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter application description"
            />
          </div>
        </div>
      </div>

      <!-- Delivery Section -->
      <div class="bg-gray-50 p-6 rounded-lg">
        <h4 class="text-md font-medium text-gray-900 mb-4">Delivery Settings</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Country
            </label>
            <input
              v-model="form.country"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="e.g., France, Morocco"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Delivery Price (FCFA)
            </label>
            <input
              v-model="form.delivery_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="0.00"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Seller Delivery Price (FCFA)
            </label>
            <input
              v-model="form.seller_delivery_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="0.00"
            />
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="isLoading"
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isLoading ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const settings = ref({
  app_logo: null,
  country: '',
  delivery_price: 0,
  seller_delivery_price: 0,
  app_name: '',
  app_description: ''
})

const form = ref({
  app_name: '',
  app_description: '',
  country: '',
  delivery_price: 0,
  seller_delivery_price: 0
})

const logoInput = ref(null)
const selectedLogo = ref(null)
const isLoading = ref(false)
const message = ref('')
const messageType = ref('success')

const fetchSettings = async () => {
  try {
    const response = await fetch('/settings/get')
    if (response.ok) {
      const data = await response.json()
      settings.value = data
      form.value = {
        app_name: data.app_name || '',
        app_description: data.app_description || '',
        country: data.country || '',
        delivery_price: data.delivery_price || 0,
        seller_delivery_price: data.seller_delivery_price || 0
      }
    }
  } catch (error) {
    console.error('Failed to fetch settings:', error)
    showMessage('Failed to load settings', 'error')
  }
}

const handleLogoSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedLogo.value = file
  }
}

const saveSettings = async () => {
  isLoading.value = true
  message.value = ''

  try {
    const formData = new FormData()
    formData.append('app_name', form.value.app_name)
    formData.append('app_description', form.value.app_description)
    formData.append('country', form.value.country)
    formData.append('delivery_price', form.value.delivery_price)
    formData.append('seller_delivery_price', form.value.seller_delivery_price)
    
    if (selectedLogo.value) {
      formData.append('app_logo', selectedLogo.value)
    }

    const response = await fetch('/settings/update', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    const result = await response.json()

    if (result.success) {
      showMessage(result.message, 'success')
      await fetchSettings() // Refresh settings
      selectedLogo.value = null
      if (logoInput.value) {
        logoInput.value.value = ''
      }
    } else {
      showMessage(result.message || 'Failed to save settings', 'error')
    }

  } catch (error) {
    console.error('Failed to save settings:', error)
    showMessage('Failed to save settings', 'error')
  } finally {
    isLoading.value = false
  }
}

const deleteLogo = async () => {
  if (!confirm('Are you sure you want to delete the logo?')) {
    return
  }

  try {
    const response = await fetch('/settings/logo', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    const result = await response.json()

    if (result.success) {
      showMessage(result.message, 'success')
      await fetchSettings() // Refresh settings
    } else {
      showMessage(result.message || 'Failed to delete logo', 'error')
    }

  } catch (error) {
    console.error('Failed to delete logo:', error)
    showMessage('Failed to delete logo', 'error')
  }
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  
  // Auto-hide message after 5 seconds
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

onMounted(() => {
  fetchSettings()
})
</script> 