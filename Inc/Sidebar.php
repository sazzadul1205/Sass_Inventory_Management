<?php
include_once 'link.php';

$role = $_SESSION['role_id'];

// Returns the current project root folder (e.g., /Sass_Inventory_Management)
function getProjectRoot()
{
  $parts = explode('/', trim($_SERVER['PHP_SELF'], '/'));
  return '/' . $parts[0] . '/';
}

$projectRoot = getProjectRoot();
$Project_URL = $projectRoot; // use this as the base URL


// Sidebar menu structure
$sidebarMenu = [
  ['title' => 'Dashboard', 'icon' => 'bi bi-speedometer2', 'url' => 'index.php'],

  // Authentication Section
  [
    'title' => 'Authentication',
    'icon' => 'bi bi-person-badge',
    'submenu' => [
      [
        'title' => 'Users',
        'url' => 'auth/users.php',
        'icon' => 'bi bi-people'
      ],
      [
        'title' => 'Add User',
        'url' => 'auth/add_user.php',
        'icon' => 'bi bi-person-plus'
      ],
      [
        'title' => 'Edit User',
        'url' => 'auth/edit_user.php',
        'hidden' => true,
        'icon' => 'bi bi-pencil-square'
      ],


      [
        'title' => 'Roles',
        'url' => 'auth/roles.php',
        'icon' => 'bi bi-shield-lock'
      ],
      [
        'title' => 'Edit Role',
        'url' => 'auth/edit_role.php',
        'hidden' => true,
        'icon' => 'bi bi-pencil-square'
      ],
      [
        'title' => 'Add Role',
        'url' => 'auth/add_role.php',
        'hidden' => true,
        'icon' => 'bi bi-plus-square'
      ],


      [
        'title' => 'Permissions',
        'url' => 'auth/permissions.php',
        'icon' => 'bi bi-key'
      ],
    ]
  ],

  // Category Section
  [
    'title' => 'Categories',
    'icon'  => 'bi bi-tags',
    'submenu' => [
      [
        'title' => 'All Categories',
        'url'   => 'category/index.php',
        'icon'  => 'bi bi-card-list'
      ],
      [
        'title' => 'Add Category',
        'url'   => 'category/add.php',
        'icon'  => 'bi bi-plus-circle'
      ],
      [
        'title' => 'Edit Category',
        'url'   => 'category/edit.php',
        'icon'  => 'bi bi-pencil-square',
        'hidden' => true,
      ]
    ]
  ],

  // Supplier Section
  [
    'title' => 'Suppliers',
    'icon' => 'bi bi-truck',
    'submenu' => [
      ['title' => 'All Suppliers', 'url' => 'supplier/index.php', 'icon' => 'bi bi-list'],
      ['title' => 'Add Supplier', 'url' => 'supplier/add.php', 'icon' => 'bi bi-plus-circle'],
      [
        'title' => 'Edit Supplier',
        'url'   => 'supplier/edit.php',
        'icon'  => 'bi bi-pencil-square',
        'hidden' => true,
      ]
    ]
  ],

  // Product Section
  [
    'title' => 'Products',
    'icon' => 'bi bi-box-seam',
    'submenu' => [
      ['title' => 'All Products', 'url' => 'product/index.php', 'icon' => 'bi bi-list'],
      ['title' => 'Add Product', 'url' => 'product/add.php', 'icon' => 'bi bi-plus-circle'],
      [
        'title' => 'Edit Product',
        'url'   => 'product/edit.php',
        'icon'  => 'bi bi-pencil-square',
        'hidden' => true,
      ],
      ['title' => 'Stock Overview', 'url' => 'product/stock.php', 'icon' => 'bi bi-bar-chart-line-fill']
    ]
  ],

  // Request Section
  // [
  //   'title' => 'Product Requests',
  //   'icon' => 'bi bi-envelope-exclamation',
  //   'submenu' => [
  //     ['title' => 'Request List', 'url' => 'requests/index.php']
  //   ]
  // ],

  // Purchase Section
  [
    'title' => 'Purchases',
    'icon'  => 'bi bi-cart-check',
    'submenu' => [
      [
        'title' => 'All Purchases',
        'url' => 'purchase/index.php',
        'icon' => 'bi bi-list-ul'
      ],
      [
        'title' => 'My Purchases',
        'url' => 'purchase/my-purchases.php',
        'icon' => 'bi bi-person-check'
      ],
      [
        'title' => 'All Purchase Receipts',
        'url' => 'purchase/all-receipt.php',
        'icon' => 'bi bi-receipt'
      ],
      [
        'title' => 'My Purchase Receipts',
        'url' => 'purchase/my-receipts.php',
        'icon' => 'bi bi-person-lines-fill'
      ],
      [
        'title' => 'Receipts',
        'url' => 'purchase/receipt.php',
        'icon' => 'bi bi-receipt',
        'hidden' => true
      ],
      [
        'title' => 'Add Purchase',
        'url' => 'purchase/add.php',
        'icon' => 'bi bi-plus-circle'
      ],
    ]
  ],


  // Sales Section
  [
    'title' => 'Sales',
    'icon'  => 'bi bi-bag-check', // different icon from purchases
    'submenu' => [
      [
        'title' => 'All Sales',
        'url' => 'sales/index.php',
        'icon' => 'bi bi-list-ul'
      ],
      [
        'title' => 'My Sales',
        'url' => 'sales/my-sales.php',
        'icon' => 'bi bi-person-check'
      ],
      [
        'title' => 'All Sales Receipts',
        'url' => 'sales/all-receipt.php',
        'icon' => 'bi bi-receipt'
      ],
      [
        'title' => 'My Sales Receipts',
        'url' => 'sales/my-receipts.php',
        'icon' => 'bi bi-person-lines-fill'
      ],
      [
        'title' => 'Receipts',
        'url' => 'sales/receipt.php',
        'icon' => 'bi bi-receipt',
        'hidden' => true
      ],
      [
        'title' => 'Add Sale',
        'url' => 'sales/add.php',
        'icon' => 'bi bi-plus-circle'
      ],
    ]
  ],

  [
    'title' => 'All Receipts',
    'icon' => 'bi bi-box-arrow-right',
    'url' => 'receipts/index.php'
  ],
  // [
  //   'title' => 'Inventory Adjustments',
  //   'icon' => 'bi bi-pencil-square',
  //   'submenu' => [
  //     ['title' => 'Adjustment History', 'url' => 'adjustments/index.php'],
  //     ['title' => 'Add Adjustment', 'url' => 'adjustments/add.php']
  //   ]
  // ],

  [
    'title' => 'Reports',
    'icon'  => 'bi bi-graph-up-arrow',
    'submenu' => [
      [
        'title' => 'Stock Report',
        'url'   => 'reports/stock.php',
        'icon'  => 'bi bi-box-seam'
      ],
      [
        'title' => 'Low Stock',
        'url'   => 'reports/low_stock.php',
        'icon'  => 'bi bi-arrow-down-short'
      ],
      [
        'title' => 'Sales Report',
        'url'   => 'reports/sales.php',
        'icon'  => 'bi bi-cash-coin'
      ],
      [
        'title' => 'Purchase Report',
        'url'   => 'reports/purchases.php',
        'icon'  => 'bi bi-cart-check'
      ],
    ]
  ],

  // [
  //   'title' => 'System Settings',
  //   'icon' => 'bi bi-gear',
  //   'submenu' => [
  //     ['title' => 'Company Info', 'url' => 'settings/company.php'],
  //     ['title' => 'Theme Settings', 'url' => 'settings/theme.php'],
  //     ['title' => 'Email Settings', 'url' => 'settings/email.php'],
  //     ['title' => 'Backup & Restore', 'url' => 'settings/backup.php']
  //   ]
  // ],
  // ['title' => 'Activity Logs', 'icon' => 'bi bi-journal-text', 'url' => 'logs/index.php'],
  [
    'title' => 'Logout',
    'icon' => 'bi bi-box-arrow-right',
    'url' => 'auth/logout.php'
  ]
];

// Render sidebar without active/open classes
?>

<?php
// Returns the current relative path from project root
function getCurrentRelativePath()
{
  $current = $_SERVER['PHP_SELF']; // e.g. /Sass_Inventory_Management/category/index.php
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

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <div class="sidebar-brand">
    <a href="<?= $Project_URL; ?>index.php" class="brand-link">
      <img src="<?= $Project_URL; ?>assets/Dashboard/Website_logo.png" alt="Logo">
    </a>
  </div>

  <div class="sidebar-wrapper">
    <nav>
      <ul class="nav sidebar-menu flex-column" id="sidebarAccordion">

        <?php foreach ($sidebarMenu as $index => $menu): ?>
          <?php if (isset($menu['submenu'])): ?>
            <?php
            $collapseId = "collapseMenu$index";
            $anyActive = false;
            foreach ($menu['submenu'] as $sub) {
              if (isActivePage($sub['url'])) $anyActive = true;
            }
            ?>
            <li class="nav-item">
              <a class="nav-link d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse"
                href="#<?= $collapseId ?>"
                role="button"
                aria-expanded="<?= $anyActive ? 'true' : 'false' ?>"
                aria-controls="<?= $collapseId ?>">

                <span class="d-inline-flex align-items-center gap-2">
                  <i class="nav-icon <?= $menu['icon'] ?>"></i>
                  <?= $menu['title'] ?>
                </span>
                <i class="bi bi-chevron-down collapse-arrow rotate-0"></i>
              </a>

              <div class="collapse <?= $anyActive ? 'show' : '' ?>" id="<?= $collapseId ?>" data-bs-parent="#sidebarAccordion">
                <ul class="nav flex-column ms-3">
                  <?php foreach ($menu['submenu'] as $sub): ?>
                    <?php
                    $isActive = isActivePage($sub['url']);
                    if (!empty($sub['hidden']) && !$isActive) continue; // skip hidden unless active
                    $iconClass = !empty($sub['icon']) ? $sub['icon'] : 'bi bi-circle';
                    ?>
                    <li class="nav-item">
                      <a href="<?= $Project_URL . $sub['url'] ?>"
                        class="nav-link <?= $isActive ? 'active-page' : '' ?>">
                        <i class="nav-icon <?= $iconClass ?>"></i>
                        <p><?= $sub['title'] ?></p>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </li>

          <?php else: ?>
            <li class="nav-item">
              <a href="<?= $Project_URL . $menu['url'] ?>" class="nav-link <?= isActivePage($menu['url']) ? 'active-page' : '' ?>">
                <i class="nav-icon <?= $menu['icon'] ?>"></i>
                <p><?= $menu['title'] ?></p>
              </a>
            </li>
          <?php endif; ?>
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