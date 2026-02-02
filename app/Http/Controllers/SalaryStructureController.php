<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class SalaryStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'Director', 'CEO'])) {
            abort(403);
        }

        $salaryStructures = SalaryStructure::with(['department', 'position'])
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return view('modules.hr.salary-structures', compact('salaryStructures', 'departments', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:salary_structures,code',
            'description' => 'nullable|string',
            'min_salary' => 'required|numeric|min:0',
            'max_salary' => 'required|numeric|gte:min_salary',
            'basic_salary' => 'required|numeric|min:0|lte:max_salary',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'qualifications' => 'nullable|array',
            'allowances' => 'nullable|array',
            'deductions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = $user->id;
        $validated['is_active'] = $request->has('is_active'); // Checkbox handling

        $salaryStructure = SalaryStructure::create($validated);

        ActivityLogService::logCreated($salaryStructure, "Created Salary Structure: {$salaryStructure->name}", [
            'name' => $salaryStructure->name,
            'min_salary' => $salaryStructure->min_salary,
            'max_salary' => $salaryStructure->max_salary
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salary Structure created successfully.',
            'data' => $salaryStructure
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryStructure $salaryStructure)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'Director', 'CEO'])) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $salaryStructure->load(['department', 'position', 'creator']);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $salaryStructure
            ]);
        }
        
        // Return view if needed in future
        return response()->json(['success' => false, 'message' => 'Not implemented view yet.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:salary_structures,code,' . $salaryStructure->id,
            'description' => 'nullable|string',
            'min_salary' => 'required|numeric|min:0',
            'max_salary' => 'required|numeric|gte:min_salary',
            'basic_salary' => 'required|numeric|min:0|lte:max_salary',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'qualifications' => 'nullable|array',
            'allowances' => 'nullable|array',
            'deductions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = $user->id;
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        // Handle checkbox - if not present in request (unchecked), it should be false? 
        // Or if we use AJAX JSON payload, we should expect boolean.
        // Assuming JSON payload for consistency with store:
        if ($request->wantsJson()) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $oldValues = $salaryStructure->toArray();
        $salaryStructure->update($validated);

        ActivityLogService::logUpdated($salaryStructure, $oldValues, $salaryStructure->getChanges(), "Updated Salary Structure: {$salaryStructure->name}");

        return response()->json([
            'success' => true,
            'message' => 'Salary Structure updated successfully.',
            'data' => $salaryStructure
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryStructure $salaryStructure)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check for dependencies (Recruitment Jobs, Institutional Positions, Employees)
        if ($salaryStructure->employees()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: Structure is assigned to employees.'], 422);
        }
        
        if ($salaryStructure->institutionalPositions()->exists()) {
             return response()->json(['success' => false, 'message' => 'Cannot delete: Structure is assigned to institutional positions.'], 422);
        }

        ActivityLogService::logAction('salary_structure_deleted', "Deleted Salary Structure: {$salaryStructure->name}", $salaryStructure);
        
        $salaryStructure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Salary Structure deleted successfully.'
        ]);
    }
}
