{{-- Template-based Minutes Preview --}}
{{-- This follows the structured format documented in the template guide --}}
@php
use Illuminate\Support\Facades\Storage;
@endphp

<div class="minutes-preview p-4" style="font-family: 'Times New Roman', serif;">
    
    {{-- HEADER SECTION WITH ORGANIZATION INFORMATION --}}
    <div class="text-center mb-5" style="border-bottom: 3px solid #333; padding-bottom: 20px;">
        {{-- Organization Logo --}}
        @if(isset($organizationInfo['logo']) && $organizationInfo['logo'])
            @php
                $logoPath = Storage::disk('public')->exists($organizationInfo['logo']) 
                    ? Storage::disk('public')->url($organizationInfo['logo']) 
                    : (file_exists(public_path('storage/' . $organizationInfo['logo'])) 
                        ? asset('storage/' . $organizationInfo['logo']) 
                        : null);
            @endphp
            @if($logoPath)
                <div class="mb-3">
                    <img src="{{ $logoPath }}" alt="Organization Logo" style="max-height: 80px; max-width: 200px;">
                </div>
            @endif
        @endif
        
        {{-- Organization Name --}}
        <h3 class="fw-bold text-uppercase mb-2" style="font-size: 22px; letter-spacing: 1px; color: #000;">
            {{ $organizationInfo['name'] ?? config('app.name', 'Organization') }}
        </h3>
        
        {{-- Organization Details --}}
        <div class="mb-3" style="font-size: 11px; line-height: 1.6; color: #555;">
            @if($organizationInfo['full_address'])
                <p class="mb-1"><strong>Address:</strong> {{ $organizationInfo['full_address'] }}</p>
            @endif
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                @if($organizationInfo['phone'])
                    <span><strong>Phone:</strong> {{ $organizationInfo['phone'] }}</span>
                @endif
                @if($organizationInfo['email'])
                    <span><strong>Email:</strong> {{ $organizationInfo['email'] }}</span>
                @endif
                @if($organizationInfo['website'])
                    <span><strong>Website:</strong> {{ $organizationInfo['website'] }}</span>
                @endif
            </div>
            <div class="d-flex justify-content-center gap-3 flex-wrap mt-1">
                @if($organizationInfo['registration_number'])
                    <span><strong>Reg. No:</strong> {{ $organizationInfo['registration_number'] }}</span>
                @endif
                @if($organizationInfo['tax_id'])
                    <span><strong>Tax ID:</strong> {{ $organizationInfo['tax_id'] }}</span>
                @endif
            </div>
        </div>
        
        {{-- Meeting Category and Title --}}
        <h4 class="fw-bold text-uppercase mb-2" style="font-size: 18px; color: #000;">
            {{ $meeting->category_name ?? 'BOARD MEETING' }}
        </h4>
        <h5 class="text-uppercase mb-2" style="font-size: 16px; font-weight: 600; color: #333;">
            MINUTES OF THE {{ strtoupper($meeting->category_name ?? 'BOARD') }} MEETING
        </h5>
        
        {{-- Meeting Details --}}
        <div class="mt-3" style="font-size: 14px; line-height: 1.8;">
            <p class="mb-1">
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, d F Y') }}
            </p>
            <p class="mb-1">
                <strong>Time:</strong> {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }} 
                @if($meeting->end_time)
                    - {{ \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }}
                @endif
            </p>
            <p class="mb-0">
                <strong>Venue:</strong> {{ $meeting->venue ?? $meeting->location ?? 'N/A' }}
                @if($meeting->branch_name)
                    ({{ $meeting->branch_name }})
                @endif
            </p>
        </div>
    </div>

    {{-- OPENING SECTION --}}
    @if($minutes && isset($minutes->opening_prayer_leader))
    <div class="minutes-section mb-4" style="border-left: 4px solid #007bff; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">AGENDA NO. 1: OPENING OF MEETING</h6>
        <p class="mb-2" style="text-align: justify; line-height: 1.8;">
            <strong>Ref. No. 1</strong><br>
            The meeting was opened at {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }} 
            with prayer led by {{ isset($minutes->opening_prayer_leader) && $minutes->opening_prayer_leader ? $minutes->opening_prayer_leader : '[CHAIRPERSON/VICE CHAIRPERSON]' }}, 
            followed by hymn {{ isset($minutes->opening_hymn) && $minutes->opening_hymn ? $minutes->opening_hymn : '[HYMN NUMBER]' }} 
            and scripture reading from {{ isset($minutes->opening_scripture) && $minutes->opening_scripture ? $minutes->opening_scripture : '[BIBLE REFERENCE]' }}.
        </p>
        @if(isset($minutes->opening_remarks) && $minutes->opening_remarks)
        <p class="mb-2" style="text-align: justify; line-height: 1.8;">
            {{ $minutes->opening_remarks }}
        </p>
        @endif
        <p class="mb-0" style="text-align: justify; line-height: 1.8;">
            Opening was concluded with prayer led by {{ isset($minutes->opening_closing_prayer_leader) && $minutes->opening_closing_prayer_leader ? $minutes->opening_closing_prayer_leader : '[NAME]' }}.
        </p>
    </div>
    @endif

    {{-- ATTENDANCE SECTION --}}
    <div class="minutes-section mb-4" style="border-left: 4px solid #28a745; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">AGENDA NO. 2: ATTENDANCE REVIEW AND AGENDA REVIEW</h6>
        <p class="mb-3" style="text-align: justify; line-height: 1.8;">
            <strong>Ref. No. 2</strong><br>
            @if($minutes && isset($minutes->quorum_statement))
                {{ $minutes->quorum_statement }}
            @else
                The Chairperson reviewed attendance of Board Members considering the 
                [ORGANIZATION] Constitution Article [X] which states "Board meetings will be 
                valid if members in attendance are half of all Board Members". The quorum was 
                satisfied with the attendance of {{ $attendees->where('participant_type', 'staff')->count() }} Board Members, 
                {{ $attendees->where('participant_type', 'external')->count() }} Invitee(s).
            @endif
        </p>

        {{-- Board Members Table --}}
        @php
            $boardMembers = $attendees->where('participant_type', 'staff');
            $invitees = $attendees->where('participant_type', 'external');
        @endphp
        @if($boardMembers->count() > 0)
        <div class="mb-4">
            <h6 class="fw-bold mb-2" style="font-size: 13px;">ATTENDEES:</h6>
            <table class="table table-bordered table-sm" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                        <th width="5%" style="padding: 8px;">No.</th>
                        <th width="40%" style="padding: 8px;">NAME</th>
                        <th width="55%" style="padding: 8px;">POSITION/TITLE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($boardMembers as $index => $member)
                    <tr>
                        <td style="padding: 8px;">{{ $index + 1 }}</td>
                        <td style="padding: 8px;"><strong>{{ $member->user_name ?? $member->name }}</strong></td>
                        <td style="padding: 8px;">{{ $member->role ?? 'Board Member' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Invitees Table --}}
        @if($invitees->count() > 0)
        <div class="mb-4">
            <h6 class="fw-bold mb-2" style="font-size: 13px;">INVITEES:</h6>
            <table class="table table-bordered table-sm" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                        <th width="5%" style="padding: 8px;">No.</th>
                        <th width="40%" style="padding: 8px;">NAME</th>
                        <th width="55%" style="padding: 8px;">POSITION/TITLE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitees as $index => $invitee)
                    <tr>
                        <td style="padding: 8px;">{{ $index + 1 }}</td>
                        <td style="padding: 8px;"><strong>{{ $invitee->name }}</strong></td>
                        <td style="padding: 8px;">{{ $invitee->role ?? 'Invitee' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Agenda Items List --}}
        @if($agendas && $agendas->count() > 0)
        <div class="mb-3">
            <h6 class="fw-bold mb-2" style="font-size: 13px;">AGENDA ITEMS:</h6>
            <ol style="padding-left: 20px;">
                @foreach($agendas as $agenda)
                <li style="margin-bottom: 5px;">{{ $agenda->title }}</li>
                @endforeach
            </ol>
        </div>
        @endif
    </div>

    {{-- PREVIOUS MEETING MINUTES --}}
    @if($minutes && isset($minutes->previous_meeting_date))
    <div class="minutes-section mb-4" style="border-left: 4px solid #6c757d; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">AGENDA NO. 3: READING OF PREVIOUS MEETING MINUTES</h6>
        <p class="mb-0" style="text-align: justify; line-height: 1.8;">
            <strong>Ref. No. 3</strong><br>
            The Chairperson presented members with minutes from the previous meeting 
            dated {{ \Carbon\Carbon::parse($minutes->previous_meeting_date)->format('d/m/Y') }}. 
            {{ $minutes->previous_minutes_confirmation ?? 'Members confirmed that these accurately reflect what was discussed in those meetings.' }}
        </p>
    </div>
    @endif

    {{-- FOLLOW-UPS FROM PREVIOUS MEETING (YATOKANAYO) --}}
    @php
        // Load follow-ups if stored in database
        $followUps = collect();
        if ($minutes && isset($minutes->followups)) {
            $followUps = is_string($minutes->followups) ? json_decode($minutes->followups, true) : $followUps;
            if (is_array($followUps)) {
                $followUps = collect($followUps);
            }
        }
    @endphp
    @if($followUps->count() > 0)
    <div class="minutes-section mb-4" style="border-left: 4px solid #ffc107; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">
            AGENDA NO. 4: FOLLOW-UPS FROM MEETING DATED {{ $minutes->previous_meeting_date ? \Carbon\Carbon::parse($minutes->previous_meeting_date)->format('d/m/Y') : '[DATE]' }}
        </h6>
        <p class="mb-3" style="text-align: justify; line-height: 1.8;">
            <strong>Ref. No. 4</strong>
        </p>
        <div class="table-responsive">
            <table class="table table-bordered" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                        <th width="10%" style="padding: 8px;">Ref. No.</th>
                        <th width="35%" style="padding: 8px;">DESCRIPTION</th>
                        <th width="35%" style="padding: 8px;">BOARD ORDERS/RESOLUTIONS</th>
                        <th width="20%" style="padding: 8px;">IMPLEMENTATION STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($followUps as $index => $followUp)
                    <tr>
                        <td style="padding: 8px;"><strong>{{ $followUp['ref_no'] ?? 'Ref. ' . ($index + 1) }}</strong></td>
                        <td style="padding: 8px;">{{ $followUp['description'] ?? '' }}</td>
                        <td style="padding: 8px;">{{ $followUp['board_orders'] ?? '' }}</td>
                        <td style="padding: 8px;">
                            <span class="badge bg-{{ $followUp['status'] == 'completed' ? 'success' : ($followUp['status'] == 'in_progress' ? 'warning' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $followUp['status'] ?? 'pending')) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- AGENDA ITEMS WITH DISCUSSIONS AND RESOLUTIONS --}}
    @if($agendas && $agendas->count() > 0)
        @foreach($agendas as $index => $agenda)
        @php
            $agendaNumber = $index + 5; // Starting from Agenda No. 5
            $discussion = $agenda->discussion_notes ?? '';
            $resolution = $agenda->resolution ?? '';
            $boardOrders = isset($agenda->board_orders) ? (is_string($agenda->board_orders) ? explode("\n", $agenda->board_orders) : $agenda->board_orders) : [];
        @endphp
        <div class="minutes-section mb-4" style="border-left: 4px solid #17a2b8; padding-left: 20px;">
            <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">
                AGENDA NO. {{ $agendaNumber }}: {{ strtoupper($agenda->title) }}
            </h6>
            <p class="mb-2" style="text-align: justify; line-height: 1.8;">
                <strong>Ref. No. {{ $agendaNumber }}</strong>
                @if($agenda->presenter_name)
                    <br><em>Presented by: {{ $agenda->presenter_name }}</em>
                @endif
            </p>

            @if($discussion)
            <div class="mb-3">
                <strong>DISCUSSION:</strong>
                <p class="mb-0" style="text-align: justify; line-height: 1.8; white-space: pre-wrap;">{{ $discussion }}</p>
            </div>
            @endif

            @if($resolution)
            <div class="mb-3">
                <strong>RESOLUTION/DECISION:</strong>
                <p class="mb-0" style="text-align: justify; line-height: 1.8; white-space: pre-wrap;">{{ $resolution }}</p>
            </div>
            @endif

            @if(!empty($boardOrders))
            <div class="mb-3">
                <strong>BOARD ORDER NO. {{ $index + 1 }}:</strong>
                <p>The Board issued the following directives to Management:</p>
                <ol style="padding-left: 25px;">
                    @foreach($boardOrders as $order)
                        @if(trim($order))
                        <li style="margin-bottom: 5px; text-align: justify; line-height: 1.8;">{{ trim($order) }}</li>
                        @endif
                    @endforeach
                </ol>
            </div>
            @endif
        </div>
        @endforeach
    @endif

    {{-- ACTION ITEMS --}}
    @if($actionItems && $actionItems->count() > 0)
    <div class="minutes-section mb-4" style="border-left: 4px solid #6610f2; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">ACTION ITEMS</h6>
        <div class="table-responsive">
            <table class="table table-bordered" style="font-size: 12px;">
                <thead class="table-light">
                    <tr>
                        <th width="5%" style="padding: 8px;">#</th>
                        <th width="30%" style="padding: 8px;">Action</th>
                        <th width="25%" style="padding: 8px;">Responsible</th>
                        <th width="15%" style="padding: 8px;">Deadline</th>
                        <th width="15%" style="padding: 8px;">Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actionItems as $index => $item)
                    <tr>
                        <td style="padding: 8px;">{{ $index + 1 }}</td>
                        <td style="padding: 8px;">{{ $item->description ?? $item->title ?? 'N/A' }}</td>
                        <td style="padding: 8px;">{{ $item->responsible_name ?? 'TBD' }}</td>
                        <td style="padding: 8px;">{{ $item->deadline ? \Carbon\Carbon::parse($item->deadline)->format('d M Y') : 'TBD' }}</td>
                        <td style="padding: 8px;">
                            <span class="badge bg-{{ $item->priority == 'urgent' ? 'danger' : ($item->priority == 'high' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($item->priority ?? 'medium') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ANY OTHER BUSINESS (AOB) --}}
    @if($minutes && $minutes->aob)
    <div class="minutes-section mb-4" style="border-left: 4px solid #e83e8c; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">
            AGENDA NO. {{ ($agendas ? $agendas->count() : 0) + 8 }}: ANY OTHER BUSINESS
        </h6>
        <p class="mb-0" style="text-align: justify; line-height: 1.8;">
            <strong>Ref. No. {{ ($agendas ? $agendas->count() : 0) + 8 }}</strong><br>
            {{ $minutes->aob }}
        </p>
    </div>
    @endif

    {{-- CLOSING SECTION --}}
    @if($minutes && $minutes->closing_time)
    <div class="minutes-section mb-4" style="border-left: 4px solid #dc3545; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px; text-transform: uppercase;">
            AGENDA NO. {{ ($agendas ? $agendas->count() : 0) + 9 }}: CLOSING OF MEETING
        </h6>
        <p class="mb-2" style="text-align: justify; line-height: 1.8;">
            <strong>Ref. No. {{ ($agendas ? $agendas->count() : 0) + 9 }}</strong><br>
            The meeting was closed at {{ $minutes->closing_time }} 
            @if(isset($minutes->closing_hymn) && $minutes->closing_hymn)
                with the hymn "{{ $minutes->closing_hymn }}" 
            @endif
            followed by prayer led by {{ isset($minutes->closing_prayer_leader) && $minutes->closing_prayer_leader ? $minutes->closing_prayer_leader : '[NAME]' }} 
            and concluded with the words "{{ isset($minutes->closing_remarks) && $minutes->closing_remarks ? $minutes->closing_remarks : 'NEEMA' }}".
        </p>
        @if(isset($minutes->organization_motto) && $minutes->organization_motto)
        <p class="mb-0" style="font-weight: 600; font-style: italic;">
            {{ $minutes->organization_motto }}
        </p>
        @endif
    </div>
    @endif

    {{-- NEXT MEETING --}}
    @if($minutes && $minutes->next_meeting_date)
    <div class="minutes-section mb-4" style="border-left: 4px solid #20c997; padding-left: 20px;">
        <h6 class="fw-bold mb-3" style="font-size: 14px;">NEXT MEETING</h6>
        <p class="mb-0" style="text-align: justify; line-height: 1.8;">
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($minutes->next_meeting_date)->format('l, d F Y') }}<br>
            @if($minutes->next_meeting_time)
                <strong>Time:</strong> {{ $minutes->next_meeting_time }}<br>
            @endif
            @if($minutes->next_meeting_venue)
                <strong>Venue:</strong> {{ $minutes->next_meeting_venue }}
            @endif
        </p>
    </div>
    @endif

    {{-- SIGNATURES --}}
    <div class="mt-5 pt-4 border-top" style="margin-top: 50px; padding-top: 30px;">
        <div class="row">
            <div class="col-md-6 mb-4">
                <p class="mb-1" style="font-weight: bold; font-size: 14px;">CHAIRPERSON</p>
                <div style="border-top: 1px solid #000; width: 300px; margin-top: 60px; margin-bottom: 10px;"></div>
                <p class="mb-0" style="font-size: 12px;">{{ $minutes->approved_by_name ?? '[NAME]' }}</p>
            </div>
            <div class="col-md-6 mb-4">
                <p class="mb-1" style="font-weight: bold; font-size: 14px;">GENERAL MANAGER/SECRETARY</p>
                <div style="border-top: 1px solid #000; width: 300px; margin-top: 60px; margin-bottom: 10px;"></div>
                <p class="mb-0" style="font-size: 12px;">{{ $minutes->prepared_by_name ?? '[NAME]' }}</p>
            </div>
        </div>
    </div>

</div>

