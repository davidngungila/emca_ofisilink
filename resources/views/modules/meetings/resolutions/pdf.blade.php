<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meeting Resolutions - {{ $meeting->title }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: "Helvetica", Arial, sans-serif; 
            font-size: 10pt; 
            color: #333; 
            line-height: 1.4; 
        }
        h1, h2, h3, h4 { 
            color: #500000; 
            margin: 0 0 10px 0; 
            font-weight: bold; 
        }
        h1 { 
            font-size: 22pt; 
            border-bottom: 2px solid #940000; 
            padding-bottom: 10px; 
            margin-bottom: 25px; 
            text-align: center; 
        }
        h2 { 
            font-size: 16pt; 
            background-color: #fceeee; 
            padding: 12px; 
            margin-top: 25px; 
            border-left: 4px solid #940000; 
        }
        h3 { 
            font-size: 13pt; 
            border-bottom: 1px solid #f0d0d0; 
            padding-bottom: 5px; 
            margin-top: 20px; 
        }
        h4 { 
            font-size: 11pt; 
            color: #6c757d; 
            margin-top: 15px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        th, td { 
            padding: 8px; 
            text-align: left; 
            vertical-align: top; 
        }
        .bordered-table th, .bordered-table td { 
            border: 1px solid #ddd; 
        }
        .bordered-table th { 
            background-color: #f9f9f9; 
            font-weight: bold; 
            color: #500000;
        }
        .meeting-summary { 
            background-color: #fff9f9; 
            border: 1px solid #f0d0d0; 
            padding: 20px; 
            margin-bottom: 25px; 
            border-radius: 5px; 
        }
        .summary-table td { 
            padding: 10px; 
            border: 1px solid #f0d0d0; 
        }
        .summary-table td.label { 
            font-weight: bold; 
            color: #500000; 
            width: 20%; 
            background-color: #fceeee;
        }
        .resolution-card { 
            border: 1px solid #e9ecef; 
            padding: 20px; 
            margin-top: 20px; 
            margin-bottom: 25px; 
            page-break-inside: avoid; 
            border-radius: 4px; 
            background-color: #fdfdfd;
            border-left: 4px solid #940000;
        }
        .resolution-header { 
            font-weight: bold; 
            color: #500000; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #f0d0d0; 
            margin-bottom: 15px; 
        }
        .resolution-number { 
            display: inline-block; 
            background-color: #940000; 
            color: white; 
            padding: 5px 12px; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 11pt; 
            margin-right: 10px; 
        }
        .resolution-title { 
            font-size: 14pt; 
            font-weight: bold; 
            color: #500000; 
        }
        .background-section { 
            margin-bottom: 15px; 
            padding: 12px; 
            background-color: #f8f9fa; 
            border-left: 3px solid #940000; 
            border-radius: 3px;
        }
        .background-section h4 { 
            font-size: 10pt; 
            font-weight: bold; 
            margin: 0 0 8px 0; 
            color: #500000; 
        }
        .background-section p { 
            margin: 0; 
            font-size: 10pt; 
            text-align: justify; 
        }
        .resolution-text-box { 
            background-color: #fff; 
            border: 2px solid #940000; 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 5px;
        }
        .resolution-text-box h4 { 
            font-size: 10pt; 
            font-weight: bold; 
            margin: 0 0 10px 0; 
            color: #500000; 
            text-transform: uppercase; 
        }
        .resolution-text-box p { 
            margin: 0; 
            font-size: 11pt; 
            font-weight: normal; 
            text-align: justify; 
            line-height: 1.6; 
        }
        .resolution-footer { 
            margin-top: 20px; 
            padding-top: 15px; 
            border-top: 1px solid #e9ecef; 
            font-size: 9pt; 
        }
        .resolution-footer table { 
            width: 100%; 
            border: none; 
            border-collapse: collapse; 
        }
        .resolution-footer td { 
            border: none; 
            padding: 5px 0; 
            vertical-align: top; 
        }
        .resolution-footer strong { 
            font-weight: bold; 
            color: #500000;
        }
        .approval-info { 
            background-color: #d4edda; 
            padding: 12px; 
            margin-top: 10px; 
            border-left: 3px solid #28a745; 
            border-radius: 3px;
        }
        .approval-info strong { 
            font-weight: bold; 
            color: #155724;
        }
        .status-badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 10px; 
            color: white; 
            font-weight: bold; 
            font-size: 9pt; 
        }
        .badge-approved { background-color: #28a745; }
        .badge-pending { background-color: #ffc107; color: #000; }
        .no-resolutions { 
            text-align: center; 
            padding: 60px 20px; 
            color: #6c757d; 
            font-size: 12pt; 
            font-style: italic; 
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stat-card { 
            display: inline-block; 
            width: 22%; 
            text-align: center; 
            padding: 12px; 
            margin: 3px; 
            background-color: #f8f9fa; 
            border: 2px solid #940000; 
            border-radius: 5px; 
            vertical-align: top;
        }
        .stat-card strong { 
            display: block; 
            font-size: 20pt; 
            color: #940000; 
            margin-bottom: 5px; 
        }
        .stat-card span { 
            font-size: 9pt; 
            color: #6c757d; 
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'MEETING-RES-' . ($meeting->reference_code ?? $meeting->id) . '-' . \Carbon\Carbon::parse($meeting->meeting_date)->format('Ymd');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'MEETING RESOLUTIONS',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Meeting Resolutions</h1>
        
        <div class="meeting-summary">
            <h2 style="margin-top: 0; margin-bottom: 15px;">{{ $meeting->title }}</h2>
            <table class="summary-table">
                <tr>
                    <td class="label">Category</td>
                    <td>{{ $meeting->category_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Meeting Date</td>
                    <td>{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, d F Y') }}</td>
                </tr>
                @php
                    $venue = null;
                    if (property_exists($meeting, 'location') && $meeting->location) {
                        $venue = $meeting->location;
                    } elseif (property_exists($meeting, 'venue') && $meeting->venue) {
                        $venue = $meeting->venue;
                    }
                @endphp
                @if($venue)
                <tr>
                    <td class="label">Venue</td>
                    <td>{{ $venue }}</td>
                </tr>
                @endif
                @if(property_exists($meeting, 'reference_code') && $meeting->reference_code)
                <tr>
                    <td class="label">Reference Code</td>
                    <td>{{ $meeting->reference_code }}</td>
                </tr>
                @endif
                @if(property_exists($meeting, 'branch_name') && $meeting->branch_name)
                <tr>
                    <td class="label">Branch</td>
                    <td>{{ $meeting->branch_name }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Created By</td>
                    <td>{{ $meeting->creator_name ?? 'N/A' }}</td>
                </tr>
            </table>
            
            @if($resolutions->count() > 0)
            <div style="margin-top: 20px; text-align: center;">
                <div class="stat-card">
                    <strong>{{ $resolutions->count() }}</strong>
                    <span>Total Resolutions</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $resolutions->where('approved_at', '!=', null)->count() }}</strong>
                    <span>Approved</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $resolutions->where('approved_at', null)->count() }}</strong>
                    <span>Pending</span>
                </div>
                <div class="stat-card">
                    <strong>{{ $resolutions->where('proposer_name', '!=', null)->count() }}</strong>
                    <span>Proposed</span>
                </div>
            </div>
            @endif
        </div>
        
        <h2>Resolutions</h2>
        
        @if($resolutions->count() > 0)
            @foreach($resolutions as $index => $resolution)
            <div class="resolution-card">
                <div class="resolution-header">
                    <span class="resolution-number">
                        {{ $resolution->resolution_number ?? 'RES-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="resolution-title">{{ $resolution->title }}</span>
                    @if($resolution->approved_at)
                        <span class="status-badge badge-approved" style="float: right; margin-top: 5px;">Approved</span>
                    @else
                        <span class="status-badge badge-pending" style="float: right; margin-top: 5px;">Pending</span>
                    @endif
                </div>
                
                <div>
                    @if($resolution->description)
                    <div class="background-section">
                        <h4>Background</h4>
                        <p>{!! nl2br(e($resolution->description)) !!}</p>
                    </div>
                    @endif
                    
                    <div class="resolution-text-box">
                        <h4>Resolution</h4>
                        <p>{!! nl2br(e($resolution->resolution_text)) !!}</p>
                    </div>
                </div>
                
                <div class="resolution-footer">
                    <table>
                        <tr>
                            <td style="width: 50%;">
                                @if($resolution->proposer_name)
                                    <div><strong>Proposed By:</strong></div>
                                    <div style="margin-top: 5px; color: #500000;">{{ $resolution->proposer_name }}</div>
                                @else
                                    <div><strong>Proposed By:</strong> N/A</div>
                                @endif
                            </td>
                            <td style="width: 50%; text-align: right;">
                                @if($resolution->seconder_name)
                                    <div><strong>Seconded By:</strong></div>
                                    <div style="margin-top: 5px; color: #500000;">{{ $resolution->seconder_name }}</div>
                                @else
                                    <div><strong>Seconded By:</strong> N/A</div>
                                @endif
                            </td>
                        </tr>
                        @if($resolution->approved_at && $resolution->approver_name)
                        <tr>
                            <td colspan="2" style="text-align: right; padding-top: 10px;">
                                <div class="approval-info">
                                    <div><strong>✓ Approved By:</strong> {{ $resolution->approver_name }}</div>
                                    <div style="margin-top: 5px;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($resolution->approved_at)->format('d F Y') }}</div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </table>
                    
                    @if($resolution->approval_notes)
                    <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px; font-size: 9pt;">
                        <strong style="color: #856404;">Approval Notes:</strong> 
                        <span style="color: #856404;">{!! nl2br(e($resolution->approval_notes)) !!}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div class="no-resolutions">
                <p>No resolutions have been prepared for this meeting.</p>
            </div>
        @endif

        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #f0d0d0;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%; text-align: left;">
                        <strong style="color: #500000;">Meeting Created By:</strong><br>
                        {{ $meeting->creator_name ?? 'N/A' }}<br>
                        @if(property_exists($meeting, 'created_at') && $meeting->created_at)
                            {{ \Carbon\Carbon::parse($meeting->created_at)->format('d M, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <strong style="color: #500000;">Report Generated:</strong><br>
                        {{ now()->format('d M, Y, h:i A') }}<br>
                        OfisiLink System
                    </td>
                </tr>
            </table>
        </div>
    </main>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
