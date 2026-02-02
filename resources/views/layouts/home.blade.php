<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT.METINCA PRIMA INDUSTRIAL WORKS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/menu-modal.css') }}">
    @stack('styles')
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fs-5" href="#">
                PT. METINCA PRIMA INDUSTRIAL WORKS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.main') ? 'active' :'' }}" href="/home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/home#profile">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/home/divisions">Divisions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home.products') }}">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home.facilities') }}">Facilities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home.gallery') }}">Gallery</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" data-bs-target="#metincaAppsModal" data-bs-toggle="modal" href="#"><i
                                class="bi bi-grid-3x3-gap-fill me-2"></i> Application</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

   @yield('content')

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>PT. METINCA PRIMA INDUSTRIAL WORKS</h5>
                    <p>
                        The #1 Precision Casting and Tooling Facility in Indonesia    
                    </p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Products</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Investment Casting</a></li>
                        <li class="mb-2"><a href="#">Sand Casting</a></li>
                        <li class="mb-2"><a href="#">Valve</a></li>

                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact</h5>
                    
                    <p><i class="fas fa-phone me-2"></i>+62 21 1234 5678</p>
                    <p><i class="fas fa-envelope me-2"></i>info@metinca-prima.co.id</p>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center pt-3">
                <p>&copy; 2025 Metinca. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    <!-- Modal Menu-->
    <div class="modal fade" id="metincaAppsModal" tabindex="-1" aria-labelledby="metincaAppsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="metincaAppsModalLabel">
                        <i class="bi bi-app-indicator me-2"></i>Metinca Apps
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- HR Category -->
                    <div class="category-card">
                        <div class="category-title">
                            <i class="bi bi-people-fill"></i>
                            Human Resources
                        </div>
                        <div class="menu-grid">
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-person-plus-fill"></i>
                                </div>
                                <div class="menu-text">Recruitment</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <div class="menu-text">Placement</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-arrow-up-circle-fill"></i>
                                </div>
                                <div class="menu-text">Job Promotion</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </div>
                                <div class="menu-text">Development</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                                <div class="menu-text">Mutation</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="menu-text">Pension</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                    <i class="bi bi-calendar-x-fill"></i>
                                </div>
                                <div class="menu-text">Leave</div>
                            </a>
                        </div>
                    </div>

                    <!-- General Affair Category -->
                    <div class="category-card" style="background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);">
                        <div class="category-title">
                            <i class="bi bi-briefcase-fill"></i>
                            General Affair
                        </div>
                        <div class="menu-grid">
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="menu-text">Inventory of Facilities and Assets</div>
                            </a>
                            
                            <a href="#" class="menu-item">
                                <div class="menu-icon" style="background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);">
                                    <i class="bi bi-boxes"></i>
                                </div>
                                <div class="menu-text">Asset Management</div>
                            </a>
                        </div>
                    </div>

                    
                    <div class="category-card" style="background: linear-gradient(135deg, #232526 0%, #414345 100%);">
                        <div class="category-title">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            Administration
                        </div>
                        <div class="menu-grid">
                            <a href="#" class="menu-item">
                                <div class="menu-icon"
                                    style="background: linear-gradient(135deg, #232526 0%, #414345 100%);">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div class="menu-text">Attendance System</div>
                            </a>
                            
                            <a href="#" class="menu-item">
                                <div class="menu-icon"
                                    style="background: linear-gradient(135deg, #232526 0%, #414345 100%);">
                                    <i class="bi bi-alarm-fill"></i>
                                </div>
                                <div class="menu-text">Overtime System</div>
                            </a>
                            
                            <a href="#" class="menu-item">
                                <div class="menu-icon"
                                    style="background: linear-gradient(135deg, #232526 0%, #414345 100%);">
                                    <i class="bi bi-cart-fill"></i>
                                </div>
                                <div class="menu-text">Procurement of Goods & Operations</div>
                            </a>
                            
                            <a href="#" class="menu-item">
                                <div class="menu-icon"
                                    style="background: linear-gradient(135deg, #232526 0%, #414345 100%);">
                                    <i class="bi bi-lightning-fill"></i>
                                </div>
                                <div class="menu-text">Utility System</div>
                            </a>
                        </div>
                    </div>

                    <!-- Commercial Division -->
                    <div class="category-card commercial">
                        <div class="category-title">
                            <i class="bi bi-briefcase-fill"></i>
                            Commercial & Business
                        </div>

                        <!-- Purchasing -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-cart-check-fill"></i>
                                Purchasing
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                    <div class="menu-text">Supplier Determination</div>
                                </a>
                            </div>
                        </div>

                        <!-- Sales -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-graph-up-arrow"></i>
                                Sales
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-bag-check-fill"></i>
                                    </div>
                                    <div class="menu-text">Product Sales</div>
                                </a>
                            </div>
                        </div>

                        <!-- Marketing -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-megaphone-fill"></i>
                                Marketing
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-megaphone"></i>
                                    </div>
                                    <div class="menu-text">Product Promotion</div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Division -->
                    <div class="category-card maintenance">
                        <div class="category-title">
                            <i class="bi bi-tools"></i>
                            Maintenance
                        </div>
                        <div class="menu-grid">
                            <a href="#" class="menu-item">
                                <div class="menu-icon">
                                    <i class="bi bi-calendar-check-fill"></i>
                                </div>
                                <div class="menu-text">Scheduling</div>
                            </a>
                            <a href="{{ route('login', ['module' => 'repair']) }}" class="menu-item">
                                <div class="menu-icon">
                                    <i class="bi bi-wrench-adjustable"></i>
                                </div>
                                <div class="menu-text">Repair</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="menu-text">Preventive Maintenance</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon">
                                    <i class="bi bi-box-seam-fill"></i>
                                </div>
                                <div class="menu-text">Stock Sparepart Machine</div>
                            </a>
                            <a href="#" class="menu-item">
                                <div class="menu-icon">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                                <div class="menu-text">Personnel Determination</div>
                            </a>
                        </div>
                    </div>

                    <!-- Production & Warehouse Division -->
                    <div class="category-card production">
                        <div class="category-title">
                            <i class="bi bi-building"></i>
                            Production & Warehouse
                        </div>

                        <!-- PPIC -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-clipboard-data-fill"></i>
                                Production Planning Inventory Control (PPIC)
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-calendar2-week-fill"></i>
                                    </div>
                                    <div class="menu-text">Production Scheduling</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-cart-plus-fill"></i>
                                    </div>
                                    <div class="menu-text">Production Material Procurement</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-boxes"></i>
                                    </div>
                                    <div class="menu-text">Material Inventory</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-bullseye"></i>
                                    </div>
                                    <div class="menu-text">Production Target</div>
                                </a>
                            </div>
                        </div>

                        <!-- Gudang -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-house-fill"></i>
                                Warehouse
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div class="menu-text">Goods Delivery</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </div>
                                    <div class="menu-text">Production Material Return</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </div>
                                    <div class="menu-text">Product Return (NG)</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </div>
                                    <div class="menu-text">Goods In and Out Management</div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Quality Management Division -->
                    <div class="category-card quality">
                        <div class="category-title">
                            <i class="bi bi-patch-check-fill"></i>
                            Quality Management
                        </div>

                        <!-- Quality Control -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-search"></i>
                                Quality Control
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div class="menu-text">Raw Material Inspection</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-gear-wide-connected"></i>
                                    </div>
                                    <div class="menu-text">Production Process Inspection</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-check2-square"></i>
                                    </div>
                                    <div class="menu-text">Finished Product Inspection</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                    </div>
                                    <div class="menu-text">Documentation & Reporting</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div class="menu-text">Nonconformity Follow-up</div>
                                </a>
                            </div>
                        </div>

                        <!-- Quality Assurance -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-shield-fill-check"></i>
                                Quality Assurance
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-diagram-3-fill"></i>
                                    </div>
                                    <div class="menu-text">Quality Planning</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-eye-fill"></i>
                                    </div>
                                    <div class="menu-text">Process Monitoring</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                    <div class="menu-text">Testing and Validation</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="menu-text">Documentation</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                                    </div>
                                    <div class="menu-text">Improvement and Development</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <div class="menu-text">Training and Socialization</div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Production Engineering Division -->
                    <div class="category-card engineering">
                        <div class="category-title">
                            <i class="bi bi-cpu-fill"></i>
                            Production Engineering
                        </div>

                        <!-- Manajemen Produksi -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-kanban-fill"></i>
                                Production Management
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-clipboard-check-fill"></i>
                                    </div>
                                    <div class="menu-text">Production Planning</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-diagram-2-fill"></i>
                                    </div>
                                    <div class="menu-text">Organization</div>
                                </a>
                                <a href="{{ route('login') }}" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-play-circle-fill"></i>
                                    </div>
                                    <div class="menu-text">Production Execution</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                    <div class="menu-text">Production Control</div>
                                </a>
                            </div>
                        </div>

                        <!-- Development Engineering -->
                        <div class="sub-category">
                            <div class="sub-category-title">
                                <i class="bi bi-lightbulb-fill"></i>
                                Development Engineering
                            </div>
                            <div class="menu-grid">
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-box2-heart-fill"></i>
                                    </div>
                                    <div class="menu-text">Product Development</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-bezier2"></i>
                                    </div>
                                    <div class="menu-text">Process Development</div>
                                </a>
                                <a href="#" class="menu-item">
                                    <div class="menu-icon">
                                        <i class="bi bi-rocket-takeoff-fill"></i>
                                    </div>
                                    <div class="menu-text">Technology Development</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    @stack('scripts')
</body>

</html>
