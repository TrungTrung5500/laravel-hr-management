<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'license_plate',
        'vehicle_type',
        'brand_model',
        'color',
        'card_number',
        'status',
        'note',
        'registered_date',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function vehicleTypeLabel(): string
    {
        return match ($this->vehicle_type) {
            'motorbike' => 'Xe máy',
            'car' => 'Ô tô',
            'electric_bike' => 'Xe đạp điện / Máy điện',
            'bicycle' => 'Xe đạp',
            default => 'Phương tiện khác',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt / Cấp thẻ',
            'active' => 'Đang hoạt động',
            'suspended' => 'Tạm ngưng',
            'revoked' => 'Đã thu hồi',
            default => $this->status,
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            'pending' => 'pending',
            'active' => 'completed',
            'suspended' => 'in_progress',
            'revoked' => 'rejected',
            default => 'neutral',
        };
    }
}
