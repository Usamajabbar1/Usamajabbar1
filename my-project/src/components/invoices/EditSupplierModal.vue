<template>
  <div v-if="visible" class="modal-overlay">
    <div class="modal-content">
      <h3>Edit Supplier</h3>

      <form @submit.prevent="submit">
        <label>
          Name:
          <input v-model="localSupplier.name" required />
        </label>

        <label>
          Email:
          <input v-model="localSupplier.email" type="email" />
        </label>

        <label>
          Phone:
          <input v-model="localSupplier.phone" />
        </label>

        <label>
          Address:
          <textarea v-model="localSupplier.address"></textarea>
        </label>

        <div class="modal-actions">
          <button type="submit">Save</button>
          <button type="button" @click="$emit('close')">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditSupplierModal',
  props: {
    visible: Boolean,
    supplier: Object,
  },
  data() {
    return {
      localSupplier: { ...this.supplier },
    };
  },
  watch: {
    supplier(newVal) {
      this.localSupplier = { ...newVal };
    },
  },
  methods: {
    submit() {
      this.$emit('submit', this.localSupplier);
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 400px;
  max-width: 90%;
}

.modal-actions {
  margin-top: 1rem;
  display: flex;
  justify-content: space-between;
}
</style>
