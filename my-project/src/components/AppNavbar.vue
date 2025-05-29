<template>
  <header class="navbar">
    <div class="navbar-left">
      <!-- Left side content can go here -->
    </div>

    <div class="navbar-right">
      <!-- 🔔 Notification button removed -->

      <div class="profile-dropdown" @click="toggleDropdown">
        <img :src="user.avatar || defaultAvatar" alt="Avatar" class="avatar" />
        <span>{{ user.name }}</span>

        <div v-if="showDropdown" class="dropdown-menu">
          <p v-if="user.role === 'admin'" class="dropdown-item admin-label">
            Admin Panel
            <br />
            <small>You are logged in as admin.</small>
          </p>

          <div class="dropdown-item profile-info">
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Status:</strong> {{ user.status }}</p>
            <p><strong>Role:</strong> {{ user.role }}</p>
          </div>

          <button class="dropdown-item logout-btn" @click.stop="logout">Logout</button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";

export default {
  name: "AppNavbar",
  data() {
    return {
      showDropdown: false,
      defaultAvatar: "https://ui-avatars.com/api/?name=User",
      user: {
        name: "",
        email: "",
        avatar: "",
        role: "",
        status: "",
      },
    };
  },
  mounted() {
    this.fetchUserProfile();
  },
  methods: {
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    async fetchUserProfile() {
      const token = localStorage.getItem("token");
      if (!token) return;

      try {
        const response = await axios.get("http://127.0.0.1:8000/api/me", {
          headers: { Authorization: `Bearer ${token}` },
        });
        const { name, email, avatar, role, status } = response.data;
        this.user = { name, email, avatar, role, status };
      } catch (error) {
        console.error("Failed to fetch user profile:", error);
      }
    },
    async logout() {
      const toast = useToast();
      try {
        await axios.post(
          "http://127.0.0.1:8000/api/logout",
          {},
          { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
        );
        toast.success("Logged out successfully");
      } catch (e) {
        toast.error("Logout failed");
      } finally {
        localStorage.clear();
        this.$router.push("/login");
      }
    },
  },
};
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 1.5rem;
  background-color: #ffffff;
  border-bottom: 1px solid #ddd;
  align-items: center;
}

.profile-dropdown {
  position: relative;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 0.5rem;
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 40px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  min-width: 220px;
  z-index: 10;
  padding: 0.5rem;
}

.dropdown-item {
  padding: 0.5rem 0.75rem;
  white-space: nowrap;
  font-size: 0.9rem;
  color: #333;
}

.dropdown-item:hover {
  background-color: #f5f5f5;
}

.admin-label {
  font-weight: bold;
  color: #2e7d32;
}

.profile-info p {
  margin: 0.2rem 0;
  font-size: 0.85rem;
}

.logout-btn {
  color: #d32f2f;
  font-weight: bold;
  width: 100%;
  text-align: left;
  border: none;
  background: none;
  cursor: pointer;
  padding: 0.5rem 0.75rem;
}
.logout-btn:hover {
  background-color: #ffebee;
}
</style>
