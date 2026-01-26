# Android App API Documentation

## Complete API Reference for EMCA OfficeLink Mobile Application

**Base URL**: `https://your-domain.com/api/mobile/v1`  
**Authentication**: Bearer Token (Laravel Sanctum)  
**Content-Type**: `application/json`

---

## Table of Contents

1. [Authentication](#authentication)
2. [Dashboard](#dashboard)
3. [Profile](#profile)
4. [Users](#users)
5. [Departments](#departments)
6. [Attendance](#attendance)
7. [Leave Management](#leave-management)
8. [Task Management](#task-management)
9. [File Management](#file-management)
10. [Finance](#finance)
11. [HR Management](#hr-management)
12. [Notifications](#notifications)
13. [Incidents](#incidents)
14. [Device Management](#device-management)

---

## Authentication

### Login (Email/Password)

**Endpoint**: `POST /auth/login`  
**Authentication**: Not required

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "Samsung Galaxy S21" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "phone": "+255123456789",
      "employee_id": "EMP001",
      "photo": "https://domain.com/storage/photos/photo.jpg",
      "primary_department": {
        "id": 1,
        "name": "IT Department"
      },
      "roles": [
        {
          "id": 1,
          "name": "Staff",
          "display_name": "Staff"
        }
      ],
      "permissions": []
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

**Error Responses**:
- `401` - Invalid credentials
- `403` - Account inactive
- `422` - Validation failed

---

### Login with OTP (Request OTP)

**Endpoint**: `POST /auth/login-otp`  
**Authentication**: Not required

**Request Body**:
```json
{
  "email": "user@example.com"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "OTP has been sent to your registered phone number.",
  "data": {
    "user_id": 1,
    "email": "user@example.com"
  }
}
```

---

### Verify OTP

**Endpoint**: `POST /auth/verify-otp`  
**Authentication**: Not required

**Request Body**:
```json
{
  "email": "user@example.com",
  "otp": "123456",
  "device_name": "Samsung Galaxy S21" // Optional
}
```

**Response**: Same as Login response

---

### Resend OTP

**Endpoint**: `POST /auth/resend-otp`  
**Authentication**: Not required

**Request Body**:
```json
{
  "email": "user@example.com"
}
```

---

### Forgot Password

**Endpoint**: `POST /auth/forgot-password`  
**Authentication**: Not required

**Request Body**:
```json
{
  "email": "user@example.com"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Password reset OTP has been sent to your registered phone number.",
  "data": {
    "user_id": 1,
    "email": "user@example.com"
  }
}
```

---

### Reset Password

**Endpoint**: `POST /auth/reset-password`  
**Authentication**: Not required

**Request Body**:
```json
{
  "email": "user@example.com",
  "otp": "123456",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

---

### Get Current User

**Endpoint**: `GET /auth/me`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+255123456789",
    "employee_id": "EMP001",
    "photo": "https://domain.com/storage/photos/photo.jpg",
    "primary_department": {
      "id": 1,
      "name": "IT Department"
    },
    "roles": [...],
    "permissions": [...]
  }
}
```

---

### Logout

**Endpoint**: `POST /auth/logout`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Refresh Token

**Endpoint**: `POST /auth/refresh`  
**Authentication**: Required

**Request Body**:
```json
{
  "device_name": "Samsung Galaxy S21" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

---

### Change Password

**Endpoint**: `PUT /auth/change-password`  
**Authentication**: Required

**Request Body**:
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

## Dashboard

### Get Dashboard Data

**Endpoint**: `GET /dashboard`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "type": "staff", // admin, ceo, hod, accountant, hr, staff
  "data": {
    "my_leave_requests": 5,
    "pending_leave_requests": 2,
    "my_tasks": 10,
    "pending_tasks": 3
  }
}
```

**Admin Dashboard**:
```json
{
  "success": true,
  "type": "admin",
  "data": {
    "total_users": 150,
    "active_users": 145,
    "total_departments": 10,
    "pending_leave_requests": 12,
    "pending_file_requests": 5,
    "pending_permissions": 8,
    "pending_sick_sheets": 3,
    "total_incidents": 25
  }
}
```

---

### Get Dashboard Statistics

**Endpoint**: `GET /dashboard/stats`  
**Authentication**: Required

**Response**: Same structure as dashboard data

---

### Get Dashboard Notifications

**Endpoint**: `GET /dashboard/notifications`  
**Authentication**: Required

**Query Parameters**:
- `limit` (optional, default: 20)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "message": "Your leave request has been approved",
      "link": "/leaves/123",
      "is_read": false,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "unread_count": 5
}
```

---

## Profile

### Get Profile

**Endpoint**: `GET /profile`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+255123456789",
    "mobile": "+255123456789",
    "employee_id": "EMP001",
    "photo": "https://domain.com/storage/photos/photo.jpg",
    "date_of_birth": "1990-01-15",
    "gender": "Male",
    "marital_status": "Single",
    "nationality": "Tanzanian",
    "address": "123 Main St, Dar es Salaam",
    "hire_date": "2020-01-01",
    "primary_department": {
      "id": 1,
      "name": "IT Department"
    },
    "roles": [...]
  }
}
```

---

### Update Profile

**Endpoint**: `PUT /profile`  
**Authentication**: Required

**Request Body**:
```json
{
  "name": "John Doe",
  "phone": "+255123456789",
  "mobile": "+255123456789",
  "date_of_birth": "1990-01-15",
  "gender": "Male",
  "marital_status": "Single",
  "nationality": "Tanzanian",
  "address": "123 Main St, Dar es Salaam"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": { /* Updated profile */ }
}
```

---

### Update Profile Photo

**Endpoint**: `POST /profile/photo`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

**Request Body** (Form Data):
- `photo` (file) - Image file (jpg, png, max 2MB)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Photo updated successfully",
  "data": {
    "photo": "https://domain.com/storage/photos/photo.jpg"
  }
}
```

---

## Users

### Get Users List

**Endpoint**: `GET /users`  
**Authentication**: Required

**Query Parameters**:
- `registered` (optional) - Filter by device registration status (true/false)
- `is_active` (optional) - Filter by active status (true/false)
- `department_id` (optional) - Filter by department

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "employee_id": "EMP001",
      "enroll_id": "001",
      "registered_on_device": true,
      "device_registered_at": "2024-01-15 10:30:00",
      "department": "IT Department"
    }
  ]
}
```

---

### Get User Details

**Endpoint**: `GET /users/{id}`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "employee_id": "EMP001",
    "department": "IT Department",
    // ... full user details
  }
}
```

---

### Search Users

**Endpoint**: `GET /users/search`  
**Authentication**: Required

**Query Parameters**:
- `q` (required) - Search query

**Response**: Same as users list

---

## Departments

### Get Departments List

**Endpoint**: `GET /departments`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "IT Department",
      "description": "Information Technology",
      "is_active": true
    }
  ]
}
```

---

### Get Department Details

**Endpoint**: `GET /departments/{id}`  
**Authentication**: Required

**Response**: Department object with full details

---

### Get Department Members

**Endpoint**: `GET /departments/{id}/members`  
**Authentication**: Required

**Response**: Array of users in the department

---

## Attendance

### Get Attendance Records

**Endpoint**: `GET /attendance`  
**Authentication**: Required

**Query Parameters**:
- `date` (optional) - Filter by date (YYYY-MM-DD)
- `employee_id` (optional) - Filter by employee
- `per_page` (optional, default: 20)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "John Doe",
      "date": "2024-01-15",
      "check_in": "08:00:00",
      "check_out": "17:00:00",
      "status": "present",
      "hours_worked": 9.0
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 100,
    "per_page": 20,
    "last_page": 5
  }
}
```

---

### Get My Attendance

**Endpoint**: `GET /attendance/my`  
**Authentication**: Required

**Query Parameters**:
- `start_date` (optional)
- `end_date` (optional)

**Response**: Array of attendance records for current user

---

### Get Attendance Details

**Endpoint**: `GET /attendance/{id}`  
**Authentication**: Required

**Response**: Single attendance record with full details

---

### Get Daily Attendance

**Endpoint**: `GET /attendance/daily/{date}`  
**Authentication**: Required

**URL Parameter**: `date` (YYYY-MM-DD)

**Response**: Attendance record for specific date

---

### Get Attendance Summary

**Endpoint**: `GET /attendance/summary`  
**Authentication**: Required

**Query Parameters**:
- `month` (optional) - Month (1-12)
- `year` (optional) - Year

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "total_days": 22,
    "present_days": 20,
    "absent_days": 2,
    "late_days": 1,
    "hours_worked": 180.0,
    "average_hours": 9.0
  }
}
```

---

### Check In

**Endpoint**: `POST /attendance/check-in`  
**Authentication**: Required

**Request Body**:
```json
{
  "latitude": -6.7924,
  "longitude": 39.2083,
  "location": "Office Building" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Checked in successfully",
  "data": {
    "id": 1,
    "check_in": "08:00:00",
    "date": "2024-01-15"
  }
}
```

---

### Check Out

**Endpoint**: `POST /attendance/check-out`  
**Authentication**: Required

**Request Body**:
```json
{
  "latitude": -6.7924,
  "longitude": 39.2083,
  "location": "Office Building" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Checked out successfully",
  "data": {
    "id": 1,
    "check_in": "08:00:00",
    "check_out": "17:00:00",
    "hours_worked": 9.0
  }
}
```

---

## Leave Management

### Get Leave Requests

**Endpoint**: `GET /leaves`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `leave_type_id` (optional) - Filter by leave type
- `per_page` (optional, default: 20)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee_id": 1,
      "employee_name": "John Doe",
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "days": 5,
      "status": "pending",
      "reason": "Family vacation",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": { /* pagination info */ }
}
```

---

### Get My Leave Requests

**Endpoint**: `GET /leaves/my`  
**Authentication**: Required

**Response**: Array of leave requests for current user

---

### Get Pending Leave Requests

**Endpoint**: `GET /leaves/pending`  
**Authentication**: Required (Managers only)

**Response**: Array of pending leave requests

---

### Get Leave Request Details

**Endpoint**: `GET /leaves/{id}`  
**Authentication**: Required

**Response**: Single leave request with full details

---

### Create Leave Request

**Endpoint**: `POST /leaves`  
**Authentication**: Required

**Request Body**:
```json
{
  "leave_type_id": 1,
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "reason": "Family vacation",
  "emergency_contact": "+255123456789", // Optional
  "dependents": [ // Optional
    {
      "name": "Jane Doe",
      "relationship": "Spouse"
    }
  ]
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Leave request created successfully",
  "data": { /* leave request object */ }
}
```

---

### Update Leave Request

**Endpoint**: `PUT /leaves/{id}`  
**Authentication**: Required

**Request Body**: Same as create (only if status is pending or rejected)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Leave request updated successfully"
}
```

---

### Cancel Leave Request

**Endpoint**: `POST /leaves/{id}/cancel`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Leave request cancelled successfully"
}
```

---

### Approve Leave Request

**Endpoint**: `POST /leaves/{id}/approve`  
**Authentication**: Required (Managers only)

**Request Body**:
```json
{
  "comments": "Approved" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Leave request approved successfully"
}
```

---

### Reject Leave Request

**Endpoint**: `POST /leaves/{id}/reject`  
**Authentication**: Required (Managers only)

**Request Body**:
```json
{
  "comments": "Insufficient leave balance" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Leave request rejected successfully"
}
```

---

### Get Leave Balance

**Endpoint**: `GET /leaves/balance`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "total_days": 21,
      "used_days": 5,
      "remaining_days": 16,
      "pending_days": 5
    }
  ]
}
```

---

### Get Leave Types

**Endpoint**: `GET /leaves/types`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Annual Leave",
      "max_days": 21,
      "is_paid": true
    }
  ]
}
```

---

## Task Management

### Get Tasks

**Endpoint**: `GET /tasks`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `priority` (optional) - Filter by priority
- `per_page` (optional, default: 20)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Complete project documentation",
      "description": "Write comprehensive documentation",
      "status": "in_progress",
      "priority": "high",
      "due_date": "2024-02-01",
      "created_by": {
        "id": 1,
        "name": "John Doe"
      },
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": { /* pagination info */ }
}
```

---

### Get My Tasks

**Endpoint**: `GET /tasks/my`  
**Authentication**: Required

**Response**: Array of tasks assigned to current user

---

### Get Assigned Tasks

**Endpoint**: `GET /tasks/assigned`  
**Authentication**: Required

**Response**: Array of tasks created by current user

---

### Get Task Details

**Endpoint**: `GET /tasks/{id}`  
**Authentication**: Required

**Response**: Single task with full details including activities

---

### Create Task

**Endpoint**: `POST /tasks`  
**Authentication**: Required

**Request Body**:
```json
{
  "title": "Complete project documentation",
  "description": "Write comprehensive documentation",
  "priority": "high",
  "due_date": "2024-02-01",
  "activities": [
    {
      "title": "Research phase",
      "description": "Gather requirements",
      "assigned_users": [1, 2]
    }
  ]
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Task created successfully",
  "data": { /* task object */ }
}
```

---

### Update Task

**Endpoint**: `PUT /tasks/{id}`  
**Authentication**: Required

**Request Body**: Same as create

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task updated successfully"
}
```

---

### Complete Task

**Endpoint**: `POST /tasks/{id}/complete`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task completed successfully"
}
```

---

### Assign Task

**Endpoint**: `POST /tasks/{id}/assign`  
**Authentication**: Required

**Request Body**:
```json
{
  "user_ids": [1, 2, 3]
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Task assigned successfully"
}
```

---

### Get Task Activities

**Endpoint**: `GET /tasks/{id}/activities`  
**Authentication**: Required

**Response**: Array of task activities

---

### Add Task Activity

**Endpoint**: `POST /tasks/{id}/activities`  
**Authentication**: Required

**Request Body**:
```json
{
  "title": "Research phase",
  "description": "Gather requirements",
  "assigned_users": [1, 2]
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Activity added successfully",
  "data": { /* activity object */ }
}
```

---

### Complete Activity

**Endpoint**: `POST /tasks/{id}/activities/{activityId}/complete`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Activity completed successfully"
}
```

---

### Submit Activity Report

**Endpoint**: `POST /tasks/{id}/activities/{activityId}/report`  
**Authentication**: Required

**Request Body**:
```json
{
  "report": "Completed research phase. Findings documented.",
  "attachments": [] // Optional file attachments
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Report submitted successfully"
}
```

---

## File Management

### Digital Files

#### Get Digital Files

**Endpoint**: `GET /files/digital`  
**Authentication**: Required

**Query Parameters**:
- `folder_id` (optional) - Filter by folder
- `search` (optional) - Search query
- `per_page` (optional, default: 20)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "document.pdf",
      "type": "file",
      "size": 1024000,
      "mime_type": "application/pdf",
      "created_at": "2024-01-15T10:30:00Z",
      "has_access": true
    }
  ]
}
```

---

#### Get Digital Folders

**Endpoint**: `GET /files/digital/folders`  
**Authentication**: Required

**Response**: Array of folder objects

---

#### Get Folder Contents

**Endpoint**: `GET /files/digital/folders/{id}`  
**Authentication**: Required

**Response**: Array of files and subfolders in the folder

---

#### Get Digital File Details

**Endpoint**: `GET /files/digital/{id}`  
**Authentication**: Required

**Response**: Single file object with full details

---

#### Upload Digital File

**Endpoint**: `POST /files/digital/upload`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

**Request Body** (Form Data):
- `file` (file) - File to upload
- `folder_id` (optional) - Target folder ID
- `name` (optional) - Custom file name
- `description` (optional) - File description

**Response** (201 Created):
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": { /* file object */ }
}
```

---

#### Download Digital File

**Endpoint**: `GET /files/digital/{id}/download`  
**Authentication**: Required

**Response**: File download (binary)

---

#### Request File Access

**Endpoint**: `POST /files/digital/{id}/request-access`  
**Authentication**: Required

**Request Body**:
```json
{
  "reason": "Need access for project work" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Access request submitted successfully"
}
```

---

#### Get My Access Requests

**Endpoint**: `GET /files/digital/my-requests`  
**Authentication**: Required

**Response**: Array of access requests made by current user

---

#### Get Pending Access Requests

**Endpoint**: `GET /files/digital/pending-requests`  
**Authentication**: Required (File managers only)

**Response**: Array of pending access requests

---

#### Approve Access Request

**Endpoint**: `POST /files/digital/requests/{id}/approve`  
**Authentication**: Required (File managers only)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Access request approved"
}
```

---

#### Reject Access Request

**Endpoint**: `POST /files/digital/requests/{id}/reject`  
**Authentication**: Required (File managers only)

**Request Body**:
```json
{
  "reason": "Access denied" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Access request rejected"
}
```

---

#### Search Digital Files

**Endpoint**: `GET /files/digital/search`  
**Authentication**: Required

**Query Parameters**:
- `q` (required) - Search query

**Response**: Array of matching files

---

### Physical Racks

#### Get Physical Files

**Endpoint**: `GET /files/physical`  
**Authentication**: Required

**Response**: Array of physical file categories/racks

---

#### Get Physical Categories

**Endpoint**: `GET /files/physical/categories`  
**Authentication**: Required

**Response**: Array of physical file categories

---

#### Get Rack Contents

**Endpoint**: `GET /files/physical/racks/{id}`  
**Authentication**: Required

**Response**: Array of files in the rack

---

#### Get Physical File Details

**Endpoint**: `GET /files/physical/{id}`  
**Authentication**: Required

**Response**: Single physical file object

---

#### Request Physical File

**Endpoint**: `POST /files/physical/{id}/request`  
**Authentication**: Required

**Request Body**:
```json
{
  "reason": "Need for reference",
  "expected_return_date": "2024-02-15" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "File request submitted successfully"
}
```

---

#### Get My Physical Requests

**Endpoint**: `GET /files/physical/my-requests`  
**Authentication**: Required

**Response**: Array of physical file requests made by current user

---

#### Get Pending Physical Requests

**Endpoint**: `GET /files/physical/pending-requests`  
**Authentication**: Required (File managers only)

**Response**: Array of pending physical file requests

---

#### Approve Physical Request

**Endpoint**: `POST /files/physical/requests/{id}/approve`  
**Authentication**: Required (File managers only)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Request approved"
}
```

---

#### Reject Physical Request

**Endpoint**: `POST /files/physical/requests/{id}/reject`  
**Authentication**: Required (File managers only)

**Request Body**:
```json
{
  "reason": "File not available" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Request rejected"
}
```

---

#### Return Physical File

**Endpoint**: `POST /files/physical/requests/{id}/return`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "File returned successfully"
}
```

---

## Finance

### Petty Cash

#### Get Petty Cash Statistics

**Endpoint**: `GET /finance/petty-cash/stats`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "total": 50,
    "pending": 5,
    "approved": 30,
    "paid": 10,
    "retired": 5,
    "pending_amount": 50000,
    "approved_amount": 300000,
    "paid_amount": 100000
  }
}
```

---

#### Get Petty Cash List

**Endpoint**: `GET /finance/petty-cash`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `per_page` (optional, default: 20)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "voucher_number": "PCV202401150001",
      "amount": 50000,
      "status": "pending_accountant",
      "user": "John Doe",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

#### Get Petty Cash Details

**Endpoint**: `GET /finance/petty-cash/{id}`  
**Authentication**: Required

**Response**: Single petty cash voucher with full details including line items

---

#### Create Petty Cash Request

**Endpoint**: `POST /finance/petty-cash`  
**Authentication**: Required

**Request Body**:
```json
{
  "amount": 50000,
  "purpose": "Office supplies",
  "items": [
    {
      "description": "Stationery",
      "amount": 30000
    },
    {
      "description": "Printer paper",
      "amount": 20000
    }
  ]
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Petty cash voucher created successfully",
  "data": { /* voucher object */ }
}
```

---

#### Update Petty Cash Request

**Endpoint**: `PUT /finance/petty-cash/{id}`  
**Authentication**: Required

**Request Body**:
```json
{
  "amount": 60000,
  "purpose": "Updated purpose"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Voucher updated successfully"
}
```

---

#### Approve Petty Cash

**Endpoint**: `POST /finance/petty-cash/{id}/approve`  
**Authentication**: Required (Accountant/Admin/HOD/CEO)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Voucher approved"
}
```

---

#### Reject Petty Cash

**Endpoint**: `POST /finance/petty-cash/{id}/reject`  
**Authentication**: Required (Accountant/Admin/HOD/CEO)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Voucher rejected"
}
```

---

### Imprest

#### Get Imprest List

**Endpoint**: `GET /finance/imprest`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `per_page` (optional, default: 20)

**Response**: Array of imprest requests

---

#### Get Imprest Details

**Endpoint**: `GET /finance/imprest/{id}`  
**Authentication**: Required

**Response**: Single imprest request with full details

---

#### Create Imprest Request

**Endpoint**: `POST /finance/imprest`  
**Authentication**: Required

**Request Body**:
```json
{
  "amount": 100000,
  "purpose": "Business trip",
  "expected_return_date": "2024-02-15"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Imprest request created successfully",
  "data": { /* imprest object */ }
}
```

---

#### Approve Imprest

**Endpoint**: `POST /finance/imprest/{id}/approve`  
**Authentication**: Required (Managers only)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Imprest approved"
}
```

---

#### Reject Imprest

**Endpoint**: `POST /finance/imprest/{id}/reject`  
**Authentication**: Required (Managers only)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Imprest rejected"
}
```

---

#### Submit Imprest Receipt

**Endpoint**: `POST /finance/imprest/{id}/submit-receipt`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

**Request Body** (Form Data):
- `receipt` (file) - Receipt image/document
- `amount_used` (optional) - Amount used
- `notes` (optional) - Additional notes

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Receipt submitted successfully"
}
```

---

### Payroll

#### Get Payroll List

**Endpoint**: `GET /finance/payroll`  
**Authentication**: Required

**Query Parameters**:
- `month` (optional) - Month (1-12)
- `year` (optional) - Year
- `per_page` (optional, default: 20)

**Response**: Array of payroll records

---

#### Get My Payroll

**Endpoint**: `GET /finance/payroll/my`  
**Authentication**: Required

**Response**: Array of payroll records for current user

---

#### Get Payroll Details

**Endpoint**: `GET /finance/payroll/{id}`  
**Authentication**: Required

**Response**: Single payroll record with full details including breakdown

---

## HR Management

### Permissions

#### Get Permission Requests

**Endpoint**: `GET /hr/permissions`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `per_page` (optional, default: 20)

**Response**: Array of permission requests

---

#### Get My Permissions

**Endpoint**: `GET /hr/permissions/my`  
**Authentication**: Required

**Response**: Array of permission requests for current user

---

#### Get Permission Details

**Endpoint**: `GET /hr/permissions/{id}`  
**Authentication**: Required

**Response**: Single permission request with full details

---

#### Create Permission Request

**Endpoint**: `POST /hr/permissions`  
**Authentication**: Required

**Request Body**:
```json
{
  "start_date": "2024-02-01",
  "end_date": "2024-02-01",
  "start_time": "10:00:00",
  "end_time": "14:00:00",
  "reason": "Personal appointment"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Permission request created successfully",
  "data": { /* permission object */ }
}
```

---

#### Approve Permission

**Endpoint**: `POST /hr/permissions/{id}/approve`  
**Authentication**: Required (HR/HOD/Admin)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Permission approved"
}
```

---

#### Reject Permission

**Endpoint**: `POST /hr/permissions/{id}/reject`  
**Authentication**: Required (HR/HOD/Admin)

**Request Body**:
```json
{
  "reason": "Not approved" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Permission rejected"
}
```

---

#### Confirm Return from Permission

**Endpoint**: `POST /hr/permissions/{id}/confirm-return`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Return confirmed"
}
```

---

### Sick Sheets

#### Get Sick Sheets

**Endpoint**: `GET /hr/sick-sheets`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `per_page` (optional, default: 20)

**Response**: Array of sick sheet requests

---

#### Get My Sick Sheets

**Endpoint**: `GET /hr/sick-sheets/my`  
**Authentication**: Required

**Response**: Array of sick sheet requests for current user

---

#### Get Sick Sheet Details

**Endpoint**: `GET /hr/sick-sheets/{id}`  
**Authentication**: Required

**Response**: Single sick sheet request with full details

---

#### Submit Sick Sheet

**Endpoint**: `POST /hr/sick-sheets`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

**Request Body** (Form Data):
- `sick_sheet` (file) - Sick sheet document/image
- `start_date` (required) - Start date
- `end_date` (required) - End date
- `doctor_name` (optional) - Doctor name
- `hospital_name` (optional) - Hospital name
- `notes` (optional) - Additional notes

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Sick sheet submitted successfully",
  "data": { /* sick sheet object */ }
}
```

---

#### Approve Sick Sheet

**Endpoint**: `POST /hr/sick-sheets/{id}/approve`  
**Authentication**: Required (HR/HOD/Admin)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Sick sheet approved"
}
```

---

#### Reject Sick Sheet

**Endpoint**: `POST /hr/sick-sheets/{id}/reject`  
**Authentication**: Required (HR/HOD/Admin)

**Request Body**:
```json
{
  "reason": "Invalid document" // Optional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Sick sheet rejected"
}
```

---

### Assessments

#### Get Assessments

**Endpoint**: `GET /hr/assessments`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `per_page` (optional, default: 20)

**Response**: Array of assessments

---

#### Get My Assessments

**Endpoint**: `GET /hr/assessments/my`  
**Authentication**: Required

**Response**: Array of assessments for current user

---

#### Get Assessment Details

**Endpoint**: `GET /hr/assessments/{id}`  
**Authentication**: Required

**Response**: Single assessment with full details

---

#### Create Assessment

**Endpoint**: `POST /hr/assessments`  
**Authentication**: Required

**Request Body**:
```json
{
  "title": "Q1 Performance Review",
  "description": "Quarterly performance assessment",
  "due_date": "2024-03-31"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Assessment created successfully",
  "data": { /* assessment object */ }
}
```

---

#### Submit Assessment Progress

**Endpoint**: `POST /hr/assessments/{id}/progress`  
**Authentication**: Required

**Request Body**:
```json
{
  "progress": "Completed 50% of objectives",
  "achievements": ["Objective 1 completed"],
  "challenges": ["Limited resources"]
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Progress submitted successfully"
}
```

---

#### Get Assessment Progress

**Endpoint**: `GET /hr/assessments/{id}/progress`  
**Authentication**: Required

**Response**: Array of progress updates for the assessment

---

### Recruitment

#### Get Job Postings

**Endpoint**: `GET /hr/jobs`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status (open, closed)
- `per_page` (optional, default: 20)

**Response**: Array of job postings

---

#### Get Job Details

**Endpoint**: `GET /hr/jobs/{id}`  
**Authentication**: Required

**Response**: Single job posting with full details

---

#### Get Job Applications

**Endpoint**: `GET /hr/jobs/{id}/applications`  
**Authentication**: Required (HR/HOD/CEO/Admin)

**Response**: Array of applications for the job

---

#### Apply for Job

**Endpoint**: `POST /hr/jobs/{id}/apply`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

**Request Body** (Form Data):
- `cover_letter` (optional) - Cover letter text
- `resume` (file) - Resume document
- `additional_documents` (files) - Additional documents

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Application submitted successfully",
  "data": { /* application object */ }
}
```

---

### Employees

#### Get Employees List

**Endpoint**: `GET /hr/employees`  
**Authentication**: Required

**Query Parameters**:
- `department_id` (optional) - Filter by department
- `search` (optional) - Search query
- `per_page` (optional, default: 20)

**Response**: Array of employee objects

---

#### Get Employee Details

**Endpoint**: `GET /hr/employees/{id}`  
**Authentication**: Required

**Response**: Single employee with full details

---

## Notifications

### Get Notifications

**Endpoint**: `GET /notifications`  
**Authentication**: Required

**Query Parameters**:
- `per_page` (optional, default: 20)
- `unread_only` (optional) - Filter unread only (true/false)

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "message": "Your leave request has been approved",
      "link": "/leaves/123",
      "is_read": false,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": { /* pagination info */ }
}
```

---

### Get Unread Notifications

**Endpoint**: `GET /notifications/unread`  
**Authentication**: Required

**Response**: Array of unread notifications

---

### Get Notification Details

**Endpoint**: `GET /notifications/{id}`  
**Authentication**: Required

**Response**: Single notification object

---

### Mark Notification as Read

**Endpoint**: `POST /notifications/{id}/read`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

---

### Mark All Notifications as Read

**Endpoint**: `POST /notifications/read-all`  
**Authentication**: Required

**Response** (200 OK):
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

---

## Incidents

### Get Incidents

**Endpoint**: `GET /incidents`  
**Authentication**: Required

**Query Parameters**:
- `status` (optional) - Filter by status
- `priority` (optional) - Filter by priority
- `per_page` (optional, default: 20)

**Response**: Array of incidents

---

### Get My Incidents

**Endpoint**: `GET /incidents/my`  
**Authentication**: Required

**Response**: Array of incidents reported by current user

---

### Get Incident Details

**Endpoint**: `GET /incidents/{id}`  
**Authentication**: Required

**Response**: Single incident with full details including updates

---

### Create Incident

**Endpoint**: `POST /incidents`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

**Request Body** (Form Data):
- `title` (required) - Incident title
- `description` (required) - Incident description
- `category` (required) - Incident category
- `priority` (required) - Priority level (low, medium, high, critical)
- `location` (optional) - Location
- `latitude` (optional) - Latitude
- `longitude` (optional) - Longitude
- `photos` (files) - Incident photos
- `attachments` (files) - Additional attachments

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Incident reported successfully",
  "data": { /* incident object */ }
}
```

---

### Update Incident

**Endpoint**: `PUT /incidents/{id}`  
**Authentication**: Required

**Request Body**: Same as create (only if status allows)

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Incident updated successfully"
}
```

---

### Add Incident Update

**Endpoint**: `POST /incidents/{id}/update`  
**Authentication**: Required

**Request Body**:
```json
{
  "update": "Investigation in progress",
  "status": "in_progress", // Optional
  "photos": [] // Optional file attachments
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Update added successfully"
}
```

---

## Device Management

### Register Device Token (FCM)

**Endpoint**: `POST /device/register`  
**Authentication**: Required

**Request Body**:
```json
{
  "token": "fcm_device_token_here",
  "platform": "android", // android or ios
  "device_name": "Samsung Galaxy S21",
  "app_version": "1.0.0"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Device registered successfully"
}
```

---

### Unregister Device Token

**Endpoint**: `DELETE /device/unregister`  
**Authentication**: Required

**Request Body**:
```json
{
  "token": "fcm_device_token_here"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Device unregistered successfully"
}
```

---

### Get Device Tokens

**Endpoint**: `GET /device/tokens`  
**Authentication**: Required

**Response**: Array of registered device tokens for current user

---

### Update Device Token

**Endpoint**: `PUT /device/tokens/{id}`  
**Authentication**: Required

**Request Body**:
```json
{
  "device_name": "Updated Device Name",
  "app_version": "1.0.1"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Device token updated successfully"
}
```

---

## Error Responses

All endpoints may return the following error responses:

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
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
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

All protected endpoints require the following header:

```
Authorization: Bearer {token}
```

Example:
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## Pagination

Endpoints that support pagination return data in the following format:

```json
{
  "success": true,
  "data": [ /* array of items */ ],
  "pagination": {
    "current_page": 1,
    "total": 100,
    "per_page": 20,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

**Query Parameters for Pagination**:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

---

## Date/Time Format

All dates are returned in ISO 8601 format:
- Date: `YYYY-MM-DD` (e.g., `2024-01-15`)
- DateTime: `YYYY-MM-DDTHH:mm:ssZ` (e.g., `2024-01-15T10:30:00Z`)

---

## File Uploads

For file upload endpoints, use `multipart/form-data` content type:

1. **Single File**:
   - Field name: `file`, `photo`, `receipt`, etc. (as specified in endpoint)
   - Max file size: 10MB (may vary by endpoint)
   - Allowed types: Check individual endpoint documentation

2. **Multiple Files**:
   - Use array notation: `photos[]` or `attachments[]`
   - Each file in the array will be processed

---

## Rate Limiting

API requests are rate-limited:
- **Authentication endpoints**: 5 requests per minute
- **Other endpoints**: 60 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Time when limit resets (Unix timestamp)

---

## WebSocket/Real-time Updates

For real-time notifications and updates, connect to:
- **WebSocket URL**: `wss://your-domain.com/ws`
- **Authentication**: Send token in connection header or query parameter

---

*Last Updated: Based on current API implementation*

