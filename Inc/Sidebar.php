<?php include_once 'link.php' ?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
        <a href="<?= $Project_URL; ?>index.php" class="brand-link">
            <img src="<?= $Project_URL; ?>assets/Dashboard/Website_logo.png" alt="Logo">
        </a>
    </div>

    <!-- Sidebar Menu -->
    <div class="sidebar-wrapper">
        <nav>
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="./index.php" class="nav-link">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Authentication -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-person-badge"></i>
                        <p>Authentication <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./auth/users.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Users</p>
                            </a></li>
                        <li class="nav-item"><a href="./auth/add_user.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add User</p>
                            </a></li>
                        <li class="nav-item"><a href="./auth/roles.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Roles</p>
                            </a></li>
                        <li class="nav-item"><a href="./auth/permissions.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Permissions</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Category -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-tags"></i>
                        <p>Categories<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./category/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>All Categories</p>
                            </a></li>
                        <li class="nav-item"><a href="./category/add.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Category</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Suppliers -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-truck"></i>
                        <p>Suppliers<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./supplier/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>All Suppliers</p>
                            </a></li>
                        <li class="nav-item"><a href="./supplier/add.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Supplier</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Products -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>Products<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./product/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>All Products</p>
                            </a></li>
                        <li class="nav-item"><a href="./product/add.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Product</p>
                            </a></li>
                        <li class="nav-item"><a href="./product/stock.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Stock Overview</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Product Requests -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-envelope-exclamation"></i>
                        <p>Product Requests<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./requests/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Request List</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Purchases -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart-check"></i>
                        <p>Purchases<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./purchase/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>All Purchases</p>
                            </a></li>
                        <li class="nav-item"><a href="./purchase/add.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Purchase</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Sales -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cash-stack"></i>
                        <p>Sales<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./sales/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>All Sales</p>
                            </a></li>
                        <li class="nav-item"><a href="./sales/add.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Sale</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Inventory Adjustment -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-pencil-square"></i>
                        <p>Inventory Adjustments<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./adjustments/index.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Adjustment History</p>
                            </a></li>
                        <li class="nav-item"><a href="./adjustments/add.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Adjustment</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-graph-up-arrow"></i>
                        <p>Reports<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./reports/stock.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Stock Report</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/low_stock.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Low Stock</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/sales.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Sales Report</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/purchases.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Purchase Report</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/movement.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Product Movement</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Settings -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>System Settings<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./settings/company.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Company Info</p>
                            </a></li>
                        <li class="nav-item"><a href="./settings/theme.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Theme Settings</p>
                            </a></li>
                        <li class="nav-item"><a href="./settings/email.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Email Settings</p>
                            </a></li>
                        <li class="nav-item"><a href="./settings/backup.php" class="nav-link"><i class="nav-icon bi bi-circle"></i>
                                <p>Backup & Restore</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Logs -->
                <li class="nav-item">
                    <a href="./logs/index.php" class="nav-link">
                        <i class="nav-icon bi bi-journal-text"></i>
                        <p>Activity Logs</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>