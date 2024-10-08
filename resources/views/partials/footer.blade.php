<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

<!-- BEGIN: Footer-->
<footer class="footer footer-static footer-light">
    <p class="clearfix mb-0"><span class="float-md-start d-block d-md-inline-block mt-25">COPYRIGHT &copy; 2021<a
                class="ms-25" href="https://1.envato.market/pixinvent_portfolio" target="_blank">Pixinvent</a><span
                class="d-none d-sm-inline-block">, All rights Reserved</span></span><span
            class="float-md-end d-none d-md-block">Hand-crafted & Made with<i data-feather="heart"></i></span></p>
</footer>
<button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
<!-- END: Footer-->

{{-- modal Success --}}
<div class="modal fade" id="modalSuccess" tabindex="-1" aria-labelledby="modalSuccess" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body text-center h4" id="body-success">
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

{{-- modal Error --}}
<div class="modal fade" id="errorSuccess" tabindex="-1" aria-labelledby="errorSuccess" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body text-center h4" id="body-error">
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN: Vendor JS-->
<script src="{{ asset('app-assets/vendors/js/vendors.min.js') }}"></script>
<script src="{{ asset('app-assets/vendors/js/extensions/toastr.min.js') }}"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Theme JS-->
<script src="{{ asset('app-assets/js/core/app-menu.js') }}"></script>
<script src="{{ asset('app-assets/js/core/app.js') }}"></script>
<!-- END: Theme JS-->

<script>
    function successMessage(message = 'Berhasil') {
        $('#body-success').html(message);
        const modalSuccess = new bootstrap.Modal(document.getElementById("modalSuccess"), {});
        modalSuccess.show()
    }

    function errorMessage(message = 'Gagal') {
        $('#body-error').html(message);
        const modalSuccess = new bootstrap.Modal(document.getElementById("errorSuccess"), {});
        modalSuccess.show()
    }

    function toastError(message = 'Maaf, Terjadi Kesalahan Server.') {
        toastr['error'](message, 'Warning!', {
            showMethod: 'slideDown',
            hideMethod: 'slideUp',
            timeOut: 7000,
            closeButton: true,
            progressBar: true,
            rtl: false
        });
    }

    function toastSuccess(message = 'Data berhasil diproses.') {
        toastr['success'](message, 'Info!', {
            showMethod: 'slideDown',
            hideMethod: 'slideUp',
            timeOut: 7000,
            closeButton: true,
            progressBar: true,
            rtl: false
        });
    }
</script>

@if (session('error'))
    <script>
        toastError('{{ session('error') }}')
    </script>
@endif

@if (session('success'))
    <script>
        toastSuccess('{{ session('success') }}')
    </script>
@endif

<script>
    $(window).on('load', function() {
        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    })
</script>
@yield('js')
    @stack('js')

</body>
<!-- END: Body-->

</html>
