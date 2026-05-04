<?php
$host = "localhost";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass);

// 1. ------------- Database Creation ------------
$conn->query("CREATE DATABASE IF NOT EXISTS lp_db");
$conn->select_db("lp_db");


// 2. ------------ Districts Table ------------
$district_table = "CREATE TABLE IF NOT EXISTS districts (
    district_id INT AUTO_INCREMENT PRIMARY KEY,
    district_name VARCHAR(100) NOT NULL,
    region_code VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
)";
$conn->query($district_table);



// 3. --------------- Tehsils Table ----------------
$tehsils_table = "CREATE TABLE IF NOT EXISTS tehsils (
    tehsil_id INT AUTO_INCREMENT PRIMARY KEY,
    tehsil_name VARCHAR(100) NOT NULL,
    district_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES districts(district_id) ON DELETE CASCADE
)";
$conn->query($tehsils_table);


// 4. ---------------- Pumps Table -----------------
$pumps_table = "CREATE TABLE IF NOT EXISTS pumps (
    pump_id INT AUTO_INCREMENT PRIMARY KEY,
    pump_name VARCHAR(150) NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    owner_phone VARCHAR(20) NOT NULL,
    owner_account VARCHAR(50),
    owner_cnic VARCHAR(20),
    tehsil_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id)
)";
$conn->query($pumps_table);


// 5. ----------------- Vehicle Types Table ----------------
$vehicle_types = "CREATE TABLE IF NOT EXISTS vehicle_types (
    v_type_id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL, 
    fuel_type ENUM('Petrol', 'Diesel', 'CNG') DEFAULT 'Diesel',
    mileage_unit ENUM('KM', 'HR') DEFAULT 'KM',
    approved_fuel DECIMAL(10,2) DEFAULT 0.00,
    lp_fuel DECIMAL(10,2) DEFAULT 0.00,
    service_charges DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($vehicle_types);


// 6. ----------------- Vehicles Table ---------------
$vehicles = "CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    reg_no VARCHAR(50) NOT NULL UNIQUE,
    tehsil_id INT NOT NULL,
    v_type_id INT NOT NULL,
    fuel_type ENUM('Diesel', 'Petrol') NOT NULL DEFAULT 'Diesel',
    company VARCHAR(100) NOT NULL, -- ENUM ki bajaye VARCHAR taake nayi companies add ho saken
    arrival_date DATE NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Foreign Keys ensure data integrity
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id) ON DELETE CASCADE,
    FOREIGN KEY (v_type_id) REFERENCES vehicle_types(v_type_id) ON DELETE CASCADE
)";

if ($conn->query($vehicles)) {
    // Agar column pehle se nahi hai (Existing table fix)
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS fuel_type ENUM('Diesel', 'Petrol') NOT NULL DEFAULT 'Diesel' AFTER v_type_id");
}


// ------------ MACHINE_INVENTORY TABLE -------------
$machinery_inventory = "CREATE TABLE IF NOT EXISTS machinery_inventory (
    inv_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    tehsil_id INT NOT NULL,
    expense_date DATE NOT NULL,
    maintenance_cost DECIMAL(10,2) DEFAULT 0.00,
    parts_cost DECIMAL(10,2) DEFAULT 0.00, -- Spare parts etc
    description TEXT, -- Detail kya kaam hua
    added_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id),
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id)
)";
$conn->query($machinery_inventory);



// --------------STAFF TABLE---------------
$tableSchema = "CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_role ENUM('Manager', 'Operator', 'Driver') NOT NULL,
    tehsil_id INT NULL, 
    staff_name VARCHAR(100) NOT NULL,
    staff_phone VARCHAR(20),
    staff_cnic VARCHAR(20), -- Naya addition
    fixed_salary DECIMAL(15,2) DEFAULT 0.00,
    joining_date DATE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conn->query($tableSchema)) {
    die("Database Error (Staff Table): " . $conn->error);
}



// ------------SALERIES TABLE--------------- 
$tableSchema = "CREATE TABLE IF NOT EXISTS salaries (
    salary_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    staff_role ENUM('Manager', 'Driver', 'Operator') NOT NULL,
    salary_month DATE NOT NULL,
    
    -- Calculation Columns
    working_days INT DEFAULT 30,        -- Jitne din banda kaam par aya
    leaves INT DEFAULT 0,               -- Jitni chuttiyan ki
    
    fixed_salary DECIMAL(15,2),         -- Basic Salary
    advance_amount DECIMAL(15,2) DEFAULT 0.00, -- Jo advance pehle le chuka hai
    bonus_amount DECIMAL(15,2) DEFAULT 0.00,   -- Extra bonus
    deduction_amount DECIMAL(15,2) DEFAULT 0.00, -- Fine ya leaves ki wajah se deduction
    
    net_salary DECIMAL(15,2),           -- Final Amount jo banti hai (Fixed + Bonus - Advance - Deduction)
    paid_amount DECIMAL(15,2),          -- Jo actual mein pay ki gayi
    remaining_balance DECIMAL(15,2) DEFAULT 0.00, -- Jo baqi reh gayi
    
    payment_status ENUM('Paid', 'Pending', 'Partial') DEFAULT 'Pending',
    payment_method ENUM('Cash', 'Bank') DEFAULT 'Cash',
    bank_name VARCHAR(100) NULL,
    account_info VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conn->query($tableSchema)) {
    die("Database Error (Staff Table): " . $conn->error);
}




// ----------------- CATEGORY TYPES TABLE -----------------
// Ye table main categories (e.g. Fuel, Maintenance, Office) store karegi
$category_types_table = "CREATE TABLE IF NOT EXISTS expense_category_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($category_types_table)) {
    // Debugging ke liye (Aap isse baad mein hata sakte hain)
    // echo "Table expense_category_types ready!<br>";
} else {
    die("Database Error (Category Types): " . $conn->error);
}


$expense_categories = "CREATE TABLE IF NOT EXISTS expense_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,  -- Ye column missing hai jiski wajah se error aa raha hai
    category_name VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES expense_category_types(type_id) ON DELETE CASCADE
)";

if ($conn->query($expense_categories)) {
    // Debugging ke liye (Aap isse baad mein hata sakte hain)
    // echo "Table expense_category_types ready!<br>";
} else {
    die("Database Error (Category Types): " . $conn->error);
}

$expense_sub_categories = "CREATE TABLE IF NOT EXISTS expense_sub_categories (
    sub_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL, 
    sub_name VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE CASCADE
)";
$conn->query($expense_sub_categories);



//  Manager Income / Fund Allotment Table
$tableSchema = "CREATE TABLE IF NOT EXISTS manager_income (
    manager_income_id INT AUTO_INCREMENT PRIMARY KEY,
    tehsil_id INT NOT NULL,
    manager_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    pay_mode ENUM('Cash', 'Bank') NOT NULL DEFAULT 'Cash',
    bank_name VARCHAR(100) NULL,      -- Sirf Bank mode ke liye
    account_no VARCHAR(100) NULL,     -- Sirf Bank/Cheque ke liye
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys (Taake data integrity rahay)
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES staff(staff_id) ON DELETE CASCADE
)";

if (!$conn->query($tableSchema)) {
    die("Database Error: " . $conn->error);
}

// ------------ MANAGER EXPENSES TABLE --------------
$manager_expenses = "CREATE TABLE IF NOT EXISTS manager_expenses (
    expense_id INT PRIMARY KEY AUTO_INCREMENT,
    manager_id INT NOT NULL,  -- References staff table
    district_id INT NOT NULL,
    tehsil_id INT NOT NULL,
    
    -- 3-Level Category Hierarchy
    type_id INT NOT NULL,
    category_id INT NOT NULL,
    sub_id INT DEFAULT NULL,
    
    expense_date DATE NOT NULL, -- Is column ko index.php mein use karenge
    amount DECIMAL(15, 2) NOT NULL,
    item_name VARCHAR(255),    
    description TEXT,
    bill_attachment VARCHAR(255),
    transaction_attachment VARCHAR(255),
    
    -- Payment Details
    payment_method ENUM('Cash', 'Mobile Acc', 'Bank Account') DEFAULT 'Cash',
    pay_owner_name VARCHAR(150),
    pay_acc_no VARCHAR(100),
    pay_contact VARCHAR(20),
    pay_cnic VARCHAR(20),
    bank_name VARCHAR(100) DEFAULT NULL,
    
    -- Status Workflow
    status ENUM('Pending', 'Approved', 'Rejected', 'Deleted') DEFAULT 'Pending',
    added_by_role ENUM('admin', 'manager') DEFAULT 'manager',
    
    -- Timestamp Tracking
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT fk_exp_staff FOREIGN KEY (manager_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    CONSTRAINT fk_exp_district FOREIGN KEY (district_id) REFERENCES districts(district_id) ON DELETE CASCADE,
    CONSTRAINT fk_exp_tehsil FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id) ON DELETE CASCADE,
    CONSTRAINT fk_exp_type FOREIGN KEY (type_id) REFERENCES expense_category_types(type_id) ON DELETE CASCADE,
    CONSTRAINT fk_exp_cat FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE CASCADE,
    CONSTRAINT fk_exp_sub FOREIGN KEY (sub_id) REFERENCES expense_sub_categories(sub_id) ON DELETE SET NULL
)";

if (!$conn->query($manager_expenses)) {
    die("Database Error: " . $conn->error);
}


// 5. ----------------- MACHINER REGISTRATION ----------------
$machinery_registration = "CREATE TABLE IF NOT EXISTS machinery_registration (
    machine_id INT(11) NOT NULL AUTO_INCREMENT,
    district_id INT(11) NOT NULL,
    tehsil_id INT(11) NOT NULL,
    type_id INT(11) NOT NULL,        -- Purchase / Rent
    category_id INT(11) NOT NULL,    -- Machinery
    sub_id INT(11) NOT NULL,         -- Water Browser, Dumper, etc.
    registration_no VARCHAR(100) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (machine_id)
)";
if (!$conn->query($machinery_registration)) {
    die("Database Error: " . $conn->error);
}
$companies = "CREATE TABLE IF NOT EXISTS companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT,
    tehsil_id INT,
    company_name VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    FOREIGN KEY (district_id) REFERENCES districts(district_id),
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id)
)";
if (!$conn->query($companies)) {
    die("Database Error: " . $conn->error);
}

// 5. ----------------- Vehicle Types Table ----------------
$vehicle_types = "CREATE TABLE IF NOT EXISTS vehicle_types (
    v_type_id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL, 
    fuel_type ENUM('Petrol', 'Diesel', 'CNG') DEFAULT 'Diesel',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($vehicle_types)) {
    // Table successfully created or already exists
} else {
    die("Database Error (Vehicle Types): " . $conn->error);
}



// 6. ----------------- Vehicles owners -----------------
$vehicle_owners = "CREATE TABLE IF NOT EXISTS vehicle_owners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT NOT NULL,
    tehsil_id INT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    cnic VARCHAR(20) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    -- Banking Details
    account_title VARCHAR(150) DEFAULT NULL,
    account_number VARCHAR(50) DEFAULT NULL,
    account_type VARCHAR(50) DEFAULT NULL, -- e.g., JazzCash, EasyPaisa, HBL
    
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (district_id) REFERENCES districts(district_id),
    FOREIGN KEY (tehsil_id) REFERENCES tehsils(tehsil_id)
)";

if ($conn->query($vehicle_owners)) {
    // Table successfully created or already exists
} else {
    die("Database Error (Vehicle Owners): " . $conn->error);
}



// 6. ----------------- Vehicles Table (Final Fix) ---------------
$vehicles_init = "CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    reg_no VARCHAR(50) NOT NULL UNIQUE,
    tehsil_id INT NOT NULL,
    v_type_id INT NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($vehicles_init)) {
    // AB YE COLUMNS LAZMI ADD KAREIN (Tension free logic)
    // Har line check karegi ke column hai ya nahi, aur missing column add kar degi
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS district_id INT NOT NULL AFTER vehicle_id");
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS owner_id INT NOT NULL AFTER tehsil_id");
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS company_id INT NOT NULL AFTER owner_id");
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS meter_type ENUM('KM', 'HR') NOT NULL DEFAULT 'KM' AFTER reg_no");
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS rental_status ENUM('Rental', 'Exempted', 'Non-Rental') NOT NULL DEFAULT 'Non-Rental' AFTER meter_type");
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS company_rent DECIMAL(10,2) DEFAULT 0.00 AFTER rental_status");
    $conn->query("ALTER TABLE vehicles ADD COLUMN IF NOT EXISTS lp_rent DECIMAL(10,2) DEFAULT 0.00 AFTER company_rent");

    // Foreign Keys manually add karein taake relations set ho jayen
    $conn->query("ALTER TABLE vehicles ADD CONSTRAINT fk_v_dist FOREIGN KEY IF NOT EXISTS (district_id) REFERENCES districts(district_id)");
    $conn->query("ALTER TABLE vehicles ADD CONSTRAINT fk_v_owner FOREIGN KEY IF NOT EXISTS (owner_id) REFERENCES vehicle_owners(owner_id)");
    $conn->query("ALTER TABLE vehicles ADD CONSTRAINT fk_v_company FOREIGN KEY IF NOT EXISTS (company_id) REFERENCES companies(company_id)");
} else {
    die("Database Error (Fuel Entries): " . $conn->error);
}



// 7. ----------------- Fuel Entries Table (Final Optimized Version) -----------------  
$fuel_entries_final = "CREATE TABLE IF NOT EXISTS fuel_entries (
    fuel_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    district_id INT NULL,  -- Reporting fast karne ke liye
    tehsil_id INT NULL,    -- Reporting fast karne ke liye
    fuel_date DATE NOT NULL,
    fuel_type ENUM('Diesel', 'Petrol') NOT NULL DEFAULT 'Diesel',
    qty DECIMAL(10,2) DEFAULT 0.00,          -- Fuel Quantity
    meter_reading DECIMAL(15,2) DEFAULT 0.00, -- Current Meter/KM/HR
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints & Indexes
    UNIQUE KEY unique_daily_fuel (vehicle_id, fuel_date), 
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE,
    INDEX (fuel_date), -- Filters ko fast karne ke liye index
    INDEX (district_id),
    INDEX (tehsil_id)
)";

if ($conn->query($fuel_entries_final)) {
    // Agar purani table mein qty ya meter_reading ka naam different tha, toh ye update fix kar dega
    $conn->query("ALTER TABLE fuel_entries ADD COLUMN IF NOT EXISTS district_id INT NULL AFTER vehicle_id");
    $conn->query("ALTER TABLE fuel_entries ADD COLUMN IF NOT EXISTS tehsil_id INT NULL AFTER district_id");
    $conn->query("ALTER TABLE fuel_entries ADD COLUMN IF NOT EXISTS meter_reading DECIMAL(15,2) DEFAULT 0.00 AFTER qty");
} else {
    die("Database Error (Fuel Entries): " . $conn->error);
}

$rental_attendance="CREATE TABLE IF NOT EXISTS rental_attendance (
    att_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Maintenance') DEFAULT 'Present',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY daily_val (vehicle_id, attendance_date)
)";

if ($conn->query($rental_attendance)) {
    // Table successfully created or already exists
} else {
    die("Database Error (rental_attendance): " . $conn->error);
}


// ---------------- LOGIN AUTH ----------------
// 1. Table Schema
$tableSchema = "CREATE TABLE IF NOT EXISTS auth (
    id INT(11) NOT NULL AUTO_INCREMENT, -- 255 ki zaroorat nahi, 11 kafi hai
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'driver', 'operator') NOT NULL,
    district_id INT(11) DEFAULT NULL,
    tehsil_id INT(11) DEFAULT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username),
    CONSTRAINT fk_user_district FOREIGN KEY (district_id) REFERENCES districts (district_id) ON DELETE SET NULL,
    CONSTRAINT fk_user_tehsil FOREIGN KEY (tehsil_id) REFERENCES tehsils (tehsil_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($tableSchema)) {

    // 2. Behtar tareeka: Pehle check karein admin hai ya nahi
    $checkAdmin = $conn->query("SELECT id FROM auth WHERE username = 'admin'");

    if ($checkAdmin->num_rows == 0) {
        $adminPassword = 'admin123';
        // Admin ke liye district/tehsil NULL honge, isliye constraints ka masla nahi aayega
        $sql = "INSERT INTO auth (username, password, role, status, district_id, tehsil_id) 
                VALUES ('admin', '$adminPassword', 'admin', 'Active', NULL, NULL)";
        $conn->query($sql);
    }
}

// -- 1. Company Income Table
$company_income = "CREATE TABLE IF NOT EXISTS company_income (
    income_id INT PRIMARY KEY AUTO_INCREMENT,
    district_id INT,
    tehsil_id INT,
    amount DECIMAL(15,2),
    income_date DATE,
    payment_method ENUM('Cash', 'Bank Account', 'Mobile Account'),
    -- Details for Cash/Bank/Mobile
    receiver_name VARCHAR(100), -- Person name for Cash
    account_details VARCHAR(255), -- Bank/Bank Name or Mobile Network
    holder_name VARCHAR(100), 
    contact_no VARCHAR(20),
    cnic VARCHAR(20),
    proof_img VARCHAR(255), -- Image/SS path
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($company_income)) {
    die("Database Error: " . $conn->error);
}
// -- 2. Manager Payments Table
$manager_payments = "CREATE TABLE IF NOT EXISTS manager_payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    manager_id INT, -- Link to employees/users table
    amount DECIMAL(15,2),
    payment_date DATE,
    purpose TEXT, -- Salary, Operational cost, etc.
    payment_method ENUM('Cash', 'Bank Account', 'Mobile Account'),
    proof_img VARCHAR(255),
    status ENUM('Pending', 'Approved') DEFAULT 'Approved'
)";
if (!$conn->query($manager_payments)) {
    die("Database Error: " . $conn->error);
}