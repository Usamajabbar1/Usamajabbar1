import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api', // your Laravel API base URL
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Set the Authorization token dynamically for each request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token'); // Retrieve token from localStorage
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`; // Attach token to header
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;
