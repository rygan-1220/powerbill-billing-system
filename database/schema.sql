-- Electricity Billing System Database Schema
-- Microsoft SQL Server

USE master;
GO

-- Create Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'ElectricityBilling')
BEGIN
    CREATE DATABASE ElectricityBilling;
END
GO

USE ElectricityBilling;
GO

-- Users Table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='users' AND xtype='U')
BEGIN
    CREATE TABLE users (
        user_id INT IDENTITY(1,1) PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(20) NOT NULL CHECK (role IN ('customer', 'admin', 'staff')),
        phone VARCHAR(20),
        address VARCHAR(255),
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE(),
        status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive'))
    );
END
GO

-- Bills Table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='bills' AND xtype='U')
BEGIN
    CREATE TABLE bills (
        bill_id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        bill_month VARCHAR(7) NOT NULL, -- Format: YYYY-MM
        previous_reading DECIMAL(10,2) NOT NULL,
        current_reading DECIMAL(10,2) NOT NULL,
        units_consumed DECIMAL(10,2) NOT NULL,
        rate_per_unit DECIMAL(10,2) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        due_date DATE NOT NULL,
        status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'overdue')),
        created_by INT,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (created_by) REFERENCES users(user_id)
    );
END
GO

-- Payments Table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='payments' AND xtype='U')
BEGIN
    CREATE TABLE payments (
        payment_id INT IDENTITY(1,1) PRIMARY KEY,
        bill_id INT NOT NULL,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100),
        card_id VARCHAR(100),
        payment_date DATETIME DEFAULT GETDATE(),
        status VARCHAR(20) DEFAULT 'completed' CHECK (status IN ('pending', 'completed', 'failed')),
        FOREIGN KEY (bill_id) REFERENCES bills(bill_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );
END
GO


-- Inquiries Table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='inquiries' AND xtype='U')
BEGIN
    CREATE TABLE inquiries (
        inquiry_id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'open' CHECK (status IN ('open', 'in-progress', 'resolved', 'closed')),
        response TEXT,
        responded_by INT,
        created_at DATETIME DEFAULT GETDATE(),
        responded_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (responded_by) REFERENCES users(user_id)
    );
END
GO

-- Feedback Table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='feedback' AND xtype='U')
BEGIN
    CREATE TABLE feedback (
        feedback_id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        rating INT CHECK (rating BETWEEN 1 AND 5),
        comments TEXT,
        category VARCHAR(50),
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    );
END
GO

-- Insert Default Admin User (password: admin123)
IF NOT EXISTS (SELECT * FROM users WHERE username = 'admin')
BEGIN
    INSERT INTO users (username, password, email, full_name, role, phone, address)
    VALUES ('admin', '$2y$10$iUzKJfRboQtmZU3YnNOqTOOTNH.r05TRCwecRKxJsF1OZJ77atFy2', 'admin@electric.com', 'System Administrator', 'admin', '1234567890', 'Admin Office');
END
GO

-- Insert Sample Customer (password: customer123)
IF NOT EXISTS (SELECT * FROM users WHERE username = 'customer1')
BEGIN
    INSERT INTO users (username, password, email, full_name, role, phone, address)
    VALUES ('customer1', '$2y$10$qx3LdB9Us1O4WjIRF/Sgfeaq8R8ToRXxMxgb.K5cNvjt3f0Xzhk2.', 'customer1@email.com', 'John Doe', 'customer', '9876543210', '123 Main Street');
END
GO

-- Insert Sample Staff (password: staff123)
IF NOT EXISTS (SELECT * FROM users WHERE username = 'staff1')
BEGIN
    INSERT INTO users (username, password, email, full_name, role, phone, address)
    VALUES ('staff1', '$2y$10$cxgyFEp1lr.w.OkUTZgje..yN9t66B5jUE9PQKlHTC2/24gXqvaOK', 'staff1@electric.com', 'Jane Smith', 'staff', '5551234567', 'Staff Office');
END
GO

PRINT 'Database schema created successfully!';
GO
