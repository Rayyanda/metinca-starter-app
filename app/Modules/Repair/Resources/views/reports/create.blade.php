@extends('repair::layouts.module')

@section('title', 'Create Report')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Create Damage Report</h3>
                <p class="text-subtitle text-muted">Report a new machine damage</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('repair.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('repair.reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">New Damage Report</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('repair.reports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="machine_id" class="form-label">Machine <span class="text-danger">*</span></label>
                                <select name="machine_id" id="machine_id" class="form-select @error('machine_id') is-invalid @enderror" required>
                                    <option value="">Select Machine</option>
                                    @foreach($machines as $machine)
                                    <option value="{{ $machine->id }}"
                                            data-department="{{ $machine->department }}"
                                            data-location="{{ $machine->location }}"
                                            {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                        {{ $machine->code }} - {{ $machine->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('machine_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="assigned_technician_id" class="form-label">Assign Technician <span class="text-danger">*</span></label>
                                <select name="assigned_technician_id" id="assigned_technician_id" class="form-select @error('assigned_technician_id') is-invalid @enderror" required>
                                    <option value="">Select Technician</option>
                                    @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ old('assigned_technician_id') == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('assigned_technician_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                <input type="text" name="department" id="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department') }}" required>
                                @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
                                @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="section" class="form-label">Section</label>
                                <input type="text" name="section" id="section" class="form-control @error('section') is-invalid @enderror" value="{{ old('section') }}">
                                @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="damage_type" class="form-label">Damage Type <span class="text-danger">*</span></label>
                                <select name="damage_type" id="damage_type" class="form-select @error('damage_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="Mechanical" {{ old('damage_type') == 'Mechanical' ? 'selected' : '' }}>Mechanical</option>
                                    <option value="Electrical" {{ old('damage_type') == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                    <option value="Hydraulic" {{ old('damage_type') == 'Hydraulic' ? 'selected' : '' }}>Hydraulic</option>
                                    <option value="Pneumatic" {{ old('damage_type') == 'Pneumatic' ? 'selected' : '' }}>Pneumatic</option>
                                    <option value="Software" {{ old('damage_type') == 'Software' ? 'selected' : '' }}>Software</option>
                                    <option value="Other" {{ old('damage_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('damage_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6" id="damage_type_other_wrapper" style="display: none;">
                            <div class="form-group mb-3">
                                <label for="damage_type_other" class="form-label">Specify Other Type</label>
                                <input type="text" name="damage_type_other" id="damage_type_other" class="form-control @error('damage_type_other') is-invalid @enderror" value="{{ old('damage_type_other') }}">
                                @error('damage_type_other')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low (30 days)</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium (21 days)</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High (10 days)</option>
                                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical (3 days)</option>
                                </select>
                                @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="target_completed_at" class="form-label">Target Completion Date</label>
                                <input type="date" name="target_completed_at" id="target_completed_at" class="form-control @error('target_completed_at') is-invalid @enderror" value="{{ old('target_completed_at') }}" min="{{ date('Y-m-d') }}">
                                @error('target_completed_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="before_photos" class="form-label">Before Photos <span class="text-danger">*</span></label>
                        <input type="file" name="before_photos[]" id="before_photos" class="form-control @error('before_photos') is-invalid @enderror @error('before_photos.*') is-invalid @enderror" multiple accept="image/*" required>
                        <small class="text-muted">Upload photos of the damage (jpg, jpeg, png, webp - max 5MB each)</small>
                        @error('before_photos')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('before_photos.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('repair.reports.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Report</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('machine_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('department').value = selected.dataset.department || '';
        document.getElementById('location').value = selected.dataset.location || '';
    });

    document.getElementById('damage_type').addEventListener('change', function() {
        const wrapper = document.getElementById('damage_type_other_wrapper');
        wrapper.style.display = this.value === 'Other' ? 'block' : 'none';
    });

    // Trigger on load if Other is selected
    if (document.getElementById('damage_type').value === 'Other') {
        document.getElementById('damage_type_other_wrapper').style.display = 'block';
    }
</script>
@endpush
