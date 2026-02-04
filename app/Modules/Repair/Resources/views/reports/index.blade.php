@extends('repair::layouts.module')

@section('title', 'Semua Laporan')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Laporan Kerusakan</h3>
                <p class="text-subtitle text-muted">Daftar semua laporan kerusakan</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('repair.dashboard') }}">Dasbor</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Laporan</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Filter</h4>
                <div>
                    @can('repair.create')
                    <a href="{{ route('repair.reports.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Laporan Baru
                    </a>
                    @endcan
                    @can('repair.export')
                    <a href="{{ route('repair.reports.export', $filters) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Ekspor
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('repair.reports.index') }}">
                    <div class="row">
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="uploaded_by_operator" {{ ($filters['status'] ?? '') == 'uploaded_by_operator' ? 'selected' : '' }}>Diupload oleh Operator</option>
                                <option value="received_by_foreman_waiting_manager" {{ ($filters['status'] ?? '') == 'received_by_foreman_waiting_manager' ? 'selected' : '' }}>Diterima oleh Foreman</option>
                                <option value="approved_by_manager_waiting_technician" {{ ($filters['status'] ?? '') == 'approved_by_manager_waiting_technician' ? 'selected' : '' }}>Disetujui oleh Manager</option>
                                <option value="on_fixing_progress" {{ ($filters['status'] ?? '') == 'on_fixing_progress' ? 'selected' : '' }}>Sedang Diperbaiki</option>
                                <option value="done_fixing" {{ ($filters['status'] ?? '') == 'done_fixing' ? 'selected' : '' }}>Selesai Diperbaiki</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">Semua Prioritas</option>
                                <option value="low" {{ ($filters['priority'] ?? '') == 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="medium" {{ ($filters['priority'] ?? '') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="high" {{ ($filters['priority'] ?? '') == 'high' ? 'selected' : '' }}>Tinggi</option>
                                <option value="critical" {{ ($filters['priority'] ?? '') == 'critical' ? 'selected' : '' }}>Kritis</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="department" class="form-select form-select-sm">
                                <option value="">Semua Departemen</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ ($filters['department'] ?? '') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from" class="form-control form-control-sm" placeholder="Dari" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to" class="form-control form-control-sm" placeholder="Sampai" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('repair.reports.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kode Laporan</th>
                                <th>Mesin</th>
                                <th>Departemen</th>
                                <th>Jenis Kerusakan</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Target Selesai</th>
                                <th>Teknisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->report_code }}</td>
                                <td>{{ $report->machine->code ?? '-' }}</td>
                                <td>{{ $report->department }}</td>
                                <td>{{ $report->damage_type_other ?: $report->damage_type }}</td>
                                <td>
                                    <span class="badge {{ $report->priorityBadgeClass() }}">
                                        @if($report->priority == 'low') Rendah
                                        @elseif($report->priority == 'medium') Sedang
                                        @elseif($report->priority == 'high') Tinggi
                                        @elseif($report->priority == 'critical') Kritis
                                        @else {{ ucfirst($report->priority) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $report->statusBadgeClass() }}">
                                        {{ $report->status_label }}
                                    </span>
                                </td>
                                <td>{{ $report->target_completed_at?->format('d M Y') }}</td>
                                <td>{{ $report->assignedTechnician->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('repair.reports.show', $report) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada laporan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
