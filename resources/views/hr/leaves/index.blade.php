@extends('layouts.hr')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div class="stitch-context">
            <span>CỔNG NHÂN SỰ</span>
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
            <h1 style="margin:0; font-size:26px; color:#002d5d;">Quản lý đơn xin nghỉ phép</h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Xét duyệt đơn nghỉ phép của cán bộ - giảng viên - nhân viên. Dữ liệu nghỉ phép đã duyệt sẽ tự động liên kết vào bảng lương và chấm công.</p>
        </div>
    </div>

    <section class="stitch-summary-grid" style="margin-bottom:24px;">
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Tổng số đơn</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">receipt_long</span></div></div>
            <div class="stitch-summary-value">{{ $totalCount }} <small>đơn</small></div>
            <span class="stitch-summary-caption">Toàn bộ hồ sơ nghỉ phép</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Chờ duyệt</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">pending_actions</span></div></div>
            <div class="stitch-summary-value" style="color:#d97706;">{{ $pendingCount }} <small>đơn</small></div>
            <span class="stitch-summary-tag">Cần xử lý ngay</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Đã phê duyệt</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">task_alt</span></div></div>
            <div class="stitch-summary-value" style="color:#059669;">{{ $approvedCount }} <small>đơn</small></div>
            <span class="stitch-summary-caption">Chấp thuận nghỉ phép</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Từ chối</span><div class="stitch-summary-icon neutral"><span class="material-symbols-outlined">cancel</span></div></div>
            <div class="stitch-summary-value" style="color:#dc2626;">{{ $rejectedCount }} <small>đơn</small></div>
            <span class="stitch-summary-caption">Không đủ điều kiện</span>
        </article>
    </section>

    <section class="stitch-panel">
        <form class="hr-demo-toolbar" method="GET" action="{{ route('hr.leaves.index') }}">
            <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc mã nhân viên..." style="min-width:260px;">
            <select class="select" name="department_id">
                <option value="">Tất cả phòng ban</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((string)request('department_id') === (string)$dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            <select class="select" name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt</option>
                <option value="approved" @selected(request('status') === 'approved')>Đã duyệt</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Từ chối</option>
            </select>
            <input class="input" type="month" name="month" value="{{ request('month') }}" title="Lọc theo tháng">
            <button class="button button-light" type="submit">
                <span class="material-symbols-outlined">filter_list</span> Lọc
            </button>
            @if(request()->anyFilled(['search', 'department_id', 'status', 'month']))
                <a class="button light" href="{{ route('hr.leaves.index') }}">Đặt lại</a>
            @endif
        </form>

        <div class="stitch-table-wrap">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Nhân sự</th>
                        <th>Phòng ban</th>
                        <th>Loại nghỉ</th>
                        <th>Thời gian nghỉ</th>
                        <th>Số ngày</th>
                        <th>Lý do</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            <td><strong style="color:#004385;">#NP-{{ str_pad($leave->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>
                                <div class="hr-demo-person">
                                    <div class="avatar">{{ strtoupper(substr($leave->employee?->full_name ?? 'NV', 0, 2)) }}</div>
                                    <div>
                                        <strong>{{ $leave->employee?->full_name ?? '—' }}</strong>
                                        <small>{{ $leave->employee?->employee_code ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $leave->employee?->department?->name ?? '—' }}</td>
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
                            <td style="max-width:200px; color:#475569;" title="{{ $leave->reason }}">
                                {{ Str::limit($leave->reason, 45) }}
                            </td>
                            <td>
                                <span class="stitch-status-pill {{ $leave->statusClass() }}">
                                    {{ $leave->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <a class="button light" href="{{ route('hr.leaves.show', $leave) }}" style="padding:6px 12px; font-size:12px;">
                                    Chi tiết / Duyệt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="stitch-empty">Không có đơn xin nghỉ phép nào phù hợp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            <div class="admin-pagination" style="margin-top:16px;">
                {{ $leaves->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
