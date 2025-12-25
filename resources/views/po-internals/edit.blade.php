@extends('layouts.app')

@section('title', 'Edit PO Internal')
@section('page-title', 'Edit PO Internal: ' . $poInternal->po_number)

@section('content')
<section class="section">
    <form action="{{ route('po-internals.update', $poInternal->id) }}" method="POST" id="poForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Edit PO Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">PO Number <span class="text-danger">*</span></label>
                                    <input type="text" name="po_number" class="form-control @error('po_number') is-invalid @enderror" 
                                           value="{{ old('po_number', $poInternal->po_number) }}" required>
                                    @error('po_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                           value="{{ old('customer_name', $poInternal->customer_name) }}" required>
                                    @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity', $poInternal->quantity) }}" min="1" required>
                                    @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($poInternal->batches->isNotEmpty())
                                    <small class="text-muted">
                                        Already batched: {{ $poInternal->batches->sum('quantity') }} pcs. 
                                        Cannot be less than batched quantity.
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                                           value="{{ old('due_date', $poInternal->due_date?->format('Y-m-d')) }}">
                                    @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Description</label>
                            <textarea name="product_description" class="form-control @error('product_description') is-invalid @enderror" 
                                      rows="3">{{ old('product_description', $poInternal->product_description) }}</textarea>
                            @error('product_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ old('status', $poInternal->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ old('status', $poInternal->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="in_production" {{ old('status', $poInternal->status) == 'in_production' ? 'selected' : '' }}>In Production</option>
                                <option value="completed" {{ old('status', $poInternal->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $poInternal->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Note:</strong> Operations cannot be edited after PO is created. If you need to change operations, please create a new PO.
                        </div>
                    </div>
                </div>

                {{-- Operations Display (Read-only) --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Operations (Read-only)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Operation</th>
                                        <th>Division</th>
                                        <th>Est. Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($poInternal->operations as $poOp)
                                    <tr>
                                        <td>{{ $poOp->sequence_order }}</td>
                                        <td>{{ $poOp->operation->name }}</td>
                                        <td>{{ $poOp->operation->division->name }}</td>
                                        <td>{{ $poOp->estimated_duration_minutes ?? $poOp->operation->estimated_duration_minutes }} min</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Update PO</h5>
                    </div>
                    <div class="card-body">
                        @if($poInternal->batches->isNotEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            This PO has <strong>{{ $poInternal->batches->count() }} batch(es)</strong>. 
                            Some fields may be restricted.
                        </div>
                        @endif

                        <h6>Current Status:</h6>
                        <p class="mb-3">
                            <span class="badge bg-{{ $statusColors[$poInternal->status] ?? 'secondary' }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $poInternal->status)) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('po-internals.show', $poInternal->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Update PO
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection