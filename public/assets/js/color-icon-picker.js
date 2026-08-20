document.addEventListener('DOMContentLoaded', function () {
    const colorInput = document.getElementById('color');
    const colorPreview = document.getElementById('color-preview');
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    const defaultIcon = (iconInput && iconInput.dataset.defaultIcon) || 'bx-square';

    if (colorInput && colorPreview) {
        document.querySelectorAll('.color-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const color = btn.dataset.color;
                colorInput.value = color;
                colorPreview.style.backgroundColor = color;
            });
        });

        colorInput.addEventListener('input', function () {
            if (/^#[0-9A-Fa-f]{6}$/.test(colorInput.value)) {
                colorPreview.style.backgroundColor = colorInput.value;
            }
        });
    }

    if (iconInput && iconPreview) {
        document.querySelectorAll('.icon-swatch-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const icon = btn.dataset.icon;
                iconInput.value = icon;
                iconPreview.className = 'icon-base bx ' + icon + ' icon-md';
            });
        });

        iconInput.addEventListener('input', function () {
            const value = iconInput.value.trim() || defaultIcon;
            iconPreview.className = 'icon-base bx ' + value + ' icon-md';
        });
    }
});