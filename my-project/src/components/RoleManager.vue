<template>
  <div class="role-manager">
    <h2 class="title">Role Management</h2>

    <!-- Role Form -->
    <div class="form-container">
      <input
        v-model="newRole"
        type="text"
        placeholder="Enter role name"
        :class="{ error: errors.name }"
      />
      <div v-if="errors.name" class="error-message">{{ errors.name }}</div>

      <!-- Permissions -->
      <div class="permissions-list" :class="{ error: errors.permission }">
        <label
          v-for="permission in permissions"
          :key="permission.id"
          class="permission-checkbox"
        >
          <input
            type="checkbox"
            :value="permission.name"
            v-model="selectedPermissions"
          />
          {{ permission.name }}
        </label>
      </div>
      <div v-if="errors.permission" class="error-message">
        {{ errors.permission }}
      </div>

      <button
        @click="addOrUpdateRole"
        :disabled="!newRole.trim() || selectedPermissions.length === 0 || loading"
      >
        {{ isEditing ? "Update Role" : "Add Role" }}
      </button>
    </div>

    <!-- Role List -->
    <ul class="role-list">
      <li v-for="role in roles" :key="role.id">
        <span>{{ role.name }}</span>
        <div class="actions">
          <button @click="editRole(role)">Edit</button>
          <button @click="deleteRole(role.id)" class="delete">Delete</button>
        </div>
      </li>
    </ul>

    <!-- Reassign Dialog -->
    <div v-if="reassignDialogVisible" class="reassign-dialog">
      <p>
        This role is assigned to users. Please select another role to reassign them before deletion.
      </p>
      <select v-model="reassignRoleId">
        <option disabled value="">Select a role</option>
        <option v-for="role in availableRoles" :key="role.id" :value="role.id">
          {{ role.name }}
        </option>
      </select>
      <div class="dialog-actions">
        <button @click="reassignUsersAndDelete">Submit</button>
        <button @click="reassignDialogVisible = false">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/axios";

export default {
  name: "RoleManager",
  data() {
    return {
      roles: [],
      permissions: [],
      newRole: "",
      selectedPermissions: [],
      isEditing: false,
      editingId: null,
      errors: {},
      loading: false,
      reassignDialogVisible: false,
      roleToDelete: null,
      reassignRoleId: null,
      availableRoles: [],
      selectedRole: '', // for dropdown binding
    };
  },
  async created() {
    await this.fetchRoles();
    await this.fetchPermissions();
  },
  methods: {
    async fetchRoles() {
      try {
        const response = await api.get("/roles");
        this.roles = response.data.roles;
      } catch (error) {
        console.error("Failed to fetch roles:", error);
      }
    },

 async fetchPermissions() {
  try {
    const response = await api.get("/roles/permissions");

    const excluded = [
      "role-list",
      "role-create",
      "role-edit",
      "role-delete",
      "assign-role",
      "view users",
      "create users",
      "view reports",
      "view content"
    ];

    // Filter out unwanted permissions
    this.permissions = response.data.permissions.filter(
      (perm) => !excluded.includes(perm.name)
    );
  } catch (error) {
    console.error("Failed to fetch permissions:", error);
  }
}
,

    async addOrUpdateRole() {
      this.errors = {};

      if (!this.newRole.trim()) {
        this.errors.name = "Role name is required.";
      }
      if (this.selectedPermissions.length === 0) {
        this.errors.permission = "Please select at least one permission.";
      }
      if (Object.keys(this.errors).length) return;

      this.loading = true;

      try {
        const payload = {
          name: this.newRole.trim(),
          permission: this.selectedPermissions,
        };

        if (this.isEditing) {
          await api.put(`/roles/update/${this.editingId}`, payload);
        } else {
          await api.post("/roles/add", payload);
        }

        this.resetForm();
        await this.fetchRoles();
      } catch (error) {
        if (error.response?.status === 422) {
          const backendErrors = error.response.data.errors;
          for (const key in backendErrors) {
            this.errors[key] = backendErrors[key][0];
          }
        } else {
          console.error("Role save failed:", error);
        }
      } finally {
        this.loading = false;
      }
    },

    async editRole(role) {
      this.resetForm();
      this.newRole = role.name;
      this.isEditing = true;
      this.editingId = role.id;

      try {
        const response = await api.get(`/roles/show/${role.id}`);
        const roleData = response.data.data;
        this.selectedPermissions = roleData.permissions.map((p) => p.name);
      } catch (error) {
        console.error("Failed to fetch role details:", error);
      }
    },

    async deleteRole(id) {
      if (!confirm("Are you sure you want to delete this role?")) return;

      try {
        await api.delete(`/roles/delete/${id}`);
        await this.fetchRoles();
      } catch (error) {
        const msg = error.response?.data?.message || "";

        if (error.response?.status === 400 && msg.includes("assigned to")) {
          this.roleToDelete = id;
          this.availableRoles = this.roles.filter((r) => r.id !== id);
          this.reassignDialogVisible = true;
        } else {
          console.error("Failed to delete role:", error);
          alert("An error occurred while trying to delete the role.");
        }
      }
    },

    async reassignUsersAndDelete() {
      if (!this.reassignRoleId) {
        alert("Please select a role to reassign users.");
        return;
      }

      try {
        await api.put(`/roles/reassign-users`, {
          from_role_id: this.roleToDelete,
          to_role_id: this.reassignRoleId,
        });

        await api.delete(`/roles/delete/${this.roleToDelete}`);
        this.reassignDialogVisible = false;
        this.roleToDelete = null;
        this.reassignRoleId = null;

        await this.fetchRoles();
      } catch (error) {
        console.error("Reassignment or deletion failed:", error);
        alert("An error occurred while reassigning users.");
      }
    },

    resetForm() {
      this.newRole = "";
      this.selectedPermissions = [];
      this.isEditing = false;
      this.editingId = null;
      this.errors = {};
    },
  },
};
</script>

<style scoped>
.role-manager {
  max-width: 600px;
  margin: 40px auto;
  padding: 30px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  font-family: "Segoe UI", sans-serif;
}

.title {
  font-size: 1.8rem;
  color: #2c3e50;
  margin-bottom: 20px;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 30px;
}

.form-container input {
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 1rem;
}

.form-container input.error {
  border-color: #e74c3c;
}

.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 10px 0;
  max-height: 150px;
  overflow-y: auto;
  border: 1px solid #ddd;
  padding: 10px;
  border-radius: 6px;
  background: #fafafa;
}

.permissions-list.error {
  border-color: #e74c3c;
}

.permission-checkbox {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.9rem;
}

.error-message {
  color: #e74c3c;
  font-size: 0.85rem;
  margin-top: -8px;
  margin-bottom: 8px;
}

.form-container button {
  padding: 10px 20px;
  background-color: #1abc9c;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
}

.form-container button:disabled {
  background-color: #95d3c3;
  cursor: not-allowed;
}

.role-list {
  list-style: none;
  padding: 0;
}

.role-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9fafb;
  padding: 12px 16px;
  margin-bottom: 10px;
  border-radius: 6px;
  box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
}

.actions button {
  margin-left: 10px;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.actions .delete {
  background-color: #e74c3c;
  color: white;
}

.actions button:not(.delete) {
  background-color: #3498db;
  color: white;
}

.reassign-dialog {
  background: #fff;
  border: 1px solid #ddd;
  padding: 20px;
  margin-top: 20px;
  border-radius: 8px;
}

.reassign-dialog select {
  width: 100%;
  padding: 8px;
  margin-top: 10px;
  margin-bottom: 10px;
}

.dialog-actions {
  display: flex;
  gap: 10px;
}
</style>
