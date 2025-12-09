<?php include_once 'link.php' ?>

<?php
// Sidebar Menu
$sidebarMenu = [
    [
        'title' => 'Dashboard',
        'icon' => 'bi bi-speedometer2',
        'url' => 'index.php',
        'permission' => 'view_dashboard'
    ],

    // Authentication Section
    [
        'title' => 'Authentication',
        'icon' => 'bi bi-person-badge',
        'permission' => 'view_authentication_menu',
        'submenu' => [

            [
                'title' => 'Users',
                'url' => 'auth/users.php',
                'icon' => 'bi bi-people',
                'permission' => 'view_users'
            ],
            [
                'title' => 'Add User',
                'url' => 'auth/add_user.php',
                'icon' => 'bi bi-person-plus',
                'permission' => 'add_user'
            ],
            [
                'title' => 'Edit User',
                'url' => 'auth/edit_user.php',
                'hidden' => true,
                'icon' => 'bi bi-pencil-square',
                'permission' => 'edit_user'
            ],

            [
                'title' => 'Roles',
                'url' => 'auth/roles.php',
                'icon' => 'bi bi-shield-lock',
                'permission' => 'view_roles'
            ],
            [
                'title' => 'Edit Role',
                'url' => 'auth/edit_role.php',
                'hidden' => true,
                'icon' => 'bi bi-pencil-square',
                'permission' => 'edit_role'
            ],
            [
                'title' => 'Add Role',
                'url' => 'auth/add_role.php',
                'hidden' => true,
                'icon' => 'bi bi-plus-square',
                'permission' => 'add_role'
            ],

            [
                'title' => 'Permissions',
                'url' => 'auth/permissions.php',
                'icon' => 'bi bi-key',
                'permission' => 'view_permissions'
            ],
        ]
    ],

    // Category Section
    [
        'title' => 'Categories',
        'icon'  => 'bi bi-tags',
        'permission' => 'view_categories_menu',
        'submenu' => [
            [
                'title' => 'All Categories',
                'url'   => 'category/index.php',
                'icon'  => 'bi bi-card-list',
                'permission' => 'view_categories'
            ],
            [
                'title' => 'Add Category',
                'url'   => 'category/add.php',
                'icon'  => 'bi bi-plus-circle',
                'permission' => 'add_category'
            ],
            [
                'title' => 'Edit Category',
                'url'   => 'category/edit.php',
                'icon'  => 'bi bi-pencil-square',
                'hidden' => true,
                'permission' => 'edit_category'
            ]
        ]
    ],

    // Supplier Section
    [
        'title' => 'Suppliers',
        'icon' => 'bi bi-truck',
        'permission' => 'view_suppliers_menu',
        'submenu' => [
            [
                'title' => 'All Suppliers',
                'url' => 'supplier/index.php',
                'icon' => 'bi bi-list',
                'permission' => 'view_suppliers'
            ],
            [
                'title' => 'Add Supplier',
                'url' => 'supplier/add.php',
                'icon' => 'bi bi-plus-circle',
                'permission' => 'add_supplier'
            ],
            [
                'title' => 'Edit Supplier',
                'url'   => 'supplier/edit.php',
                'icon'  => 'bi bi-pencil-square',
                'hidden' => true,
                'permission' => 'edit_supplier'
            ]
        ]
    ],

    // Product Section
    [
        'title' => 'Products',
        'icon' => 'bi bi-box-seam',
        'permission' => 'view_products_menu',
        'submenu' => [
            [
                'title' => 'All Products',
                'url' => 'product/index.php',
                'icon' => 'bi bi-list',
                'permission' => 'view_products'
            ],
            [
                'title' => 'Add Product',
                'url' => 'product/add.php',
                'icon' => 'bi bi-plus-circle',
                'permission' => 'add_product'
            ],
            [
                'title' => 'Edit Product',
                'url'   => 'product/edit.php',
                'icon'  => 'bi bi-pencil-square',
                'hidden' => true,
                'permission' => 'edit_product'
            ],
            [
                'title' => 'Stock Overview',
                'url' => 'product/stock.php',
                'icon' => 'bi bi-bar-chart-line-fill',
                'permission' => 'view_stock_overview'
            ]
        ]
    ],

    // Purchases Section
    [
        'title' => 'Purchases',
        'icon'  => 'bi bi-cart-check',
        'permission' => 'view_purchases_menu',
        'submenu' => [
            [
                'title' => 'All Purchases',
                'url' => 'purchase/index.php',
                'icon' => 'bi bi-list-ul',
                'permission' => 'view_all_purchases'
            ],
            [
                'title' => 'My Purchases',
                'url' => 'purchase/my-purchases.php',
                'icon' => 'bi bi-person-check',
                'permission' => 'view_my_purchases'
            ],
            [
                'title' => 'All Purchase Receipts',
                'url' => 'purchase/all-receipt.php',
                'icon' => 'bi bi-receipt',
                'permission' => 'view_all_purchase_receipts'
            ],
            [
                'title' => 'My Purchase Receipts',
                'url' => 'purchase/my-receipts.php',
                'icon' => 'bi bi-person-lines-fill',
                'permission' => 'view_my_purchase_receipts'
            ],
            [
                'title' => 'Receipts',
                'url' => 'purchase/receipt.php',
                'icon' => 'bi bi-receipt',
                'hidden' => true,
                'permission' => 'view_purchase_receipt'
            ],
            [
                'title' => 'Add Purchase',
                'url' => 'purchase/add.php',
                'icon' => 'bi bi-plus-circle',
                'permission' => 'add_purchase'
            ],
        ]
    ],

    // Sales Section
    [
        'title' => 'Sales',
        'icon'  => 'bi bi-bag-check',
        'permission' => 'view_sales_menu',
        'submenu' => [
            [
                'title' => 'All Sales',
                'url' => 'sales/index.php',
                'icon' => 'bi bi-list-ul',
                'permission' => 'view_all_sales'
            ],
            [
                'title' => 'My Sales',
                'url' => 'sales/my-sales.php',
                'icon' => 'bi bi-person-check',
                'permission' => 'view_my_sales'
            ],
            [
                'title' => 'All Sales Receipts',
                'url' => 'sales/all-receipt.php',
                'icon' => 'bi bi-receipt',
                'permission' => 'view_all_sales_receipts'
            ],
            [
                'title' => 'My Sales Receipts',
                'url' => 'sales/my-receipts.php',
                'icon' => 'bi bi-person-lines-fill',
                'permission' => 'view_my_sales_receipts'
            ],
            [
                'title' => 'Receipts',
                'url' => 'sales/receipt.php',
                'icon' => 'bi bi-receipt',
                'hidden' => true,
                'permission' => 'view_sales_receipt'
            ],
            [
                'title' => 'Add Sale',
                'url' => 'sales/add.php',
                'icon' => 'bi bi-plus-circle',
                'permission' => 'add_sale'
            ],
        ]
    ],

    [
        'title' => 'All Receipts',
        'icon' => 'bi bi-receipt',
        'url' => 'receipts/index.php',
        'permission' => 'view_all_receipts'
    ],

    [
        'title' => 'All My Receipts',
        'icon' => 'bi bi-person-badge',
        'url' => 'receipts/my-receipts.php',
        'permission' => 'view_all_my_receipts'
    ],


    // Reports Section
    [
        'title' => 'Reports',
        'icon'  => 'bi bi-graph-up-arrow',
        'permission' => 'view_reports_menu',
        'submenu' => [
            [
                'title' => 'Stock Report',
                'url'   => 'reports/stock.php',
                'icon'  => 'bi bi-box-seam',
                'permission' => 'view_stock_report'
            ],
            [
                'title' => 'Low Stock',
                'url'   => 'reports/low_stock.php',
                'icon'  => 'bi bi-arrow-down-short',
                'permission' => 'view_low_stock'
            ],
            [
                'title' => 'Sales Report',
                'url'   => 'reports/sales.php',
                'icon'  => 'bi bi-cash-coin',
                'permission' => 'view_sales_report'
            ],
            [
                'title' => 'Purchase Report',
                'url'   => 'reports/purchases.php',
                'icon'  => 'bi bi-cart-check',
                'permission' => 'view_purchase_report'
            ],
        ]
    ],

    [
        'title' => 'Logout',
        'icon' => 'bi bi-box-arrow-right',
        'url' => 'auth/logout.php',
    ]
];

// Current page detection
$currentPath = $_SERVER['PHP_SELF'];           // e.g. /Sass_Inventory_Management/category/index.php
$currentFile = basename($currentPath);         // e.g. index.php
$currentDir  = basename(dirname($currentPath)); // e.g. category

$parentTitle = null;
$childTitle  = null;

foreach ($sidebarMenu as $item) {
    // If it has submenu
    if (!empty($item['submenu'])) {
        foreach ($item['submenu'] as $sub) {
            $subFile = basename($sub['url']);
            $subDir  = basename(dirname($sub['url']));

            if (
                ($subFile === $currentFile && $subDir === $currentDir) ||
                ($subFile === "index.php" && $subDir === $currentDir && $currentFile === "index.php")
            ) {
                $parentTitle = $item['title'];
                $childTitle  = $sub['title'];
                break 2;
            }
        }
    }

    // Single item menu
    if (!empty($item['url'])) {
        $itemFile = basename($item['url']);
        $itemDir  = basename(dirname($item['url']));

        if (
            ($itemFile === $currentFile && $itemDir === $currentDir) ||
            ($itemFile === "index.php" && $itemDir === $currentDir && $currentFile === "index.php")
        ) {
            $childTitle = $item['title'];
            break;
        }
    }
}

// Fallback if not found
if (!$childTitle) $childTitle = "Dashboard";
?>


<nav class="app-header navbar navbar-expand bg-body shadow-sm">
    <div class="container-fluid">

        <!-- Left Controls -->
        <ul class="navbar-nav align-items-center">

            <!-- Sidebar toggle -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="sidebar" title="Toggle Sidebar">
                    <img
                        src="<?= $Project_URL ?>assets/Dashboard/Dashboard_Nav/list.png"
                        alt="Menu"
                        width="24"
                        height="24"
                        class="img-fluid">
                </a>
            </li>

            <!-- Breadcrumb -->
            <li class="nav-item ms-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <?php if ($parentTitle): ?>
                            <li class="breadcrumb-item"><?= $parentTitle ?></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active fw-semibold"><?= $childTitle ?></li>
                    </ol>
                </nav>
            </li>

        </ul>

        <!-- Right Controls -->
        <ul class="navbar-nav ms-auto align-items-center">

            <!-- Fullscreen -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen" title="Fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
                </a>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                    <img
                        src="<?= $Project_URL ?>assets/User_Placeholder.jpg"
                        class="user-image rounded-circle shadow-sm me-2"
                        width="32"
                        height="32"
                        alt="User">
                    <span class="d-none d-md-inline fw-semibold">Sazzadul Islam</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg">

                    <!-- User Header -->
                    <li class="user-header text-bg-primary text-center">
                        <img
                            src="<?= $Project_URL ?>assets/User_Placeholder.jpg"
                            class="rounded-circle shadow-sm mb-2"
                            width="80"
                            height="80"
                            alt="User">
                        <p class="mb-0">
                            <strong>Sazzadul Islam</strong> – Web Developer
                        </p>
                        <small>Member since Nov 2023</small>
                    </li>

                    <!-- User Stats -->
                    <li class="user-body py-2 bg-light">
                        <div class="row text-center">
                            <div class="col-4"><a href="#" class="text-decoration-none">Followers</a></div>
                            <div class="col-4"><a href="#" class="text-decoration-none">Sales</a></div>
                            <div class="col-4"><a href="#" class="text-decoration-none">Friends</a></div>
                        </div>
                    </li>

                    <!-- Footer -->
                    <li class="user-footer d-flex justify-content-between">
                        <a href="#" class="btn btn-outline-primary btn-flat">Profile</a>
                        <a href="#" class="btn btn-outline-danger btn-flat">Sign Out</a>
                    </li>

                </ul>
            </li>
        </ul>
    </div>
</nav>