<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Modul Perbaikan</title>
    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="{{ route('repair.dashboard') }}">
                                <i class="bi bi-wrench-adjustable fs-3 text-primary"></i>
                                <span class="ms-2 fw-bold">Perbaikan</span>
                            </a>
                        </div>
                        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                            <div class="form-check form-switch fs-6">
                                <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                            </div>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Modul Perbaikan</li>

                        <li class="sidebar-item {{ request()->routeIs('repair.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('repair.dashboard') }}" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dasbor</span>
                            </a>
                        </li>

                        <li class="sidebar-item has-sub {{ request()->routeIs('repair.reports.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Laporan</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item {{ request()->routeIs('repair.reports.index') ? 'active' : '' }}">
                                    <a href="{{ route('repair.reports.index') }}" class="submenu-link">Semua Laporan</a>
                                </li>
                                @can('repair.create')
                                <li class="submenu-item {{ request()->routeIs('repair.reports.create') ? 'active' : '' }}">
                                    <a href="{{ route('repair.reports.create') }}" class="submenu-link">Buat Baru</a>
                                </li>
                                @endcan
                            </ul>
                        </li>

                        <li class="sidebar-title">Aksi</li>

                        <li class="sidebar-item">
                            <a href="{{ route('dashboard') }}" class='sidebar-link'>
                                <i class="bi bi-arrow-left-circle"></i>
                                <span>Kembali ke Menu Utama</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="main">
            <header>
                <nav class="navbar navbar-expand navbar-light navbar-top">
                    <div class="container-fluid">
                        <a href="#" class="burger-btn d-block">
                            <i class="bi bi-justify fs-3"></i>
                        </a>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto mb-lg-0">
                                <li class="nav-item dropdown me-3">
                                    <a class="nav-link active dropdown-toggle text-gray-600" href="#"
                                        data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                        <i class="bi bi-bell bi-sub fs-4"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                                        <li class="dropdown-header">
                                            <h6>Notifikasi</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="#">Tidak ada notifikasi</a></li>
                                    </ul>
                                </li>
                            </ul>
                            <div class="dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="user-menu d-flex">
                                        <div class="user-name text-end me-3">
                                            <h6 class="mb-0 text-gray-600">{{ auth()->user()->name }}</h6>
                                            <p class="mb-0 text-sm text-gray-600">{{ auth()->user()->getModuleRole('repair') ?? 'User' }}</p>
                                        </div>
                                        <div class="user-img d-flex align-items-center">
                                            <div class="avatar avatar-md">
                                                <img src="{{ asset('assets/compiled/jpg/1.jpg') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 11rem">
                                    <li>
                                        <h6 class="dropdown-header">Halo, {{ auth()->user()->name }}!</h6>
                                    </li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li>
                                        <form id="formLogout">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="icon-mid bi bi-box-arrow-left me-2"></i> Keluar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            <div class="main-content">
                @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2026 &copy; Modul Perbaikan Metinca</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('formLogout').addEventListener('submit', function(e){
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Konfirmasi Keluar',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    App.loading('Sedang keluar...');
                    App.ajax('{{ route('logout') }}', 'POST', new FormData(form)).then(response => {
                        App.closeLoading();
                        window.location.href = '{{ route('login') }}';
                    }).catch(error => {
                        App.closeLoading();
                        window.location.href = '{{ route('login') }}';
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
