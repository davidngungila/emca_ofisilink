<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\RefundAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RefundApiController extends Controller
{
    /**
     * Get all refund requests (filtered by user role)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HOD', 'Accountant', 'CEO', 'Director', 'HR Officer']);
        
        $query = RefundRequest::with(['staff:id,name,email', 'attachments']);
        
        if (!$isManager) {
            $query->where('staff_id', $user->id);
        }
        
        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('staff', function($staffQuery) use ($search) {
                      $staffQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $refunds = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $refunds->map(function ($refund) {
                return $this->formatRefund($refund);
            }),
            'pagination' => [
                'current_page' => $refunds->currentPage(),
                'total' => $refunds->total(),
                'per_page' => $refunds->perPage(),
                'last_page' => $refunds->lastPage(),
            ]
        ]);
    }

    /**
     * Get my refund requests
     */
    public function myRefunds(Request $request)
    {
        $user = Auth::user();
        
        $refunds = RefundRequest::where('staff_id', $user->id)
            ->with(['attachments'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($refund) {
                return $this->formatRefund($refund);
            });

        return response()->json([
            'success' => true,
            'data' => $refunds
        ]);
    }

    /**
     * Get single refund request
     */
    public function show($id)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HOD', 'Accountant', 'CEO', 'Director', 'HR Officer']);
        
        $refund = RefundRequest::with([
            'staff',
            'attachments',
            'hodApproval',
            'accountantVerification',
            'ceoApproval',
            'paidBy',
            'rejectedBy'
        ])->findOrFail($id);

        // Check access
        if (!$isManager && $refund->staff_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatRefund($refund, true)
        ]);
    }

    /**
     * Create refund request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'required|string',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Generate unique request number
        $requestNo = 'REF-' . date('Y') . '-' . strtoupper(Str::random(6));
        while (RefundRequest::where('request_no', $requestNo)->exists()) {
            $requestNo = 'REF-' . date('Y') . '-' . strtoupper(Str::random(6));
        }

        $refund = RefundRequest::create([
            'request_no' => $requestNo,
            'staff_id' => $user->id,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'status' => 'pending_hod',
            'created_by' => $user->id,
        ]);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('refund_attachments', $filename, 'public');
                
                RefundAttachment::create([
                    'refund_request_id' => $refund->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'uploaded_by' => $user->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Refund request created successfully',
            'data' => $this->formatRefund($refund->load('attachments'))
        ], 201);
    }

    /**
     * HOD Approve/Reject refund request
     */
    public function hodApprove(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $refund = RefundRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HOD can approve refund requests.'
            ], 403);
        }

        if ($refund->status !== 'pending_hod') {
            return response()->json([
                'success' => false,
                'message' => 'This request is not pending HOD approval.'
            ], 422);
        }

        if ($request->action === 'approve') {
            $refund->update([
                'status' => 'pending_accountant',
                'hod_approved_at' => now(),
                'hod_approved_by' => $user->id,
                'hod_comments' => $request->comments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund request approved by HOD. Now pending accountant verification.'
            ]);
        } else {
            $refund->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $user->id,
                'rejection_reason' => $request->comments,
                'hod_comments' => $request->comments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund request rejected'
            ]);
        }
    }

    /**
     * Accountant Verify refund request
     */
    public function accountantVerify(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:verify,reject',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $refund = RefundRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can verify refund requests.'
            ], 403);
        }

        if ($refund->status !== 'pending_accountant') {
            return response()->json([
                'success' => false,
                'message' => 'This request is not pending accountant verification.'
            ], 422);
        }

        if ($request->action === 'verify') {
            $refund->update([
                'status' => 'pending_ceo',
                'accountant_verified_at' => now(),
                'accountant_verified_by' => $user->id,
                'accountant_comments' => $request->comments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund request verified. Now pending CEO approval.'
            ]);
        } else {
            $refund->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $user->id,
                'rejection_reason' => $request->comments,
                'accountant_comments' => $request->comments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund request rejected'
            ]);
        }
    }

    /**
     * CEO Approve refund request
     */
    public function ceoApprove(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $refund = RefundRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only CEO/Director can approve refund requests.'
            ], 403);
        }

        if ($refund->status !== 'pending_ceo') {
            return response()->json([
                'success' => false,
                'message' => 'This request is not pending CEO approval.'
            ], 422);
        }

        if ($request->action === 'approve') {
            $refund->update([
                'status' => 'approved',
                'ceo_approved_at' => now(),
                'ceo_approved_by' => $user->id,
                'ceo_comments' => $request->comments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund request approved by CEO. Ready for payment.'
            ]);
        } else {
            $refund->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => $user->id,
                'rejection_reason' => $request->comments,
                'ceo_comments' => $request->comments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund request rejected'
            ]);
        }
    }

    /**
     * Mark refund as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'payment_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $refund = RefundRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can mark refund as paid.'
            ], 403);
        }

        if ($refund->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Refund request must be approved before marking as paid.'
            ], 422);
        }

        $refund->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => $user->id,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'payment_notes' => $request->payment_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund marked as paid successfully'
        ]);
    }

    /**
     * Download refund attachment
     */
    public function downloadAttachment($id, $attachmentId)
    {
        $refund = RefundRequest::findOrFail($id);
        $attachment = RefundAttachment::where('refund_request_id', $refund->id)
            ->findOrFail($attachmentId);
        
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HOD', 'Accountant', 'CEO', 'Director', 'HR Officer']);

        if (!$isManager && $refund->staff_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Format refund request for API response
     */
    private function formatRefund($refund, $detailed = false)
    {
        $data = [
            'id' => $refund->id,
            'request_no' => $refund->request_no,
            'staff_id' => $refund->staff_id,
            'staff' => $refund->staff ? [
                'id' => $refund->staff->id,
                'name' => $refund->staff->name,
                'email' => $refund->staff->email,
            ] : null,
            'purpose' => $refund->purpose,
            'amount' => (float) $refund->amount,
            'expense_date' => $refund->expense_date,
            'description' => $refund->description,
            'status' => $refund->status,
            'progress_percentage' => $refund->progress_percentage,
            'created_at' => $refund->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['attachments'] = $refund->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_size' => $attachment->file_size,
                    'file_type' => $attachment->file_type,
                    'download_url' => url("/api/refunds/{$refund->id}/attachments/{$attachment->id}/download"),
                ];
            });
            
            $data['hod_approval'] = $refund->hodApproval ? [
                'id' => $refund->hodApproval->id,
                'name' => $refund->hodApproval->name,
                'approved_at' => $refund->hod_approved_at?->toIso8601String(),
                'comments' => $refund->hod_comments,
            ] : null;
            
            $data['accountant_verification'] = $refund->accountantVerification ? [
                'id' => $refund->accountantVerification->id,
                'name' => $refund->accountantVerification->name,
                'verified_at' => $refund->accountant_verified_at?->toIso8601String(),
                'comments' => $refund->accountant_comments,
            ] : null;
            
            $data['ceo_approval'] = $refund->ceoApproval ? [
                'id' => $refund->ceoApproval->id,
                'name' => $refund->ceoApproval->name,
                'approved_at' => $refund->ceo_approved_at?->toIso8601String(),
                'comments' => $refund->ceo_comments,
            ] : null;
            
            $data['payment'] = $refund->paidBy ? [
                'paid_by' => [
                    'id' => $refund->paidBy->id,
                    'name' => $refund->paidBy->name,
                ],
                'paid_at' => $refund->paid_at?->toIso8601String(),
                'payment_method' => $refund->payment_method,
                'payment_reference' => $refund->payment_reference,
                'payment_notes' => $refund->payment_notes,
            ] : null;
            
            $data['rejection'] = $refund->rejectedBy ? [
                'rejected_by' => [
                    'id' => $refund->rejectedBy->id,
                    'name' => $refund->rejectedBy->name,
                ],
                'rejected_at' => $refund->rejected_at?->toIso8601String(),
                'rejection_reason' => $refund->rejection_reason,
            ] : null;
        }

        return $data;
    }
}

