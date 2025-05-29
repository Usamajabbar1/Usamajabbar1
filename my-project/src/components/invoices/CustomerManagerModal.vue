<template>
  <div class="modal-backdrop">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <h2 id="modalTitle" class="text-lg font-bold mb-4">Customer Manager</h2>

      <!-- Customer Form -->
      <form @submit.prevent="handleSubmit" class="mb-4" novalidate>
        <input
          v-model.trim="form.name"
          placeholder="Full Name"
          class="input mb-2"
          required
          autocomplete="off"
        />
        <input
          v-model.trim="form.email"
          placeholder="Email"
          class="input mb-2"
          type="email"
          autocomplete="off"
        />
        <input
          v-model.trim="form.phone"
          placeholder="Phone Number"
          class="input mb-2"
          type="tel"
          autocomplete="off"
        />

        <div class="flex justify-between">
          <button type="submit" class="btn btn-primary">
            {{ isEditing ? 'Update' : 'Add' }}
          </button>
          <button
            type="button"
            class="btn btn-secondary"
            @click="resetForm"
            v-if="isEditing"
          >
            Cancel
          </button>
        </div>
      </form>

      <!-- Customer List -->
      <ul>
        <li
          v-for="customer in customers"
          :key="customer.id"
          class="flex justify-between items-center mb-2"
        >
          <span>
            {{ customer.name }} ({{ customer.email || '—' }})
          </span>
          <div class="space-x-2">
            <button
              class="btn btn-secondary btn-sm"
              @click="editCustomer(customer)"
              aria-label="Edit customer"
            >
              Edit
            </button>
            <button
              class="btn btn-danger btn-sm"
              @click="deleteCustomer(customer.id)"
              aria-label="Archive customer"
            >
              Archive
            </button>
          </div>
        </li>
      </ul>

      <button class="btn btn-secondary mt-6" @click="$emit('close')">Close</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/axios';

const emit = defineEmits(['close', 'refresh']);
const customers = ref([]);
const isEditing = ref(false);
const form = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
});

const fetchCustomers = async () => {
  try {
    const res = await api.get('/customers');
    customers.value = res.data.data.filter((c) => !c.archived);
  } catch (err) {
    console.error('Failed to fetch customers', err);
    alert('Failed to load customers');
  }
};

const handleSubmit = async () => {
  try {
    if (!form.value.name.trim()) return alert('Name is required.');

    if (form.value.email && !validateEmail(form.value.email)) {
      return alert('Please enter a valid email address.');
    }

    if (isEditing.value) {
      await api.put(`/customers/${form.value.id}`, form.value);
    } else {
      await api.post('/customers', form.value);
    }

    await fetchCustomers();
    emit('refresh');
    resetForm();
  } catch (err) {
    alert('Error saving customer');
    console.error(err);
  }
};

const editCustomer = (customer) => {
  form.value = {
    id: customer.id,
    name: customer.name,
    email: customer.email || '',
    phone: customer.phone || '',
  };
  isEditing.value = true;
};

const deleteCustomer = async (id) => {
  if (
    !confirm(
      'Are you sure you want to archive this customer? They will no longer be selectable but will not be deleted.'
    )
  )
    return;

  try {
    await api.put(`/customers/archive/${id}`);
    await fetchCustomers();
    emit('refresh');
  } catch (err) {
    alert(err.response?.data?.message || 'Error archiving customer');
    console.error(err);
  }
};

const resetForm = () => {
  form.value = { id: null, name: '', email: '', phone: '' };
  isEditing.value = false;
};

const validateEmail = (email) => {
  const re =
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email.toLowerCase());
};

onMounted(fetchCustomers);
</script>

<style scoped>
/* Your existing styles here — no changes needed */
</style>


  <style scoped>
  .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
  }

  .modal {
    background: white;
    padding: 24px;
    border-radius: 8px;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }

  .input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 15px;
    margin-bottom: 8px;
  }

  .btn {
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
  }

  .btn-primary {
    background-color: #3b82f6;
    color: white;
  }

  .btn-primary:hover {
    background-color: #2563eb;
  }

  .btn-secondary {
    background-color: #9ca3af;
    color: white;
  }

  .btn-secondary:hover {
    background-color: #6b7280;
  }

  .btn-danger {
    background-color: #ef4444;
    color: white;
  }

  .btn-danger:hover {
    background-color: #dc2626;
  }

  .btn-sm {
    font-size: 13px;
    padding: 6px 10px;
  }
  </style>
