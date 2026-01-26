# Invoice Approval API Documentation

This document provides API documentation for Accounts Receivable Invoice management and approval endpoints in the mobile application.

**Base URL:** `http://your-domain.com/api`  
**Authentication:** All endpoints require Bearer token authentication.

---

## Table of Contents

1. [Get All Invoices](#get-all-invoices)
2. [Get Pending Invoices](#get-pending-invoices)
3. [Get Invoice Details](#get-invoice-details)
4. [Approve Invoice](#approve-invoice)
5. [Reject Invoice](#reject-invoice)

---

## Get All Invoices

Retrieve a paginated list of invoices. Available to Accountant, System Admin, CEO, HOD, and Director roles.

**Endpoint:** `GET /api/finance/invoices`

**Authentication:** Required

**Query Parameters:**
- `status` (optional, string): Filter by status (`Pending for Approval`, `Pending CEO Approval`, `Approved`, `Sent`, `Partially Paid`, `Paid`, `Overdue`, `Rejected`)
- `customer_id` (optional, integer): Filter by customer ID
- `from_date` (optional, date): Filter invoices from this date (format: YYYY-MM-DD)
- `to_date` (optional, date): Filter invoices to this date (format: YYYY-MM-DD)
- `search` (optional, string): Search by invoice number, reference number, or customer name/email
- `per_page` (optional, integer): Number of records per page (default: 20)
- `page` (optional, integer): Page number (default: 1)

**Response:** `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 9,
      "invoice_no": "INV202401150001",
      "customer_id": 5,
      "customer": {
        "id": 5,
        "name": "ABC Company Ltd",
        "email": "contact@abccompany.com",
        "phone": "+255123456789"
      },
      "invoice_date": "2024-01-15",
      "due_date": "2024-02-15",
      "reference_no": "PO-2024-001",
      "subtotal": 100000.00,
      "tax_amount": 18000.00,
      "discount_amount": 0.00,
      "total_amount": 118000.00,
      "paid_amount": 0.00,
      "balance": 118000.00,
      "status": "Pending for Approval",
      "notes": "Payment terms: Net 30",
      "terms": "Net 30 days",
      "hod_approval": null,
      "ceo_approval": null,
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

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have required permissions

---

## Get Pending Invoices

Retrieve invoices pending approval. HODs see invoices pending HOD approval, CEOs see invoices pending CEO approval.

**Endpoint:** `GET /api/finance/invoices/pending`

**Authentication:** Required

**Query Parameters:**
- `per_page` (optional, integer): Number of records per page (default: 20)
- `page` (optional, integer): Page number (default: 1)

**Response:** `200 OK`

```json
{
  "success": true,
  "data": [
    {
      "id": 9,
      "invoice_no": "INV202401150001",
      "customer_id": 5,
      "customer": {
        "id": 5,
        "name": "ABC Company Ltd",
        "email": "contact@abccompany.com",
        "phone": "+255123456789"
      },
      "invoice_date": "2024-01-15",
      "due_date": "2024-02-15",
      "reference_no": "PO-2024-001",
      "subtotal": 100000.00,
      "tax_amount": 18000.00,
      "discount_amount": 0.00,
      "total_amount": 118000.00,
      "paid_amount": 0.00,
      "balance": 118000.00,
      "status": "Pending for Approval",
      "notes": "Payment terms: Net 30",
      "terms": "Net 30 days",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 5,
    "per_page": 20,
    "last_page": 1
  }
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have required permissions

---

## Get Invoice Details

Retrieve detailed information about a specific invoice, including items and payments.

**Endpoint:** `GET /api/finance/invoices/{id}`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Invoice ID

**Response:** `200 OK`

```json
{
  "success": true,
  "data": {
    "id": 9,
    "invoice_no": "INV202401150001",
    "customer_id": 5,
    "customer": {
      "id": 5,
      "name": "ABC Company Ltd",
      "email": "contact@abccompany.com",
      "phone": "+255123456789"
    },
    "invoice_date": "2024-01-15",
    "due_date": "2024-02-15",
    "reference_no": "PO-2024-001",
    "subtotal": 100000.00,
    "tax_amount": 18000.00,
    "discount_amount": 0.00,
    "total_amount": 118000.00,
    "paid_amount": 0.00,
    "balance": 118000.00,
    "status": "Pending for Approval",
    "notes": "Payment terms: Net 30",
    "terms": "Net 30 days",
    "items": [
      {
        "id": 1,
        "description": "Consulting Services",
        "quantity": 10.00,
        "unit_price": 10000.00,
        "total": 100000.00,
        "account": {
          "id": 10,
          "name": "Service Revenue",
          "code": "SERV-REV"
        }
      }
    ],
    "payments": [],
    "hod_approval": null,
    "ceo_approval": null,
    "creator": {
      "id": 2,
      "name": "John Accountant"
    },
    "updater": {
      "id": 2,
      "name": "John Accountant"
    },
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Error Responses:**
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have required permissions
- `404 Not Found`: Invoice not found

---

## Approve Invoice

Approve an invoice. The approval workflow depends on the user's role:
- **HOD**: Can approve invoices with status "Pending for Approval" → moves to "Pending CEO Approval"
- **CEO/Director**: Can approve invoices with status "Pending CEO Approval" → moves to "Approved"
- **System Admin**: Can approve invoices with status "Pending for Approval" directly → moves to "Approved"

**Endpoint:** `POST /api/finance/invoices/{id}/approve`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Invoice ID

**Request Body:**
```json
{
  "comments": "Approved for payment processing"
}
```

**Request Body Fields:**
- `comments` (optional, string, max: 1000): Approval comments or notes

**Response:** `200 OK`

**HOD Approval Response:**
```json
{
  "success": true,
  "message": "Invoice approved by HOD. Waiting for CEO approval.",
  "data": {
    "id": 9,
    "invoice_no": "INV202401150001",
    "status": "Pending CEO Approval",
    "hod_approval": {
      "approved_at": "2024-01-16T09:00:00Z",
      "approved_by": {
        "id": 3,
        "name": "Jane HOD"
      },
      "comments": "Approved for payment processing"
    },
    ...
  }
}
```

**CEO Approval Response:**
```json
{
  "success": true,
  "message": "Invoice approved successfully by CEO. Invoice can now be paid.",
  "data": {
    "id": 9,
    "invoice_no": "INV202401150001",
    "status": "Approved",
    "hod_approval": {
      "approved_at": "2024-01-16T09:00:00Z",
      "approved_by": {
        "id": 3,
        "name": "Jane HOD"
      },
      "comments": "Approved for payment processing"
    },
    "ceo_approval": {
      "approved_at": "2024-01-17T14:00:00Z",
      "approved_by": {
        "id": 4,
        "name": "CEO Name"
      },
      "comments": "Final approval granted"
    },
    ...
  }
}
```

**Error Responses:**
- `400 Bad Request`: Invoice is not pending approval or already processed
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission to approve invoices
- `404 Not Found`: Invoice not found
- `422 Unprocessable Entity`: Validation errors

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "comments": ["The comments field must not exceed 1000 characters."]
  }
}
```

---

## Reject Invoice

Reject an invoice. Only HOD, CEO, Director, or System Admin can reject invoices.

**Endpoint:** `POST /api/finance/invoices/{id}/reject`

**Authentication:** Required

**URL Parameters:**
- `id` (required, integer): Invoice ID

**Request Body:**
```json
{
  "rejection_reason": "Incorrect pricing information. Please review and resubmit."
}
```

**Request Body Fields:**
- `rejection_reason` (required, string, max: 1000): Reason for rejection

**Response:** `200 OK`

```json
{
  "success": true,
  "message": "Invoice rejected successfully",
  "data": {
    "id": 9,
    "invoice_no": "INV202401150001",
    "status": "Rejected",
    "notes": "Payment terms: Net 30\n\nRejected: Incorrect pricing information. Please review and resubmit.",
    ...
  }
}
```

**Error Responses:**
- `400 Bad Request`: Invoice is not pending approval
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: User does not have permission to reject invoices
- `404 Not Found`: Invoice not found
- `422 Unprocessable Entity`: Validation errors

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rejection_reason": [
      "The rejection reason field is required.",
      "The rejection reason field must not exceed 1000 characters."
    ]
  }
}
```

---

## Invoice Status Flow

The invoice follows this status progression:

1. **Draft** → Invoice being created
2. **Pending for Approval** → Submitted, awaiting HOD approval
3. **Pending CEO Approval** → Approved by HOD, awaiting CEO approval
4. **Approved** → Approved by CEO, ready to be sent
5. **Sent** → Invoice sent to customer
6. **Partially Paid** → Some payment received
7. **Paid** → Fully paid
8. **Overdue** → Past due date with outstanding balance
9. **Rejected** → Rejected at any approval stage

---

## Approval Workflow

### HOD Approval
- **Who can approve:** HOD, System Admin
- **Status required:** `Pending for Approval`
- **Result:** Status changes to `Pending CEO Approval`
- **Next step:** CEO/Director approval required

### CEO Approval
- **Who can approve:** CEO, Director, System Admin
- **Status required:** `Pending CEO Approval`
- **Result:** Status changes to `Approved`, then automatically to `Sent`
- **Next step:** Invoice can be paid

### System Admin Approval
- **Who can approve:** System Admin only
- **Status required:** `Pending for Approval`
- **Result:** Status changes directly to `Approved` (bypasses CEO approval)
- **Next step:** Invoice can be paid

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
  "message": "Unauthorized. Only HOD, CEO, or System Admin can approve invoices."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Invoice not found"
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
  "message": "Error approving invoice: [error details]"
}
```

---

## Notes

1. **Permissions**: 
   - Regular users cannot access invoice endpoints
   - Only Accountant, System Admin, CEO, HOD, and Director roles have access
   - Approval permissions are role-specific (HOD for first level, CEO for final approval)

2. **Status Updates**: When an invoice is approved by CEO, the status automatically updates to "Sent" if it's not already in a payment-related status.

3. **Notifications**: When HOD approves an invoice, the CEO is automatically notified (if notification service is available).

4. **Activity Logging**: All approval and rejection actions are logged in the activity log system.

5. **Rejection**: Rejected invoices have the rejection reason appended to the notes field.

