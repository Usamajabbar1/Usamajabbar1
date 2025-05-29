// src/main.js
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import api from './axios';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

// Restore token on page load
const token = localStorage.getItem('token');
if (token) {
  api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp(App);

app.use(router);
app.use(Toast);
app.mount('#app');
