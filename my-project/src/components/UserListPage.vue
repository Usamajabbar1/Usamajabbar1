<template>
  <div>
    <UserList
      :key="usersKey"
      :users="users"
      :loadingUsers="loading"
      :usersError="error"
      :deletingUserId="deletingUserId"
      :searchQuery="searchQuery"
      :itemsPerPage="itemsPerPage"
      @edit-user="startEditingUser"
      @delete-user="handleDeleteUser"
      @update-status="handleUpdateStatus"
    />

    <!-- Modal Overlay & Edit Form -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="cancelEdit">
      <div class="modal-content">
        <h3>Edit User</h3>
        <form @submit.prevent="submitEditUser">
          <label>
            Name:
            <input v-model="editingUser.name" required />
          </label>
          <label>
            Email:
            <input type="email" v-model="editingUser.email" required />
          </label>
          <label>
            Role:
 <select v-if="roles.length" v-model="selectedRole" required>
  <option v-for="role in roles" :key="role.id" :value="role.name">
    {{ formatRoleName(role.name) }}
  </option>
</select>


          </label>
          <div class="modal-buttons">
            <button type="submit">Save</button>
            <button type="button" @click="cancelEdit">Cancel</button>
          </div>
          <p v-if="editError" class="error">{{ editError }}</p>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api';
import UserList from './UserList.vue';

export default {
  name: 'UserListPage',
  components: { UserList },
  data() {
    return {
      users: [],
      roles: [], // always initialized as array
      loading: false,
      error: null,
      deletingUserId: null,
      searchQuery: '',
      itemsPerPage: 10,
      editingUser: null,
      editError: '',
      showEditModal: false,
    };
  },
  computed: {
    usersKey() {
      return this.users.map(u => u.id).join('-');
    },
    filteredRoles() {
      return Array.isArray(this.roles)
        ? this.roles.filter(role => role && role.name)
        : [];
    }
  },
  created() {
    const usersFromStorage = sessionStorage.getItem('users');
    if (usersFromStorage) {
      this.users = JSON.parse(usersFromStorage);
      this.fetchUsers({ refresh: true });
    } else {
      this.fetchUsers();
    }
    this.fetchRoles();
  },
  methods: {
    formatRoleName(name) {
      if (!name) return 'Unnamed Role';
      return name.charAt(0).toUpperCase() + name.slice(1);
    },

    async fetchUsers({ refresh = false } = {}) {
      if (!refresh) this.loading = true;
      this.error = null;
      try {
        const response = await api.get('/users');
        this.users = response.data;
        sessionStorage.setItem('users', JSON.stringify(this.users));
      } catch (err) {
        this.error = 'Failed to load users.';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
async fetchRoles() {
  try {
    const response = await api.get('/roles');
    this.roles = response.data.roles || []; // ✅ FIX: use .roles
    console.log('Roles loaded:', this.roles);
  } catch (error) {
    console.error('Failed to load roles:', error);
    this.roles = [];
  }
}

,

  startEditingUser(userId) {
  const user = this.users.find(u => u.id === userId);
  if (user) {
    const clonedUser = JSON.parse(JSON.stringify(user));

    // Ensure roles exists
    if (!Array.isArray(clonedUser.roles)) {
      clonedUser.roles = [];
    }

    // Fallback if no role
    const currentRoleName = clonedUser.roles[0]?.name || '';
    this.selectedRole = currentRoleName;

    this.editingUser = clonedUser;
    this.editError = '';
    this.showEditModal = true;
  }
}
,

    cancelEdit() {
      this.editingUser = null;
      this.editError = '';
      this.showEditModal = false;
    },

    async submitEditUser() {
      try {
        this.editError = '';
      const payload = {
  name: this.editingUser.name,
  email: this.editingUser.email,
  role: this.selectedRole,
        };
        const response = await api.put(`/users/update/${this.editingUser.id}`, payload);
        let updatedUser = response.data;

        if (!updatedUser.roles && updatedUser.role) {
          updatedUser.roles = [{ name: updatedUser.role }];
          delete updatedUser.role;
        }

        const index = this.users.findIndex(u => u.id === updatedUser.id);
        if (index !== -1) {
          this.users.splice(index, 1, updatedUser);
          sessionStorage.setItem('users', JSON.stringify(this.users));
        } else {
          await this.fetchUsers();
        }

        this.editingUser = null;
        this.showEditModal = false;
        alert('User updated successfully!');
      } catch (error) {
        console.error(error);
        this.editError = error.response?.data?.message || 'Failed to update user.';
      }
    },

    async handleDeleteUser(userId) {
      if (!confirm('Are you sure you want to delete this user?')) return;

      this.deletingUserId = userId;
      try {
        await api.delete(`/users/delete/${userId}`);
        this.users = this.users.filter(user => user.id !== userId);
        sessionStorage.setItem('users', JSON.stringify(this.users));
        alert('User deleted successfully!');
      } catch (error) {
        console.error('Failed to delete user:', error);
        alert('Failed to delete user.');
      } finally {
        this.deletingUserId = null;
      }
    },

    async handleUpdateStatus({ id, status }) {
      try {
        await api.put(`/users/${id}/update-status`, { status });
        await this.fetchUsers({ refresh: true });
        alert('User status updated successfully!');
      } catch (error) {
        console.error('Failed to update status:', error);
        alert('Failed to update user status');
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: white;
  padding: 20px 30px;
  border-radius: 10px;
  width: 400px;
  max-width: 90vw;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  position: relative;
}

.modal-content h3 {
  margin-top: 0;
  margin-bottom: 15px;
  font-weight: 600;
  text-align: center;
}

.modal-content label {
  display: block;
  margin-bottom: 10px;
}

.modal-content input,
.modal-content select {
  width: 100%;
  padding: 8px;
  margin-top: 4px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  margin-top: 15px;
}

.modal-buttons button {
  padding: 8px 14px;
  margin-left: 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  background-color: #4e73df;
  color: white;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.modal-buttons button:hover {
  background-color: #2e59d9;
}

.error {
  margin-top: 10px;
  color: #e74a3b;
  font-weight: 600;
  text-align: center;
}
</style>
