@extends('repair::layouts.module')

@section('title', 'Buat Laporan')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Buat Laporan Kerusakan</h3>
                <p class="text-subtitle text-muted">Laporkan kerusakan mesin baru</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('repair.dashboard') }}">Dasbor</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('repair.reports.index') }}">Laporan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Buat</li>
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
                <h4 class="card-title">Laporan Kerusakan Baru</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('repair.reports.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="machine_id" class="form-label">Mesin <span class="text-danger">*</span></label>
                                <select name="machine_id" id="machine_id" class="form-select @error('machine_id') is-invalid @enderror" required>
                                    <option value="">Pilih Mesin</option>
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

                        @can('repair.assign')
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="assigned_technician_id" class="form-label">Pilih Teknisi</label>
                                <select name="assigned_technician_id" id="assigned_technician_id" class="form-select @error('assigned_technician_id') is-invalid @enderror">
                                    <option value="">Akan ditugaskan oleh Manager</option>
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
                        @endcan
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="department" class="form-label">Departemen <span class="text-danger">*</span></label>
                                <input type="text" name="department" id="department" class="form-control @error('department') is-invalid @enderror" value="{{ old('department') }}" required>
                                @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="location" class="form-label">Lokasi</label>
                                <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
                                @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="section" class="form-label">Seksi</label>
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
                                <label for="damage_type" class="form-label">Jenis Kerusakan <span class="text-danger">*</span></label>
                                <select name="damage_type" id="damage_type" class="form-select @error('damage_type') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="Mechanical" {{ old('damage_type') == 'Mechanical' ? 'selected' : '' }}>Mekanik</option>
                                    <option value="Electrical" {{ old('damage_type') == 'Electrical' ? 'selected' : '' }}>Elektrik</option>
                                    <option value="Hydraulic" {{ old('damage_type') == 'Hydraulic' ? 'selected' : '' }}>Hidrolik</option>
                                    <option value="Pneumatic" {{ old('damage_type') == 'Pneumatic' ? 'selected' : '' }}>Pneumatik</option>
                                    <option value="Software" {{ old('damage_type') == 'Software' ? 'selected' : '' }}>Perangkat Lunak</option>
                                    <option value="Other" {{ old('damage_type') == 'Other' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('damage_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6" id="damage_type_other_wrapper" style="display: none;">
                            <div class="form-group mb-3">
                                <label for="damage_type_other" class="form-label">Sebutkan Jenis Lainnya</label>
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
                                <label for="priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="">Pilih Prioritas</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah (30 hari)</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Sedang (21 hari)</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi (10 hari)</option>
                                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Kritis (3 hari)</option>
                                </select>
                                @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="target_completed_at" class="form-label">Target Selesai</label>
                                <input type="date" name="target_completed_at" id="target_completed_at" class="form-control @error('target_completed_at') is-invalid @enderror" value="{{ old('target_completed_at') }}" min="{{ date('Y-m-d') }}">
                                @error('target_completed_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="before_photos" class="form-label">Foto Sebelum Perbaikan <span class="text-danger">*</span></label>
                        <input type="file" name="before_photos[]" id="before_photos" class="form-control @error('before_photos') is-invalid @enderror @error('before_photos.*') is-invalid @enderror" multiple accept="image/*" required>
                        <small class="text-muted">Upload foto kerusakan (jpg, jpeg, png, webp - maks 5MB per file)</small>
                        @error('before_photos')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('before_photos.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('repair.reports.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Buat Laporan</button>
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
