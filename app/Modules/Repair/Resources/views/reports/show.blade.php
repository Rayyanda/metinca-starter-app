@extends('repair::layouts.module')

@section('title', 'Report Details')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Report: {{ $report->report_code }}</h3>
                <p class="text-subtitle text-muted">{{ $report->machine->code ?? 'Unknown Machine' }}</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('repair.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('repair.reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $report->report_code }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="row">
            <div class="col-md-8">
                <!-- Report Details Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Report Details</h4>
                        <div>
                            <span class="badge {{ $report->priorityBadgeClass() }}">{{ ucfirst($report->priority) }}</span>
                            <span class="badge {{ $report->statusBadgeClass() }}">{{ str_replace('_', ' ', ucfirst($report->status)) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Machine:</strong> {{ $report->machine->code ?? '-' }} - {{ $report->machine->name ?? '-' }}</p>
                                <p><strong>Department:</strong> {{ $report->department }}</p>
                                <p><strong>Location:</strong> {{ $report->location ?: '-' }}</p>
                                <p><strong>Section:</strong> {{ $report->section ?: '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reporter:</strong> {{ $report->reporter->name ?? '-' }}</p>
                                <p><strong>Technician:</strong> {{ $report->assignedTechnician->name ?? '-' }}</p>
                                <p><strong>Reported At:</strong> {{ $report->reported_at->format('d M Y H:i') }}</p>
                                <p><strong>Target Date:</strong> {{ $report->target_completed_at?->format('d M Y') ?? '-' }}</p>
                            </div>
                        </div>

                        <hr>

                        <p><strong>Damage Type:</strong> {{ $report->damage_type_other ?: $report->damage_type }}</p>
                        <p><strong>Description:</strong></p>
                        <div class="bg-light p-3 rounded">
                            {{ $report->description }}
                        </div>
                    </div>
                </div>

                <!-- Photos Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Photos</h4>
                    </div>
                    <div class="card-body">
                        <h6>Before Photos</h6>
                        <div class="row mb-3">
                            @forelse($beforeAttachments as $attachment)
                            <div class="col-md-3 mb-2">
                                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $attachment->path) }}" class="img-fluid rounded" alt="{{ $attachment->original_name }}">
                                </a>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">No before photos</p>
                            </div>
                            @endforelse
                        </div>

                        @if($report->status === 'done')
                        <h6>After Photos</h6>
                        <div class="row">
                            @forelse($afterAttachments as $attachment)
                            <div class="col-md-3 mb-2">
                                <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $attachment->path) }}" class="img-fluid rounded" alt="{{ $attachment->original_name }}">
                                </a>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted">No after photos</p>
                            </div>
                            @endforelse
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status Update Card -->
                @if($report->status !== 'done')
                @can('repair.update-status')
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Update Status</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('repair.reports.status', $report) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="status" class="form-label">New Status</label>
                                <select name="status" id="status" class="form-select" required>
                                    @foreach($report->allowedNextStatuses() as $nextStatus)
                                    <option value="{{ $nextStatus }}">{{ str_replace('_', ' ', ucfirst($nextStatus)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
                            </div>

                            <div class="form-group mb-3" id="after_photos_wrapper" style="display: none;">
                                <label for="after_photos" class="form-label">After Photos (Required for Done status)</label>
                                <input type="file" name="after_photos[]" id="after_photos" class="form-control" multiple accept="image/*">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
                @endcan
                @endif

                <!-- History Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">History</h4>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @forelse($report->histories->sortByDesc('created_at') as $history)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <strong>{{ ucfirst($history->action) }}</strong>
                                <small class="text-muted">{{ $history->created_at->format('d M Y H:i') }}</small>
                            </div>
                            @if($history->from_status || $history->to_status)
                            <small class="text-muted">
                                {{ $history->from_status ? ucfirst(str_replace('_', ' ', $history->from_status)) : 'New' }}
                                &rarr;
                                {{ ucfirst(str_replace('_', ' ', $history->to_status)) }}
                            </small>
                            @endif
                            @if($history->notes)
                            <p class="mb-0 mt-1 small">{{ $history->notes }}</p>
                            @endif
                            <small class="text-muted">by {{ $history->actor->name ?? 'System' }}</small>
                        </div>
                        @empty
                        <p class="text-muted">No history</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('status')?.addEventListener('change', function() {
        const wrapper = document.getElementById('after_photos_wrapper');
        wrapper.style.display = this.value === 'done' ? 'block' : 'none';
        document.getElementById('after_photos').required = this.value === 'done';
    });
</script>
@endpush
