{{-- 
    MEETING MINUTES TEMPLATE STRUCTURE
    ===================================
    
    This template follows the format used for Board Meeting Minutes
    
    STRUCTURE:
    1. Header Section
    2. Opening Section
    3. Attendance Section
    4. Previous Meeting Follow-ups (YATOKANAYO)
    5. Agenda Items & Resolutions
    6. Committee Reports
    7. Action Items & Decisions
    8. Closing Section
    9. Signatures
--}}

{{-- 1. HEADER SECTION --}}
<div class="minutes-header text-center mb-4">
    <h3 class="fw-bold text-uppercase">{{ config('app.organization_name', 'ORGANIZATION NAME') }}</h3>
    <h4 class="fw-bold text-uppercase">{{ $meeting->category_name ?? 'MEETING TYPE' }}</h4>
    <h5 class="text-uppercase">MINUTES OF THE {{ strtoupper($meeting->category_name ?? 'BOARD') }} MEETING</h5>
    <h6 class="text-uppercase">{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d/m/Y') }}</h6>
    <p class="mb-0">
        <strong>Venue:</strong> {{ $meeting->venue ?? 'N/A' }}
    </p>
</div>

{{-- 2. OPENING SECTION --}}
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. 1: OPENING OF MEETING</h6>
    <p class="mb-2">
        <strong>Ref. No. 1</strong><br>
        The meeting was opened at {{ \Carbon\Carbon::parse($meeting->start_time)->format('h:i A') }} 
        with prayer led by [CHAIRPERSON/VICE CHAIRPERSON], followed by hymn [HYMN NUMBER] 
        and scripture reading from [BIBLE REFERENCE].
    </p>
    <p>
        [Opening remarks/theme of the meeting - e.g., "All matters should be done with love. 
        Love is a powerful word, to do everything with love. Love is a sign of mercy and 
        recognizing that God loves us. The Board meeting is a sign of love that we love our 
        organization..."]
    </p>
    <p>
        Opening was concluded with prayer led by [NAME].
    </p>
</div>

{{-- 3. ATTENDANCE SECTION --}}
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. 2: ATTENDANCE REVIEW AND AGENDA REVIEW</h6>
    <p class="mb-3">
        <strong>Ref. No. 2</strong><br>
        The Chairperson reviewed attendance of Board Members considering the 
        [ORGANIZATION] Constitution Article [X] which states "Board meetings will be 
        valid if members in attendance are half of all Board Members". The quorum was 
        satisfied with the attendance of [NUMBER] Board Members, [NUMBER] Member(s) 
        Representative(s), and [NUMBER] member(s) of the [COMMITTEE NAME] Committee.
    </p>
    
    {{-- Board Members Attendance --}}
    <div class="mb-3">
        <h6 class="fw-bold">ATTENDEES:</h6>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="40%">NAME</th>
                    <th width="55%">POSITION/TITLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($boardMembers as $index => $member)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->position ?? $member->title ?? 'Board Member' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- Invitees/Guest Attendance --}}
    @if($invitees && $invitees->count() > 0)
    <div class="mb-3">
        <h6 class="fw-bold">INVITEES:</h6>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="40%">NAME</th>
                    <th width="55%">POSITION/TITLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invitees as $index => $invitee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $invitee->name }}</td>
                    <td>{{ $invitee->position ?? $invitee->title ?? 'Invitee' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    {{-- Agenda Items List --}}
    <div class="mt-3">
        <h6 class="fw-bold">AGENDA ITEMS:</h6>
        <ol>
            @foreach($agendas as $agenda)
            <li>{{ $agenda->title }}</li>
            @endforeach
        </ol>
    </div>
</div>

{{-- 4. PREVIOUS MEETING MINUTES --}}
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. 3: READING OF PREVIOUS MEETING MINUTES</h6>
    <p class="mb-2">
        <strong>Ref. No. 3</strong><br>
        The Chairperson presented members with minutes from the previous meeting 
        dated {{ $previousMeetingDate ?? '[DATE]' }}. Members confirmed that these 
        accurately reflect what was discussed in those meetings.
    </p>
</div>

{{-- 5. FOLLOW-UPS FROM PREVIOUS MEETING (YATOKANAYO) --}}
@if($previousActions && $previousActions->count() > 0)
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. 4: FOLLOW-UPS FROM MEETING DATED {{ $previousMeetingDate ?? '[DATE]' }}</h6>
    <p class="mb-3">
        <strong>Ref. No. 4</strong>
    </p>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="10%">Ref. No.</th>
                <th width="35%">DESCRIPTION</th>
                <th width="35%">BOARD ORDERS/RESOLUTIONS</th>
                <th width="20%">IMPLEMENTATION STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($previousActions as $index => $action)
            <tr>
                <td><strong>Ref. {{ $action->reference_number ?? ($index + 1) }}</strong></td>
                <td>{{ $action->description }}</td>
                <td>{{ $action->board_order ?? $action->resolution ?? 'N/A' }}</td>
                <td>
                    @if($action->implementation_status)
                        <span class="badge bg-{{ $action->implementation_status == 'completed' ? 'success' : ($action->implementation_status == 'in_progress' ? 'warning' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $action->implementation_status)) }}
                        </span>
                    @else
                        <span class="text-muted">Pending</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- 6. AGENDA ITEMS WITH DISCUSSIONS AND RESOLUTIONS --}}
@foreach($agendas as $index => $agenda)
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. {{ $index + 5 }}: {{ strtoupper($agenda->title) }}</h6>
    <p class="mb-2">
        <strong>Ref. No. {{ $index + 5 }}</strong>
        @if($agenda->presenter_name)
            <br><em>Presented by: {{ $agenda->presenter_name }}</em>
        @endif
    </p>
    
    @if($agenda->discussion_notes)
    <div class="mb-3">
        <strong>DISCUSSION:</strong>
        <p class="mb-0">{{ $agenda->discussion_notes }}</p>
    </div>
    @endif
    
    @if($agenda->resolution)
    <div class="mb-3">
        <strong>RESOLUTION/DECISION:</strong>
        <p class="mb-0">{{ $agenda->resolution }}</p>
    </div>
    @endif
    
    {{-- Board Orders from this Agenda Item --}}
    @if($agenda->board_orders && count($agenda->board_orders) > 0)
    <div class="mb-3">
        <strong>BOARD ORDER NO. {{ $index + 1 }}:</strong>
        <p>The Board issued the following directives to Management:</p>
        <ol>
            @foreach($agenda->board_orders as $orderIndex => $order)
            <li>{{ $order }}</li>
            @endforeach
        </ol>
    </div>
    @endif
</div>
@endforeach

{{-- 7. COMMITTEE REPORTS SECTION (if applicable) --}}
@if(isset($committeeReports) && count($committeeReports) > 0)
@foreach($committeeReports as $reportIndex => $report)
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. {{ count($agendas) + 5 + $reportIndex }}: COMMITTEE REPORT - {{ strtoupper($report->committee_name) }}</h6>
    <p class="mb-2">
        <strong>Ref. No. {{ count($agendas) + 5 + $reportIndex }}</strong>
    </p>
    
    @if($report->presenter_name)
        <p><em>Report presented by: {{ $report->presenter_name }}</em></p>
    @endif
    
    @if($report->report_content)
        <div class="mb-3">
            <strong>REPORT SUMMARY:</strong>
            <p class="mb-0">{!! nl2br(e($report->report_content)) !!}</p>
        </div>
    @endif
    
    @if($report->decisions && count($report->decisions) > 0)
    <div class="mb-3">
        <strong>BOARD DECISIONS:</strong>
        <ol>
            @foreach($report->decisions as $decision)
            <li>{{ $decision }}</li>
            @endforeach
        </ol>
    </div>
    @endif
</div>
@endforeach
@endif

{{-- 8. INTERNAL AUDIT REPORT (if applicable) --}}
@if(isset($internalAuditReport))
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. {{ $auditAgendaNumber ?? count($agendas) + 6 }}: INTERNAL AUDIT UNIT REPORT</h6>
    <p class="mb-2">
        <strong>Ref. No. {{ $auditAgendaNumber ?? count($agendas) + 6 }}</strong>
    </p>
    
    @if($internalAuditReport->report_content)
        <div class="mb-3">
            <p>{{ $internalAuditReport->report_content }}</p>
        </div>
    @endif
    
    @if($internalAuditReport->directives && count($internalAuditReport->directives) > 0)
    <div class="mb-3">
        <strong>BOARD DIRECTIVES TO INTERNAL AUDITOR:</strong>
        <p>The Board directed the Internal Auditor to undertake the following:</p>
        <ol>
            @foreach($internalAuditReport->directives as $directive)
            <li>{{ $directive }}</li>
            @endforeach
        </ol>
    </div>
    @endif
</div>
@endif

{{-- 9. MEMBER REPRESENTATIVE REPORT (if applicable) --}}
@if(isset($memberRepresentativeReport))
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. {{ $memberRepAgendaNumber ?? count($agendas) + 7 }}: MEMBER REPRESENTATIVE REPORT</h6>
    <p class="mb-2">
        <strong>Ref. No. {{ $memberRepAgendaNumber ?? count($agendas) + 7 }}</strong>
    </p>
    
    @if($memberRepresentativeReport->report_content)
        <div class="mb-3">
            <p>{{ $memberRepresentativeReport->report_content }}</p>
        </div>
    @endif
    
    @if($memberRepresentativeReport->decisions && count($memberRepresentativeReport->decisions) > 0)
    <div class="mb-3">
        <strong>BOARD DECISIONS:</strong>
        <ol>
            @foreach($memberRepresentativeReport->decisions as $decision)
            <li>{{ $decision }}</li>
            @endforeach
        </ol>
    </div>
    @endif
</div>
@endif

{{-- 10. ANY OTHER BUSINESS (AOB) --}}
@if($minutes && $minutes->aob)
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. {{ $aobAgendaNumber ?? count($agendas) + 8 }}: ANY OTHER BUSINESS</h6>
    <p class="mb-2">
        <strong>Ref. No. {{ $aobAgendaNumber ?? count($agendas) + 8 }}</strong>
    </p>
    <p>{{ $minutes->aob }}</p>
</div>
@endif

{{-- 11. CLOSING SECTION --}}
<div class="minutes-section mb-4">
    <h6 class="fw-bold">AGENDA NO. {{ $closingAgendaNumber ?? count($agendas) + 9 }}: CLOSING OF MEETING</h6>
    <p class="mb-2">
        <strong>Ref. No. {{ $closingAgendaNumber ?? count($agendas) + 9 }}</strong><br>
        The meeting was closed at {{ $minutes->closing_time ?? \Carbon\Carbon::parse($meeting->end_time)->format('h:i A') }} 
        with the hymn "[HYMN TITLE]" followed by prayer led by [NAME] and concluded with 
        the words "[CLOSING WORDS]".
    </p>
    @if($minutes && $minutes->closing_remarks)
        <p><em>{{ $minutes->closing_remarks }}</em></p>
    @endif
    <p class="mb-0">
        <strong>[ORGANIZATION MOTTO/TAGLINE]</strong><br>
        <em>e.g., "Unity is Our Progress"</em>
    </p>
</div>

{{-- 12. NEXT MEETING --}}
@if($minutes && $minutes->next_meeting_date)
<div class="minutes-section mb-4">
    <h6 class="fw-bold">NEXT MEETING</h6>
    <p class="mb-0">
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

{{-- 13. SIGNATURES SECTION --}}
<div class="minutes-signatures mt-5 pt-4 border-top">
    <div class="row">
        <div class="col-md-6 mb-4">
            <p class="mb-1"><strong>CHAIRPERSON</strong></p>
            <div class="signature-line mb-2" style="border-top: 1px solid #000; width: 300px; margin-top: 60px;"></div>
            <p class="mb-0"><small>{{ $chairpersonName ?? '[NAME]' }}</small></p>
        </div>
        <div class="col-md-6 mb-4">
            <p class="mb-1"><strong>GENERAL MANAGER/SECRETARY</strong></p>
            <div class="signature-line mb-2" style="border-top: 1px solid #000; width: 300px; margin-top: 60px;"></div>
            <p class="mb-0"><small>{{ $secretaryName ?? '[NAME]' }}</small></p>
        </div>
    </div>
</div>





