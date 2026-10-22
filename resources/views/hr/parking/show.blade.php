@extends('layouts.hr')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div class="stitch-context">
            <span>CỔNG NHÂN SỰ</span>
            <span class="stitch-context-slash">/</span>
            <a href="{{ route('hr.parking.index') }}" style="color:inherit; text-decoration:none;">Quản lý gửi xe</a>
            <span class="stitch-context-slash">/</span>
            <span>Chi tiết xe {{ $vehicle->license_plate }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="notice" style="background:#e4f8f0; color:#087f5b; border:1px solid #a3e6cd; margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="section-head" style="margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:26px; color:#002d5d;">Phương tiện: {{ $vehicle->license_plate }}</h1>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Trạng thái thẻ xe: <span class="stitch-status-pill {{ $vehicle->statusClass() }}">{{ $vehicle->statusLabel() }}</span></p>
        </div>
        <div class="toolbar">
            <a class="button light" href="{{ route('hr.parking.index') }}">
                <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns:minmax(0, 1.4fr) minmax(320px, 1fr); gap:24px;">
        <div style="display:flex; flex-direction:column; gap:20px;">
            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Thông tin chủ xe & Phòng ban</h2>
                <div class="stitch-key-list">
                    <div>
                        <span>Chủ phương tiện:</span>
                        <strong>{{ $vehicle->employee?->full_name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Mã nhân viên:</span>
                        <strong>{{ $vehicle->employee?->employee_code ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Phòng ban:</span>
                        <strong>{{ $vehicle->employee?->department?->name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Chức vụ:</span>
                        <strong>{{ $vehicle->employee?->position?->name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Email:</span>
                        <strong>{{ $vehicle->employee?->email ?? '—' }}</strong>
                    </div>
                    <div>
                        <span>Số điện thoại:</span>
                        <strong>{{ $vehicle->employee?->phone ?? '—' }}</strong>
                    </div>
                </div>
            </section>

            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Thông số phương tiện</h2>
                <div class="stitch-key-list">
                    <div>
                        <span>Biển số xe:</span>
                        <span style="display:inline-block; padding:4px 12px; border-radius:6px; background:#fff; border:2px solid #002d5d; color:#002d5d; font-weight:800; font-family:monospace; font-size:16px;">
                            {{ $vehicle->license_plate }}
                        </span>
                    </div>
                    <div>
                        <span>Loại phương tiện:</span>
                        <strong>{{ $vehicle->vehicleTypeLabel() }}</strong>
                    </div>
                    <div>
                        <span>Hãng & Dòng xe:</span>
                        <strong>{{ $vehicle->brand_model ?: '—' }}</strong>
                    </div>
                    <div>
                        <span>Màu sơn xe:</span>
                        <strong>{{ $vehicle->color ?: '—' }}</strong>
                    </div>
                    <div>
                        <span>Ngày đăng ký:</span>
                        <strong>{{ $vehicle->registered_date?->format('d/m/Y') }}</strong>
                    </div>
                    @if($vehicle->approved_at)
                        <div>
                            <span>Thời điểm cấp thẻ:</span>
                            <strong>{{ $vehicle->approved_at->format('d/m/Y H:i') }} (bởi {{ $vehicle->approver?->name ?? 'HR' }})</strong>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div>
            <section class="stitch-panel">
                <h2 style="font-size:18px; margin-bottom:16px; color:#002d5d;">Cấp thẻ & Cập nhật trạng thái</h2>

                <form method="POST" action="{{ route('hr.parking.update', $vehicle) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="card_number" style="font-weight:700;">Mã thẻ gửi xe (Thẻ chip / Vé tháng)</label>
                        <input class="input" type="text" id="card_number" name="card_number" value="{{ old('card_number', $vehicle->card_number) }}" placeholder="Ví dụ: PK-0089 hoặc HUIT-PK-12" style="width:100%; font-family:monospace; font-weight:700;">
                        <small style="color:#64748b;">Mã thẻ vật lý in trên thẻ gửi xe giao cho nhân viên.</small>
                    </div>

                    <div class="form-group">
                        <label for="status" style="font-weight:700;">Trạng thái thẻ <span style="color:#dc2626;">*</span></label>
                        <select class="select" id="status" name="status" required style="width:100%;">
                            <option value="pending" @selected(old('status', $vehicle->status) === 'pending')>Chờ duyệt / Cấp thẻ</option>
                            <option value="active" @selected(old('status', $vehicle->status) === 'active')>Đang hoạt động (Đã giao thẻ)</option>
                            <option value="suspended" @selected(old('status', $vehicle->status) === 'suspended')>Tạm ngưng sử dụng</option>
                            <option value="revoked" @selected(old('status', $vehicle->status) === 'revoked')>Đã thu hồi thẻ / Chấm dứt gửi xe</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="note" style="font-weight:700;">Ghi chú của HR</label>
                        <textarea class="input textarea" id="note" name="note" rows="3" placeholder="Ghi chú vị trí để xe, lý do thu hồi hoặc số seri thẻ..." style="width:100%;">{{ old('note', $vehicle->note) }}</textarea>
                    </div>

                    <div style="margin-top:20px;">
                        <button class="button" type="submit" style="width:100%;">
                            <span class="material-symbols-outlined">save</span> Lưu cập nhật
                        </button>
                    </div>
                </form>

                <hr style="border:0; border-top:1px solid #e2e8f0; margin:24px 0 16px;">

                <form method="POST" action="{{ route('hr.parking.destroy', $vehicle) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hồ sơ xe này khỏi hệ thống?');">
                    @csrf
                    @method('DELETE')
                    <button class="button danger" type="submit" style="width:100%; font-size:13px;">
                        <span class="material-symbols-outlined">delete</span> Xóa hồ sơ xe này
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
