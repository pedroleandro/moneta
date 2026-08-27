document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.navbar-flash-message').forEach(function (el) {
        setTimeout(function () {
            const alert = bootstrap.Alert.getOrCreateInstance(el);
            alert.close();
        }, 6000);
    });
});