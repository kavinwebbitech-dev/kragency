@extends('layouts.app')
@section('title', __('customers/message.customers'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.customers') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">List {{ __('customers/message.customers') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header ">
                            <h3 style="margin-top: 10px;" class="card-title ">{{ __('customers/message.customers') }}</h3>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary float-end" name="save" value="create">Create Customer</a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select id="statusFilter" class="form-select">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="deleted">Deleted</option>
                                    </select>
                                </div>
                            </div>
                            <table id ="usersTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
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

<!-- Bank Details Modal -->
<div class="modal fade" id="bankDetailModal" tabindex="-1" aria-labelledby="bankDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankDetailModalLabel">Bank Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- content filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            let table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.users.get-record') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val(); // send filter value to server
                    }
                },
                columns: [
                    { 
                        data: null, 
                        name: 'id',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'status', name: 'status' },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end', // right align optional
                        render: function (data, type, row) {
                            const editUrl = `{{ route('admin.users.edit', ':id') }}`.replace(':id', row.id);
                            const viewUrl = `{{ route('admin.view-transaction', ':id') }}`.replace(':id', row.id);
                            const deleteUrl = `{{ route('admin.user.destroy', ':id') }}`.replace(':id', row.id);
                            let bankBtn = '';
                            if (row.has_bank_details) {
                                bankBtn = `<button class="btn btn-sm btn-outline-info btn-view-bank" data-id="${row.id}" title="View Bank Details"><i class="bi bi-bank"></i></button>`;
                            }
                            return `
                            <a href="${viewUrl}" class="btn btn-sm btn-outline-primary" title="View Transactions">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="${editUrl}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            ${bankBtn}
                            <a href="${deleteUrl}" class="btn btn-sm btn-danger btn-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                            `;
                        }
                    }
                ]
            });

            // Reload table when status filter changes
            $('#statusFilter').on('change', function () {
                table.ajax.reload();
            });

            // Handle delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this user?')) return;

                let url = $(this).attr('href');

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        alert(res.message);
                        table.ajax.reload();
                    },
                    error: function(err) {
                        alert('Something went wrong!');
                    }
                });
            });

            // Handle view bank details
            $(document).on('click', '.btn-view-bank', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                const url = `{{ url('/admin/users') }}/${userId}/bank-details`;

                $.get(url, function(res) {
                    if (!res.has) {
                        alert('No bank details found for this user.');
                        return;
                    }

                    const bank = res.bank;
                    $('#bankDetailModal .modal-body').html(`
                        <p><strong>Bank Name:</strong> ${bank.bank_name || ''}</p>
                        <p><strong>IFSC Code:</strong> ${bank.ifsc_code || ''}</p>
                        <p><strong>Branch Name:</strong> ${bank.branch_name || ''}</p>
                        <p><strong>Account Number:</strong> ${bank.account_number || ''}</p>
                        <p><strong>Notes:</strong> ${bank.notes || ''}</p>
                    `);
                    $('#bankDetailModal').modal('show');
                }).fail(function() {
                    alert('Failed to fetch bank details.');
                });
            });
        });
    </script>
@endpush