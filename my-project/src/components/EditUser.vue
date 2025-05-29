<template>
  <div class="edit-user-container">
    <h2>Edit User</h2>
    <form @submit.prevent="updateUser">
      <input type="text" v-model="user.name" placeholder="Name" required />
      <input type="email" v-model="user.email" placeholder="Email" required />

      <select v-model="selectedRole">
        <option disabled value="">Select Role</option>
        <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
      </select>

      <button type="submit">Update User</button>
      <p v-if="message" class="message">{{ message }}</p>
    </form>
  </div>
</template>

<script src="../scripts/EditUser.js"></script>
