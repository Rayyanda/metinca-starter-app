@extends('layouts.app')

@section('title', 'Create Operation')
@section('page-title', 'Create New Operation')

@section('content')
<section class="section">
    <form action="{{ route('operations.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Operation Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Operation Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code') }}" placeholder="OP-WAX-01" required>
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Unique code for this operation</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Operation Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" placeholder="Wax Injection Process" required>
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
                                    <label class="form-label">Sequence Order <span class="text-danger">*</span></label>
                                    <input type="number" name="sequence_order" class="form-control @error('sequence_order') is-invalid @enderror" 
                                           value="{{ old('sequence_order', 1) }}" min="1" required>
                                    @error('sequence_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Global sequence in production flow</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estimated Duration (minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="estimated_duration_minutes" class="form-control @error('estimated_duration_minutes') is-invalid @enderror" 
                                   value="{{ old('estimated_duration_minutes', 60) }}" min="1" required>
                            @error('estimated_duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Default estimated time for this operation</small>
                        </div>

                        <hr>

                        <h6 class="mb-3">Quality Control Requirements</h6>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="requires_qc_before" id="qcBefore" 
                                       value="1" {{ old('requires_qc_before') ? 'checked' : '' }}>
                                <label class="form-check-label" for="qcBefore">
                                    <strong>Requires QC Before Start</strong>
                                    <small class="text-muted d-block">Quality check must be performed before starting this operation</small>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="requires_qc_after" id="qcAfter" 
                                       value="1" {{ old('requires_qc_after', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="qcAfter">
                                    <strong>Requires QC After Complete</strong>
                                    <small class="text-muted d-block">Quality check must be performed after completing this operation</small>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    <strong>Active</strong>
                                    <small class="text-muted d-block">Only active operations can be used in production</small>
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
                        <h6><i class="bi bi-info-circle text-primary"></i> What is an Operation?</h6>
                        <p class="small text-muted">
                            An operation is a specific step in the production process. For example: Wax Injection, CNC Milling, Assembly, etc.
                        </p>

                        <h6 class="mt-3"><i class="bi bi-list-ol text-primary"></i> Sequence Order</h6>
                        <p class="small text-muted">
                            This determines the order of operations in the production flow. Lower numbers go first.
                        </p>

                        <h6 class="mt-3"><i class="bi bi-clipboard-check text-primary"></i> Quality Checks</h6>
                        <p class="small text-muted">
                            Enable QC requirements if this operation needs quality inspection before starting or after completion.
                        </p>

                        <h6 class="mt-3"><i class="bi bi-tools text-primary"></i> Machines</h6>
                        <p class="small text-muted">
                            After creating the operation, you can assign machines that can perform this operation in the detail page.
                        </p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('operations.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Operation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection