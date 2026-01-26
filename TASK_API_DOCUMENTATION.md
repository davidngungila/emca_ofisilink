# Task Management API Documentation

## Complete API Reference for Task Management

**Base URL**: `http://your-domain.com/api`  
**Authentication**: Bearer Token (Laravel Sanctum) - Required for all endpoints  
**Content-Type**: `application/json`

---

## Table of Contents

1. [Get Tasks](#get-tasks)
2. [Get My Tasks](#get-my-tasks)
3. [Get Assigned Tasks](#get-assigned-tasks)
4. [Get Task Details](#get-task-details)
5. [Create Task](#create-task)
6. [Update Task](#update-task)
7. [Complete Task](#complete-task)
8. [Assign Users to Task](#assign-users-to-task)
9. [Get Task Activities](#get-task-activities)
10. [Create Activity](#create-activity)
11. [Complete Activity](#complete-activity)
12. [Submit Activity Report](#submit-activity-report)

---

## Get Tasks

Get all tasks (filtered by user role).

**Endpoint**: `GET /api/tasks`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | Filter by status (Not Started, In Progress, On Hold, Completed, Cancelled) |
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Access Control

- **Staff**: Only sees tasks where they are team leader or assigned to activities
- **Managers** (System Admin, CEO, HOD, Manager, Director): See all tasks

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Complete project documentation",
      "description": "Write comprehensive documentation for the project",
      "status": "In Progress",
      "priority": "High",
      "start_date": "2024-01-01",
      "end_date": "2024-02-01",
      "team_leader": {
        "id": 5,
        "name": "John Doe"
      },
      "activities_count": 3,
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
curl -X GET "http://192.168.100.102:8000/api/tasks?status=In Progress&per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get My Tasks

Get all tasks where the authenticated user is the team leader.

**Endpoint**: `GET /api/tasks/my`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Complete project documentation",
      "description": "Write comprehensive documentation",
      "status": "In Progress",
      "priority": "High",
      "start_date": "2024-01-01",
      "end_date": "2024-02-01",
      "team_leader": {
        "id": 5,
        "name": "John Doe"
      },
      "activities_count": 3,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/tasks/my" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get Assigned Tasks

Get all tasks where the authenticated user is assigned to activities.

**Endpoint**: `GET /api/tasks/assigned`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Complete project documentation",
      "description": "Write comprehensive documentation",
      "status": "In Progress",
      "priority": "High",
      "team_leader": {
        "id": 5,
        "name": "John Doe"
      },
      "activities_count": 3,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/tasks/assigned" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Get Task Details

Get detailed information about a specific task.

**Endpoint**: `GET /api/tasks/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |

### Access Control

- **Team Leader**: Can view their own tasks
- **Assigned Users**: Can view tasks they're assigned to
- **Managers**: Can view all tasks

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Complete project documentation",
    "description": "Write comprehensive documentation for the project",
    "status": "In Progress",
    "priority": "High",
    "start_date": "2024-01-01",
    "end_date": "2024-02-01",
    "team_leader": {
      "id": 5,
      "name": "John Doe"
    },
    "activities_count": 3,
    "created_at": "2024-01-15T10:30:00Z",
    "activities": [
      {
        "id": 1,
        "name": "Research phase",
        "status": "Completed",
        "priority": "High"
      },
      {
        "id": 2,
        "name": "Writing phase",
        "status": "In Progress",
        "priority": "Normal"
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
curl -X GET "http://192.168.100.102:8000/api/tasks/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Create Task

Create a new task (HOD, CEO, HR Officer, System Admin only).

**Endpoint**: `POST /api/tasks`  
**Authentication**: Required  
**Access**: System Admin, CEO, HOD, HR Officer only

### Request Body

```json
{
  "name": "Complete project documentation",
  "description": "Write comprehensive documentation for the project",
  "start_date": "2024-01-01",
  "end_date": "2024-02-01",
  "team_leader_id": 5,
  "priority": "High",
  "status": "Not Started"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Task name (max 255 characters) |
| `description` | string | No | Task description |
| `start_date` | date | No | Start date (YYYY-MM-DD) |
| `end_date` | date | No | End date (YYYY-MM-DD, must be >= start_date) |
| `team_leader_id` | integer | Yes | User ID of team leader (must exist) |
| `priority` | string | No | Priority: Low, Normal, High, Urgent (default: Normal) |
| `status` | string | No | Status: Not Started, In Progress, On Hold, Completed, Cancelled (default: Not Started) |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Task created successfully",
  "data": {
    "id": 1,
    "name": "Complete project documentation",
    "description": "Write comprehensive documentation",
    "status": "Not Started",
    "priority": "High",
    "start_date": "2024-01-01",
    "end_date": "2024-02-01",
    "team_leader": {
      "id": 5,
      "name": "John Doe"
    },
    "activities_count": 0,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Error Responses

**403 Forbidden** (Not authorized to create):
```json
{
  "success": false,
  "message": "Unauthorized: Only HOD, CEO, or HR Officer can create tasks"
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "team_leader_id": ["The team leader id field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/tasks" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Complete project documentation",
    "description": "Write comprehensive documentation",
    "start_date": "2024-01-01",
    "end_date": "2024-02-01",
    "team_leader_id": 5,
    "priority": "High"
  }'
```

---

## Update Task

Update an existing task (team leader or managers only).

**Endpoint**: `PUT /api/tasks/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |

### Request Body

```json
{
  "name": "Updated task name",
  "description": "Updated description",
  "start_date": "2024-01-02",
  "end_date": "2024-02-02",
  "priority": "Urgent",
  "status": "In Progress"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | No | Updated task name |
| `description` | string | No | Updated description |
| `start_date` | date | No | Updated start date |
| `end_date` | date | No | Updated end date (must be >= start_date) |
| `priority` | string | No | Updated priority |
| `status` | string | No | Updated status |

**Note**: All parameters are optional. Only include fields you want to update.

### Response (200 OK)

```json
{
  "success": true,
  "message": "Task updated successfully",
  "data": {
    "id": 1,
    "name": "Updated task name",
    "description": "Updated description",
    "status": "In Progress",
    "priority": "Urgent",
    "start_date": "2024-01-02",
    "end_date": "2024-02-02",
    "team_leader": {
      "id": 5,
      "name": "John Doe"
    },
    "activities_count": 3,
    "created_at": "2024-01-15T10:30:00Z"
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
curl -X PUT "http://192.168.100.102:8000/api/tasks/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "In Progress",
    "priority": "Urgent"
  }'
```

---

## Complete Task

Mark a task as completed (team leader or managers only).

**Endpoint**: `POST /api/tasks/{id}/complete`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Task marked as completed"
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
curl -X POST "http://192.168.100.102:8000/api/tasks/1/complete" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Assign Users to Task

Assign users to a task activity.

**Endpoint**: `POST /api/tasks/{id}/assign`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |

### Request Body

```json
{
  "activity_id": 1,
  "user_ids": [2, 3, 4]
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `activity_id` | integer | Yes | Activity ID (must exist) |
| `user_ids` | array | Yes | Array of user IDs to assign (must exist) |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Users assigned successfully"
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "activity_id": ["The activity id field is required."],
    "user_ids": ["The user ids field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/tasks/1/assign" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "activity_id": 1,
    "user_ids": [2, 3, 4]
  }'
```

---

## Get Task Activities

Get all activities for a specific task.

**Endpoint**: `GET /api/tasks/{id}/activities`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Research phase",
      "status": "Completed",
      "priority": "High",
      "start_date": "2024-01-01",
      "end_date": "2024-01-15",
      "assigned_users": [
        {
          "id": 2,
          "name": "Jane Smith"
        },
        {
          "id": 3,
          "name": "Bob Johnson"
        }
      ]
    },
    {
      "id": 2,
      "name": "Writing phase",
      "status": "In Progress",
      "priority": "Normal",
      "start_date": "2024-01-16",
      "end_date": "2024-02-01",
      "assigned_users": [
        {
          "id": 4,
          "name": "Alice Brown"
        }
      ]
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/tasks/1/activities" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Create Activity

Create a new activity for a task.

**Endpoint**: `POST /api/tasks/{id}/activities`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |

### Request Body

```json
{
  "name": "Research phase",
  "start_date": "2024-01-01",
  "end_date": "2024-01-15",
  "priority": "High"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Activity name (max 255 characters) |
| `start_date` | date | No | Start date (YYYY-MM-DD) |
| `end_date` | date | No | End date (YYYY-MM-DD, must be >= start_date) |
| `priority` | string | No | Priority: Low, Normal, High, Urgent (default: Normal) |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Activity created successfully",
  "data": {
    "id": 1,
    "main_task_id": 1,
    "name": "Research phase",
    "start_date": "2024-01-01",
    "end_date": "2024-01-15",
    "priority": "High",
    "status": "Not Started",
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
    "name": ["The name field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/tasks/1/activities" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Research phase",
    "start_date": "2024-01-01",
    "end_date": "2024-01-15",
    "priority": "High"
  }'
```

---

## Complete Activity

Mark an activity as completed.

**Endpoint**: `POST /api/tasks/{id}/activities/{activityId}/complete`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |
| `activityId` | integer | Yes | Activity ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Activity marked as completed"
}
```

### Error Response (422 Unprocessable)

```json
{
  "success": false,
  "message": "Activity does not belong to this task"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/tasks/1/activities/1/complete" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Submit Activity Report

Submit a report for an activity.

**Endpoint**: `POST /api/tasks/{id}/activities/{activityId}/report`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Task ID |
| `activityId` | integer | Yes | Activity ID |

### Request Body

```json
{
  "report": "Completed research phase. Findings documented in attached report."
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `report` | string | Yes | Activity report text |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Report submitted successfully"
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "report": ["The report field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/tasks/1/activities/1/report" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "report": "Completed research phase. Findings documented."
  }'
```

---

## Task Status Values

The following status values are used for tasks:

| Status | Description |
|--------|-------------|
| `Not Started` | Task has been created but not started |
| `In Progress` | Task is currently being worked on |
| `On Hold` | Task is temporarily paused |
| `Completed` | Task has been completed |
| `Cancelled` | Task has been cancelled |

---

## Task Priority Values

The following priority values are used for tasks:

| Priority | Description |
|---------|-------------|
| `Low` | Low priority task |
| `Normal` | Normal priority (default) |
| `High` | High priority task |
| `Urgent` | Urgent priority task |

---

## Date Format

All dates should be in **ISO 8601 format**: `YYYY-MM-DD`

Examples:
- `2024-01-01` (January 1, 2024)
- `2024-12-31` (December 31, 2024)

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

class TaskApiService {
  final String baseUrl = 'http://192.168.100.102:8000/api';
  final String? token;

  TaskApiService(this.token);

  // Get my tasks
  Future<List<dynamic>> getMyTasks() async {
    final response = await http.get(
      Uri.parse('$baseUrl/tasks/my'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load tasks');
  }

  // Get assigned tasks
  Future<List<dynamic>> getAssignedTasks() async {
    final response = await http.get(
      Uri.parse('$baseUrl/tasks/assigned'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load assigned tasks');
  }

  // Create task
  Future<Map<String, dynamic>> createTask({
    required String name,
    String? description,
    String? startDate,
    String? endDate,
    required int teamLeaderId,
    String? priority,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/tasks'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'name': name,
        'description': description,
        'start_date': startDate,
        'end_date': endDate,
        'team_leader_id': teamLeaderId,
        'priority': priority ?? 'Normal',
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to create task');
  }

  // Complete task
  Future<void> completeTask(int taskId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/tasks/$taskId/complete'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to complete task');
    }
  }

  // Get task activities
  Future<List<dynamic>> getTaskActivities(int taskId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/tasks/$taskId/activities'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load activities');
  }

  // Create activity
  Future<Map<String, dynamic>> createActivity({
    required int taskId,
    required String name,
    String? startDate,
    String? endDate,
    String? priority,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/tasks/$taskId/activities'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'name': name,
        'start_date': startDate,
        'end_date': endDate,
        'priority': priority ?? 'Normal',
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to create activity');
  }
}
```

---

*Last Updated: Based on current TaskApiController implementation*

