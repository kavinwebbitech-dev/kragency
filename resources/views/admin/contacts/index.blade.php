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
                        <div class="card-header">
                            <h3 style="margin-top: 10px;" class="card-title">{{ __('customers/message.customer_contacts') }}</h3>
                            <a href="{{ route('admin.contacts.export') }}" class="btn btn-success float-end">
                                <i class="bi bi-download"></i> Export Contacts
                            </a>
                        </div>
                        <div class="card-body">
                            <table id="customerTable" class="table table-bordered table-striped visual-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer ID</th>
                                        <th>Customer Name</th>
                                        <th>Total Contacts</th>
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

    <div class="modal fade" id="contactsModal" tabindex="-1" aria-labelledby="contactsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="contactsModalLabel">Contacts for Customer: <span id="modalCustomerName" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="modalContactsTable" class="table table-bordered w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Contact Name</th>
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
</main>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var customerTable = $('#customerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.users.get-contacts') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { 
                        data: 'contacts_count', 
                        name: 'contacts_count', 
                        className: 'text-center fw-bold text-success' 
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function (data, type, row) {
                            return `
                                <button type="button" class="btn btn-sm btn-primary btn-view-contacts" data-id="${row.id}" data-name="${row.name}">
                                    <i class="bi bi-eye"></i> View Contacts
                                </button>
                            `;
                        }
                    }
                ]
            });

            var modalContactsTable = null;

            $(document).on('click', '.btn-view-contacts', function() {
                var customerId = $(this).data('id');
                var customerName = $(this).data('name');

                $('#modalCustomerName').text(`${customerName}`);

                var dynamicAjaxUrl = "{{ url('admin/customers') }}/" + customerId + "/contacts";

                if ($.fn.DataTable.isDataTable('#modalContactsTable')) {
                    modalContactsTable.destroy();
                }

                modalContactsTable = $('#modalContactsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: dynamicAjaxUrl,
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        {
                            data: 'mobile_numbers',
                            name: 'mobile_numbers',
                            render: function(data) {
                                if (!data || data.length === 0) return '-';
                                return typeof data === 'object' ? data.map(d => d.number).join(', ') : data;
                            }
                        },
                        { 
                            data: 'emails',
                            name: 'emails',
                            render: function(data) {
                                if (!data || data.length === 0) return '-';
                                return Array.isArray(data) ? data.join(', ') : data;
                            }
                        },
                        {
                            data: null,
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
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

                $('#contactsModal').modal('show');
            });

            $(document).on('click', '.btn-delete-provider', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this contact?')) return;
                
                let url = $(this).data('url');
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        alert(res.message || 'Contact dropped successfully');
                        if (modalContactsTable) modalContactsTable.ajax.reload();
                        customerTable.ajax.reload(null, false);
                    },
                    error: function(err) {
                        alert('Something went wrong during deletion process.');
                    }
                });
            });
        });
    </script>
@endpush