@extends('layouts.hr')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div class="stitch-context">
            <span>CỔNG NHÂN SỰ</span>
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
            <h1 style="margin:0; font-size:26px; color:#002d5d;">Quản lý phương tiện & Thẻ gửi xe</h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Theo dõi danh sách phương tiện của nhân sự, cấp mã thẻ gửi xe và kiểm soát ra vào bãi xe HUIT.</p>
        </div>
    </div>

    <section class="stitch-summary-grid" style="margin-bottom:24px;">
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Tổng số xe</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">directions_car</span></div></div>
            <div class="stitch-summary-value">{{ $totalCount }} <small>xe</small></div>
            <span class="stitch-summary-caption">Toàn bộ bãi gửi xe</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Đang hoạt động</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">verified</span></div></div>
            <div class="stitch-summary-value" style="color:#059669;">{{ $activeCount }} <small>thẻ</small></div>
            <span class="stitch-summary-caption">Đã cấp thẻ vé tháng</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Chờ cấp thẻ</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">hourglass_top</span></div></div>
            <div class="stitch-summary-value" style="color:#d97706;">{{ $pendingCount }} <small>xe</small></div>
            <span class="stitch-summary-tag">Cần gán mã thẻ</span>
        </article>
        <article class="stitch-summary-card">
            <div class="stitch-summary-head"><span>Xe máy / Ô tô</span><div class="stitch-summary-icon neutral"><span class="material-symbols-outlined">commute</span></div></div>
            <div class="stitch-summary-value" style="font-size:20px;">
                {{ $motorbikeCount }} <small>xe máy</small> / {{ $carCount }} <small>ô tô</small>
            </div>
            <span class="stitch-summary-caption">Phân loại phương tiện</span>
        </article>
    </section>

    <section class="stitch-panel">
        <form class="hr-demo-toolbar" method="GET" action="{{ route('hr.parking.index') }}">
            <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo biển số, mã thẻ, tên nhân viên..." style="min-width:260px;">
            <select class="select" name="vehicle_type">
                <option value="">Tất cả loại xe</option>
                <option value="motorbike" @selected(request('vehicle_type') === 'motorbike')>Xe máy</option>
                <option value="car" @selected(request('vehicle_type') === 'car')>Ô tô</option>
                <option value="electric_bike" @selected(request('vehicle_type') === 'electric_bike')>Xe điện</option>
                <option value="bicycle" @selected(request('vehicle_type') === 'bicycle')>Xe đạp</option>
            </select>
            <select class="select" name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt / Cấp thẻ</option>
                <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Tạm ngưng</option>
                <option value="revoked" @selected(request('status') === 'revoked')>Đã thu hồi</option>
            </select>
            <select class="select" name="department_id">
                <option value="">Tất cả phòng ban</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((string)request('department_id') === (string)$dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button class="button button-light" type="submit">
                <span class="material-symbols-outlined">filter_list</span> Lọc
            </button>
            @if(request()->anyFilled(['search', 'vehicle_type', 'status', 'department_id']))
                <a class="button light" href="{{ route('hr.parking.index') }}">Đặt lại</a>
            @endif
        </form>

        <div class="stitch-table-wrap">
            <table class="stitch-table">
                <thead>
                    <tr>
                        <th>Biển số xe</th>
                        <th>Chủ phương tiện</th>
                        <th>Phòng ban</th>
                        <th>Loại xe</th>
                        <th>Hãng / Dòng xe</th>
                        <th>Mã thẻ gửi xe</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
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
                                <div class="hr-demo-person">
                                    <div class="avatar">{{ strtoupper(substr($veh->employee?->full_name ?? 'NV', 0, 2)) }}</div>
                                    <div>
                                        <strong>{{ $veh->employee?->full_name ?? '—' }}</strong>
                                        <small>{{ $veh->employee?->employee_code ?? '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $veh->employee?->department?->name ?? '—' }}</td>
                            <td>
                                <strong>{{ $veh->vehicleTypeLabel() }}</strong>
                            </td>
                            <td>{{ $veh->brand_model ?: '—' }} ({{ $veh->color ?: '—' }})</td>
                            <td>
                                @if($veh->card_number)
                                    <strong style="color:#004385; font-family:monospace; font-size:14px;">{{ $veh->card_number }}</strong>
                                @else
                                    <span class="badge" style="background:#fef3c7; color:#b45309;">Chờ gán thẻ</span>
                                @endif
                            </td>
                            <td class="stitch-muted">{{ $veh->registered_date?->format('d/m/Y') }}</td>
                            <td>
                                <span class="stitch-status-pill {{ $veh->statusClass() }}">
                                    {{ $veh->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <a class="button light" href="{{ route('hr.parking.show', $veh) }}" style="padding:6px 12px; font-size:12px;">
                                    Cấp thẻ / Quản lý
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="stitch-empty">Không tìm thấy phương tiện nào phù hợp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="admin-pagination" style="margin-top:16px;">
                {{ $vehicles->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
