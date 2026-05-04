<?php
// Database connection aur counts fetch krna
include('backend/config.php');

// Pending Expenses Count
$p_query = "SELECT COUNT(*) as total FROM manager_expenses WHERE status = 'pending'";
$p_result = mysqli_query($conn, $p_query);
$pending_count = mysqli_fetch_assoc($p_result)['total'];

// Today's Total Expense
// Today's Total Expense Query
$today = date('Y-m-d');
$e_query = "SELECT SUM(amount) as total FROM manager_expenses WHERE expense_date = '$today' AND status = 'Approved'";
$e_result = mysqli_query($conn, $e_query);
$today_expense = mysqli_fetch_assoc($e_result)['total'] ?? 0;

// URL parameter se content handle krne ke liye logic
$p = isset($_GET['p']) ? $_GET['p'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Plus - Sleek Admin Panel</title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm bg-white">
        <div class="container-fluid p-0">
            <button class="btn d-lg-none me-3 text-dark" id="sidebarToggle">
                <i class="ri-menu-2-line ri-xl"></i>
            </button>
            <a class="navbar-brand fw-bold text-primary ms-3" href="index.php">LABOUR PLUS</a>
            
            <div class="d-flex align-items-center ms-auto me-3">
                <!-- Notifications -->
                <div class="position-relative me-4 d-none d-md-block text-secondary cursor-pointer" 
                     onclick="loadContent('components/expenses/pending-expenses.php', event)">
                    <i class="ri-notification-3-line ri-lg"></i>
                    <?php if($pending_count > 0): ?>
                        <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;">
                            <?php echo $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- User Profile -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle text-decoration-none d-flex align-items-center p-0" type="button" data-bs-toggle="dropdown">
                        <img src="https://api.dicebear.com/8.x/notionists/svg?seed=Felix" alt="avatar" class="rounded-circle me-2" width="35">
                        <span class="fw-medium d-none d-sm-block">Super Admin</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                        <li><a class="dropdown-item py-2" href="#"><i class="ri-settings-3-line me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider opacity-50"></li>
                        <li><a class="dropdown-item text-danger py-2" href="../auth/logout.php"><i class="ri-logout-box-r-line me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Navigation -->
    <div id="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link" href="javascript:void(0)" onclick="loadContent('components/dashboard/dashboard.php', event)">
                <i class="ri-dashboard-fill me-2"></i> Dashboard
            </a>

            <!-- Regions -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#tehsilMenu">
                <i class="ri-map-2-line me-2"></i> Regions
            </a>
            <div class="collapse" id="tehsilMenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/district/district.php', event)" class="nav-link small py-1"><i class="ri-government-line me-2"></i> Districts</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/tehsil/tehsil.php', event)" class="nav-link small py-1"><i class="ri-map-pin-user-line me-2"></i> Tehsils</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/company/company.php', event)" class="nav-link small py-1"><i class="ri-building-line me-2"></i> Companies</a></li>
                </ul>
            </div>

            <!-- Employees -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#staffmenu">
                <i class="ri-team-fill me-2"></i> Employees
            </a>
            <div class="collapse" id="staffmenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/staff/staff-list.php', event)" class="nav-link small py-1"><i class="ri-user-settings-line me-2"></i> Management</a></li>
                </ul>
            </div>

            <!-- Vehicles -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#vehiclemenu">
                <i class="ri-truck-line me-2"></i> Vehicles
            </a>
            <div class="collapse" id="vehiclemenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/vehicle_type/vehicle-type.php', event)" class="nav-link small py-1">Vehicle Types</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/owner/owner.php', event)" class="nav-link small py-1">Owners</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/vehicle/vehicle.php', event)" class="nav-link small py-1">Vehicles List</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/fuel/fuel-mileage.php', event)" class="nav-link small py-1">Fuel & Mileage</a></li>
                </ul>
            </div>

            <!-- Attendance -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#Attendancemenu">
                <i class="ri-calendar-check-line me-2"></i> Attendance
            </a>
            <div class="collapse" id="Attendancemenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/attendance/attendance-list.php', event)" class="nav-link small py-1">Rental Vehicles</a></li>
                </ul>
            </div>

            <!-- Pumps -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#pumpMenu">
                <i class="ri-gas-station-fill me-2 text-danger"></i> Pumps
            </a>
            <div class="collapse" id="pumpMenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/pump/pump.php', event)" class="nav-link small py-1">Pump Details</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#categoryMenu">
                <i class="ri-price-tag-3-line me-2"></i> Setup
            </a>
            <div class="collapse" id="categoryMenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/category-type/category-type.php', event)" class="nav-link small py-1">Category Type</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/category/category.php', event)" class="nav-link small py-1">Categories</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/category/sub-category-list.php', event)" class="nav-link small py-1">Sub Categories</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/machinery/add-machinery.php', event)" class="nav-link small py-1">Machinery Setup</a></li>
                </ul>
            </div>

            <!-- Payroll -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#payrollMenu">
                <i class="ri-money-dollar-box-line me-2"></i> Payroll
            </a>
            <div class="collapse" id="payrollMenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/income/income-list.php', event)" class="nav-link small py-1">Company Income</a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/payments/manager-list.php', event)" class="nav-link small py-1">Manager Payment</a>
                    </li>
                </ul>
            </div>
            <!-- Expenses -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#expensesMenu">
                <i class="ri-money-dollar-box-line me-2"></i> Expenses
            </a>
            <div class="collapse" id="expensesMenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/expenses/manager-income.php', event)" class="nav-link small py-1">Manager Income</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/expenses/manager-expense.php', event)" class="nav-link small py-1">Manager Expenses</a></li>
                    <li><a href="javascript:void(0)" onclick="loadContent('components/salary/salary-list.php', event)" class="nav-link small py-1">Salaries</a></li>
                </ul>
            </div>

            <!-- Auth -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#authMenu">
                <i class="ri-lock-password-line me-2"></i> Security
            </a>
            <div class="collapse" id="authMenu">
                <ul class="sub-menu list-unstyled ps-4">
                    <li><a href="javascript:void(0)" onclick="loadContent('components/auth/auth-list.php', event)" class="nav-link small py-1">User's Auth</a></li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div id="main-content">
        <div class="container-fluid py-4">
            <div id="dynamic-container">
                <?php
                if (!empty($p) && file_exists($p)) {
                    include($p);
                } else {
                    include('components/dashboard/dashboard.php');
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Scripts Area -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Custom Logic Scripts -->
    <script src="assets/js/script.js"></script>
    <script src="assets/js/backend-script.js"></script>
    
    <!-- Component Scripts -->
    <script src="assets/js/owner.js"></script>
    <script src="assets/js/district.js"></script>
    <script src="assets/js/tehsil.js"></script>
    <script src="assets/js/pumps.js"></script>
    <script src="assets/js/salary.js"></script>
    <script src="assets/js/staff.js"></script>
    <script src="assets/js/manager-income.js"></script>
    <script src="assets/js/category.js"></script>
    <script src="assets/js/company.js"></script>
    <script src="assets/js/vehicles.js"></script>
    <script src="assets/js/vehicle_owners.js"></script>
    <script src="assets/js/fuel.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/attendance.js"></script>
    <script src="assets/js/manager-expenses.js"></script>
    <script src="assets/js/income.js"></script>
    <script src="assets/js/manager-payments.js"></script>



    <!-- AJAX Form Handlers -->
    <script>
        $(document).on('submit', '#addPumpForm', function(e) {
            e.preventDefault();
            let $btn = $(this).find('button[type="submit"]');
            let formData = $(this).serialize();

            $.ajax({
                url: "backend/pump/pump_process.php",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $btn.attr('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Saving...');
                },
                success: function(response) {
                    if (response.trim() === "success") {
                        Swal.fire('Success!', 'Pump register ho gaya hai.', 'success');
                        loadContent('components/pump/pump.php');
                    } else {
                        Swal.fire('Error', 'Backend: ' + response, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'AJAX connection failed.', 'error');
                },
                complete: function() {
                    $btn.attr('disabled', false).html('<i class="ri-save-3-line me-1"></i> Save Pump Record');
                }
            });
        });
    </script>

</body>
</html>