<template>
  <div>
    <h2>User Management</h2>
    <UserList
      :users="users"
      :loadingUsers="loadingUsers"
      :usersError="usersError"
      :deletingUserId="deletingUserId"
      :searchQuery="searchQuery"
      :itemsPerPage="itemsPerPage"
      @edit-user="editUser"
      @delete-user="deleteUser"
    />
    <EditUserModal
      v-if="showEditModal"
      :visible="showEditModal"
      :user="selectedUser"
      :roles="roles"
      @submit="updateUser"
      @close="showEditModal = false"
    />
  </div>
</template>

<script>
import api from '../axios';
import UserList from '@/components/UserList.vue';
import EditUserModal from '@/components/EditUserModal.vue';

export default {
  name: 'UserManagement',
  components: { UserList, EditUserModal },
  data() {
    return {
      users: [],
      loadingUsers: false,
      usersError: null,
      deletingUserId: null,
      searchQuery: '',
      itemsPerPage: 10,
      showEditModal: false,
      selectedUser: null,
      roles: [],
    };
  },
  methods: {
    async fetchUsers() {
      this.loadingUsers = true;
      this.usersError = null;
      try {
        const response = await api.get('/users');
        this.users = response.data;
      } catch (error) {
        this.usersError = 'Failed to load users.';
        console.error(error);
      } finally {
        this.loadingUsers = false;
      }
    },
    async fetchRoles() {
      try {
        const response = await api.get('/roles');
        this.roles = response.data.roles;
      } catch (error) {
        console.error(error);
      }
    },
    async editUser(userId) {
      if (!this.roles.length) {
        await this.fetchRoles();
      }
      const user = this.users.find(u => u.id === userId);
      if (user) {
        this.selectedUser = { ...user };
        this.showEditModal = true;
      }
    },
    async updateUser(updatedUser) {
      try {
        await api.put(`/users/update/${updatedUser.id}`, updatedUser);
        this.showEditModal = false;
        await this.fetchUsers();
      } catch (error) {
        console.error(error);
      }
    },
    async deleteUser(userId) {
      if (!confirm('Are you sure?')) return;
      this.deletingUserId = userId;
      try {
        await api.delete(`/users/delete/${userId}`);
        await this.fetchUsers();
      } catch (error) {
        console.error(error);
      } finally {
        this.deletingUserId = null;
      }
    },
  },
  created() {
    this.fetchUsers();
  },
};
</script>
