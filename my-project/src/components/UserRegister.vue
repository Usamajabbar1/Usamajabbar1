<template>
  <div class="register-container">
    <h2>Add New User</h2>
    <form @submit.prevent="register">
      <input type="email" v-model="email" placeholder="Email" required />
      <input type="text" v-model="name" placeholder="Full Name" required />
      <input type="password" v-model="password" placeholder="Password" required />
      <input type="password" v-model="passwordConfirmation" placeholder="Confirm Password" required />

      <!-- Dynamically populate roles -->
      <select v-model="role" required>
        <option disabled value="">Select Role</option>
        <option v-for="r in roles" :key="r.id" :value="r.name">
          {{ r.name }}
        </option>
      </select>

      <button type="submit">Register</button>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="success">{{ successMessage }}</p>
    </form>
  </div>
</template>

<script>
import api from '../axios';

export default {
  name: 'UserRegister',
  data() {
    return {
      email: '',
      name: '',
      password: '',
      passwordConfirmation: '',
      role: '',
      roles: [],
      errorMessage: '',
      successMessage: '',
    };
  },
  mounted() {
    this.fetchRoles();
  },
  methods: {
    async fetchRoles() {
      try {
        const token = localStorage.getItem('token');
        const res = await api.get('/roles', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
     this.roles = res.data.roles; // 👈 Use `roles`, not `data`

      } catch (error) {
        console.error('Error fetching roles:', error);
        this.errorMessage = 'Failed to load roles. Make sure you are logged in as admin.';
      }
    },
    async register() {
      this.errorMessage = '';
      this.successMessage = '';

      if (this.password !== this.passwordConfirmation) {
        this.errorMessage = 'Passwords do not match.';
        return;
      }

      try {
        const token = localStorage.getItem('token');
        await api.post(
          '/register',
          {
            name: this.name,
            email: this.email,
            password: this.password,
            password_confirmation: this.passwordConfirmation,
            role: this.role,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        this.successMessage = 'User registered successfully.';
        this.email = '';
        this.name = '';
        this.password = '';
        this.passwordConfirmation = '';
        this.role = '';

        sessionStorage.setItem('userListNeedsUpdate', 'true');
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message || 'Registration failed. Please check your details.';
      }
    },
  },
};
</script>

<style scoped>
.register-container {
  max-width: 450px;
  margin: 50px auto;
  padding: 30px;
  background-color: #f9f9f9;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  font-family: 'Arial', sans-serif;
}

h2 {
  text-align: center;
  font-size: 24px;
  color: #4e73df;
  margin-bottom: 20px;
}

input,
select {
  display: block;
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s ease-in-out;
}

input:focus,
select:focus {
  border-color: #4e73df;
  outline: none;
  box-shadow: 0 0 10px rgba(78, 115, 223, 0.2);
}

button {
  padding: 12px;
  background-color: #4e73df;
  color: white;
  border: none;
  border-radius: 8px;
  width: 100%;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #2e59d9;
}

.error {
  color: #e74a3b;
  font-size: 14px;
  margin-top: 10px;
  text-align: center;
}

.success {
  color: #1cc88a;
  font-size: 14px;
  margin-top: 10px;
  text-align: center;
}
</style>
