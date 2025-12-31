<!-- Sidebar inc -->
<?php
include_once 'link.php';

// Check if user is logged in
$role = $_SESSION['role_id'];
$conn = connectDB();

// Get user permissions
$USER_PERMISSIONS = getUserPermissions($role, $conn);

// Returns the current project root folder (e.g., /Sass_Inventory_Management)
function getProjectRoot()
{
  $parts = explode('/', trim($_SERVER['PHP_SELF'], '/'));
  return '/' . $parts[0] . '/';
}

$projectRoot = getProjectRoot();
$Project_URL = $projectRoot; // use this as the base URL

// Sidebar Menu
$sidebarMenu = [
  [
    'title' => 'Dashboard',
    'icon' => 'bi bi-speedometer2',
    'url' => 'index.php',
  ],

  // Authentication Section
  [
    'title' => 'Manage Users',
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
        'title' => 'Add Purchase',
        'url' => 'purchase/add.php',
        'icon' => 'bi bi-plus-circle',
        'permission' => 'add_purchase'
      ],
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
        'title' => 'Purchase Returns',
        'url' => 'purchase/purchase_return.php',
        'icon' => 'bi bi-arrow-counterclockwise',
        'hidden' => true,
        'permission' => 'product_return'
      ],
      [
        'title' => 'Return Purchase List',
        'url'   => 'purchase/return-list.php',
        'icon'  => 'bi bi-list-check',
        'permission' => 'add_purchase'
      ]
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

// Returns the current relative path from project root
function getCurrentRelativePath()
{
  $current = $_SERVER['PHP_SELF'];
  $parts = explode('/', trim($current, '/'));
  $projectRoot = '/' . $parts[0] . '/';
  return str_replace($projectRoot, '', $current);
}

// Check if a link matches current page
function isActivePage($url)
{
  return getCurrentRelativePath() === $url;
}
?>

<style>
  .rotate-180 {
    transform: rotate(180deg);
    transition: transform 0.25s ease;
  }

  .rotate-0 {
    transform: rotate(0deg);
    transition: transform 0.25s ease;
  }

  .active-page {
    font-weight: 600;
    background-color: #4a90e2;
    /* medium blue, eye-catching but not harsh */
    border-radius: 4px;
  }

  .active-page>p {
    color: #ffffff;
    /* white text for strong contrast */
  }
</style>


<!-- Sidebar -->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

  <!-- Sidebar Header -->
  <div class="sidebar-brand">
    <a href="<?= $Project_URL; ?>index.php" class="brand-link">
      <img src="<?= $Project_URL; ?>assets/Dashboard/Website_logo.png" alt="Logo">
    </a>
  </div>

  <!-- Sidebar Menu -->
  <div class="sidebar-wrapper">
    <nav>
      <ul class="nav sidebar-menu flex-column" id="sidebarAccordion">

        <?php foreach ($sidebarMenu as $index => $menu): ?>

          <?php
          $hasSubmenu = isset($menu['submenu']);

          if ($hasSubmenu) {
            // Only count submenus that are visible (not hidden) AND allowed by permission
            $visibleSubmenus = array_filter($menu['submenu'], function ($sub) use ($USER_PERMISSIONS) {
              $hasPermission = empty($sub['permission']) || in_array($sub['permission'], $USER_PERMISSIONS);
              // Allow hidden items only if currently active
              $notHidden = empty($sub['hidden']) || $sub['hidden'] !== true || isActivePage($sub['url']);
              return $hasPermission && $notHidden;
            });

            if (empty($visibleSubmenus)) continue; // Skip if no visible submenus

            if (count($visibleSubmenus) === 1) {
              // Only 1 visible submenu, show it as normal link
              $sub = array_values($visibleSubmenus)[0];
          ?>
              <li class="nav-item">
                <a href="<?= $Project_URL . $sub['url'] ?>" class="nav-link <?= isActivePage($sub['url']) ? 'active-page' : '' ?>">
                  <i class="nav-icon <?= $sub['icon'] ?>"></i>
                  <p><?= $sub['title'] ?></p>
                </a>
              </li>
            <?php
            } else {
              // Multiple visible submenus → collapsible menu
              $collapseId = "collapseMenu$index";
              $anyActive = false;
              foreach ($visibleSubmenus as $sub) {
                if (isActivePage($sub['url'])) $anyActive = true;
              }
            ?>
              <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                  href="#<?= $collapseId ?>" aria-expanded="<?= $anyActive ? 'true' : 'false' ?>"
                  aria-controls="<?= $collapseId ?>">
                  <span class="d-inline-flex align-items-center gap-2">
                    <i class="nav-icon <?= $menu['icon'] ?>"></i>
                    <?= $menu['title'] ?>
                  </span>
                  <i class="bi bi-chevron-down collapse-arrow rotate-0"></i>
                </a>
                <div class="collapse <?= $anyActive ? 'show' : '' ?>" id="<?= $collapseId ?>" data-bs-parent="#sidebarAccordion">
                  <ul class="nav flex-column ms-3">
                    <?php foreach ($visibleSubmenus as $sub): ?>
                      <li class="nav-item">
                        <a href="<?= $Project_URL . $sub['url'] ?>" class="nav-link <?= isActivePage($sub['url']) ? 'active-page' : '' ?>">
                          <i class="nav-icon <?= $sub['icon'] ?>"></i>
                          <p><?= $sub['title'] ?></p>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </li>
            <?php
            }
          } else {
            // No submenu → check parent permission
            if (!empty($menu['permission']) && !in_array($menu['permission'], $USER_PERMISSIONS)) {
              continue;
            }
            ?>
            <li class="nav-item">
              <a href="<?= $Project_URL . $menu['url'] ?>" class="nav-link <?= isActivePage($menu['url']) ? 'active-page' : '' ?>">
                <i class="nav-icon <?= $menu['icon'] ?>"></i>
                <p><?= $menu['title'] ?></p>
              </a>
            </li>
          <?php } ?>

        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</aside>


<script>
  document.addEventListener('DOMContentLoaded', () => {
    const triggers = document.querySelectorAll('[data-bs-toggle="collapse"]');

    triggers.forEach(trigger => {
      const target = document.querySelector(trigger.getAttribute('href'));
      const arrow = trigger.querySelector('.collapse-arrow');
      if (!target || !arrow) return;

      target.addEventListener('show.bs.collapse', () => {
        arrow.classList.remove('rotate-0');
        arrow.classList.add('rotate-180');
      });

      target.addEventListener('hide.bs.collapse', () => {
        arrow.classList.remove('rotate-180');
        arrow.classList.add('rotate-0');
      });
    });

    // Auto-expand the parent of the active page
    const activeItem = document.querySelector('.active-page');
    if (activeItem) {
      const collapseDiv = activeItem.closest('.collapse');
      if (collapseDiv) {
        const collapse = new bootstrap.Collapse(collapseDiv, {
          toggle: false
        });
        collapse.show();

        const arrow = document.querySelector('[href="#' + collapseDiv.id + '"] .collapse-arrow');
        if (arrow) {
          arrow.classList.remove('rotate-0');
          arrow.classList.add('rotate-180');
        }
      }
    }
  });
</script>