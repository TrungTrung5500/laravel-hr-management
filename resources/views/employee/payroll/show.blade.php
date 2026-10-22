@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <a href="{{ route('employee.payroll.index') }}" style="color:inherit; text-decoration:none;">Bảng lương cá nhân</a>
                <span class="stitch-context-slash">/</span>
                <span>Phiếu lương Tháng {{ $payroll->month }}/{{ $payroll->year }}</span>
            </div>
        </div>

        <div class="section-head" style="margin-bottom:20px;">
            <div>
                <h1 style="margin:0; font-size:26px; color:#002d5d;">Phiếu lương Tháng {{ $payroll->month }}/{{ $payroll->year }}</h1>
                <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Trạng thái thanh toán: <span class="stitch-status-pill {{ $payroll->statusClass() }}">{{ $payroll->statusLabel() }}</span></p>
            </div>
            <div class="toolbar">
                <button class="button" onclick="window.print()" type="button" style="background:#475569;">
                    <span class="material-symbols-outlined">print</span> In phiếu lương
                </button>
                <a class="button light" href="{{ route('employee.payroll.index') }}">
                    <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
                </a>
            </div>
        </div>

        <div style="background:#fff; border:1px solid #dbe5f2; border-radius:16px; padding:32px; box-shadow:0 4px 20px rgba(0,0,0,0.05); max-width:850px; margin:0 auto 30px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #002d5d; padding-bottom:18px; margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="width:48px; height:48px; border-radius:12px; background:#002d5d; color:#fff; display:grid; place-items:center; font-weight:800; font-size:24px;">
                        H
                    </div>
                    <div>
                        <div style="font-size:18px; font-weight:800; color:#002d5d; letter-spacing:-0.02em;">TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP.HCM (HUIT)</div>
                        <div style="font-size:12px; color:#a04100; font-weight:700; letter-spacing:0.06em;">PHÒNG QUẢN LÝ NHÂN SỰ & TIỀN LƯƠNG</div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:20px; font-weight:900; color:#004385;">PHIẾU LƯƠNG</div>
                    <div style="font-size:13px; color:#64748b; font-weight:600;">Kỳ lương: Tháng {{ $payroll->month }}/{{ $payroll->year }}</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; background:#f8faff; border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-bottom:24px; font-size:13px;">
                <div>
                    <div style="color:#64748b;">Họ và tên cán bộ:</div>
                    <div style="font-size:15px; font-weight:800; color:#0b1c30; margin-top:2px;">{{ $payroll->employee?->full_name }}</div>
                </div>
                <div>
                    <div style="color:#64748b;">Mã nhân viên:</div>
                    <div style="font-size:15px; font-weight:800; color:#004385; font-family:monospace; margin-top:2px;">{{ $payroll->employee?->employee_code }}</div>
                </div>
                <div>
                    <div style="color:#64748b;">Đơn vị / Phòng ban:</div>
                    <div style="font-weight:700; color:#334155; margin-top:2px;">{{ $payroll->employee?->department?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="color:#64748b;">Chức vụ:</div>
                    <div style="font-weight:700; color:#334155; margin-top:2px;">{{ $payroll->employee?->position?->name ?? '—' }}</div>
                </div>
            </div>

            <h3 style="font-size:16px; color:#002d5d; margin:0 0 12px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">1. Bóc tách công tác & Ngày làm việc</h3>
            <div class="stitch-key-list" style="margin-bottom:24px;">
                <div>
                    <span>Số ngày công chuẩn trong tháng:</span>
                    <strong>{{ $payroll->standard_days }} ngày</strong>
                </div>
                <div>
                    <span>Số ngày làm đủ trọn ngày:</span>
                    <strong>{{ $payroll->full_work_days }} ngày</strong>
                </div>
                <div>
                    <span>Số ngày nghỉ nửa buổi (tính công 1 buổi còn lại):</span>
                    <strong style="color:#004385;">{{ $payroll->half_work_days }} buổi (= {{ $payroll->half_work_days * 0.5 }} ngày công)</strong>
                </div>
                <div>
                    <span>Số ngày nghỉ cả ngày:</span>
                    <strong>{{ $payroll->leave_days }} ngày</strong>
                </div>
                <div style="background:#f1f5f9; padding:8px 12px; border-radius:6px;">
                    <span style="font-weight:700; color:#002d5d;">TỔNG NGÀY CÔNG QUY ĐỔI HƯỞNG LƯƠNG:</span>
                    <strong style="color:#002d5d; font-size:15px;">{{ number_format($payroll->total_work_days, 1) }} ngày công</strong>
                </div>
            </div>

            <h3 style="font-size:16px; color:#002d5d; margin:0 0 12px; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">2. Chi tiết các khoản thu nhập</h3>
            <div class="stitch-key-list" style="margin-bottom:24px;">
                <div>
                    <span>Mức lương cơ bản thỏa thuận:</span>
                    <strong>{{ number_format($payroll->base_salary, 0, ',', '.') }} đ</strong>
                </div>
                <div>
                    <span>Lương theo ngày công ({{ number_format($payroll->daily_salary, 0, ',', '.') }} đ/ngày × {{ number_format($payroll->total_work_days, 1) }} công):</span>
                    <strong>{{ number_format($payroll->salary_amount, 0, ',', '.') }} đ</strong>
                </div>
                <div>
                    <span>Phụ cấp tiền ăn ({{ $payroll->meal_days }} suất × 35.000 đ/suất - kể cả nghỉ nửa buổi):</span>
                    <strong style="color:#059669;">+{{ number_format($payroll->meal_allowance_total, 0, ',', '.') }} đ</strong>
                </div>
                @if($payroll->bonus_amount > 0)
                    <div>
                        <span>Thưởng & Phụ cấp khác:</span>
                        <strong style="color:#059669;">+{{ number_format($payroll->bonus_amount, 0, ',', '.') }} đ</strong>
                    </div>
                @endif
                @if($payroll->deduction_amount > 0)
                    <div>
                        <span>Khấu trừ (Bảo hiểm / Thuế / Tạm ứng):</span>
                        <strong style="color:#dc2626;">-{{ number_format($payroll->deduction_amount, 0, ',', '.') }} đ</strong>
                    </div>
                @endif
            </div>

            <div style="background:#eff6ff; border:2px solid #004385; border-radius:12px; padding:18px 24px; display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <div style="font-size:13px; font-weight:700; color:#004385; text-transform:uppercase; letter-spacing:0.05em;">THỰC LĨNH CHUYỂN KHOẢN (NET)</div>
                    <div style="font-size:11px; color:#64748b; margin-top:2px;">(Đã bao gồm tiền công các buổi làm việc và đầy đủ tiền ăn)</div>
                </div>
                <div style="font-size:28px; font-weight:900; color:#002d5d;">
                    {{ number_format($payroll->net_salary, 0, ',', '.') }} <span style="font-size:16px;">VNĐ</span>
                </div>
            </div>

            @if($payroll->note)
                <div style="background:#f8faff; border:1px solid #e2e8f0; border-radius:8px; padding:12px; font-size:12px; color:#475569;">
                    <strong>Ghi chú từ phòng Nhân sự:</strong> {{ $payroll->note }}
                </div>
            @endif

            <div style="display:flex; justify-content:space-between; margin-top:36px; padding-top:20px; border-top:1px dashed #cbd5e1; font-size:12px; color:#64748b; text-align:center;">
                <div>
                    <strong>NGƯỜI LẬP BIỂU</strong><br>
                    <span style="display:block; margin-top:45px;">Phòng Kế toán - Tài chính</span>
                </div>
                <div>
                    <strong>TRƯỞNG PHÒNG NHÂN SỰ</strong><br>
                    <span style="display:block; margin-top:45px;">(Đã duyệt điện tử)</span>
                </div>
                <div>
                    <strong>NGƯỜI NHẬN LƯƠNG</strong><br>
                    <span style="display:block; margin-top:45px;">{{ $payroll->employee?->full_name }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
