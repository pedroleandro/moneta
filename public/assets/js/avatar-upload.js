document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("avatar");
    const preview = document.getElementById("avatar-preview");

    if (!input || !preview) {
        return;
    }

    input.addEventListener("change", function () {
        const file = input.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            preview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
});