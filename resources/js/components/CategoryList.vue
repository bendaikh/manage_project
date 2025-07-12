<template>
  <div class="max-w-3xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold">Categories</h2>
      <button @click="showCreateModal = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">+ Create Category</button>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">ID</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Category Name</th>
            <th class="px-3 py-2 text-right text-xs font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id" class="border-b">
            <td class="px-3 py-2 font-mono text-xs">{{ cat.id }}</td>
            <td class="px-3 py-2">{{ cat.name }}</td>
            <td class="px-3 py-2 text-right">
              <div class="flex justify-end space-x-2">
                <button @click="editCategory(cat)" class="text-blue-500 hover:text-blue-700" title="Edit Category">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="deleteCategory(cat)" class="text-red-500 hover:text-red-700" title="Delete Category">
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
    
    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
        <h3 class="text-lg font-bold mb-4">Create Category</h3>
        <form @submit.prevent="createCategory">
          <label class="block font-semibold mb-1">Category Name</label>
          <input v-model="newCategory" type="text" class="w-full border rounded px-3 py-2 mb-4" placeholder="Enter category name" required />
          <div class="flex justify-end gap-2">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Create</button>
          </div>
          <div v-if="error" class="text-red-600 mt-2">{{ error }}</div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
        <h3 class="text-lg font-bold mb-4">Edit Category</h3>
        <form @submit.prevent="updateCategory">
          <label class="block font-semibold mb-1">Category Name</label>
          <input v-model="editingCategory.name" type="text" class="w-full border rounded px-3 py-2 mb-4" placeholder="Enter category name" required />
          <div class="flex justify-end gap-2">
            <button type="button" @click="closeEditModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Update</button>
          </div>
          <div v-if="error" class="text-red-600 mt-2">{{ error }}</div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const categories = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const newCategory = ref('')
const editingCategory = ref({ id: null, name: '' })
const error = ref('')

const fetchCategories = async () => {
  const res = await fetch('/categories', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) return
  const data = await res.json()
  categories.value = data.categories || []
}

const createCategory = async () => {
  error.value = ''
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    const response = await fetch('/categories', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ name: newCategory.value }),
      credentials: 'same-origin'
    })
    if (!response.ok) {
      const data = await response.json()
      error.value = data.message || 'Failed to create category.'
      return
    }
    closeCreateModal()
    fetchCategories()
  } catch (e) {
    error.value = 'Failed to create category.'
  }
}

const editCategory = (category) => {
  editingCategory.value = { id: category.id, name: category.name }
  showEditModal.value = true
}

const updateCategory = async () => {
  error.value = ''
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    const response = await fetch(`/categories/${editingCategory.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ name: editingCategory.value.name }),
      credentials: 'same-origin'
    })
    if (!response.ok) {
      const data = await response.json()
      error.value = data.message || 'Failed to update category.'
      return
    }
    closeEditModal()
    fetchCategories()
  } catch (e) {
    error.value = 'Failed to update category.'
  }
}

const deleteCategory = async (category) => {
  if (!confirm(`Are you sure you want to delete "${category.name}"?`)) {
    return
  }
  
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    const response = await fetch(`/categories/${category.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      // Remove the category from the list
      categories.value = categories.value.filter(c => c.id !== category.id)
    } else {
      const data = await response.json()
      alert(data.message || 'Failed to delete category')
    }
  } catch (error) {
    console.error('Error deleting category:', error)
    alert('Failed to delete category')
  }
}

const closeCreateModal = () => {
  showCreateModal.value = false
  newCategory.value = ''
  error.value = ''
}

const closeEditModal = () => {
  showEditModal.value = false
  editingCategory.value = { id: null, name: '' }
  error.value = ''
}

onMounted(fetchCategories)
</script> 