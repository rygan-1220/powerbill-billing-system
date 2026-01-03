const { createApp } = Vue;
const { createVuetify } = Vuetify;

const vuetify = createVuetify();

const app = createApp({
    data() {
        return {
            formValid: false,
            loading: false,
            error: '',
            success: '',
            showError: false,
            showSuccess: false,
            showPassword: false,
            showConfirmPassword: false,
            formData: {
                username: '',
                full_name: '',
                password: '',
                confirmPassword: '',
                agreeToPolicy: false,
                role: 'customer'  // Always customer for registration
            },
            usernameRules: [
                v => !!v || 'Username is required',
                v => (v && v.length >= 3) || 'Username must be at least 3 characters',
                v => (v && v.length <= 50) || 'Username must be less than 50 characters',
                v => /^[a-zA-Z0-9_]+$/.test(v) || 'Username can only contain letters, numbers, and underscores'
            ],
            requiredRules: [
                v => !!v || 'This field is required'
            ],
            passwordRules: [
                v => !!v || 'Password is required',
                v => (v && v.length >= 6) || 'Password must be at least 6 characters'
            ],
            confirmPasswordRules: [
                v => !!v || 'Please confirm your password',
                v => v === this.formData.password || 'Passwords do not match'
            ],
            policyRules: [
                v => !!v || 'You must agree to the policy to register'
            ]
        };
    },
    methods: {
        async register() {
            // Validate form
            const { valid } = await this.$refs.form.validate();
            if (!valid) {
                this.error = 'Please fill in all required fields correctly';
                this.showError = true;
                return;
            }

            // Check if passwords match
            if (this.formData.password !== this.formData.confirmPassword) {
                this.error = 'Passwords do not match';
                this.showError = true;
                return;
            }

            // Check if policy is agreed
            if (!this.formData.agreeToPolicy) {
                this.error = 'You must agree to the Personal Data Protection Policy';
                this.showError = true;
                return;
            }

            this.loading = true;
            this.error = '';
            this.success = '';
            this.showError = false;
            this.showSuccess = false;

            try {
                // Prepare data for API 
                const registrationData = {
                    username: this.formData.username,
                    full_name: this.formData.full_name,
                    password: this.formData.password,
                    role: 'customer'
                };

                const response = await axios.post('../backend/api/auth/register.php', registrationData);

                if (response.data.success) {
                    this.success = 'Registration successful! Redirecting to login page...';
                    this.showSuccess = true;
                    
                    // Reset form
                    this.formData = {
                        username: '',
                        full_name: '',
                        password: '',
                        confirmPassword: '',
                        agreeToPolicy: false,
                        role: 'customer'
                    };
                    this.$refs.form.reset();

                    // Redirect to login page after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 2000);
                } else {
                    this.error = response.data.message || 'Registration failed';
                    this.showError = true;
                }
            } catch (error) {
                console.error('Registration error:', error);
                if (error.response && error.response.data && error.response.data.message) {
                    this.error = error.response.data.message;
                } else {
                    this.error = 'An error occurred during registration. Please try again.';
                }
                this.showError = true;
            } finally {
                this.loading = false;
            }
        },
        goToLogin() {
            window.location.href = 'index.html';
        }
    },
    mounted() {
        // Set page background color
        document.body.style.backgroundColor = '#f5f5f5';
    }
});

app.use(vuetify).mount('#app');
