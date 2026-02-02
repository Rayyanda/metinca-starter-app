@extends('repair::layouts.module')

@section('title', 'All Reports')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Damage Reports</h3>
                <p class="text-subtitle text-muted">List of all damage reports</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('repair.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reports</li>
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
                <h4 class="card-title">Filters</h4>
                <div>
                    @can('repair.create')
                    <a href="{{ route('repair.reports.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> New Report
                    </a>
                    @endcan
                    @can('repair.export')
                    <a href="{{ route('repair.reports.export', $filters) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Export
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('repair.reports.index') }}">
                    <div class="row">
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="waiting" {{ ($filters['status'] ?? '') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="in_progress" {{ ($filters['status'] ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="done" {{ ($filters['status'] ?? '') == 'done' ? 'selected' : '' }}>Done</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">All Priority</option>
                                <option value="low" {{ ($filters['priority'] ?? '') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ ($filters['priority'] ?? '') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ ($filters['priority'] ?? '') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ ($filters['priority'] ?? '') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="department" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ ($filters['department'] ?? '') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from" class="form-control form-control-sm" placeholder="From" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to" class="form-control form-control-sm" placeholder="To" value="{{ $filters['to'] ?? '' }}">
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
                                <th>Report Code</th>
                                <th>Machine</th>
                                <th>Department</th>
                                <th>Damage Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Target Date</th>
                                <th>Technician</th>
                                <th>Actions</th>
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
                                        {{ ucfirst($report->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $report->statusBadgeClass() }}">
                                        {{ str_replace('_', ' ', ucfirst($report->status)) }}
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
                                <td colspan="9" class="text-center">No reports found</td>
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
