<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metinca - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="bi bi-gear-fill"></i>
                </div>
                <h1 class="brand-name">Metinca Starter App</h1>
                <p class="brand-tagline">The Starter App for metinca application web program</p>
            </div>

            <!-- Alert Example (hidden by default) -->
            <div class="alert alert-danger d-none" id="errorAlert" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <span id="errorMessage">Username atau password salah!</span>
            </div>

            <!-- Login Form -->
            <form id="loginForm">
                <!-- Username/Email -->
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-fill me-1"></i>Username atau Email
                    </label>
                    <div class="input-group">
                        <input type="text" name="email" class="form-control with-icon" id="username" placeholder="Masukkan username atau email" required>
                        <span class="input-icon">
                            <i class="bi bi-person"></i>
                        </span>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock-fill me-1"></i>Password
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control with-icon" id="password" placeholder="Masukkan password" required>
                        <span class="input-icon password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="remember-forgot">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>
                    <a href="#" class="forgot-link">Lupa password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>

            {{-- <!-- Divider -->
            <div class="divider">
                <span>atau masuk dengan</span>
            </div>

            <!-- Social Login -->
            <div class="social-login">
                <button class="btn-social" onclick="socialLogin('google')">
                    <i class="bi bi-google"></i>
                    Google
                </button>
                <button class="btn-social" onclick="socialLogin('microsoft')">
                    <i class="bi bi-microsoft"></i>
                    Microsoft
                </button>
            </div>

            <!-- Sign Up Link -->
            <div class="signup-link">
                Belum punya akun? <a href="#">Daftar sekarang</a>
            </div> --}}
            <div class="signup-link">
                kembali ke <a href="/home">Homepage</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.3/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Handle Login Form Submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            var formData = new FormData(this);
            
            // Simulate login validation
            if (username && password) {
                // Show success message (in real app, this would be an API call)
                //console.log('Login attempt:', { username, password, remember });

                App.loading('Proses login');

                App.ajax('{{ route('login.store') }}', 'POST',formData).then(response => {
                    // Handle successful login with dynamic redirect
                    App.closeLoading();
                    Swal.fire({
                        title: 'Login Berhasil',
                        text: 'Selamat datang kembali!',
                        icon: 'success',
                        confirmButtonText: 'Lanjutkan',
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        // App.ajax returns response.data directly, so access redirect directly
                        window.location.href = response.redirect || '{{ route('dashboard') }}';
                    });
                }).catch(error => {
                    // Handle login error
                    App.closeLoading();
                    App.error('Gagal Login', error.response?.data?.message || 'Terjadi kesalahan saat login.');
                });
                // Example: Show error
                // showError('Username atau password salah!');
                
                // Example: Successful login redirect
                //alert('Login berhasil! Redirecting...');
                // window.location.href = 'dashboard.html';
            }
        });

        // Show Error Message
        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            errorMessage.textContent = message;
            errorAlert.classList.remove('d-none');
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                errorAlert.classList.add('d-none');
            }, 5000);
        }

        

        // Hide error alert when user starts typing
        document.getElementById('username').addEventListener('input', function() {
            document.getElementById('errorAlert').classList.add('d-none');
        });

        document.getElementById('password').addEventListener('input', function() {
            document.getElementById('errorAlert').classList.add('d-none');
        });
    </script>
</body>
</html>