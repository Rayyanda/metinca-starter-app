@extends('layouts.app')

@section('title', 'Batches List')
@section('page-title', 'Batches')

@section('content')
<section class="section">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Batch Management</h5>
            <a href="{{ route('batches.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Batch
            </a>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority" class="form-select">
                            <option value="">All Priority</option>
                            <option value="1" {{ request('priority') == '1' ? 'selected' : '' }}>Normal</option>
                            <option value="2" {{ request('priority') == '2' ? 'selected' : '' }}>High</option>
                            <option value="3" {{ request('priority') == '3' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="rush_order" class="form-select">
                            <option value="">All Orders</option>
                            <option value="1" {{ request('rush_order') == '1' ? 'selected' : '' }}>Rush Orders Only</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search batch or PO number..." value="{{ request('search') }}">
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
                                    <h6 class="text-muted mb-1">Total Batches</h6>
                                    <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-primary">
                                    <i class="bi bi-box-seam text-white"></i>
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
                                    <h6 class="text-muted mb-1">In Progress</h6>
                                    <h4 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-success">
                                    <i class="bi bi-arrow-repeat text-white"></i>
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
                                    <h6 class="text-muted mb-1">Rush Orders</h6>
                                    <h4 class="mb-0">{{ $stats['rush'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-danger">
                                    <i class="bi bi-exclamation-triangle text-white"></i>
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
                                    <h6 class="text-muted mb-1">Completed</h6>
                                    <h4 class="mb-0">{{ $stats['completed'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-info">
                                    <i class="bi bi-check-circle text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Batches Table --}}
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Batch Number</th>
                            <th>PO Number</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Current Op</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <strong>{{ $batch->batch_number }}</strong>
                                @if($batch->is_rush_order)
                                <br><span class="badge bg-danger">RUSH</span>
                                @endif
                            </td>
                            <td>{{ $batch->poInternal->po_number }}</td>
                            <td>{{ Str::limit($batch->poInternal->customer_name, 20) }}</td>
                            <td>{{ $batch->quantity }} pcs</td>
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
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'pending_approval' => 'warning',
                                    'approved' => 'info',
                                    'released' => 'primary',
                                    'in_progress' => 'success',
                                    'completed' => 'dark',
                                    'on_hold' => 'danger',
                                    'rejected' => 'danger',
                                ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$batch->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px; min-width: 100px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $batch->getProgressPercentage() }}%"
                                         aria-valuenow="{{ $batch->getProgressPercentage() }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ round($batch->getProgressPercentage()) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($batch->currentOperation)
                                <small class="text-muted">{{ $batch->currentOperation->name }}</small>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <small>{{ $batch->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('batches.show', $batch->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
                                    @if(in_array($batch->status, ['draft', 'rejected']))
                                    <a href="{{ route('batches.edit', $batch->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif

                                    @if($batch->status == 'draft')
                                    <form action="{{ route('batches.submit-approval', $batch->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="Submit for Approval" onclick="return confirm('Submit this batch for approval?')">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </form>
                                    @endif

                                    @if(in_array($batch->status, ['draft', 'pending_approval']))
                                    <form action="{{ route('batches.destroy', $batch->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-2">No batches found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</section>
@endsection