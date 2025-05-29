<template>
  <div class="container">
    <h2 class="title">Customer Management</h2>

    <!-- Add/Edit Form -->
    <div class="card form-card">
      <h3 class="card-title">{{ editingCustomer ? 'Edit Customer' : 'Add New Customer' }}</h3>
      <form @submit.prevent="saveCustomer" class="form" novalidate>
        <input
          v-model.trim="form.name"
          placeholder="Full Name"
          class="input"
          required
          autocomplete="off"
        />
        <input
          v-model.trim="form.email"
          placeholder="Email Address"
          class="input"
          type="email"
          autocomplete="off"
        />
        <input
          v-model.trim="form.phone"
          placeholder="Phone Number"
          class="input"
          type="tel"
          autocomplete="off"
        />
        <!-- Added Address field -->
        <input
          v-model.trim="form.address"
          placeholder="Address"
          class="input"
          autocomplete="off"
        />

        <div class="button-group">
          <button type="submit" class="btn btn-primary">
            {{ editingCustomer ? 'Update' : 'Add' }}
          </button>
          <button type="button" class="btn btn-secondary" @click="resetForm">
            Cancel
          </button>
        </div>
      </form>
    </div>

    <!-- Customer Table -->
    <div class="card table-card">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th> <!-- Added Address header -->
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="customers.length === 0">
            <td colspan="5" class="empty-row">No customers found.</td> <!-- colspan adjusted -->
          </tr>
          <tr v-for="customer in customers" :key="customer.id">
            <td>{{ customer.name }}</td>
            <td>{{ customer.email || '—' }}</td>
            <td>{{ customer.phone || '—' }}</td>
            <td>{{ customer.address || '—' }}</td> <!-- Added Address column -->
            <td class="text-right">
              <button @click="editCustomer(customer)" class="btn btn-sm btn-secondary">
                Edit
              </button>
              <button @click="deleteCustomer(customer.id)" class="btn btn-sm btn-danger">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/axios';

const customers = ref([]);
const editingCustomer = ref(null);
const form = ref({ name: '', email: '', phone: '', address: '' }); // added address

const fetchCustomers = async () => {
  try {
    const res = await api.get('/customers');
    customers.value = res.data.data.filter(c => !c.archived); // only active customers
  } catch (err) {
    console.error('Error loading customers', err);
    alert('Failed to load customers');
  }
};

const saveCustomer = async () => {
  try {
    if (!form.value.name.trim()) return alert('Name is required.');

    if (form.value.email && !validateEmail(form.value.email)) {
      return alert('Please enter a valid email address.');
    }

    if (editingCustomer.value) {
      await api.put(`/customers/${editingCustomer.value.id}`, form.value);
    } else {
      await api.post('/customers', form.value);
    }

    resetForm();
    await fetchCustomers();
  } catch (err) {
    alert('Failed to save customer.');
    console.error(err);
  }
};

const editCustomer = (customer) => {
  editingCustomer.value = customer;
  form.value = {
    name: customer.name,
    email: customer.email || '',
    phone: customer.phone || '',
    address: customer.address || '', // added address
  };
};

const deleteCustomer = async (id) => {
  if (
    confirm(
      'Are you sure you want to delete this customer? This action cannot be undone.'
    )
  ) {
    try {
      await api.delete(`/customers/${id}`);
      await fetchCustomers();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete customer.');
      console.error(err);
    }
  }
};

const resetForm = () => {
  form.value = { name: '', email: '', phone: '', address: '' }; // reset address
  editingCustomer.value = null;
};

const validateEmail = (email) => {
  const re =
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email.toLowerCase());
};

onMounted(fetchCustomers);
</script>


<style scoped>
.container {
  max-width: 960px;
  margin: 0 auto;
  padding: 2rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.title {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  color: #2d3748;
}

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.card-title {
  font-size: 1.25rem;
  margin-bottom: 1rem;
  color: #374151;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.input {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  background-color: #f9f9f9;
  transition: border-color 0.2s;
}
.input:focus {
  outline: none;
  border-color: #4a90e2;
}

.button-group {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.btn {
  padding: 8px 14px;
  font-size: 14px;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  font-weight: 500;
}

.btn-primary {
  background-color: #4a90e2;
  color: white;
}
.btn-primary:hover {
  background-color: #347edc;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}
.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}
.btn-danger:hover {
  background-color: #c82333;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
  margin-left: 5px;
}

.table-card {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.table th,
.table td {
  padding: 12px 16px;
  border-bottom: 1px solid #eee;
}

.table th {
  background-color: #f1f5f9;
  color: #555;
  text-align: left;
  font-weight: 600;
}

.table tr:hover {
  background-color: #f9fafb;
}

.empty-row {
  text-align: center;
  padding: 2rem;
  color: #999;
}

.text-right {
  text-align: right;
}
</style>
