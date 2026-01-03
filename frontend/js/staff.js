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
            tab: 'customers',
            customers: [],
            bills: [],
            inquiries: [],
            search: '',
            billSearch: '',
            customerDialog: false,
            customerForm: {
                user_id: '',
                full_name: '',
                email: '',
                phone: '',
                address: '',
                status: 'active'
            },
            billForm: {
                customer_id: '',
                bill_month: this.getLastMonth(),
                previous_reading: '0',
                current_reading: '',
                rate_per_unit: '5.50',
                due_date: this.getNextMonthDate()
            },
            inquiryResponse: {},
            customerLoading: false,
            billLoading: false,
            inquiryLoading: false,
            snackbar: false,
            snackbarText: '',
            snackbarColor: 'success'
        }
    },
    computed: {
        filteredCustomers() {
            if (!this.search || !Array.isArray(this.customers)) return this.customers || [];
            
            const searchLower = this.search.toLowerCase();
            return this.customers.filter(c => {
                if (!c || !c.username) return false;
                return c.username.toLowerCase().includes(searchLower) ||
                    (c.full_name && c.full_name.toLowerCase().includes(searchLower)) ||
                    (c.email && c.email.toLowerCase().includes(searchLower)) ||
                    (c.phone && c.phone.includes(searchLower));
            });
        },
        filteredBills() {
            if (!this.billSearch || !Array.isArray(this.bills)) return this.bills || [];
            
            const searchLower = this.billSearch.toLowerCase();
            return this.bills.filter(b => {
                if (!b) return false;
                return (b.bill_id && b.bill_id.toString().includes(searchLower)) ||
                    (b.full_name && b.full_name.toLowerCase().includes(searchLower)) ||
                    (b.email && b.email.toLowerCase().includes(searchLower)) ||
                    (b.bill_month && b.bill_month.includes(searchLower));
            });
        }
    },
    watch: {
        'billForm.customer_id': async function(newCustomerId) {
            if (newCustomerId) {
                await this.fetchLatestBillForCustomer(newCustomerId);
            }
        }
    },
    methods: {
        getLastMonth() {
            const date = new Date();
            date.setMonth(date.getMonth() - 1);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            return `${year}-${month}`;
        },
        getNextMonthDate() {
            const date = new Date();
            date.setMonth(date.getMonth() + 1);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        async fetchLatestBillForCustomer(customerId) {
            try {
                const response = await axios.get(`${API_BASE_URL}/bills/get.php?customer_id=${customerId}`, {
                    withCredentials: true
                });
                
                if (response.data && response.data.success && response.data.bills && response.data.bills.length > 0) {
                    // Get the latest bill 
                    const latestBill = response.data.bills[0];
                    // Set previous_reading to the current_reading of the latest bill
                    this.billForm.previous_reading = latestBill.current_reading || '';
                } else {
                    // No previous bills, reset to empty
                    this.billForm.previous_reading = '0';
                }
            } catch (error) {
                console.error('Error fetching latest bill:', error);
                // Don't show error to user, just keep previous_reading empty
                this.billForm.previous_reading = '';
            }
        },
        getInquiryStatusColor(status) {
            switch(status) {
                case 'open': return 'info';
                case 'resolved': return 'success';
                case 'closed': return 'grey';
                default: return 'grey';
            }
        },
        getStatusColor(status) {
            switch(status) {
                case 'paid': return 'success';
                case 'pending': return 'warning';
                case 'overdue': return 'error';
                default: return 'grey';
            }
        },
        async fetchCustomers() {
            try {
                console.log('Fetching customers...');
                const response = await axios.get(`${API_BASE_URL}/customers/get.php`, {
                    withCredentials: true
                });
                console.log('Customers response:', response.data);
                
                if (response.data && response.data.success && Array.isArray(response.data.customers)) {
                    this.customers = response.data.customers;
                    console.log('Customers loaded:', this.customers.length);
                } else {
                    console.error('Invalid response format:', response.data);
                    this.customers = [];
                    this.showSnackbar('No customers found', 'info');
                }
            } catch (error) {
                console.error('Error fetching customers:', error);
                console.error('Status:', error.response?.status);
                console.error('Data:', error.response?.data);
                this.customers = [];
                this.showSnackbar('Failed to load customers: ' + (error.response?.data?.message || error.message), 'error');
            }
        },
        async fetchBills() {
            try {
                console.log('Fetching bills...');
                const response = await axios.get(`${API_BASE_URL}/bills/view_all.php`, {
                    withCredentials: true
                });
                console.log('Bills response:', response.data);
                
                if (response.data && response.data.success && Array.isArray(response.data.bills)) {
                    this.bills = response.data.bills;
                    console.log('Bills loaded:', this.bills.length);
                } else {
                    console.error('Invalid response format:', response.data);
                    this.bills = [];
                    this.showSnackbar('No bills found', 'info');
                }
            } catch (error) {
                console.error('Error fetching bills:', error);
                console.error('Status:', error.response?.status);
                console.error('Data:', error.response?.data);
                this.bills = [];
                this.showSnackbar('Failed to load bills: ' + (error.response?.data?.message || error.message), 'error');
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
        openCustomerDialog(customer) {
            this.customerForm = {
                user_id: customer.user_id,
                full_name: customer.full_name,
                email: customer.email,
                phone: customer.phone,
                address: customer.address,
                status: customer.status
            };
            this.customerDialog = true;
        },
        async saveCustomer() {
            this.customerLoading = true;
            try {
                const response = await axios.put(`${API_BASE_URL}/users/manage.php`, this.customerForm, {
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSnackbar('Customer updated successfully!', 'success');
                    this.customerDialog = false;
                    this.fetchCustomers();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Save customer error:', error);
                this.showSnackbar('Failed to update customer', 'error');
            } finally {
                this.customerLoading = false;
            }
        },
        async generateBill() {
            this.billLoading = true;
            try {
                const response = await axios.post(`${API_BASE_URL}/bills/generate.php`, this.billForm, {
                    withCredentials: true
                });

                if (response.data.success) {
                    this.showSnackbar('Bill generated successfully!', 'success');
                    this.billForm = {
                        customer_id: '',
                        bill_month: this.getLastMonth(),
                        previous_reading: '',
                        current_reading: '',
                        rate_per_unit: '5.50',
                        due_date: this.getNextMonthDate()
                    };
                    this.fetchBills();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                console.error('Generate bill error:', error);
                this.showSnackbar('Failed to generate bill', 'error');
            } finally {
                this.billLoading = false;
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
        const userData = localStorage.getItem('user');
        if (!userData) {
            window.location.href = '../index.html';
            return;
        }
        
        try {
            this.user = JSON.parse(userData);
            
            if (!this.user.role || this.user.role !== 'staff') {
                window.location.href = '../index.html';
                return;
            }
            
            this.fetchCustomers();
            this.fetchBills();
            this.fetchInquiries();
        } catch (error) {
            console.error('Error loading staff dashboard:', error);
            localStorage.removeItem('user');
            window.location.href = '../index.html';
        }
    }
}).use(vuetify).mount('#app');
