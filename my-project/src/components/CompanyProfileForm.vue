<template>
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Edit Company Profile</h2>

    <form @submit.prevent="updateProfile">
      <div class="mb-4">
        <label class="block text-sm font-semibold mb-1">Company Name</label>
        <input v-model="form.name" type="text" class="w-full border px-3 py-2 rounded" required>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-semibold mb-1">Address</label>
        <textarea v-model="form.address" class="w-full border px-3 py-2 rounded"></textarea>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-semibold mb-1">Phone</label>
        <input v-model="form.phone" type="text" class="w-full border px-3 py-2 rounded">
      </div>

      <div class="mb-4">
        <label class="block text-sm font-semibold mb-1">Email</label>
        <input v-model="form.email" type="email" class="w-full border px-3 py-2 rounded">
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const form = ref({
  name: '',
  address: '',
  phone: '',
  email: '',
})

const loadProfile = async () => {
  const res = await axios.get('/api/company-profile')
  Object.assign(form.value, res.data)
}

const updateProfile = async () => {
  try {
    await axios.put('/api/company-profile', form.value)
    toast.success('Company profile updated!')
  } catch (err) {
    toast.error('Failed to update profile')
  }
}

onMounted(loadProfile)
</script>
