{{-- Include layout utama (Sidebar dan footer) --}}
@extends('layouts.app')

{{-- Set title berdasarkan page --}}
@section('title', 'Dashboard')

{{-- Untuk menggunakan css --}}
@push('styles')
<style>
    .module-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .module-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #435ebe;
    }
    .module-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 28px;
    }
    .module-icon.maintenance { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .module-icon.hr { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .module-icon.production { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .module-icon.default { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
</style>
@endpush

{{-- Isi content --}}
@section('content')

<div class="page-heading">
    <h3>Welcome, {{ $user->name }}</h3>
    <p class="text-subtitle text-muted">
        @if($user->isSuperAdmin())
            Super Administrator - You have access to all modules
        @else
            {{ $user->getRoleNames()->implode(', ') }}
        @endif
    </p>
</div>

<div class="page-content">
    <!-- Accessible Modules Section -->
    <section class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Your Modules</h4>
                    <p class="text-muted mb-0">Click a module to access its features</p>
                </div>
                <div class="card-body">
                    @if(count($accessibleModules) > 0)
                    <div class="row">
                        @foreach($accessibleModules as $key => $module)
                        <div class="col-6 col-md-4 col-lg-3 mb-4">
                            <a href="{{ route($key . '.dashboard') }}" class="text-decoration-none">
                                <div class="card module-card h-100">
                                    <div class="card-body text-center py-4">
                                        <div class="module-icon {{ $module['category'] ?? 'default' }} mx-auto mb-3 text-white">
                                            <i class="bi {{ $module['icon'] ?? 'bi-grid' }}"></i>
                                        </div>
                                        <h5 class="card-title mb-1">{{ $module['display_name'] }}</h5>
                                        <p class="text-muted small mb-0">
                                            @php
                                                $role = $user->getModuleRole($key);
                                            @endphp
                                            {{ $role ? ucfirst(str_replace('_', ' ', $role)) : 'Access Granted' }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x display-1 text-muted"></i>
                        <h5 class="mt-3">No Modules Available</h5>
                        <p class="text-muted">You don't have access to any modules yet. Please contact your administrator.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Section -->
    <section class="row">
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon purple mb-2">
                                <i class="bi bi-grid"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Available Modules</h6>
                            <h6 class="font-extrabold mb-0">{{ count($accessibleModules) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Your Roles</h6>
                            <h6 class="font-extrabold mb-0">{{ $user->getRoleNames()->count() }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon green mb-2">
                                <i class="bi bi-shield-check"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Account Type</h6>
                            <h6 class="font-extrabold mb-0">{{ $user->isSuperAdmin() ? 'Admin' : 'User' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Last Login</h6>
                            <h6 class="font-extrabold mb-0">Today</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Profile Card -->
    <section class="row">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body py-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xl bg-primary text-white d-flex align-items-center justify-content-center" style="font-size: 24px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="ms-3 name">
                            <h5 class="font-bold mb-1">{{ $user->name }}</h5>
                            <h6 class="text-muted mb-0">{{ $user->email }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>Your Permissions</h4>
                </div>
                <div class="card-body">
                    @if($user->isSuperAdmin())
                        <span class="badge bg-success me-1 mb-1">Full Access (Super Admin)</span>
                    @else
                        @forelse($user->getAllPermissions() as $permission)
                            <span class="badge bg-primary me-1 mb-1">{{ $permission->name }}</span>
                        @empty
                            <p class="text-muted mb-0">No specific permissions assigned</p>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

{{-- Untuk menggunakan js --}}
@push('scripts')
@endpush
