<?php
include_once 'link.php';

// Returns 'active' if the current page matches the URL and is not in root
function isActive($page)
{
    $currentPath = $_SERVER['PHP_SELF'];        // e.g. /Sass_Inventory_Management/index.php
    $pagePath = '/' . ltrim($page, './');      // e.g. /logs/index.php

    // Adjust if project is in a subfolder
    $projectRoot = '/Sass_Inventory_Management';
    $pageFullPath = $projectRoot . $pagePath;

    return $currentPath === $pageFullPath ? 'active' : '';
}


// Returns 'menu-open' if one of the subpages matches and not in root
function isMenuOpen($pages = [])
{
    $currentFile = basename($_SERVER['PHP_SELF']);
    $currentDir = dirname($_SERVER['PHP_SELF']);

    // Check if current page is in project root folder
    $projectRoot = '/Sass_Inventory_Management'; // adjust if your folder name changes
    if ($currentDir === $projectRoot || $currentDir === '/' || $currentDir === '' || $currentDir === '\\') {
        return '';
    }

    foreach ($pages as $page) {
        if ($currentFile === basename($page)) {
            return 'menu-open';
        }
    }
    return '';
}
?>


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
                <li class="nav-item <?= isActive('index.php') ?>">
                    <a href="./index.php" class="nav-link <?= isActive('index.php') ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Authentication -->
                <?php $authPages = ['./auth/users.php', './auth/add_user.php', './auth/roles.php', './auth/permissions.php']; ?>
                <li class="nav-item <?= isMenuOpen($authPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-person-badge"></i>
                        <p>Authentication <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= $Project_URL ?>auth/users.php" class="nav-link <?= isActive('./auth/users.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Users</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>auth/add_user.php" class="nav-link <?= isActive('./auth/add_user.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add User</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>auth/roles.php" class="nav-link <?= isActive('./auth/roles.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Roles</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>auth/permissions.php" class="nav-link <?= isActive('./auth/permissions.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Permissions</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Category -->
                <?php $categoryPages = ['category/index.php', 'category/add.php']; ?>
                <li class="nav-item <?= isMenuOpen($categoryPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-tags"></i>
                        <p>Categories<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= $Project_URL ?>category/index.php" class="nav-link <?= isActive('./category/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>All Categories</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>category/add.php" class="nav-link <?= isActive('./category/add.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Category</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Suppliers -->
                <?php $supplierPages = ['supplier/index.php', 'supplier/add.php']; ?>
                <li class="nav-item <?= isMenuOpen($supplierPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-truck"></i>
                        <p>Suppliers<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= $Project_URL ?>supplier/index.php" class="nav-link <?= isActive('./supplier/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>All Suppliers</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>supplier/add.php" class="nav-link <?= isActive('./supplier/add.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Supplier</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Products -->
                <?php $productPages = ['product/index.php', 'product/add.php', 'product/stock.php']; ?>
                <li class="nav-item <?= isMenuOpen($productPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>Products<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="<?= $Project_URL ?>product/index.php" class="nav-link <?= isActive('./product/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>All Products</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>product/add.php" class="nav-link <?= isActive('./product/add.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Product</p>
                            </a></li>
                        <li class="nav-item"><a href="<?= $Project_URL ?>product/stock.php" class="nav-link <?= isActive('./product/stock.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Stock Overview</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Product Requests -->
                <?php $requestPages = ['./requests/index.php']; ?>
                <li class="nav-item <?= isMenuOpen($requestPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-envelope-exclamation"></i>
                        <p>Product Requests<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./requests/index.php" class="nav-link <?= isActive('./requests/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Request List</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Purchases -->
                <?php $purchasePages = ['./purchase/index.php', './purchase/add.php']; ?>
                <li class="nav-item <?= isMenuOpen($purchasePages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cart-check"></i>
                        <p>Purchases<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./purchase/index.php" class="nav-link <?= isActive('./purchase/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>All Purchases</p>
                            </a></li>
                        <li class="nav-item"><a href="./purchase/add.php" class="nav-link <?= isActive('./purchase/add.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Purchase</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Sales -->
                <?php $salesPages = ['./sales/index.php', './sales/add.php']; ?>
                <li class="nav-item <?= isMenuOpen($salesPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-cash-stack"></i>
                        <p>Sales<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./sales/index.php" class="nav-link <?= isActive('./sales/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>All Sales</p>
                            </a></li>
                        <li class="nav-item"><a href="./sales/add.php" class="nav-link <?= isActive('./sales/add.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Sale</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Inventory Adjustments -->
                <?php $adjustPages = ['./adjustments/index.php', './adjustments/add.php']; ?>
                <li class="nav-item <?= isMenuOpen($adjustPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-pencil-square"></i>
                        <p>Inventory Adjustments<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./adjustments/index.php" class="nav-link <?= isActive('./adjustments/index.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Adjustment History</p>
                            </a></li>
                        <li class="nav-item"><a href="./adjustments/add.php" class="nav-link <?= isActive('./adjustments/add.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Add Adjustment</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Reports -->
                <?php $reportPages = ['./reports/stock.php', './reports/low_stock.php', './reports/sales.php', './reports/purchases.php', './reports/movement.php']; ?>
                <li class="nav-item <?= isMenuOpen($reportPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-graph-up-arrow"></i>
                        <p>Reports<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./reports/stock.php" class="nav-link <?= isActive('./reports/stock.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Stock Report</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/low_stock.php" class="nav-link <?= isActive('./reports/low_stock.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Low Stock</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/sales.php" class="nav-link <?= isActive('./reports/sales.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Sales Report</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/purchases.php" class="nav-link <?= isActive('./reports/purchases.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Purchase Report</p>
                            </a></li>
                        <li class="nav-item"><a href="./reports/movement.php" class="nav-link <?= isActive('./reports/movement.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Product Movement</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Settings -->
                <?php $settingsPages = ['./settings/company.php', './settings/theme.php', './settings/email.php', './settings/backup.php']; ?>
                <li class="nav-item <?= isMenuOpen($settingsPages) ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>System Settings<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"><a href="./settings/company.php" class="nav-link <?= isActive('./settings/company.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Company Info</p>
                            </a></li>
                        <li class="nav-item"><a href="./settings/theme.php" class="nav-link <?= isActive('./settings/theme.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Theme Settings</p>
                            </a></li>
                        <li class="nav-item"><a href="./settings/email.php" class="nav-link <?= isActive('./settings/email.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Email Settings</p>
                            </a></li>
                        <li class="nav-item"><a href="./settings/backup.php" class="nav-link <?= isActive('./settings/backup.php') ?>"><i class="nav-icon bi bi-circle"></i>
                                <p>Backup & Restore</p>
                            </a></li>
                    </ul>
                </li>

                <!-- Logs -->
                <li class="nav-item <?= isActive('./logs/index.php') ?>">
                    <a href="./logs/index.php" class="nav-link <?= isActive('./logs/index.php') ?>">
                        <i class="nav-icon bi bi-journal-text"></i>
                        <p>Activity Logs</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>