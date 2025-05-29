<template>
  <div v-if="visible" class="modal-overlay">
    <div class="modal-content">
      <h3>Edit User</h3>
      <form @submit.prevent="submit">
        <label>Name:</label>
        <input v-model="formData.name" required />

        <label>Email:</label>
        <input v-model="formData.email" type="email" required />

        <label>Role:</label>
   <select v-model="formData.role" required>
  <option v-for="role in roles" :key="role.id" :value="role.name">
    {{ role.name }}
  </option>
</select>



        <div class="modal-actions">
          <button type="submit">Update</button>
          <button type="button" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    visible: Boolean,
    user: Object,
    roles: Array,
  },
  data() {
    return {
      formData: { id: null, name: '', email: '', role: '' },
    };
  },
 watch: {
  user: {
    immediate: true,
    handler(val) {
      if (val) {
        this.formData = {
          id: val.id,
          name: val.name,
          email: val.email,
          // Support both `role` and `roles[0].name`
          role: val.role || val.roles?.[0]?.name || '',
        };
      }
    },
  },
},

  methods: {
    submit() {
      console.log('Submitting formData:', this.formData);
      this.$emit('submit', this.formData);
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-content {
  background: white;
  padding: 24px;
  border-radius: 10px;
  width: 400px;
}
.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}
input,
select {
  width: 100%;
  margin-bottom: 10px;
  padding: 8px;
}
</style>
