import { createRouter, createWebHistory } from 'vue-router';

// Public components
import UserLogin from '@/components/UserLogin.vue';
import AdminLogin from '@/components/AdminLogin.vue';
import UserRegister from '@/components/UserRegister.vue';

// Admin layout and dashboard
import AdminLayout from '@/layouts/AdminLayout.vue';
import Home from '@/views/Home.vue';

// Lazy-loaded admin feature components
const UserListPage = () => import('@/components/UserListPage.vue');
const EditUser = () => import('@/components/EditUser.vue');
const RoleManager = () => import('@/components/RoleManager.vue');
const InvoiceManager = () => import('@/components/invoices/InvoiceManager.vue');
const InvoiceForm = () => import('@/components/invoices/InvoiceForm.vue');
const InvoiceDetail = () => import('@/components/invoices/InvoiceDetail.vue');
const CustomerManager = () => import('@/components/invoices/CustomerManager.vue');

const routes = [
  // Public routes
  { path: '/login', name: 'UserLogin', component: UserLogin },
  { path: '/admin/login', name: 'AdminLogin', component: AdminLogin },
  { path: '/register', name: 'UserRegister', component: UserRegister },

  // Redirect root to admin login
  { path: '/', redirect: '/admin/login' },

  // Protected routes
  {
    path: '/',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'Dashboard', component: Home },

      // Role Management
      {
        path: 'roles',
        name: 'RoleManager',
        component: RoleManager,
        meta: { role: ['admin'] },
      },

      {
  path: '/company-profile',
  name: 'CompanyProfile',
  component: () => import('@/components/CompanyProfileForm.vue'),
  meta: { requiresAuth: true }
},


      // User Management
      {
        path: 'users/register',
        name: 'RegisterUser',
        component: UserRegister,
        meta: { role: ['admin'] },
      },
      {
        path: 'users/list',
        name: 'UserList',
        component: UserListPage,
        meta: { role: ['admin'] },
      },
      {
        path: 'users/edit/:id',
        name: 'EditUser',
        component: EditUser,
        meta: { role: ['admin'] },
      },

      // Invoice Management
      {
        path: 'invoices',
        name: 'InvoiceManager',
        component: InvoiceManager,
        meta: { role: ['admin'] },
      },
      {
        path: 'invoices/create',
        name: 'CreateInvoice',
        component: InvoiceForm,
        meta: { role: ['admin'] },
      },
      {
        path: 'invoices/:id',
        name: 'InvoiceDetail',
        component: InvoiceDetail,
        meta: { role: ['admin'] },
      },
      {
        path: 'invoices/:id/edit',
        name: 'EditInvoice',
        component: InvoiceForm,
        meta: { role: ['admin'] },
      },

      // Customer Management
      {
        path: 'customers',
        name: 'CustomerManager',
        component: CustomerManager,
        meta: { role: ['admin'] },
      },
    ],
  },

  // Catch-all fallback
  { path: '/:pathMatch(.*)*', redirect: '/admin/login' },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// 🔐 Global navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const role = (localStorage.getItem('role') || '').toLowerCase();
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  // Prevent access to login pages if already authenticated
  if ((to.path === '/login' || to.path === '/admin/login') && token) {
    return next('/dashboard');
  }

  // Redirect unauthenticated users
  if (requiresAuth && !token) {
    return to.fullPath.startsWith('/admin') ? next('/admin/login') : next('/login');
  }

  // Role-based access control
  const allowedRoles = to.matched.map(record => record.meta.role).filter(Boolean).flat();
  if (allowedRoles.length && !allowedRoles.includes(role)) {
    return next('/dashboard');
  }

  next(); // Allow navigation
});

export default router;
