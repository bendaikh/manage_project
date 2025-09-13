<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Edit Warehouse</h2>
        <button @click="$emit('back')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
          Back to List
        </button>
      </div>
      
      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Warehouse Name *</label>
            <input v-model="form.name" type="text" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
            <input v-model="form.location" type="text" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errors.location" class="mt-1 text-sm text-red-600">{{ errors.location[0] }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
            <input v-model="form.contact_person" type="text" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errors.contact_person" class="mt-1 text-sm text-red-600">{{ errors.contact_person[0] }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
            <input v-model="form.phone" type="tel" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input v-model="form.email" type="email" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="form.status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</p>
          </div>
          
          <div class="md:col-span-2">
            <div class="flex items-center">
              <input v-model="form.is_principal" type="checkbox" id="is_principal" 
                     class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <label for="is_principal" class="ml-2 block text-sm font-medium text-gray-700">
                Set as Principal Warehouse
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Only one warehouse can be set as principal. If another warehouse is already principal, you cannot set this one as principal.
            </p>
            <p v-if="errors.is_principal" class="mt-1 text-sm text-red-600">
              {{ errors.is_principal[0] }}
            </p>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea v-model="form.description" rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
        </div>
        
        <div class="flex justify-end space-x-4">
          <button type="button" @click="$emit('back')" 
                  class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
            Cancel
          </button>
          <button type="submit" :disabled="loading"
                  class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ loading ? 'Updating...' : 'Update Warehouse' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  warehouseId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['back', 'warehouse-updated'])

const form = ref({
  name: '',
  location: '',
  contact_person: '',
  phone: '',
  email: '',
  status: 'active',
  is_principal: false,
  description: ''
})

const errors = ref({})
const loading = ref(false)

const fetchWarehouse = async () => {
  try {
    const response = await fetch(`/warehouses/${props.warehouseId}`)
    if (response.ok) {
      const warehouse = await response.json()
      form.value = {
        name: warehouse.name || '',
        location: warehouse.location || '',
        contact_person: warehouse.contact_person || '',
        phone: warehouse.phone || '',
        email: warehouse.email || '',
        status: warehouse.status || 'active',
        is_principal: warehouse.is_principal || false,
        description: warehouse.description || ''
      }
    } else {
      alert('Error fetching warehouse data')
    }
  } catch (error) {
    console.error('Error fetching warehouse:', error)
    alert('Error fetching warehouse data')
  }
}

const submitForm = async () => {
  try {
    // Clear previous errors
    errors.value = {}
    loading.value = true
    
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch(`/warehouses/${props.warehouseId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      alert('Warehouse updated successfully!')
      emit('warehouse-updated')
      emit('back')
    } else {
      const errorData = await response.json()
      if (errorData.errors) {
        errors.value = errorData.errors
      } else {
        alert('Error: ' + (errorData.message || 'Failed to update warehouse'))
      }
    }
  } catch (error) {
    console.error('Error updating warehouse:', error)
    alert('Error updating warehouse')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchWarehouse()
})
</script>
