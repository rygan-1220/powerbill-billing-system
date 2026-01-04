const API_BASE_URL = 'http://localhost/power-billing-system/backend/api';

const { createApp } = Vue;
const { createVuetify } = Vuetify;

const vuetify = createVuetify();

createApp({
    data() {
        return {
            username: '',
            password: '',
            showPassword: false,
            loading: false,
            error: '',
            showError: false
        }
    },
    methods: {
        async login() {
            if (!this.username || !this.password) {
                this.error = 'Please enter username and password';
                this.showError = true;
                return;
            }

            this.loading = true;
            this.error = '';
            this.showError = false;

            try {
                const response = await axios.post(`${API_BASE_URL}/auth/login.php`, {
                    username: this.username,
                    password: this.password
                }, {
                    withCredentials: true
                });

                if (response.data.success) {
                    const role = response.data.user.role;
                    
                    // Store user data in localStorage
                    localStorage.setItem('user', JSON.stringify(response.data.user));
                    
                    // Redirect to appropriate dashboard
                    switch(role) {
                        case 'customer':
                            window.location.href = 'dashboard/customer.html';
                            break;
                        case 'admin':
                            window.location.href = 'dashboard/admin.html';
                            break;
                        case 'staff':
                            window.location.href = 'dashboard/staff.html';
                            break;
                        default:
                            this.error = 'Invalid user role';
                            this.showError = true;
                    }
                } else {
                    this.error = response.data.message || 'Login failed';
                    this.showError = true;
                }
            } catch (error) {
                console.error('Login error:', error);
                this.error = error.response?.data?.message || 'An error occurred during login';
                this.showError = true;
            } finally {
                this.loading = false;
            }
        }
    },
    mounted() {
        // Check if user is already logged in
        const user = localStorage.getItem('user');
        if (user) {
            const userData = JSON.parse(user);
            const role = userData.role;
            
            // Redirect to dashboard
            switch(role) {
                case 'customer':
                    window.location.href = 'dashboard/customer.html';
                    break;
                case 'admin':
                    window.location.href = 'dashboard/admin.html';
                    break;
                case 'staff':
                    window.location.href = 'dashboard/staff.html';
                    break;
            }
        }
    }
}).use(vuetify).mount('#app');
