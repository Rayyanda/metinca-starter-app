@extends('layouts.app')

@section('title', 'Quality Check - ' . auth()->user()->name)
@section('page-title', auth()->user()->name . ' - Quality Check')

@section('content')
<section class="section">
    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-warning me-3">
                            <i class="bi bi-hourglass-split text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Pending QC</h6>
                            <h4 class="mb-0">{{ $stats['pending'] ?? 0 }}</h4>
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
                            <i class="bi bi-check-circle-fill text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Passed Today</h6>
                            <h4 class="mb-0">{{ $stats['passed_today'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-danger me-3">
                            <i class="bi bi-x-circle-fill text-white"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0">Failed Today</h6>
                            <h4 class="mb-0">{{ $stats['failed_today'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QC Tabs --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ !request('type') || request('type') == 'after_complete' ? 'active' : '' }}" 
               href="{{ route('qc.pending', ['type' => 'after_complete']) }}">
                <i class="bi bi-arrow-up-circle"></i> After Complete
                @if(($pendingAfter ?? 0) > 0)
                <span class="badge bg-warning">{{ $pendingAfter }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('type') == 'before_start' ? 'active' : '' }}" 
               href="{{ route('qc.pending', ['type' => 'before_start']) }}">
                <i class="bi bi-arrow-down-circle"></i> Before Start
                @if(($pendingBefore ?? 0) > 0)
                <span class="badge bg-info">{{ $pendingBefore }}</span>
                @endif
            </a>
        </li>
    </ul>

    {{-- QC List Card --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-check"></i> 
                Operations Pending Quality Check
                <span class="badge bg-secondary">{{ auth()->user()->division->name }} Division</span>
            </h5>
        </div>
        <div class="card-body">
            @if($operations->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-clipboard-check display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">No operations pending QC</h5>
                <p class="text-muted">All operations have been checked or no operations available.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Batch Number</th>
                            <th>PO Number</th>
                            <th>Operation</th>
                            <th>Operator</th>
                            <th>Machine</th>
                            <th>Quantity</th>
                            <th>Completed At</th>
                            <th>Waiting Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operations as $op)
                        <tr>
                            <td>
                                <strong>{{ $op->batch->batch_number }}</strong>
                                @if($op->batch->is_rush_order)
                                <br><span class="badge bg-danger">RUSH</span>
                                @endif
                            </td>
                            <td>{{ $op->batch->poInternal->po_number }}</td>
                            <td>
                                <strong>{{ $op->operation->name }}</strong>
                                @if($op->status == 'qc_pending')
                                <br><small class="text-muted"><i class="bi bi-arrow-up"></i> After Complete</small>
                                @else
                                <br><small class="text-muted"><i class="bi bi-arrow-down"></i> Before Start</small>
                                @endif
                            </td>
                            <td>
                                @if($op->operator)
                                {{ $op->operator->name }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($op->machine)
                                <span class="badge bg-secondary">{{ $op->machine->name }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $op->batch->quantity }}</strong> pcs
                                @if($op->actual_good_quantity > 0 || $op->actual_reject_quantity > 0)
                                <br>
                                <small class="text-success">Good: {{ $op->actual_good_quantity }}</small>
                                <small class="text-danger">Reject: {{ $op->actual_reject_quantity }}</small>
                                @endif
                            </td>
                            <td>
                                @if($op->actual_completion_at)
                                {{ $op->actual_completion_at->format('d M Y H:i') }}
                                @else
                                <span class="text-muted">Not completed</span>
                                @endif
                            </td>
                            <td>
                                @if($op->actual_completion_at)
                                @php
                                $waitingMinutes = $op->actual_completion_at->diffInMinutes(now());
                                $hours = floor($waitingMinutes / 60);
                                $minutes = $waitingMinutes % 60;
                                @endphp
                                <span class="{{ $waitingMinutes > 60 ? 'text-danger' : 'text-muted' }}">
                                    @if($hours > 0)
                                    {{ $hours }}h 
                                    @endif
                                    {{ $minutes }}m
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('qc.check-form', $op->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-clipboard-check"></i> Check Now
                                </a>
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