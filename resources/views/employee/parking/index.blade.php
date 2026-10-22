@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <span>Quản lý gửi xe</span>
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
                <h1 style="margin:0; font-size:26px; color:#002d5d;">Phương tiện & Thẻ gửi xe</h1>
                <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Đăng ký phương tiện ra vào bãi xe trường HUIT. Sau khi duyệt, phòng Nhân sự sẽ cấp mã thẻ xe tháng.</p>
            </div>
            <div class="toolbar">
                <a class="button" href="{{ route('employee.parking.create') }}">
                    <span class="material-symbols-outlined">two_wheeler</span> Đăng ký xe mới
                </a>
            </div>
        </div>

        <section class="stitch-summary-grid" style="margin-bottom:24px;">
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Phương tiện đăng ký</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">directions_car</span></div></div>
                <div class="stitch-summary-value">{{ $totalVehicles }} <small>xe</small></div>
                <span class="stitch-summary-caption">Tổng phương tiện của bạn</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Thẻ đang hoạt động</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">badge</span></div></div>
                <div class="stitch-summary-value" style="color:#059669;">{{ $activeVehicles }} <small>thẻ</small></div>
                <span class="stitch-summary-caption">Ra vào bãi đỗ xe bình thường</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Chờ duyệt / Cấp thẻ</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">hourglass_top</span></div></div>
                <div class="stitch-summary-value" style="color:#d97706;">{{ $pendingVehicles }} <small>yêu cầu</small></div>
                <span class="stitch-summary-tag">Đang xử lý</span>
            </article>
        </section>

        <section class="stitch-panel">
            <div class="stitch-panel-head">
                <div class="stitch-panel-title">
                    <div class="stitch-panel-icon primary"><span class="material-symbols-outlined">local_parking</span></div>
                    <div>
                        <h2>Danh sách phương tiện của bạn</h2>
                        <p>Thông tin biển số xe, loại xe và mã thẻ gửi xe được liên kết</p>
                    </div>
                </div>
            </div>

            <div class="stitch-table-wrap">
                <table class="stitch-table">
                    <thead>
                        <tr>
                            <th>Biển số xe</th>
                            <th>Loại phương tiện</th>
                            <th>Hãng / Dòng xe</th>
                            <th>Màu xe</th>
                            <th>Mã thẻ gửi xe</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $veh)
                            <tr>
                                <td>
                                    <span style="display:inline-block; padding:4px 10px; border-radius:6px; background:#fff; border:2px solid #002d5d; color:#002d5d; font-weight:800; font-family:monospace; letter-spacing:1px; box-shadow:0 2px 4px rgba(0,0,0,0.06);">
                                        {{ $veh->license_plate }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $veh->vehicleTypeLabel() }}</strong>
                                </td>
                                <td>{{ $veh->brand_model ?: '—' }}</td>
                                <td>{{ $veh->color ?: '—' }}</td>
                                <td>
                                    @if($veh->card_number)
                                        <strong style="color:#004385; font-family:monospace;">{{ $veh->card_number }}</strong>
                                    @else
                                        <span class="stitch-muted">Chưa cấp</span>
                                    @endif
                                </td>
                                <td class="stitch-muted">{{ $veh->registered_date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="stitch-status-pill {{ $veh->statusClass() }}">
                                        {{ $veh->statusLabel() }}
                                    </span>
                                </td>
                                <td style="color:#64748b; font-size:12px;">{{ $veh->note ?: '—' }}</td>
                                <td>
                                    @if($veh->status === 'pending')
                                        <form method="POST" action="{{ route('employee.parking.destroy', $veh) }}" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký xe này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button danger" style="padding:5px 10px; font-size:12px;">Hủy đăng ký</button>
                                        </form>
                                    @else
                                        <span class="stitch-muted" style="font-size:12px;">Đã khóa</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="stitch-empty">Bạn chưa đăng ký phương tiện gửi xe nào. Hãy nhấn "Đăng ký xe mới" để gửi thông tin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
