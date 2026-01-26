<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ActivityLogService;

class AccountsReceivableApiController extends Controller
{
    /**
     * Get all invoices (paginated)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO', 'HOD', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Invoice::with(['customer:id,name,email,phone', 'hodApprover:id,name', 'ceoApprover:id,name']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $invoices->map(function ($invoice) {
                return $this->formatInvoice($invoice);
            }),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'last_page' => $invoices->lastPage(),
            ]
        ]);
    }

    /**
     * Get invoice details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO', 'HOD', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $invoice = Invoice::with([
                'customer',
                'items.account',
                'payments.bankAccount',
                'payments.creator',
                'hodApprover',
                'ceoApprover',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $this->formatInvoice($invoice, true)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Approve invoice (HOD or CEO)
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // Check if user has HOD or CEO role
        if (!$user->hasAnyRole(['HOD', 'CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HOD, CEO, or System Admin can approve invoices.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = Invoice::findOrFail($id);
            $comments = $request->input('comments');
            
            // Determine approval level
            $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
            $isCEO = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
            
            // HOD approval
            if ($isHOD && !$isCEO && $invoice->status === 'Pending for Approval') {
                if (!$isSystemAdmin && !$user->hasRole('HOD')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. Only HOD can approve at this level.'
                    ], 403);
                }

                $oldStatus = $invoice->status;
                $invoice->status = 'Pending CEO Approval';
                $invoice->hod_approved_at = now();
                $invoice->hod_approved_by = Auth::id();
                $invoice->hod_comments = $comments;
                $invoice->updated_by = Auth::id();
                $invoice->save();

                // Log activity
                if (class_exists(ActivityLogService::class)) {
                    ActivityLogService::logApproved($invoice, "Invoice #{$invoice->invoice_no} approved by HOD", Auth::user()->name, [
                        'old_status' => $oldStatus,
                        'new_status' => 'Pending CEO Approval',
                        'invoice_no' => $invoice->invoice_no,
                        'invoice_amount' => $invoice->total_amount,
                    ]);
                }

                // Notify CEO for approval
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $link = route('modules.accounting.ar.invoices.show', ['id' => $invoice->id]);
                    $notificationService->notifyCEO(
                        "Invoice {$invoice->invoice_no} approved by HOD, pending your approval",
                        $link,
                        'Invoice Pending CEO Approval'
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice approved by HOD. Waiting for CEO approval.',
                    'data' => $this->formatInvoice($invoice->load(['customer', 'hodApprover', 'ceoApprover']))
                ]);
            }
            
            // CEO approval
            if ($isCEO && $invoice->status === 'Pending CEO Approval') {
                if (!$isSystemAdmin && !$user->hasAnyRole(['CEO', 'Director'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized. Only CEO/Director can give final approval.'
                    ], 403);
                }

                $oldStatus = $invoice->status;
                $invoice->status = 'Approved';
                $invoice->ceo_approved_at = now();
                $invoice->ceo_approved_by = Auth::id();
                $invoice->ceo_comments = $comments;
                $invoice->updated_by = Auth::id();
                $invoice->save();

                // Update status to Sent
                $invoice->updateStatus();

                // Log activity
                if (class_exists(ActivityLogService::class)) {
                    ActivityLogService::logApproved($invoice, "Invoice #{$invoice->invoice_no} approved by CEO", Auth::user()->name, [
                        'old_status' => $oldStatus,
                        'new_status' => 'Approved',
                        'invoice_no' => $invoice->invoice_no,
                        'invoice_amount' => $invoice->total_amount,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice approved successfully by CEO. Invoice can now be paid.',
                    'data' => $this->formatInvoice($invoice->load(['customer', 'hodApprover', 'ceoApprover']))
                ]);
            }
            
            // System Admin can approve directly
            if ($isSystemAdmin && $invoice->status === 'Pending for Approval') {
                $oldStatus = $invoice->status;
                $invoice->status = 'Approved';
                $invoice->hod_approved_at = now();
                $invoice->hod_approved_by = Auth::id();
                $invoice->ceo_approved_at = now();
                $invoice->ceo_approved_by = Auth::id();
                $invoice->hod_comments = $comments ?? 'Approved by System Admin';
                $invoice->updated_by = Auth::id();
                $invoice->save();

                $invoice->updateStatus();

                if (class_exists(ActivityLogService::class)) {
                    ActivityLogService::logApproved($invoice, "Invoice #{$invoice->invoice_no} approved by System Admin", Auth::user()->name, [
                        'old_status' => $oldStatus,
                        'new_status' => 'Approved',
                        'invoice_no' => $invoice->invoice_no,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice approved successfully',
                    'data' => $this->formatInvoice($invoice->load(['customer', 'hodApprover', 'ceoApprover']))
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invoice is not pending approval or already processed'
            ], 400);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error approving invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject invoice
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->hasAnyRole(['HOD', 'CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HOD, CEO, or System Admin can reject invoices.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = Invoice::findOrFail($id);
            
            if (!in_array($invoice->status, ['Pending for Approval', 'Pending CEO Approval'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is not pending approval'
                ], 400);
            }

            $oldStatus = $invoice->status;
            $rejectionReason = $request->rejection_reason;
            $invoice->status = 'Rejected';
            $invoice->notes = ($invoice->notes ?? '') . "\n\nRejected: " . $rejectionReason;
            $invoice->updated_by = Auth::id();
            $invoice->save();

            // Log activity
            if (class_exists(ActivityLogService::class)) {
                ActivityLogService::logRejected($invoice, "Invoice #{$invoice->invoice_no} rejected", Auth::user()->name, $rejectionReason, [
                    'old_status' => $oldStatus,
                    'new_status' => 'Rejected',
                    'invoice_no' => $invoice->invoice_no,
                    'invoice_amount' => $invoice->total_amount,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice rejected successfully',
                'data' => $this->formatInvoice($invoice->load(['customer', 'hodApprover', 'ceoApprover']))
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error rejecting invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending invoices for approval
     */
    public function pending(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO', 'HOD', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Invoice::with(['customer:id,name,email,phone', 'hodApprover:id,name', 'ceoApprover:id,name']);

        // Filter based on user role
        if ($user->hasAnyRole(['HOD', 'System Admin'])) {
            $query->where('status', 'Pending for Approval');
        } elseif ($user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            $query->where('status', 'Pending CEO Approval');
        } else {
            $query->whereIn('status', ['Pending for Approval', 'Pending CEO Approval']);
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $invoices->map(function ($invoice) {
                return $this->formatInvoice($invoice);
            }),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'last_page' => $invoices->lastPage(),
            ]
        ]);
    }

    /**
     * Format invoice for API response
     */
    private function formatInvoice($invoice, $detailed = false)
    {
        $data = [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'customer_id' => $invoice->customer_id,
            'customer' => $invoice->customer ? [
                'id' => $invoice->customer->id,
                'name' => $invoice->customer->name,
                'email' => $invoice->customer->email,
                'phone' => $invoice->customer->phone,
            ] : null,
            'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : null,
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
            'reference_no' => $invoice->reference_no,
            'subtotal' => (float) $invoice->subtotal,
            'tax_amount' => (float) $invoice->tax_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'total_amount' => (float) $invoice->total_amount,
            'paid_amount' => (float) $invoice->paid_amount,
            'balance' => (float) $invoice->balance,
            'status' => $invoice->status,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'created_at' => $invoice->created_at->toIso8601String(),
        ];

        // Add approval information
        if ($invoice->hod_approved_at) {
            $data['hod_approval'] = [
                'approved_at' => $invoice->hod_approved_at->toIso8601String(),
                'approved_by' => $invoice->hodApprover ? [
                    'id' => $invoice->hodApprover->id,
                    'name' => $invoice->hodApprover->name,
                ] : null,
                'comments' => $invoice->hod_comments,
            ];
        }

        if ($invoice->ceo_approved_at) {
            $data['ceo_approval'] = [
                'approved_at' => $invoice->ceo_approved_at->toIso8601String(),
                'approved_by' => $invoice->ceoApprover ? [
                    'id' => $invoice->ceoApprover->id,
                    'name' => $invoice->ceoApprover->name,
                ] : null,
                'comments' => $invoice->ceo_comments,
            ];
        }

        if ($detailed) {
            // Add items
            $data['items'] = $invoice->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                    'account' => $item->account ? [
                        'id' => $item->account->id,
                        'name' => $item->account->name,
                        'code' => $item->account->code,
                    ] : null,
                ];
            });

            // Add payments
            $data['payments'] = $invoice->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'reference' => $payment->reference,
                ];
            });

            // Add creator/updater info
            $data['creator'] = $invoice->creator ? [
                'id' => $invoice->creator->id,
                'name' => $invoice->creator->name,
            ] : null;

            $data['updater'] = $invoice->updater ? [
                'id' => $invoice->updater->id,
                'name' => $invoice->updater->name,
            ] : null;
        }

        return $data;
    }
}

