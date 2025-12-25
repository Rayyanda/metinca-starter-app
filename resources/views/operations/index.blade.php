@extends('layouts.app')

@section('title', 'Master Operations')
@section('page-title', 'Master Operations')

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Operations Management</h5>
            <a href="{{ route('operations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Operation
            </a>
        </div>
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
                        <select name="requires_qc" class="form-select">
                            <option value="">All QC Requirements</option>
                            <option value="before" {{ request('requires_qc') == 'before' ? 'selected' : '' }}>QC Before Start</option>
                            <option value="after" {{ request('requires_qc') == 'after' ? 'selected' : '' }}>QC After Complete</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="is_active" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Active Only</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Statistics --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-1">Total Operations</h6>
                                    <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-primary">
                                    <i class="bi bi-gear text-white"></i>
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
                                    <h6 class="text-muted mb-1">Active</h6>
                                    <h4 class="mb-0">{{ $stats['active'] ?? 0 }}</h4>
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
                                    <h6 class="text-muted mb-1">Requires QC</h6>
                                    <h4 class="mb-0">{{ $stats['requires_qc'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-warning">
                                    <i class="bi bi-clipboard-check text-white"></i>
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
                                    <h6 class="text-muted mb-1">Divisions</h6>
                                    <h4 class="mb-0">{{ $stats['divisions'] ?? 0 }}</h4>
                                </div>
                                <div class="stats-icon bg-info">
                                    <i class="bi bi-building text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operations Table --}}
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Operation Name</th>
                            <th>Division</th>
                            <th>Sequence</th>
                            <th>Est. Duration</th>
                            <th>QC Requirements</th>
                            <th>Machines</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($operations as $operation)
                        <tr>
                            <td><code>{{ $operation->code }}</code></td>
                            <td><strong>{{ $operation->name }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ $operation->division->name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $operation->sequence_order }}</span>
                            </td>
                            <td>{{ $operation->estimated_duration_minutes }} min</td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($operation->requires_qc_before)
                                    <span class="badge bg-warning">
                                        <i class="bi bi-arrow-down"></i> Before Start
                                    </span>
                                    @endif
                                    @if($operation->requires_qc_after)
                                    <span class="badge bg-info">
                                        <i class="bi bi-arrow-up"></i> After Complete
                                    </span>
                                    @endif
                                    @if(!$operation->requires_qc_before && !$operation->requires_qc_after)
                                    <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($operation->machines->count() > 0)
                                <span class="badge bg-primary">{{ $operation->machines->count() }} machines</span>
                                @else
                                <span class="text-muted">No machines</span>
                                @endif
                            </td>
                            <td>
                                @if($operation->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('operations.show', $operation->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('operations.edit', $operation->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('operations.destroy', $operation->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure? This will affect all related data.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-2">No operations found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $operations->links() }}
            </div>
        </div>
    </div>
</section>
@endsection