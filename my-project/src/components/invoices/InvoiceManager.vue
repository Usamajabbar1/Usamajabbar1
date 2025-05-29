<template>
  <div class="invoice-manager">
    <div class="header">
      <h2>Invoices</h2>
      <button class="btn primary" @click="goToCreate">+ Create New Invoice</button>
    </div>

    <div v-if="invoices.length === 0">No invoices found.</div>

    <table v-else class="invoice-table">
      <thead>
        <tr>
          <th v-for="key in visibleKeys" :key="key">
            {{ formatHeader(key) }}
          </th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
        <template v-for="invoice in invoices" :key="invoice.id">
          <!-- Main invoice row -->
          <tr>
            <td v-for="key in visibleKeys" :key="key">
              {{ formatValue(invoice[key]) }}
            </td>
            <td class="actions">
              <button class="btn small secondary" @click="downloadInvoice(invoice.id)">Download PDF</button>
              <button class="btn small secondary" @click="goToEdit(invoice.id)">Edit</button>
              <button class="btn small danger" @click="deleteInvoice(invoice.id)">Delete</button>
              <button class="btn small toggle" @click="toggleExpanded(invoice.id)">
                {{ expandedId === invoice.id ? 'Hide Items' : 'Show Items' }}
              </button>
              <!-- <button class="btn small secondary" @click="sendInvoiceEmail(invoice.id)">Send Email</button> -->
            </td>
          </tr>

          <!-- Expanded item details row -->
          <tr v-if="expandedId === invoice.id" class="expanded-row">
            <td :colspan="visibleKeys.length + 1">
              <div v-if="invoice.items && invoice.items.length">
                <h4>Invoice Items</h4>
                <table class="nested-table">
                  <thead>
                    <tr>
                      <th v-for="itemKey in Object.keys(invoice.items[0])" :key="itemKey">
                        {{ formatHeader(itemKey) }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in invoice.items" :key="item.id || item.description">
                      <td v-for="itemKey in Object.keys(item)" :key="itemKey">
                        {{ formatValue(item[itemKey]) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else>No items available.</div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/axios';

const invoices = ref([]);
const expandedId = ref(null);
const router = useRouter();

const visibleKeys = ['invoice_number', 'invoice_date', 'customer_name', 'status', 'total_amount'];

const fetchInvoices = async () => {
  try {
    const response = await axios.get('/invoices');
    invoices.value = response.data.data;
  } catch (error) {
    console.error('Error fetching invoices:', error);
  }
};

const downloadInvoice = async (id) => {
  try {
    const response = await axios.get(`/invoices/${id}/download`, {
      responseType: 'blob',
    });
    const blob = new Blob([response.data], { type: 'application/pdf' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `invoice_${id}.pdf`;
    link.click();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Download failed:', error);
  }
};

// const sendInvoiceEmail = async (id) => {
//   try {
//     await axios.post(`/api/invoices/${id}/send-email`);
//     alert('Invoice email sent successfully!');
//   } catch (error) {
//     console.error('Failed to send invoice email:', error);
//     alert('Failed to send invoice email.');
//   }
// };

const deleteInvoice = async (id) => {
  if (confirm('Are you sure you want to delete this invoice?')) {
    try {
      await axios.delete(`/invoices/${id}`);
      await fetchInvoices();
    } catch (error) {
      console.error('Failed to delete invoice:', error);
    }
  }
};

const goToCreate = () => router.push('/invoices/create');
const goToEdit = (id) => router.push(`/invoices/${id}/edit`);
const toggleExpanded = (id) => {
  expandedId.value = expandedId.value === id ? null : id;
};

const formatHeader = (key) => {
  return key
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase());
};

const formatValue = (val) => {
  if (!val && val !== 0) return '—';
  if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}/.test(val)) {
    return new Date(val).toLocaleDateString();
  }
  if (!isNaN(val) && val !== '' && val !== null) {
    return parseFloat(val).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }
  return val;
};

onMounted(fetchInvoices);
</script>

<style scoped>
.invoice-manager {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.invoice-table,
.nested-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.invoice-table th,
.invoice-table td,
.nested-table th,
.nested-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}

.btn {
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.primary {
  background-color: #007bff;
  color: white;
}

.secondary {
  background-color: #6c757d;
  color: white;
}

.danger {
  background-color: #dc3545;
  color: white;
}

.toggle {
  background-color: #17a2b8;
  color: white;
}

.small {
  font-size: 0.85rem;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.expanded-row {
  background-color: #f9f9f9;
}
</style>
