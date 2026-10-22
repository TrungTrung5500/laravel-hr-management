<div class="stitch-scale-controller" id="screenScaleWidget">
    <button type="button" class="stitch-scale-btn" id="btnScaleDown" title="Thu nhỏ (Ctrl -)" aria-label="Thu nhỏ">
        <span class="material-symbols-outlined">remove</span>
    </button>
    <div class="stitch-scale-display" title="Tỷ lệ màn hình hiện tại">
        <span class="material-symbols-outlined stitch-scale-icon">aspect_ratio</span>
        <select id="scaleSelect" class="stitch-scale-select" aria-label="Chọn tỷ lệ màn hình">
            <option value="80">80%</option>
            <option value="85">85%</option>
            <option value="90">90%</option>
            <option value="95">95%</option>
            <option value="100" selected>100%</option>
            <option value="105">105%</option>
            <option value="110">110%</option>
            <option value="115">115%</option>
            <option value="125">125%</option>
        </select>
    </div>
    <button type="button" class="stitch-scale-btn" id="btnScaleUp" title="Phóng to (Ctrl +)" aria-label="Phóng to">
        <span class="material-symbols-outlined">add</span>
    </button>
    <button type="button" class="stitch-scale-reset" id="btnScaleReset" title="Đặt lại 100%" aria-label="Đặt lại">
        <span class="material-symbols-outlined">restart_alt</span>
    </button>
</div>

<style>
.stitch-scale-controller {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #edf3ff;
    border: 1px solid #d0e0fc;
    border-radius: 10px;
    padding: 3px 6px;
    user-select: none;
    margin-right: 12px;
}
.stitch-scale-btn, .stitch-scale-reset {
    background: transparent;
    border: 0;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    color: #004385;
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    padding: 0;
}
.stitch-scale-btn:hover, .stitch-scale-reset:hover {
    background: #d6e5ff;
    color: #002d5d;
}
.stitch-scale-btn .material-symbols-outlined,
.stitch-scale-reset .material-symbols-outlined {
    font-size: 17px;
}
.stitch-scale-display {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 0 4px;
}
.stitch-scale-icon {
    font-size: 16px;
    color: #004385;
}
.stitch-scale-select {
    background: transparent;
    border: 0;
    color: #002d5d;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    outline: none;
    padding: 2px 0;
}
@media (max-width: 800px) {
    .stitch-scale-controller {
        display: none;
    }
}
</style>

<script>
(function() {
    const STORAGE_KEY = 'huit_screen_scale';
    const select = document.getElementById('scaleSelect');
    const btnUp = document.getElementById('btnScaleUp');
    const btnDown = document.getElementById('btnScaleDown');
    const btnReset = document.getElementById('btnScaleReset');

    function applyScale(scaleVal) {
        scaleVal = parseInt(scaleVal, 10) || 100;
        if (scaleVal < 70) scaleVal = 70;
        if (scaleVal > 150) scaleVal = 150;

        // Apply zoom to document body/html
        document.body.style.zoom = (scaleVal / 100).toString();
        document.documentElement.style.setProperty('--app-scale', (scaleVal / 100).toString());

        localStorage.setItem(STORAGE_KEY, scaleVal.toString());

        if (select) {
            let matched = false;
            for (let i = 0; i < select.options.length; i++) {
                if (parseInt(select.options[i].value, 10) === scaleVal) {
                    select.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched) {
                const opt = new Option(scaleVal + '%', scaleVal, true, true);
                select.add(opt);
            }
        }
    }

    // Init from storage
    const saved = localStorage.getItem(STORAGE_KEY) || '100';
    applyScale(saved);

    if (select) {
        select.addEventListener('change', function() {
            applyScale(this.value);
        });
    }

    if (btnUp) {
        btnUp.addEventListener('click', function() {
            const current = parseInt(localStorage.getItem(STORAGE_KEY) || '100', 10);
            applyScale(current + 5);
        });
    }

    if (btnDown) {
        btnDown.addEventListener('click', function() {
            const current = parseInt(localStorage.getItem(STORAGE_KEY) || '100', 10);
            applyScale(current - 5);
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function() {
            applyScale(100);
        });
    }
})();
</script>
