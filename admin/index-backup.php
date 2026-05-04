<?php
session_start();

// 1. Agar user login NAHI hai, ya uska role 'admin' NAHI hai
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {

    // Sab kuch destroy karo taake koi purana data na bachay
    session_unset();
    session_destroy();

    // Seedha Login page par phenk do
    header("Location: ../auth/login.php?error=unauthorized_access");
    exit();
}

// Browser ko purana page cache se dikhane se rokein (Very Important)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Yahan se niche aapka admin panel ka asli code shuru hoga
include('backend/config.php');
// index.php ke bilkul start mein

// Check karein ke URL mein 'p' parameter hai ya nahi
$p = isset($_GET['p']) ? $_GET['p'] : '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Plus - Sleek Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid p-0">
            <button class="btn d-lg-none me-3 text-dark" id="sidebarToggle"><i
                    class="ri-menu-2-line ri-xl"></i></button>
            <a class="navbar-brand" href="#">LABOUR PLUS</a>
            <div class="d-flex align-items-center ms-auto">
                <div class="position-relative me-4 d-none d-md-block text-secondary">
                    <i class="ri-notification-3-line ri-lg"></i>
                    <span class="badge rounded-pill bg-danger badge-notification">3</span>
                </div>
                <div class="dropdown">
                    <button
                        class="btn btn-link text-dark dropdown-toggle text-decoration-none d-flex align-items-center p-0"
                        type="button" data-bs-toggle="dropdown">
                        <img src="https://api.dicebear.com/8.x/notionists/svg?seed=Felix" alt="avatar"
                            class="rounded-circle me-2" width="35">
                        <span class="fw-medium d-none d-sm-block">Super Admin</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                        <li><a class="dropdown-item py-2" href="#"><i class="ri-settings-3-line me-2"></i> Settings</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider opacity-50">
                        </li>
                        <li><a class="dropdown-item text-danger py-2" href="../auth/logout.php"><i class="ri-logout-box-r-line me-2"></i>
                                Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div id="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link" href="javascript:void(0)" onclick="loadContent('components/dashboard/dashboard.php', event)">
                <i class="ri-dashboard-fill icon-dashboard"></i> Dashboard
            </a>

            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#tehsilMenu">
                <i class="ri-map-2-line icon-tehsil"></i> Regions
            </a>
            <div class="collapse" id="tehsilMenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/district/district.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-government-line me-2 small-icon"></i> Districts
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/tehsil/tehsil.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-map-pin-user-line me-2 small-icon"></i> Tehsils
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/company/company.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-map-pin-user-line me-2 small-icon"></i> Companies
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#staffmenu">
                <i class="ri-team-fill icon-staff"></i> Employees
            </a>
            <div class="collapse" id="staffmenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/staff/staff-list.php', event)">
                            <i class="ri-user-settings-line me-2"></i> Employees Management
                        </a>
                    </li>

                    <!-- <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/salary/salary-list.php', event)">
                            <i class="ri-bank-card-line me-2"></i> Salaries
                        </a>
                    </li> -->
                </ul>
            </div>
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#vehiclemenu">
                <i class="ri-team-fill icon-staff"></i> Vehicles
            </a>
            <div class="collapse" id="vehiclemenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/vehicle_type/vehicle-type.php', event)">
                            <i class="ri-user-settings-line me-2"></i> Vehicles Type
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/owner/owner.php', event)">
                            <i class="ri-user-settings-line me-2"></i> Vehicles Owners
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/vehicle/vehicle.php', event)">
                            <i class="ri-user-settings-line me-2"></i> Vehicles
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/fuel/fuel-mileage.php', event)">
                            <i class="ri-user-settings-line me-2"></i> Fuel & Milage
                        </a>
                    </li>

                </ul>
            </div>
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#Attendancemenu">
                <i class="ri-team-fill icon-staff"></i> Vehicles Attendance
            </a>
            <div class="collapse" id="Attendancemenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/attendance/attendance-list.php', event)">
                            <i class="ri-user-settings-line me-2"></i> Rental Vehicles
                        </a>
                    </li>
                </ul>
            </div>
            <!-- <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#financeMenu">
                <i class="ri-bank-card-2-line icon-finance"></i> Finance / Payroll
            </a>
            <div class="collapse" id="financeMenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/salary/manager.php', event)">
                            <i class="ri-bank-card-line me-2"></i> Managers
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/salary/driver.php', event)">
                            <i class="ri-hand-coin-line me-2"></i> Drivers
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/salary/operator.php', event)">
                            <i class="ri-money-dollar-box-line me-2"></i> Operators
                        </a>
                    </li>
                </ul>
            </div> -->
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#pumpMenu">
                <i class="ri-gas-station-fill icon-pump text-danger"></i> Pumps
            </a>
            <div class="collapse" id="pumpMenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/pump/pump.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-list-settings-line me-2 small-icon"></i> Pump Details
                        </a>
                    </li>
                </ul>
            </div>

            <!-- <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#machineMenu">
                <i class="ri-steering-2-fill icon-machine"></i> Machinery
            </a> -->
            <div class="collapse" id="machineMenu">
                <ul class="sub-menu">
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/fuel/fuel_list.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-oil-line me-2"></i> Fuel & Mileage
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/vehicle_type/vehicle_type.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-truck-line me-2"></i> Vehicle Types
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/vehicle/vehicle.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-car-washing-line me-2"></i> Vehicles
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/fuel/fuel_rates.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-price-tag-3-line me-2"></i> Fuel Rates
                        </a>
                    </li>
                </ul>
            </div>

            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#categoryMenu">
                <i class="ri-money-cny-box-line icon-expense"></i> Categories
            </a>
            <div class="collapse" id="categoryMenu">
                <ul class="sub-menu">

                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/category-type/category-type.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-exchange-funds-line me-2"></i> Catagory Type
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/category/category.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-exchange-funds-line me-2"></i> Categories
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/category/sub-category-list.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-exchange-funds-line me-2"></i>Sub Categories
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/machinery/add-machinery.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-exchange-funds-line me-2"></i>Sub Category Machinary
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#expensesMenu">
                <i class="ri-money-cny-box-line icon-expense"></i> Expenses
            </a>
            <div class="collapse" id="expensesMenu">
                <ul class="sub-menu">

                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/expenses/manager-income.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-exchange-funds-line me-2"></i> Manager Income
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/expenses/manager-expense.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-bill-line me-2"></i> Manager Expenses
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/salary/salary-list.php', event)">
                            <i class="ri-bank-card-line me-2"></i> Salaries
                        </a>
                    </li>
                </ul>
            </div>


            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#authMenu">
                <i class="ri-money-cny-box-line icon-expense"></i> Auth
            </a>
            <div class="collapse" id="authMenu">
                <ul class="sub-menu">

                    <li>
                        <a href="javascript:void(0)" onclick="loadContent('components/auth/auth-list.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-exchange-funds-line me-2"></i> Manager Auth
                        </a>
                    </li>

                </ul>
            </div>





            <!-- <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#fuelMenu">
                <i class="ri-gas-station-line icon-fuel"></i> Fuel & Pump
            </a>
            <div class="collapse" id="fuelMenu">
                <ul class="sub-menu">
                    <li>
                        <a href="#" onclick="loadContent('components/pump/pump.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-drop-line me-2"></i> Total Pumps
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="loadContent('components/pump/pump-detail.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-file-list-3-line me-2"></i> Pumps Details
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="loadContent('components/owner/owner-detail.php', event)" class="d-flex align-items-center py-2">
                            <i class="ri-user-star-line me-2"></i> Owner Details
                        </a>
                    </li>
                </ul>
            </div>

            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#salaryMenu">
                <i class="ri-bank-card-line icon-salary"></i> Salaries
            </a>
            <div class="collapse" id="salaryMenu">
                <ul class="sub-menu">
                    <li><a href="#" onclick="loadContent('components/salary/pending.php', event)">Pending Payments</a></li>
                    <li><a href="#" onclick="loadContent('components/salary/history.php', event)">Paid History</a></li>
                    <li><a href="#" onclick="loadContent('components/salary/rates.php', event)">Labour Rates</a></li>
                </ul>
            </div>

            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#managerMenu">
                <i class="ri-group-line icon-manager"></i> Managers
            </a>
            <div class="collapse" id="managerMenu">
                <ul class="sub-menu">
                    <li><a href="#" onclick="loadContent('components/managers/active.php', event)">Active Managers</a></li>
                    <li><a href="#" onclick="loadContent('components/managers/assignments.php', event)">Assignments</a></li>
                </ul>
            </div>

            

            <a class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#reportMenu">
                <i class="ri-file-chart-line icon-report"></i> Reports
            </a>
            <div class="collapse" id="reportMenu">
                <ul class="sub-menu">
                    <li><a href="#" onclick="loadContent('components/reports/summary.php', event)">Monthly Summary</a></li>
                    <li><a href="#" onclick="loadContent('components/reports/audit.php', event)">Audit Logs</a></li>
                </ul>
            </div> -->
        </nav>
    </div>

    <div id="main-content">
        <div class="container-fluid">
            <?php
            // URL se 'p' parameter check karein
            $p = isset($_GET['p']) ? $_GET['p'] : '';

            // Agar parameter hai aur file exist karti hai, to direct load karein
            if (!empty($p) && file_exists($p)) {
                include($p);
            } else {
                // Default Dashboard
                include('components/dashboard/dashboard.php');
            }
            ?>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- index.php ke footer mein -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/backend-script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/owner.js"></script>
    <script src="assets/js/district.js"></script>
    <script src="assets/js/tehsil.js"></script>
    <script src="assets/js/pumps.js"></script>
    <!-- <script src="assets/js/machinary.js"></script> -->
    <script src="assets/js/salary.js"></script>
    <script src="assets/js/staff.js"></script>
    <script src="assets/js/manager-income.js"></script>
    <script src="assets/js/category.js"></script>
    <script src="assets/js/manager-expenses.js"></script>
    <script src="assets/js/company.js"></script>
    <script src="assets/js/vehicles.js"></script>
    <script src="assets/js/vehicle_owners.js"></script>
    <script src="assets/js/fuel.js"></script>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/attendance.js"></script>
    <script src="assets/js/manager-payments.js"></script>



    <script>
        $(document).on('submit', '#addPumpForm', function(e) {
            e.preventDefault();

            // Form ka sara data akatha karna
            let formData = $(this).serialize();
            console.log("Sending Data:", formData); // Check karein console mein data show ho raha hai?

            $.ajax({
                url: "backend/pump/pump_process.php", // Path check karein: agar dashboard se call ho raha hai to yehi path hoga
                type: "POST",
                data: formData,
                beforeSend: function() {
                    // Button ko disable kar dein taake double click na ho
                    $('button[type="submit"]').attr('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Saving...');
                },
                success: function(response) {
                    console.log("Server Response:", response);
                    if (response.trim() === "success") {
                        alert("Mubarak ho! Pump register ho gaya.");
                        loadContent('components/pump/pump.php'); // Wapis list par le jaye ga
                    } else {
                        alert("Backend Error: " + response);
                    }
                    $('button[type="submit"]').attr('disabled', false).html('<i class="ri-save-3-line me-1"></i> Save Pump Record');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("Script ka masla hai! Check console.");
                    $('button[type="submit"]').attr('disabled', false).html('Try Again');
                }
            });
        });
    </script>

</body>

</html>