<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Access Rights Management</h2>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message" class="mb-4 p-4 rounded-md" :class="messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
      {{ message }}
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Role Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Description
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Permissions
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Users Count
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ role.name }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  {{ role.description || 'No description' }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="permission in role.permissions" :key="permission.id" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ permission.name }}
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ role.users_count || 0 }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="editRole(role)" 
                          class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md text-xs">
                    Modify
                  </button>
                  <button @click="deleteRole(role)" 
                          :disabled="role.name === 'superadmin'"
                          class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit Role Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-[600px] max-w-full shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Edit Role Permissions
          </h3>
          <form @submit.prevent="updateRole()">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Role Name
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter role name"
                required
                :disabled="editingRole?.name === 'superadmin'"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="2"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter role description"
              ></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Permissions
              </label>
              <div class="overflow-x-auto border border-gray-200 rounded-md">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permission</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                      <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Allow</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="permission in permissions" :key="permission.id">
                      <td class="px-4 py-2 text-sm">{{ permission.name }}</td>
                      <td class="px-4 py-2 text-sm text-gray-500">{{ permission.description || '-' }}</td>
                      <td class="px-4 py-2 text-center">
                        <input
                          type="checkbox"
                          :id="'perm-' + permission.id"
                          v-model="form.permissions"
                          :value="permission.id"
                          :disabled="editingRole?.name === 'superadmin' && permission.name === 'manage_roles'"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isLoading"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ isLoading ? 'Saving...' : 'Update' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const roles = ref([])
const permissions = ref([])
const showEditModal = ref(false)
const editingRole = ref(null)
const isLoading = ref(false)
const message = ref('')
const messageType = ref('success')

const form = ref({
  name: '',
  description: '',
  permissions: []
})

const fetchRoles = async () => {
  try {
    const response = await fetch('/roles', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (response.ok) {
      const data = await response.json()
      roles.value = data
    }
  } catch (error) {
    console.error('Failed to fetch roles:', error)
    showMessage('Failed to load roles', 'error')
  }
}

const fetchPermissions = async () => {
  try {
    const response = await fetch('/permissions', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    if (response.ok) {
      const data = await response.json();
      permissions.value = data;
    }
  } catch (error) {
    console.error('Failed to fetch permissions:', error);
  }
}

const editRole = (role) => {
  editingRole.value = role;
  form.value = {
    name: role.name,
    description: role.description || '',
    permissions: role.permissions.map(p => p.id)
  };
  fetchPermissions(); // Fetch permissions before showing modal
  showEditModal.value = true;
}

const deleteRole = async (role) => {
  if (role.name === 'superadmin') {
    showMessage('Cannot delete the superadmin role', 'error')
    return
  }
  
  if (!confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
    return
  }
  
  try {
    const response = await fetch(`/roles/${role.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      showMessage('Role deleted successfully', 'success')
      await fetchRoles()
    } else {
      const error = await response.json()
      showMessage(error.message || 'Failed to delete role', 'error')
    }
  } catch (error) {
    console.error('Failed to delete role:', error)
    showMessage('Failed to delete role', 'error')
  }
}



const updateRole = async () => {
  isLoading.value = true
  try {
    const response = await fetch(`/roles/${editingRole.value.id}`, {
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form.value)
    })
    
    if (response.ok) {
      showMessage('Role updated successfully', 'success')
      closeModal()
      await fetchRoles()
    } else {
      const error = await response.json()
      showMessage(error.message || 'Failed to update role', 'error')
    }
  } catch (error) {
    console.error('Failed to update role:', error)
    showMessage('Failed to update role', 'error')
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  showEditModal.value = false
  editingRole.value = null
  form.value = {
    name: '',
    description: '',
    permissions: []
  }
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

onMounted(() => {
  fetchRoles()
  fetchPermissions()
})
</script> 