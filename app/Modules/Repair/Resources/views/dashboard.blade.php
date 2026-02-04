@extends('repair::layouts.module')

@section('title', 'Dasbor')

@section('content')
<div class="page-heading">
    <h3>Dasbor Perbaikan</h3>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12 col-lg-9">
            <div class="row">
                <div class="col-6 col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-body px-4 py-4-5">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon purple mb-2">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Total Laporan</h6>
                                    <h6 class="font-extrabold mb-0">{{ $stats['total'] }}</h6>
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
                                    <div class="stats-icon orange mb-2">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Menunggu</h6>
                                    <h6 class="font-extrabold mb-0">{{ $stats['waiting'] }}</h6>
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
                                        <i class="bi bi-gear"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Sedang Dikerjakan</h6>
                                    <h6 class="font-extrabold mb-0">{{ $stats['in_progress'] }}</h6>
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
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Selesai</h6>
                                    <h6 class="font-extrabold mb-0">{{ $stats['done'] }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Laporan Terbaru</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kode Laporan</th>
                                            <th>Mesin</th>
                                            <th>Prioritas</th>
                                            <th>Status</th>
                                            <th>Dilaporkan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentReports as $report)
                                        <tr>
                                            <td>
                                                <a href="{{ route('repair.reports.show', $report) }}">
                                                    {{ $report->report_code }}
                                                </a>
                                            </td>
                                            <td>{{ $report->machine->code ?? '-' }}</td>
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
                                            <td>{{ $report->reported_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada laporan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h4>Aksi Cepat</h4>
                </div>
                <div class="card-body">
                    @can('repair.create')
                    <a href="{{ route('repair.reports.create') }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-plus-circle me-2"></i> Laporan Baru
                    </a>
                    @endcan
                    <a href="{{ route('repair.reports.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-list me-2"></i> Semua Laporan
                    </a>
                    @can('repair.export')
                    <a href="{{ route('repair.reports.export') }}" class="btn btn-outline-success w-100">
                        <i class="bi bi-download me-2"></i> Ekspor
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
