<template>
  <div class="users-list">
    <h2>All Users</h2>

    <div class="search-container">
      <input
        v-model="searchQueryInternal"
        class="search-input"
        placeholder="Search users..."
      />
    </div>

    <div v-if="loadingUsers" class="loading-message">Loading users...</div>
    <div v-if="usersError" class="error-message">{{ usersError }}</div>
    <div v-else-if="filteredUsers.length === 0" class="no-users-message">No users found.</div>
    
    <table v-else>
      <thead>
        <tr>
          <th>S. No</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user, index) in paginatedUsers" :key="user.id">
          <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.roles?.[0]?.name || 'N/A' }}</td>
         <td>
  <select v-model="user.status" @change="updateStatus(user)">
    <option value="active">Active</option>
    <option value="pending">Pending</option>
    <option value="suspended">Suspended</option>
  </select>
</td>

          <td>
            <button class="edit-btn" @click="$emit('edit-user', user.id)">Edit</button>
            <button
              class="delete-btn"
              :disabled="deletingUserId === user.id"
              @click="$emit('delete-user', user.id)"
            >
              {{ deletingUserId === user.id ? 'Deleting...' : 'Delete' }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="pagination-controls" v-if="totalPages > 1">
      <button
        class="pagination-btn"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        Prev
      </button>
      Page {{ currentPage }} of {{ totalPages }}
      <button
        class="pagination-btn"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserList',
  props: {
    users: {
      type: Array,
      default: () => []
    },
    loadingUsers: Boolean,
    usersError: String,
    deletingUserId: Number,
    searchQuery: String,
    itemsPerPage: Number,
  },
  data() {
    return {
      currentPage: 1,
      searchQueryInternal: '',
    };
  },
  watch: {
    searchQuery: {
      immediate: true,
      handler(newVal) {
        this.searchQueryInternal = newVal;
      },
    },
    searchQueryInternal() {
      this.currentPage = 1;
    },
  },
  computed: {
    filteredUsers() {
      if (!Array.isArray(this.users)) return [];
      if (!this.searchQueryInternal) return this.users;
      const query = this.searchQueryInternal.toLowerCase();
      return this.users.filter(user => {
        const roleName = user.roles?.[0]?.name?.toLowerCase() || '';
        const status = user.status?.toLowerCase() || '';
        return (
          user.name.toLowerCase().includes(query) ||
          user.email.toLowerCase().includes(query) ||
          roleName.includes(query) ||
          status.includes(query)
        );
      });
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredUsers.slice(start, start + this.itemsPerPage);
    },
  },
  methods: {
    updateStatus(user) {
      // Emit an event with the user's id and new status
      this.$emit('update-status', { id: user.id, status: user.status });
    }
  }
};
</script>

<style scoped>
.users-list {
  margin-top: 20px;
  text-align: left;
}

h2 {
  margin-bottom: 10px;
  color: #2c3e50;
}

.search-container {
  margin-bottom: 15px;
}

.search-input {
  width: 100%;
  max-width: 400px;
  padding: 10px 15px;
  font-size: 16px;
  border: 1.5px solid #ccc;
  border-radius: 8px;
  transition: border-color 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #28a745;
}

.loading-message,
.no-users-message,
.error-message {
  font-weight: 600;
  padding: 12px;
  margin-bottom: 20px;
}

.error-message {
  color: #dc3545;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f4f4f4;
  color: #333;
  text-align: left;
}

.edit-btn, .delete-btn {
  padding: 6px 12px;
  margin-right: 8px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.edit-btn {
  background-color: #ffc107;
  color: white;
}

.edit-btn:hover {
  background-color: #e0a800;
}

.delete-btn {
  background-color: #dc3545;
  color: white;
}
.status-select {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
  background-color: white;
  cursor: pointer;
}


.delete-btn:hover:not(:disabled) {
  background-color: #c82333;
}

.delete-btn:disabled {
  background-color: #f5a1a1;
  cursor: not-allowed;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 6px;
  font-weight: 600;
  text-transform: capitalize;
  display: inline-block;
}

.status-badge.active {
  background-color: #28a745;
  color: white;
}

.status-badge.suspended {
  background-color: #ffc107;
  color: black;
}

.status-badge.pending {
  background-color: #6c757d;
  color: white;
}

.pagination-controls {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  font-weight: 600;
  color: #333;
}

.pagination-btn {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 8px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.3s ease;
}

.pagination-btn:disabled {
  background-color: #a5d6a7;
  cursor: not-allowed;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #218838;
}
</style>
