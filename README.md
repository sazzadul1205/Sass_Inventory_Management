# Sass Inventory Management System

## Introduction

The **Sass Inventory Management System** is a comprehensive web-based application designed to simplify and automate the management of inventory, purchases, sales, suppliers, and user roles within a business. Inventory management is a critical component for any organization that handles physical goods, and traditional manual methods of tracking stock often lead to inefficiencies, human errors, and financial losses.

This system addresses these challenges by providing a **centralized platform** where businesses can maintain accurate records of their products, monitor stock levels in real-time, manage suppliers, and track both purchase and sales transactions. With a role-based access control system, the application ensures that only authorized personnel can perform sensitive operations such as modifying stock, generating reports, or managing users, thus improving security and accountability.

### Key motivations for developing this system include:

- **Error Reduction:** Automated tracking reduces mistakes caused by manual record-keeping.
- **Time Efficiency:** Quick access to stock levels, sales, and purchase data saves operational time.
- **Data-Driven Decisions:** Real-time reports and graphical analyses allow management to make informed decisions.
- **Scalability:** The system can accommodate growing product lines, users, and transactions without compromising performance.
- **Accountability:** Role-based permissions ensure that actions are traceable and controlled.

In essence, the Sass Inventory Management System transforms the traditional inventory process into a **streamlined, reliable, and user-friendly solution**. It not only provides a digital record of products and transactions but also empowers businesses with insights through reports and analytics, ultimately aiding in strategic planning and operational efficiency.

## 1. Authentication Module

The **Authentication Module** is a critical component of the Sass Inventory Management System that ensures secure access and proper role-based permissions for all users. It handles user login, logout, role assignment, and permissions management, providing a secure and organized way to control who can access which parts of the system.

### 1.1 Features

1. **User Login and Logout**

   - Users can securely log in using a username/email and password.
   - Sessions are managed to maintain user authentication during usage.
   - Users can log out to end their session, preventing unauthorized access.

2. **User Management**

   - Admins can add, edit, and delete users.
   - Each user is assigned a specific **role** which defines their access rights.
   - The system ensures that users can only access modules and perform actions permitted by their role.

3. **Role Management**

   - Roles define levels of access within the system (e.g., Admin, Manager, Employee).
   - Admins can add, edit, or delete roles to reflect organizational hierarchy.
   - Each role can have specific permissions linked to system features.

4. **Permissions Management**

   - Permissions define what actions a user can perform within a module.
   - Admins can update permissions for each role, ensuring precise control over system operations.
   - Examples include access to add products, generate reports, or manage users.

5. **Security Measures**
   - Passwords are securely stored using encryption.
   - Role-based access control ensures users can only perform allowed actions.
   - Unauthorized access attempts are restricted and logged for auditing.

### 1.2 Pages in the Authentication Module

| Page                     | Purpose                                                 |
| ------------------------ | ------------------------------------------------------- |
| `login.php`              | Provides the login interface for users to authenticate. |
| `logout.php`             | Ends user sessions and redirects to the login page.     |
| `add_user.php`           | Allows admins to add new users with assigned roles.     |
| `edit_user.php`          | Enables modification of user details and roles.         |
| `delete_user.php`        | Removes users from the system.                          |
| `users.php`              | Displays a list of all registered users.                |
| `roles.php`              | Displays all roles within the system.                   |
| `add_role.php`           | Allows admins to create new roles.                      |
| `edit_role.php`          | Enables modification of existing roles.                 |
| `delete_role.php`        | Removes roles from the system.                          |
| `permissions.php`        | Displays permissions associated with roles.             |
| `update_permissions.php` | Allows updating of role permissions.                    |

### 1.3 Importance

The authentication module ensures that only authorized personnel can access sensitive data and perform critical operations. By implementing **role-based access control**, the system maintains security, accountability, and proper segregation of duties within the organization.

---

## 2. Categories Module

The **Categories Module** is designed to manage product categorization within the Sass Inventory Management System. Proper categorization helps in organizing products, generating reports, tracking inventory, and improving search functionality. This module allows administrators to create, edit, and delete categories efficiently.

### 3.1 Features

1. **Add Categories**

   - Administrators can add new product categories.
   - Each category can have a unique name and optional description.
   - Ensures products can be grouped logically for better management.

2. **Edit Categories**

   - Existing categories can be updated to correct errors or rename them.
   - Modifications are reflected across all associated products automatically.

3. **Delete Categories**

   - Categories that are no longer needed can be removed.
   - The system ensures proper handling to avoid orphaned product records.

4. **View Categories**
   - Lists all available categories in a table format.
   - Provides quick access to edit or delete actions.
   - Supports search and filtering to quickly find categories.

### 2.2 Pages in the Categories Module

| Page         | Purpose                                                           |
| ------------ | ----------------------------------------------------------------- |
| `index.php`  | Displays a list of all categories with options to edit or delete. |
| `add.php`    | Interface to add a new category.                                  |
| `edit.php`   | Interface to modify an existing category.                         |
| `delete.php` | Handles the removal of categories from the system.                |

### 2.3 Importance

The Categories Module is essential for maintaining a structured inventory. It improves the efficiency of inventory tracking, reporting, and product management. Proper categorization reduces errors, simplifies product searches, and enhances overall system usability.

---

## 3. Supplier Module

The **Supplier Module** manages all information related to suppliers who provide products to the inventory. Efficient supplier management ensures that purchase orders, stock levels, and product sourcing are well-organized. This module allows administrators to add, edit, delete, and view supplier details.

### 3.1 Features

1. **Add Suppliers**

   - Administrators can register new suppliers.
   - Supplier information includes name, contact details (phone/email), address, and optional notes.
   - Ensures smooth communication and record-keeping with vendors.

2. **Edit Suppliers**

   - Update supplier details when contact information or company name changes.
   - Keeps the supplier database accurate and up-to-date.

3. **Delete Suppliers**

   - Remove suppliers who are no longer active.
   - Ensures that inactive suppliers don’t clutter purchase processes.

4. **View Suppliers**
   - Displays all registered suppliers in a tabular format.
   - Provides search and filtering options for quick access.
   - Includes quick action buttons for editing or deleting a supplier.

### 3.2 Pages in the Supplier Module

| Page         | Purpose                                                       |
| ------------ | ------------------------------------------------------------- |
| `index.php`  | Shows a list of all suppliers with options to edit or delete. |
| `add.php`    | Interface to add a new supplier to the system.                |
| `edit.php`   | Interface to modify existing supplier details.                |
| `delete.php` | Handles the removal of suppliers from the database.           |

### 3.3 Importance

The Supplier Module is critical for maintaining a reliable inventory. By managing supplier information effectively, the system ensures timely procurement of products, improves supplier relationships, and supports accurate reporting and stock management.

---

## 4. Product Module

The **Product Module** is the core of the inventory management system. It manages all products in stock, including their details, categories, suppliers, and stock levels. This module ensures that products are tracked accurately from procurement to sales.

### 4.1 Features

1. **Add Products**

   - Allows administrators to add new products.
   - Product details include name, category, supplier, purchase price, selling price, stock quantity, and description.
   - Ensures all necessary product information is available for inventory tracking.

2. **Edit Products**

   - Modify existing product information such as price, quantity, category, or supplier.
   - Keeps product data accurate for reporting and stock management.

3. **Delete Products**

   - Remove products that are discontinued or no longer available.
   - Helps maintain a clean and manageable inventory list.

4. **View Products**

   - Displays all products in a tabular format.
   - Provides search, filter, and sorting options based on category, supplier, or stock levels.
   - Includes quick action buttons for editing or deleting products.

5. **Product Stock Management**

   - Monitors stock levels for each product.
   - Highlights low stock to prevent shortages and support timely reordering.
   - Tracks stock additions through purchase and reductions through sales.

6. **Product Reports & Graphs**
   - Generates stock reports and product graphs.
   - Helps in analyzing product trends and inventory performance over time.

### 4.2 Pages in the Product Module

| Page         | Purpose                                                                 |
| ------------ | ----------------------------------------------------------------------- |
| `index.php`  | Lists all products with options to edit, delete, or view stock details. |
| `add.php`    | Interface to add new products to the system.                            |
| `edit.php`   | Interface to modify existing product information.                       |
| `delete.php` | Handles removal of products from the database.                          |
| `stock.php`  | Manages and displays stock levels for products.                         |
| `graph.php`  | Visualizes product trends and stock reports.                            |

### 4.3 Importance

The Product Module is essential for tracking inventory accurately. It ensures correct stock levels, supports procurement and sales decisions, and provides detailed insights into product performance, helping businesses reduce losses and optimize inventory management.

---

## 5. Purchase Module

The **Purchase Module** manages the procurement of products from suppliers. It keeps track of all purchase transactions, generates receipts, and ensures stock levels are updated accurately after each purchase.

### 5.1 Features

1. **Add Purchase**

   - Allows administrators or authorized users to record new purchases.
   - Includes details such as product name, quantity, purchase price, supplier, and purchase date.
   - Automatically updates the stock levels in the Product Module.

2. **View All Purchases**

   - Displays a list of all purchase records.
   - Includes options to filter by date, supplier, or product.
   - Helps monitor purchase history and track expenditures.

3. **Manage My Purchases**

   - Allows users to view purchases they recorded or are responsible for.
   - Provides a personalized view for accountability and tracking.

4. **Generate Receipts**

   - Automatically generates receipts for each purchase transaction.
   - Receipts include supplier details, product details, quantities, prices, and total cost.
   - Can be used for record-keeping, auditing, or supplier communication.

5. **Edit Purchase Records**

   - Allows authorized users to update purchase details if errors are made.
   - Ensures that the stock quantity reflects the correct amounts after edits.

6. **Purchase Reports**
   - Generates reports for all purchases.
   - Helps in analyzing procurement trends, supplier reliability, and cost patterns.

### 5.2 Pages in the Purchase Module

| Page               | Purpose                                                      |
| ------------------ | ------------------------------------------------------------ |
| `index.php`        | Main dashboard for purchase transactions and quick overview. |
| `add.php`          | Form to record a new purchase.                               |
| `all-receipt.php`  | Lists all generated purchase receipts.                       |
| `my-purchases.php` | Shows purchases recorded by the logged-in user.              |
| `my-receipts.php`  | Displays receipts relevant to the logged-in user.            |
| `receipt.php`      | Detailed view of a single purchase receipt.                  |

### 5.3 Importance

The Purchase Module ensures accurate procurement records and maintains updated stock information. It provides transparency in purchasing, supports financial management, and helps prevent stock discrepancies, enabling efficient inventory control.

---

## 6. Sales Module

The **Sales Module** manages all product sales within the system. It tracks sales transactions, generates receipts, and ensures stock levels are updated immediately after each sale. This module is crucial for monitoring revenue, customer purchases, and inventory.

### 6.1 Features

1. **Add Sale**

   - Allows administrators or authorized users to record new sales transactions.
   - Includes product details, quantity sold, selling price, and sale date.
   - Automatically deducts sold quantities from the stock.

2. **View All Sales**

   - Displays a list of all sales transactions.
   - Can be filtered by date, product, or customer for easier tracking.
   - Helps management monitor revenue and sales trends.

3. **Manage My Sales**

   - Provides a personalized view for users to see the sales they recorded.
   - Increases accountability and ensures proper tracking of user activity.

4. **Generate Receipts**

   - Automatically generates receipts for each sale.
   - Receipts include product details, quantities, prices, and total amount.
   - Can be used for customer delivery, auditing, or financial records.

5. **Edit Sales Records**

   - Authorized users can update sales details if mistakes are made.
   - Stock quantities are automatically adjusted to reflect corrections.

6. **Sales Reports**
   - Generates detailed sales reports.
   - Helps analyze revenue trends, popular products, and overall business performance.

### 6.2 Pages in the Sales Module

| Page              | Purpose                                                |
| ----------------- | ------------------------------------------------------ |
| `index.php`       | Dashboard displaying sales summary and quick overview. |
| `add.php`         | Form to record a new sale.                             |
| `all-receipt.php` | Lists all generated sales receipts.                    |
| `my-sales.php`    | Displays sales recorded by the logged-in user.         |
| `my-receipts.php` | Shows receipts specific to the logged-in user.         |
| `receipt.php`     | Detailed view of a single sales receipt.               |

### 6.3 Importance

The Sales Module is essential for revenue management, customer transaction tracking, and stock control. It ensures transparency in sales operations, helps with financial reporting, and prevents discrepancies in inventory levels, supporting overall business efficiency.

---

## 7. Receipts Module

The **Receipts Module** handles the management of sales and purchase receipts within the system. It provides a way to view, track, and print receipts for individual transactions, ensuring accurate records for auditing and customer reference.

### 7.1 Features

1. **View All Receipts**

   - Displays a comprehensive list of all receipts in the system.
   - Can be filtered by type (sales or purchase), date, or user.
   - Helps administrators and management quickly access transaction records.

2. **My Receipts**

   - Shows receipts specifically generated by the logged-in user.
   - Enhances accountability and allows users to track their own activities.

3. **Detailed Receipt View**

   - Provides a complete view of a single receipt.
   - Includes transaction details like products, quantities, prices, total amount, and date.
   - Useful for both internal tracking and customer reference.

4. **Printable Receipts**
   - Each receipt can be printed or exported for physical record-keeping.
   - Supports auditing, accounting, and customer delivery documentation.

### 7.2 Pages in the Receipts Module

| Page              | Purpose                                                  |
| ----------------- | -------------------------------------------------------- |
| `index.php`       | Displays all receipts with filtering and search options. |
| `my-receipts.php` | Shows receipts generated by the logged-in user.          |
| `receipt.php`     | Detailed view of a single receipt for sales or purchase. |

### 7.3 Importance

The Receipts Module ensures proper documentation of all financial transactions in the system. It provides transparency, supports auditing and accounting processes, and helps maintain trust with customers by offering official proof of purchase or transaction.

---

## 8. Reports Module

The **Reports Module** provides analytics and insights on the inventory, sales, purchases, and stock levels. It allows administrators and managers to make data-driven decisions, identify trends, and maintain efficient inventory management.

### 8.1 Features

1. **Low Stock Report**

   - Displays products that are below a predefined minimum stock level.
   - Helps in timely reordering to avoid stockouts.
   - Can be filtered by category or supplier.

2. **Product Graph**

   - Visual representation of product-related data such as stock levels or sales trends.
   - Makes it easier to analyze performance over time.
   - Supports bar charts, line charts, or pie charts for different metrics.

3. **Purchases Report**

   - Shows all purchase transactions within a selected date range.
   - Provides details such as supplier, product, quantity, and total cost.
   - Useful for budgeting and supplier performance evaluation.

4. **Sales Report**

   - Displays all sales transactions over a specified period.
   - Shows product-wise and user-wise sales, revenue generated, and trends.
   - Helps track business performance and revenue streams.

5. **Stock Report**
   - Provides a snapshot of current stock levels for all products.
   - Includes product details, category, supplier, and available quantity.
   - Supports better inventory control and auditing.

### 8.2 Pages in the Reports Module

| Page                | Purpose                                                   |
| ------------------- | --------------------------------------------------------- |
| `low_stock.php`     | Generates report of products with low stock levels.       |
| `product-graph.php` | Displays visual graphs for product trends and statistics. |
| `purchases.php`     | Shows purchase transactions and analytics.                |
| `sales.php`         | Shows sales transactions and performance metrics.         |
| `stock.php`         | Displays current inventory stock report.                  |

### 8.3 Importance

The Reports Module is crucial for strategic decision-making. It allows managers to monitor inventory health, analyze sales and purchases, identify trends, and plan restocking. With accurate reporting, the business can reduce losses, optimize operations, and improve profitability.

---

## 9. Config Module

The **Config Module** manages the core configuration and security settings of the Sass Inventory Management System. It ensures that the system connects correctly to the database and maintains secure access control for users.

### 9.1 Files in the Config Module

| File             | Purpose                                                                                                                                                                                                   |
| ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `db_config.php`  | Contains the database configuration and connection logic. It defines database host, name, username, and password. This file is included in all modules that require database access.                      |
| `auth_guard.php` | Manages authentication and access control. It ensures that only authorized users can access restricted pages and modules. It checks the login session and redirects unauthorized users to the login page. |

### 9.2 Responsibilities

1. **Database Connectivity**

   - Centralized configuration for database connections.
   - Easy to update credentials or change the database server without modifying multiple files.

2. **Authentication & Security**
   - Guards protected pages from unauthorized access.
   - Ensures session validation to maintain secure user login states.
   - Provides the backbone for user permission checks throughout the system.

### 9.3 Importance

Without proper configuration and authentication, the system would be vulnerable to unauthorized access and potential data loss. The Config Module ensures the application runs smoothly and securely by handling essential backend settings and access control.

---

## 10. Inc (Includes) Module

The **Inc Module** contains reusable components and partial files that are included across multiple pages of the Sass Inventory Management System. These files help maintain consistency in layout, navigation, and footer content.

### 10.1 Files in the Inc Module

| File          | Purpose                                                                                                                                              |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Navbar.php`  | Contains the HTML and PHP code for the top navigation bar. It usually includes links to major sections of the system and user profile options.       |
| `Sidebar.php` | Contains the HTML and PHP code for the side navigation menu. It dynamically shows links based on user roles and permissions.                         |
| `Footer.php`  | Contains the footer layout and copyright information. Included on all pages for consistency.                                                         |
| `link.php`    | Centralized file for including CSS and JS links, as well as other shared resources. It helps in maintaining consistent references across the system. |

### 10.2 Responsibilities

1. **Code Reusability**

   - Avoids duplication by keeping common page components in separate files.
   - Simplifies maintenance: updating the sidebar or navbar in one file updates it across all pages.

2. **Dynamic Content**

   - Sidebar and Navbar adapt based on user roles and permissions.
   - Ensures proper navigation visibility depending on the user’s access level.

3. **Consistent Layout**
   - Provides a unified look and feel across the system.
   - Maintains design consistency for headers, footers, and navigation menus.

### 10.3 Importance

The Inc Module is essential for maintaining a structured, organized, and professional user interface. By separating common elements, the system becomes easier to manage, more scalable, and visually consistent.

---

## Database Structure

The database `sass_inventory` is designed to handle all inventory operations efficiently. It includes relational tables, foreign key constraints, and several views for reporting.

---

### Tables

#### 1. `category`

Stores product categories.

| Column        | Type         | Description                      |
| ------------- | ------------ | -------------------------------- |
| `id`          | int(11)      | Primary key, auto-increment      |
| `name`        | varchar(100) | Category name, unique            |
| `description` | text         | Optional description             |
| `created_at`  | timestamp    | Record creation time             |
| `updated_at`  | timestamp    | Auto-updated when record changes |

---

#### 2. `supplier`

Tracks suppliers of products.

| Column       | Type         | Description                   |
| ------------ | ------------ | ----------------------------- |
| `id`         | int(11)      | Primary key, auto-increment   |
| `name`       | varchar(100) | Supplier name, unique         |
| `phone`      | varchar(20)  | Optional phone number         |
| `email`      | varchar(100) | Optional email                |
| `created_at` | timestamp    | Record creation time          |
| `updated_at` | timestamp    | Auto-updated on record change |

---

#### 3. `product`

Stores product information.

| Column              | Type          | Description                                       |
| ------------------- | ------------- | ------------------------------------------------- |
| `id`                | int(11)       | Primary key, auto-increment                       |
| `name`              | varchar(100)  | Product name                                      |
| `category_id`       | int(11)       | FK → `category.id` (nullable, ON DELETE SET NULL) |
| `supplier_id`       | int(11)       | FK → `supplier.id` (nullable, ON DELETE SET NULL) |
| `price`             | decimal(10,2) | Price per unit                                    |
| `quantity_in_stock` | int(11)       | Current stock                                     |
| `created_at`        | timestamp     | Record creation time                              |
| `updated_at`        | timestamp     | Auto-updated on change                            |

---

#### 4. `purchase`

Records all product purchases.

| Column           | Type          | Description                   |
| ---------------- | ------------- | ----------------------------- |
| `id`             | int(11)       | Primary key, auto-increment   |
| `product_id`     | int(11)       | FK → `product.id`             |
| `supplier_id`    | int(11)       | FK → `supplier.id` (optional) |
| `quantity`       | int(11)       | Quantity purchased            |
| `purchase_price` | decimal(10,2) | Total price of purchase       |
| `purchase_date`  | date          | Default today                 |
| `receipt_id`     | int(11)       | FK → `receipt.id` (optional)  |
| `purchased_by`   | int(11)       | FK → `user.id`                |
| `created_at`     | timestamp     | Record creation time          |
| `updated_at`     | timestamp     | Auto-updated on record change |

---

#### 5. `sale`

Tracks product sales.

| Column       | Type          | Description                   |
| ------------ | ------------- | ----------------------------- |
| `id`         | int(11)       | Primary key, auto-increment   |
| `product_id` | int(11)       | FK → `product.id`             |
| `quantity`   | int(11)       | Quantity sold                 |
| `sale_price` | decimal(10,2) | Total sale price              |
| `sale_date`  | date          | Default today                 |
| `receipt_id` | int(11)       | FK → `receipt.id` (optional)  |
| `sold_by`    | int(11)       | FK → `user.id` (seller/admin) |
| `created_at` | timestamp     | Record creation time          |
| `updated_at` | timestamp     | Auto-updated on change        |

---

#### 6. `receipt`

Stores purchase and sales receipts.

| Column           | Type          | Description                 |
| ---------------- | ------------- | --------------------------- |
| `id`             | int(11)       | Primary key, auto-increment |
| `receipt_number` | varchar(50)   | Unique receipt number       |
| `type`           | enum          | Either `purchase` or `sale` |
| `total_amount`   | decimal(12,2) | Total amount of receipt     |
| `created_by`     | int(11)       | FK → `user.id`              |
| `created_at`     | timestamp     | Record creation time        |
| `updated_at`     | timestamp     | Auto-updated on change      |

---

#### 7. `user`

Stores system users.

| Column       | Type         | Description                   |
| ------------ | ------------ | ----------------------------- |
| `id`         | int(11)      | Primary key, auto-increment   |
| `username`   | varchar(100) | Unique username               |
| `password`   | text         | Hashed password               |
| `email`      | varchar(100) | Optional email                |
| `role_id`    | int(11)      | FK → `role.id`                |
| `created_at` | timestamp    | Record creation time          |
| `updated_at` | timestamp    | Auto-updated on record change |

---

#### 8. `role` & `permission`

Defines user roles and permissions.

- **role**: `id`, `role_name` (admin, staff, etc.)
- **permission**: `id`, `permission_name` (add, edit, delete, view)
- **role_permission**: maps roles to permissions (many-to-many relationship)

---

### Views

Predefined views for easier reporting:

- `product_with_details` → product info with category & supplier names
- `purchase_details` → purchase info with product, supplier, and user
- `sale_details` → sale info with product and user
- `purchase_receipts_view` / `sales_receipts_view` → summary of receipts
- `stock_report` → product stock with purchased & sold quantities
- `view_product_movement` → product movement (in/out)
- `view_purchase_report` / `view_sales_report` → purchase and sales reports
- `role_permission_matrix` → roles vs permissions assignment

---

### Relationships

- **Products → Categories & Suppliers**: Many-to-one
- **Purchases → Products, Suppliers, Users**: Many-to-one
- **Sales → Products, Users**: Many-to-one
- **Receipts → Users**: Many-to-one
- **Users → Roles**: Many-to-one
- **Role-Permission**: Many-to-many

---