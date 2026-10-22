<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use App\Models\VehicleRegistration;
use App\Services\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class NewFeaturesTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    public function test_leave_management_flows(): void
    {
        $employee = $this->createEmployee();
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $hrUser = User::factory()->create(['role' => UserRole::HR]);
        $hrEmployee = $this->createEmployee(['role' => UserRole::HR], ['user_id' => $hrUser->id]);
        Attendance::create([
            'employee_id' => $hrEmployee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        // Employee views leave page
        $response = $this->actingAs($employee->user)->get(route('employee.leaves.index'));
        $response->assertStatus(200);
        $response->assertSee('Đơn nghỉ phép');

        // Employee creates leave request
        $response = $this->actingAs($employee->user)->post(route('employee.leaves.store'), [
            'leave_type' => 'half_day_morning',
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'reason' => 'Khám sức khỏe định kỳ buổi sáng',
        ]);
        $response->assertRedirect(route('employee.leaves.index'));

        $leave = LeaveRequest::where('employee_id', $employee->id)->where('leave_type', 'half_day_morning')->first();
        $this->assertNotNull($leave);
        $this->assertEquals(0.5, $leave->days_count);

        // HR views and approves leave
        $response = $this->actingAs($hrUser)->get(route('hr.leaves.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($hrUser)->patch(route('hr.leaves.update', $leave), [
            'status' => 'approved',
            'hr_note' => 'Đồng ý duyệt nghỉ nửa buổi sáng.',
        ]);
        $this->assertEquals('approved', $leave->fresh()->status);
    }

    public function test_payroll_calculation_with_half_day_leave(): void
    {
        $employee = $this->createEmployee();
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $hrUser = User::factory()->create(['role' => UserRole::HR]);
        $hrEmployee = $this->createEmployee(['role' => UserRole::HR], ['user_id' => $hrUser->id]);
        Attendance::create([
            'employee_id' => $hrEmployee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $service = app(PayrollCalculationService::class);
        $payroll = $service->calculateForEmployee($employee, (int) now()->month, (int) now()->year);

        $this->assertNotNull($payroll);
        $this->assertGreaterThan(0, $payroll->net_salary);
        $this->assertGreaterThan(0, $payroll->meal_allowance_total);

        // Employee views payroll
        $response = $this->actingAs($employee->user)->get(route('employee.payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Bảng lương');

        $response = $this->actingAs($employee->user)->get(route('employee.payroll.show', $payroll));
        $response->assertStatus(200);
        $response->assertSee('PHIẾU LƯƠNG');

        // HR views payroll
        $response = $this->actingAs($hrUser)->get(route('hr.payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Bảng lương Tháng');
    }

    public function test_parking_management_flows(): void
    {
        $employee = $this->createEmployee();
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $hrUser = User::factory()->create(['role' => UserRole::HR]);
        $hrEmployee = $this->createEmployee(['role' => UserRole::HR], ['user_id' => $hrUser->id]);
        Attendance::create([
            'employee_id' => $hrEmployee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        // Employee views parking
        $response = $this->actingAs($employee->user)->get(route('employee.parking.index'));
        $response->assertStatus(200);
        $response->assertSee('Phương tiện');

        // Employee registers a vehicle
        $response = $this->actingAs($employee->user)->post(route('employee.parking.store'), [
            'license_plate' => '59-P1 999.99',
            'vehicle_type' => 'motorbike',
            'brand_model' => 'Honda AirBlade',
            'color' => 'Đen nhám',
            'note' => 'Xe mới mua',
        ]);
        $response->assertRedirect(route('employee.parking.index'));

        $vehicle = VehicleRegistration::where('license_plate', '59-P1 999.99')->first();
        $this->assertNotNull($vehicle);

        // HR views and approves vehicle
        $response = $this->actingAs($hrUser)->get(route('hr.parking.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($hrUser)->patch(route('hr.parking.update', $vehicle), [
            'card_number' => 'PK-HUIT-999',
            'status' => 'active',
            'note' => 'Đã cấp thẻ tháng',
        ]);
        $this->assertEquals('active', $vehicle->fresh()->status);
        $this->assertEquals('PK-HUIT-999', $vehicle->fresh()->card_number);
    }
}
