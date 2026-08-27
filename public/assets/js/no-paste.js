document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.no-paste').forEach(function (field) {
        ['paste', 'drop'].forEach(function (eventName) {
            field.addEventListener(eventName, function (event) {
                event.preventDefault();
            });
        });
    });
});