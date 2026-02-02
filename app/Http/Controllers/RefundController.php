<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use App\Models\RefundAttachment;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RefundController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of refund requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Staff can only see their own requests
        // Managers can see all requests
        $isManager = $user->hasAnyRole(['System Admin', 'HOD', 'Accountant', 'CEO', 'Director', 'HR Officer']);
        
        $query = RefundRequest::with(['staff', 'attachments', 'hodApproval', 'accountantVerification', 'ceoApproval']);
        
        if (!$isManager) {
            $query->where('staff_id', $user->id);
        }
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('staff', function($staffQuery) use ($search) {
                      $staffQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $refundRequests = $query->orderByDesc('created_at')->paginate(20);
        
        // Statistics
        $stats = [
            'all' => $isManager ? RefundRequest::count() : RefundRequest::where('staff_id', $user->id)->count(),
            'pending_hod' => $isManager ? RefundRequest::where('status', 'pending_hod')->count() : RefundRequest::where('staff_id', $user->id)->where('status', 'pending_hod')->count(),
            'pending_accountant' => $isManager ? RefundRequest::where('status', 'pending_accountant')->count() : RefundRequest::where('staff_id', $user->id)->where('status', 'pending_accountant')->count(),
            'pending_ceo' => $isManager ? RefundRequest::where('status', 'pending_ceo')->count() : RefundRequest::where('staff_id', $user->id)->where('status', 'pending_ceo')->count(),
            'approved' => $isManager ? RefundRequest::where('status', 'approved')->count() : RefundRequest::where('staff_id', $user->id)->where('status', 'approved')->count(),
            'paid' => $isManager ? RefundRequest::where('status', 'paid')->count() : RefundRequest::where('staff_id', $user->id)->where('status', 'paid')->count(),
            'rejected' => $isManager ? RefundRequest::where('status', 'rejected')->count() : RefundRequest::where('staff_id', $user->id)->where('status', 'rejected')->count(),
        ];
        
        return view('modules.refunds.index', compact('refundRequests', 'stats', 'isManager'));
    }

    /**
     * Show the form for creating a new refund request
     */
    public function create()
    {
        $user = Auth::user();
        
        // All staff can create refund requests
        return view('modules.refunds.create');
    }

    /**
     * Store a newly created refund request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'attachments' => 'required|array|min:1',
            'attachments.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max per file
        ], [
            'attachments.required' => 'At least one supporting document is required',
            'attachments.min' => 'At least one supporting document is required',
        ]);

        try {
            DB::beginTransaction();
            
            // Generate unique request number
            $requestNo = 'REF-' . date('Y') . '-' . strtoupper(Str::random(6));
            while (RefundRequest::where('request_no', $requestNo)->exists()) {
                $requestNo = 'REF-' . date('Y') . '-' . strtoupper(Str::random(6));
            }
            
            $refundRequest = RefundRequest::create([
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
                $files = $request->file('attachments');
                $descriptions = $request->input('attachment_descriptions', []);
                
                foreach ($files as $index => $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $fileName = time() . '_' . $refundRequest->id . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                        $filePath = $file->storeAs('refund_attachments', $fileName, 'public');
                        
                        if ($filePath) {
                            RefundAttachment::create([
                                'refund_request_id' => $refundRequest->id,
                                'file_name' => $originalName,
                                'file_path' => $filePath,
                                'file_type' => $file->getMimeType(),
                                'file_size' => $file->getSize(),
                                'description' => $descriptions[$index] ?? null,
                                'uploaded_by' => $user->id,
                            ]);
                        }
                    }
                }
            }
            
            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('refund_request_created', "Created refund request {$refundRequest->request_no}", $refundRequest, [
                'request_no' => $refundRequest->request_no,
                'amount' => $refundRequest->amount,
                'purpose' => $refundRequest->purpose,
            ]);
            
            // Notify HOD
            $this->notifyHod($refundRequest);
            
            return redirect()->route('refunds.index')
                ->with('success', 'Refund request submitted successfully. It is now pending HOD approval.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create refund request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified refund request
     */
    public function show($id)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HOD', 'Accountant', 'CEO', 'Director', 'HR Officer']);
        
        $refundRequest = RefundRequest::with([
            'staff',
            'attachments.uploader',
            'hodApproval',
            'accountantVerification',
            'ceoApproval',
            'paidBy',
            'rejectedBy',
            'creator'
        ])->findOrFail($id);
        
        // Check permissions
        if (!$isManager && $refundRequest->staff_id !== $user->id) {
            abort(403, 'You do not have permission to view this refund request');
        }
        
        return view('modules.refunds.show', compact('refundRequest', 'isManager'));
    }

    /**
     * HOD Approval
     */
    public function hodApprove(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            abort(403, 'Unauthorized. Only HOD can approve refund requests.');
        }
        
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);
        
        $refundRequest = RefundRequest::findOrFail($id);
        
        if ($refundRequest->status !== 'pending_hod') {
            return redirect()->back()->with('error', 'This request is not pending HOD approval.');
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->action === 'approve') {
                $refundRequest->update([
                    'status' => 'pending_accountant',
                    'hod_approved_at' => now(),
                    'hod_approved_by' => $user->id,
                    'hod_comments' => $request->comments,
                ]);
                
                // Log activity
                ActivityLogService::logAction('refund_hod_approved', "HOD approved refund request {$refundRequest->request_no}", $refundRequest);
                
                // Notify Accountant
                $this->notifyAccountant($refundRequest);
                
                // Notify staff
                $this->notificationService->notify(
                    $refundRequest->staff_id,
                    "Your refund request {$refundRequest->request_no} has been approved by HOD and is now pending accountant verification.",
                    route('refunds.show', $refundRequest->id),
                    'Refund Request Approved by HOD'
                );
                
                $message = 'Refund request approved successfully. It is now pending accountant verification.';
            } else {
                $refundRequest->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => $user->id,
                    'rejection_reason' => $request->comments,
                    'hod_comments' => $request->comments,
                ]);
                
                // Log activity
                ActivityLogService::logAction('refund_hod_rejected', "HOD rejected refund request {$refundRequest->request_no}", $refundRequest);
                
                // Notify staff
                $this->notificationService->notify(
                    $refundRequest->staff_id,
                    "Your refund request {$refundRequest->request_no} has been rejected by HOD. Reason: " . ($request->comments ?? 'No reason provided'),
                    route('refunds.show', $refundRequest->id),
                    'Refund Request Rejected'
                );
                
                $message = 'Refund request rejected successfully.';
            }
            
            DB::commit();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('refunds.show', $refundRequest->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process approval: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }

    /**
     * Accountant Verification
     */
    public function accountantVerify(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized. Only Accountant can verify refund requests.');
        }
        
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);
        
        $refundRequest = RefundRequest::findOrFail($id);
        
        if ($refundRequest->status !== 'pending_accountant') {
            return redirect()->back()->with('error', 'This request is not pending accountant verification.');
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->action === 'approve') {
                // IMPORTANT: All refund requests MUST go through CEO approval
                // Accountant can only verify, not approve. CEO approval is mandatory.
                $refundRequest->update([
                    'status' => 'pending_ceo', // Always goes to CEO - no exceptions
                    'accountant_verified_at' => now(),
                    'accountant_verified_by' => $user->id,
                    'accountant_comments' => $request->comments,
                ]);
                
                // Log activity
                ActivityLogService::logAction('refund_accountant_verified', "Accountant verified refund request {$refundRequest->request_no}. Now pending CEO approval.", $refundRequest);
                
                // Notify CEO - MANDATORY STEP
                $this->notifyCeo($refundRequest);
                
                // Notify staff
                $this->notificationService->notify(
                    $refundRequest->staff_id,
                    "Your refund request {$refundRequest->request_no} has been verified by Accountant and is now pending CEO approval. CEO approval is required before payment can be processed.",
                    route('refunds.show', $refundRequest->id),
                    'Refund Request Verified - Awaiting CEO Approval'
                );
                
                $message = 'Refund request verified successfully. It is now pending CEO approval. CEO approval is mandatory before payment can be processed.';
            } else {
                $refundRequest->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => $user->id,
                    'rejection_reason' => $request->comments,
                    'accountant_comments' => $request->comments,
                ]);
                
                // Log activity
                ActivityLogService::logAction('refund_accountant_rejected', "Accountant rejected refund request {$refundRequest->request_no}", $refundRequest);
                
                // Notify staff
                $this->notificationService->notify(
                    $refundRequest->staff_id,
                    "Your refund request {$refundRequest->request_no} has been rejected by Accountant. Reason: " . ($request->comments ?? 'No reason provided'),
                    route('refunds.show', $refundRequest->id),
                    'Refund Request Rejected'
                );
                
                $message = 'Refund request rejected successfully.';
            }
            
            DB::commit();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('refunds.show', $refundRequest->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process verification: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to process verification: ' . $e->getMessage());
        }
    }

    /**
     * CEO Approval - MANDATORY FINAL STEP
     * All refund requests MUST be approved by CEO before payment can be processed.
     * This is the final approval step in the refund workflow.
     */
    public function ceoApprove(Request $request, $id)
    {
        $user = Auth::user();
        
        // Only CEO, Director, or System Admin can approve
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            abort(403, 'Unauthorized. Only CEO/Director can give final approval. CEO approval is mandatory for all refund requests.');
        }
        
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);
        
        $refundRequest = RefundRequest::findOrFail($id);
        
        // Ensure request is in the correct status for CEO approval
        if ($refundRequest->status !== 'pending_ceo') {
            return redirect()->back()->with('error', 'This request is not pending CEO approval. Current status: ' . $refundRequest->status . '. CEO approval is required before any refund can be processed.');
        }
        
        // Additional validation: Ensure HOD and Accountant have approved
        if (!$refundRequest->hod_approved_at) {
            return redirect()->back()->with('error', 'This request has not been approved by HOD yet. CEO approval requires HOD approval first.');
        }
        
        if (!$refundRequest->accountant_verified_at) {
            return redirect()->back()->with('error', 'This request has not been verified by Accountant yet. CEO approval requires Accountant verification first.');
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->action === 'approve') {
                // CEO approval is the final step - only after this can payment be processed
                $refundRequest->update([
                    'status' => 'approved', // Only CEO can set this status
                    'ceo_approved_at' => now(),
                    'ceo_approved_by' => $user->id,
                    'ceo_comments' => $request->comments,
                ]);
                
                // Log activity
                ActivityLogService::logAction('refund_ceo_approved', "CEO approved refund request {$refundRequest->request_no}", $refundRequest);
                
                // Notify Accountant for payment processing
                $this->notifyAccountantForPayment($refundRequest);
                
                // Notify staff
                $this->notificationService->notify(
                    $refundRequest->staff_id,
                    "Your refund request {$refundRequest->request_no} has been approved by CEO. Payment will be processed soon.",
                    route('refunds.show', $refundRequest->id),
                    'Refund Request Approved'
                );
                
                $message = 'Refund request approved successfully. Payment can now be processed.';
            } else {
                $refundRequest->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => $user->id,
                    'rejection_reason' => $request->comments,
                    'ceo_comments' => $request->comments,
                ]);
                
                // Log activity
                ActivityLogService::logAction('refund_ceo_rejected', "CEO rejected refund request {$refundRequest->request_no}", $refundRequest);
                
                // Notify staff
                $this->notificationService->notify(
                    $refundRequest->staff_id,
                    "Your refund request {$refundRequest->request_no} has been rejected by CEO. Reason: " . ($request->comments ?? 'No reason provided'),
                    route('refunds.show', $refundRequest->id),
                    'Refund Request Rejected'
                );
                
                $message = 'Refund request rejected successfully.';
            }
            
            DB::commit();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->route('refunds.show', $refundRequest->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process approval: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }

    /**
     * Mark refund as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized. Only Accountant can mark refunds as paid.');
        }
        
        $request->validate([
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
            'payment_notes' => 'nullable|string',
        ]);
        
        $refundRequest = RefundRequest::findOrFail($id);
        
        // Payment can only be processed after CEO approval
        if ($refundRequest->status !== 'approved') {
            return redirect()->back()->with('error', 'This request must be approved by CEO before payment can be processed. Current status: ' . $refundRequest->status);
        }
        
        // Additional validation: Ensure CEO has approved
        if (!$refundRequest->ceo_approved_at || !$refundRequest->ceo_approved_by) {
            return redirect()->back()->with('error', 'CEO approval is required before payment can be processed. This request has not been approved by CEO yet.');
        }
        
        try {
            DB::beginTransaction();
            
            $refundRequest->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => $user->id,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'payment_notes' => $request->payment_notes,
            ]);
            
            // Log activity
            ActivityLogService::logAction('refund_paid', "Refund request {$refundRequest->request_no} marked as paid", $refundRequest);
            
            // Notify staff
            $this->notificationService->notify(
                $refundRequest->staff_id,
                "Your refund request {$refundRequest->request_no} has been paid. Payment Method: {$request->payment_method}",
                route('refunds.show', $refundRequest->id),
                'Refund Paid'
            );
            
            DB::commit();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Refund marked as paid successfully.'
                ]);
            }
            
            return redirect()->route('refunds.show', $refundRequest->id)
                ->with('success', 'Refund marked as paid successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark as paid: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to mark as paid: ' . $e->getMessage());
        }
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($id, $attachmentId)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HOD', 'Accountant', 'CEO', 'Director', 'HR Officer']);
        
        $refundRequest = RefundRequest::findOrFail($id);
        $attachment = RefundAttachment::findOrFail($attachmentId);
        
        // Check permissions
        if (!$isManager && $refundRequest->staff_id !== $user->id) {
            abort(403, 'You do not have permission to download this attachment');
        }
        
        if ($attachment->refund_request_id !== $refundRequest->id) {
            abort(404, 'Attachment not found');
        }
        
        $filePath = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->download($filePath, $attachment->file_name);
    }

    /**
     * Notify HOD
     */
    private function notifyHod(RefundRequest $refundRequest)
    {
        $hods = User::whereHas('roles', function($query) {
            $query->where('name', 'HOD');
        })->get();
        
        foreach ($hods as $hod) {
            $this->notificationService->notify(
                $hod->id,
                "New refund request {$refundRequest->request_no} from {$refundRequest->staff->name} for " . number_format($refundRequest->amount, 2) . " requires your approval.",
                route('refunds.show', $refundRequest->id),
                'New Refund Request'
            );
        }
    }

    /**
     * Notify Accountant
     */
    private function notifyAccountant(RefundRequest $refundRequest)
    {
        $accountants = User::whereHas('roles', function($query) {
            $query->where('name', 'Accountant');
        })->get();
        
        foreach ($accountants as $accountant) {
            $this->notificationService->notify(
                $accountant->id,
                "Refund request {$refundRequest->request_no} from {$refundRequest->staff->name} requires your verification.",
                route('refunds.show', $refundRequest->id),
                'Refund Request Pending Verification'
            );
        }
    }

    /**
     * Notify CEO
     */
    private function notifyCeo(RefundRequest $refundRequest)
    {
        $ceos = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['CEO', 'Director']);
        })->get();
        
        foreach ($ceos as $ceo) {
            $this->notificationService->notify(
                $ceo->id,
                "Refund request {$refundRequest->request_no} from {$refundRequest->staff->name} for " . number_format($refundRequest->amount, 2) . " requires your approval.",
                route('refunds.show', $refundRequest->id),
                'Refund Request Pending Approval'
            );
        }
    }

    /**
     * Notify Accountant for Payment
     */
    private function notifyAccountantForPayment(RefundRequest $refundRequest)
    {
        $accountants = User::whereHas('roles', function($query) {
            $query->where('name', 'Accountant');
        })->get();
        
        foreach ($accountants as $accountant) {
            $this->notificationService->notify(
                $accountant->id,
                "Refund request {$refundRequest->request_no} has been approved. Please process payment of " . number_format($refundRequest->amount, 2) . " to {$refundRequest->staff->name}.",
                route('refunds.show', $refundRequest->id),
                'Refund Payment Required'
            );
        }
    }
}
