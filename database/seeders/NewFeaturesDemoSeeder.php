<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\VehicleRegistration;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NewFeaturesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $hrUser = User::where('role', 'hr')->first();
        $employees = Employee::all();

        if ($employees->isEmpty()) {
            return;
        }

        $emp1 = $employees->first();
        $emp2 = $employees->count() > 1 ? $employees->skip(1)->first() : $emp1;

        // 1. Seed demo Leave Requests
        LeaveRequest::firstOrCreate(
            [
                'employee_id' => $emp1->id,
                'start_date' => now()->subDays(5)->toDateString(),
            ],
            [
                'leave_type' => 'half_day_morning',
                'end_date' => now()->subDays(5)->toDateString(),
                'days_count' => 0.5,
                'reason' => 'Đưa người nhà đi khám bệnh buổi sáng, buổi chiều vẫn đi làm bình thường.',
                'status' => 'approved',
                'approved_by' => $hrUser?->id,
                'approved_at' => now()->subDays(6),
                'hr_note' => 'Đã duyệt nghỉ nửa buổi sáng. Vẫn tính công nửa buổi chiều và phụ cấp ăn trưa.',
            ]
        );

        LeaveRequest::firstOrCreate(
            [
                'employee_id' => $emp1->id,
                'start_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'leave_type' => 'full_day',
                'end_date' => now()->addDays(3)->toDateString(),
                'days_count' => 1.0,
                'reason' => 'Có việc gia đình ở quê cần giải quyết.',
                'status' => 'pending',
            ]
        );

        if ($emp2->id !== $emp1->id) {
            LeaveRequest::firstOrCreate(
                [
                    'employee_id' => $emp2->id,
                    'start_date' => now()->subDays(2)->toDateString(),
                ],
                [
                    'leave_type' => 'half_day_afternoon',
                    'end_date' => now()->subDays(2)->toDateString(),
                    'days_count' => 0.5,
                    'reason' => 'Đi thi chứng chỉ tin học buổi chiều.',
                    'status' => 'approved',
                    'approved_by' => $hrUser?->id,
                    'approved_at' => now()->subDays(3),
                    'hr_note' => 'Đồng ý cho nghỉ nửa buổi chiều.',
                ]
            );
        }

        // 2. Seed demo Vehicle Registrations
        VehicleRegistration::firstOrCreate(
            [
                'license_plate' => '59-S3 888.68',
            ],
            [
                'employee_id' => $emp1->id,
                'vehicle_type' => 'motorbike',
                'brand_model' => 'Honda SH 150i',
                'color' => 'Trắng ngọc trai',
                'card_number' => 'PK-HUIT-0101',
                'status' => 'active',
                'note' => 'Đã cấp thẻ tháng bãi xe nhà A.',
                'registered_date' => now()->subMonths(1)->toDateString(),
                'approved_by' => $hrUser?->id,
                'approved_at' => now()->subMonths(1),
            ]
        );

        VehicleRegistration::firstOrCreate(
            [
                'license_plate' => '51K-999.88',
            ],
            [
                'employee_id' => $emp1->id,
                'vehicle_type' => 'car',
                'brand_model' => 'Mazda CX-5',
                'color' => 'Đỏ pha lê',
                'card_number' => 'PK-HUIT-CAR-01',
                'status' => 'active',
                'note' => 'Đậu bãi xe ô tô sân trường cổng chính.',
                'registered_date' => now()->subMonths(2)->toDateString(),
                'approved_by' => $hrUser?->id,
                'approved_at' => now()->subMonths(2),
            ]
        );

        if ($emp2->id !== $emp1->id) {
            VehicleRegistration::firstOrCreate(
                [
                    'license_plate' => '60-B1 123.45',
                ],
                [
                    'employee_id' => $emp2->id,
                    'vehicle_type' => 'motorbike',
                    'brand_model' => 'Yamaha Grande',
                    'color' => 'Xanh xi măng',
                    'card_number' => null,
                    'status' => 'pending',
                    'note' => 'Mới nộp đăng ký thẻ xe tháng.',
                    'registered_date' => now()->toDateString(),
                ]
            );
        }

        // 3. Seed / Calculate payrolls for current month
        $payrollService = app(PayrollCalculationService::class);
        $payrollService->calculateAll(now()->month, now()->year, $hrUser?->id);
    }
}
