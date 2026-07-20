@extends('frontend.layouts.app')

@section('title', 'Recharge Wallet - Kumaranbooking')

@push('styles')
    <!-- DataTables Core & Responsive CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <style>
        :root {
            --kb-primary: #4e54c8;
            --kb-secondary: #8f94fb;
            --kb-light: #f8f9fa;
            --kb-dark: #1e293b;
            --kb-border: #e2e8f0;
            --kb-success: #10b981;
            --kb-warning: #f59e0b;
            --kb-danger: #ef4444;
        }

        /* Strict White Background */
        body, .kb-premium-section {
            background-color: #ffffff !important; 
        }

        .kb-premium-section {
            padding: 50px 0;
            min-height: calc(100vh - 80px);
        }

        .kb-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 1px 5px rgba(0, 0, 0, 0.02);
            border: 1px solid var(--kb-border);
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .kb-card-title {
            color: var(--kb-dark);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid var(--kb-light);
            padding-bottom: 15px;
        }

        /* QR Section */
        #qr-section {
            display: none;
            animation: fadeInDown 0.4s ease forwards;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .kb-qr-container {
            background: #fafaf9;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 25px 20px;
            text-align: center;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .kb-qr-container:hover {
            border-color: var(--kb-primary);
            background: #f0f5ff;
        }

        .kb-qr-container img {
            max-width: 150px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .kb-qr-container p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Form Controls */
        .kb-form-group { margin-bottom: 1.5rem; }
        
        .kb-form-group label {
            font-weight: 600;
            color: var(--kb-dark);
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.95rem;
        }

        .kb-input-group {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1.5px solid var(--kb-border);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .kb-input-group:focus-within {
            border-color: var(--kb-primary);
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.1);
        }

        .kb-input-group .kb-icon {
            padding: 0.8rem 1.2rem;
            background: var(--kb-light);
            color: #64748b;
            border-right: 1.5px solid var(--kb-border);
            font-weight: 600;
        }

        .kb-input-group input {
            border: none;
            outline: none;
            padding: 0.8rem 1rem;
            width: 100%;
            background: transparent;
            font-size: 1rem;
            color: var(--kb-dark);
        }

        /* Quick Amount Suggestions */
        .quick-amounts {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .amount-pill {
            background: #ffffff;
            border: 1.5px solid var(--kb-border);
            color: var(--kb-dark);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .amount-pill:hover, .amount-pill.active {
            background: var(--kb-primary);
            color: #ffffff;
            border-color: var(--kb-primary);
        }

        /* File Input */
        .kb-file-input {
            width: 100%;
            padding: 6px 10px;
            border-radius: 10px;
            border: 1.5px solid var(--kb-border);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #ffffff;
            color: var(--kb-dark);
        }

        .kb-file-input:focus {
            border-color: var(--kb-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.1);
        }

        .kb-file-input::file-selector-button {
            background: var(--kb-light);
            border: 1px solid var(--kb-border);
            border-radius: 6px;
            padding: 6px 12px;
            margin-right: 12px;
            color: var(--kb-dark);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .kb-file-input::file-selector-button:hover { background: #e2e8f0; }

        /* Submit Button */
        .kb-btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--kb-primary) 0%, var(--kb-secondary) 100%);
            color: #ffffff;
            border: none;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(78, 84, 200, 0.2);
            margin-top: auto; 
        }

        .kb-btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(78, 84, 200, 0.3);
        }

        /* -------------------------------------------
           FIXED TABLE STYLING FOR DATATABLES 
           ------------------------------------------- */
        .table-responsive {
            width: 100%;
            overflow-x: hidden; /* Let DataTables Responsive handle it */
        }

        table.receipt-table {
            width: 100% !important;
            border-collapse: collapse !important; /* Fixes misalignment */
            margin-top: 10px !important;
        }

        table.receipt-table thead th {
            background: var(--kb-light);
            color: var(--kb-dark);
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid var(--kb-border);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-align: left;
            white-space: nowrap;
        }

        table.receipt-table tbody td {
            padding: 15px;
            border-bottom: 1px solid var(--kb-border);
            vertical-align: middle;
            font-size: 0.95rem;
        }

        table.receipt-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .bg-pending { background: #fef3c7; color: #d97706; }
        .bg-approved { background: #d1fae5; color: #059669; }
        .bg-rejected { background: #fee2e2; color: #dc2626; }

        /* DataTables UI Overrides */
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
            margin-bottom: 15px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 1.5px solid var(--kb-border); 
            border-radius: 8px; 
            padding: 6px 12px; 
            margin-left: 8px; 
            outline: none; 
            transition: all 0.3s;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus { 
            border-color: var(--kb-primary); 
            box-shadow: 0 0 0 3px rgba(78,84,200,0.1); 
        }
        
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 20px;
            padding-top: 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button { 
            border-radius: 6px !important; 
            padding: 5px 12px !important;
            border: 1px solid transparent !important; 
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--kb-primary) !important; 
            color: white !important; 
            border-color: var(--kb-primary) !important; 
            box-shadow: 0 4px 10px rgba(78,84,200,0.15);
        }

        /* Alerts */
        .kb-alert {
            border-radius: 10px;
            padding: 15px 20px;
            border: none;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        .kb-alert-success { background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981; }
        .kb-alert-danger { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }

        @media (max-width: 991px) {
            .kb-premium-section { padding: 30px 15px; }
            .kb-card { padding: 25px 20px; margin-bottom: 25px; }
            .dataTables_wrapper .dataTables_filter { float: none; text-align: left; }
            .dataTables_wrapper .dataTables_filter input { width: 100%; margin-left: 0; margin-top: 8px;}
        }
    </style>
@endpush

@section('content')
    <section class="kb-premium-section">
        <div class="container">
            
            @if(session('success'))
                <div class="kb-alert kb-alert-success">
                    <i class="fas fa-check-circle fa-lg"></i> 
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="kb-alert kb-alert-danger">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row align-items-stretch">
                <!-- Left Column: Form -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="kb-card">
                        <h3 class="kb-card-title"><i class="fas fa-wallet text-primary"></i> Add Funds</h3>

                        <form action="{{ route('customer.recharge.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-grow-1">
                            @csrf
                            
                            <!-- Amount Input with Suggestions -->
                            <div class="kb-form-group">
                                <label for="amount">Enter Amount</label>
                                <div class="kb-input-group">
                                    <div class="kb-icon"><i class="fas fa-rupee-sign"></i></div>
                                    <input type="number" name="amount" id="amount" placeholder="0.00" min="1" required value="{{ old('amount') }}">
                                </div>
                                
                                <!-- Quick Amounts -->
                                <div class="quick-amounts">
                                    <span class="amount-pill" data-amount="500">500</span>
                                    <span class="amount-pill" data-amount="1000">1000</span>
                                    <span class="amount-pill" data-amount="1500">1500</span>
                                    <span class="amount-pill" data-amount="2000">2000</span>
                                    <span class="amount-pill" data-amount="3000">3000</span>
                                    <span class="amount-pill" data-amount="5000">5000+</span>
                                </div>
                            </div>

                            <!-- QR Code Section -->
                            <div id="qr-section">
                                <div class="kb-qr-container">
                                    @if($qr)
                                        <img src="{{ $qr }}" alt="Payment QR Code">
                                        <p><i class="fas fa-qrcode mr-1"></i> Scan to Pay</p>
                                    @else
                                        <div style="padding: 20px 0; color: #cbd5e1;">
                                            <i class="fas fa-qrcode fa-3x mb-3"></i>
                                            <p>QR Code Unavailable</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Image Upload Box -->
                            <div class="kb-form-group">
                                <label for="image">Upload Screenshot</label>
                                <input type="file" name="image" id="image" class="kb-file-input" accept="image/jpeg,image/png,image/jpg" required>
                            </div>

                            <button type="submit" class="kb-btn-submit mt-3">
                                <i class="fas fa-paper-plane mr-2"></i> Submit Request
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: History Table -->
                <div class="col-lg-8">
                    <div class="kb-card">
                        <h3 class="kb-card-title"><i class="fas fa-history text-primary"></i> Recharge History</h3>
                        
                        <div class="table-responsive">
                            <!-- Added style="width:100%" to enforce DataTables alignment -->
                            <table id="rechargeTable" class="receipt-table display nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Date</th>
                                        <th style="width: 25%;">Amount</th>
                                        <th style="width: 25%;">Status</th>
                                        <th style="width: 20%; text-align: center;">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recharges as $recharge)
                                        <tr>
                                            <td>
                                                <span class="text-dark font-weight-bold">{{ $recharge->created_at->format('M d, Y') }}</span><br>
                                                <small class="text-muted">{{ $recharge->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td class="font-weight-bold" style="color: var(--kb-primary); font-size: 1.1rem;">
                                                ₹{{ number_format($recharge->amount, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge-status bg-{{ strtolower($recharge->status) }}">
                                                    {{ ucfirst($recharge->status) }}
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                @if($recharge->image)
                                                    <a href="{{ asset($recharge->image) }}" target="_blank" class="btn btn-sm" style="background: var(--kb-light); color: var(--kb-dark); border-radius: 8px; font-weight: 600; padding: 6px 12px; white-space: nowrap;">
                                                        <i class="fas fa-external-link-alt mr-1 text-primary"></i> View
                                                    </a>
                                                @else
                                                    <span class="text-muted"><i class="fas fa-minus"></i></span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <!-- DataTables Core & Responsive JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    <script>
        $(document).ready(function() {
            
            // Initialize DataTable with True Responsiveness
            $('#rechargeTable').DataTable({
                responsive: true,
                ordering: false,
                pageLength: 8,
                lengthChange: false,
                autoWidth: false, // Forces table to respect our CSS widths
                language: {
                    search: "",
                    searchPlaceholder: "Search records..."
                }
            });

            // Form Logic
            const amountInput = $('#amount');
            const qrSection = $('#qr-section');
            const amountPills = $('.amount-pill');

            function toggleQrVisibility() {
                const amount = parseFloat(amountInput.val());
                if (amount > 0) {
                    qrSection.slideDown(300);
                } else {
                    qrSection.slideUp(300);
                }
            }

            amountInput.on('input', function() {
                toggleQrVisibility();
                amountPills.removeClass('active');
                $(`.amount-pill[data-amount="${$(this).val()}"]`).addClass('active');
            });

            amountPills.on('click', function() {
                amountPills.removeClass('active');
                $(this).addClass('active');

                let selectedAmount = $(this).data('amount');
                amountInput.val(selectedAmount === 5000 ? 5000 : selectedAmount);
                
                toggleQrVisibility();
            });

            if(amountInput.val()) {
                toggleQrVisibility();
            }
        });
    </script>
@endpush