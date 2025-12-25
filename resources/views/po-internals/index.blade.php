@extends('layouts.app')

@section('title', 'PO Internal List')
@section('page-title', 'PO Internal')

@section('content')
<section class="section" >
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">List of PO Internal</h5>
            <a href="{{ route('po-internals.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New PO
            </a>
        </div>
        <div class="card-body">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_production" {{ request('status') == 'in_production' ? 'selected' : '' }}>In Production</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search PO Number or Customer..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($poInternals as $po)
                        <tr>
                            <td><strong>{{ $po->po_number }}</strong></td>
                            <td>{{ $po->customer_name }}</td>
                            <td>{{ Str::limit($po->product_description, 30) }}</td>
                            <td>{{ $po->quantity }}</td>
                            <td>{{ $po->due_date?->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $po->status == 'completed' ? 'success' : ($po->status == 'in_production' ? 'primary' : ($po->status == 'confirmed' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('po-internals.show', $po->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('po-internals.edit', $po->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($po->status == 'draft')
                                    <form action="{{ route('po-internals.destroy', $po->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No PO Internal found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center">
                {{ $poInternals->links() }}
            </div>
        </div>
    </div>
</section>
@endsection