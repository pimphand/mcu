@extends('layouts.main')

@section('title')
    Dashboard Page
@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/pages/dashboard-ecommerce.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/plugins/charts/chart-apex.css') }}">
@endsection

@section('content')
    <!-- Dashboard Ecommerce Starts -->
    <section id="dashboard-ecommerce">
        <div class="row match-height">
            <!-- Medal Card -->
            <div class="col-xl-12 col-md-12 col-12">
                <div class="card card-congratulation-medal">
                    <div class="card-body">
                        <h5>Congratulations 🎉 KLINIK dr. Dini</h5>
                        <p class="card-text font-small-3">Medical Center</p>
                        {{-- <h3 class="mb-75 mt-2 pt-50">
                            <a href="#">$48.9k</a>
                        </h3> --}}
                        {{-- <button type="button" class="btn btn-primary">View Sales</button>
                        <img src="../../../app-assets/images/illustration/badge.svg" class="congratulation-medal"
                            alt="Medal Pic" /> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Dashboard Ecommerce ends -->
@endsection

@section('js')
    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/extensions/toastr.min.js') }}"></script>
    <!-- END: Page Vendor JS-->
@endsection
