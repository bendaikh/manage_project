<template>
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold capitalize">{{ roleLabel }} List</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">ID</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Name</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Username</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Email</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Status</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Roles</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-b" :class="{ 'opacity-50': !u.is_active }">
            <td class="px-3 py-2 font-mono text-xs">{{ u.id }}</td>
            <td class="px-3 py-2">{{ u.name }}</td>
            <td class="px-3 py-2">{{ u.username }}</td>
            <td class="px-3 py-2">{{ u.email }}</td>
            <td class="px-3 py-2">
              <span :class="u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" 
                    class="px-2 py-1 text-xs rounded font-medium">
                {{ u.is_active ? 'Active' : 'Disabled' }}
              </span>
            </td>
            <td class="px-3 py-2">
              <span v-for="r in u.roles" :key="r" class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 mr-1 capitalize">{{ r }}</span>
            </td>
            <td class="px-3 py-2">
              <div class="flex items-center space-x-2">
                <!-- Edit Button -->
                <button @click="editUser(u)" 
                        class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded transition-colors"
                        title="Edit User">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                
                <!-- Toggle Status Button -->
                <button @click="toggleUserStatus(u)" 
                        :class="u.is_active ? 'text-red-600 hover:text-red-800 hover:bg-red-100' : 'text-green-600 hover:text-green-800 hover:bg-green-100'"
                        class="p-1 rounded transition-colors"
                        :title="u.is_active ? 'Disable User' : 'Enable User'">
                  <svg v-if="u.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit User</h3>
        <form @submit.prevent="updateUser">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input v-model="editingUser.name" type="text" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input v-model="editingUser.username" type="text" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input v-model="editingUser.email" type="email" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select v-model="editingUser.role" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="agent">Agent</option>
              <option value="seller">Seller</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3">
            <button type="button" @click="closeEditModal" 
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="isUpdating"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ isUpdating ? 'Updating...' : 'Update' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, computed } from 'vue'

const props = defineProps({ role: String })
const users = ref([])
const showEditModal = ref(false)
const editingUser = ref({})
const isUpdating = ref(false)

const roleLabel = computed(() => {
  if (!props.role) return 'User'
  return props.role.charAt(0).toUpperCase() + props.role.slice(1)
})

const fetchUsers = async () => {
  const res = await fetch(`/users?role=${encodeURIComponent(props.role)}`, {
    headers: { 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
  if (!res.ok) return
  const data = await res.json()
  users.value = data.users || []
}

const editUser = (user) => {
  editingUser.value = {
    id: user.id,
    name: user.name,
    username: user.username,
    email: user.email,
    role: user.roles[0] || 'seller' // Get the first role
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingUser.value = {}
}

const updateUser = async () => {
  isUpdating.value = true
  try {
    const res = await fetch(`/users/${editingUser.value.id}/update`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      credentials: 'same-origin',
      body: JSON.stringify(editingUser.value)
    })
    
    if (res.ok) {
      const data = await res.json()
      // Update the user in the local array
      const userIndex = users.value.findIndex(u => u.id === editingUser.value.id)
      if (userIndex !== -1) {
        users.value[userIndex] = {
          ...users.value[userIndex],
          name: editingUser.value.name,
          username: editingUser.value.username,
          email: editingUser.value.email,
          roles: [editingUser.value.role]
        }
      }
      closeEditModal()
      alert('User updated successfully!')
    } else {
      const error = await res.json()
      alert('Error updating user: ' + (error.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error updating user:', error)
    alert('Error updating user. Please try again.')
  } finally {
    isUpdating.value = false
  }
}

const toggleUserStatus = async (user) => {
  if (confirm(`Are you sure you want to ${user.is_active ? 'disable' : 'enable'} this user?`)) {
    try {
      const res = await fetch(`/users/${user.id}/toggle-status`, {
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
      })
      
      if (res.ok) {
        const data = await res.json()
        // Update the user status in the local array
        const userIndex = users.value.findIndex(u => u.id === user.id)
        if (userIndex !== -1) {
          users.value[userIndex].is_active = data.is_active
        }
        alert(data.message)
      } else {
        const error = await res.json()
        alert('Error: ' + (error.error || 'Unknown error'))
      }
    } catch (error) {
      console.error('Error toggling user status:', error)
      alert('Error updating user status. Please try again.')
    }
  }
}

onMounted(fetchUsers)
</script> 