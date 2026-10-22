<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\VehicleRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrParkingController extends Controller
{
    public function index(Request $request): View
    {
        $query = VehicleRegistration::with(['employee.department', 'employee.position', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->string('vehicle_type')->toString());
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $request->integer('department_id')));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                    ->orWhere('card_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($emp) => $emp->where('full_name', 'like', "%{$search}%")->orWhere('employee_code', 'like', "%{$search}%"));
            });
        }

        $allVehicles = VehicleRegistration::all();
        $vehicles = $query->latest()->paginate(15)->withQueryString();

        return view('hr.parking.index', [
            'role' => 'hr',
            'active' => 'parking',
            'title' => 'Quản lý gửi xe',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Quản lý phương tiện & thẻ gửi xe',
            'vehicles' => $vehicles,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'totalCount' => $allVehicles->count(),
            'pendingCount' => $allVehicles->where('status', 'pending')->count(),
            'activeCount' => $allVehicles->where('status', 'active')->count(),
            'motorbikeCount' => $allVehicles->where('vehicle_type', 'motorbike')->count(),
            'carCount' => $allVehicles->where('vehicle_type', 'car')->count(),
        ]);
    }

    public function show(VehicleRegistration $vehicle): View
    {
        $vehicle->load(['employee.department', 'employee.position', 'approver']);

        return view('hr.parking.show', [
            'role' => 'hr',
            'active' => 'parking',
            'title' => 'Chi tiết phương tiện gửi xe',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Cấp thẻ và quản lý phương tiện',
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, VehicleRegistration $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'card_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,active,suspended,revoked'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $updatePayload = [
            'card_number' => $data['card_number'] ?? $vehicle->card_number,
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
        ];

        if ($data['status'] === 'active' && !$vehicle->approved_at) {
            $updatePayload['approved_by'] = $request->user()->id;
            $updatePayload['approved_at'] = now();
        }

        $vehicle->update($updatePayload);

        return redirect()->back()->with('success', 'Đã cập nhật thông tin thẻ gửi xe thành công.');
    }

    public function destroy(VehicleRegistration $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('hr.parking.index')->with('success', 'Đã xóa hồ sơ phương tiện gửi xe.');
    }
}
