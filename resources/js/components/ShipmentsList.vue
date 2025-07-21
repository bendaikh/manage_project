<template>
  <div class="max-w-7xl mx-auto p-4 lg:p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <h2 class="text-xl lg:text-2xl font-bold">Shipments</h2>
      <button v-if="isSeller || isAdmin" @click="openCreate" class="flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm lg:text-base">
        + Create Shipment
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap gap-2">
      <select v-model="filterValidated" class="border rounded px-3 py-2 text-sm">
        <option value="">All</option>
        <option value="1">Validated</option>
        <option value="0">Not Validated</option>
      </select>
      <button @click="fetchShipments" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Apply</button>
    </div>

    <!-- Shipments Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-bold">Title</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Reference</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Qty</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Photo</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Validated</th>
            <th class="px-3 py-2 text-left text-xs font-bold">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in shipments" :key="s.id" :class="!s.validated ? 'bg-red-50' : ''" class="border-b">
            <td class="px-3 py-2">{{ s.title }}</td>
            <td class="px-3 py-2">{{ s.reference }}</td>
            <td class="px-3 py-2">{{ s.quantity }}</td>
            <td class="px-3 py-2">
              <img v-if="s.photo" :src="storageUrl(s.photo)" class="w-10 h-10 object-cover rounded" />
            </td>
            <td class="px-3 py-2 font-semibold" :class="s.validated ? 'text-green-600' : 'text-red-600'">
              {{ s.validated ? 'Yes' : 'No' }}
            </td>
            <td class="px-3 py-2 flex gap-2">
              <button v-if="canValidate" @click="toggleValidate(s)" class="text-purple-600 hover:text-purple-800 text-xs">{{ s.validated ? 'Revoke' : 'Validate' }}</button>
              <button v-if="canEdit(s)" @click="openEdit(s)" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
              <button v-if="canDelete(s)" @click="deleteShipment(s)" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
            </td>
          </tr>
          <tr v-if="shipments.length === 0">
            <td colspan="6" class="text-center py-6 text-gray-400">No shipments found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination (simple) -->
    <nav v-if="pagination.total_pages > 1" class="flex justify-center mt-4">
      <ul class="inline-flex">
        <li><button class="px-3 py-1 border rounded-l" :disabled="pagination.page===1" @click="changePage(pagination.page-1)">&laquo;</button></li>
        <li v-for="p in pagination.total_pages" :key="p"><button class="px-3 py-1 border-t border-b" :class="p===pagination.page ? 'bg-blue-600 text-white' : ''" @click="changePage(p)">{{ p }}</button></li>
        <li><button class="px-3 py-1 border rounded-r" :disabled="pagination.page===pagination.total_pages" @click="changePage(pagination.page+1)">&raquo;</button></li>
      </ul>
    </nav>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white w-full max-w-lg rounded-lg p-6 overflow-y-auto max-h-[90vh]">
        <h3 class="text-lg font-semibold mb-4">{{ editMode ? 'Edit Shipment' : 'Create Shipment' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input v-model="form.title" required type="text" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Reference</label>
            <input v-model="form.reference" required type="text" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Quantity</label>
            <input v-model.number="form.quantity" required type="number" min="1" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Link</label>
            <input v-model="form.link" required type="url" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Shipment Date</label>
            <input v-model="form.shipment_date" required type="date" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Customs Fees (optional)</label>
            <input v-model="form.customs_fees" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Description (optional)</label>
            <textarea v-model="form.description" class="w-full border rounded px-3 py-2"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Photo (optional)</label>
            <input @change="onPhotoChange" type="file" accept="image/*" />
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">Cancel</button>
            <button type="submit" class="px-6 py-2 bg-violet-700 text-white rounded hover:bg-violet-800 text-sm">{{ editMode ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const shipments = ref([])
const pagination = ref({ page: 1, total_pages: 1 })
const filterValidated = ref('')
const showModal = ref(false)
const editMode = ref(false)
const editingShipmentId = ref(null)
const form = ref({ title: '', reference: '', quantity: 1, description: '', link: '', photo: null, shipment_date: '', customs_fees: '' })

const rawRoles = window.Laravel?.user?.roles || []
const roleNames = rawRoles.map(r => {
  const n = typeof r === 'string' ? r : (r.name || '')
  return n.toLowerCase()
})
const isSeller = roleNames.includes('seller')
const isAdmin = roleNames.includes('admin') || roleNames.includes('superadmin')
const isAgent = roleNames.includes('agent')
const canValidate = isAdmin || isAgent

const canEdit = (s) => isSeller && !s.validated && s.seller_id === window.Laravel?.user?.id
const canDelete = canEdit

const storageUrl = (path) => path ? `/storage/${path}` : ''

const fetchShipments = async (page = 1) => {
  let url = `/shipments?page=${page}`
  if (filterValidated.value !== '') url += `&validated=${filterValidated.value}`
  const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
  if (!res.ok) { shipments.value = []; return }
  const data = await res.json()
  shipments.value = data.data || []
  pagination.value = { page: data.current_page || 1, total_pages: data.last_page || 1 }
}

const changePage = (p) => {
  if (p < 1 || p > pagination.value.total_pages) return
  fetchShipments(p)
}

const openCreate = () => {
  editMode.value = false
  editingShipmentId.value = null
  form.value = { title: '', reference: '', quantity: 1, description: '', link: '', photo: null, shipment_date: '', customs_fees: '' }
  showModal.value = true
}

const openEdit = (s) => {
  editMode.value = true
  editingShipmentId.value = s.id
  form.value = { ...s, photo: null }
  showModal.value = true
}

const closeModal = () => { showModal.value = false }

const onPhotoChange = (e) => { form.value.photo = e.target.files[0] }

const submitForm = async () => {
  const fd = new FormData()
  Object.entries(form.value).forEach(([k, v]) => {
    if (v !== null && v !== undefined) fd.append(k, v)
  })
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  const opts = { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd, credentials: 'same-origin' }
  let url = '/shipments'
  if (editMode.value) {
    fd.append('_method', 'PUT')
    url = `/shipments/${editingShipmentId.value}`
  }
  const res = await fetch(url, opts)
  if (res.ok) {
    closeModal()
    fetchShipments()
  }
}

const deleteShipment = async (s) => {
  if (!confirm('Delete this shipment?')) return
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  await fetch(`/shipments/${s.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, credentials: 'same-origin' })
  fetchShipments()
}

const toggleValidate = async (s) => {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  await fetch(`/shipments/${s.id}/validate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, credentials: 'same-origin' })
  fetchShipments(pagination.value.page)
}

onMounted(() => fetchShipments())
</script> 