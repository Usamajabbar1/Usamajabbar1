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

// Request Interceptor: Attach token if present
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

// Response Interceptor: Handle Unauthorized (401)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Get role BEFORE removing
      const role = localStorage.getItem('role');

      localStorage.removeItem('token');
      localStorage.removeItem('role');

      toast.error('Session expired. Please log in again.');

      const loginRoute = role === 'admin' ? '/admin/login' : '/login';
      router.push(loginRoute);
    }

    return Promise.reject(error);
  }
);

export default api;
