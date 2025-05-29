// src/axios.js
import axios from 'axios';
import router from './router';
import { useToast } from 'vue-toastification';

const toast = useToast();

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Request Interceptor: Attach token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response Interceptor: Handle Unauthorized
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('role');

      // Optional: Show toast
      toast.error('Session expired. Please log in again.');

      // Redirect to correct login page based on last known role
      const role = localStorage.getItem('role');
      const loginRoute = role === 'admin' ? '/admin/login' : '/login';

      router.push(loginRoute);
    }

    return Promise.reject(error);
  }
);

export default api;
