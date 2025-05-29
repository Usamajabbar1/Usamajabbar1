// src/composables/useAuth.js
import { reactive } from 'vue'

const state = reactive({
  isLoggedIn: false,
  user: null,
})

export function useAuth() {
  const login = (userData) => {
    state.isLoggedIn = true
    state.user = userData
  }

  const logout = () => {
    state.isLoggedIn = false
    state.user = null
    localStorage.removeItem('token')
    localStorage.removeItem('role')
  }

  return {
    state,
    login,
    logout,
  }
}
