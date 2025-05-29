<template>
  <div class="dashboard-container">
    <!-- ✅ Success Message -->
    <div v-if="successMessage" class="success-message">
      {{ successMessage }}
    </div>

    <!-- 👨‍💼 Admin Section -->
    <div v-if="isAdmin">
      <h2>Supplier Management</h2>
      <ul>
        <li v-for="s in suppliers" :key="s.id">
          {{ s.name }} ({{ s.email || 'No Email' }})
          <button @click="editSupplier(s)">Edit</button>
        </li>
      </ul>

      <!-- ✏️ Edit Supplier Modal -->
      <EditSupplierModal
        v-if="showEditSupplierModal"
        :visible="showEditSupplierModal"
        :supplier="selectedSupplier"
        @submit="updateSupplier"
        @close="showEditSupplierModal = false"
      />
    </div>
  </div>
</template>

<script>
import api from '../axios';
import EditSupplierModal from '@/components/invoices/EditSupplierModal.vue';

export default {
  name: 'HomeView',
  components: {
    EditSupplierModal,
  },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')) || {},
      suppliers: [],
      selectedSupplier: null,
      showEditSupplierModal: false,
      successMessage: '',
    };
  },
  computed: {
    isAdmin() {
      return (this.user.role || '').toLowerCase() === 'admin';
    },
  },
  methods: {
    async fetchSuppliers() {
      try {
        const res = await api.get('/suppliers');
        this.suppliers = res.data;
      } catch (error) {
        console.error('Error fetching suppliers:', error);
      }
    },
    editSupplier(supplier) {
      this.selectedSupplier = { ...supplier };
      this.showEditSupplierModal = true;
    },
    async updateSupplier(updatedSupplier) {
      try {
        const res = await api.put(`/suppliers/${updatedSupplier.id}`, updatedSupplier);
        if (res.status === 200) {
          await this.fetchSuppliers();
          this.successMessage = 'Supplier updated successfully!';
          this.showEditSupplierModal = false;
          setTimeout(() => (this.successMessage = ''), 3000);
        }
      } catch (error) {
        console.error('Supplier update failed:', error);
      }
    },
  },
  async created() {
    if (this.isAdmin) {
      await this.fetchSuppliers();
    }
  },
};
</script>

<style scoped>
.dashboard-container {
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

.success-message {
  color: #27ae60;
  font-weight: 600;
  margin-bottom: 1rem;
}

ul {
  list-style-type: none;
  padding: 0;
}

li {
  margin: 0.5rem 0;
  display: flex;
  align-items: center;
}

button {
  margin-left: 1rem;
  background-color: #1abc9c;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #16a085;
}
</style>
