<template>
  <div class="layout-container">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'dark-mode': isDark }">
      <div class="sidebar-header">
        {{ isAdmin ? 'Admin Panel' : 'User Panel' }}
        <button class="theme-toggle" @click="toggleTheme">
          {{ isDark ? '☀️ Light' : '🌙 Dark' }}
        </button>
      </div>

      <nav class="sidebar-nav">
        <ul>
          <li>
            <router-link to="/dashboard" :class="{ active: $route.path === '/dashboard' }">
              <HomeIcon class="icon" /> Dashboard
            </router-link>
          </li>

          <!-- Admin-only User Management Dropdown -->
          <template v-if="isAdmin">
            <li>
              <div @click="toggleUserDropdown" class="dropdown-header" :class="{ expanded: showUserDropdown }">
                <UsersIcon class="icon" />
                User Management
                <ChevronDownIcon class="arrow" :class="{ rotate: showUserDropdown }" />
              </div>
              <transition-group name="dropdown" tag="ul" class="dropdown-items" v-show="showUserDropdown">
                <li key="register">
                  <router-link to="/users/register" :class="{ active: $route.path === '/users/register' }">
                    <UserPlusIcon class="icon" /> Add New User
                  </router-link>
                </li>
                <li key="list">
                  <router-link to="/users/list" :class="{ active: $route.path === '/users/list' }">
                    <ListIcon class="icon" /> User List
                  </router-link>
                </li>
                <li key="roles">
                  <router-link to="/roles" :class="{ active: $route.path === '/roles' }">
                    <ShieldIcon class="icon" /> Role Manager
                  </router-link>
                </li>
              </transition-group>
            </li>

            <!-- Invoice Management Dropdown -->
            <li>
              <div @click="toggleInvoiceDropdown" class="dropdown-header" :class="{ expanded: showInvoiceDropdown }">
                <FileTextIcon class="icon" />
                Invoice Management
                <ChevronDownIcon class="arrow" :class="{ rotate: showInvoiceDropdown }" />
              </div>
              <transition-group name="dropdown" tag="ul" class="dropdown-items" v-show="showInvoiceDropdown">
                <li key="invoice-list">
                  <router-link to="/invoices" :class="{ active: $route.path === '/invoices' }">
                    <ListIcon class="icon" /> Invoice List
                  </router-link>
                </li>
                <li key="invoice-create">
                  <router-link to="/invoices/create" :class="{ active: $route.path === '/invoices/create' }">
                    <PlusIcon class="icon" /> Create Invoice
                  </router-link>
                </li>
                <li key="customers">
                  <router-link to="/customers" :class="{ active: $route.path === '/customers' }">
                    <UsersIcon class="icon" /> Customers
                  </router-link>
                </li>
              </transition-group>
            </li>
          </template>

          <!-- Logout -->
          <li>
            <a href="#" class="logout-link" @click.prevent="logout">
              <LogOutIcon class="icon" /> Logout
            </a>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main content -->
    <main class="main-content">
      <AppNavbar />
      <router-view />
    </main>
  </div>
</template>

<script>
import axios from "axios";
import { useToast } from "vue-toastification";
import {
  Home as HomeIcon,
  Users as UsersIcon,
  UserPlus as UserPlusIcon,
  List as ListIcon,
  Shield as ShieldIcon,
  ChevronDown as ChevronDownIcon,
  LogOut as LogOutIcon,
  FileText as FileTextIcon,
  Plus as PlusIcon,
} from "lucide-vue-next";
import AppNavbar from "@/components/AppNavbar.vue";

export default {
  name: "AdminLayout",
  components: {
    AppNavbar,
    HomeIcon,
    UsersIcon,
    UserPlusIcon,
    ListIcon,
    ShieldIcon,
    ChevronDownIcon,
    LogOutIcon,
    FileTextIcon,
    PlusIcon,
  },
  data() {
    return {
      showUserDropdown: true,
      showInvoiceDropdown: true,
      isDark: localStorage.getItem("theme") === "dark",
    };
  },
  computed: {
    isAdmin() {
      return (localStorage.getItem("role") || "").toLowerCase() === "admin";
    },
  },
  methods: {
    toggleUserDropdown() {
      this.showUserDropdown = !this.showUserDropdown;
    },
    toggleInvoiceDropdown() {
      this.showInvoiceDropdown = !this.showInvoiceDropdown;
    },
    toggleTheme() {
      this.isDark = !this.isDark;
      localStorage.setItem("theme", this.isDark ? "dark" : "light");
    },
    async logout() {
      const token = localStorage.getItem("token");
      const role = (localStorage.getItem("role") || "").toLowerCase();
      const toast = useToast();

      try {
        if (token) {
          await axios.post(
            "http://127.0.0.1:8000/api/logout",
            {},
            { headers: { Authorization: `Bearer ${token}` } }
          );
        }
        toast.success("Logged out successfully!");
      } catch (error) {
        console.error("Logout failed:", error);
        toast.error("Logout failed. Please try again.");
      } finally {
        localStorage.removeItem("token");
        localStorage.removeItem("role");

        if (role === "admin") {
          this.$router.push("/admin/login");
        } else {
          this.$router.push("/login");
        }
      }
    },
  },
};
</script>

<style scoped>
.layout-container {
  display: flex;
  height: 100vh;
  font-family: Arial, sans-serif;
}

/* Sidebar default (light) */
.sidebar {
  width: 250px;
  background-color: #ffffff;
  color: #000000;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar.dark-mode {
  background-color: #222222;
  color: #ffffff;
}

.sidebar-header {
  font-size: 1.5rem;
  font-weight: bold;
  padding: 1.25rem;
  border-bottom: 1px solid #ccc;
  text-align: center;
}

.sidebar.dark-mode .sidebar-header {
  border-bottom: 1px solid #444;
}

.theme-toggle {
  margin-top: 10px;
  font-size: 0.75rem;
  background: none;
  color: inherit;
  border: none;
  cursor: pointer;
}

.sidebar-nav {
  flex-grow: 1;
  padding: 1rem 0;
}

.sidebar-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-nav li {
  margin-bottom: 0.5rem;
}

.sidebar-nav a,
.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: inherit;
  text-decoration: none;
  padding: 0.75rem 1.5rem;
  border-left: 4px solid transparent;
  transition: background-color 0.3s, border-color 0.3s;
  cursor: pointer;
}

.sidebar-nav a:hover,
.dropdown-header:hover {
  background-color: #f0f0f0;
  border-left-color: #4caf50;
}

.sidebar.dark-mode .sidebar-nav a:hover,
.sidebar.dark-mode .dropdown-header:hover {
  background-color: #444;
  border-left-color: #4caf50;
}

.sidebar-nav a.active {
  background-color: #d0f0d0;
  border-left-color: #4caf50;
}

.sidebar.dark-mode .sidebar-nav a.active {
  background-color: #555;
  border-left-color: #4caf50;
}

.icon {
  margin-right: 0.75rem;
  width: 18px;
  height: 18px;
}

.dropdown-header {
  font-weight: bold;
}

.arrow {
  margin-left: auto;
  transition: transform 0.3s ease;
}

.arrow.rotate {
  transform: rotate(180deg);
}

.dropdown-items {
  background-color: #f9f9f9;
  margin-top: 0.25rem;
}

.sidebar.dark-mode .dropdown-items {
  background-color: #2b2b2b;
}

.dropdown-items li a {
  padding-left: 2.5rem;
  font-size: 0.95rem;
}

.logout-link {
  color: #d32f2f;
}

.sidebar.dark-mode .logout-link {
  color: #ff4d4f;
}

.logout-link:hover {
  background-color: #ffebee;
  border-left-color: #d32f2f;
}

.sidebar.dark-mode .logout-link:hover {
  background-color: #440000;
  border-left-color: #ff4d4f;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.main-content {
  flex-grow: 1;
  background-color: #f4f4f4;
  overflow-y: auto;
  padding: 1rem 2rem;
}
</style>
  