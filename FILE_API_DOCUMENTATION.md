# File Management API Documentation

## Complete API Reference for File Management

**Base URL**: `http://your-domain.com/api`  
**Authentication**: Bearer Token (Laravel Sanctum) - Required for all endpoints  
**Content-Type**: `application/json` (multipart/form-data for file uploads)

---

## Table of Contents

### Digital Files
1. [Get Digital Files](#get-digital-files)
2. [Get Digital Folders](#get-digital-folders)
3. [Get Folder Contents](#get-folder-contents)
4. [Get Digital File Details](#get-digital-file-details)
5. [Upload Digital File](#upload-digital-file)
6. [Download Digital File](#download-digital-file)
7. [Request File Access](#request-file-access)
8. [Get My Access Requests](#get-my-access-requests)
9. [Get Pending Access Requests](#get-pending-access-requests)
10. [Approve Access Request](#approve-access-request)
11. [Reject Access Request](#reject-access-request)
12. [Search Digital Files](#search-digital-files)

### Physical Racks
13. [Get Physical Files](#get-physical-files)
14. [Get Physical Categories](#get-physical-categories)
15. [Get Rack Contents](#get-rack-contents)
16. [Get Physical File Details](#get-physical-file-details)
17. [Request Physical File](#request-physical-file)
18. [Get My Physical Requests](#get-my-physical-requests)
19. [Get Pending Physical Requests](#get-pending-physical-requests)
20. [Approve Physical Request](#approve-physical-request)
21. [Reject Physical Request](#reject-physical-request)
22. [Return Physical File](#return-physical-file)

---

## Digital Files

### Get Digital Files

Get list of digital files (optionally filtered by folder).

**Endpoint**: `GET /api/files/digital`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `folder_id` | integer | No | Filter by folder ID |
| `per_page` | integer | No | Items per page (default: 20, max: 100) |
| `page` | integer | No | Page number (default: 1) |

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "document.pdf",
      "file_type": "pdf",
      "size": 1024000,
      "uploader": "John Doe",
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
curl -X GET "http://192.168.100.102:8000/api/files/digital?folder_id=1&per_page=10" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Digital Folders

Get list of top-level digital folders.

**Endpoint**: `GET /api/files/digital/folders`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "HR Documents",
      "access_level": "department"
    },
    {
      "id": 2,
      "name": "Finance Documents",
      "access_level": "public"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/digital/folders" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Folder Contents

Get files and subfolders within a specific folder.

**Endpoint**: `GET /api/files/digital/folders/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Folder ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "folder": {
      "id": 1,
      "name": "HR Documents"
    },
    "files": [
      {
        "id": 1,
        "name": "employee_handbook.pdf",
        "size": 2048000,
        "created_at": "2024-01-15T10:30:00Z"
      },
      {
        "id": 2,
        "name": "policy_document.pdf",
        "size": 1536000,
        "created_at": "2024-01-14T09:20:00Z"
      }
    ]
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/digital/folders/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Digital File Details

Get detailed information about a specific digital file.

**Endpoint**: `GET /api/files/digital/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | File ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "document.pdf",
    "file_type": "pdf",
    "size": 1024000,
    "description": "Important document",
    "uploader": "John Doe",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/digital/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Upload Digital File

Upload a new digital file.

**Endpoint**: `POST /api/files/digital/upload`  
**Authentication**: Required  
**Content-Type**: `multipart/form-data`

### Request Body (Form Data)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `file` | file | Yes | File to upload (max 10MB) |
| `folder_id` | integer | Yes | Target folder ID (must exist) |
| `name` | string | No | Custom file name (defaults to original filename) |
| `description` | string | No | File description |

### Response (201 Created)

```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "id": 1,
    "name": "document.pdf"
  }
}
```

### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "file": ["The file field is required."],
    "folder_id": ["The folder id field is required."]
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/files/digital/upload" \
  -H "Authorization: Bearer {token}" \
  -F "file=@/path/to/document.pdf" \
  -F "folder_id=1" \
  -F "name=My Document" \
  -F "description=Important document"
```

---

### Download Digital File

Download a digital file.

**Endpoint**: `GET /api/files/digital/{id}/download`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | File ID |

### Response

Returns the file as a binary download with appropriate headers.

### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "File not found"
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/digital/1/download" \
  -H "Authorization: Bearer {token}" \
  -o downloaded_file.pdf
```

---

### Request File Access

Request access to a digital file.

**Endpoint**: `POST /api/files/digital/{id}/request-access`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | File ID |

### Request Body

```json
{
  "reason": "Need access for project work"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `reason` | string | No | Reason for access request |

### Response (201 Created)

```json
{
  "success": true,
  "message": "Access request submitted",
  "data": {
    "id": 1,
    "file_id": 1,
    "requester_id": 5,
    "status": "pending",
    "reason": "Need access for project work",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/files/digital/1/request-access" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Need access for project work"
  }'
```

---

### Get My Access Requests

Get all file access requests made by the authenticated user.

**Endpoint**: `GET /api/files/digital/my-requests`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "file_id": 1,
      "file": {
        "id": 1,
        "name": "document.pdf"
      },
      "status": "pending",
      "reason": "Need access for project work",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/digital/my-requests" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Pending Access Requests

Get all pending file access requests (managers only).

**Endpoint**: `GET /api/files/digital/pending-requests`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO, Record Officer only

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "file_id": 1,
      "file": {
        "id": 1,
        "name": "document.pdf"
      },
      "requester": {
        "id": 5,
        "name": "John Doe"
      },
      "status": "pending",
      "reason": "Need access for project work",
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
curl -X GET "http://192.168.100.102:8000/api/files/digital/pending-requests" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Approve Access Request

Approve a file access request (managers only).

**Endpoint**: `POST /api/files/digital/requests/{id}/approve`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO, Record Officer only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Access request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Access request approved"
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
curl -X POST "http://192.168.100.102:8000/api/files/digital/requests/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Reject Access Request

Reject a file access request (managers only).

**Endpoint**: `POST /api/files/digital/requests/{id}/reject`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO, Record Officer only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Access request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Access request rejected"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/files/digital/requests/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Search Digital Files

Search for digital files by name or description.

**Endpoint**: `GET /api/files/digital/search`  
**Authentication**: Required

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | Yes | Search query |

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "document.pdf",
      "file_type": "pdf",
      "size": 1024000,
      "uploader": {
        "id": 5,
        "name": "John Doe"
      },
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/digital/search?q=document" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Physical Racks

### Get Physical Files

Get list of physical file categories and racks.

**Endpoint**: `GET /api/files/physical`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "HR Files",
      "folders": [
        {
          "id": 1,
          "name": "Employee Records"
        }
      ]
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/physical" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Physical Categories

Get list of physical file categories.

**Endpoint**: `GET /api/files/physical/categories`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "HR Files",
      "description": "Human Resources files"
    },
    {
      "id": 2,
      "name": "Finance Files",
      "description": "Finance department files"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/physical/categories" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Rack Contents

Get files within a specific rack folder.

**Endpoint**: `GET /api/files/physical/racks/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Rack folder ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "folder": {
      "id": 1,
      "name": "Employee Records"
    },
    "files": [
      {
        "id": 1,
        "name": "EMP001 - John Doe",
        "rack_number": "A-001",
        "status": "available"
      }
    ]
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/physical/racks/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Physical File Details

Get detailed information about a specific physical file.

**Endpoint**: `GET /api/files/physical/{id}`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Physical file ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "EMP001 - John Doe",
    "rack_number": "A-001",
    "status": "available",
    "folder": {
      "id": 1,
      "name": "Employee Records"
    },
    "category": {
      "id": 1,
      "name": "HR Files"
    }
  }
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/physical/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Request Physical File

Request a physical file from the rack.

**Endpoint**: `POST /api/files/physical/{id}/request`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Physical file ID |

### Request Body

```json
{
  "reason": "Need for reference",
  "expected_return_date": "2024-02-15"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `reason` | string | No | Reason for request |
| `expected_return_date` | date | No | Expected return date (YYYY-MM-DD) |

### Response (201 Created)

```json
{
  "success": true,
  "message": "File request submitted",
  "data": {
    "id": 1,
    "rack_file_id": 1,
    "requester_id": 5,
    "status": "pending",
    "reason": "Need for reference",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/files/physical/1/request" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Need for reference",
    "expected_return_date": "2024-02-15"
  }'
```

---

### Get My Physical Requests

Get all physical file requests made by the authenticated user.

**Endpoint**: `GET /api/files/physical/my-requests`  
**Authentication**: Required

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "rack_file_id": 1,
      "rack_file": {
        "id": 1,
        "name": "EMP001 - John Doe"
      },
      "status": "pending",
      "reason": "Need for reference",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/physical/my-requests" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Get Pending Physical Requests

Get all pending physical file requests (managers only).

**Endpoint**: `GET /api/files/physical/pending-requests`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO, Record Officer only

### Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "rack_file_id": 1,
      "rack_file": {
        "id": 1,
        "name": "EMP001 - John Doe"
      },
      "requester": {
        "id": 5,
        "name": "John Doe"
      },
      "status": "pending",
      "reason": "Need for reference",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### Example Request

```bash
curl -X GET "http://192.168.100.102:8000/api/files/physical/pending-requests" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Approve Physical Request

Approve a physical file request (managers only).

**Endpoint**: `POST /api/files/physical/requests/{id}/approve`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO, Record Officer only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "File request approved"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/files/physical/requests/1/approve" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Reject Physical Request

Reject a physical file request (managers only).

**Endpoint**: `POST /api/files/physical/requests/{id}/reject`  
**Authentication**: Required  
**Access**: System Admin, HR Officer, HOD, CEO, Record Officer only

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "File request rejected"
}
```

### Example Request

```bash
curl -X POST "http://192.168.100.102:8000/api/files/physical/requests/1/reject" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

### Return Physical File

Return a physical file that was previously issued.

**Endpoint**: `POST /api/files/physical/requests/{id}/return`  
**Authentication**: Required

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Request ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "File returned successfully"
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
curl -X POST "http://192.168.100.102:8000/api/files/physical/requests/1/return" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## File Status Values

### Physical File Status

| Status | Description |
|--------|-------------|
| `available` | File is available for request |
| `issued` | File has been issued to a user |
| `archived` | File has been archived |

### Access Request Status

| Status | Description |
|--------|-------------|
| `pending` | Request is pending approval |
| `approved` | Request has been approved |
| `rejected` | Request has been rejected |
| `returned` | Physical file has been returned (physical files only) |

---

## File Upload Limits

- **Maximum file size**: 10MB (10,240 KB)
- **Supported file types**: All file types are accepted
- **Storage**: Files are stored in the `public/files` directory

---

## Date Format

All dates should be in **ISO 8601 format**: `YYYY-MM-DD`

Examples:
- `2024-01-15` (January 15, 2024)
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
  "message": "File not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "file": ["The file field is required."],
    "folder_id": ["The folder id field is required."]
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

class FileApiService {
  final String baseUrl = 'http://192.168.100.102:8000/api';
  final String? token;

  FileApiService(this.token);

  // Get digital folders
  Future<List<dynamic>> getDigitalFolders() async {
    final response = await http.get(
      Uri.parse('$baseUrl/files/digital/folders'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load folders');
  }

  // Upload digital file
  Future<Map<String, dynamic>> uploadDigitalFile({
    required File file,
    required int folderId,
    String? name,
    String? description,
  }) async {
    var request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl/files/digital/upload'),
    );

    request.headers['Authorization'] = 'Bearer $token';
    request.files.add(
      await http.MultipartFile.fromPath('file', file.path),
    );
    request.fields['folder_id'] = folderId.toString();
    if (name != null) request.fields['name'] = name;
    if (description != null) request.fields['description'] = description;

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to upload file');
  }

  // Download digital file
  Future<void> downloadDigitalFile(int fileId, String savePath) async {
    final response = await http.get(
      Uri.parse('$baseUrl/files/digital/$fileId/download'),
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      File(savePath).writeAsBytesSync(response.bodyBytes);
    } else {
      throw Exception('Failed to download file');
    }
  }

  // Request file access
  Future<Map<String, dynamic>> requestFileAccess({
    required int fileId,
    String? reason,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/files/digital/$fileId/request-access'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'reason': reason,
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to request access');
  }

  // Get physical categories
  Future<List<dynamic>> getPhysicalCategories() async {
    final response = await http.get(
      Uri.parse('$baseUrl/files/physical/categories'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    }
    throw Exception('Failed to load categories');
  }

  // Request physical file
  Future<Map<String, dynamic>> requestPhysicalFile({
    required int fileId,
    String? reason,
    String? expectedReturnDate,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/files/physical/$fileId/request'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'reason': reason,
        'expected_return_date': expectedReturnDate,
      }),
    );

    if (response.statusCode == 201) {
      return json.decode(response.body);
    }
    throw Exception('Failed to request physical file');
  }
}
```

---

*Last Updated: Based on current FileApiController implementation*

