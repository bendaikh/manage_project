<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Add Warehouse</h2>
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
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
            <input v-model="form.location" type="text" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
            <input v-model="form.contact_person" type="text" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
            <input v-model="form.phone" type="tel" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input v-model="form.email" type="email" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="form.status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea v-model="form.description" rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="flex justify-end space-x-4">
          <button type="button" @click="$emit('back')" 
                  class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
            Cancel
          </button>
          <button type="submit" 
                  class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Create Warehouse
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const form = ref({
  name: '',
  location: '',
  contact_person: '',
  phone: '',
  email: '',
  status: 'active',
  description: ''
})

const submitForm = async () => {
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    const response = await fetch('/warehouses', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      alert('Warehouse created successfully!')
      form.value = {
        name: '',
        location: '',
        contact_person: '',
        phone: '',
        email: '',
        status: 'active',
        description: ''
      }
      // Emit back to go to warehouse list
      emit('back')
    } else {
      const error = await response.json()
      alert('Error: ' + (error.message || 'Failed to create warehouse'))
    }
  } catch (error) {
    console.error('Error creating warehouse:', error)
    alert('Error creating warehouse')
  }
}

const emit = defineEmits(['back'])
</script>
