@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .bg-custom-primary { background-color: #940000 !important; color: white !important; }
    .btn-custom-primary { background-color: #940000 !important; border-color: #940000 !important; color: white !important; }
    .btn-custom-primary:hover { background-color: #7a0000 !important; border-color: #7a0000 !important; color: white !important; }
    .text-custom-primary { color: #940000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card" style="background-color: #940000; color: white;">
                <div class="card-body">
                    <h4 class="card-title text-white mb-1">
                        <i class="bx bx-home me-2"></i>DASHBOARD
                    </h4>
                    <p class="card-text text-white-50 mb-0">welcome, {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
