<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Labour Plus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fe;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            /* Aapki pasand ka elevation aur shadow depth */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding-left: 45px;
            transition: 0.3s;
        }

        .form-control:focus {
            /* Input field focus state management */
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            border-color: #6366f1;
        }

        .input-group-text {
            background: transparent;
            border: none;
            position: absolute;
            z-index: 10;
            height: 50px;
            display: flex;
            align-items: center;
            color: #9e9e9e;
        }

        .btn-login {
            height: 50px;
            border-radius: 12px;
            background: #6366f1;
            border: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.3);
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: #6366f1;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 30px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="text-center">
            <div class="brand-logo">
                <i class="ri-shield-user-line"></i>
            </div>
            <h4 class="fw-bold text-dark">Welcome Back</h4>
            <p class="text-muted small mb-4">Please enter your details to sign in</p>
        </div>

        <form id="loginForm">
            <div class="mb-3 position-relative">
                <span class="input-group-text ps-3"><i class="ri-mail-line"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>

            <div class="mb-4 position-relative">
                <span class="input-group-text ps-3"><i class="ri-lock-line"></i></span>
                <input type="password" name="password" id="passwordField" class="form-control" placeholder="Password" required>
                <span id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #9e9e9e;">
                    <i class="ri-eye-line" id="eyeIcon"></i>
                </span>
            </div>

            <button type="submit" id="loginBtn" class="btn btn-primary w-100 btn-login">
                <span id="btnText">Sign In</span>
                <div id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></div>
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="small text-muted">Protected by Secure System</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // 1. Password Toggle
            $('#togglePassword').on('click', function() {
                const passwordField = $('#passwordField');
                const eyeIcon = $('#eyeIcon');
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    eyeIcon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
                } else {
                    passwordField.attr('type', 'password');
                    eyeIcon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
                }
            });

            // 2. Login Submit
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: 'login-process.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        let res = response.trim();
                        if (res === 'admin_success') {
                            window.location.href = '../admin/index.php';
                        } else if (res === 'manager_success') {
                            window.location.href = '../manager/index.php';
                        } else if (res === 'driver_success') {
                            window.location.href = '../driver/index.php';
                        } else if (res === 'operator_success') {
                            window.location.href = '../operator/index.php';
                        } else if (res === 'invalid') {
                            Swal.fire('Error', 'Invalid Username or Password!', 'error');
                        } else {
                            Swal.fire('Error', 'Role not recognized: ' + res, 'warning');
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>