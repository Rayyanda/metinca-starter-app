@extends('layouts.app')

@section('title', 'PO Internal Detail')
@section('page-title', 'PO Internal: ' . $poInternal->po_number)

@section('content')
<section class="section">
    <div class="row">
        <div class="col-lg-8">
            {{-- PO Information --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">PO Information</h5>
                    <div class="btn-group">
                        @if($poInternal->status == 'draft')
                        <a href="{{ route('po-internals.edit', $poInternal->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('po-internals.destroy', $poInternal->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="mb-2">{{ $poInternal->po_number }}</h3>
                            @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'confirmed' => 'info',
                                'in_production' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$poInternal->status] ?? 'secondary' }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $poInternal->status)) }}
                            </span>
                        </div>
                        @if($poInternal->status == 'draft')
                        <form action="{{ route('po-internals.confirm', $poInternal->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Confirm this PO?')">
                                <i class="bi bi-check-circle"></i> Confirm PO
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Customer</th>
                                    <td><strong>{{ $poInternal->customer_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Total Quantity</th>
                                    <td><strong>{{ number_format($poInternal->quantity) }}</strong> pcs</td>
                                </tr>
                                <tr>
                                    <th>Due Date</th>
                                    <td>
                                        @if($poInternal->due_date)
                                        {{ $poInternal->due_date->format('d M Y') }}
                                        @php
                                        $daysLeft = now()->diffInDays($poInternal->due_date, false);
                                        @endphp
                                        @if($daysLeft < 0)
                                        <span class="badge bg-danger">Overdue</span>
                                        @elseif($daysLeft <= 7)
                                        <span class="badge bg-warning">{{ abs($daysLeft) }} days left</span>
                                        @endif
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Created By</th>
                                    <td>{{ $poInternal->creator->name }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $poInternal->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $poInternal->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($poInternal->product_description)
                    <div class="alert alert-light border">
                        <strong>Product Description:</strong><br>
                        {{ $poInternal->product_description }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Operations List --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Operations Timeline</h5>
                </div>
                <div class="card-body">
                    @if($poInternal->operations->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">No operations defined</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Operation</th>
                                    <th>Division</th>
                                    <th>Est. Duration</th>
                                    <th>QC Requirements</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($poInternal->operations as $poOp)
                                <tr>
                                    <td>
                                        <div class="badge bg-primary rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                            {{ $poOp->sequence_order }}
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $poOp->operation->name }}</strong><br>
                                        <small class="text-muted"><code>{{ $poOp->operation->code }}</code></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $poOp->operation->division->name }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $poOp->estimated_duration_minutes ?? $poOp->operation->estimated_duration_minutes }}</strong> minutes
                                    </td>
                                    <td>
                                        @if($poOp->operation->requires_qc_before)
                                        <span class="badge bg-warning">Before Start</span><br>
                                        @endif
                                        @if($poOp->operation->requires_qc_after)
                                        <span class="badge bg-info">After Complete</span>
                                        @endif
                                        @if(!$poOp->operation->requires_qc_before && !$poOp->operation->requires_qc_after)
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($poOp->notes)
                                        <small>{{ Str::limit($poOp->notes, 50) }}</small>
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Estimated Time:</strong></td>
                                    <td colspan="3">
                                        @php
                                        $totalMinutes = $poInternal->getTotalEstimatedDuration();
                                        $hours = floor($totalMinutes / 60);
                                        $minutes = $totalMinutes % 60;
                                        @endphp
                                        <strong>{{ $hours }}h {{ $minutes }}m</strong> ({{ $totalMinutes }} minutes)
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Batches List --}}
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Batches</h5>
                    @if($poInternal->getRemainingQuantity() > 0 && in_array($poInternal->status, ['confirmed', 'in_production']))
                    <a href="{{ route('batches.create', ['po_internal_id' => $poInternal->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Create Batch
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($poInternal->batches->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam display-4 text-muted"></i>
                        <p class="text-muted mt-2">No batches created yet</p>
                        @if($poInternal->status == 'confirmed')
                        <a href="{{ route('batches.create', ['po_internal_id' => $poInternal->id]) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create First Batch
                        </a>
                        @endif
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Batch Number</th>
                                    <th>Quantity</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($poInternal->batches as $batch)
                                <tr>
                                    <td>
                                        <strong>{{ $batch->batch_number }}</strong>
                                        @if($batch->is_rush_order)
                                        <br><span class="badge bg-danger">RUSH</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($batch->quantity) }} pcs</td>
                                    <td>
                                        @php
                                        $priorityColors = [1 => 'secondary', 2 => 'warning', 3 => 'danger'];
                                        $priorityNames = [1 => 'Normal', 2 => 'High', 3 => 'Urgent'];
                                        @endphp
                                        <span class="badge bg-{{ $priorityColors[$batch->priority] }}">
                                            {{ $priorityNames[$batch->priority] }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                        $batchStatusColors = [
                                            'draft' => 'secondary',
                                            'pending_approval' => 'warning',
                                            'approved' => 'info',
                                            'released' => 'primary',
                                            'in_progress' => 'success',
                                            'completed' => 'dark',
                                            'on_hold' => 'danger',
                                        ];
                                        @endphp
                                        <span class="badge bg-{{ $batchStatusColors[$batch->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 80px;">
                                            <div class="progress-bar" style="width: {{ $batch->getProgressPercentage() }}%">
                                                {{ round($batch->getProgressPercentage()) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $batch->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('batches.show', $batch->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Total Batched:</strong></td>
                                    <td><strong>{{ number_format($poInternal->batches->sum('quantity')) }} pcs</strong></td>
                                    <td colspan="5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Summary Card --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Total Quantity</label>
                        <h4>{{ number_format($poInternal->quantity) }} pcs</h4>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Batched Quantity</label>
                        <h4 class="text-success">{{ number_format($poInternal->batches->sum('quantity')) }} pcs</h4>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Remaining Quantity</label>
                        <h4 class="{{ $poInternal->getRemainingQuantity() > 0 ? 'text-warning' : 'text-success' }}">
                            {{ number_format($poInternal->getRemainingQuantity()) }} pcs
                        </h4>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Total Operations</label>
                        <h4>{{ $poInternal->operations->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Total Batches</label>
                        <h4>{{ $poInternal->batches->count() }}</h4>
                    </div>
                    <div>
                        <label class="text-muted small">Completed Batches</label>
                        <h4 class="text-success">{{ $poInternal->batches->where('status', 'completed')->count() }}</h4>
                    </div>
                </div>
            </div>

            {{-- Production Progress --}}
            @if($poInternal->batches->isNotEmpty())
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Production Progress</h5>
                </div>
                <div class="card-body">
                    @php
                    $totalBatches = $poInternal->batches->count();
                    $completedBatches = $poInternal->batches->where('status', 'completed')->count();
                    $inProgressBatches = $poInternal->batches->where('status', 'in_progress')->count();
                    $progressPercentage = $totalBatches > 0 ? ($completedBatches / $totalBatches) * 100 : 0;
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Overall Progress</span>
                            <strong>{{ round($progressPercentage) }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $progressPercentage }}%"></div>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-4">
                            <h3 class="text-muted">{{ $totalBatches - $completedBatches - $inProgressBatches }}</h3>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-primary">{{ $inProgressBatches }}</h3>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-4">
                            <h3 class="text-success">{{ $completedBatches }}</h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('po-internals.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                        @if($poInternal->status == 'draft')
                        <a href="{{ route('po-internals.edit', $poInternal->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit PO
                        </a>
                        @endif
                        @if($poInternal->getRemainingQuantity() > 0 && in_array($poInternal->status, ['confirmed', 'in_production']))
                        <a href="{{ route('batches.create', ['po_internal_id' => $poInternal->id]) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Batch
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection