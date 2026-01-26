# Leave Management API Documentation

## Complete API Reference for Leave Management

**Base URL**: `http://your-domain.com/api`  
**Authentication**: Bearer Token (Laravel Sanctum) - Required for all endpoints except login  
**Content-Type**: `application/json`

---

## Table of Contents

1. [Get Leave Requests](#get-leave-requests)
2. [Get My Leave Requests](#get-my-leave-requests)
3. [Get Pending Leave Requests](#get-pending-leave-requests)
4. [Get Leave Request Details](#get-leave-request-details)
5. [Create Leave Request](#create-leave-request)
6. [Update Leave Request](#update-leave-request)
7. [Cancel Leave Request](#cancel-leave-request)
8. [Approve Leave Request](#approve-leave-request)
9. [Reject Leave Request](#reject-leave-request)
10. [Get Leave Balance](#get-leave-balance)
11. [Get Leave Types](#get-leave-types)

---

## Get Leave Requests

Get all leave requests (filtered by user role).

**Endpoint**: `GET /api/leaves`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | Filter by status (pending, approved, rejected, cancelled) |
| `leave_type_id` | integer | No | Filter by leave type ID |
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **Staff**: Only sees their own leave requests
- **HOD**: Sees leave requests from their department
- **HR Officer/CEO/System Admin**: Sees all leave requests

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "days": 5,
      "reason": "Family vacation",
      "status": "pending",
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
curl -X GET "http://192.168.100.102:8000/api/leaves?status=pending&per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get My Leave Requests

Get all leave requests for the authenticated user.

**Endpoint**: `GET /api/leaves/my`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "days": 5,
      "reason": "Family vacation",
      "status": "pending",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/leaves/my" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get Pending Leave Requests

Get all pending leave requests (for managers only).

**Endpoint**: `GET /api/leaves/pending`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO only

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "days": 5,
      "reason": "Family vacation",
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
curl -X GET "http://192.168.100.102:8000/api/leaves/pending" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get Leave Request Details

Get detailed information about a specific leave request.

**Endpoint**: `GET /api/leaves/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Leave request ID |

### Access Control

- **Staff**: Can only view their own leave requests
- **Managers**: Can view leave requests they have access to

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "employee": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "leave_type": {
      "id": 1,
      "name": "Annual Leave"
    },
    "start_date": "2024-02-01",
    "end_date": "2024-02-05",
    "days": 5,
    "reason": "Family vacation",
    "status": "pending",
    "created_at": "2024-01-15T10:30:00Z",
    "reviewer": {
      "id": 2,
      "name": "Jane Manager"
    },
    "reviewed_at": "2024-01-16T14:20:00Z",
    "dependents": [
      {
        "name": "Jane Doe",
        "relationship": "Spouse",
        "fare_amount": 50000
      }
    ]
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
curl -X GET "http://192.168.100.102:8000/api/leaves/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Create Leave Request

Create a new leave request.

**Endpoint**: `POST /api/leaves`  
**Authentication**: Required

### Request Body

```json
{
  "leave_type_id": 1,
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "reason": "Family vacation",
  "dependents": [
    {
      "name": "Jane Doe",
      "relationship": "Spouse",
      "fare_amount": 50000
    }
  ]
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `leave_type_id` | integer | Yes | Leave type ID (must exist in leave_types table) |
| `start_date` | date | Yes | Start date (YYYY-MM-DD) |
| `end_date` | date | Yes | End date (YYYY-MM-DD, must be >= start_date) |
| `reason` | string | Yes | Reason for leave request |
| `dependents` | array | No | Array of dependent objects |
| `dependents[].name` | string | Yes | Dependent name |
| `dependents[].relationship` | string | Yes | Relationship (e.g., "Spouse", "Child") |
| `dependents[].fare_amount` | number | No | Fare amount for dependent |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Leave request created successfully",
  "data": {
    "id": 1,
    "employee": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "leave_type": {
      "id": 1,
      "name": "Annual Leave"
    },
    "start_date": "2024-02-01",
    "end_date": "2024-02-05",
    "days": 5,
    "reason": "Family vacation",
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
    "leave_type_id": ["The leave type id field is required."],
    "start_date": ["The start date field is required."],
    "end_date": ["The end date must be a date after or equal to start date."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/leaves" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "leave_type_id": 1,
    "start_date": "2024-02-01",
    "end_date": "2024-02-05",
    "reason": "Family vacation",
    "dependents": [
      {
        "name": "Jane Doe",
        "relationship": "Spouse",
        "fare_amount": 50000
      }
    ]
  }'
```

---

## Update Leave Request

Update an existing leave request (only if status is pending or pending_hr_review).

**Endpoint**: `PUT /api/leaves/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Leave request ID |

### Request Body

```json
{
  "start_date": "2024-02-02",
  "end_date": "2024-02-06",
  "reason": "Updated reason for leave"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `start_date` | date | No | Updated start date |
| `end_date` | date | No | Updated end date (must be >= start_date) |
| `reason` | string | No | Updated reason |

**Note**: All parameters are optional. Only include fields you want to update.

### Response (200 OK)

```json
{
  "success": true,
  "message": "Leave request updated successfully",
  "data": {
    "id": 1,
    "employee": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "leave_type": {
      "id": 1,
      "name": "Annual Leave"
    },
    "start_date": "2024-02-02",
    "end_date": "2024-02-06",
    "days": 5,
    "reason": "Updated reason for leave",
    "status": "pending",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Responses

**403 Forbidden** (Not the owner):
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**422 Unprocessable** (Cannot update in current status):
```json
{
  "success": false,
  "message": "Cannot update leave request in current status"
}
```

### Example Request

```bash
curl -X PUT "http://192.168.100.102:8000/api/leaves/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2024-02-02",
    "end_date": "2024-02-06",
    "reason": "Updated reason"
  }'
```

---

## Cancel Leave Request

Cancel a leave request (only if status is pending, pending_hr_review, or pending_hod_approval).

**Endpoint**: `POST /api/leaves/{id}/cancel`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Leave request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Leave request cancelled successfully"
}
```

### Error Responses

**403 Forbidden** (Not the owner):
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**422 Unprocessable** (Cannot cancel in current status):
```json
{
  "success": false,
  "message": "Cannot cancel leave request in current status"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/leaves/1/cancel" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Approve Leave Request

Approve a leave request (managers only).

**Endpoint**: `POST /api/leaves/{id}/approve`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Leave request ID |

### Request Body (Optional)

```json
{
  "comments": "Approved for family vacation"
}
```

### Response (200 OK)

```json
{
  "success": true,
  "message": "Leave request approved successfully"
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
curl -X POST "http://192.168.100.102:8000/api/leaves/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "comments": "Approved"
  }'
```

---

## Reject Leave Request

Reject a leave request (managers only).

**Endpoint**: `POST /api/leaves/{id}/reject`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Leave request ID |

### Request Body

```json
{
  "rejection_reason": "Insufficient leave balance available"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `rejection_reason` | string | Yes | Reason for rejection |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Leave request rejected"
}
```

### Error Responses

**403 Forbidden**:
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rejection_reason": ["The rejection reason field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/leaves/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "rejection_reason": "Insufficient leave balance"
  }'
```

---

## Get Leave Balance

Get leave balance for the authenticated user.

**Endpoint**: `GET /api/leaves/balance`  
**Authentication**: Required

### Response (200 OK)

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
      "remaining_days": 16
    },
    {
      "leave_type": {
        "id": 2,
        "name": "Sick Leave"
      },
      "total_days": 7,
      "used_days": 2,
      "remaining_days": 5
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/leaves/balance" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get Leave Types

Get all active leave types available in the system.

**Endpoint**: `GET /api/leaves/types`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Annual Leave",
      "description": "Annual vacation leave",
      "max_days": 21
    },
    {
      "id": 2,
      "name": "Sick Leave",
      "description": "Medical leave",
      "max_days": 7
    },
    {
      "id": 3,
      "name": "Maternity Leave",
      "description": "Maternity leave",
      "max_days": 90
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/leaves/types" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Leave Status Values

The following status values are used for leave requests:

| Status | Description |
|--------|-------------|
| `pending` | Initial status when created |
| `pending_hr_review` | Awaiting HR review |
| `pending_hod_approval` | Awaiting HOD approval |
| `pending_ceo_approval` | Awaiting CEO approval |
| `approved` | Leave request approved |
| `rejected` | Leave request rejected |
| `cancelled` | Leave request cancelled by employee |

---

## Date Format

All dates should be in **ISO 8601 format**: `YYYY-MM-DD`

Examples:
- `2024-02-01` (February 1, 2024)
- `2024-12-25` (December 25, 2024)

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

class LeaveApiService {
  final String baseUrl = 'http://192.168.100.102:8000/api';
  final String? token;

  LeaveApiService(this.token);

  // Get leave types
  Future<List<dynamic>> getLeaveTypes() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leaves/types'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load leave types');
  }

  // Get my leaves
  Future<List<dynamic>> getMyLeaves() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leaves/my'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load leaves');
  }

  // Create leave request
  Future<Map<String, dynamic>> createLeaveRequest({
    required int leaveTypeId,
    required String startDate,
    required String endDate,
    required String reason,
    List<Map<String, dynamic>>? dependents,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/leaves'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'leave_type_id': leaveTypeId,
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason,
        if (dependents != null) 'dependents': dependents,
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to create leave request');
  }

  // Get leave balance
  Future<List<dynamic>> getLeaveBalance() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leaves/balance'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load leave balance');
  }
}
```

---

*Last Updated: Based on current LeaveApiController implementation*

