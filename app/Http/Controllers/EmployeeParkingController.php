<?php

namespace App\Http\Controllers;

use App\Models\VehicleRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeParkingController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        $vehicles = VehicleRegistration::where('employee_id', $employee->id)
            ->latest()
            ->get();

        return view('employee.parking.index', [
            'role' => 'employee',
            'active' => 'parking',
            'title' => 'Quản lý gửi xe',
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => 'Phương tiện & Vé xe của bạn',
            'employee' => $employee,
            'vehicles' => $vehicles,
            'totalVehicles' => $vehicles->count(),
            'activeVehicles' => $vehicles->where('status', 'active')->count(),
            'pendingVehicles' => $vehicles->where('status', 'pending')->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        return view('employee.parking.form', [
            'role' => 'employee',
            'active' => 'parking',
            'title' => 'Đăng ký gửi xe',
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => 'Đăng ký vé xe / thẻ xe',
            'employee' => $employee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Tài khoản chưa được liên kết với nhân viên.');

        $data = $request->validate([
            'license_plate' => ['required', 'string', 'max:30'],
            'vehicle_type' => ['required', 'in:motorbike,car,electric_bike,bicycle,other'],
            'brand_model' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        VehicleRegistration::create([
            'employee_id' => $employee->id,
            'license_plate' => strtoupper(trim($data['license_plate'])),
            'vehicle_type' => $data['vehicle_type'],
            'brand_model' => $data['brand_model'] ?? null,
            'color' => $data['color'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
            'registered_date' => now()->toDateString(),
        ]);

        return redirect()->route('employee.parking.index')->with('success', 'Đã nộp đăng ký thẻ gửi xe thành công. Vui lòng chờ HR kiểm tra và cấp thẻ.');
    }

    public function destroy(Request $request, VehicleRegistration $vehicle): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $vehicle->employee_id === $employee->id, 403);
        abort_unless($vehicle->status === 'pending', 400, 'Chỉ có thể hủy đăng ký xe đang chờ duyệt.');

        $vehicle->delete();

        return redirect()->route('employee.parking.index')->with('success', 'Đã hủy đơn đăng ký gửi xe.');
    }
}
