<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meeting Resolutions - {{ $meeting->title }}</title>
    <style>
        @page { 
            margin: 25mm 20mm 30mm 20mm;
            size: A4;
        }
        
        body { 
            font-family: "Times New Roman", "Times", serif; 
            font-size: 11pt; 
            color: #000; 
            line-height: 1.5; 
            text-align: justify;
        }
        
        .header-section { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 15px;
        }
        
        .logo-container {
            margin-bottom: 15px;
        }
        
        .logo-container img {
            max-height: 60px;
            max-width: 200px;
        }
        
        .org-name {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
            letter-spacing: 0.5px;
            color: #000;
        }
        
        .org-details {
            font-size: 9pt;
            line-height: 1.8;
            color: #333;
            margin: 8px 0;
        }
        
        .document-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .meeting-info {
            font-size: 10pt;
            margin: 15px 0;
            line-height: 1.8;
        }
        
        .meeting-info strong {
            font-weight: bold;
        }
        
        .resolution-section { 
            page-break-inside: avoid; 
            margin-bottom: 30px; 
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
        }
        
        .resolution-header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .resolution-number {
            display: inline-block;
            background-color: #000;
            color: #fff;
            padding: 5px 15px;
            font-weight: bold;
            font-size: 11pt;
            margin-right: 15px;
        }
        
        .resolution-title {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            display: inline-block;
        }
        
        .resolution-content {
            margin: 15px 0;
        }
        
        .background-section {
            margin-bottom: 15px;
            padding: 12px;
            background-color: #f9f9f9;
            border-left: 3px solid #666;
        }
        
        .background-section h4 {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 8px 0;
            color: #333;
        }
        
        .background-section p {
            margin: 0;
            font-size: 10pt;
            text-align: justify;
        }
        
        .resolution-text-box {
            background-color: #fff;
            border: 2px solid #000;
            padding: 15px;
            margin: 15px 0;
        }
        
        .resolution-text-box h4 {
            font-size: 10pt;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #000;
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
            border-top: 1px solid #ddd;
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
        }
        
        .approval-info {
            background-color: #f0f0f0;
            padding: 10px;
            margin-top: 10px;
            border-left: 3px solid #28a745;
        }
        
        .approval-info strong {
            font-weight: bold;
        }
        
        .no-resolutions {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 12pt;
            font-style: italic;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        p {
            margin: 8px 0;
        }
        
        h4 {
            margin: 12px 0 8px 0;
        }
    </style>
</head>
<body>
    @php
        use Illuminate\Support\Facades\Storage;
        // Get logo path for PDF
        $logoPath = null;
        if (isset($organizationInfo['logo']) && $organizationInfo['logo']) {
            $logoFile = $organizationInfo['logo'];
            if (Storage::disk('public')->exists($logoFile)) {
                $logoPath = storage_path('app/public/' . $logoFile);
            } elseif (file_exists(public_path('storage/' . $logoFile))) {
                $logoPath = public_path('storage/' . $logoFile);
            }
        }
    @endphp
    
    <main>
        {{-- Organization Header Section --}}
        <div class="header-section">
            @if($logoPath && file_exists($logoPath))
                <div class="logo-container">
                    <img src="{{ $logoPath }}" alt="Organization Logo">
                </div>
            @endif
            
            <div class="org-name">
                {{ $organizationInfo['name'] ?? config('app.name', 'Organization') }}
            </div>
            
            <div class="org-details">
                @if($organizationInfo['full_address'])
                    <div>{{ $organizationInfo['full_address'] }}</div>
                @endif
                <div>
                    @if($organizationInfo['phone'])
                        <span>Tel: {{ $organizationInfo['phone'] }}</span>
                        @if($organizationInfo['email'] || $organizationInfo['website'])
                            <span> | </span>
                        @endif
                    @endif
                    @if($organizationInfo['email'])
                        <span>Email: {{ $organizationInfo['email'] }}</span>
                        @if($organizationInfo['website'])
                            <span> | </span>
                        @endif
                    @endif
                    @if($organizationInfo['website'])
                        <span>Website: {{ $organizationInfo['website'] }}</span>
                    @endif
                </div>
            </div>
            
            <div class="document-title">MEETING RESOLUTIONS</div>
            
            <div class="meeting-info">
                <div><strong>Meeting:</strong> {{ $meeting->title }}</div>
                @if($meeting->category_name)
                <div><strong>Category:</strong> {{ $meeting->category_name }}</div>
                @endif
                <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, d F Y') }}</div>
                @php
                    $venue = null;
                    if (property_exists($meeting, 'location') && $meeting->location) {
                        $venue = $meeting->location;
                    } elseif (property_exists($meeting, 'venue') && $meeting->venue) {
                        $venue = $meeting->venue;
                    }
                @endphp
                @if($venue)
                <div><strong>Venue:</strong> {{ $venue }}</div>
                @endif
                @if(property_exists($meeting, 'reference_code') && $meeting->reference_code)
                <div><strong>Reference:</strong> {{ $meeting->reference_code }}</div>
                @endif
            </div>
        </div>
        
        {{-- Resolutions --}}
        @if($resolutions->count() > 0)
            @foreach($resolutions as $index => $resolution)
            <div class="resolution-section">
                <div class="resolution-header">
                    <span class="resolution-number">
                        {{ $resolution->resolution_number ?? 'RES-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="resolution-title">{{ $resolution->title }}</span>
                </div>
                
                <div class="resolution-content">
                    @if($resolution->description)
                    <div class="background-section">
                        <h4>BACKGROUND</h4>
                        <p>{{ $resolution->description }}</p>
                    </div>
                    @endif
                    
                    <div class="resolution-text-box">
                        <h4>RESOLUTION</h4>
                        <p>{{ $resolution->resolution_text }}</p>
                    </div>
                </div>
                
                <div class="resolution-footer">
                    <table>
                        <tr>
                            <td style="width: 50%;">
                                @if($resolution->proposer_name)
                                    <div><strong>Proposed By:</strong></div>
                                    <div style="margin-top: 5px;">{{ $resolution->proposer_name }}</div>
                                @endif
                            </td>
                            <td style="width: 50%; text-align: right;">
                                @if($resolution->seconder_name)
                                    <div><strong>Seconded By:</strong></div>
                                    <div style="margin-top: 5px;">{{ $resolution->seconder_name }}</div>
                                @endif
                            </td>
                        </tr>
                        @if($resolution->approved_at && $resolution->approver_name)
                        <tr>
                            <td colspan="2" style="text-align: right; padding-top: 10px;">
                                <div class="approval-info">
                                    <div><strong>Approved By:</strong> {{ $resolution->approver_name }}</div>
                                    <div style="margin-top: 5px;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($resolution->approved_at)->format('d F Y') }}</div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </table>
                    
                    @if($resolution->approval_notes)
                    <div style="margin-top: 15px; padding: 10px; background-color: #f9f9f9; border-left: 3px solid #007bff; font-size: 9pt;">
                        <strong>Approval Notes:</strong> {{ $resolution->approval_notes }}
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
        
        {{-- Footer Section --}}
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 9pt; text-align: center; color: #666;">
            <p>This document was generated on {{ \Carbon\Carbon::now()->format('d F Y \a\t H:i') }}</p>
            <p style="margin-top: 5px;">{{ $organizationInfo['name'] ?? config('app.name', 'Organization') }}</p>
        </div>
    </main>
</body>
</html>




