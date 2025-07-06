<template>
  <div class="max-w-3xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold">Categories</h2>
      <button @click="showModal = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">+ Create Category</button>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">ID</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Category Name</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.id" class="border-b">
            <td class="px-3 py-2 font-mono text-xs">{{ cat.id }}</td>
            <td class="px-3 py-2">{{ cat.name }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">
        <h3 class="text-lg font-bold mb-4">Create Category</h3>
        <form @submit.prevent="createCategory">
          <label class="block font-semibold mb-1">Category Name</label>
          <input v-model="newCategory" type="text" class="w-full border rounded px-3 py-2 mb-4" placeholder="Enter category name" required />
          <div class="flex justify-end gap-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Create</button>
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
const showModal = ref(false)
const newCategory = ref('')
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
    showModal.value = false
    newCategory.value = ''
    fetchCategories()
  } catch (e) {
    error.value = 'Failed to create category.'
  }
}

onMounted(fetchCategories)
</script> 