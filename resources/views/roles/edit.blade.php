<div class="modal-header">
    <h4 class="modal-title" id="editModalLabel">Edit Unit Kerja</h4>
    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
        <i data-feather="x"></i>
    </button>
</div>
<form class="form form-horizontal" id="frm-edit" >
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="name">Name</label>
            </div>
            <div class="col-md-4">
                <div class="form-group has-icon-left">
                    <div class="position-relative">
                        <input type="text" name="name" id="name" placeholder="Name"
                            value="{{ old('name', $role->name) }}" class="form-control">
                        <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="roles">Permissions</label>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <select class="choices form-select multiple-remove" name="permissions[]" multiple="multiple">
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->name }}"
                                {{ in_array($permission->id, $rolePermissions) ? 'selected' : '' }}>
                                {{ $permission->name }}</option>
                        @endforeach
                    </select>
                </div>
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

<script>
    document.getElementById('frm-edit').addEventListener('submit',function(e){
        e.preventDefault();


        Swal.fire({
            title : 'data sudah benar?',
            icon  : 'question',
            text  : 'Pastikan data yang diinput sudah benar',
            showCancelButton : true,
            confirmButtonText : 'Simpan',
            cancelButtonText : 'Batal'
        }).then((result)=>{
            if(result.isConfirmed){
                const formData = new FormData(this);
                fetch("{{ route('roles.update', $role->id) }}",{
                    method : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    body : formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        Swal.fire({
                            title : 'Berhasil',
                            text  : data.message,
                            icon  : 'success',
                        }).then(()=>{
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title : 'Gagal',
                            text  : data.message,
                            icon  : 'error',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title : 'Gagal',
                        text  : 'Terjadi kesalahan pada server.',
                        icon  : 'error',
                    });
                });
            }
        });

    });
</script>
