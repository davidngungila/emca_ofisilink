# Meeting Minutes Template Documentation

## Overview

This directory contains the template structure and documentation for preparing meeting minutes following the organizational format used for Board and Committee meetings.

## Files Created

### 1. `_template-structure.blade.php`
A Blade template file that shows the complete structure for meeting minutes. This is a reference template that can be included or referenced when creating meeting minutes.

**Location:** `resources/views/modules/meetings/minutes/_template-structure.blade.php`

**Usage:**
- Reference when building minutes creation/edit forms
- Use as a guide for what sections to include
- Template variables are marked for dynamic content insertion

### 2. `MINUTES_TEMPLATE_GUIDE.md`
Comprehensive documentation guide explaining the meeting minutes format in detail.

**Location:** `resources/views/modules/meetings/MINUTES_TEMPLATE_GUIDE.md`

**Contents:**
- Complete structure explanation
- Section-by-section breakdown
- Examples for each section
- Best practices
- Template variables reference
- Formatting guidelines

### 3. `minutes-template-format.md`
Quick reference guide for the meeting minutes format.

**Location:** `resources/views/modules/meetings/minutes-template-format.md`

**Contents:**
- Concise format structure
- Quick reference for each section
- Key formatting rules
- Checklist for finalizing minutes
- Template variables quick reference

## Format Structure Summary

The meeting minutes follow this standard structure:

1. **Header Section** - Organization name, meeting type, date, venue
2. **Opening Section** - Opening prayer, hymn, scripture, remarks
3. **Attendance & Agenda Review** - Quorum verification, attendees table, invitees table, agenda list
4. **Previous Minutes** - Confirmation of previous meeting minutes
5. **Follow-ups from Previous Meeting (YATOKANAYO)** - Table format with:
   - Reference Number
   - Description
   - Board Orders/Resolutions
   - Implementation Status
6. **Agenda Items** - Numbered items with:
   - Reference Number
   - Discussion notes
   - Resolution/Decision
   - Board Orders (if applicable)
7. **Committee Reports** - Reports from various committees
8. **Internal Audit Report** - Audit findings and directives
9. **Member Representative Report** - Member concerns and feedback
10. **Any Other Business (AOB)** - Additional matters
11. **Closing Section** - Closing time, hymn, prayer, remarks
12. **Next Meeting** - Date, time, venue
13. **Signatures** - Chairperson and Secretary signatures

## Key Features

### Follow-ups Table (YATOKANAYO)
This is a critical section that tracks action items from previous meetings:

| Ref. No. | DESCRIPTION | BOARD ORDERS/RESOLUTIONS | IMPLEMENTATION STATUS |
|----------|-------------|--------------------------|----------------------|
| Ref. 1   | ...         | ...                      | Completed/In Progress/Pending |

**Implementation Status Options:**
- Completed
- In Progress
- Pending
- Deferred

### Board Orders Format
When the Board issues directives, they are formatted as:

```
BOARD ORDER NO. [N]:
The Board issued the following directives to Management:
1. [Directive 1]
2. [Directive 2]
3. ...
```

### Reference Numbering
All agenda items and resolutions are numbered sequentially:
- Ref. No. 1, Ref. No. 2, Ref. No. 3, etc.

## Implementation Notes

### Database Fields Needed

For the Follow-ups section, you may need these fields in your database:

- `reference_number` - Reference from previous meeting
- `description` - Description of the item
- `board_order` - Board directive/resolution
- `implementation_status` - Status (completed, in_progress, pending, deferred)
- `responsible_party` - Person/team responsible
- `due_date` - Deadline (optional)
- `remarks` - Additional notes

### Agenda Items Structure

Each agenda item should support:
- `title` - Agenda item title
- `reference_number` - Sequential reference number
- `presenter_name` - Person presenting (optional)
- `discussion_notes` - Detailed discussion text
- `resolution` - Decision/resolution text
- `board_orders` - Array/list of Board directives (optional)

### Committee Reports Structure

Each committee report should support:
- `committee_name` - Name of the committee
- `presenter_name` - Person presenting the report
- `report_content` - Report summary/details
- `decisions` - Array/list of Board decisions made

## Integration with Existing System

To integrate this format with your existing meeting minutes system:

1. **Update the minutes creation form** (`create.blade.php`) to include:
   - Follow-ups from previous meeting section
   - Better structured agenda item discussions
   - Board orders/directives section for each agenda item

2. **Update the minutes preview** (`preview.blade.php`) to:
   - Display follow-ups table format
   - Show Board orders in the proper format
   - Include all sections in the correct order

3. **Update the PDF template** (`pdf.blade.php`) to:
   - Match the print-friendly format
   - Include proper table formatting
   - Ensure proper page breaks

4. **Database modifications** (if needed):
   - Add fields for follow-up tracking
   - Add fields for Board orders
   - Add fields for implementation status

## Example Usage

### In Blade Template

```blade
@include('modules.meetings.minutes._template-structure', [
    'meeting' => $meeting,
    'boardMembers' => $boardMembers,
    'invitees' => $invitees,
    'agendas' => $agendas,
    'minutes' => $minutes,
    'previousActions' => $previousActions,
    'committeeReports' => $committeeReports,
    // ... other variables
])
```

### In Controller

```php
public function createMinutes($id)
{
    $meeting = Meeting::with(['participants', 'agendas', 'minutes'])->findOrFail($id);
    
    // Separate board members from invitees
    $boardMembers = $meeting->participants()
        ->where('participant_type', 'board_member')
        ->get();
    
    $invitees = $meeting->participants()
        ->where('participant_type', 'invitee')
        ->get();
    
    // Get previous meeting follow-ups
    $previousMeeting = Meeting::where('id', '<', $id)
        ->where('category_id', $meeting->category_id)
        ->orderBy('id', 'desc')
        ->first();
    
    $previousActions = $previousMeeting 
        ? $previousMeeting->minutes->actionItems ?? collect()
        : collect();
    
    return view('modules.meetings.minutes.create', compact(
        'meeting', 'boardMembers', 'invitees', 'previousActions'
    ));
}
```

## Best Practices

1. **Consistency**: Always use the same format and numbering system
2. **Clarity**: Write decisions and directives clearly and specifically
3. **Completeness**: Ensure all agenda items have discussions and resolutions
4. **Tracking**: Always update implementation status for follow-ups
5. **Verification**: Verify quorum and attendance accurately
6. **Timeliness**: Prepare minutes promptly after the meeting
7. **Approval**: Ensure minutes are approved at the next meeting

## Translation Notes

The original format uses Swahili terminology. When implementing:

- **YATOKANAYO** = Follow-ups / Matters Arising
- **MAELEZO YA JAMBO** = Description of the Matter
- **MAAGIZO YA BODI** = Board Orders/Directives
- **UTEKELEZAJI** = Implementation Status

You can keep these terms in Swahili if that's your organization's preference, or translate them to English.

## Support

For questions or modifications to the template format, refer to:
- `MINUTES_TEMPLATE_GUIDE.md` - Detailed documentation
- `minutes-template-format.md` - Quick reference
- `_template-structure.blade.php` - Code structure

---

**Last Updated:** January 2025  
**Version:** 1.0

