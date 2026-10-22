@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <span>Bảng lương cá nhân</span>
            </div>
            <div class="stitch-date">
                <span></span> Hôm nay: {{ now()->translatedFormat('l, d/m/Y') }}
            </div>
        </div>

        <div class="section-head" style="margin-bottom:20px;">
            <div>
                <h1 style="margin:0; font-size:26px; color:#002d5d;">Bảng lương & Thu nhập cá nhân</h1>
                <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Theo dõi chi tiết phiếu lương hàng tháng, số ngày công làm việc, phụ cấp tiền ăn và thực lĩnh.</p>
            </div>
        </div>

        <section class="stitch-summary-grid" style="margin-bottom:24px;">
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Lương thực lĩnh gần nhất</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">payments</span></div></div>
                <div class="stitch-summary-value" style="font-size:22px; color:#002d5d;">
                    {{ $latestPayroll ? number_format($latestPayroll->net_salary, 0, ',', '.') : '0' }} <small>VNĐ</small>
                </div>
                <span class="stitch-summary-caption">
                    {{ $latestPayroll ? "Tháng {$latestPayroll->month}/{$latestPayroll->year}" : 'Chưa có bảng lương' }}
                </span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Tổng thu nhập năm {{ now()->year }}</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">account_balance_wallet</span></div></div>
                <div class="stitch-summary-value" style="font-size:22px; color:#059669;">
                    {{ number_format($totalEarnedYear, 0, ',', '.') }} <small>VNĐ</small>
                </div>
                <span class="stitch-summary-caption">Tổng thực nhận đã chi trả</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Số tháng đã có lương</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">history_edu</span></div></div>
                <div class="stitch-summary-value" style="color:#d97706;">{{ $payrolls->count() }} <small>kỳ lương</small></div>
                <span class="stitch-summary-tag">Lịch sử thu nhập</span>
            </article>
        </section>

        <section class="stitch-panel">
            <div class="stitch-panel-head">
                <div class="stitch-panel-title">
                    <div class="stitch-panel-icon primary"><span class="material-symbols-outlined">receipt</span></div>
                    <div>
                        <h2>Danh sách phiếu lương của bạn</h2>
                        <p>Bấm "Xem chi tiết" để xem bóc tách ngày công, tiền ăn và các khoản thưởng/khấu trừ</p>
                    </div>
                </div>
            </div>

            <div class="stitch-table-wrap">
                <table class="stitch-table">
                    <thead>
                        <tr>
                            <th>Kỳ lương</th>
                            <th>Lương cơ bản</th>
                            <th>Ngày công chuẩn</th>
                            <th>Công thực tế</th>
                            <th>Suất tiền ăn</th>
                            <th>Tiền lương công</th>
                            <th>Phụ cấp tiền ăn</th>
                            <th>Thực lĩnh (Net)</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $pl)
                            <tr>
                                <td>
                                    <strong style="color:#004385; font-size:15px;">Tháng {{ $pl->month }}/{{ $pl->year }}</strong>
                                </td>
                                <td>{{ number_format($pl->base_salary, 0, ',', '.') }} đ</td>
                                <td>{{ $pl->standard_days }} ngày</td>
                                <td>
                                    <strong>{{ number_format($pl->total_work_days, 1) }} công</strong>
                                    @if($pl->half_work_days > 0)
                                        <br><small style="color:#004385;">(Nghỉ nửa buổi: {{ $pl->half_work_days }} ca)</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:#fef3c7; color:#92400e;">
                                        {{ $pl->meal_days }} suất
                                    </span>
                                </td>
                                <td>{{ number_format($pl->salary_amount, 0, ',', '.') }} đ</td>
                                <td style="color:#059669;">+{{ number_format($pl->meal_allowance_total, 0, ',', '.') }} đ</td>
                                <td>
                                    <strong style="color:#002d5d; font-size:15px;">
                                        {{ number_format($pl->net_salary, 0, ',', '.') }} đ
                                    </strong>
                                </td>
                                <td>
                                    <span class="stitch-status-pill {{ $pl->statusClass() }}">
                                        {{ $pl->statusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <a class="button light" href="{{ route('employee.payroll.show', $pl) }}" style="padding:6px 12px; font-size:12px;">
                                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="stitch-empty">
                                    Hiện chưa có dữ liệu phiếu lương cho tài khoản của bạn. Khi phòng Nhân sự chốt lương hàng tháng, thông tin sẽ hiển thị tại đây.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
