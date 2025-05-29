<template>
  <div class="login-container">
    <h2>Admin Login</h2>
    <form @submit.prevent="login">
      <input type="email" v-model="email" placeholder="Email" required />
      <input type="password" v-model="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <div v-if="errorMessage" class="error">{{ errorMessage }}</div>
    </form>
  </div>
</template>

<script>
import api from '../axios';
import { useAuth } from '@/composables/useAuth'; // 🔥 import

export default {
  name: 'AdminLogin',
  data() {
    return {
      email: '',
      password: '',
      errorMessage: '',
    };
  },
  methods: {
    async login() {
      const auth = useAuth(); // 🔥 use inside method
      try {
        const response = await api.post('/admin/login', {
          email: this.email,
          password: this.password,
        });

        const token = response.data.token;
        localStorage.setItem('token', token);
        localStorage.setItem('role', 'admin');

        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // 🔥 Set global auth user info
        auth.login({
          name: response.data.name,
          email: response.data.email,
          role: 'admin',
          profile_photo_url: response.data.profile_photo_url || '/default-user.png',
        });

        this.$router.push('/dashboard');
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Login failed.';
      }
    },
  },
};
</script>

<style scoped>
/* ... your styles remain unchanged ... */
</style>


<style scoped>
.login-container {
  max-width: 400px;
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

input {
  display: block;
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s ease-in-out;
}

input:focus {
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
</style>
