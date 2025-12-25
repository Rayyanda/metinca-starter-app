@extends('layouts.app')

@section('title', 'Roles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/choices.js/pbl/assets/styles/choices.css') }}">
@endpush

@section('content')
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Roles</h5>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createRoleModal"
                            class="btn btn-primary btn-sm">Add New Role</a>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            @foreach ($role->permissions as $item)
                                                <span class="badge bg-info mr-1">{{ $item->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="#" data-id="{{ $role->id }}"
                                                class="btn btn-edit btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this role?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="createRoleModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-lg"
            role="document">
            <div class="modal-content">
                <form id="formCreateRole">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createRoleModalTitle">Role Baru
                        </h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Role</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                @foreach ($permissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="{{ $permission->name }}" id="perm-{{ $permission->id }}">
                                            <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
    <div class="modal fade text-left" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content" id="editModalContent">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/extensions/choices.js/pbl/assets/scripts/choices.js') }}"></script>
<script src="{{ asset('assets/static/js/pages/form-element-select.js') }}"></script>
    <script>

        //load modal edit
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                let roleId = this.closest('tr').querySelector('form').getAttribute('action').split('/').pop();
                console.log(roleId);
                fetch(`{{ url('roles') }}/${roleId}/edit`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('editModalContent').innerHTML = html;
                        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                        editModal.show();
                    })
                    .catch(error => {
                        console.error('Error loading edit form:', error);
                    });
            });
        });



        document.getElementById('formCreateRole').addEventListener('submit', function(e) {
            e.preventDefault();

            let form = e.target;
            let formData = new FormData(form);

            // loading Swal
            Swal.fire({
                'title': 'Processing...',
                'allowOutsideClick': false,
                'didOpen': () => {
                    Swal.showLoading()
                }
            });

            fetch("{{ route('roles.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => {
                            throw errData;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Success handling
                    Swal.fire({
                        title: 'Success!',
                        text: 'Role created successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to see the new role
                        location.reload();
                    });
                })
                .catch(errorData => {
                    // Error handling
                    let errorMessages = '';
                    if (errorData.errors) {
                        for (let key in errorData.errors) {
                            errorMessages += errorData.errors[key].join(' ') + '\n';
                        }
                    } else if (errorData.message) {
                        errorMessages = errorData.message;
                    }
                    alert('Error: \n' + errorMessages);
                });
        });
    </script>
@endpush
