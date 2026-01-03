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
                role: '',
                phone: '',
                address: ''
            },
            drawer: true,
            tab: 'dashboard',
            bills: [],
            inquiry: {
                subject: '',
                message: ''
            },
            feedback: {
                rating: 0,
                category: '',
                comments: ''
            },
            payment: {
                method: '',
                card_name: '',
                card_number: '',
                card_expiry: '',
                card_cvv: ''
            },
            selectedBill: {
                bill_id: null,
                bill_month: '',
                total_amount: 0
            },
            inquiries: [],
            paymentDialog: false,
            paymentLoading: false,
            editProfileDialog: false,
            changePasswordDialog: false,
            profileSaving: false,
            passwordSaving: false,
            profileForm: {
                full_name: '',
                email: '',
                phone: '',
                address: ''
            },
            passwordForm: {
                current_password: '',
                new_password: '',
                confirm_password: ''
            },
            inquiryLoading: false,
            feedbackLoading: false,
            snackbar: false,
            snackbarText: '',
            snackbarColor: 'success'
        }
    },
    computed: {
        paidBills() {
            return this.bills.filter(b => b.status === 'paid').length;
        },
        pendingBills() {
            return this.bills.filter(b => b.status === 'pending').length;
        },
        totalDue() {
            return this.bills
                .filter(b => b.status === 'pending')
                .reduce((sum, b) => sum + parseFloat(b.total_amount), 0);
        }
    },
    methods: {
        getStatusColor(status) {
            switch(status) {
                case 'paid': return 'success';
                case 'pending': return 'warning';
                case 'overdue': return 'error';
                default: return 'grey';
            }
        },
        getInquiryStatusColor(status) {
            switch(status) {
                case 'resolved': return 'success';
                case 'open': return 'warning';
                case 'in-progress': return 'info';
                case 'closed': return 'grey';
                default: return 'grey';
            }
        },
        async fetchBills() {
            try {
                console.log('Fetching bills...');
                const response = await axios.get(`${API_BASE_URL}/bills/get.php`, {
                    withCredentials: true
                });
                console.log('Bills response:', response.data);
                
                if (response.data && response.data.success && Array.isArray(response.data.bills)) {
                    this.bills = response.data.bills;
                    console.log('Bills loaded:', this.bills.length);
                } else {
                    console.error('Invalid response format:', response.data);
                    this.bills = [];
                }
            } catch (error) {
                console.error('Error fetching bills:', error);
                console.error('Status:', error.response?.status);
                console.error('Data:', error.response?.data);
                this.bills = [];
                this.showSnackbar('Failed to load bills: ' + (error.response?.data?.message || error.message), 'error');
            }
        },
        openPaymentDialog(bill) {
            this.selectedBill = bill;
            this.paymentDialog = true;
        },
        async makePayment() {
            if (!this.payment.method) {
                this.showSnackbar('Please select a payment method', 'error');
                return;
            }

            // Basic card validations
            if (!this.payment.card_name || !this.payment.card_number || !this.payment.card_expiry || !this.payment.card_cvv) {
                this.showSnackbar('Please fill all card details', 'error');
                return;
            }

            const digits = (this.payment.card_number || '').replace(/\D/g, '');
            if (digits.length < 12) { // very basic check
                this.showSnackbar('Invalid card number', 'error');
                return;
            }
            const last4 = digits.slice(-4);
            const cardId = `CARD-${last4}`;

            this.paymentLoading = true;
            try {
                const response = await axios.post(`${API_BASE_URL}/payments/make.php`, {
                    bill_id: this.selectedBill.bill_id,
                    amount: this.selectedBill.total_amount,
                    payment_method: this.payment.method,
                    card_id: cardId
                }, {
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSnackbar('Payment successful!', 'success');
                    this.paymentDialog = false;
                    this.payment = { method: '', card_name: '', card_number: '', card_expiry: '', card_cvv: '' };
                    this.fetchBills();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                this.showSnackbar('Payment failed', 'error');
            } finally {
                this.paymentLoading = false;
            }
        },
        // Profile
        async fetchProfile() {
            try {
                const res = await axios.get(`${API_BASE_URL}/users/profile.php`, { withCredentials: true });
                if (res.data?.success && res.data.user) {
                    this.user = res.data.user;
                    // keep localStorage in sync
                    localStorage.setItem('user', JSON.stringify(this.user));
                }
            } catch (e) {
                console.error('Profile fetch error', e);
            }
        },
        openEditProfile() {
            this.profileForm = {
                full_name: this.user.full_name || '',
                email: this.user.email || '',
                phone: this.user.phone || '',
                address: this.user.address || ''
            };
            this.editProfileDialog = true;
        },
        async updateProfile() {
            if (!this.profileForm.full_name || !this.profileForm.email) {
                this.showSnackbar('Full name and email are required', 'error');
                return;
            }
            this.profileSaving = true;
            try {
                const res = await axios.put(`${API_BASE_URL}/users/profile.php`, this.profileForm, { withCredentials: true });
                if (res.data?.success) {
                    this.user = res.data.user || this.user;
                    localStorage.setItem('user', JSON.stringify(this.user));
                    this.showSnackbar('Profile updated', 'success');
                    this.editProfileDialog = false;
                } else {
                    this.showSnackbar(res.data?.message || 'Update failed', 'error');
                }
            } catch (e) {
                console.error('Update profile error', e);
                this.showSnackbar('Failed to update profile', 'error');
            } finally {
                this.profileSaving = false;
            }
        },
        openChangePassword() {
            this.passwordForm = { current_password: '', new_password: '', confirm_password: '' };
            this.changePasswordDialog = true;
        },
        async changePassword() {
            if (!this.passwordForm.current_password || !this.passwordForm.new_password || !this.passwordForm.confirm_password) {
                this.showSnackbar('Please fill all password fields', 'error');
                return;
            }
            if (this.passwordForm.new_password !== this.passwordForm.confirm_password) {
                this.showSnackbar('New passwords do not match', 'error');
                return;
            }
            this.passwordSaving = true;
            try {
                const res = await axios.post(`${API_BASE_URL}/auth/change_password.php`, {
                    current_password: this.passwordForm.current_password,
                    new_password: this.passwordForm.new_password
                }, { withCredentials: true });
                if (res.data?.success) {
                    this.showSnackbar('Password updated', 'success');
                    this.changePasswordDialog = false;
                } else {
                    this.showSnackbar(res.data?.message || 'Failed to change password', 'error');
                }
            } catch (e) {
                console.error('Change password error', e);
                this.showSnackbar('Failed to change password', 'error');
            } finally {
                this.passwordSaving = false;
            }
        },
        async submitInquiry() {
            if (!this.inquiry.subject || !this.inquiry.message) {
                this.showSnackbar('Please fill all fields', 'error');
                return;
            }

            this.inquiryLoading = true;
            try {
                const response = await axios.post(`${API_BASE_URL}/inquiries/submit.php`, this.inquiry, {
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSnackbar('Inquiry submitted successfully!', 'success');
                    this.inquiry = { subject: '', message: '' };
                    this.fetchInquiries();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Inquiry error:', error);
                this.showSnackbar('Failed to submit inquiry', 'error');
            } finally {
                this.inquiryLoading = false;
            }
        },
        async fetchInquiries() {
            try {
                const response = await axios.get(`${API_BASE_URL}/inquiries/get_my.php`, {
                    withCredentials: true
                });
                if (response.data && response.data.success) {
                    this.inquiries = response.data.inquiries;
                }
            } catch (error) {
                console.error('Error fetching inquiries:', error);
            }
        },
        async submitFeedback() {
            if (this.feedback.rating === 0) {
                this.showSnackbar('Please provide a rating', 'error');
                return;
            }

            this.feedbackLoading = true;
            try {
                const response = await axios.post(`${API_BASE_URL}/feedback/submit.php`, this.feedback, {
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSnackbar('Feedback submitted successfully!', 'success');
                    this.feedback = { rating: 0, category: '', comments: '' };
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Feedback error:', error);
                this.showSnackbar('Failed to submit feedback', 'error');
            } finally {
                this.feedbackLoading = false;
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
            return date.toLocaleDateString();
        },
        showSnackbar(text, color = 'success') {
            this.snackbarText = text;
            this.snackbarColor = color;
            this.snackbar = true;
        }
    },
    mounted() {
        const userData = localStorage.getItem('user');
        if (!userData) {
            window.location.href = '../index.html';
            return;
        }
        
        try {
            this.user = JSON.parse(userData);
            
            if (!this.user.role || this.user.role !== 'customer') {
                window.location.href = '../index.html';
                return;
            }
            
            this.fetchBills();
            this.fetchInquiries();
            this.fetchProfile();
        } catch (error) {
            console.error('Error loading customer dashboard:', error);
            localStorage.removeItem('user');
            window.location.href = '../index.html';
        }
    }
}).use(vuetify).mount('#app');
