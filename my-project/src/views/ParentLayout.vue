<template>
  <div class="parent-layout">
    <Sidebar />         <!-- Your sidebar component -->
    <main class="main-content">
      <router-view />   <!-- Child route components (like AdminDashboard) render here -->
    </main>
  </div>
</template>

<script>
import Sidebar from '@/components/Sidebar.vue'; // Adjust path if needed

export default {
  name: 'ParentLayout',
  components: {
    Sidebar,
  },
};
</script>

<style scoped>
.parent-layout {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex-grow: 1;
  padding: 20px;
  background: #f9f9f9;
}
</style>
