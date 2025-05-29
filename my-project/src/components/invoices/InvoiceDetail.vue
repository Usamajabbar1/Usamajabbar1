<template>
  <div class="invoice-detail max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <!-- Loading and Error -->
    <div v-if="loading" class="text-center py-10 text-gray-500">Loading invoice details...</div>
    <div v-if="error" class="text-center py-10 text-red-600">{{ error }}</div>

    <template v-if="!loading && !error">
      <!-- Company Info -->
      <div class="company-info mb-6 border-b pb-4">
        <h1 class="company-name text-3xl font-bold">{{ company.name || 'Your Company Name' }}</h1>
        <p class="text-gray-700">{{ company.address || 'No address provided' }}</p>
        <p class="text-gray-700">
          Phone: {{ company.phone || '-' }} | Email: {{ company.email || '-' }}
        </p>
      </div>

      <!-- Invoice & Customer Info -->
      <div class="info-grid flex justify-between mb-8">
        <div class="info-block w-1/2 pr-4">
          <p><strong>Invoice Number:</strong> {{ invoice.invoice_number || 'N/A' }}</p>
          <p><strong>Invoice Date:</strong> {{ invoice.invoice_date || 'N/A' }}</p>
          <p><strong>Due Date:</strong> {{ invoice.due_date || 'N/A' }}</p>
          <p><strong>Status:</strong>
            <span :class="statusClass" class="capitalize">{{ invoice.status || 'unpaid' }}</span>
          </p>
        </div>
        <div class="info-block w-1/2 pl-4">
          <p><strong>Customer Name:</strong> {{ invoice.customer?.name || 'N/A' }}</p>
          <p><strong>Email:</strong> {{ invoice.customer?.email || 'N/A' }}</p>
          <p><strong>Phone:</strong> {{ invoice.customer?.phone || 'N/A' }}</p>
          <p><strong>Address:</strong> {{ invoice.customer?.address || 'No address provided' }}</p>
        </div>
      </div>

      <!-- Notes -->
      <div class="notes mb-8">
        <p><strong>Notes:</strong></p>
        <p class="notes-content bg-gray-100 p-3 rounded">
          {{ invoice.notes || 'No notes provided.' }}
        </p>
      </div>

      <!-- Items Table -->
      <h3 class="subtitle text-xl font-semibold mb-3">Items</h3>
      <table class="items-table w-full border border-gray-300 mb-6">
        <thead class="bg-gray-100">
          <tr>
            <th class="border border-gray-300 p-2 text-left">Description</th>
            <th class="border border-gray-300 p-2 text-right">Quantity</th>
            <th class="border border-gray-300 p-2 text-right">Unit Price</th>
            <th class="border border-gray-300 p-2 text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoice.items" :key="index" class="hover:bg-gray-50">
            <td class="border border-gray-300 p-2">{{ item.description }}</td>
            <td class="border border-gray-300 p-2 text-right">{{ item.quantity }}</td>
            <td class="border border-gray-300 p-2 text-right">{{ formatCurrency(item.unit_price) }}</td>
            <td class="border border-gray-300 p-2 text-right">{{ formatCurrency(item.quantity * item.unit_price) }}</td>
          </tr>
          <tr v-if="invoice.items.length === 0">
            <td colspan="4" class="p-4 text-center text-gray-500 italic">No items added</td>
          </tr>
        </tbody>
      </table>

      <!-- Total -->
      <div class="total text-right text-2xl font-bold mb-6">
        Total: {{ formatCurrency(totalAmount) }}
      </div>

      <!-- Thank You Footer -->
      <div class="text-center text-gray-600 italic mb-6">
        Thank you for doing business with us!
      </div>

      <!-- Back Button -->
      <div class="back-button text-center print:hidden">
        <button
          class="btn-secondary bg-gray-600 text-white px-5 py-2 rounded hover:bg-gray-700 transition"
          @click="goBack"
        >
          Back to Invoices
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/axios'

const route = useRoute()
const router = useRouter()

const company = ref({})
const invoice = ref({
  invoice_number: '',
  invoice_date: '',
  due_date: '',
  status: '',
  notes: '',
  customer: {},
  items: []
})

const loading = ref(true)
const error = ref(null)

const fetchCompanyProfile = async () => {
  try {
    const res = await api.get('/company-profile')
        console.log('Company profile response:', res.data)

    company.value = res.data
  } catch (err) {
    console.error('Failed to fetch company profile:', err)
  }
}

const fetchInvoiceDetail = async () => {
  try {
    const res = await api.get(`/invoices/${route.params.id}`)
    invoice.value = res.data.data
  } catch (err) {
    error.value = 'Error fetching invoice details.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await Promise.all([fetchCompanyProfile(), fetchInvoiceDetail()])
})

const totalAmount = computed(() => {
  return invoice.value.items.reduce((sum, item) => {
    return sum + (item.quantity * item.unit_price)
  }, 0)
})

const formatCurrency = (value) => {
  if (isNaN(value)) return '$0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

const statusClass = computed(() => {
  switch ((invoice.value.status || '').toLowerCase()) {
    case 'paid':
      return 'status-paid text-green-600 font-semibold'
    case 'partial':
      return 'status-partial text-yellow-600 font-semibold'
    default:
      return 'status-unpaid text-red-600 font-semibold'
  }
})

const goBack = () => {
  router.push('/invoices')
}
</script>

<style scoped>
/* Status classes already inlined in computed */
.invoice-detail {
  font-family: Arial, sans-serif;
}

/* Print styles */
@media print {
  .print\\:hidden {
    display: none !important;
  }

  .invoice-detail {
    box-shadow: none !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }
}
</style>
