@extends('layouts.app')

@section('title', 'Quality Check - ' . $division)
@section('page-title', $division . ' - Quality Check')

@section('content')
<section class="section">
    <div class="card shadow">
        {{-- <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Quality Check</h5>
            <a href="{{ route('machines.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Machine
            </a>
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('supervisor'))
            @endif
        </div> --}}
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="division_id" class="form-select">
                            <option value="">All Divisions</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="breakdown" {{ request('status') == 'breakdown' ? 'selected' : '' }}>Breakdown</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search machine name or code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Statistics Cards --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">Total Machines</h6>
                                    <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-primary">
                                    <i class="bi bi-tools text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">Available</h6>
                                    <h4 class="mb-0 text-success">{{ $stats['available'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-success">
                                    <i class="bi bi-check-circle text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">In Use</h6>
                                    <h4 class="mb-0 text-primary">{{ $stats['in_use'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-primary">
                                    <i class="bi bi-gear-fill text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">Issues</h6>
                                    <h4 class="mb-0 text-danger">{{ $stats['issues'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-danger">
                                    <i class="bi bi-exclamation-triangle text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Machines Grid/Table View Toggle --}}
            <div class="d-flex justify-content-end mb-3">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary active" id="gridViewBtn">
                        <i class="bi bi-grid-3x3"></i> Grid
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="tableViewBtn">
                        <i class="bi bi-list"></i> Table
                    </button>
                </div>
            </div>

            {{-- Grid View --}}
            <div id="gridView">
                <div class="row">
                    @forelse($machines as $machine)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card shadow h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">{{ $machine->name }}</h5>
                                        <small class="text-muted"><code>{{ $machine->code }}</code></small>
                                    </div>
                                    @php
                                    $statusColors = [
                                        'available' => 'success',
                                        'in_use' => 'primary',
                                        'maintenance' => 'warning',
                                        'breakdown' => 'danger',
                                    ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$machine->status] ?? 'secondary' }}">
                                        {{ ucfirst($machine->status) }}
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <span class="badge bg-secondary">{{ $machine->division->name }}</span>
                                    @if($machine->machine_type)
                                    <span class="badge bg-light text-dark">{{ $machine->machine_type }}</span>
                                    @endif
                                </div>

                                <div class="small text-muted mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><i class="bi bi-gear"></i> Operations:</span>
                                        <strong>{{ $machine->current_operations }} / {{ $machine->max_concurrent_operations }}</strong>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $machine->current_operations >= $machine->max_concurrent_operations ? 'danger' : 'primary' }}" 
                                             style="width: {{ $machine->max_concurrent_operations > 0 ? ($machine->current_operations / $machine->max_concurrent_operations) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </div>

                                @if($machine->status == 'breakdown' || $machine->status == 'maintenance')
                                @php
                                $ongoingDowntime = $machine->downtimes()->ongoing()->latest()->first();
                                @endphp
                                @if($ongoingDowntime)
                                <div class="alert alert-{{ $machine->status == 'breakdown' ? 'danger' : 'warning' }} py-2 mb-2">
                                    <small>
                                        <strong>{{ ucfirst($ongoingDowntime->downtime_type) }}</strong><br>
                                        {{ $ongoingDowntime->getDurationHours() }}h - {{ $ongoingDowntime->reason }}
                                    </small>
                                </div>
                                @endif
                                @endif

                                <div class="d-flex gap-1">
                                    <a href="{{ route('machines.show', $machine->id) }}" class="btn btn-sm btn-info flex-fill">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if($machine->status == 'available')
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#downtimeModal{{ $machine->id }}">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </button>
                                    @elseif($machine->status == 'breakdown' || $machine->status == 'maintenance')
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $machine->id }}">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Report Downtime Modal --}}
                        <div class="modal fade" id="downtimeModal{{ $machine->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('machines.report-downtime', $machine->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Report Downtime - {{ $machine->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Downtime Type <span class="text-danger">*</span></label>
                                                <select name="downtime_type" class="form-select" required>
                                                    <option value="">Select Type</option>
                                                    <option value="breakdown">Breakdown</option>
                                                    <option value="maintenance">Maintenance</option>
                                                    <option value="calibration">Calibration</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-exclamation-triangle"></i> Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Resolve Downtime Modal --}}
                        @if($ongoingDowntime ?? false)
                        <div class="modal fade" id="resolveModal{{ $machine->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('machines.resolve-downtime', $ongoingDowntime->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Resolve Downtime - {{ $machine->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <strong>Type:</strong> {{ ucfirst($ongoingDowntime->downtime_type) }}<br>
                                                <strong>Started:</strong> {{ $ongoingDowntime->started_at->format('d M Y H:i') }}<br>
                                                <strong>Duration:</strong> {{ $ongoingDowntime->getDurationHours() }} hours<br>
                                                <strong>Reason:</strong> {{ $ongoingDowntime->reason }}
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Resolution Notes <span class="text-danger">*</span></label>
                                                <textarea name="resolution_notes" class="form-control" rows="3" required placeholder="Describe what was done to fix the issue..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Resolve
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-tools display-1 text-muted"></i>
                            <p class="text-muted mt-2">No machines found</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Table View (Hidden by default) --}}
            <div id="tableView" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Division</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Operations</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($machines as $machine)
                            <tr>
                                <td><code>{{ $machine->code }}</code></td>
                                <td><strong>{{ $machine->name }}</strong></td>
                                <td>{{ $machine->division->name }}</td>
                                <td>{{ $machine->machine_type ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$machine->status] ?? 'secondary' }}">
                                        {{ ucfirst($machine->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $machine->current_operations }} / {{ $machine->max_concurrent_operations }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('machines.show', $machine->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('machines.edit', $machine->id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $machines->links() }}
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// View Toggle
document.getElementById('gridViewBtn').addEventListener('click', function() {
    document.getElementById('gridView').style.display = 'block';
    document.getElementById('tableView').style.display = 'none';
    this.classList.add('active');
    document.getElementById('tableViewBtn').classList.remove('active');
});

document.getElementById('tableViewBtn').addEventListener('click', function() {
    document.getElementById('gridView').style.display = 'none';
    document.getElementById('tableView').style.display = 'block';
    this.classList.add('active');
    document.getElementById('gridViewBtn').classList.remove('active');
});
</script>
@endpush
@endsection