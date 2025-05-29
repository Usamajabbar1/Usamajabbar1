import api from '../axios';

export default {
  name: 'EditUser',
  data() {
    return {
      user: {
        name: '',
        email: ''
      },
      roles: [],
      selectedRole: '',
      message: ''
    };
  },
  methods: {
    async fetchUser() {
      const userId = this.$route.params.id;
      try {
        const response = await api.get(`/users/get/${userId}`);
        this.user = response.data.user;
        this.selectedRole = response.data.user.roles[0]?.name || '';
      } catch (error) {
        console.error('Failed to fetch user:', error);
      }
    },
    async fetchRoles() {
      try {
        const response = await api.get('/roles');
        this.roles = response.data.roles;
      } catch (error) {
        console.error('Failed to fetch roles:', error);
      }
    },
    async updateUser() {
      const userId = this.$route.params.id;
      try {
        await api.put(`/users/update/${userId}`, {
          name: this.user.name,
          email: this.user.email,
        });

        await api.post(`/users/${userId}/assign-role`, {
          role: this.selectedRole
        });

        sessionStorage.setItem('successMessage', 'User updated successfully.');
        sessionStorage.setItem('userListNeedsUpdate', 'true');

        setTimeout(() => {
          this.$router.push('/admin-dashboard');
        }, 200);
      } catch (error) {
        console.error(error);
        this.message = 'Failed to update user.';
      }
    }
  },
  async created() {
    await this.fetchUser();
    await this.fetchRoles();
  }
};
