<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['code' => 'EXP-001', 'name' => 'Cleaning and sanitation', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 1],
            ['code' => 'EXP-002', 'name' => 'Printing and stationery', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 2],
            ['code' => 'EXP-003', 'name' => 'Equipment and furniture repair', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 3],
            ['code' => 'EXP-004', 'name' => 'Postage and telephone', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 4],
            ['code' => 'EXP-005', 'name' => 'Utilities', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 5],
            ['code' => 'EXP-006', 'name' => 'Office rent', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 6],
            ['code' => 'EXP-007', 'name' => 'Licence and insurance', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 7],
            ['code' => 'EXP-008', 'name' => 'Salaries and wages', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 8],
            ['code' => 'EXP-009', 'name' => 'NSSF contribution', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 9],
            ['code' => 'EXP-010', 'name' => 'NHIF contribution', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 10],
            ['code' => 'EXP-011', 'name' => 'WCF contribution', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 11],
            ['code' => 'EXP-012', 'name' => 'Marketing/Information dissemination', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 12],
            ['code' => 'EXP-013', 'name' => 'Traveling & Transportation', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 13],
            ['code' => 'EXP-014', 'name' => 'Staff welfare', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 14],
            ['code' => 'EXP-015', 'name' => 'Staff uniform', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 15],
            ['code' => 'EXP-016', 'name' => 'Tax liabilities', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 16],
            ['code' => 'EXP-017', 'name' => 'Staff seminar Training', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 17],
            ['code' => 'EXP-018', 'name' => 'Annual Registration fees', 'type' => 'Expense', 'category' => 'Operating Expense', 'sort_order' => 18],
        ];

        foreach ($accounts as $accountData) {
            // Check if account already exists
            $existing = ChartOfAccount::where('code', $accountData['code'])->first();
            
            if (!$existing) {
                ChartOfAccount::create(array_merge($accountData, [
                    'is_active' => true,
                    'is_system' => false,
                    'opening_balance' => 0,
                ]));
            } else {
                // Update if exists but name is different
                if ($existing->name !== $accountData['name']) {
                    $existing->update(['name' => $accountData['name']]);
                }
            }
        }
    }
}
