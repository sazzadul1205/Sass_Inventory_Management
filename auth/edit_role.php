<?php
session_start();
include_once __DIR__ . '/../config/db_config.php';
?>

<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Users | Sass Inventory Management System</title>
    <link rel="icon" href="<?= $Project_URL ?>assets/inventory.png" type="image/x-icon" />

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="Admin Home | Sass Inventory Management System" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="description"
        content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance." />
    <meta name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
        onload="this.media='all'" />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="<?= $Project_URL ?>/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- apexcharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />

    <!-- jsvectormap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
        integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous" />
</head>
<!--end::Head-->
<!--begin::Body-->

<?php
$conn = connectDB();

// Get role ID from query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['fail_message'] = "Invalid role ID!";
    header("Location: roles.php");
    exit;
}

$roleId = intval($_GET['id']);

// Fetch role info
$stmt = $conn->prepare("SELECT * FROM role WHERE id = ?");
$stmt->bind_param('i', $roleId);
$stmt->execute();
$roleResult = $stmt->get_result();
if ($roleResult->num_rows === 0) {
    $_SESSION['fail_message'] = "Role not found!";
    header("Location: roles.php");
    exit;
}
$role = $roleResult->fetch_assoc();
$stmt->close();

// Fetch all permissions
$permissionsResult = $conn->query("SELECT * FROM permission ORDER BY permission_name ASC");
$permissions = [];
while ($perm = $permissionsResult->fetch_assoc()) {
    $permissions[$perm['id']] = $perm['permission_name'];
}

// Fetch role's current permissions
$rolePermissionsResult = $conn->prepare("SELECT permission_id FROM role_permission WHERE role_id = ?");
$rolePermissionsResult->bind_param('i', $roleId);
$rolePermissionsResult->execute();
$rolePermissionsResultData = $rolePermissionsResult->get_result();
$assignedPermissions = [];
while ($rp = $rolePermissionsResultData->fetch_assoc()) {
    $assignedPermissions[] = $rp['permission_id'];
}
$rolePermissionsResult->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = trim($_POST['role_name']);
    $selectedPermissions = $_POST['permissions'] ?? [];

    if (!empty($roleName)) {
        // Update role name
        $stmt = $conn->prepare("UPDATE role SET role_name = ? WHERE id = ?");
        $stmt->bind_param('si', $roleName, $roleId);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            // Delete old permissions
            $stmt = $conn->prepare("DELETE FROM role_permission WHERE role_id = ?");
            $stmt->bind_param('i', $roleId);
            $stmt->execute();
            $stmt->close();

            // Insert new permissions
            foreach ($selectedPermissions as $permId) {
                $stmt = $conn->prepare("INSERT INTO role_permission (role_id, permission_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $roleId, $permId);
                $stmt->execute();
                $stmt->close();
            }

            $_SESSION['success_message'] = "Role '$roleName' updated successfully!";
            header("Location: roles.php");
            exit;
        } else {
            $_SESSION['fail_message'] = "Failed to update role!";
        }
    } else {
        $_SESSION['fail_message'] = "Role name cannot be empty!";
    }
}
?>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <!--Header-->
        <?php include_once '../Inc/Navbar.php'; ?>

        <!--Sidebar-->
        <?php include_once '../Inc/Sidebar.php'; ?>

        <!--App Main-->
        <main class="app-main">
            <!-- App Content Header -->
            <div class="app-content-header py-3 border-bottom shadow-sm bg-light">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <!-- Page Title -->
                        <h3 class="mb-0">All Roles</h3>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <!-- Add New Role Button -->
                            <a href="add_role.php" class="btn btn-primary btn-sm d-flex align-items-center px-3 py-2">
                                <i class="bi bi-plus me-1"></i> Add New Role
                            </a>
                        </div>

                    </div>
                </div>
            </div>


            <!-- Message -->
            <?php
            // Display success message if set
            if (!empty($_SESSION['success_message'])) {
                echo "
                <div id='successMsg' class='alert alert-success' style='position:relative; z-index:9999;'>
                  {$_SESSION['success_message']}
                </div>";

                unset($_SESSION['success_message']); // Remove so it shows only once
            }

            // Display fail message if set
            if (!empty($_SESSION['fail_message'])) {
                echo "
                <div id='failMsg' class='alert alert-danger' style='position:relative; z-index:9999;'>
                  {$_SESSION['fail_message']}
                </div>";

                unset($_SESSION['fail_message']); // Remove so it shows only once
            }
            ?>

            <!-- App Content Body -->
            <div class="app-content-body mt-3">
                <div class="container-fluid">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="role_name" name="role_name" required
                                value="<?= htmlspecialchars($role['role_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign Permissions</label>
                            <div class="row">
                                <?php foreach ($permissions as $permId => $permName): ?>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="<?= $permId ?>" id="perm<?= $permId ?>"
                                                <?= in_array($permId, $assignedPermissions) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="perm<?= $permId ?>">
                                                <?= htmlspecialchars($permName) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Role</button>
                        <a href="roles.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>

        </main>

        <!--Footer-->
        <?php include_once '../Inc/Footer.php'; ?>


    </div>

    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="./js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

            // Disable OverlayScrollbars on mobile devices to prevent touch interference
            const isMobile = window.innerWidth <= 992;

            if (
                sidebarWrapper &&
                OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
                !isMobile
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!-- OPTIONAL SCRIPTS -->

    <!-- sortablejs -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" crossorigin="anonymous"></script>

    <!-- sortablejs -->
    <script>
        new Sortable(document.querySelector('.connectedSortable'), {
            group: 'shared',
            handle: '.card-header',
        });

        const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
        cardHeaders.forEach((cardHeader) => {
            cardHeader.style.cursor = 'move';
        });
    </script>

    <!-- apex charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
        integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>

    <!-- ChartJS -->
    <script>
        // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
        // IT'S ALL JUST JUNK FOR DEMO
        // ++++++++++++++++++++++++++++++++++++++++++

        const sales_chart_options = {
            series: [{
                    name: 'Digital Goods',
                    data: [28, 48, 40, 19, 86, 27, 90],
                },
                {
                    name: 'Electronics',
                    data: [65, 59, 80, 81, 56, 55, 40],
                },
            ],
            chart: {
                height: 300,
                type: 'area',
                toolbar: {
                    show: false,
                },
            },
            legend: {
                show: false,
            },
            colors: ['#0d6efd', '#20c997'],
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth',
            },
            xaxis: {
                type: 'datetime',
                categories: [
                    '2023-01-01',
                    '2023-02-01',
                    '2023-03-01',
                    '2023-04-01',
                    '2023-05-01',
                    '2023-06-01',
                    '2023-07-01',
                ],
            },
            tooltip: {
                x: {
                    format: 'MMMM yyyy',
                },
            },
        };

        const sales_chart = new ApexCharts(
            document.querySelector('#revenue-chart'),
            sales_chart_options,
        );
        sales_chart.render();
    </script>

    <!-- jsvectormap -->
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
        integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
        integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY=" crossorigin="anonymous"></script>

    <!-- jsvectormap -->
    <script>
        // World map by jsVectorMap
        new jsVectorMap({
            selector: '#world-map',
            map: 'world',
        });

        // Sparkline charts
        const option_sparkline1 = {
            series: [{
                data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
            }, ],
            chart: {
                type: 'area',
                height: 50,
                sparkline: {
                    enabled: true,
                },
            },
            stroke: {
                curve: 'straight',
            },
            fill: {
                opacity: 0.3,
            },
            yaxis: {
                min: 0,
            },
            colors: ['#DCE6EC'],
        };

        const sparkline1 = new ApexCharts(document.querySelector('#sparkline-1'), option_sparkline1);
        sparkline1.render();

        const option_sparkline2 = {
            series: [{
                data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
            }, ],
            chart: {
                type: 'area',
                height: 50,
                sparkline: {
                    enabled: true,
                },
            },
            stroke: {
                curve: 'straight',
            },
            fill: {
                opacity: 0.3,
            },
            yaxis: {
                min: 0,
            },
            colors: ['#DCE6EC'],
        };

        const sparkline2 = new ApexCharts(document.querySelector('#sparkline-2'), option_sparkline2);
        sparkline2.render();

        const option_sparkline3 = {
            series: [{
                data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
            }, ],
            chart: {
                type: 'area',
                height: 50,
                sparkline: {
                    enabled: true,
                },
            },
            stroke: {
                curve: 'straight',
            },
            fill: {
                opacity: 0.3,
            },
            yaxis: {
                min: 0,
            },
            colors: ['#DCE6EC'],
        };

        const sparkline3 = new ApexCharts(document.querySelector('#sparkline-3'), option_sparkline3);
        sparkline3.render();
    </script>

    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "language": {
                    "emptyTable": "No users found"
                }
            });
        });
    </script>

    <script>
        setTimeout(() => {
            const msg = document.getElementById('successMsg');
            if (msg) {
                msg.style.transition = "opacity 0.5s";
                msg.style.opacity = "0";
                setTimeout(() => msg.remove(), 500);
            }
        }, 3000); // 3 seconds
    </script>

    <!--end::Script-->
</body>
<!--end::Body-->

</html>