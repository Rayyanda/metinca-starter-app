@extends('repair::layouts.module')

@section('title', 'Detail Laporan')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Laporan: {{ $report->report_code }}</h3>
                <p class="text-subtitle text-muted">{{ $report->machine->code ?? 'Mesin Tidak Diketahui' }}</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('repair.dashboard') }}">Dasbor</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('repair.reports.index') }}">Laporan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $report->report_code }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="row">
            <div class="col-md-8">
                <!-- Report Details Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Detail Laporan</h4>
                        <div>
                            <span class="badge {{ $report->priorityBadgeClass() }}">
                                @if($report->priority == 'low') Rendah
                                @elseif($report->priority == 'medium') Sedang
                                @elseif($report->priority == 'high') Tinggi
                                @elseif($report->priority == 'critical') Kritis
                                @else {{ ucfirst($report->priority) }}
                                @endif
                            </span>
                            <span class="badge {{ $report->statusBadgeClass() }}">{{ $report->status_label }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mesin:</strong> {{ $report->machine->code ?? '-' }} - {{ $report->machine->name ?? '-' }}</p>
                                <p><strong>Departemen:</strong> {{ $report->department }}</p>
                                <p><strong>Lokasi:</strong> {{ $report->location ?: '-' }}</p>
                                <p><strong>Seksi:</strong> {{ $report->section ?: '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Pelapor:</strong> {{ $report->reporter->name ?? '-' }}</p>
                                <p><strong>Teknisi:</strong> {{ $report->assignedTechnician->name ?? '-' }}</p>
                                <p><strong>Dilaporkan Pada:</strong> {{ $report->reported_at->format('d M Y H:i') }}</p>
                                <p><strong>Target Selesai:</strong> {{ $report->target_completed_at?->format('d M Y') ?? '-' }}</p>
                            </div>
                        </div>

                        <hr>

                        <p><strong>Jenis Kerusakan:</strong> {{ $report->damage_type_other ?: $report->damage_type }}</p>
                        <p><strong>Deskripsi:</strong></p>
                        <div class="bg-light p-3 rounded">
                            {{ $report->description }}
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">Progres Alur Kerja</h6>
                                <div class="workflow-timeline">
                                    <!-- Step 1: Uploaded by Operator -->
                                    <div class="timeline-item {{ $report->status === 'uploaded_by_operator' ? 'active' : ($report->reported_at ? 'completed' : '') }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <strong>Diupload oleh Operator</strong>
                                            @if($report->reported_at)
                                            <br><small class="text-muted">{{ $report->reported_at->format('d M Y H:i') }} oleh {{ $report->reporter->name }}</small>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Step 2: Received by Foreman -->
                                    <div class="timeline-item {{ $report->status === 'received_by_foreman_waiting_manager' ? 'active' : (in_array($report->status, ['approved_by_manager_waiting_technician', 'on_fixing_progress', 'done_fixing']) ? 'completed' : '') }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <strong>Diterima oleh Foreman</strong>
                                            @if($report->received_by_foreman_at)
                                            <br><small class="text-muted">{{ $report->received_by_foreman_at->format('d M Y H:i') }} oleh {{ $report->receivedByForeman->name ?? 'Tidak Diketahui' }}</small>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Step 3: Approved by Manager -->
                                    <div class="timeline-item {{ $report->status === 'approved_by_manager_waiting_technician' ? 'active' : (in_array($report->status, ['on_fixing_progress', 'done_fixing']) ? 'completed' : '') }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <strong>Disetujui oleh Manager</strong>
                                            @if($report->approved_by_manager_at)
                                            <br><small class="text-muted">{{ $report->approved_by_manager_at->format('d M Y H:i') }} oleh {{ $report->approvedByManager->name ?? 'Tidak Diketahui' }}</small>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Step 4: Fixing in Progress -->
                                    <div class="timeline-item {{ $report->status === 'on_fixing_progress' ? 'active' : ($report->status === 'done_fixing' ? 'completed' : '') }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <strong>Sedang Diperbaiki</strong>
                                            @if($report->started_fixing_at)
                                            <br><small class="text-muted">Dimulai {{ $report->started_fixing_at->format('d M Y H:i') }}</small>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Step 5: Repair Completed -->
                                    <div class="timeline-item {{ $report->status === 'done_fixing' ? 'completed' : '' }}">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <strong>Selesai Diperbaiki</strong>
                                            @if($report->actual_completed_at)
                                            <br><small class="text-muted">{{ $report->actual_completed_at->format('d M Y H:i') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Foto</h4>
                    </div>
                    <div class="card-body">
                        <h6>Foto Sebelum Perbaikan</h6>
                        <div class="row mb-3">
                            @forelse($beforeAttachments as $attachment)
                            <div class="col-md-3 mb-2">
                                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $attachment->path) }}" class="img-fluid rounded" alt="{{ $attachment->original_name }}">
                                </a>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">Tidak ada foto sebelum perbaikan</p>
                            </div>
                            @endforelse
                        </div>

                        @if($report->isCompleted())
                        <h6>Foto Setelah Perbaikan</h6>
                        <div class="row">
                            @forelse($afterAttachments as $attachment)
                            <div class="col-md-3 mb-2">
                                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $attachment->path) }}" class="img-fluid rounded" alt="{{ $attachment->original_name }}">
                                </a>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">Tidak ada foto setelah perbaikan</p>
                            </div>
                            @endforelse
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status Update Card -->
                @if(!$report->isCompleted())
                @can('repair.update-status')
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Perbarui Status</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('repair.reports.status', $report) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="status" class="form-label">Status Baru</label>
                                <select name="status" id="status" class="form-select" required>
                                    @forelse($allowedTransitions as $nextStatus)
                                    <option value="{{ $nextStatus }}">
                                        @php
                                            $statusLabels = [
                                                'uploaded_by_operator' => 'Diupload oleh Operator',
                                                'received_by_foreman_waiting_manager' => 'Diterima oleh Foreman',
                                                'approved_by_manager_waiting_technician' => 'Disetujui oleh Manager',
                                                'on_fixing_progress' => 'Sedang Diperbaiki',
                                                'done_fixing' => 'Selesai Diperbaiki',
                                                'waiting' => 'Menunggu',
                                                'in_progress' => 'Sedang Dikerjakan',
                                                'done' => 'Selesai',
                                            ];
                                        @endphp
                                        {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                    </option>
                                    @empty
                                    <option value="" disabled>Tidak ada transisi tersedia untuk peran Anda</option>
                                    @endforelse
                                </select>
                                @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control"
                                    placeholder="Opsional: Tambahkan catatan tentang perubahan status ini"></textarea>
                            </div>

                            @if(!$report->assigned_technician_id)
                            <div class="form-group mb-3" id="technician_wrapper" style="display: none;">
                                <label for="assigned_technician_id" class="form-label">Pilih Teknisi <span class="text-danger">*</span></label>
                                <select name="assigned_technician_id" id="assigned_technician_id" class="form-select">
                                    <option value="">Pilih Teknisi</option>
                                    @foreach(\App\Models\User::role('repair.technician')->orderBy('name')->get() as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Wajib diisi saat menyetujui perbaikan</small>
                            </div>
                            @endif

                            <div class="form-group mb-3" id="after_photos_wrapper" style="display: none;">
                                <label for="after_photos" class="form-label">Foto Setelah Perbaikan <span class="text-danger">*</span></label>
                                <input type="file" name="after_photos[]" id="after_photos"
                                    class="form-control" multiple accept="image/*">
                                <small class="text-muted">Wajib diisi saat menandai perbaikan selesai</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100"
                                {{ count($allowedTransitions) === 0 ? 'disabled' : '' }}>
                                Perbarui Status
                            </button>
                        </form>
                    </div>
                </div>
                @endcan
                @endif

                <!-- History Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Riwayat</h4>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @forelse($report->histories->sortByDesc('created_at') as $history)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <strong>
                                    @if($history->action == 'created') Dibuat
                                    @elseif($history->action == 'status_change') Perubahan Status
                                    @elseif($history->action == 'upload_before') Upload Foto Sebelum
                                    @elseif($history->action == 'upload_after') Upload Foto Setelah
                                    @else {{ ucfirst($history->action) }}
                                    @endif
                                </strong>
                                <small class="text-muted">{{ $history->created_at->format('d M Y H:i') }}</small>
                            </div>
                            @if($history->from_status || $history->to_status)
                            <small class="text-muted">
                                {{ $history->from_status ? ucfirst(str_replace('_', ' ', $history->from_status)) : 'Baru' }}
                                &rarr;
                                {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                            </small>
                            @endif
                            @if($history->notes)
                            <p class="mb-0 mt-1 small">{{ $history->notes }}</p>
                            @endif
                            <small class="text-muted">oleh {{ $history->actor->name ?? 'Sistem' }}</small>
                        </div>
                        @empty
                        <p class="text-muted">Tidak ada riwayat</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
.workflow-timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    opacity: 0.5;
}

.timeline-item.active,
.timeline-item.completed {
    opacity: 1;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 8px;
    bottom: -12px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #dee2e6;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item.active .timeline-marker {
    background: #0d6efd;
    box-shadow: 0 0 0 2px #0d6efd;
}

.timeline-item.completed .timeline-marker {
    background: #198754;
    box-shadow: 0 0 0 2px #198754;
}

.timeline-item.completed::before {
    background: #198754;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('status')?.addEventListener('change', function() {
        // Handle after photos requirement
        const afterPhotosWrapper = document.getElementById('after_photos_wrapper');
        const isDoneStatus = this.value === 'done_fixing' || this.value === 'done';
        afterPhotosWrapper.style.display = isDoneStatus ? 'block' : 'none';
        document.getElementById('after_photos').required = isDoneStatus;

        // Handle technician assignment for manager approval
        const technicianWrapper = document.getElementById('technician_wrapper');
        if (technicianWrapper) {
            const isManagerApproval = this.value === 'approved_by_manager_waiting_technician';
            technicianWrapper.style.display = isManagerApproval ? 'block' : 'none';
            const technicianSelect = document.getElementById('assigned_technician_id');
            if (technicianSelect) {
                technicianSelect.required = isManagerApproval;
            }
        }
    });

    // Trigger change event on page load to show relevant fields based on selected status
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            statusSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
