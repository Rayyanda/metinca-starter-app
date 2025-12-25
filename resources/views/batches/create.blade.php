@extends('layouts.app')

@section('title', 'Create Batch')
@section('page-title', 'Create New Batch')

@section('content')
<section class="section">
    <form action="{{ route('batches.store') }}" method="POST" id="batchForm">
        @csrf
        
        <div class="row">
            {{-- Batch Details --}}
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5>Batch Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Select PO Internal <span class="text-danger">*</span></label>
                                    <select name="po_internal_id" id="poSelect" class="form-select @error('po_internal_id') is-invalid @enderror" required>
                                        <option value="">-- Select PO --</option>
                                        @foreach($poInternals as $po)
                                        <option value="{{ $po->id }}" 
                                                data-customer="{{ $po->customer_name }}"
                                                data-quantity="{{ $po->quantity }}"
                                                data-remaining="{{ $po->getRemainingQuantity() }}"
                                                data-operations="{{ json_encode($po->operations) }}"
                                                {{ old('po_internal_id') == $po->id ? 'selected' : '' }}>
                                            {{ $po->po_number }} - {{ $po->customer_name }} (Remaining: {{ $po->getRemainingQuantity() }} pcs)
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('po_internal_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- PO Details (shown after selection) --}}
                        <div id="poDetails" style="display: none;">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Customer:</strong><br>
                                        <span id="poCustomer">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Total Quantity:</strong><br>
                                        <span id="poQuantity">-</span> pcs
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Remaining:</strong><br>
                                        <span id="poRemaining" class="text-success">-</span> pcs
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Batch Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="batchQuantity" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity') }}" min="1" required>
                                    @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max: <span id="maxQuantity">-</span> pcs</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="1" {{ old('priority', 1) == 1 ? 'selected' : '' }}>Normal</option>
                                        <option value="2" {{ old('priority') == 2 ? 'selected' : '' }}>High</option>
                                        <option value="3" {{ old('priority') == 3 ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_rush_order" id="rushOrder" 
                                       value="1" {{ old('is_rush_order') ? 'checked' : '' }}>
                                <label class="form-check-label" for="rushOrder">
                                    <strong>Rush Order</strong>
                                    <small class="text-muted d-block">Mark this batch as rush order for high priority processing</small>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any special instructions or notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Operations Preview --}}
                <div class="card shadow mt-3" id="operationsCard" style="display: none;">
                    <div class="card-header">
                        <h5>Operations Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div id="operationsTimeline">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar --}}
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header ">
                        <h5 class="mb-0">Batch Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Batch Number</label>
                            <h6 id="summaryBatchNumber" class="text-muted">Will be auto-generated</h6>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="text-muted small">PO Number</label>
                            <h6 id="summaryPO">-</h6>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Batch Quantity</label>
                            <h6 id="summaryQuantity">-</h6>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Total Operations</label>
                            <h6 id="summaryOperations">-</h6>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Estimated Duration</label>
                            <h6 id="summaryDuration">-</h6>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="{{ route('batches.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Batch
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="card mt-3 shadow">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle text-primary"></i> Information
                        </h6>
                        <small class="text-muted">
                            <ul class="ps-3 mb-0">
                                <li>Select a PO to start</li>
                                <li>Batch quantity cannot exceed remaining PO quantity</li>
                                <li>Rush orders will be prioritized</li>
                                <li>Batch must be approved before production</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

@push('scripts')
<script>
document.getElementById('poSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    
    if (this.value) {
        // Show PO details
        document.getElementById('poDetails').style.display = 'block';
        document.getElementById('poCustomer').textContent = selected.dataset.customer;
        document.getElementById('poQuantity').textContent = selected.dataset.quantity;
        document.getElementById('poRemaining').textContent = selected.dataset.remaining;
        document.getElementById('maxQuantity').textContent = selected.dataset.remaining;
        
        // Set max quantity
        document.getElementById('batchQuantity').max = selected.dataset.remaining;
        
        // Update summary
        document.getElementById('summaryPO').textContent = selected.text.split(' - ')[0];
        
        // Show operations
        const operations = JSON.parse(selected.dataset.operations || '[]');
        if (operations.length > 0) {
            document.getElementById('operationsCard').style.display = 'block';
            displayOperations(operations);
            updateSummary(operations);
        }
    } else {
        document.getElementById('poDetails').style.display = 'none';
        document.getElementById('operationsCard').style.display = 'none';
    }
});

document.getElementById('batchQuantity').addEventListener('input', function() {
    document.getElementById('summaryQuantity').textContent = this.value + ' pcs';
});

function displayOperations(operations) {
    const timeline = document.getElementById('operationsTimeline');
    let html = '<div class="timeline">';
    
    operations.forEach((op, index) => {
        html += `
            <div class="timeline-item mb-3">
                <div class="d-flex">
                    <div class="badge bg-primary rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        ${index + 1}
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${op.operation.name}</h6>
                        <small class="text-muted">
                            <i class="bi bi-building"></i> ${op.operation.division.name} | 
                            <i class="bi bi-clock"></i> ${op.estimated_duration_minutes || op.operation.estimated_duration_minutes} minutes
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    timeline.innerHTML = html;
}

function updateSummary(operations) {
    document.getElementById('summaryOperations').textContent = operations.length + ' operations';
    
    const totalMinutes = operations.reduce((sum, op) => {
        return sum + (parseInt(op.estimated_duration_minutes) || parseInt(op.operation.estimated_duration_minutes) || 0);
    }, 0);
    
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    document.getElementById('summaryDuration').textContent = `${hours}h ${minutes}m`;
}
</script>
@endpush
@endsection