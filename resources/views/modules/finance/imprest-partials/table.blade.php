<div class="card">
  <div class="card-body">
    @if(isset($requests) && $requests->count() > 0)
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Request #</th>
              <th>Accountant</th>
              <th>Purpose</th>
              <th>Amount</th>
              <th>Staff Assigned</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Created</th>
              @if($showActions ?? false)
              <th>Actions</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($requests as $req)
            <tr>
              <td><strong>{{ $req->request_no }}</strong></td>
              <td>{{ $req->accountant->name ?? '—' }}</td>
              <td>{{ Str::limit($req->purpose, 40) }}</td>
              <td><strong>TZS {{ number_format($req->amount, 2) }}</strong></td>
              <td>
                @if($req->assignments->count() > 0)
                  <span class="badge bg-info">{{ $req->assignments->count() }} staff</span>
                @else
                  <span class="badge bg-secondary">Not assigned</span>
                @endif
              </td>
              <td>
                @php
                  $badgeClass = match($req->status) {
                    'pending_hod' => 'warning',
                    'pending_ceo' => 'info',
                    'approved' => 'success',
                    'assigned' => 'primary',
                    'paid' => 'success',
                    'pending_receipt_verification' => 'warning',
                    'completed' => 'dark',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $req->status)) }}</span>
              </td>
              <td>
                @php
                  $progress = match($req->status) {
                    'pending_hod' => 20,
                    'pending_ceo' => 40,
                    'approved' => 60,
                    'assigned' => 70,
                    'paid' => 80,
                    'pending_receipt_verification' => 90,
                    'completed' => 100,
                    default => 0
                  };
                @endphp
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">{{ $progress }}%</div>
                </div>
              </td>
              <td>{{ $req->created_at?->format('M d, Y') }}</td>
              @if($showActions ?? false)
              <td>
                @php
                  $isSystemAdmin = auth()->user()->hasRole('System Admin');
                  $isHOD = auth()->user()->hasAnyRole(['HOD', 'System Admin']);
                  $isCEO = auth()->user()->hasAnyRole(['CEO', 'Director', 'System Admin']);
                  $isAccountant = auth()->user()->hasAnyRole(['Accountant', 'System Admin']);
                  
                  // Check if actions have already been performed
                  $hodAlreadyApproved = !is_null($req->hod_approved_at);
                  $ceoAlreadyApproved = !is_null($req->ceo_approved_at);
                  $hasAssignments = $req->assignments->count() > 0;
                  $paymentAlreadyProcessed = !is_null($req->paid_at);
                  
                  // System Admin can approve at any level regardless of status or actionType
                  // But only if the action hasn't been performed yet
                  $canHodApprove = false;
                  if (!$hodAlreadyApproved) {
                      if ($isSystemAdmin) {
                          // System Admin can approve HOD and CEO levels at any status (except completed)
                          $canHodApprove = !in_array($req->status, ['completed']);
                      } else {
                          // Regular users follow normal flow based on actionType and status
                          $canHodApprove = $isHOD && (($actionType ?? '') === 'hod' || ($actionType ?? '') === 'all') && $req->status === 'pending_hod';
                      }
                      
                      // Accountant can also approve HOD level if status hasn't progressed
                      if (!$canHodApprove && $isAccountant && !$isSystemAdmin) {
                          if (in_array($req->status, ['pending_hod', 'pending_ceo']) && (($actionType ?? '') === 'hod' || ($actionType ?? '') === 'all')) {
                              $canHodApprove = true;
                          }
                      }
                  }
                  
                  $canCeoApprove = false;
                  if (!$ceoAlreadyApproved) {
                      if ($isSystemAdmin) {
                          $canCeoApprove = !in_array($req->status, ['completed']);
                      } else {
                          $canCeoApprove = $isCEO && (($actionType ?? '') === 'ceo' || ($actionType ?? '') === 'all') && $req->status === 'pending_ceo';
                      }
                  }
                  
                  $canAssignStaff = false;
                  if (!$hasAssignments) {
                      $canAssignStaff = $isAccountant && (($actionType ?? '') === 'assign' || ($actionType ?? '') === 'all') && $req->status === 'approved';
                  }
                  
                  $canProcessPayment = false;
                  if (!$paymentAlreadyProcessed) {
                      $canProcessPayment = $isAccountant && (($actionType ?? '') === 'payment' || ($actionType ?? '') === 'all') && $req->status === 'assigned';
                  }
                  
                  // Check for unverified receipts
                  $hasUnverifiedReceipts = false;
                  if ($req->assignments) {
                      foreach ($req->assignments as $assignment) {
                          if ($assignment->receipts) {
                              foreach ($assignment->receipts as $receipt) {
                                  if (!$receipt->is_verified) {
                                      $hasUnverifiedReceipts = true;
                                      break 2;
                                  }
                              }
                          }
                      }
                  }
                  
                  $canVerifyReceipts = false;
                  if ($hasUnverifiedReceipts || $req->status === 'pending_receipt_verification') {
                      $canVerifyReceipts = $isAccountant && (($actionType ?? '') === 'verify' || ($actionType ?? '') === 'all') && $req->status === 'pending_receipt_verification';
                  }
                @endphp
                <div class="btn-group btn-group-sm" role="group">
                  <a href="{{ route('imprest.show', $req->id) }}" class="btn btn-outline-primary" title="View Details">
                    <i class="bx bx-show"></i>
                  </a>
                  
                  @if($canHodApprove)
                  <button class="btn btn-outline-success" onclick="hodApprove({{ $req->id }})" title="Approve">
                    <i class="bx bx-check"></i>
                  </button>
                  @endif

                  @if($canCeoApprove)
                  <button class="btn btn-outline-success" onclick="ceoApprove({{ $req->id }})" title="Final Approval">
                    <i class="bx bx-check-double"></i>
                  </button>
                  @endif

                  @if($canAssignStaff)
                  <button class="btn btn-outline-info" onclick="openAssignStaff({{ $req->id }})" title="Assign Staff">
                    <i class="bx bx-user-plus"></i>
                  </button>
                  @endif

                  @if($canProcessPayment)
                  <button class="btn btn-outline-success" onclick="openPayment({{ $req->id }})" title="Process Payment">
                    <i class="bx bx-money"></i>
                  </button>
                  @endif

                  @if($canVerifyReceipts)
                  <button class="btn btn-outline-warning" onclick="viewReceiptsForVerification({{ $req->id }})" title="Verify Receipts">
                    <i class="bx bx-check-circle"></i>
                  </button>
                  @endif

                  <a href="{{ route('imprest.pdf', $req->id) }}" class="btn btn-outline-danger" target="_blank" title="Download PDF">
                    <i class="bx bx-file-blank"></i>
                  </a>
                </div>
              </td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if(method_exists($requests, 'links'))
      <div class="d-flex justify-content-center mt-3">
        {{ $requests->links() }}
      </div>
      @endif
    @else
      <div class="text-center py-5 text-muted">
        <i class="bx bx-inbox" style="font-size: 4rem;"></i>
        <h5>No requests found</h5>
        <p>There are no imprest requests in this category.</p>
      </div>
    @endif
  </div>
</div>

