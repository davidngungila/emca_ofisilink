# Meeting Minutes Format - Quick Reference Guide

## Standard Format Structure

### 1. HEADER
```
[ORGANIZATION NAME]
[MEETING TYPE]
MINUTES OF THE [TYPE] MEETING - [PERIOD/YEAR]
[DATE: DD/MM/YYYY]
VENUE: [LOCATION]
```

### 2. OPENING SECTION
**Agenda No. 1: Opening of Meeting**
- Ref. No. 1
- Opening time and prayer leader
- Hymn (if applicable)
- Scripture reading
- Opening remarks/theme
- Closing prayer

### 3. ATTENDANCE & AGENDA REVIEW
**Agenda No. 2: Attendance Review and Agenda Review**
- Ref. No. 2
- Quorum verification statement
- **ATTENDEES Table:**
  - No. | Name | Position/Title
- **INVITEES Table:**
  - No. | Name | Position/Title
- **Agenda Items List** (numbered)

### 4. PREVIOUS MINUTES
**Agenda No. 3: Reading of Previous Meeting Minutes**
- Ref. No. 3
- Date of previous meeting
- Confirmation statement

### 5. FOLLOW-UPS FROM PREVIOUS MEETING (YATOKANAYO)
**Agenda No. 4: Follow-ups from Meeting Dated [DATE]**
- Ref. No. 4

**Table Format:**
| Ref. No. | DESCRIPTION | BOARD ORDERS/RESOLUTIONS | IMPLEMENTATION STATUS |
|----------|-------------|--------------------------|----------------------|
| Ref. 1   | [Item description] | [Board directive] | [Completed/In Progress/Pending] |

**Column Definitions:**
- **Ref. No.** - Reference number from previous meeting
- **DESCRIPTION (MAELEZO YA JAMBO)** - Brief description of the matter
- **BOARD ORDERS/RESOLUTIONS (MAAGIZO YA BODI)** - Specific directives made by Board
- **IMPLEMENTATION STATUS (UTEKELEZAJI)** - Current status (Completed/In Progress/Pending/Deferred)

### 6. AGENDA ITEMS (Numbered sequentially)
**Agenda No. [N]: [TITLE]**

**Structure:**
- Ref. No. [N]
- Presented by: [Name] (if applicable)
- **DISCUSSION:** [Detailed notes]
- **RESOLUTION/DECISION:** [Clear statement]
- **BOARD ORDER NO. [N]:** (if applicable)
  - Numbered list of directives to Management

**Example:**
```
AGENDA NO. 5: MANAGER'S REPORT

Ref. No. 5
Presented by: [Manager Name]

DISCUSSION:
[Detailed discussion notes, data, statistics, etc.]

RESOLUTION/DECISION:
[Decision made by the Board]

BOARD ORDER NO. 1:
The Board issued the following directives to Management:
1. [Directive 1]
2. [Directive 2]
3. ...
```

### 7. COMMITTEE REPORTS
**Agenda No. [N]: [COMMITTEE NAME] COMMITTEE REPORT**

**Structure:**
- Ref. No. [N]
- Report presented by: [Name]
- **REPORT SUMMARY:** [Overview of activities, findings, recommendations]
- **BOARD DECISIONS:** (numbered list)

**Common Committees:**
- Loans Committee
- Finance and Audit Committee
- TEHAMA, Investment and Procurement Committee
- Education, Training and Service Committee
- Marketing and Awareness Committee
- Management Committee
- Internal Audit Unit

### 8. INTERNAL AUDIT REPORT
**Agenda No. [N]: INTERNAL AUDIT UNIT REPORT**

- Ref. No. [N]
- **REPORT:** [Findings, observations, recommendations]
- **BOARD DIRECTIVES TO INTERNAL AUDITOR:** (numbered list)

### 9. MEMBER REPRESENTATIVE REPORT
**Agenda No. [N]: MEMBER REPRESENTATIVE REPORT**

- Ref. No. [N]
- **REPORT:** [Member concerns, feedback, suggestions]
- **BOARD DECISIONS:** (numbered list)

### 10. ANY OTHER BUSINESS (AOB)
**Agenda No. [N]: ANY OTHER BUSINESS**

- Ref. No. [N]
- [Any additional matters discussed]

### 11. CLOSING
**Agenda No. [N]: CLOSING OF MEETING**

- Ref. No. [N]
- Closing time
- Closing hymn
- Closing prayer leader
- Closing remarks/words
- Organization motto/tagline

**Example:**
```
The meeting was closed at [TIME] with the hymn "[HYMN TITLE]" 
followed by prayer led by [NAME] and concluded with the words 
"[CLOSING WORDS]".

[ORGANIZATION MOTTO]
"UNITY IS OUR PROGRESS"
```

### 12. NEXT MEETING
- Date: [DD MMM YYYY]
- Time: [HH:MM AM/PM]
- Venue: [LOCATION]

### 13. SIGNATURES
```
CHAIRPERSON              GENERAL MANAGER/SECRETARY

_________________        _________________

[NAME]                   [NAME]
```

---

## Key Formatting Rules

1. **Reference Numbers:** Sequential numbering for all agenda items (Ref. No. 1, 2, 3...)

2. **Board Orders:** When Board issues directives, format as:
   ```
   BOARD ORDER NO. [N]:
   The Board issued the following directives to Management:
   1. [Directive]
   2. [Directive]
   ```

3. **Board Decisions:** Format as:
   ```
   BOARD DECISIONS:
   The Board made the following decisions:
   1. [Decision]
   2. [Decision]
   ```

4. **Financial Data:** Always include:
   - Currency (TSh., USD, etc.)
   - Actual vs. Budgeted figures
   - Percentages for comparisons
   - Example: "TSh. 15,733,436,556/= (88% of budget)"

5. **Implementation Status:** Use clear status labels:
   - Completed
   - In Progress
   - Pending
   - Deferred

6. **Tables:** Use for:
   - Attendance lists
   - Follow-up items (YATOKANAYO)
   - Action items tracking

7. **Numbered Lists:** Use for:
   - Agenda items
   - Board directives
   - Decisions
   - Action items

---

## Template Variables Reference

When implementing in the system, use these variables:

- `$meeting->title` - Meeting title
- `$meeting->meeting_date` - Meeting date
- `$meeting->start_time` - Start time
- `$meeting->end_time` - End time
- `$meeting->venue` - Venue
- `$meeting->category_name` - Meeting category
- `$boardMembers` - Board member attendees array
- `$invitees` - Invitee attendees array
- `$agendas` - Agenda items collection
- `$minutes` - Minutes object
- `$previousActions` - Follow-up items from previous meeting
- `$committeeReports` - Committee reports array
- `$internalAuditReport` - Internal audit report object
- `$memberRepresentativeReport` - Member representative report object

---

## Quick Checklist

Before finalizing minutes, ensure:

- [ ] All agenda items have reference numbers
- [ ] Attendance is properly recorded (Board Members and Invitees separate)
- [ ] Quorum verification statement is included
- [ ] All previous meeting follow-ups are documented with status
- [ ] All discussions have clear resolutions/decisions
- [ ] All Board directives are numbered and clear
- [ ] Financial data includes currency and comparisons
- [ ] Closing time and details are recorded
- [ ] Next meeting details are included (if scheduled)
- [ ] Signatures section is prepared
- [ ] All reference numbers are sequential
- [ ] All tables are properly formatted
- [ ] Organization name and motto are correct

---

**Version:** 1.0  
**Date:** January 2025





