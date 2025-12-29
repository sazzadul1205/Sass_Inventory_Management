<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Database Not Connected</title>

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef2f7, #dbe4f0);
        }

        .error-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1rem;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">

                <div class="card error-card p-4 text-center">
                    <div class="card-body">

                        <div class="icon-circle">
                            <i class="bi bi-database-x"></i>
                        </div>

                        <h3 class="fw-bold text-danger mb-3">
                            Database Not Connected
                        </h3>

                        <p class="text-muted mb-4">
                            The application was unable to establish a connection to the database.
                            Please verify your database credentials, ensure the database server is
                            running, or create the required database.
                        </p>

                        <div class="d-grid gap-2 mb-4">
                            <a
                                href="DB/sass_inventory.sql"
                                class="btn btn-outline-primary"
                                download>
                                <i class="bi bi-download me-2"></i>
                                Download Example Database
                            </a>

                            <a
                                href="../auth/login.php"
                                class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-2"></i>
                                Recheck Connection
                            </a>
                        </div>

                        <div class="alert alert-light border small mb-0">
                            <strong>Tip:</strong> Check <code>db_config.php</code> for correct
                            host, username, password, and database name.
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>