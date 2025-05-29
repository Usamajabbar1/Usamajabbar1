<template>
  <div class="dashboard-container">
    <!-- <h1>{{ isAdmin ? 'Admin Panel' : 'Dashboard' }}</h1> -->

    <!-- ✅ Success Message -->
    <div v-if="successMessage" class="success-message">
      {{ successMessage }}
    </div>

    <!-- 👨‍💼 Admin Section -->
    <div v-if="isAdmin">
      <h2>User Management</h2>
      <ul>
        <li v-for="u in users" :key="u.id">
          {{ u.name }} ({{ u.email }})
          <button @click="editUser(u.id)">Edit</button>
        </li>
      </ul>

      <!-- ✏️ Edit User Modal -->
      <EditUserModal
        v-if="showEditModal"
        :visible="showEditModal"
        :user="selectedUser"
        :roles="roles"
        @submit="updateUser"
        @close="showEditModal = false"
      />
    </div>
  </div>
</template>

<script>
import api from '../axios';
import EditUserModal from '@/components/EditUserModal.vue';

export default {
  name: 'HomeView',
  components: {
    EditUserModal,
  },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')) || {},
      users: [],
      roles: JSON.parse(localStorage.getItem('roles')) || [],
      selectedUser: null,
      showEditModal: false,
      successMessage: '',
    };
  },
  computed: {
    isAdmin() {
      return (this.user.role || '').toLowerCase() === 'admin';
    },
  },
  methods: {
    async fetchUsers() {
      try {
        const res = await api.get('/users');
        this.users = res.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    editUser(id) {
      const user = this.users.find(u => u.id === id);
      if (user) {
        this.selectedUser = { ...user };
        this.showEditModal = true;
      }
    },
    async updateUser(updatedUser) {
      try {
        const res = await api.put(`/users/update/${updatedUser.id}`, updatedUser);
        if (res.status === 200) {
          await this.fetchUsers();
          this.successMessage = 'User updated successfully!';
          this.showEditModal = false;
          setTimeout(() => (this.successMessage = ''), 3000);
        }
      } catch (error) {
        console.error('Update failed:', error);
      }
    },
  },
  async created() {
    if (this.isAdmin) {
      await this.fetchUsers();
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
