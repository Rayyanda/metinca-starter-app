@extends('layouts.app')

@section('title', 'Permissions')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
@endpush

@section('content')

    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">
                    Permission
                </h5>
                <a href="#" data-bs-toggle="modal" data-bs-target="#createPermissionModal" class="btn btn-primary btn-sm">Add New Permission</a>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="table1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Permission</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $index => $permission)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    <a href="#" data-bs-target="#updatePermissionModal" data-bs-toggle="modal"
                                        data-id="{{ $permission->id }}" data-nama="{{ $permission->name }}"
                                        class="btn btn-sm btn-warning btnEdit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    {{-- @can('edit permissions')
                                    @endcan --}}

                                    <form data-id="{{ $permission->id }}" class="d-inline formDelete">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    {{-- @can('delete permissions')
                                    @endcan --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </section>
    <div class="modal fade" id="createPermissionModal" tabindex="-1" role="dialog"
        aria-labelledby="createPermissionModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-lg"
            role="document">
            <div class="modal-content">
                <form id="formCreatePermission">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createPermissionModalTitle">Permission Baru
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Permission</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: snake_case (contoh: edit_articles)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Close</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" data-bs-dismiss="modal">
                            <i class="bx bx-check d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Accept</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updatePermissionModal" tabindex="-1" role="dialog"
        aria-labelledby="updatePermissionModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <form id="formUpdatePermission">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="updatePermissionModalTitle">Edit Baru
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="number" name="permissionId" id="permissionId" hidden>
                        <div class="mb-3">
                            <label for="permissionName" class="form-label">Nama Permission</label>
                            <input type="text" name="name" class="form-control" id="permissionName" required>
                            <small class="text-muted">Format: snake_case (contoh: edit_articles)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Close</span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-1" data-bs-dismiss="modal">
                            <i class="bx bx-check d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Accept</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
    <script>

        document.getElementById('formCreatePermission').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = this.name.value;

            Swal.fire({
                title : 'Creating Permission....',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false
            });

            fetch("{{ route('permissions.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: name })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Permission created successfully',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => console.error('Error:', error));
        });

    </script>
@endpush
