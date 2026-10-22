@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <span>Quản lý nghỉ phép</span>
            </div>
            <div class="stitch-date">
                <span></span> Hôm nay: {{ now()->translatedFormat('l, d/m/Y') }}
            </div>
        </div>

        @if(session('success'))
            <div class="notice" style="background:#e4f8f0; color:#087f5b; border:1px solid #a3e6cd; margin-bottom:16px;">
                {{ session('success') }}
            </div>
        @endif

        <div class="section-head" style="margin-bottom:20px;">
            <div>
                <h1 style="margin:0; font-size:26px; color:#002d5d;">Lịch sử & Đơn nghỉ phép</h1>
                <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Nhân viên được nghỉ phép 1 ngày hoặc nửa buổi. Nghỉ nửa buổi vẫn được tính lương và tiền ăn cho buổi đi làm còn lại.</p>
            </div>
            <div class="toolbar">
                <a class="button" href="{{ route('employee.leaves.create') }}">
                    <span class="material-symbols-outlined">add_circle</span> Nộp đơn nghỉ phép mới
                </a>
            </div>
        </div>

        <section class="stitch-summary-grid" style="margin-bottom:24px;">
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Tổng đơn đã gửi</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">receipt_long</span></div></div>
                <div class="stitch-summary-value">{{ $totalRequests }} <small>đơn</small></div>
                <span class="stitch-summary-caption">Toàn bộ lịch sử nộp đơn</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Đang chờ HR duyệt</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">hourglass_top</span></div></div>
                <div class="stitch-summary-value" style="color:#d97706;">{{ $pendingRequests }} <small>đơn</small></div>
                <span class="stitch-summary-tag">Cần xét duyệt</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Đơn đã phê duyệt</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">task_alt</span></div></div>
                <div class="stitch-summary-value" style="color:#059669;">{{ $approvedRequests }} <small>đơn</small></div>
                <span class="stitch-summary-caption">Được chấp thuận</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Tổng ngày/buổi nghỉ</span><div class="stitch-summary-icon neutral"><span class="material-symbols-outlined">event_busy</span></div></div>
                <div class="stitch-summary-value">{{ number_format($approvedDays, 1) }} <small>ngày công</small></div>
                <span class="stitch-summary-caption">Đã tính vào bảng công</span>
            </article>
        </section>

        <section class="stitch-panel">
            <div class="stitch-panel-head">
                <div class="stitch-panel-title">
                    <div class="stitch-panel-icon primary"><span class="material-symbols-outlined">history</span></div>
                    <div>
                        <h2>Danh sách đơn xin nghỉ phép của bạn</h2>
                        <p>Theo dõi tiến độ duyệt và phản hồi từ phòng Nhân sự (HR)</p>
                    </div>
                </div>
            </div>

            <div class="stitch-table-wrap">
                <table class="stitch-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Loại nghỉ</th>
                            <th>Thời gian nghỉ</th>
                            <th>Số ngày/buổi</th>
                            <th>Lý do</th>
                            <th>Ngày nộp</th>
                            <th>Trạng thái</th>
                            <th>Phản hồi HR</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr>
                                <td><strong style="color:#004385;">#NP-{{ str_pad($leave->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>
                                    <strong>{{ $leave->leaveTypeLabel() }}</strong>
                                </td>
                                <td>
                                    @if($leave->start_date->equalTo($leave->end_date))
                                        {{ $leave->start_date->format('d/m/Y') }}
                                    @else
                                        {{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:#e0edff; color:#00346a;">
                                        {{ $leave->days_count }} ngày
                                    </span>
                                </td>
                                <td style="max-width:240px; color:#475569;">{{ $leave->reason }}</td>
                                <td class="stitch-muted">{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="stitch-status-pill {{ $leave->statusClass() }}">
                                        {{ $leave->statusLabel() }}
                                    </span>
                                </td>
                                <td style="font-size:12px; color:#475569;">
                                    @if($leave->hr_note)
                                        <em>{{ $leave->hr_note }}</em>
                                        @if($leave->approver)
                                            <br><small class="stitch-muted">({{ $leave->approver->name }})</small>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($leave->status === 'pending')
                                        <form method="POST" action="{{ route('employee.leaves.destroy', $leave) }}" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn xin nghỉ phép này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button danger" style="padding:5px 10px; font-size:12px;">Hủy đơn</button>
                                        </form>
                                    @else
                                        <span class="stitch-muted" style="font-size:12px;">Đã khóa</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="stitch-empty">Bạn chưa nộp đơn xin nghỉ phép nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
