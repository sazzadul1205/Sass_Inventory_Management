# Sass Inventory Management System (SIMS)

## 🌐 LIVE DEMO

**Access the system:** [https://billcorporation.org/Inventory](https://billcorporation.org/Inventory)

---

## 📋 Executive Summary

The **Sass Inventory Management System (SIMS)** is a comprehensive, web-based solution designed to revolutionize inventory management for businesses of all sizes. By replacing error-prone manual processes with automated digital workflows, SIMS provides real-time visibility into stock levels, supplier relationships, sales performance, and purchasing activities—all from a centralized, secure platform.

### 🎯 Core Value Proposition

- **Real-time Inventory Tracking**: Eliminate stockouts and overstocking with live inventory monitoring
- **Role-Based Security**: Ensure data integrity with granular permission controls
- **Automated Reporting**: Make data-driven decisions with insightful analytics and visualizations
- **End-to-End Traceability**: Track every product from procurement to point-of-sale
- **Scalable Architecture**: Grow with your business without performance compromises

---

## 🏗️ System Architecture Overview

### Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Security**: Password hashing, SQL injection prevention, XSS protection
- **Session Management**: PHP native sessions with secure configurations

### Directory Structure

```
sass_inventory/
├── assets/              # CSS, JS, Images
├── auth/                # Authentication Module
├── categories/          # Categories Module
├── config/              # Database & Security Config
├── inc/                 # Reusable Components
├── products/            # Product Management
├── purchases/           # Purchase Module
├── reports/             # Analytics & Reporting
├── sales/               # Sales Module
├── suppliers/           # Supplier Management
└── receipts/            # Receipt Management
```

---

## 🔐 1. Authentication & Authorization Module

Here’s the **clean, honest update**—no fluff, no pretending. This reflects what you’re actually doing right now and keeps the door open for improvement later.

---

### 🛡️ Security Features

- **Authentication (Testing Phase)**: Passwords are currently hashed using **MD5** for testing and development purposes only
- **Dynamic Role & Permission System**: Roles are **manually created** with **dynamically assignable permissions** per module and action
- **Session Management**: Server-side session handling with enforced login checks and configurable inactivity timeout
- **Upgrade Path Ready**: Architecture allows future migration to stronger hashing (bcrypt/argon2) and optional multi-factor authentication

---

### 👥 User Roles & Permission Model

```
System Admin
    ├── Custom Role (Inventory Permissions)
    ├── Custom Role (Sales Permissions)
    ├── Custom Role (Purchase Permissions)
    ├── Custom Role (Warehouse Permissions)
    └── Custom Role (Cashier Permissions)
```

- Roles are **not hardcoded by level**
- Each role is defined by a **set of permissions**
- Permissions can be added, removed, or reassigned **without code changes**

---

### Straight talk

- MD5 is **acceptable only for testing** — switch before production
- Dynamic permissions > fixed role levels (you made the right call)
- This setup scales cleanly for real businesses

If you want, next we can:

- Design the **roles & permissions DB schema**
- Implement a **`can()` permission middleware**
- Plan a **safe MD5 → bcrypt migration**

Say which one and we move.

### 📁 Module Files

| File              | Purpose                       | Access Level |
| ----------------- | ----------------------------- | ------------ |
| `login.php`       | Secure authentication portal  | Public       |
| `users.php`       | User management dashboard     | Admin Only   |
| `roles.php`       | Role configuration interface  | Admin Only   |
| `permissions.php` | Permission matrix editor      | Admin Only   |
| `auth_guard.php`  | Session validation middleware | System       |

---

## 📦 2. Product Management

### 🔄 Product Lifecycle Management

1. **Product Onboarding** → Category assignment → Supplier linkage → Pricing setup
2. **Stock Monitoring** → Real-time quantity tracking → Low-stock alerts → Reorder point calculation
3. **Product Analysis** → Sales performance → Profit margin tracking → Seasonal trends

### 📊 Key Metrics Tracked

- Current Stock Value
- Turnover Rate
- Gross Margin per Product
- Stock-to-Sales Ratio
- Days of Inventory On Hand

---

## 🤝 3. Supplier Relationship Management

### 📋 Supplier Evaluation Matrix

| Criteria        | Weight | Tracking Method        |
| --------------- | ------ | ---------------------- |
| Delivery Time   | 30%    | Purchase order history |
| Product Quality | 25%    | Return/defect rate     |
| Pricing         | 20%    | Comparative analysis   |
| Payment Terms   | 15%    | Credit period tracking |
| Communication   | 10%    | Response time logs     |

### 🔔 Automated Features

- **Supplier Performance Reports**: Quarterly evaluations
- **Order Lead Time Alerts**: Proactive notifications
- **Contract Renewal Reminders**: Automated calendar events

---

## 💰 4. Procurement Workflow

### 📈 Purchase Order Process

```mermaid
graph LR
    A[Low Stock Alert] --> B[PO Creation]
    B --> C[Supplier Selection]
    C --> D[Price Negotiation]
    D --> E[Order Placement]
    E --> F[Goods Receipt]
    F --> G[Quality Check]
    G --> H[Stock Update]
    H --> I[Invoice Processing]
    I --> J[Payment]
```

### 📄 Document Management

- **Purchase Requisitions**: Internal approval workflows
- **Purchase Orders**: Automated numbering with supplier copies
- **Goods Received Notes**: Three-way matching system
- **Supplier Invoices**: Digital archival with payment tracking

---

## 🛒 5. Sales & Point of Sale

### 🚀 Quick Sale Features

- **Barcode Scanner Support**: Quick product lookup
- **Customer Database**: Repeat customer tracking
- **Discount Management**: Percentage/fixed amount discounts
- **Receipt Customization**: Branded receipt templates
- **Sales Returns**: Complete refund/credit note processing

### 💳 Payment Integration Ready

- **Multiple Payment Methods**: Cash, Card, Mobile Money
- **Partial Payments**: Customer credit tracking
- **Receipt Numbering**: Sequential/date-based options
- **Tax Calculation**: VAT/GST compliance ready

---

## 📊 6. Advanced Reporting Suite

### 📈 Real-Time Dashboards

1. **Executive Dashboard**

   - Daily Sales vs Target
   - Inventory Valuation
   - Top 10 Products by Revenue
   - Supplier Performance Scorecard

2. **Operational Dashboard**
   - Stock Movement Analysis
   - Pending Purchase Orders
   - Cashier Performance Metrics
   - Customer Purchase Patterns

### 📋 Standard Reports

| Report Type             | Frequency | Key Metrics                      |
| ----------------------- | --------- | -------------------------------- |
| **Inventory Valuation** | Daily     | COGS, Stock Value, Write-offs    |
| **Sales Performance**   | Weekly    | Revenue, Units Sold, Avg. Ticket |
| **Supplier Analysis**   | Monthly   | On-time Delivery, Quality Rating |
| **Profitability**       | Quarterly | Gross Margin, Net Profit, ROI    |

---

## 🗄️ 7. Database Schema

### 📐 Core Entity Relationships

```sql
-- Simplified Schema Overview
User (1) --- (*) Purchase
User (1) --- (*) Sale
Product (1) --- (*) Purchase
Product (1) --- (*) Sale
Category (1) --- (*) Product
Supplier (1) --- (*) Product
Supplier (1) --- (*) Purchase
Role (1) --- (*) User
```

### 🔍 Optimized Views for Reporting

- `v_product_performance` - Sales velocity and profitability
- `v_supplier_metrics` - Performance scorecards
- `v_inventory_aging` - Slow-moving stock identification
- `v_customer_purchase_history` - Customer behavior patterns

---

## 🚀 8. Installation & Setup

### ⚙️ Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- 1GB RAM minimum, 2GB recommended

### 📥 Installation Steps

1. **Download & Extract**

   ```bash
   wget https://billcorporation.org/Inventory/download/sass_inventory.zip
   unzip sass_inventory.zip -d /var/www/html/
   ```

2. **Database Configuration**

   ```bash
   mysql -u root -p
   CREATE DATABASE sass_inventory;
   USE sass_inventory;
   SOURCE /path/to/database.sql;
   ```

3. **Environment Setup**

   ```bash
   cp config/db_config.example.php config/db_config.php
   nano config/db_config.php  # Edit database credentials
   ```

4. **Permissions Setup**

   ```bash
   chmod 755 -R /var/www/html/sass_inventory/
   chown www-data:www-data -R /var/www/html/sass_inventory/
   ```

5. **Access System**
   - Navigate to `http://your-domain.com/sass_inventory`
   - Default Admin Login: `admin / admin123` _(Change immediately!)_

---

## 🔒 9. Security Best Practices

### 🛡️ Mandatory Configuration

1. **Change Default Credentials** Immediately after installation
2. **Enable HTTPS** SSL certificate installation
3. **Regular Backups** Automated database backups
4. **Access Logging** Monitor unauthorized access attempts
5. **IP Whitelisting** For administrative access (optional)

### 📋 Security Checklist

- [ ] Strong password policy enforced
- [ ] Session timeout configured (30 minutes)
- [ ] SQL injection protection verified
- [ ] XSS prevention measures implemented
- [ ] Regular security updates applied
- [ ] Backup restoration tested

---

## 📈 10. Performance Optimization

### ⚡ Recommended Settings

```php
// config/performance.php
'memory_limit' => '256M',
'max_execution_time' => 300,
'opcache.enable' => 1,
'realpath_cache_size' => '10M'
```

### 🗃️ Database Optimization

- **Indexing Strategy**: Implemented on all foreign keys and search columns
- **Query Caching**: MySQL query cache enabled
- **Regular Maintenance**: Weekly optimization schedules
- **Connection Pooling**: Persistent database connections

---

## 🤝 11. Support & Maintenance

### 📞 Getting Help

- **Documentation**: [https://billcorporation.org/Inventory/docs](https://billcorporation.org/Inventory/docs)
- **Issue Tracking**: GitHub repository issues section
- **Community Forum**: User discussion and best practices

### 🔄 Update Procedures

1. **Backup Current Installation**
2. **Download Latest Release**
3. **Merge Configuration Files**
4. **Run Database Migrations**
5. **Verify Functionality**

---

## 📱 12. Mobile Responsiveness

### 📲 Supported Devices

- **Desktop**: Full feature access
- **Tablet**: Optimized for 7-10 inch screens
- **Mobile**: Essential functions on 5-6 inch screens

### 📱 Mobile-Optimized Features

- Touch-friendly navigation
- Responsive data tables
- Mobile receipt printing
- Barcode scanning interface

---

## 🔮 13. Roadmap & Future Features

### 🚧 Planned Enhancements

| Quarter | Feature                         | Status         |
| ------- | ------------------------------- | -------------- |
| Q3 2024 | Mobile App (iOS/Android)        | Planned        |
| Q4 2024 | API for Third-party Integration | In Design      |
| Q1 2025 | Advanced Analytics with ML      | Research Phase |
| Q2 2025 | Multi-warehouse Support         | Planned        |

---

## 📄 License & Compliance

### 📜 Usage Rights

- **License**: MIT Open Source License
- **Commercial Use**: Allowed with attribution
- **Modifications**: Permitted with source disclosure
- **Warranty**: Provided "as-is" without guarantees

### 🌍 Compliance Ready

- **GDPR**: User data protection compliant
- **Tax Ready**: VAT/GST calculation support
- **Audit Trail**: Complete transaction logging
- **Data Export**: Standard formats (CSV, PDF, Excel)

---

## 🙏 Acknowledgments

### 👥 Development Team

- **Project Lead**: [Your Name/Organization]
- **UI/UX Design**: [Designer Name]
- **Database Architecture**: [DBA Name]
- **Quality Assurance**: [Tester Name]

### 📚 Built With

- Bootstrap 5
- Chart.js
- DataTables
- Font Awesome

---

## 📞 Contact & Contributions

### 💬 Get Involved

- **Bug Reports**: GitHub Issues
- **Feature Requests**: Community voting system
- **Code Contributions**: Pull requests welcome
- **Documentation**: Wiki edits encouraged

### 📧 Contact Information

- **Website**: [https://billcorporation.org](https://billcorporation.org)
- **Email**: support@billcorporation.org
- **Documentation**: [Full Technical Documentation](https://billcorporation.org/Inventory/docs)

---

**⭐ If you find this project useful, please consider giving it a star on GitHub!**

---

_Last Updated: March 2024 | Version: 2.1.0 | Database Schema Version: 3_
