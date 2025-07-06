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
            <th class="px-3 py-2 text-left text-xs font-bold">Roles</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-b">
            <td class="px-3 py-2 font-mono text-xs">{{ u.id }}</td>
            <td class="px-3 py-2">{{ u.name }}</td>
            <td class="px-3 py-2">{{ u.username }}</td>
            <td class="px-3 py-2">{{ u.email }}</td>
            <td class="px-3 py-2">
              <span v-for="r in u.roles" :key="r" class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700 mr-1 capitalize">{{ r }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, computed } from 'vue'

const props = defineProps({ role: String })
const users = ref([])

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

onMounted(fetchUsers)
</script> 