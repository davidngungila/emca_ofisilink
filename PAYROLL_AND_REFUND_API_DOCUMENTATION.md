# Payroll and Refund API Documentation

This document provides comprehensive API documentation for Payroll and Refund Request management endpoints in the mobile application.

**Base URL:** `http://your-domain.com/api`  
**Authentication:** All endpoints (except public auth endpoints) require Bearer token authentication.

---

## Table of Contents

1. [Payroll API](#payroll-api)
   - [Get All Payrolls](#get-all-payrolls)
   - [Get My Payrolls](#get-my-payrolls)
   - [Get Payroll Details](#get-payroll-details)

2. [Refund Request API](#refund-request-api)
   - [Get All Refund Requests](#get-all-refund-requests)
   - [Get My Refund Requests](#get-my-refund-requests)
   - [Get Refund Request Details](#get-refund-request-details)
   - [Create Refund Request](#create-refund-request)
   - [HOD Approve/Reject](#hod-approvereject)
   - [Accountant Verify](#accountant-verify)
   - [CEO Approve](#ceo-approve)
   - [Mark as Paid](#mark-as-paid)
   - [Download Attachment](#download-attachment)

---

## Payroll API

### Get All Payrolls

Retrieve a paginated list of payroll records. Managers (System Admin, Accountant, HR Officer, CEO) can see all payrolls, while regular users only see their own.

**Endpoint:** `GET /api/finance/payroll`

**Authentication:** Required

**Query Parameters:**
- `per_page` (optional, integer): Number of records per page (default: 20)
- `page` (optional, integer): Page number (default: 1)

**Response:** `200 OK`

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 5,
        "payroll_number": "PAY-2024-001",
        "pay_period": "2024-01",
        "pay_date": "2024-01-31",
        "pay_period_start": "2024-01-01",
        "pay_period_end": "2024-01-31",
        "basic_salary": 50000.00,
        "allowances": 10000.00,
        "deductions": 5000.00,
        "total_amount": 55000.00,
        "status": "paid",
        "user": {
          "id": 5,
          "name": "John Doe",
          "email": "john.doe@example.com"
        },
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-31T14:20:00Z"
      }
    ],
    "first_page_url": "http://your-domain.com/api/finance/payroll?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://your-domain.com/api/finance/payroll?page=5",
    "links": [...],
    "next_page_url": "http://your-domain.com/api/finance/payroll?page=2",
    "path": "http://your-domain.com/api/finance/payroll",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 100
  }
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token

---

### Get My Payrolls

Retrieve all payroll records for the authenticated user (non-paginated).

**Endpoint:** `GET /api/finance/payroll/my`

**Authentication:** Required

**Response:** `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "payroll_number": "PAY-2024-001",
      "pay_period": "2024-01",
      "pay_date": "2024-01-31",
      "pay_period_start": "2024-01-01",
      "pay_period_end": "2024-01-31",
      "basic_salary": 50000.00,
      "allowances": 10000.00,
      "deductions": 5000.00,
      "total_amount": 55000.00,
      "status": "paid",
      "items": [
        {
          "id": 1,
          "payroll_id": 1,
          "employee_id": 5,
          "basic_salary": 50000.00,
          "overtime_hours": 10.00,
          "overtime_amount": 5000.00,
          "bonus_amount": 5000.00,
          "allowance_amount": 10000.00,
          "deduction_amount": 5000.00,
          "nssf_amount": 2000.00,
          "paye_amount": 1500.00,
          "nhif_amount": 500.00,
          "heslb_amount": 0.00,
          "wcf_amount": 500.00,
          "sdl_amount": 500.00,
          "other_deductions": 0.00,
          "employer_nssf": 2000.00,
          "employer_wcf": 500.00,
          "employer_sdl": 500.00,
          "total_employer_cost": 3000.00,
          "net_salary": 55000.00,
          "status": "paid"
        }
      ],
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-31T14:20:00Z"
    }
  ]
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token

---

### Get Payroll Details

Retrieve detailed information about a specific payroll record, including all payroll items.

**Endpoint:** `GET /api/finance/payroll/{id}`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Payroll ID

**Response:** `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 5,
    "payroll_number": "PAY-2024-001",
    "pay_period": "2024-01",
    "pay_date": "2024-01-31",
    "pay_period_start": "2024-01-01",
    "pay_period_end": "2024-01-31",
    "basic_salary": 50000.00,
    "allowances": 10000.00,
    "deductions": 5000.00,
    "total_amount": 55000.00,
    "status": "paid",
    "processed_by": 2,
    "branch_id": 1,
    "reviewed_by": 3,
    "approved_by": 4,
    "paid_by": 2,
    "reviewed_at": "2024-01-30T10:00:00Z",
    "approved_at": "2024-01-30T14:00:00Z",
    "paid_at": "2024-01-31T09:00:00Z",
    "review_notes": "Reviewed and approved",
    "approval_notes": "Approved for payment",
    "payment_method": "bank_transfer",
    "payment_date": "2024-01-31",
    "transaction_reference": "TXN-2024-001",
    "gl_account_id": 10,
    "cash_box_id": null,
    "transaction_details": "Payment processed successfully",
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "items": [
      {
        "id": 1,
        "payroll_id": 1,
        "employee_id": 5,
        "basic_salary": 50000.00,
        "overtime_hours": 10.00,
        "overtime_amount": 5000.00,
        "bonus_amount": 5000.00,
        "allowance_amount": 10000.00,
        "deduction_amount": 5000.00,
        "nssf_amount": 2000.00,
        "paye_amount": 1500.00,
        "nhif_amount": 500.00,
        "heslb_amount": 0.00,
        "wcf_amount": 500.00,
        "sdl_amount": 500.00,
        "other_deductions": 0.00,
        "employer_nssf": 2000.00,
        "employer_wcf": 500.00,
        "employer_sdl": 500.00,
        "total_employer_cost": 3000.00,
        "net_salary": 55000.00,
        "status": "paid"
      }
    ],
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-31T14:20:00Z"
  }
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission to view this payroll
- `404 Not Found`: Payroll not found

---

## Refund Request API

### Get All Refund Requests

Retrieve a paginated list of refund requests. Managers can see all requests, while regular users only see their own.

**Endpoint:** `GET /api/finance/refunds`

**Authentication:** Required

**Query Parameters:**
- `status` (optional, string): Filter by status (`pending_hod`, `pending_accountant`, `pending_ceo`, `approved`, `paid`, `rejected`)
- `from_date` (optional, date): Filter requests from this date (format: YYYY-MM-DD)
- `to_date` (optional, date): Filter requests to this date (format: YYYY-MM-DD)
- `search` (optional, string): Search by request number, purpose, or staff name
- `per_page` (optional, integer): Number of records per page (default: 20)
- `page` (optional, integer): Page number (default: 1)

**Response:** `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "request_no": "REF-2024-ABC123",
      "staff_id": 5,
      "staff": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "purpose": "Travel expenses",
      "amount": 5000.00,
      "expense_date": "2024-01-15",
      "description": "Business trip to Nairobi",
      "status": "pending_hod",
      "progress_percentage": 20,
      "created_at": "2024-01-16T10:30:00Z"
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

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token

---

### Get My Refund Requests

Retrieve all refund requests for the authenticated user (non-paginated).

**Endpoint:** `GET /api/finance/refunds/my`

**Authentication:** Required

**Response:** `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "request_no": "REF-2024-ABC123",
      "staff_id": 5,
      "staff": {
        "id": 5,
        "name": "John Doe",
        "email": "john.doe@example.com"
      },
      "purpose": "Travel expenses",
      "amount": 5000.00,
      "expense_date": "2024-01-15",
      "description": "Business trip to Nairobi",
      "status": "pending_hod",
      "progress_percentage": 20,
      "created_at": "2024-01-16T10:30:00Z"
    }
  ]
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token

---

### Get Refund Request Details

Retrieve detailed information about a specific refund request, including attachments and approval history.

**Endpoint:** `GET /api/finance/refunds/{id}`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Refund request ID

**Response:** `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "request_no": "REF-2024-ABC123",
    "staff_id": 5,
    "staff": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "purpose": "Travel expenses",
    "amount": 5000.00,
    "expense_date": "2024-01-15",
    "description": "Business trip to Nairobi for client meeting",
    "status": "approved",
    "progress_percentage": 80,
    "attachments": [
      {
        "id": 1,
        "file_name": "receipt.pdf",
        "file_size": 245760,
        "file_type": "pdf",
        "download_url": "http://your-domain.com/api/finance/refunds/1/attachments/1/download"
      }
    ],
    "hod_approval": {
      "id": 3,
      "name": "Jane Manager",
      "approved_at": "2024-01-17T09:00:00Z",
      "comments": "Approved for accountant verification"
    },
    "accountant_verification": {
      "id": 2,
      "name": "Bob Accountant",
      "verified_at": "2024-01-18T10:00:00Z",
      "comments": "Verified and forwarded to CEO"
    },
    "ceo_approval": {
      "id": 4,
      "name": "CEO Name",
      "approved_at": "2024-01-19T14:00:00Z",
      "comments": "Approved for payment"
    },
    "payment": null,
    "rejection": null,
    "created_at": "2024-01-16T10:30:00Z"
  }
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission to view this refund request
- `404 Not Found`: Refund request not found

---

### Create Refund Request

Create a new refund request with optional file attachments.

**Endpoint:** `POST /api/finance/refunds`

**Authentication:** Required

**Request Body (multipart/form-data):**
- `purpose` (required, string, max: 255): Purpose of the refund request
- `amount` (required, numeric, min: 0): Refund amount
- `expense_date` (required, date): Date when the expense was incurred (format: YYYY-MM-DD)
- `description` (required, string): Detailed description of the expense
- `attachments[]` (optional, array of files): Supporting documents (PDF, JPG, JPEG, PNG, DOC, DOCX, max 5MB each)

**Example Request (multipart/form-data):**
```
purpose: Travel expenses
amount: 5000.00
expense_date: 2024-01-15
description: Business trip to Nairobi for client meeting
attachments[0]: [file]
attachments[1]: [file]
```

**Response:** `201 Created`

```json
{
  "success": true,
  "message": "Refund request created successfully",
  "data": {
    "id": 1,
    "request_no": "REF-2024-ABC123",
    "staff_id": 5,
    "staff": {
      "id": 5,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "purpose": "Travel expenses",
    "amount": 5000.00,
    "expense_date": "2024-01-15",
    "description": "Business trip to Nairobi for client meeting",
    "status": "pending_hod",
    "progress_percentage": 20,
    "attachments": [
      {
        "id": 1,
        "file_name": "receipt.pdf",
        "file_size": 245760,
        "file_type": "pdf"
      }
    ],
    "created_at": "2024-01-16T10:30:00Z"
  }
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `422 Unprocessable Entity`: Validation errors

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "purpose": ["The purpose field is required."],
    "amount": ["The amount must be a number."],
    "expense_date": ["The expense date field is required."],
    "description": ["The description field is required."]
  }
}
```

---

### HOD Approve/Reject

Approve or reject a refund request at the HOD level. Only HOD and System Admin can perform this action.

**Endpoint:** `POST /api/finance/refunds/{id}/hod-approve`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Refund request ID

**Request Body:**
```json
{
  "action": "approve",
  "comments": "Approved for accountant verification"
}
```

**Request Body Fields:**
- `action` (required, string): Either `"approve"` or `"reject"`
- `comments` (optional, string): Comments or notes

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Refund request approved by HOD. Now pending accountant verification."
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission (not HOD or System Admin)
- `404 Not Found`: Refund request not found
- `422 Unprocessable Entity`: Request is not in `pending_hod` status or validation errors

---

### Accountant Verify

Verify or reject a refund request at the accountant level. Only Accountant and System Admin can perform this action.

**Endpoint:** `POST /api/finance/refunds/{id}/accountant-verify`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Refund request ID

**Request Body:**
```json
{
  "action": "verify",
  "comments": "Verified and forwarded to CEO"
}
```

**Request Body Fields:**
- `action` (required, string): Either `"verify"` or `"reject"`
- `comments` (optional, string): Comments or notes

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Refund request verified. Now pending CEO approval."
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission (not Accountant or System Admin)
- `404 Not Found`: Refund request not found
- `422 Unprocessable Entity`: Request is not in `pending_accountant` status or validation errors

---

### CEO Approve

Approve or reject a refund request at the CEO level. Only CEO, Director, and System Admin can perform this action.

**Endpoint:** `POST /api/finance/refunds/{id}/ceo-approve`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Refund request ID

**Request Body:**
```json
{
  "action": "approve",
  "comments": "Approved for payment"
}
```

**Request Body Fields:**
- `action` (required, string): Either `"approve"` or `"reject"`
- `comments` (optional, string): Comments or notes

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Refund request approved by CEO. Ready for payment."
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission (not CEO, Director, or System Admin)
- `404 Not Found`: Refund request not found
- `422 Unprocessable Entity`: Request is not in `pending_ceo` status or validation errors

---

### Mark as Paid

Mark an approved refund request as paid. Only Accountant and System Admin can perform this action.

**Endpoint:** `POST /api/finance/refunds/{id}/mark-paid`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Refund request ID

**Request Body:**
```json
{
  "payment_method": "bank_transfer",
  "payment_reference": "TXN-2024-001",
  "payment_notes": "Payment processed via bank transfer"
}
```

**Request Body Fields:**
- `payment_method` (required, string): Payment method (e.g., `bank_transfer`, `cash`, `cheque`)
- `payment_reference` (optional, string): Transaction reference or cheque number
- `payment_notes` (optional, string): Additional payment notes

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Refund marked as paid successfully"
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission (not Accountant or System Admin)
- `404 Not Found`: Refund request not found
- `422 Unprocessable Entity`: Request is not in `approved` status or validation errors

---

### Download Attachment

Download a specific attachment file from a refund request.

**Endpoint:** `GET /api/finance/refunds/{id}/attachments/{attachmentId}/download`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Refund request ID
- `attachmentId` (required, integer): Attachment ID

**Response:** `200 OK` (File download)

The response will be the file content with appropriate headers for download.

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission to view this refund request
- `404 Not Found`: Refund request, attachment, or file not found

---

## Refund Request Status Flow

The refund request follows this status progression:

1. **pending_hod** → Created by staff, awaiting HOD approval
2. **pending_accountant** → Approved by HOD, awaiting accountant verification
3. **pending_ceo** → Verified by accountant, awaiting CEO approval
4. **approved** → Approved by CEO, ready for payment
5. **paid** → Marked as paid by accountant
6. **rejected** → Rejected at any stage (HOD, Accountant, or CEO)

**Progress Percentages:**
- `pending_hod`: 20%
- `pending_accountant`: 40%
- `pending_ceo`: 60%
- `approved`: 80%
- `paid`: 100%
- `rejected`: 0%

---

## Payroll Status Values

- `draft`: Payroll is being prepared
- `pending_review`: Awaiting review
- `pending_approval`: Awaiting approval
- `approved`: Approved for payment
- `paid`: Payment processed
- `cancelled`: Cancelled

---

## Common Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
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

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Notes

1. **File Uploads**: When creating refund requests with attachments, use `multipart/form-data` content type. Each file must be less than 5MB.

2. **Permissions**: 
   - Regular users can only view and create their own refund requests
   - Managers (System Admin, HOD, Accountant, CEO, Director, HR Officer) can view all refund requests
   - Only specific roles can approve/verify at each stage

3. **Payroll Access**:
   - Regular users can only view their own payrolls
   - Managers (System Admin, Accountant, HR Officer, CEO) can view all payrolls

4. **Date Formats**: All dates should be in ISO 8601 format (YYYY-MM-DDTHH:mm:ssZ) or simple date format (YYYY-MM-DD) as specified.

5. **Pagination**: Paginated endpoints return standard Laravel pagination structure with links and metadata.

