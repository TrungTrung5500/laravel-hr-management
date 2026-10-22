@extends('layouts.hr')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div class="stitch-context">
            <span>CỔNG NHÂN SỰ</span>
            <span class="stitch-context-slash">/</span>
            <span>Quản lý bảng lương</span>
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
            <h1 style="margin:0; font-size:26px; color:#002d5d;">Bảng lương Tháng {{ $selectedMonth }}/{{ $selectedYear }}</h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Tự động tính lương từ dữ liệu chấm công và đơn nghỉ phép. Nhân viên nghỉ nửa buổi được tính công 1 buổi còn lại và chi trả đầy đủ phụ cấp tiền ăn.</p>
        </div>
        <div class="toolbar">
            <form method="POST" action="{{ route('hr.payroll.generate') }}" style="display:inline-block;" onsubmit="return confirm('Hệ thống sẽ quét dữ liệu chấm công và đơn nghỉ phép Tháng {{ $selectedMonth }}/{{ $selectedYear }} để tính lương cho tất cả nhân sự. Tiếp tục?');">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <button class="button" type="submit" style="background:#004385;">
                    <span class="material-symbols-outlined">calculate</span> Tính lương tự động Tháng {{ $selectedMonth }}/{{ $selectedYear }}
                </button>
            </form>
        </div>
    </div>

    <section class="stitch-summary-grid" style="margin-bottom:24px;">
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Tổng quỹ lương thực nhận</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">payments</span></div></div>
            <div class="stitch-summary-value" style="font-size:22px; color:#002d5d;">
                {{ number_format($totalNetSalary, 0, ',', '.') }} <small>VNĐ</small>
            </div>
            <span class="stitch-summary-caption">Tổng chi trả tháng {{ $selectedMonth }}/{{ $selectedYear }}</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Nhân sự đã tính lương</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">groups</span></div></div>
            <div class="stitch-summary-value" style="color:#059669;">{{ $calculatedCount }} / {{ $activeEmployeeCount }} <small>nhân sự</small></div>
            <span class="stitch-summary-caption">Đạt tỷ lệ {{ $activeEmployeeCount > 0 ? round(($calculatedCount / $activeEmployeeCount) * 100) : 0 }}%</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Tổng ngày công quy đổi</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">calendar_month</span></div></div>
            <div class="stitch-summary-value" style="color:#d97706;">{{ number_format($totalWorkDays, 1) }} <small>công</small></div>
            <span class="stitch-summary-tag">Gồm ngày đủ + nửa buổi (0.5)</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Tổng phụ cấp tiền ăn</span><div class="stitch-summary-icon neutral"><span class="material-symbols-outlined">restaurant</span></div></div>
            <div class="stitch-summary-value" style="font-size:20px;">
                {{ number_format($totalMealAllowance, 0, ',', '.') }} <small>VNĐ</small>
            </div>
            <span class="stitch-summary-caption">35.000 đ / ngày (kể cả nghỉ nửa buổi)</span>
        </article>
    </section>

    <section class="stitch-panel">
        <form class="hr-demo-toolbar" method="GET" action="{{ route('hr.payroll.index') }}">
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="month" style="font-weight:700; font-size:13px;">Tháng:</label>
                <select class="select" id="month" name="month" style="min-width:100px;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($selectedMonth === $m)>Tháng {{ $m }}</option>
                    @endfor
                </select>
                <label for="year" style="font-weight:700; font-size:13px;">Năm:</label>
                <select class="select" id="year" name="year" style="min-width:100px;">
                    @for($y = 2024; $y <= 2027; $y++)
                        <option value="{{ $y }}" @selected($selectedYear === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <select class="select" name="department_id">
                <option value="">Tất cả phòng ban</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((string)request('department_id') === (string)$dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>

            <select class="select" name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="draft" @selected(request('status') === 'draft')>Bản nháp</option>
                <option value="approved" @selected(request('status') === 'approved')>Đã duyệt</option>
                <option value="paid" @selected(request('status') === 'paid')>Đã thanh toán</option>
            </select>

            <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc mã NV..." style="min-width:220px;">

            <button class="button button-light" type="submit">
                <span class="material-symbols-outlined">filter_list</span> Xem bảng lương
            </button>
        </form>

        <div class="stitch-table-wrap">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Nhân sự</th>
                        <th>Phòng ban</th>
                        <th>Lương cơ bản</th>
                        <th>Ngày công quy đổi</th>
                        <th>Số buổi tiền ăn</th>
                        <th>Tiền lương công</th>
                        <th>Tiền ăn</th>
                        <th>Thưởng / Phụ cấp</th>
                        <th>Khấu trừ</th>
                        <th>Thực nhận</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $pl)
                        <tr>
                            <td><strong style="color:#004385;">{{ $pl->employee?->employee_code }}</strong></td>
                            <td>
                                <div class="hr-demo-person">
                                    <div class="avatar">{{ strtoupper(substr($pl->employee?->full_name ?? 'NV', 0, 2)) }}</div>
                                    <div>
                                        <strong>{{ $pl->employee?->full_name }}</strong>
                                        <small>{{ $pl->employee?->position?->name ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $pl->employee?->department?->name ?? '—' }}</td>
                            <td>{{ number_format($pl->base_salary, 0, ',', '.') }} đ</td>
                            <td>
                                <strong>{{ number_format($pl->total_work_days, 1) }}</strong> / {{ $pl->standard_days }}
                                <br><small class="stitch-muted">({{ $pl->full_work_days }} ngày đủ + {{ $pl->half_work_days }} nửa buổi)</small>
                            </td>
                            <td>
                                <span class="badge" style="background:#fef3c7; color:#92400e;">
                                    {{ $pl->meal_days }} suất
                                </span>
                            </td>
                            <td>{{ number_format($pl->salary_amount, 0, ',', '.') }} đ</td>
                            <td>{{ number_format($pl->meal_allowance_total, 0, ',', '.') }} đ</td>
                            <td style="color:#059669;">+{{ number_format($pl->bonus_amount, 0, ',', '.') }} đ</td>
                            <td style="color:#dc2626;">-{{ number_format($pl->deduction_amount, 0, ',', '.') }} đ</td>
                            <td>
                                <strong style="font-size:15px; color:#002d5d;">
                                    {{ number_format($pl->net_salary, 0, ',', '.') }} đ
                                </strong>
                            </td>
                            <td>
                                <span class="stitch-status-pill {{ $pl->statusClass() }}">
                                    {{ $pl->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <a class="button light" href="{{ route('hr.payroll.show', $pl) }}" style="padding:6px 12px; font-size:12px;">
                                    Chi tiết / Chốt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="stitch-empty">
                                Chưa có dữ liệu bảng lương Tháng {{ $selectedMonth }}/{{ $selectedYear }}.<br>
                                <span style="font-size:13px; color:#64748b;">Vui lòng bấm nút <strong>"Tính lương tự động Tháng {{ $selectedMonth }}/{{ $selectedYear }}"</strong> ở góc trên bên phải để hệ thống tính lương tự động.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
