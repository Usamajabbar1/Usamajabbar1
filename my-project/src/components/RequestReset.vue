<template>
    <div class="request-reset">
      <h2>Forgot Password</h2>
      <form @submit.prevent="requestReset">
        <input
          v-model="email"
          type="email"
          placeholder="Enter your email"
          required
        />
        <button :disabled="loading">
          {{ loading ? "Sending..." : "Send Reset Link" }}
        </button>
      </form>
  
      <p v-if="success" class="success">{{ success }}</p>
      <p v-if="error" class="error">{{ error }}</p>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        email: "",
        loading: false,
        success: "",
        error: "",
      };
    },
    methods: {
      async requestReset() {
        this.loading = true;
        this.success = "";
        this.error = "";
  
        try {
          const res = await fetch("http://127.0.0.1:8000/api/password/email", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",  // ensures JSON response from Laravel
            },
            body: JSON.stringify({ email: this.email }),
          });
  
          const data = await res.json();
  
          if (res.ok) {
            // Laravel sends success or error with message
            this.success = data.message || "Reset link sent! Check your email.";
            this.email = "";
          } else {
            // For validation or error messages
            this.error = data.message || "Failed to send reset link.";
          }
        } catch (err) {
          this.error = "An error occurred. Try again.";
          console.error(err);
        } finally {
          this.loading = false;
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .success {
    color: green;
    margin-top: 10px;
  }
  .error {
    color: red;
    margin-top: 10px;
  }
  input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 16px;
  }
  button {
    width: 100%;
    padding: 12px;
    background-color: #4e73df;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
  }
  button:disabled {
    background-color: #a3b1f7;
    cursor: not-allowed;
  }
  button:hover:not(:disabled) {
    background-color: #2e59d9;
  }
  </style>
  