@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <a href="{{ route('employee.leaves.index') }}" style="color:inherit; text-decoration:none;">Quản lý nghỉ phép</a>
                <span class="stitch-context-slash">/</span>
                <span>Tạo đơn xin nghỉ phép</span>
            </div>
        </div>

        <div class="section-head" style="margin-bottom:20px;">
            <div>
                <h1 style="margin:0; font-size:26px; color:#002d5d;">Tạo đơn xin nghỉ phép</h1>
                <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Gửi đơn tới phòng Nhân sự (HR). Bạn có thể đăng ký nghỉ 1 ngày hoặc nửa buổi (sáng/chiều).</p>
            </div>
            <div class="toolbar">
                <a class="button light" href="{{ route('employee.leaves.index') }}">
                    <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
                </a>
            </div>
        </div>

        <div style="background:#eaf2ff; border:1px solid #c2dbff; border-radius:12px; padding:16px 20px; margin-bottom:24px; display:flex; gap:14px; align-items:flex-start;">
            <span class="material-symbols-outlined" style="color:#004385; font-size:24px; margin-top:2px;">info</span>
            <div>
                <strong style="color:#002d5d; font-size:14px;">Chế độ đãi ngộ & Quy tắc tính công nghỉ phép:</strong>
                <ul style="margin:6px 0 0; padding-left:18px; color:#1e3a8a; font-size:13px; line-height:1.6;">
                    <li><strong>Nghỉ nửa buổi (sáng hoặc chiều):</strong> Nhân viên được hưởng 0.5 ngày công cho buổi đi làm còn lại và được hưởng <strong>đầy đủ 1 suất phụ cấp tiền ăn</strong> trong ngày.</li>
                    <li><strong>Nghỉ trọn 1 ngày:</strong> Không phát sinh tiền ăn và tiền công cho ngày nghỉ.</li>
                </ul>
            </div>
        </div>

        <section class="stitch-panel" style="max-width:760px;">
            <form method="POST" action="{{ route('employee.leaves.store') }}">
                @csrf

                <div class="form-group">
                    <label for="leave_type" style="font-weight:700; color:#0b1c30;">Loại nghỉ phép <span style="color:#dc2626;">*</span></label>
                    <select class="select" id="leave_type" name="leave_type" required onchange="handleLeaveTypeChange(this.value)" style="width:100%;">
                        <option value="full_day" @selected(old('leave_type') === 'full_day')>Nghỉ cả ngày (1 ngày - trọn ca làm việc)</option>
                        <option value="half_day_morning" @selected(old('leave_type') === 'half_day_morning')>Nghỉ nửa buổi sáng (Làm buổi chiều - hưởng tiền ăn & công nửa buổi)</option>
                        <option value="half_day_afternoon" @selected(old('leave_type') === 'half_day_afternoon')>Nghỉ nửa buổi chiều (Làm buổi sáng - hưởng tiền ăn & công nửa buổi)</option>
                    </select>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr; gap:16px;" id="dateGrid">
                    <div class="form-group">
                        <label for="start_date" style="font-weight:700; color:#0b1c30;">Ngày bắt đầu nghỉ <span style="color:#dc2626;">*</span></label>
                        <input class="input" type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->addDay()->toDateString()) }}" required style="width:100%;">
                    </div>
                    <div class="form-group" id="endDateWrapper">
                        <label for="end_date" style="font-weight:700; color:#0b1c30;">Ngày kết thúc nghỉ</label>
                        <input class="input" type="date" id="end_date" name="end_date" value="{{ old('end_date', now()->addDay()->toDateString()) }}" style="width:100%;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason" style="font-weight:700; color:#0b1c30;">Lý do xin nghỉ phép <span style="color:#dc2626;">*</span></label>
                    <textarea class="input textarea" id="reason" name="reason" rows="4" placeholder="Vui lòng nêu rõ lý do xin nghỉ (việc gia đình, khám bệnh, việc cá nhân, ...)" required style="width:100%;">{{ old('reason') }}</textarea>
                </div>

                <div class="toolbar" style="margin-top:24px;">
                    <button class="button" type="submit">
                        <span class="material-symbols-outlined">send</span> Gửi đơn xin nghỉ phép
                    </button>
                    <a class="button light" href="{{ route('employee.leaves.index') }}">Hủy bỏ</a>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
function handleLeaveTypeChange(type) {
    const endDateWrapper = document.getElementById('endDateWrapper');
    const endDateInput = document.getElementById('end_date');
    const startDateInput = document.getElementById('start_date');

    if (type === 'half_day_morning' || type === 'half_day_afternoon') {
        if (endDateWrapper) {
            endDateWrapper.style.opacity = '0.5';
            endDateInput.value = startDateInput.value;
            endDateInput.disabled = true;
        }
    } else {
        if (endDateWrapper) {
            endDateWrapper.style.opacity = '1';
            endDateInput.disabled = false;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('leave_type');
    const startDate = document.getElementById('start_date');
    if (typeSelect) {
        handleLeaveTypeChange(typeSelect.value);
    }
    if (startDate) {
        startDate.addEventListener('change', function() {
            const endDate = document.getElementById('end_date');
            if (typeSelect && (typeSelect.value === 'half_day_morning' || typeSelect.value === 'half_day_afternoon')) {
                endDate.value = this.value;
            }
        });
    }
});
</script>
@endsection
