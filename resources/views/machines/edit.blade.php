@extends('layouts.app')

@section('title', 'Edit Machine')
@section('page-title', 'Edit Machine: ' . $machine->name)

@section('content')
<section class="section">
    <form action="{{ route('machines.update', $machine->id) }}" method="POST">
        @csrf
        @method('PUT')
        
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
                                           value="{{ old('code', $machine->code) }}" required>
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Machine Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $machine->name) }}" required>
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
                                        @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id', $machine->division_id) == $division->id ? 'selected' : '' }}>
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
                                           value="{{ old('machine_type', $machine->machine_type) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Concurrent Operations <span class="text-danger">*</span></label>
                                    <input type="number" name="max_concurrent_operations" class="form-control @error('max_concurrent_operations') is-invalid @enderror" 
                                           value="{{ old('max_concurrent_operations', $machine->max_concurrent_operations) }}" min="1" required>
                                    @error('max_concurrent_operations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="available" {{ old('status', $machine->status) == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="in_use" {{ old('status', $machine->status) == 'in_use' ? 'selected' : '' }}>In Use</option>
                                        <option value="maintenance" {{ old('status', $machine->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="breakdown" {{ old('status', $machine->status) == 'breakdown' ? 'selected' : '' }}>Breakdown</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specifications (JSON)</label>
                            <textarea name="specifications" class="form-control" rows="5">{{ old('specifications', json_encode($machine->specifications, JSON_PRETTY_PRINT)) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                       value="1" {{ old('is_active', $machine->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">
                                    <strong>Active</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Update Machine</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Changing machine settings may affect ongoing operations.
                        </div>

                        <h6>Current Usage:</h6>
                        <ul class="small text-muted">
                            <li>Assigned to <strong>{{ $machine->operations->count() }}</strong> operations</li>
                            <li>Currently running: <strong>{{ $machine->current_operations }}</strong></li>
                            <li>Total completed: <strong>{{ $machine->batchOperations()->completed()->count() }}</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('machines.show', $machine->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Update Machine
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection