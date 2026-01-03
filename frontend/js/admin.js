const API_BASE_URL = 'http://localhost/powerbill-billing-system/backend/api';

const { createApp } = Vue;
const { createVuetify } = Vuetify;

const vuetify = createVuetify();

createApp({
    data() {
        return {
            user: {
                username: '',
                full_name: '',
                email: '',
                role: ''
            },
            drawer: true,
            tab: 'users',
            users: [],
            inquiries: [],
            userDialog: false,
            editingUser: null,
            userForm: {
                username: '',
                password: '',
                full_name: '',
                email: '',
                role: '',
                phone: '',
                address: '',
                status: 'active'
            },
            inquiryResponse: {},
            userLoading: false,
            inquiryLoading: false,
            snackbar: false,
            snackbarText: '',
            snackbarColor: 'success'
        }
    },
    methods: {
        getInquiryStatusColor(status) {
            switch(status) {
                case 'open': return 'info';
                case 'resolved': return 'success';
                case 'closed': return 'grey';
                default: return 'grey';
            }
        },
        async fetchUsers() {
            try {
                console.log('Fetching users...');
                const response = await axios.get(`${API_BASE_URL}/users/manage.php`, {
                    withCredentials: true
                });
                console.log('Users response:', response.data);
                
                if (response.data && response.data.success && Array.isArray(response.data.users)) {
                    this.users = response.data.users;
                    console.log('Users loaded:', this.users.length);
                } else {
                    console.error('Invalid response format:', response.data);
                    this.users = [];
                }
            } catch (error) {
                console.error('Error fetching users:', error);
                console.error('Status:', error.response?.status);
                console.error('Data:', error.response?.data);
                this.users = [];
                this.showSnackbar('Failed to load users: ' + (error.response?.data?.message || error.message), 'error');
            }
        },
        async fetchInquiries() {
            try {
                console.log('Fetching inquiries...');
                const response = await axios.get(`${API_BASE_URL}/inquiries/get.php`, {
                    withCredentials: true
                });
                console.log('Inquiries response:', response.data);
                
                if (response.data && response.data.success && Array.isArray(response.data.inquiries)) {
                    this.inquiries = response.data.inquiries;
                    console.log('Inquiries loaded:', this.inquiries.length);
                } else {
                    console.error('Invalid response format:', response.data);
                    this.inquiries = [];
                }
            } catch (error) {
                console.error('Error fetching inquiries:', error);
                console.error('Status:', error.response?.status);
                console.error('Data:', error.response?.data);
                this.inquiries = [];
                this.showSnackbar('Failed to load inquiries: ' + (error.response?.data?.message || error.message), 'error');
            }
        },
        openUserDialog(user = null) {
            this.editingUser = user;
            if (user) {
                this.userForm = {
                    user_id: user.user_id,
                    username: user.username,
                    full_name: user.full_name,
                    email: user.email,
                    role: user.role,
                    phone: user.phone,
                    address: user.address,
                    status: user.status
                };
            } else {
                this.userForm = {
                    username: '',
                    password: '',
                    full_name: '',
                    email: '',
                    role: '',
                    phone: '',
                    address: '',
                    status: 'active'
                };
            }
            this.userDialog = true;
        },
        async saveUser() {
            this.userLoading = true;
            try {
                let response;
                if (this.editingUser) {
                    response = await axios.put(`${API_BASE_URL}/users/manage.php`, this.userForm, {
                        withCredentials: true
                    });
                } else {
                    response = await axios.post(`${API_BASE_URL}/users/manage.php`, this.userForm, {
                        withCredentials: true
                    });
                }

                if (response.data.success) {
                    this.showSnackbar(response.data.message, 'success');
                    this.userDialog = false;
                    this.fetchUsers();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Save user error:', error);
                this.showSnackbar('Failed to save user', 'error');
            } finally {
                this.userLoading = false;
            }
        },
        async deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }

            try {
                const response = await axios.delete(`${API_BASE_URL}/users/manage.php`, {
                    data: { user_id: userId },
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSnackbar('User deleted successfully', 'success');
                    this.fetchUsers();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Delete user error:', error);
                this.showSnackbar('Failed to delete user', 'error');
            }
        },
        async respondInquiry(inquiryId) {
            const response = this.inquiryResponse[inquiryId];
            if (!response) {
                this.showSnackbar('Please enter a response', 'error');
                return;
            }

            this.inquiryLoading = true;
            try {
                const apiResponse = await axios.post(`${API_BASE_URL}/inquiries/respond.php`, {
                    inquiry_id: inquiryId,
                    response: response
                }, {
                    withCredentials: true
                });

                if (apiResponse.data.success) {
                    this.showSnackbar('Response sent successfully!', 'success');
                    this.inquiryResponse[inquiryId] = '';
                    this.fetchInquiries();
                } else {
                    this.showSnackbar(apiResponse.data.message, 'error');
                }
            } catch (error) {
                console.error('Respond inquiry error:', error);
                this.showSnackbar('Failed to send response', 'error');
            } finally {
                this.inquiryLoading = false;
            }
        },
        async logout() {
            try {
                await axios.post(`${API_BASE_URL}/auth/logout.php`, {}, {
                    withCredentials: true
                });
            } catch (error) {
                console.error('Logout error:', error);
            }
            
            localStorage.removeItem('user');
            window.location.href = '../index.html';
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleString();
        },
        showSnackbar(text, color = 'success') {
            this.snackbarText = text;
            this.snackbarColor = color;
            this.snackbar = true;
        }
    },
    mounted() {
        console.log('Admin dashboard mounted');
        const userData = localStorage.getItem('user');
        console.log('User data from localStorage:', userData);
        
        if (!userData) {
            console.log('No user data, redirecting to login');
            window.location.href = '../index.html';
            return;
        }
        
        try {
            this.user = JSON.parse(userData);
            console.log('Parsed user:', this.user);
            
            if (!this.user.role || this.user.role !== 'admin') {
                console.log('User is not admin, redirecting');
                window.location.href = '../index.html';
                return;
            }
            
            console.log('User is admin, fetching data...');
            this.fetchUsers();
            this.fetchInquiries();
        } catch (error) {
            console.error('Error in mounted:', error);
            localStorage.removeItem('user');
            window.location.href = '../index.html';
        }
    }
}).use(vuetify).mount('#app');
