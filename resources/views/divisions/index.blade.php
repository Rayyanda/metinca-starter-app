@extends('layouts.app')

@section('title','Data Divisions')
@section('page-title','List of Divisions')

@section('content')
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Divisions</h5>
                    <a href="{{ route('divisions.create') }}" class="btn btn-primary btn-sm">Add New Division</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Division Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($divisions as $division)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $division->name }}</td>
                                <td>{{ $division->description }}</td>
                                <td>
                                    <a href="{{ route('divisions.edit', $division->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('divisions.destroy', $division->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this division?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection