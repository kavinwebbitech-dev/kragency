<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="preload" href="{{ asset('admin/css/adminlte.css') }}">
    
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media='all'"
    />

    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />

    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />

    <link rel="stylesheet" href="{{ asset('admin/css/adminlte.css') }}">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">

    <!-- Add more stylesheets as needed -->
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  @php
    $pendingKycCount = App\Models\KycDetail::where('status', 'pending')->count();
  @endphp
<div class="app-wrapper">
    @include('layouts.header')
    @include('layouts.sidebar')
    @yield('content')
    @include('layouts.footer')
</div>

<script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
<script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
<script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
<script src="{{ asset('admin/js/adminlte.js') }}"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>

    <!-- Custom Page Scripts -->
    @stack('scripts')
<!-- Add more scripts as needed -->
@if($pendingKycCount > 0)

  <div class="modal fade" id="kycAlertModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-warning">

              <div class="modal-header bg-warning">
                  <h5 class="modal-title">
                      <i class="bi bi-exclamation-triangle-fill"></i>
                      Pending KYC Alert
                  </h5>
              </div>

              <div class="modal-body text-center">

                <h4 id="pendingTitle"></h4>

                <div id="pendingContent"></div>

            </div>

              <div class="modal-footer">

                  <a href="{{ route('admin.kyc.index') }}" class="btn btn-primary">
                      View KYC
                  </a>
                  <a href="{{ route('admin.recharge.index') }}" class="btn btn-primary">
                      View Recharges
                  </a>

                  <button class="btn btn-secondary" data-bs-dismiss="modal">
                      Close
                  </button>

              </div>

          </div>
      </div>
  </div>

@endif
@if($pendingKycCount > 0)

  <audio id="kycSound">
      <source src="{{ asset('admin/notification.mp3') }}" type="audio/mpeg">
  </audio>

 <script>
$(document).ready(function () {

    const CHECK_INTERVAL = 30 * 1000; // Check every 30 seconds
    const ALERT_INTERVAL = 1 * 60 * 1000; // Show popup once every 1 minute

    const modalElement = document.getElementById('kycAlertModal');
    const audio = document.getElementById('kycSound');

    function showAlert() {

        // Close existing modal if open
        let existingModal = bootstrap.Modal.getInstance(modalElement);

        if (existingModal) {
            existingModal.hide();
            existingModal.dispose();
        }

        // Wait for hide animation
        setTimeout(function () {

            let modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });

            modal.show();

            // Replay sound
            audio.pause();
            audio.currentTime = 0;

            let playPromise = audio.play();

            if (playPromise !== undefined) {
                playPromise.catch(function (err) {
                    console.log("Audio blocked:", err);
                });
            }

        }, 300);
    }

    function checkPendingAlerts() {

        $.ajax({
            url: "{{ route('admin.checkPendingAlerts') }}",
            type: "GET",
            dataType: "json",
            cache: false,
            data: {
                _: new Date().getTime()
            },
            success: function (res) {

                if (!res.success) return;

                let html = "";

                if (res.kyc_count > 0) {
                    html += '<h5>🆔 Pending KYC : <b>' + res.kyc_count + '</b></h5>';
                }

                if (res.recharge_count > 0) {
                    html += '<h5>💰 Pending Recharges : <b>' + res.recharge_count + '</b></h5>';
                }

                $("#pendingContent").html(html);

                $("#pendingTitle").html(
                    'Total Pending Requests : <span class="text-danger">' +
                    res.total +
                    '</span>'
                );

                if (res.total > 0) {

                    let now = Date.now();
                    let lastAlert = sessionStorage.getItem('last_pending_alert');

                    if (!lastAlert || (now - parseInt(lastAlert)) >= ALERT_INTERVAL) {

                        sessionStorage.setItem('last_pending_alert', now);

                        showAlert();
                    }

                } else {

                    sessionStorage.removeItem('last_pending_alert');

                    let existingModal = bootstrap.Modal.getInstance(modalElement);

                    if (existingModal) {
                        existingModal.hide();
                    }
                }
            },
            error: function (xhr) {
                console.log("AJAX Error:", xhr);
            }
        });
    }

    // First check
    checkPendingAlerts();

    // Check every 30 seconds
    setInterval(checkPendingAlerts, CHECK_INTERVAL);

});
</script>
  @endif
</body>
</html>
