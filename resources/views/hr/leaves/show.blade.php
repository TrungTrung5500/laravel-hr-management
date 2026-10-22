@extends('layouts.hr')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div class="stitch-context">
            <span>CỔNG NHÂN SỰ</span>
            <span class="stitch-context-slash">/</span>
            <a href="{{ route('hr.leaves.index') }}" style="color:inherit; text-decoration:none;">Quản lý nghỉ phép</a>
            <span class="stitch-context-slash">/</span>
            <span>Chi tiết đơn #NP-{{ str_pad($leave->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="notice" style="background:#e4f8f0; color:#087f5b; border:1px solid #a3e6cd; margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="section-head" style="margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:26px; color:#002d5d;">Đơn nghỉ phép #NP-{{ str_pad($leave->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Trạng thái hiện tại: <span class="stitch-status-pill {{ $leave->statusClass() }}">{{ $leave->statusLabel() }}</span></p>
        </div>
        <div class="toolbar">
            <a class="button light" href="{{ route('hr.leaves.index') }}">
                <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
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
                        <strong>{{ $leave->employee?->full_name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Mã nhân viên:</span>
                        <strong>{{ $leave->employee?->employee_code ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Phòng ban:</span>
                        <strong>{{ $leave->employee?->department?->name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Chức vụ:</span>
                        <strong>{{ $leave->employee?->position?->name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Email:</span>
                        <strong>{{ $leave->employee?->email ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Số điện thoại:</span>
                        <strong>{{ $leave->employee?->phone ?? '—' }}</strong>
                    </div>
                </div>
            </section>

            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Chi tiết nghỉ phép</h2>
                <div class="stitch-key-list">
                    <div>
                        <span>Loại đăng ký:</span>
                        <strong style="color:#004385;">{{ $leave->leaveTypeLabel() }}</strong>
                    </div>
                    <div>
                        <span>Thời gian nghỉ:</span>
                        <strong>
                            @if($leave->start_date->equalTo($leave->end_date))
                                {{ $leave->start_date->format('d/m/Y') }}
                            @else
                                {{ $leave->start_date->format('d/m/Y') }} đến {{ $leave->end_date->format('d/m/Y') }}
                            @endif
                        </strong>
                    </div>
                    <div>
                        <span>Số ngày tính công:</span>
                        <strong>{{ $leave->days_count }} ngày</strong>
                    </div>
                    <div>
                        <span>Ngày nộp đơn:</span>
                        <strong>{{ $leave->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div>
                        <span>Lý do xin nghỉ:</span>
                        <p style="margin:4px 0 0; color:#334155; line-height:1.6; background:#f8faff; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                            {{ $leave->reason }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <div>
            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Xử lý phê duyệt</h2>

                <div style="background:#eaf2ff; border:1px solid #c2dbff; border-radius:10px; padding:12px 14px; margin-bottom:18px; font-size:12px; color:#1e3a8a; line-height:1.5;">
                    <strong style="display:block; margin-bottom:3px;">Quy tắc bảng lương:</strong>
                    Nếu duyệt nghỉ nửa buổi, hệ thống sẽ tự động tính 0.5 công cho buổi đi làm còn lại và chi trả đầy đủ phụ cấp tiền ăn trong ngày cho nhân viên.
                </div>

                @if($leave->approved_at)
                    <div class="stitch-key-list" style="margin-bottom:18px;">
                        <div>
                            <span>Người xét duyệt:</span>
                            <strong>{{ $leave->approver?->name ?? 'HR' }}</strong>
                        </div>
                        <div>
                            <span>Thời điểm duyệt:</span>
                            <strong>{{ $leave->approved_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        @if($leave->hr_note)
                            <div>
                                <span>Ghi chú từ HR:</span>
                                <em>{{ $leave->hr_note }}</em>
                            </div>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('hr.leaves.update', $leave) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="hr_note" style="font-weight:700;">Ghi chú / Phản hồi của HR</label>
                        <textarea class="input textarea" id="hr_note" name="hr_note" rows="3" placeholder="Nhập lý do duyệt hoặc lý do từ chối (nếu có)...">{{ old('hr_note', $leave->hr_note) }}</textarea>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:20px;">
                        <button class="button" type="submit" name="status" value="approved" style="background:#059669;">
                            <span class="material-symbols-outlined">check_circle</span> Phê duyệt
                        </button>
                        <button class="button danger" type="submit" name="status" value="rejected">
                            <span class="material-symbols-outlined">cancel</span> Từ chối
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
