@extends('layouts.app')

@section('title', 'Meeting Minutes Preview - OfisiLink')

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
    .minutes-preview {
        background: white;
        padding: 40px 50px;
        max-width: 1000px;
        margin: 0 auto;
    }
    .minutes-preview h4, .minutes-preview h5, .minutes-preview h6 {
        color: #333;
    }
    .minutes-preview table {
        width: 100%;
        border-collapse: collapse;
    }
    .minutes-preview .table-bordered {
        border: 1px solid #dee2e6;
    }
    .minutes-preview .table-bordered td,
    .minutes-preview .table-bordered th {
        padding: 10px;
        border: 1px solid #dee2e6;
        vertical-align: top;
    }
    .minutes-section {
        margin-bottom: 30px;
        page-break-inside: avoid;
    }
    @media print {
        .minutes-preview {
            padding: 20px;
        }
        .minutes-section {
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-file me-2"></i>Meeting Minutes Preview
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Preview meeting minutes for {{ $meeting->title }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.meetings.show', $meeting->id) }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Meeting
                            </a>
                            <a href="{{ route('modules.meetings.minutes.pdf', $meeting->id) }}" class="btn btn-light btn-lg shadow-sm" target="_blank">
                                <i class="bx bx-file-blank me-2"></i>Export PDF
                            </a>
                            <button onclick="window.print()" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-printer me-2"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minutes Preview Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @include('modules.meetings.partials.minutes-preview-template')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

