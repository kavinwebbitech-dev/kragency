
@php
    $userType = auth()->user()->user_type ?? null;
@endphp

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <img src="{{ asset('admin/img/AdminLTELogo.png') }}" class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light">kragency</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column">

                {{-- ================= SUBADMIN ================= --}}
                @if ($userType === 'subadmin')
                    <li class="nav-item">
                        <a href="{{ route('admin.wallet.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-wallet2"></i>
                            <p>Wallet</p>
                        </a>
                    </li>
                @endif


                {{-- ================= ADMIN ================= --}}
                @if ($userType === 'admin')
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Customers</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.customers.import') }}" class="nav-link">
                            <i class="nav-icon bi bi-upload"></i>
                            <p>Import Customers</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.wallet.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-wallet2"></i>
                            <p>Wallet</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.withdraw.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-cash-stack"></i>
                            <p>Withdraw Requests</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.provider.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-patch-check-fill"></i>
                            <p>Providers</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.publish-results') }}" class="nav-link">
                            <i class="nav-icon bi bi-megaphone"></i>
                            <p>Publish Today's Result</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.view-all-results') }}" class="nav-link">
                            <i class="nav-icon bi bi-bar-chart-line"></i>
                            <p>View All Results</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.today-customer-orders') }}" class="nav-link">
                            <i class="nav-icon bi bi-list-check"></i>
                            <p>Today's Customer Orders</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.all-customer-orders') }}" class="nav-link">
                            <i class="nav-icon bi bi-list-ul"></i>
                            <p>View All Orders</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.subadmin.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-person-badge"></i>
                            <p>Sub Admins</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.whatsapplink') }}" class="nav-link">
                            <i class="nav-icon bi bi-whatsapp"></i>
                            <p>WhatsApp Link</p>
                        </a>
                    </li>

                    @php
                        $settingsOpen = Route::is('admin.sliders.*') || Route::is('admin.close-time.*');
                    @endphp

                    <li class="nav-item has-treeview {{ $settingsOpen ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $settingsOpen ? 'active' : '' }}"
                            data-lte-toggle="treeview">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>
                                Settings
                                <i class="right bi bi-chevron-down"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview ms-3" style="display: {{ $settingsOpen ? 'block' : 'none' }};">
                            <li class="nav-item">
                                <a href="{{ route('admin.sliders.index') }}"
                                    class="nav-link {{ Route::is('admin.sliders.*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-images"></i>
                                    <p>Slider</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.close-time.edit') }}"
                                    class="nav-link {{ Route::is('admin.close-time.*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-clock"></i>
                                    <p>Close Time</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>
