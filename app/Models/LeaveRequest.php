<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'hr_note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days_count' => 'float',
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

    public function leaveTypeLabel(): string
    {
        return match ($this->leave_type) {
            'full_day' => 'Nghỉ cả ngày (1 ngày)',
            'half_day_morning' => 'Nghỉ nửa buổi sáng (0.5 ngày)',
            'half_day_afternoon' => 'Nghỉ nửa buổi chiều (0.5 ngày)',
            default => 'Nghỉ phép',
        };
    }

    public function isHalfDay(): bool
    {
        return in_array($this->leave_type, ['half_day_morning', 'half_day_afternoon'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            default => $this->status,
        };
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            'pending' => 'pending',
            'approved' => 'completed',
            'rejected' => 'rejected',
            default => 'neutral',
        };
    }
}
