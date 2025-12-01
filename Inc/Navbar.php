<?php include_once 'link.php' ?>

<nav class="app-header navbar navbar-expand bg-body shadow-sm">
    <div class="container-fluid">

        <!-- Left Controls -->
        <ul class="navbar-nav">
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