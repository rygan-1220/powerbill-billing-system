# Powerbill Billing System

A full-stack web application for managing electricity billing operations with role-based access control.

## 🎯 Project Overview

This system provides a secure platform for managing electricity billing with three distinct user roles:
- **Customer**: View bills, make payments, submit inquiries and feedback
- **Admin**: Manage users, respond to inquiries
- **Staff**: View/edit customers, generate bills, view all bills, respond to inquiries

## 🛠️ Technology Stack

### Frontend
- **Vue 3** (CDN) - Progressive JavaScript framework
- **Vuetify 3** - Material Design component library
- **Axios** - HTTP client for API communication
- **Material Design Icons** - Icon library

### Backend
- **PHP** - Server-side scripting
- **SQL Server Extensions (sqlsrv)** - Database connectivity

### Database
- **Microsoft SQL Server** - Relational database management

## 📁 Project Structure

```
power-billing-system/
├── backend/
│   ├── api/
│   │   ├── auth/
│   │   │   ├── login.php          # User authentication
│   │   │   └── logout.php         # Session termination
│   │   ├── users/
│   │   │   └── manage.php         # User CRUD operations
│   │   ├── bills/
│   │   │   ├── generate.php       # Create new bills (Admin, Staff)
│   │   │   ├── get.php            # Fetch customer bills
│   │   │   └── view_all.php       # View all bills (Staff)
│   │   ├── inquiries/
│   │   │   ├── submit.php         # Submit inquiry (Customer)
│   │   │   ├── get_my.php         # Get customer's inquiries
│   │   │   ├── get.php            # Get all inquiries (Admin, Staff)
│   │   │   └── respond.php        # Respond to inquiries (Admin, Staff)
│   │   ├── payments/
│   │   │   └── make.php           # Process payments
│   │   ├── feedback/
│   │   │   └── submit.php         # Submit feedback
│   │   └── customers/
│   │       └── get.php            # Fetch customer list (Staff)
│   ├── config/
│   │   └── database.php           # Database configuration
│   └── utils/
│       └── session.php            # Session management utilities
├── database/
│   └── schema.sql                 # Database schema and sample data
└── frontend/
    ├── index.html                 # Login page
    ├── dashboard/
    │   ├── customer.html          # Customer dashboard
    │   ├── admin.html             # Admin dashboard
    │   └── staff.html             # Staff dashboard
    ├── css/
    │   └── style.css              # Global styles
    └── js/
        ├── app.js                 # Login logic
        ├── customer.js            # Customer dashboard logic
        ├── admin.js               # Admin dashboard logic
        └── staff.js               # Staff dashboard logic
```

## 🚀 Setup Instructions

### ⚡ Quick Start (5 Minutes)

#### 1. Install Prerequisites
- ✅ WAMP (https://wampserver.aviatechno.net/)
- ✅ SQL Server 2019+ (https://www.microsoft.com/en-us/sql-server/sql-server-downloads)
- ✅ SQL Server Management Studio (SSMS)

#### 2. Install PHP SQL Server Extensions

**For PHP 8.3:**
```
1. Download: https://aka.ms/downloadsqlsrv (PHP 8.3 Thread Safe version)
2. Extract to: C:\wamp64\bin\php\php8.3.28\ext
3. Add to php.ini:
   extension=php_sqlsrv_83_ts.dll
   extension=php_pdo_sqlsrv_83_ts.dll
4. Restart Apache
```

**Verify Installation:**
```
http://localhost/power-billing-system/backend/test
```

#### 3. Setup Database

**Using SSMS:**
```
1. Open SQL Server Management Studio
2. Connect to: localhost (or localhost\SQLEXPRESS)
3. Open File: database/schema.sql
4. Click Execute (F5)
5. Create login [web / Web#123]
6. Done! ✓
```

#### 4. Configure Connection

**Edit:** `backend/config/database.php`

```php
$serverName = "localhost";        
$database = "ElectricityBilling";
$username = "web";                 
$password = "Web#123";             
```

#### 5. Start Application

**Open Browser:**
```
http://localhost/power-billing-system/frontend/index.html
```

## 🔑 Demo Accounts

Use these credentials to test different roles:

| Role | Username | Password |
|------|----------|----------|
| **Admin** | admin | admin123 |
| **Customer** | customer1 | customer123 |
| **Staff** | staff1 | staff123 |

## 🔐 Security Features

- **Session-based Authentication**: Simple PHP sessions for user authentication
- **Role-based Access Control**: Endpoints protected by role verification
- **Input Validation**: Basic validation on all user inputs
- **CORS Headers**: Configured for secure cross-origin requests

## 📊 Database Schema

### Users Table
- Stores all user accounts with roles
- Fields: user_id, username, password, email, full_name, role, phone, address, status

### Bills Table
- Contains electricity billing records
- Fields: bill_id, user_id, bill_month, readings, units_consumed, amount, status

### Payments Table
- Tracks payment transactions
- Fields: payment_id, bill_id, amount, payment_method, transaction_id, status

### Inquiries Table
- Customer support inquiries
- Fields: inquiry_id, user_id, subject, message, status, response

### Feedback Table
- Customer feedback and ratings
- Fields: feedback_id, user_id, rating, comments, category

## 🎨 Features by Role

### Customer Dashboard
- ✅ View all bills with status
- ✅ Make payments for pending bills
- ✅ Submit inquiries
- ✅ View inquiries with admin responses
- ✅ Submit feedback with ratings
- ✅ View statistics (total bills, paid, pending, amount due)

### Admin Dashboard
- ✅ User Management (Create, Read, Update, Delete)
- ✅ Generate bills for customers
- ✅ View and respond to customer inquiries
- ✅ Full system oversight

### Staff Dashboard
- ✅ View all customers
- ✅ View all bills with customer details
- ✅ Search functionality for customers and bills

## 🔧 API Endpoints

### Authentication
- `POST /api/auth/login.php` - User login
- `POST /api/auth/logout.php` - User logout

### Customer
- `GET /api/customer/get_bills.php` - Get customer bills
- `POST /api/customer/make_payment.php` - Process payment
- `POST /api/customer/submit_inquiry.php` - Submit inquiry
- `GET /api/customer/get_inquiries.php` - Get customer's inquiries with responses
- `POST /api/customer/submit_feedback.php` - Submit feedback

### Admin
- `GET /api/admin/manage_users.php` - List all users
- `POST /api/admin/manage_users.php` - Create user
- `PUT /api/admin/manage_users.php` - Update user
- `DELETE /api/admin/manage_users.php` - Delete user
- `POST /api/admin/generate_bill.php` - Generate bill
- `GET /api/admin/get_inquiries.php` - Get all inquiries
- `POST /api/admin/respond_inquiry.php` - Respond to inquiry

### Staff
- `GET /api/staff/get_customers.php` - Get customer list
- `GET /api/staff/view_bills.php` - View all bills


## 🎓 Learning Objectives

This project demonstrates:
- ✅ Full-stack web development
- ✅ RESTful API design
- ✅ Role-based access control
- ✅ Database design and normalization
- ✅ Modern frontend frameworks (Vue 3)
- ✅ Session management
- ✅ CRUD operations
- ✅ Responsive UI design

## 📚 Resources

- [Vue 3 Documentation](https://vuejs.org/)
- [Vuetify Documentation](https://vuetifyjs.com/)
- [PHP sqlsrv Documentation](https://www.php.net/manual/en/book.sqlsrv.php)
- [SQL Server Documentation](https://docs.microsoft.com/en-us/sql/)

## 📄 License

This project is created for educational purposes.
