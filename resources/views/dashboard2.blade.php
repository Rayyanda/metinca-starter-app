@extends('layouts.template')


@section('title','Dashboard')

@push('styles')
    
@endpush


@section('content')
    <div class="page-title">
        <h1>Production Dashboard</h1>
        <div class="breadcrumb">
            <a href="#">Home</a>
            <span>/</span>
            <span>Dashboard</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">1,247</div>
                    <div class="stat-label">Units Produced</div>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +12.5% from yesterday
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">8/8</div>
                    <div class="stat-label">Active Machines</div>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-cog"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-check-circle"></i> All operational
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">92%</div>
                    <div class="stat-label">Efficiency Rate</div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
            </div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +3.2% from last week
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-value">3</div>
                    <div class="stat-label">Safety Alerts</div>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i> Requires attention
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    
@endpush