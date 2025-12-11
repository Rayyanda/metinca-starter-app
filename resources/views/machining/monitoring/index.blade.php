@extends('layouts.app')

@section('title', 'Machining - Monitoring')

@push('styles')
@endpush

@section('content')

    <div class="page-heading">
        <h3>Machining Monitoring</h3>
    </div>
    <div class="page-content">
        <div class="row">
            {{-- Main --}}
            <div class="col-12 col-lg-9">
                <div class="row">
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card shadow">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-4 d-flex justify-content-center ">
                                        <div class="stats-icon purple mb-2">
                                            <i class="bi bi-cpu"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-8">
                                        <h6 class="text-muted font-semibold">Total Machines</h6>
                                        <h6 class="font-extrabold mb-0">16</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Aside --}}
            <div class="col-12 col-lg-3">

            </div>
        </div>
    </div>

@endsection
