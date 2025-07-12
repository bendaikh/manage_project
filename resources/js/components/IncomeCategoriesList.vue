<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Income Categories</h2>
        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Category
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Incomes Count
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Created
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ category.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ category.incomes_count }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(category.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="openEditModal(category)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteCategory(category)"
                class="text-red-600 hover:text-red-900"
                :disabled="category.incomes_count > 0"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="px-6 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex space-x-2">
          <button
            v-if="pagination.prev_page_url"
            @click="loadPage(pagination.current_page - 1)"
            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Previous
          </button>
          <button
            v-if="pagination.next_page_url"
            @click="loadPage(pagination.current_page + 1)"
            class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Create Income Category</h3>
          <form @submit.prevent="createCategory">
            <div class="mb-4">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input
                v-model="createForm.name"
                type="text"
                id="name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                :disabled="loading"
              >
                {{ loading ? 'Creating...' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Income Category</h3>
          <form @submit.prevent="updateCategory">
            <div class="mb-4">
              <label for="edit-name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input
                v-model="editForm.name"
                type="text"
                id="edit-name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeEditModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                :disabled="loading"
              >
                {{ loading ? 'Updating...' : 'Update' }}
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

const categories = ref([])
const pagination = ref(null)
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const createForm = ref({ name: '' })
const editForm = ref({ id: null, name: '' })

const loadCategories = async (page = 1) => {
  try {
    loading.value = true
    const response = await fetch(`/accounting/incomes/categories?page=${page}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      categories.value = data.data
      pagination.value = {
        current_page: data.current_page,
        from: data.from,
        to: data.to,
        total: data.total,
        prev_page_url: data.prev_page_url,
        next_page_url: data.next_page_url
      }
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  } finally {
    loading.value = false
  }
}

const loadPage = (page) => {
  loadCategories(page)
}

const openCreateModal = () => {
  createForm.value = { name: '' }
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = { name: '' }
}

const openEditModal = (category) => {
  editForm.value = { id: category.id, name: category.name }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = { id: null, name: '' }
}

const createCategory = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch('/accounting/incomes/categories', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(createForm.value)
    })
    
    if (response.ok) {
      closeCreateModal()
      loadCategories()
    } else {
      const error = await response.json()
      alert(error.message || 'Error creating category')
    }
  } catch (error) {
    console.error('Error creating category:', error)
    alert('Error creating category')
  } finally {
    loading.value = false
  }
}

const updateCategory = async () => {
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/incomes/categories/${editForm.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ name: editForm.value.name })
    })
    
    if (response.ok) {
      closeEditModal()
      loadCategories()
    } else {
      const error = await response.json()
      alert(error.message || 'Error updating category')
    }
  } catch (error) {
    console.error('Error updating category:', error)
    alert('Error updating category')
  } finally {
    loading.value = false
  }
}

const deleteCategory = async (category) => {
  if (category.incomes_count > 0) {
    alert('Cannot delete category with existing incomes')
    return
  }
  
  if (!confirm('Are you sure you want to delete this category?')) {
    return
  }
  
  try {
    loading.value = true
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    
    const response = await fetch(`/accounting/incomes/categories/${category.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      loadCategories()
    } else {
      const error = await response.json()
      alert(error.message || 'Error deleting category')
    }
  } catch (error) {
    console.error('Error deleting category:', error)
    alert('Error deleting category')
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  loadCategories()
})
</script> 