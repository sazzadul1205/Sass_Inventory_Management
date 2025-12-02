
# 📘 **PROJECT DETAILS — SASS Inventory Management System (IMS)**

---

# **1. Project Overview**

The **SASS Inventory Management System (IMS)** is a role-based inventory solution built to manage products, categories, suppliers, purchases, sales, user roles, permissions, and customer product requests.

The system supports:

* Admin & Employee roles
* Dynamic permission system
* Real-time stock updates
* Purchase & sale tracking
* Product request management
* Complete audit fields (created_at / updated_at)

The backend database is built using **MariaDB 10.4**, and the system uses **PHP, jQuery, Bootstrap**, and **phpMyAdmin** for database administration.

---

# **2. Core Features**

### ✅ **1. User Management**

* Add/edit users
* Assign user roles
* Password hashing
* Track creation & update time
* Unique username enforcement

### ✅ **2. Role-Based Access Control (RBAC)**

* Admin and Employee roles included
* Permissions stored in DB (Add, Edit, Delete, View for all modules)
* Many-to-many relationship between roles and permissions
* Easy to add new roles/permissions

### ✅ **3. Category Management**

* Add/edit/delete categories
* Unique names
* Optional description
* Auto timestamps

### ✅ **4. Product Management**

* Product CRUD
* Linked with Category & Supplier
* Tracks stock quantity
* Price management
* Foreign key constraints:

  * `supplier_id` → ON DELETE SET NULL
  * `category_id` → ON DELETE SET NULL

### ✅ **5. Supplier Management**

* Add/edit suppliers
* Unique supplier name
* Phone & email tracking

### ✅ **6. Purchase Management**

* Add purchase records
* Tracks purchase price, supplier, product, quantity
* Auto stock increment can be implemented
* Date and timestamp tracking

### ✅ **7. Sales Management**

* Records sales
* Links sales to a product
* Links to user who created the sale
* Auto stock reduction can be implemented

### ✅ **8. Product Request Management**

Allow visitors to request products before adding them to inventory.

Includes fields:

* Visitor name/email
* Product requested
* Quantity
* Notes
* Status (pending, reviewed, rejected, approved)
* Processed by admin

### **9. Detailed Logging**

Every table includes:

* `created_at`
* `updated_at`
* Easy auditing and tracking

---

# **3. Detailed Database Schema**

The database name is:

```
sass_inventory
```

### **3.1 Tables Overview**

| Table Name        | Purpose                        |
| ----------------- | ------------------------------ |
| `user`            | Stores system users            |
| `role`            | Defines user roles             |
| `permission`      | Defines system permissions     |
| `role_permission` | Maps roles to permissions      |
| `category`        | Product categories             |
| `product`         | Product information            |
| `supplier`        | Suppliers                      |
| `purchase`        | Purchase history               |
| `sale`            | Sales history                  |
| `product_request` | Product requests from visitors |

---

# **4. Entity Descriptions**

### **4.1 User**

Fields:

* username
* password (MD5 hash in current dump; recommend bcrypt)
* email
* role_id (FK)

Role relationship:

```
user.role_id → role.id   (FK)
```

### **4.2 Role**

* id
* role_name
* Example:

  * Admin
  * Employee

### **4.3 Permission**

Includes entries such as:

* View Users
* Add Product
* Edit Category
* Delete Sale
* View Product Requests
  … etc (34 permissions)

### **4.4 Role Permission**

This is a **many-to-many** join table:

```
role ↔ role_permission ↔ permission
```

---

### **4.5 Category**

Simple category model:

* Unique name
* Description
* Timestamps

### **4.6 Product**

Links to:

* Category
* Supplier

With constraints:

```
ON DELETE SET NULL
```

### **4.7 Supplier**

Unique name
Optional phone/email

### **4.8 Purchase**

* Linked to product
* Linked to supplier
* Price, qty
* Auto timestamps

### **4.9 Sale**

* Linked to product
* Linked to user
* Sale date, price, qty

### **4.10 Product Request**

Used to track external customer requests for new products.

Fields include:

* visitor name/email
* product name (requested)
* quantity
* notes
* status → ENUM
* processed_by → user.id
* processed_at

---

# **5. Database Relationships (ER Model Overview)**

### **User & Role**

```
user.role_id → role.id   (many-to-one)
```

### **Role & Permission**

```
role.id ↔ role_permission.role_id
permission.id ↔ role_permission.permission_id
```

### **Product Relationships**

```
product.category_id → category.id
product.supplier_id → supplier.id
```

### **Purchase**

```
purchase.product_id → product.id
purchase.supplier_id → supplier.id
```

### **Sale**

```
sale.product_id → product.id
sale.created_by → user.id
```

### **Product Request**

```
product_request.processed_by → user.id
```

---

# **6. Auto-Increment Configuration**

All primary tables include AUTO_INCREMENT on `id`.

Notable:

* role → AUTO_INCREMENT starts at 3
* permission → AUTO_INCREMENT starts at 35
* user → next ID = 12


---

# **7. Strengths of This System**

* Fully normalized schema
* Professional RBAC architecture
* Clean modular tables
* Fast indexing
* Good referential integrity
* Extendable for web applications, POS, ERP, etc.

--