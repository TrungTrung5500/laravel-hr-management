@extends('layouts.hr')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div class="stitch-context">
            <span>CỔNG NHÂN SỰ</span>
            <span class="stitch-context-slash">/</span>
            <a href="{{ route('hr.payroll.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}" style="color:inherit; text-decoration:none;">Quản lý bảng lương</a>
            <span class="stitch-context-slash">/</span>
            <span>Chi tiết lương #PL-{{ $payroll->id }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="notice" style="background:#e4f8f0; color:#087f5b; border:1px solid #a3e6cd; margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="section-head" style="margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:26px; color:#002d5d;">Phiếu lương Tháng {{ $payroll->month }}/{{ $payroll->year }}</h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Nhân viên: <strong>{{ $payroll->employee?->full_name }}</strong> ({{ $payroll->employee?->employee_code }}) · Trạng thái: <span class="stitch-status-pill {{ $payroll->statusClass() }}">{{ $payroll->statusLabel() }}</span></p>
        </div>
        <div class="toolbar">
            <button class="button" onclick="window.print()" type="button" style="background:#475569;">
                <span class="material-symbols-outlined">print</span> In phiếu lương
            </button>
            <a class="button light" href="{{ route('hr.payroll.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}">
                <span class="material-symbols-outlined">arrow_back</span> Quay lại bảng lương
            </a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns:minmax(0, 1.4fr) minmax(320px, 1fr); gap:24px;">
        <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Thông tin nhân sự</h2>
                <div class="stitch-key-list">
                    <div>
                        <span>Họ và tên nhân sự:</span>
                        <strong>{{ $payroll->employee?->full_name }}</strong>
                    </div>
                    <div>
                        <span>Mã số nhân viên:</span>
                        <strong>{{ $payroll->employee?->employee_code }}</strong>
                    </div>
                    <div>
                        <span>Phòng ban:</span>
                        <strong>{{ $payroll->employee?->department?->name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Chức danh:</span>
                        <strong>{{ $payroll->employee?->position?->name ?? '—' }}</strong>
                    </div>
                </div>
            </section>

            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Bóc tách công & Chi tiết thu nhập</h2>

                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#1e40af; line-height:1.5;">
                    <strong style="display:block; margin-bottom:4px;">Chính sách tính công nghỉ phép:</strong>
                    - Nghỉ nửa buổi (đã duyệt): Tính <strong>0.5 công</strong> cho buổi đi làm còn lại.<br>
                    - Phụ cấp tiền ăn: Chi trả đầy đủ <strong>1 suất tiền ăn</strong> cho buổi làm việc còn lại trong ngày.
                </div>

                <div class="stitch-key-list">
                    <div>
                        <span>Mức lương cơ bản tháng:</span>
                        <strong>{{ number_format($payroll->base_salary, 0, ',', '.') }} đ</strong>
                    </div>
                    <div>
                        <span>Số ngày công chuẩn trong tháng:</span>
                        <strong>{{ $payroll->standard_days }} ngày</strong>
                    </div>
                    <div>
                        <span>Đơn giá lương ngày:</span>
                        <strong>{{ number_format($payroll->daily_salary, 0, ',', '.') }} đ / ngày</strong>
                    </div>
                    <div>
                        <span>Số ngày làm đủ ca:</span>
                        <strong>{{ $payroll->full_work_days }} ngày</strong>
                    </div>
                    <div>
                        <span>Số ngày nghỉ nửa buổi (làm nửa ca):</span>
                        <strong style="color:#004385;">{{ $payroll->half_work_days }} buổi (quy đổi = {{ $payroll->half_work_days * 0.5 }} công)</strong>
                    </div>
                    <div>
                        <span>Số ngày nghỉ cả ngày:</span>
                        <strong style="color:#64748b;">{{ $payroll->leave_days }} ngày</strong>
                    </div>
                    <div>
                        <span>Tổng số ngày công thực tế tính lương:</span>
                        <strong style="font-size:16px; color:#002d5d;">{{ number_format($payroll->total_work_days, 1) }} công</strong>
                    </div>
                    <div>
                        <span>Tiền lương theo công:</span>
                        <strong>{{ number_format($payroll->salary_amount, 0, ',', '.') }} đ</strong>
                    </div>
                    <div>
                        <span>Số suất phụ cấp tiền ăn được hưởng:</span>
                        <strong style="color:#059669;">{{ $payroll->meal_days }} suất (đơn vị: 35.000 đ/suất)</strong>
                    </div>
                    <div>
                        <span>Tổng tiền phụ cấp ăn trưa:</span>
                        <strong style="color:#059669;">{{ number_format($payroll->meal_allowance_total, 0, ',', '.') }} đ</strong>
                    </div>
                    <div>
                        <span>Thưởng & Phụ cấp khác:</span>
                        <strong style="color:#059669;">+{{ number_format($payroll->bonus_amount, 0, ',', '.') }} đ</strong>
                    </div>
                    <div>
                        <span>Khấu trừ (Bảo hiểm / Tạm ứng):</span>
                        <strong style="color:#dc2626;">-{{ number_format($payroll->deduction_amount, 0, ',', '.') }} đ</strong>
                    </div>
                    <div style="background:#f1f5f9; padding:12px; border-radius:8px; margin-top:8px;">
                        <span style="font-size:15px; font-weight:800; color:#002d5d;">TỔNG THỰC LĨNH (NET SALARY):</span>
                        <strong style="font-size:22px; color:#002d5d; font-weight:900;">{{ number_format($payroll->net_salary, 0, ',', '.') }} đ</strong>
                    </div>
                </div>
            </section>
        </div>

        <div>
            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Điều chỉnh & Phê duyệt</h2>

                <form method="POST" action="{{ route('hr.payroll.update', $payroll) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="bonus_amount" style="font-weight:700;">Thưởng / Phụ cấp bổ sung (VNĐ)</label>
                        <input class="input" type="number" id="bonus_amount" name="bonus_amount" value="{{ old('bonus_amount', $payroll->bonus_amount) }}" min="0" step="1000" style="width:100%;">
                    </div>

                    <div class="form-group">
                        <label for="deduction_amount" style="font-weight:700;">Khấu trừ khác (VNĐ)</label>
                        <input class="input" type="number" id="deduction_amount" name="deduction_amount" value="{{ old('deduction_amount', $payroll->deduction_amount) }}" min="0" step="1000" style="width:100%;">
                    </div>

                    <div class="form-group">
                        <label for="status" style="font-weight:700;">Trạng thái phiếu lương <span style="color:#dc2626;">*</span></label>
                        <select class="select" id="status" name="status" required style="width:100%;">
                            <option value="draft" @selected(old('status', $payroll->status) === 'draft')>Bản nháp (Đang tính toán)</option>
                            <option value="approved" @selected(old('status', $payroll->status) === 'approved')>Đã phê duyệt (Chốt lương)</option>
                            <option value="paid" @selected(old('status', $payroll->status) === 'paid')>Đã thanh toán (Đã chuyển khoản)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="note" style="font-weight:700;">Ghi chú phiếu lương</label>
                        <textarea class="input textarea" id="note" name="note" rows="3" placeholder="Ghi chú chi tiết cho nhân sự..." style="width:100%;">{{ old('note', $payroll->note) }}</textarea>
                    </div>

                    <div style="margin-top:20px;">
                        <button class="button" type="submit" style="width:100%;">
                            <span class="material-symbols-outlined">save</span> Cập nhật phiếu lương
                        </button>
                    </div>
                </form>

                <hr style="border:0; border-top:1px solid #e2e8f0; margin:24px 0 16px;">

                <form method="POST" action="{{ route('hr.payroll.destroy', $payroll) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi phiếu lương này?');">
                    @csrf
                    @method('DELETE')
                    <button class="button danger" type="submit" style="width:100%; font-size:13px;">
                        <span class="material-symbols-outlined">delete</span> Xóa phiếu lương này
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
