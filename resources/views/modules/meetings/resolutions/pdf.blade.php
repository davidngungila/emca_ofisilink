<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Meeting Resolutions - {{ $meeting->title }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.6; }
        h1, h2, h3 { color: #000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 22pt; margin-bottom: 8px; letter-spacing: 1px; }
        h2 { font-size: 16pt; margin-top: 20px; margin-bottom: 8px; }
        h3 { font-size: 14pt; margin-top: 15px; margin-bottom: 8px; }
        h4 { font-size: 12pt; margin-top: 12px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; vertical-align: top; border: 1px solid #ddd; }
        th { background-color: #f9f9f9; font-weight: bold; font-size: 9pt; }
        .resolution-section { page-break-inside: avoid; margin-bottom: 25px; padding: 15px; background-color: #fffbf0; border-left: 4px solid #ffc107; }
        .resolution-text { background-color: #f8f9fa; padding: 12px; border-radius: 4px; margin: 10px 0; font-weight: 500; }
        p { margin: 5px 0; }
        .header-section { text-align: center; margin-bottom: 25px; border-bottom: 3px solid #333; padding-bottom: 15px; }
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
                <div style="margin-bottom: 10px;">
                    <img src="{{ $logoPath }}" alt="Organization Logo" style="max-height: 70px; max-width: 180px;">
                </div>
            @endif
            
            <h1>{{ $organizationInfo['name'] ?? config('app.name', 'Organization') }}</h1>
            
            <div style="font-size: 8pt; line-height: 1.6; color: #555; margin-bottom: 10px;">
                @if($organizationInfo['full_address'])
                    <p style="margin: 2px 0;"><strong>Address:</strong> {{ $organizationInfo['full_address'] }}</p>
                @endif
                <div style="margin: 3px 0;">
                    @if($organizationInfo['phone'])
                        <span style="margin-right: 15px;"><strong>Phone:</strong> {{ $organizationInfo['phone'] }}</span>
                    @endif
                    @if($organizationInfo['email'])
                        <span style="margin-right: 15px;"><strong>Email:</strong> {{ $organizationInfo['email'] }}</span>
                    @endif
                    @if($organizationInfo['website'])
                        <span><strong>Website:</strong> {{ $organizationInfo['website'] }}</span>
                    @endif
                </div>
            </div>
            
            <h2>MEETING RESOLUTIONS</h2>
            <h3 style="font-size: 14pt; margin-bottom: 8px; color: #333; font-weight: 600;">
                {{ strtoupper($meeting->category_name ?? 'MEETING') }} - {{ $meeting->title }}
            </h3>
            <p style="font-size: 10pt; margin-top: 8px;">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, d F Y') }}<br>
                <strong>Venue:</strong> {{ $meeting->venue ?? 'N/A' }}
            </p>
        </div>
        
        {{-- Resolutions --}}
        @if($resolutions->count() > 0)
            @foreach($resolutions as $index => $resolution)
            <div class="resolution-section">
                <h3>
                    <span style="background-color: #ffc107; color: #000; padding: 4px 12px; border-radius: 4px; font-size: 11pt; margin-right: 10px;">
                        {{ $resolution->resolution_number ?? 'RES-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}
                    </span>
                    {{ $resolution->title }}
                </h3>
                
                @if($resolution->description)
                <div style="margin-bottom: 12px;">
                    <h4 style="font-size: 10pt; color: #666; margin-bottom: 5px;">Background/Context:</h4>
                    <p style="text-align: justify; font-size: 9pt; color: #555;">{{ $resolution->description }}</p>
                </div>
                @endif
                
                <div class="resolution-text">
                    <h4 style="font-size: 10pt; color: #666; margin-bottom: 5px;">Resolution:</h4>
                    <p style="text-align: justify; font-size: 10pt; font-weight: 500; margin: 0;">{{ $resolution->resolution_text }}</p>
                </div>
                
                <div style="margin-top: 12px; font-size: 9pt; color: #666;">
                    <table style="width: 100%; border: none;">
                        <tr style="border: none;">
                            <td style="border: none; padding: 4px 0;">
                                @if($resolution->proposer_name)
                                    <strong>Proposed By:</strong> {{ $resolution->proposer_name }}
                                @endif
                            </td>
                            <td style="border: none; padding: 4px 0; text-align: right;">
                                @if($resolution->seconder_name)
                                    <strong>Seconded By:</strong> {{ $resolution->seconder_name }}
                                @endif
                            </td>
                        </tr>
                        @if($resolution->approved_at && $resolution->approver_name)
                        <tr style="border: none;">
                            <td colspan="2" style="border: none; padding: 4px 0; text-align: right;">
                                <strong>Approved By:</strong> {{ $resolution->approver_name }} 
                                on {{ \Carbon\Carbon::parse($resolution->approved_at)->format('M d, Y') }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
                
                @if($resolution->approval_notes)
                <div style="margin-top: 10px; padding: 8px; background-color: #e7f3ff; border-radius: 4px; font-size: 9pt;">
                    <strong>Notes:</strong> {{ $resolution->approval_notes }}
                </div>
                @endif
            </div>
            @endforeach
        @else
            <div style="text-align: center; padding: 40px;">
                <p style="font-size: 12pt; color: #999;">No resolutions have been prepared for this meeting.</p>
            </div>
        @endif
    </main>
</body>
</html>

