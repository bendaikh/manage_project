<template>
  <div class="max-w-4xl mx-auto p-8">
    <h2 class="text-3xl font-bold mb-1">User Registration</h2>
    <p class="mb-6 text-gray-600">Add a new user to the system</p>
    <div class="bg-blue-50 rounded-xl p-6 mb-8">
      <div class="flex items-center mb-2">
        <span class="text-blue-600 mr-2"><svg class="inline h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
        <span class="font-semibold text-lg">User Information</span>
      </div>
      <div class="text-xs text-gray-500 mb-2">Fields marked with <span class="text-red-500">*</span> are required</div>
    </div>
    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block font-semibold mb-1">Username <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
            <input v-model="form.username" type="text" class="w-full border rounded px-10 py-2" placeholder="Enter username" required />
          </div>
        </div>
        <div>
          <label class="block font-semibold mb-1">Email <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0a4 4 0 11-8 0 4 4 0 018 0zm0 0v1a4 4 0 01-8 0v-1" /></svg></span>
            <input v-model="form.email" type="email" class="w-full border rounded px-10 py-2" placeholder="Enter email address" required />
          </div>
        </div>
        <div>
          <label class="block font-semibold mb-1">Password <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.104-.896-2-2-2s-2 .896-2 2m8 0c0-1.104-.896-2-2-2s-2 .896-2 2m-2 0v2a2 2 0 002 2h4a2 2 0 002-2v-2m-6 0h6" /></svg></span>
            <input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="w-full border rounded px-10 py-2 pr-10" placeholder="Enter password" required minlength="8" />
            <button type="button" class="absolute right-3 top-2.5 text-gray-400" @click="showPassword = !showPassword"><svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg><svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></button>
          </div>
          <div class="text-xs text-gray-400 mt-1">Password must be at least 8 characters</div>
        </div>
        <div>
          <label class="block font-semibold mb-1">Confirm Password <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.104-.896-2-2-2s-2 .896-2 2m8 0c0-1.104-.896-2-2-2s-2 .896-2 2m-2 0v2a2 2 0 002 2h4a2 2 0 002-2v-2m-6 0h6" /></svg></span>
            <input :type="showConfirmPassword ? 'text' : 'password'" v-model="form.confirmPassword" class="w-full border rounded px-10 py-2 pr-10" placeholder="Confirm password" required minlength="8" />
            <button type="button" class="absolute right-3 top-2.5 text-gray-400" @click="showConfirmPassword = !showConfirmPassword"><svg v-if="showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg><svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.657.403-3.221 1.125-4.575M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></button>
          </div>
        </div>
        <div class="md:col-span-2">
          <label class="block font-semibold mb-1">Address</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a2 2 0 00-2.828 0l-4.243 4.243a8 8 0 1111.314 0z" /></svg></span>
            <textarea v-model="form.address" class="w-full border rounded px-10 py-2" placeholder="Enter your address (optional)"></textarea>
          </div>
        </div>
        <div>
          <label class="block font-semibold mb-1">Phone Number</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 10a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2zm8-8h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V7a2 2 0 012-2zm0 10h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zm6-6h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2a2 2 0 012-2z" /></svg></span>
            <input v-model="form.phone" type="text" class="w-full border rounded px-10 py-2" placeholder="Enter phone number (optional)" />
          </div>
        </div>
        <div>
          <label class="block font-semibold mb-1">Role <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
            <select v-model="form.role" class="w-full border rounded px-10 py-2" required>
              <option value="">Select role</option>
              <option value="admin">Admin</option>
              <option value="agent">Agent</option>
              <option value="manager">Manager</option>
              <option value="seller">Seller</option>
            </select>
          </div>
        </div>
      </div>
      <div class="flex justify-between mt-8">
        <button type="button" class="px-4 py-2 bg-white border rounded hover:bg-gray-100 flex items-center" @click="$emit('back')">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          Back to Login
        </button>
        <div class="flex gap-2">
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            Register User
          </button>
          <button type="reset" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582M19 20v-5h-.581M4 9a9 9 0 0116 0M4 9v1a9 9 0 0016 0V9" /></svg>
            Reset
          </button>
        </div>
      </div>
      <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>
      <div v-if="success" class="text-green-600 mt-4">User registered successfully!</div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const form = ref({ username: '', email: '', password: '', confirmPassword: '', address: '', phone: '', role: 'admin' })
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const error = ref('')
const success = ref(false)

const submitForm = async () => {
  error.value = ''
  success.value = false
  if (form.value.password !== form.value.confirmPassword) {
    error.value = 'Passwords do not match.'
    return
  }
  if (form.value.password.length < 8) {
    error.value = 'Password must be at least 8 characters.'
    return
  }
  try {
    const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content')
    const response = await fetch('/users', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify(form.value),
      credentials: 'same-origin'
    })
    if (!response.ok) {
      const data = await response.json()
      error.value = data.message || 'Failed to register user.'
      return
    }
    success.value = true
    Object.assign(form.value, { username: '', email: '', password: '', confirmPassword: '', address: '', phone: '', role: 'admin' })
  } catch (e) {
    error.value = 'Failed to register user.'
  }
}
</script> 