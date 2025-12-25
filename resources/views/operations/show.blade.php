@extends('layouts.app')

@section('title', 'Operation Detail')
@section('page-title', $operation->name)

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                {{-- Operation Info --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Operation Information</h5>
                        <div class="btn-group">
                            <a href="{{ route('operations.edit', $operation->id) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('operations.destroy', $operation->id) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Code</th>
                                        <td><code class="fs-5">{{ $operation->code }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Operation Name</th>
                                        <td><strong>{{ $operation->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Division</th>
                                        <td><span class="badge bg-secondary">{{ $operation->division->name }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Sequence Order</th>
                                        <td><span class="badge bg-light text-dark">{{ $operation->sequence_order }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="50%">Est. Duration</th>
                                        <td><strong>{{ $operation->estimated_duration_minutes }}</strong> minutes</td>
                                    </tr>
                                    <tr>
                                        <th>QC Before Start</th>
                                        <td>
                                            @if ($operation->requires_qc_before)
                                                <span class="badge bg-warning">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>QC After Complete</th>
                                        <td>
                                            @if ($operation->requires_qc_after)
                                                <span class="badge bg-info">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Not Required</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if ($operation->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Assigned Machines --}}
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Assigned Machines</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addMachineModal">
                            <i class="bi bi-plus-circle"></i> Add Machine
                        </button>
                    </div>
                    <div class="card-body">
                        @if ($operation->machines->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-tools display-4 text-muted"></i>
                                <p class="text-muted mt-2">No machines assigned yet</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addMachineModal">
                                    <i class="bi bi-plus-circle"></i> Add First Machine
                                </button>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Machine</th>
                                            <th>Est. Duration</th>
                                            <th>Setup Time</th>
                                            <th>Hourly Rate</th>
                                            <th>Default</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($operation->machines as $machine)
                                            <tr>
                                                <td>
                                                    <strong>{{ $machine->name }}</strong><br>
                                                    <small class="text-muted">{{ $machine->code }}</small>
                                                </td>
                                                <td>{{ $machine->pivot->estimated_duration_minutes }} min</td>
                                                <td>{{ $machine->pivot->setup_time_minutes }} min</td>
                                                <td>
                                                    @if ($machine->pivot->hourly_rate)
                                                        Rp {{ number_format($machine->pivot->hourly_rate, 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($machine->pivot->is_default)
                                                        <span class="badge bg-primary">Default</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($machine->pivot->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="if(confirm('Remove this machine?')) document.getElementById('remove-machine-{{ $machine->id }}').submit()">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <form id="remove-machine-{{ $machine->id }}"
                                                        action="{{ route('operations.remove-machine', [$operation->id, $machine->id]) }}"
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Operation Flow --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Operation Flow</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Previous Operations</h6>
                                @if ($operation->previousOperations->isEmpty())
                                    <p class="text-muted">This is the first operation</p>
                                @else
                                    <ul class="list-group">
                                        @foreach ($operation->previousOperations as $prev)
                                            <li class="list-group-item">
                                                <i class="bi bi-arrow-left text-primary"></i>
                                                <a href="{{ route('operations.show', $prev->id) }}">
                                                    {{ $prev->name }}
                                                </a>
                                                <span class="badge bg-light text-dark">{{ $prev->sequence_order }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6>Next Operations</h6>
                                @if ($operation->nextOperations->isEmpty())
                                    <p class="text-muted">This is the last operation</p>
                                @else
                                    <ul class="list-group">
                                        @foreach ($operation->nextOperations as $next)
                                            <li class="list-group-item">
                                                <i class="bi bi-arrow-right text-success"></i>
                                                <a href="{{ route('operations.show', $next->id) }}">
                                                    {{ $next->name }}
                                                </a>
                                                <span class="badge bg-light text-dark">{{ $next->sequence_order }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
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
                            <label class="text-muted small">Total Machines</label>
                            <h4>{{ $operation->machines->count() }}</h4>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Active in POs</label>
                            <h4>{{ $operation->poOperations->count() }}</h4>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Batch Operations</label>
                            <h4>{{ $operation->batchOperations->count() }}</h4>
                        </div>
                        <div>
                            <label class="text-muted small">Completed Operations</label>
                            <h4 class="text-success">{{ $operation->batchOperations()->completed()->count() }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('operations.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <a href="{{ route('operations.edit', $operation->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit Operation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Add Machine Modal --}}
    <div class="modal fade" id="addMachineModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('operations.add-machine', $operation->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Machine to Operation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Machine <span class="text-danger">*</span></label>
                            <select name="machine_id" class="form-select" required>
                                <option value="">Choose machine...</option>
                                @foreach ($availableMachines as $machine)
                                    <option value="{{ $machine->id }}">
                                        {{ $machine->name }} ({{ $machine->code }}) - {{ $machine->division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Est. Duration (min) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="estimated_duration_minutes" class="form-control"
                                        value="{{ $operation->estimated_duration_minutes }}" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Setup Time (min)</label>
                                    <input type="number" name="setup_time_minutes" class="form-control" value="0"
                                        min="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hourly Rate (Rp)</label>
                            <input type="number" name="hourly_rate" class="form-control" min="0"
                                step="1000">
                            <small class="text-muted">Optional: Cost per hour for using this machine</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1">
                                <label class="form-check-label">
                                    Set as default machine for this operation
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Machine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
