<!DOCTYPE html>
<html>
<head>
    <title>Performance Report - {{ $user->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1a1a1a; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; background: #f0f0f0; padding: 5px; margin-bottom: 10px; border-left: 4px solid #3366cc; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; width: 30%; }
        .score-box { background: #3366cc; color: white; padding: 20px; text-align: center; border-radius: 8px; }
        .score-box h2 { margin: 5px 0; font-size: 24px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Individual Performance Report</h1>
        <p>Generated on {{ date('d M Y, H:i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Employee Information</div>
        <table>
            <tr>
                <th>Name</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $user->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Reporting Period</th>
                <td>{{ date('Y') }} Annual Review</td>
            </tr>
        </table>
    </div>

    <div class="section" style="width: 50%; margin: 0 auto;">
        <div class="score-box">
            <div>Composite Performance Score</div>
            <h2>{{ number_format($compositeScore, 1) }}%</h2>
            <div>Rank: 
                @if($compositeScore >= 90) Excellent
                @elseif($compositeScore >= 75) Good
                @elseif($compositeScore >= 50) Average
                @else Improvement Needed
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Activity Breakdown</div>
        <table style="border: none;">
            <thead>
                <tr style="background: #eee;">
                    <th style="width: 40%">Activity</th>
                    <th style="width: 20%">Weight</th>
                    <th style="width: 20%">Progress</th>
                    <th style="width: 20%">Quality</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myActivities as $activity)
                <tr>
                    <td>{{ $activity->activity_name }}</td>
                    <td>{{ $activity->contribution_percentage }}%</td>
                    <td>{{ $activity->current_progress }}%</td>
                    <td>
                        {{ $activity->progressReports->where('status', 'approved')->avg('quality_rating') ?? 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Document - {{ config('app.name') }} Performance management System
    </div>
</body>
</html>
