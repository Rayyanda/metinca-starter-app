@extends('layouts.app')

@section('title', 'My Operations')
@section('page-title', 'My Operations')

@section('content')
<section class="section">
    {{-- Quick Stats --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary me-3">
                            <i class="bi bi-gear-fill text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">My Active Operations</h6>
                            <h4 class="mb-0">{{ $myActiveOperations }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success me-3">
                            <i class="bi bi-play-circle-fill text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Ready to Start</h6>
                            <h4 class="mb-0">{{ $readyOperations }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info me-3">
                            <i class="bi bi-check-circle-fill text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Completed Today</h6>
                            <h4 class="mb-0">{{ $completedToday }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ !request('status') || request('status') == 'ready' ? 'active' : '' }}" 
               href="{{ route('operations.my', ['status' => 'ready']) }}">
                <i class="bi bi-play-circle"></i> Ready to Start
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('status') == 'in_progress' ? 'active' : '' }}" 
               href="{{ route('operations.my', ['status' => 'in_progress']) }}">
                <i class="bi bi-arrow-repeat"></i> In Progress
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('status') == 'qc_pending' ? 'active' : '' }}" 
               href="{{ route('operations.my', ['status' => 'qc_pending']) }}">
                <i class="bi bi-hourglass-split"></i> Waiting QC
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}" 
               href="{{ route('operations.my', ['status' => 'completed']) }}">
                <i class="bi bi-check-circle"></i> Completed
            </a>
        </li>
    </ul>

    {{-- Operations List --}}
    <div class="card">
        <div class="card-body">
            @if($operations->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No operations found</h5>
                <p class="text-muted">There are no operations matching your filter criteria.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Batch Number</th>
                            <th>PO Number</th>
                            <th>Operation</th>
                            <th>Machine</th>
                            <th>Status</th>
                            <th>Est. Time</th>
                            <th>Progress</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operations as $op)
                        <tr>
                            <td>
                                <strong>{{ $op->batch->batch_number }}</strong>
                                @if($op->batch->is_rush_order)
                                <span class="badge bg-danger ms-1">RUSH</span>
                                @endif
                            </td>
                            <td>{{ $op->batch->poInternal->po_number }}</td>
                            <td>
                                <div>
                                    <strong>{{ $op->operation->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $op->operation->division->name }}</small>
                                </div>
                            </td>
                            <td>
                                @if($op->machine)
                                <span class="badge bg-primary">{{ $op->machine->name }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $statusColors = [
                                    'ready' => 'success',
                                    'in_progress' => 'primary',
                                    'qc_pending' => 'warning',
                                    'completed' => 'info',
                                    'on_hold' => 'secondary',
                                ];
                                $color = $statusColors[$op->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $op->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($op->status == 'in_progress' && $op->estimated_completion_at)
                                <div>
                                    <small class="text-muted">Started:</small><br>
                                    {{ $op->actual_start_at->format('H:i') }}
                                    <hr class="my-1">
                                    <small class="text-muted">Est. End:</small><br>
                                    <strong>{{ $op->estimated_completion_at->format('H:i') }}</strong>
                                </div>
                                @else
                                {{ $op->estimated_duration_minutes }} min
                                @endif
                            </td>
                            <td>
                                @if($op->status == 'in_progress' && $op->actual_start_at && $op->estimated_completion_at)
                                @php
                                $now = now();
                                $total = $op->actual_start_at->diffInMinutes($op->estimated_completion_at);
                                $elapsed = $op->actual_start_at->diffInMinutes($now);
                                $percentage = $total > 0 ? min(($elapsed / $total) * 100, 100) : 0;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $percentage > 100 ? 'bg-danger' : 'bg-primary' }}" 
                                         style="width: {{ $percentage }}%">
                                        {{ round($percentage) }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ round($elapsed) }} / {{ $total }} min</small>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($op->status == 'ready')
                                    <a href="{{ route('operations.start', $op->id) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-play-fill"></i> Start
                                    </a>
                                    @elseif($op->status == 'in_progress')
                                    <a href="{{ route('operations.complete', $op->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-check-circle"></i> Complete
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#pauseModal{{ $op->id }}">
                                        <i class="bi bi-pause-fill"></i>
                                    </button>
                                    @elseif($op->status == 'on_hold')
                                    <form action="{{ route('operations.resume', $op->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="bi bi-play-fill"></i> Resume
                                        </button>
                                    </form>
                                    @elseif($op->status == 'qc_pending')
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-hourglass-split"></i> Waiting QC
                                    </button>
                                    @elseif($op->status == 'completed')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Done
                                    </span>
                                    @endif
                                </div>

                                {{-- Pause Modal --}}
                                @if($op->status == 'in_progress')
                                <div class="modal fade" id="pauseModal{{ $op->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('operations.pause', $op->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Pause Operation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Pause <span class="text-danger">*</span></label>
                                                        <select name="paused_reason" class="form-select" required>
                                                            <option value="">Select Reason</option>
                                                            <option value="machine_breakdown">Machine Breakdown</option>
                                                            <option value="material_shortage">Material Shortage</option>
                                                            <option value="break_time">Break Time</option>
                                                            <option value="shift_change">Shift Change</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="bi bi-pause-fill"></i> Pause
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $operations->links() }}
            </div>
            @endif
        </div>
    </div>
</section>
@endsection