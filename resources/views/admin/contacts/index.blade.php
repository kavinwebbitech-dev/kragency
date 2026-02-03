@extends('layouts.app')
@section('title', __('customers/message.customer_contacts'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.customer_contacts') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">List {{ __('customers/message.customer_contacts') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header ">
                            <h3 style="margin-top: 10px;" class="card-title ">{{ __('customers/message.customer_contacts') }}</h3>
                            {{-- <a href="{{ route('admin.provider.add') }}" class="btn btn-primary float-end" name="save" value="create">{{ __('customers/message.add_providers') }}</a> --}}
                        </div>
                        <div class="card-body">
                            <table id ="providerTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Mobile No</th>
                                        <th>Emails</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    <script>
        $('#providerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.users.get-contacts') }}",
            columns: [
                { 
                    data: null, 
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'name', name: 'name' },
                {
                    data: 'mobile_numbers',
                    name: 'mobile_numbers',
                    render: function(data) {
                        if (!data || data.length === 0) return '-';

                        return data.map(function(d) {
                            return d.number;
                        }).join(', ');
                    }
                },
                { 
                    data: 'emails',
                    name: 'emails',
                    render: function(data) {
                        if (!data || data.length === 0) return '-';
                        return data.join(', ');
                    }
                },
                {
                    data: null,
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        const deleteUrl = `{{ route('admin.contact.delete', ':id') }}`.replace(':id', row.id);

                        return `
                            <a href="#" data-url="${deleteUrl}" class="btn btn-sm btn-danger btn-delete-provider" title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        `;
                    }
                }
            ]
        });


        // Handle delete
        $(document).on('click', '.btn-delete-provider', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this provider?')) return;
            let url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert(res.message);
                    $('#providerTable').DataTable().ajax.reload();
                },
                error: function(err) {
                    alert('Something went wrong!');
                }
            });
        });
    </script>
@endpush