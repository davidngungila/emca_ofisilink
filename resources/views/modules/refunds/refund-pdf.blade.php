<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Refund Request Report - {{ $refundRequest->request_no }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            font-size: 11px; 
            line-height: 1.4;
        }
        .status-box { 
            padding: 12px; 
            text-align: center; 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            text-transform: capitalize; 
        }
        .status-approved { 
            background-color: #e4f8e9; 
            color: #28a745; 
            border: 1px solid #28a745; 
        }
        .status-paid { 
            background-color: #e7f3ff; 
            color: #007bff; 
            border: 1px solid #007bff; 
        }
        .status-pending_hod, .status-pending_accountant, .status-pending_ceo { 
            background-color: #fff8e6; 
            color: #ffc107; 
            border: 1px solid #ffc107; 
        }
        .status-rejected { 
            background-color: #fdecec; 
            color: #dc3545; 
            border: 1px solid #dc3545; 
        }
        .section-title { 
            background-color: #940000; 
            color: #fff; 
            padding: 10px; 
            font-size: 14px; 
            margin-top: 20px; 
            margin-bottom: 0px; 
            font-weight: bold;
        }
        .content-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 0;
            margin-bottom: 15px;
        }
        .content-table th, .content-table td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left; 
        }
        .content-table th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            color: #555; 
        }
        .timeline { 
            border-left: 2px solid #940000; 
            padding-left: 20px; 
            margin-top: 10px; 
        }
        .timeline-item { 
            position: relative; 
            padding-bottom: 15px; 
        }
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -26.5px; 
            top: 3px; 
            width: 10px; 
            height: 10px; 
            background-color: #fff; 
            border: 2px solid #940000; 
            border-radius: 50%; 
        }
        .timeline-item strong { 
            display: block; 
            font-size: 12px; 
            color: #333;
            margin-bottom: 3px;
        }
        .timeline-item .meta { 
            font-size: 10px; 
            color: #777; 
        }
        .timeline-item .comment { 
            background-color: #f9f9f9; 
            border-left: 3px solid #ddd; 
            padding: 8px; 
            margin-top: 5px; 
            font-style: italic; 
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .attachments-list {
            margin-top: 10px;
        }
        .attachment-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = $refundRequest->request_no ?? 'REFUND-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'REFUND REQUEST REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="status-box status-{{ $refundRequest->status }}">
        Status: {{ ucwords(str_replace('_', ' ', $refundRequest->status)) }}
    </div>

    <h3 class="section-title">Request Details</h3>
    <table class="content-table">
        <tr>
            <th style="width: 25%;">Request Number</th>
            <td style="width: 25%;">{{ $refundRequest->request_no }}</td>
            <th style="width: 25%;">Staff Member</th>
            <td style="width: 25%;">{{ $refundRequest->staff->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Purpose</th>
            <td colspan="3">{{ $refundRequest->purpose }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><strong>TZS {{ number_format($refundRequest->amount, 2) }}</strong></td>
            <th>Expense Date</th>
            <td>{{ $refundRequest->expense_date->format('M j, Y') }}</td>
        </tr>
        <tr>
            <th>Created Date</th>
            <td>{{ $refundRequest->created_at->format('M j, Y, g:i A') }}</td>
            <th>Created By</th>
            <td>{{ $refundRequest->creator->name ?? 'N/A' }}</td>
        </tr>
        @if($refundRequest->description)
        <tr>
            <th>Description</th>
            <td colspan="3">{!! nl2br(e($refundRequest->description)) !!}</td>
        </tr>
        @endif
    </table>

    @if($refundRequest->attachments->count() > 0)
    <h3 class="section-title">Supporting Documents</h3>
    <div class="attachments-list">
        @foreach($refundRequest->attachments as $attachment)
        <div class="attachment-item">
            <strong>{{ $attachment->file_name }}</strong>
            @if($attachment->description)
                <br><small style="color: #777;">{{ $attachment->description }}</small>
            @endif
            <br><small style="color: #777;">Uploaded: {{ $attachment->created_at->format('M j, Y, g:i A') }}</small>
        </div>
        @endforeach
    </div>
    @endif

    @if($refundRequest->hod_approved_at || $refundRequest->accountant_verified_at || $refundRequest->ceo_approved_at)
    <h3 class="section-title">Approval Information</h3>
    <table class="content-table">
        @if($refundRequest->hod_approved_at)
        <tr>
            <th style="width: 25%;">HOD Approval</th>
            <td style="width: 25%;">
                <strong>Approved</strong><br>
                <small>{{ $refundRequest->hod_approved_at->format('M j, Y, g:i A') }}</small>
            </td>
            <td style="width: 50%;">
                @if($refundRequest->hodApproval)
                    Approved by: {{ $refundRequest->hodApproval->name }}
                @endif
                @if($refundRequest->hod_comments)
                    <br><small style="color: #777;">{{ $refundRequest->hod_comments }}</small>
                @endif
            </td>
        </tr>
        @endif

        @if($refundRequest->accountant_verified_at)
        <tr>
            <th>Accountant Verification</th>
            <td>
                <strong>Verified</strong><br>
                <small>{{ $refundRequest->accountant_verified_at->format('M j, Y, g:i A') }}</small>
            </td>
            <td>
                @if($refundRequest->accountantVerification)
                    Verified by: {{ $refundRequest->accountantVerification->name }}
                @endif
                @if($refundRequest->accountant_comments)
                    <br><small style="color: #777;">{{ $refundRequest->accountant_comments }}</small>
                @endif
            </td>
        </tr>
        @endif

        @if($refundRequest->ceo_approved_at)
        <tr>
            <th>CEO Approval</th>
            <td>
                <strong>Approved</strong><br>
                <small>{{ $refundRequest->ceo_approved_at->format('M j, Y, g:i A') }}</small>
            </td>
            <td>
                @if($refundRequest->ceoApproval)
                    Approved by: {{ $refundRequest->ceoApproval->name }}
                @endif
                @if($refundRequest->ceo_comments)
                    <br><small style="color: #777;">{{ $refundRequest->ceo_comments }}</small>
                @endif
            </td>
        </tr>
        @endif
    </table>
    @endif

    @if($refundRequest->paid_at)
    <h3 class="section-title">Payment Information</h3>
    <table class="content-table">
        <tr>
            <th style="width: 25%;">Payment Date</th>
            <td>{{ $refundRequest->paid_at->format('M j, Y, g:i A') }}</td>
            <th style="width: 25%;">Payment Method</th>
            <td>{{ $refundRequest->payment_method ? ucwords(str_replace('_', ' ', $refundRequest->payment_method)) : 'N/A' }}</td>
        </tr>
        @if($refundRequest->payment_reference)
        <tr>
            <th>Payment Reference</th>
            <td colspan="3"><strong>{{ $refundRequest->payment_reference }}</strong></td>
        </tr>
        @endif
        @if($refundRequest->payment_notes)
        <tr>
            <th>Payment Notes</th>
            <td colspan="3">{!! nl2br(e($refundRequest->payment_notes)) !!}</td>
        </tr>
        @endif
        @if($refundRequest->paidBy)
        <tr>
            <th>Processed By</th>
            <td colspan="3">{{ $refundRequest->paidBy->name }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if($refundRequest->rejected_at)
    <h3 class="section-title">Rejection Information</h3>
    <table class="content-table">
        <tr>
            <th style="width: 25%;">Rejected Date</th>
            <td>{{ $refundRequest->rejected_at->format('M j, Y, g:i A') }}</td>
            <th style="width: 25%;">Rejected By</th>
            <td>{{ $refundRequest->rejectedBy->name ?? 'N/A' }}</td>
        </tr>
        @if($refundRequest->rejection_reason)
        <tr>
            <th>Rejection Reason</th>
            <td colspan="3">{!! nl2br(e($refundRequest->rejection_reason)) !!}</td>
        </tr>
        @endif
    </table>
    @endif

    <h3 class="section-title">Request Timeline</h3>
    <div class="timeline">
        <div class="timeline-item">
            <strong>Request Created</strong>
            <div class="meta">{{ $refundRequest->created_at->format('M j, Y, g:i A') }}</div>
            @if($refundRequest->creator)
                <div class="meta">Created by {{ $refundRequest->creator->name }}</div>
            @endif
        </div>

        @if($refundRequest->hod_approved_at)
        <div class="timeline-item">
            <strong>HOD Approved</strong>
            <div class="meta">{{ $refundRequest->hod_approved_at->format('M j, Y, g:i A') }}</div>
            @if($refundRequest->hodApproval)
                <div class="meta">Approved by {{ $refundRequest->hodApproval->name }}</div>
            @endif
            @if($refundRequest->hod_comments)
                <div class="comment">{{ $refundRequest->hod_comments }}</div>
            @endif
        </div>
        @endif

        @if($refundRequest->accountant_verified_at)
        <div class="timeline-item">
            <strong>Accountant Verified</strong>
            <div class="meta">{{ $refundRequest->accountant_verified_at->format('M j, Y, g:i A') }}</div>
            @if($refundRequest->accountantVerification)
                <div class="meta">Verified by {{ $refundRequest->accountantVerification->name }}</div>
            @endif
            @if($refundRequest->accountant_comments)
                <div class="comment">{{ $refundRequest->accountant_comments }}</div>
            @endif
        </div>
        @endif

        @if($refundRequest->ceo_approved_at)
        <div class="timeline-item">
            <strong>CEO Approved</strong>
            <div class="meta">{{ $refundRequest->ceo_approved_at->format('M j, Y, g:i A') }}</div>
            @if($refundRequest->ceoApproval)
                <div class="meta">Final approval by {{ $refundRequest->ceoApproval->name }}</div>
            @endif
            @if($refundRequest->ceo_comments)
                <div class="comment">{{ $refundRequest->ceo_comments }}</div>
            @endif
        </div>
        @endif

        @if($refundRequest->paid_at)
        <div class="timeline-item">
            <strong>Payment Processed</strong>
            <div class="meta">{{ $refundRequest->paid_at->format('M j, Y, g:i A') }}</div>
            @if($refundRequest->payment_method)
                <div class="meta">Payment method: {{ ucwords(str_replace('_', ' ', $refundRequest->payment_method)) }}</div>
            @endif
            @if($refundRequest->payment_reference)
                <div class="meta">Reference: {{ $refundRequest->payment_reference }}</div>
            @endif
        </div>
        @endif

        @if($refundRequest->rejected_at)
        <div class="timeline-item">
            <strong>Request Rejected</strong>
            <div class="meta">{{ $refundRequest->rejected_at->format('M j, Y, g:i A') }}</div>
            @if($refundRequest->rejectedBy)
                <div class="meta">Rejected by {{ $refundRequest->rejectedBy->name }}</div>
            @endif
            @if($refundRequest->rejection_reason)
                <div class="comment">{{ $refundRequest->rejection_reason }}</div>
            @endif
        </div>
        @endif
    </div>

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

