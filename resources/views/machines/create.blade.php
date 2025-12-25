@extends('layouts.app')

@section('title', 'Add New Machine')
@section('page-title', 'Add New Machine')

@section('content')
<section class="section">
    <form action="{{ route('machines.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Machine Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Machine Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code') }}" placeholder="MCH-CNC-01" required>
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Unique code for this machine</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Machine Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" placeholder="CNC Machine 1" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Division <span class="text-danger">*</span></label>
                                    <select name="division_id" class="form-select @error('division_id') is-invalid @enderror" required>
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('division_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Machine Type</label>
                                    <input type="text" name="machine_type" class="form-control" 
                                           value="{{ old('machine_type') }}" placeholder="CNC, Injection, Manual, etc">
                                    <small class="text-muted">Optional: Category or type of machine</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Concurrent Operations <span class="text-danger">*</span></label>
                                    <input type="number" name="max_concurrent_operations" class="form-control @error('max_concurrent_operations') is-invalid @enderror" 
                                           value="{{ old('max_concurrent_operations', 1) }}" min="1" required>
                                    @error('max_concurrent_operations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">How many operations can run simultaneously</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="available" selected>Available</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specifications (JSON)</label>
                            <textarea name="specifications" class="form-control" rows="5" 
                                      placeholder='{"capacity": "1000kg", "speed": "100rpm", "power": "5kW"}'></textarea>
                            <small class="text-muted">Optional: Technical specifications in JSON format</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    <strong>Active</strong>
                                    <small class="text-muted d-block">Only active machines can be used in production</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Quick Guide</h5>
                    </div>
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle text-primary"></i> Machine Code</h6>
                        <p class="small text-muted">
                            Use a consistent naming convention, e.g., MCH-[DIVISION]-[NUMBER]
                        </p>

                        <h6 class="mt-3"><i class="bi bi-gear text-primary"></i> Concurrent Operations</h6>
                        <p class="small text-muted">
                            Set to 1 for most machines. Set higher if the machine can handle multiple batches simultaneously.
                        </p>

                        <h6 class="mt-3"><i class="bi bi-tools text-primary"></i> After Creation</h6>
                        <p class="small text-muted">
                            After creating the machine, you can assign operations that this machine can perform.
                        </p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('machines.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Machine
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection