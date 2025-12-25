@extends('layouts.app')

@section('title', 'Create PO Internal')
@section('page-title', 'Create PO Internal')

@section('content')
<section class="section">
    <div class="card shadow">
        <div class="card-header">
            <h5>Create New PO Internal</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('po-internals.store') }}" method="POST" id="poForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">PO Number <span class="text-danger">*</span></label>
                            <input type="text" name="po_number" class="form-control @error('po_number') is-invalid @enderror" value="{{ old('po_number') }}" required>
                            @error('po_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
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
                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                            @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea name="product_description" class="form-control @error('product_description') is-invalid @enderror" rows="3">{{ old('product_description') }}</textarea>
                    @error('product_description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <h6 class="mb-3">Operations <span class="text-danger">*</span></h6>
                <div id="operationsContainer">
                    <div class="operation-row mb-3">
                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label">Operation</label>
                                <select name="operations[0][operation_id]" class="form-select" required>
                                    <option value="">Select Operation</option>
                                    @foreach($operations as $operation)
                                    <option value="{{ $operation->id }}" data-duration="{{ $operation->estimated_duration_minutes }}">
                                        {{ $operation->name }} ({{ $operation->division->name }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Est. Duration (min)</label>
                                <input type="number" name="operations[0][estimated_duration_minutes]" class="form-control" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sequence</label>
                                <input type="number" name="operations[0][sequence_order]" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-operation" style="display: none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-secondary mb-3" id="addOperation">
                    <i class="bi bi-plus-circle"></i> Add Operation
                </button>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('po-internals.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

@push('scripts')
<script>
let operationIndex = 1;

document.getElementById('addOperation').addEventListener('click', function() {
    const container = document.getElementById('operationsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'operation-row mb-3';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Operation</label>
                <select name="operations[${operationIndex}][operation_id]" class="form-select" required>
                    <option value="">Select Operation</option>
                    @foreach($operations as $operation)
                    <option value="{{ $operation->id }}" data-duration="{{ $operation->estimated_duration_minutes }}">
                        {{ $operation->name }} ({{ $operation->division->name }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Est. Duration (min)</label>
                <input type="number" name="operations[${operationIndex}][estimated_duration_minutes]" class="form-control" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sequence</label>
                <input type="number" name="operations[${operationIndex}][sequence_order]" class="form-control" value="${operationIndex + 1}" min="1" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-operation">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    operationIndex++;
    
    // Show all remove buttons
    document.querySelectorAll('.remove-operation').forEach(btn => btn.style.display = 'block');
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-operation') || e.target.parentElement.classList.contains('remove-operation')) {
        const row = e.target.closest('.operation-row');
        row.remove();
        
        // Hide remove button if only one operation left
        const rows = document.querySelectorAll('.operation-row');
        if (rows.length === 1) {
            rows[0].querySelector('.remove-operation').style.display = 'none';
        }
    }
});

// Auto-fill duration when operation selected
document.addEventListener('change', function(e) {
    if (e.target.name && e.target.name.includes('[operation_id]')) {
        const selected = e.target.options[e.target.selectedIndex];
        const duration = selected.getAttribute('data-duration');
        const row = e.target.closest('.operation-row');
        const durationInput = row.querySelector('[name*="[estimated_duration_minutes]"]');
        if (duration && durationInput) {
            durationInput.value = duration;
        }
    }
});
</script>
@endpush
@endsection