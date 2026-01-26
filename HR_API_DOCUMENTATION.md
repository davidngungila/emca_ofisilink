# HR Management API Documentation

## Complete API Reference for HR Management

**Base URL**: `http://your-domain.com/api`  
**Authentication**: Bearer Token (Laravel Sanctum) - Required for all endpoints  
**Content-Type**: `application/json` (multipart/form-data for file uploads)

---

## Table of Contents

1. [Permission Requests](#permission-requests)
2. [Sick Sheets](#sick-sheets)
3. [Assessments](#assessments)
4. [Recruitment](#recruitment)
5. [Employees](#employees)
6. [Incidents](#incidents)

---

## Permission Requests

### Get Permission Requests

Get all permission requests (filtered by user role).

**Endpoint**: `GET /api/hr/permissions`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **Staff**: Only sees their own permission requests
- **HOD**: Sees permission requests from their department
- **HR Officer/System Admin**: Sees all permission requests

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "request_id": "PR20240115-001",
      "user_id": 5,
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-01",
      "start_time": "10:00:00",
      "end_time": "14:00:00",
      "reason": "Personal appointment",
      "status": "pending_hr",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 50,
    "per_page": 20,
    "last_page": 3
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/permissions?per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get My Permission Requests

Get all permission requests for the authenticated user.

**Endpoint**: `GET /api/hr/permissions/my`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "request_id": "PR20240115-001",
      "user_id": 5,
      "start_date": "2024-02-01",
      "end_date": "2024-02-01",
      "start_time": "10:00:00",
      "end_time": "14:00:00",
      "reason": "Personal appointment",
      "status": "pending_hr",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/permissions/my" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Permission Request Details

Get detailed information about a specific permission request.

**Endpoint**: `GET /api/hr/permissions/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Permission request ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "request_id": "PR20240115-001",
    "user_id": 5,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "start_date": "2024-02-01",
    "end_date": "2024-02-01",
    "start_time": "10:00:00",
    "end_time": "14:00:00",
    "reason": "Personal appointment",
    "status": "pending_hr",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/permissions/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Create Permission Request

Create a new permission request.

**Endpoint**: `POST /api/hr/permissions`  
**Authentication**: Required

### Request Body

```json
{
  "start_date": "2024-02-01",
  "end_date": "2024-02-01",
  "reason": "Personal appointment"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | date | Yes | Start date (YYYY-MM-DD) |
| `end_date` | date | Yes | End date (YYYY-MM-DD, must be >= start_date) |
| `reason` | string | Yes | Reason for permission |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Permission request created successfully",
  "data": {
    "id": 1,
    "request_id": "PR20240115-001",
    "user_id": 5,
    "start_date": "2024-02-01",
    "end_date": "2024-02-01",
    "reason": "Personal appointment",
    "status": "pending_hr",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "start_date": ["The start date field is required."],
    "end_date": ["The end date must be a date after or equal to start date."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/permissions" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2024-02-01",
    "end_date": "2024-02-01",
    "reason": "Personal appointment"
  }'
```

---

### Approve Permission Request

Approve a permission request (HR/HOD/Admin only).

**Endpoint**: `POST /api/hr/permissions/{id}/approve`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Permission request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Permission request approved"
}
```

**Note**: The status progression is:
- `pending_hr` → `pending_hod` (HR Officer approves)
- `pending_hod` → `pending_hr_final` (HOD approves)
- `pending_hr_final` → `approved` (HR Officer final approval)

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/permissions/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Reject Permission Request

Reject a permission request (HR/HOD/Admin only).

**Endpoint**: `POST /api/hr/permissions/{id}/reject`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Permission request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Permission request rejected"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/permissions/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Confirm Return from Permission

Confirm return from permission (employee only).

**Endpoint**: `POST /api/hr/permissions/{id}/confirm-return`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Permission request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Return confirmed"
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/permissions/1/confirm-return" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Sick Sheets

### Get Sick Sheets

Get all sick sheet requests (filtered by user role).

**Endpoint**: `GET /api/hr/sick-sheets`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **Staff**: Only sees their own sick sheets
- **HR Officer/HOD/System Admin**: Sees all sick sheets

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sheet_number": "SS20240115-001",
      "employee_id": 5,
      "employee": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "total_days": 5,
      "doctor_name": "Dr. Smith",
      "hospital_name": "City Hospital",
      "medical_document": "sick_sheets/1234567890_medical_cert.pdf",
      "status": "pending_hr",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 50,
    "per_page": 20,
    "last_page": 3
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/sick-sheets?per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get My Sick Sheets

Get all sick sheet requests for the authenticated user.

**Endpoint**: `GET /api/hr/sick-sheets/my`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sheet_number": "SS20240115-001",
      "employee_id": 5,
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "total_days": 5,
      "status": "pending_hr",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/sick-sheets/my" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Sick Sheet Details

Get detailed information about a specific sick sheet.

**Endpoint**: `GET /api/hr/sick-sheets/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Sick sheet ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "sheet_number": "SS20240115-001",
    "employee_id": 5,
    "employee": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "start_date": "2024-02-01",
    "end_date": "2024-02-05",
    "total_days": 5,
    "doctor_name": "Dr. Smith",
    "hospital_name": "City Hospital",
    "medical_document": "sick_sheets/1234567890_medical_cert.pdf",
    "status": "pending_hr",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/sick-sheets/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Submit Sick Sheet

Submit a new sick sheet with medical document.

**Endpoint**: `POST /api/hr/sick-sheets`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

### Request Body (Form Data)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | date | Yes | Start date (YYYY-MM-DD) |
| `end_date` | date | Yes | End date (YYYY-MM-DD, must be >= start_date) |
| `medical_document` | file | Yes | Medical document (pdf, jpg, jpeg, png, doc, docx, max 5MB) |
| `doctor_name` | string | No | Doctor name |
| `hospital_name` | string | No | Hospital name |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Sick sheet submitted successfully",
  "data": {
    "id": 1,
    "sheet_number": "SS20240115-001",
    "employee_id": 5,
    "start_date": "2024-02-01",
    "end_date": "2024-02-05",
    "total_days": 5,
    "doctor_name": "Dr. Smith",
    "hospital_name": "City Hospital",
    "medical_document": "sick_sheets/1234567890_medical_cert.pdf",
    "status": "pending_hr",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "medical_document": ["The medical document field is required."],
    "start_date": ["The start date field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/sick-sheets" \
  -H "Authorization: Bearer {token}" \
  -F "start_date=2024-02-01" \
  -F "end_date=2024-02-05" \
  -F "medical_document=@/path/to/medical_cert.pdf" \
  -F "doctor_name=Dr. Smith" \
  -F "hospital_name=City Hospital"
```

---

### Approve Sick Sheet

Approve a sick sheet (HR/HOD/Admin only).

**Endpoint**: `POST /api/hr/sick-sheets/{id}/approve`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Sick sheet ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Sick sheet approved"
}
```

**Note**: The status progression is:
- `pending_hr` → `pending_hod` (HR Officer approves)
- `pending_hod` → `approved` (HOD approves)

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/sick-sheets/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Reject Sick Sheet

Reject a sick sheet (HR/HOD/Admin only).

**Endpoint**: `POST /api/hr/sick-sheets/{id}/reject`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Sick sheet ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Sick sheet rejected"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/sick-sheets/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Assessments

### Get Assessments

Get all assessments (filtered by user role).

**Endpoint**: `GET /api/hr/assessments`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **Staff**: Only sees their own assessments
- **HR Officer/HOD/CEO/System Admin**: Sees all assessments

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": 5,
      "employee": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "title": "Q1 Performance Review",
      "status": "pending_hod",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 50,
    "per_page": 20,
    "last_page": 3
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/assessments?per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get My Assessments

Get all assessments for the authenticated user.

**Endpoint**: `GET /api/hr/assessments/my`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": 5,
      "title": "Q1 Performance Review",
      "status": "pending_hod",
      "activities": [
        {
          "id": 1,
          "name": "Complete project tasks",
          "contribution_percentage": 40
        }
      ],
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/assessments/my" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Assessment Details

Get detailed information about a specific assessment.

**Endpoint**: `GET /api/hr/assessments/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Assessment ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_id": 5,
    "employee": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "title": "Q1 Performance Review",
    "status": "pending_hod",
    "activities": [
      {
        "id": 1,
        "name": "Complete project tasks",
        "contribution_percentage": 40,
        "progress_percentage": 75
      },
      {
        "id": 2,
        "name": "Team collaboration",
        "contribution_percentage": 30,
        "progress_percentage": 80
      }
    ],
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/assessments/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Create Assessment

Create a new assessment.

**Endpoint**: `POST /api/hr/assessments`  
**Authentication**: Required

### Request Body

```json
{
  "title": "Q1 Performance Review",
  "activities": [
    {
      "name": "Complete project tasks",
      "contribution_percentage": 40
    },
    {
      "name": "Team collaboration",
      "contribution_percentage": 30
    },
    {
      "name": "Client communication",
      "contribution_percentage": 30
    }
  ]
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `title` | string | Yes | Assessment title (max 255 characters) |
| `activities` | array | Yes | Array of assessment activities (min 1) |
| `activities[].name` | string | Yes | Activity name |
| `activities[].contribution_percentage` | number | Yes | Contribution percentage (0-100) |

**Note**: Sum of all `contribution_percentage` should ideally equal 100.

### Response (201 Created)

```json
{
  "success": true,
  "message": "Assessment created successfully",
  "data": {
    "id": 1,
    "employee_id": 5,
    "title": "Q1 Performance Review",
    "status": "pending_hod",
    "activities": [
      {
        "id": 1,
        "name": "Complete project tasks",
        "contribution_percentage": 40
      }
    ],
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "activities": ["The activities field is required."],
    "activities.0.contribution_percentage": ["The contribution percentage must be between 0 and 100."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/assessments" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Q1 Performance Review",
    "activities": [
      {
        "name": "Complete project tasks",
        "contribution_percentage": 40
      },
      {
        "name": "Team collaboration",
        "contribution_percentage": 30
      }
    ]
  }'
```

---

### Submit Assessment Progress

Submit progress update for an assessment activity.

**Endpoint**: `POST /api/hr/assessments/{id}/progress`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Assessment ID |

### Request Body

```json
{
  "activity_id": 1,
  "progress_percentage": 75,
  "notes": "Completed 75% of project tasks. On track to finish by deadline."
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `activity_id` | integer | Yes | Assessment activity ID (must exist) |
| `progress_percentage` | number | Yes | Progress percentage (0-100) |
| `notes` | string | No | Progress notes |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Progress report submitted successfully",
  "data": {
    "id": 1,
    "activity_id": 1,
    "progress_percentage": 75,
    "notes": "Completed 75% of project tasks.",
    "status": "pending_approval",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "activity_id": ["The activity id field is required."],
    "progress_percentage": ["The progress percentage must be between 0 and 100."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/assessments/1/progress" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "activity_id": 1,
    "progress_percentage": 75,
    "notes": "Completed 75% of project tasks."
  }'
```

---

### Get Assessment Progress

Get progress reports for an assessment.

**Endpoint**: `GET /api/hr/assessments/{id}/progress`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Assessment ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_id": 5,
    "title": "Q1 Performance Review",
    "status": "pending_hod",
    "activities": [
      {
        "id": 1,
        "name": "Complete project tasks",
        "contribution_percentage": 40,
        "progress_reports": [
          {
            "id": 1,
            "progress_percentage": 50,
            "notes": "Halfway through project tasks",
            "status": "approved",
            "created_at": "2024-01-10T10:30:00Z"
          },
          {
            "id": 2,
            "progress_percentage": 75,
            "notes": "Completed 75% of project tasks",
            "status": "pending_approval",
            "created_at": "2024-01-15T10:30:00Z"
          }
        ]
      }
    ]
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/assessments/1/progress" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Recruitment

### Get Job Postings

Get all active job postings.

**Endpoint**: `GET /api/hr/jobs`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Senior Software Developer",
      "description": "We are looking for an experienced software developer...",
      "department": "IT",
      "location": "Dar es Salaam",
      "employment_type": "Full-time",
      "salary_range": "TZS 2,000,000 - 3,000,000",
      "requirements": "Bachelor's degree in Computer Science...",
      "status": "active",
      "application_deadline": "2024-03-01",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/jobs" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Job Details

Get detailed information about a specific job posting.

**Endpoint**: `GET /api/hr/jobs/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Job ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Senior Software Developer",
    "description": "We are looking for an experienced software developer...",
    "department": "IT",
    "location": "Dar es Salaam",
    "employment_type": "Full-time",
    "salary_range": "TZS 2,000,000 - 3,000,000",
    "requirements": "Bachelor's degree in Computer Science...",
    "responsibilities": "Develop and maintain web applications...",
    "status": "active",
    "application_deadline": "2024-03-01",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/jobs/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Job Applications

Get all applications for a specific job (HR only).

**Endpoint**: `GET /api/hr/jobs/{id}/applications`  
**Authentication**: Required  
**Access**: System Admin, HR Officer only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Job ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "job_id": 1,
      "applicant_id": 5,
      "applicant": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "cover_letter": "I am writing to apply for the position...",
      "resume": "job_applications/1234567890_resume.pdf",
      "status": "pending",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/jobs/1/applications" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Apply for Job

Submit an application for a job posting.

**Endpoint**: `POST /api/hr/jobs/{id}/apply`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Job ID |

### Request Body (Form Data)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `cover_letter` | string | Yes | Cover letter text |
| `resume` | file | Yes | Resume document (pdf, doc, docx, max 5MB) |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Application submitted successfully",
  "data": {
    "id": 1,
    "job_id": 1,
    "applicant_id": 5,
    "cover_letter": "I am writing to apply for the position...",
    "resume": "job_applications/1234567890_resume.pdf",
    "status": "pending",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "cover_letter": ["The cover letter field is required."],
    "resume": ["The resume field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/hr/jobs/1/apply" \
  -H "Authorization: Bearer {token}" \
  -F "cover_letter=I am writing to apply for the position..." \
  -F "resume=@/path/to/resume.pdf"
```

---

## Employees

### Get Employees List

Get list of employees (HR/HOD/Admin only).

**Endpoint**: `GET /api/hr/employees`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD only

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **HOD**: Sees employees from their department only
- **HR Officer/System Admin**: Sees all employees

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": "EMP001",
      "user_id": 5,
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "position": "Software Developer",
      "department": "IT",
      "hire_date": "2020-01-01",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 150,
    "per_page": 20,
    "last_page": 8
  }
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/employees?per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Employee Details

Get detailed information about a specific employee.

**Endpoint**: `GET /api/hr/employees/{id}`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Employee ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee_id": "EMP001",
    "user_id": 5,
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone": "+255123456789"
    },
    "position": "Software Developer",
    "department": "IT",
    "hire_date": "2020-01-01",
    "salary": 2500000,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/hr/employees/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Incidents

### Get Incidents

Get all incidents (filtered by user role).

**Endpoint**: `GET /api/incidents`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **Staff**: Only sees their own incidents
- **HR Officer/HOD/System Admin**: Sees all incidents

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reporter_id": 5,
      "reporter": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "title": "Network connectivity issue",
      "description": "Unable to connect to the server",
      "severity": "high",
      "location": "Office Building - Floor 3",
      "status": "open",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 50,
    "per_page": 20,
    "last_page": 3
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/incidents?per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get My Incidents

Get all incidents reported by the authenticated user.

**Endpoint**: `GET /api/incidents/my`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reporter_id": 5,
      "title": "Network connectivity issue",
      "description": "Unable to connect to the server",
      "severity": "high",
      "location": "Office Building - Floor 3",
      "status": "open",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/incidents/my" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Incident Details

Get detailed information about a specific incident.

**Endpoint**: `GET /api/incidents/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Incident ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "reporter_id": 5,
    "reporter": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "title": "Network connectivity issue",
    "description": "Unable to connect to the server",
    "severity": "high",
    "location": "Office Building - Floor 3",
    "status": "open",
    "updates": [
      {
        "id": 1,
        "user_id": 2,
        "update_text": "Investigating the issue",
        "created_at": "2024-01-15T11:00:00Z"
      }
    ],
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/incidents/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Create Incident

Report a new incident.

**Endpoint**: `POST /api/incidents`  
**Authentication**: Required

### Request Body

```json
{
  "title": "Network connectivity issue",
  "description": "Unable to connect to the server. All users on Floor 3 are affected.",
  "severity": "high",
  "location": "Office Building - Floor 3"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `title` | string | Yes | Incident title (max 255 characters) |
| `description` | string | Yes | Detailed description of the incident |
| `severity` | string | Yes | Severity level: low, medium, high, critical |
| `location` | string | No | Location where incident occurred |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Incident reported successfully",
  "data": {
    "id": 1,
    "reporter_id": 5,
    "title": "Network connectivity issue",
    "description": "Unable to connect to the server",
    "severity": "high",
    "location": "Office Building - Floor 3",
    "status": "open",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "description": ["The description field is required."],
    "severity": ["The severity must be one of: low, medium, high, critical."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/incidents" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Network connectivity issue",
    "description": "Unable to connect to the server",
    "severity": "high",
    "location": "Office Building - Floor 3"
  }'
```

---

### Update Incident

Update an existing incident (reporter or managers only).

**Endpoint**: `PUT /api/incidents/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Incident ID |

### Request Body

```json
{
  "status": "in_progress",
  "notes": "IT team is working on the issue"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | Status: open, in_progress, resolved, closed |
| `notes` | string | No | Additional notes (creates an update entry) |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Incident updated successfully"
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### Example Request

```bash
curl -X PUT "http://192.168.100.102:8000/api/incidents/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "in_progress",
    "notes": "IT team is working on the issue"
  }'
```

---

### Add Incident Update

Add an update/comment to an incident.

**Endpoint**: `POST /api/incidents/{id}/update`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Incident ID |

### Request Body

```json
{
  "update_text": "Issue resolved. Network connection restored."
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `update_text` | string | Yes | Update text/comment |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Update added successfully"
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "update_text": ["The update text field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/incidents/1/update" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "update_text": "Issue resolved. Network connection restored."
  }'
```

---

## Status Values

### Permission Request Status

| Status | Description |
|--------|-------------|
| `pending_hr` | Awaiting HR review |
| `pending_hod` | Awaiting HOD approval |
| `pending_hr_final` | Awaiting final HR approval |
| `approved` | Permission approved |
| `rejected` | Permission rejected |
| `return_pending` | Return confirmation pending |

### Sick Sheet Status

| Status | Description |
|--------|-------------|
| `pending_hr` | Awaiting HR review |
| `pending_hod` | Awaiting HOD approval |
| `approved` | Sick sheet approved |
| `rejected` | Sick sheet rejected |

### Assessment Status

| Status | Description |
|--------|-------------|
| `pending_hod` | Awaiting HOD review |
| `in_progress` | Assessment in progress |
| `completed` | Assessment completed |
| `approved` | Assessment approved |

### Incident Status

| Status | Description |
|--------|-------------|
| `open` | Incident reported, not yet addressed |
| `in_progress` | Incident is being worked on |
| `resolved` | Incident has been resolved |
| `closed` | Incident is closed |

### Incident Severity

| Severity | Description |
|----------|-------------|
| `low` | Low priority incident |
| `medium` | Medium priority incident |
| `high` | High priority incident |
| `critical` | Critical incident requiring immediate attention |

### Job Application Status

| Status | Description |
|--------|-------------|
| `pending` | Application pending review |
| `reviewed` | Application under review |
| `shortlisted` | Applicant shortlisted |
| `interviewed` | Interview completed |
| `accepted` | Application accepted |
| `rejected` | Application rejected |

---

## Date Format

All dates should be in **ISO 8601 format**: `YYYY-MM-DD`

Examples:
- `2024-01-15` (January 15, 2024)
- `2024-12-31` (December 31, 2024)

---

## File Upload Limits

### Sick Sheets
- **Maximum file size**: 5MB (5,120 KB)
- **Allowed file types**: pdf, jpg, jpeg, png, doc, docx
- **Storage**: Files are stored in the `public/sick_sheets` directory

### Job Applications
- **Maximum file size**: 5MB (5,120 KB)
- **Allowed file types**: pdf, doc, docx
- **Storage**: Files are stored in the `public/job_applications` directory

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Bad request"
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Authentication Header

All endpoints require the following header:

```
Authorization: Bearer {token}
```

Example:
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## Flutter/Dart Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';

class HrApiService {
  final String baseUrl = 'http://192.168.100.102:8000/api';
  final String? token;

  HrApiService(this.token);

  // Get my permissions
  Future<List<dynamic>> getMyPermissions() async {
    final response = await http.get(
      Uri.parse('$baseUrl/hr/permissions/my'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load permissions');
  }

  // Create permission request
  Future<Map<String, dynamic>> createPermissionRequest({
    required String startDate,
    required String endDate,
    required String reason,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/hr/permissions'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason,
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to create permission request');
  }

  // Submit sick sheet
  Future<Map<String, dynamic>> submitSickSheet({
    required String startDate,
    required String endDate,
    required File medicalDocument,
    String? doctorName,
    String? hospitalName,
  }) async {
    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/hr/sick-sheets'),
    );

    request.headers['Authorization'] = 'Bearer $token';
    request.files.add(
      await http.MultipartFile.fromPath('medical_document', medicalDocument.path),
    );
    request.fields['start_date'] = startDate;
    request.fields['end_date'] = endDate;
    if (doctorName != null) request.fields['doctor_name'] = doctorName;
    if (hospitalName != null) request.fields['hospital_name'] = hospitalName;

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to submit sick sheet');
  }

  // Get my assessments
  Future<List<dynamic>> getMyAssessments() async {
    final response = await http.get(
      Uri.parse('$baseUrl/hr/assessments/my'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load assessments');
  }

  // Create assessment
  Future<Map<String, dynamic>> createAssessment({
    required String title,
    required List<Map<String, dynamic>> activities,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/hr/assessments'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'title': title,
        'activities': activities,
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to create assessment');
  }

  // Get job postings
  Future<List<dynamic>> getJobPostings() async {
    final response = await http.get(
      Uri.parse('$baseUrl/hr/jobs'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load job postings');
  }

  // Apply for job
  Future<Map<String, dynamic>> applyForJob({
    required int jobId,
    required String coverLetter,
    required File resume,
  }) async {
    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/hr/jobs/$jobId/apply'),
    );

    request.headers['Authorization'] = 'Bearer $token';
    request.files.add(
      await http.MultipartFile.fromPath('resume', resume.path),
    );
    request.fields['cover_letter'] = coverLetter;

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to submit job application');
  }

  // Get employees
  Future<List<dynamic>> getEmployees() async {
    final response = await http.get(
      Uri.parse('$baseUrl/hr/employees'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load employees');
  }

  // Report incident
  Future<Map<String, dynamic>> reportIncident({
    required String title,
    required String description,
    required String severity,
    String? location,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/incidents'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'title': title,
        'description': description,
        'severity': severity,
        'location': location,
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to report incident');
  }

  // Get my incidents
  Future<List<dynamic>> getMyIncidents() async {
    final response = await http.get(
      Uri.parse('$baseUrl/incidents/my'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load incidents');
  }
}
```

---

*Last Updated: Based on current HrApiController implementation*

