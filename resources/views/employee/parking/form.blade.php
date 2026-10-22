@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <a href="{{ route('employee.parking.index') }}" style="color:inherit; text-decoration:none;">Quản lý gửi xe</a>
                <span class="stitch-context-slash">/</span>
                <span>Đăng ký gửi xe</span>
            </div>
        </div>

        <div class="section-head" style="margin-bottom:20px;">
            <div>
                <h1 style="margin:0; font-size:26px; color:#002d5d;">Đăng ký phương tiện gửi xe</h1>
                <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Nhập thông tin phương tiện để làm thẻ gửi xe tại các cổng bãi xe HUIT.</p>
            </div>
            <div class="toolbar">
                <a class="button light" href="{{ route('employee.parking.index') }}">
                    <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
                </a>
            </div>
        </div>

        <section class="stitch-panel" style="max-width:760px;">
            <form method="POST" action="{{ route('employee.parking.store') }}">
                @csrf

                <div class="form-group">
                    <label for="license_plate" style="font-weight:700; color:#0b1c30;">Biển số xe <span style="color:#dc2626;">*</span></label>
                    <input class="input" type="text" id="license_plate" name="license_plate" value="{{ old('license_plate') }}" placeholder="Ví dụ: 59-X1 234.56 hoặc 51F-888.88" required style="width:100%; text-transform:uppercase; font-weight:700; font-family:monospace; font-size:16px;">
                    <small style="color:#64748b;">Ghi đúng định dạng biển số hiển thị trên phương tiện.</small>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label for="vehicle_type" style="font-weight:700; color:#0b1c30;">Loại phương tiện <span style="color:#dc2626;">*</span></label>
                        <select class="select" id="vehicle_type" name="vehicle_type" required style="width:100%;">
                            <option value="motorbike" @selected(old('vehicle_type') === 'motorbike')>Xe máy / Xe tay ga</option>
                            <option value="car" @selected(old('vehicle_type') === 'car')>Ô tô (4-7 chỗ)</option>
                            <option value="electric_bike" @selected(old('vehicle_type') === 'electric_bike')>Xe máy điện / Xe đạp điện</option>
                            <option value="bicycle" @selected(old('vehicle_type') === 'bicycle')>Xe đạp</option>
                            <option value="other" @selected(old('vehicle_type') === 'other')>Phương tiện khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="brand_model" style="font-weight:700; color:#0b1c30;">Hãng xe & Dòng xe</label>
                        <input class="input" type="text" id="brand_model" name="brand_model" value="{{ old('brand_model') }}" placeholder="Ví dụ: Honda Vision, SH, Toyota Vios..." style="width:100%;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="color" style="font-weight:700; color:#0b1c30;">Màu sơn xe</label>
                    <input class="input" type="text" id="color" name="color" value="{{ old('color') }}" placeholder="Ví dụ: Đỏ đen, Trắng ngọc trai, Xám titan..." style="width:100%;">
                </div>

                <div class="form-group">
                    <label for="note" style="font-weight:700; color:#0b1c30;">Ghi chú thêm (nếu có)</label>
                    <textarea class="input textarea" id="note" name="note" rows="3" placeholder="Đăng ký gửi vé tháng cổng chính hay cổng phụ, đặc điểm nhận dạng khác..." style="width:100%;">{{ old('note') }}</textarea>
                </div>

                <div class="toolbar" style="margin-top:24px;">
                    <button class="button" type="submit">
                        <span class="material-symbols-outlined">send</span> Gửi đơn đăng ký xe
                    </button>
                    <a class="button light" href="{{ route('employee.parking.index') }}">Hủy bỏ</a>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
