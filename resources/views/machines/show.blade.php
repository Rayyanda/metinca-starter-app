@extends('layouts.app')

@section('title', 'Machine Detail')
@section('page-title', $machine->name)

@section('content')
<section class="section">
    <div class="row">
        <div class="col-lg-8">
            {{-- Machine Info --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Machine Information</h5>
                    <div class="btn-group">
                        <a href="{{ route('machines.edit', $machine->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        @if($machine->status == 'available')
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#downtimeModal">
                            <i class="bi bi-exclamation-triangle"></i> Report Issue
                        </button>
                        @elseif($machine->status == 'breakdown' || $machine->status == 'maintenance')
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal">
                            <i class="bi bi-check-circle"></i> Resolve
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="mb-2">{{ $machine->name }}</h3>
                            <code class="fs-5">{{ $machine->code }}</code>
                        </div>
                        @php
                        $statusColors = [
                            'available' => 'success',
                            'in_use' => 'primary',
                            'maintenance' => 'warning',
                            'breakdown' => 'danger',
                        ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$machine->status] ?? 'secondary' }} fs-5">
                            {{ ucfirst($machine->status) }}
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Division</th>
                                    <td><span class="badge bg-secondary">{{ $machine->division->name }}</span></td>
                                </tr>
                                <tr>
                                    <th>Machine Type</th>
                                    <td>{{ $machine->machine_type ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Max Operations</th>
                                    <td><strong>{{ $machine->max_concurrent_operations }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Current Operations</th>
                                    <td>
                                        <strong class="{{ $machine->current_operations >= $machine->max_concurrent_operations ? 'text-danger' : 'text-success' }}">
                                            {{ $machine->current_operations }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Active</th>
                                    <td>
                                        @if($machine->is_active)
                                        <span class="badge bg-success">Yes</span>
                                        @else
                                        <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Can Accept</th>
                                    <td>
                                        @if($machine->canAcceptOperation())
                                        <span class="badge bg-success">Yes</span>
                                        @else
                                        <span class="badge bg-danger">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($machine->specifications)
                    <hr>
                    <h6>Specifications</h6>
                    <div class="alert alert-light">
                        <pre class="mb-0">{{ json_encode($machine->specifications, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Assigned Operations --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Operations ({{ $machine->operations->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($machine->operations->isEmpty())
                    <p class="text-muted text-center py-3">No operations assigned</p>
                    @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Operation</th>
                                    <th>Division</th>
                                    <th>Est. Duration</th>
                                    <th>Setup Time</th>
                                    <th>Default</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($machine->operations as $operation)
                                <tr>
                                    <td>
                                        <strong>{{ $operation->name }}</strong><br>
                                        <small class="text-muted">{{ $operation->code }}</small>
                                    </td>
                                    <td>{{ $operation->division->name }}</td>
                                    <td>{{ $operation->pivot->estimated_duration_minutes }} min</td>
                                    <td>{{ $operation->pivot->setup_time_minutes }} min</td>
                                    <td>
                                        @if($operation->pivot->is_default)
                                        <span class="badge bg-primary">Default</span>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if($operation->pivot->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Downtime History --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Downtime History</h5>
                </div>
                <div class="card-body">
                    @if($machine->downtimes->isEmpty())
                    <p class="text-muted text-center py-3">No downtime records</p>
                    @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Started</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Resolved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($machine->downtimes()->latest('started_at')->take(10)->get() as $downtime)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $downtime->downtime_type == 'breakdown' ? 'danger' : 'warning' }}">
                                            {{ ucfirst($downtime->downtime_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $downtime->started_at->format('d M Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $downtime->getDurationHours() }}</strong> hours
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($downtime->reason, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($downtime->isOngoing())
                                        <span class="badge bg-warning">Ongoing</span>
                                        @else
                                        <span class="badge bg-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($downtime->resolver)
                                        {{ $downtime->resolver->name }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Statistics --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Assigned Operations</label>
                        <h4>{{ $machine->operations->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Total Downtime (30 days)</label>
                        @php
                        $totalDowntime = $machine->downtimes()
                            ->where('started_at', '>=', now()->subDays(30))
                            ->get()
                            ->sum(function($d) { return $d->getDurationMinutes(); });
                        @endphp
                        <h4 class="text-warning">{{ round($totalDowntime / 60, 1) }}h</h4>
                    </div>
                    <div>
                        <label class="text-muted small">Completed Operations (30 days)</label>
                        <h4 class="text-success">
                            {{ $machine->batchOperations()->completed()
                                ->where('actual_completion_at', '>=', now()->subDays(30))
                                ->count() }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Current Status --}}
            @if($machine->status == 'in_use')
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Currently Running</h5>
                </div>
                <div class="card-body">
                    @php
                    $runningOps = $machine->batchOperations()->inProgress()->with('batch', 'operation')->get();
                    @endphp
                    @foreach($runningOps as $op)
                    <div class="mb-2">
                        <strong>{{ $op->batch->batch_number }}</strong><br>
                        <small class="text-muted">{{ $op->operation->name }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('machines.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('machines.edit', $machine->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Machine
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Downtime Modal (sama seperti di index) --}}
{{-- Resolve Modal (sama seperti di index) --}}

@endsection