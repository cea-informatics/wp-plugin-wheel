document.addEventListener("DOMContentLoaded", function () {
    var wheel = document.getElementById("wp-wheel");
    var btn = document.getElementById("wp-wheel-spin");
    var result = document.getElementById("wpw-result");
    var canvas = document.getElementById("wpw-canvas");

    if (!wheel || !btn || !canvas) return;

    var prizes = JSON.parse(wheel.getAttribute("data-prizes") || "[]");
    var count = prizes.length;

    if (count === 0) return;

    // Two-tone alternating palette — orange and cream.
    var palette = ["#F67521", "#FCF6F0"];
    // Segment border/separator color.
    var separatorColor = "rgba(255, 255, 255, 0.8)";

    var segmentAngle = 360 / count;
    var ctx = canvas.getContext("2d");
    var size = 400;
    canvas.width = size * 2;
    canvas.height = size * 2;
    ctx.scale(2, 2);
    var center = size / 2;
    var radius = size / 2;

    // Draw a gift box icon (outline style).
    function drawGift(cx, cy, s, color) {
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.8;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        // Body
        ctx.beginPath();
        ctx.rect(cx - s * 0.5, cy - s * 0.1, s, s * 0.65);
        ctx.stroke();
        // Lid
        ctx.beginPath();
        ctx.rect(cx - s * 0.55, cy - s * 0.35, s * 1.1, s * 0.25);
        ctx.stroke();
        // Ribbon vertical
        ctx.beginPath();
        ctx.moveTo(cx, cy - s * 0.35);
        ctx.lineTo(cx, cy + s * 0.55);
        ctx.stroke();
        // Left bow
        ctx.beginPath();
        ctx.moveTo(cx, cy - s * 0.22);
        ctx.bezierCurveTo(cx - s * 0.18, cy - s * 0.68, cx - s * 0.62, cy - s * 0.3, cx, cy - s * 0.22);
        ctx.stroke();
        // Right bow
        ctx.beginPath();
        ctx.moveTo(cx, cy - s * 0.22);
        ctx.bezierCurveTo(cx + s * 0.18, cy - s * 0.68, cx + s * 0.62, cy - s * 0.3, cx, cy - s * 0.22);
        ctx.stroke();
    }

    // Draw a shopping cart icon (outline style).
    function drawCart(cx, cy, s, color) {
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.8;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        // Handle
        ctx.beginPath();
        ctx.moveTo(cx - s * 0.8, cy - s * 0.42);
        ctx.lineTo(cx - s * 0.42, cy - s * 0.42);
        ctx.stroke();
        // Basket
        ctx.beginPath();
        ctx.moveTo(cx - s * 0.42, cy - s * 0.42);
        ctx.lineTo(cx - s * 0.55, cy + s * 0.22);
        ctx.lineTo(cx + s * 0.55, cy + s * 0.22);
        ctx.lineTo(cx + s * 0.42, cy - s * 0.42);
        ctx.stroke();
        // Wheels
        ctx.beginPath();
        ctx.arc(cx - s * 0.28, cy + s * 0.48, s * 0.14, 0, Math.PI * 2);
        ctx.stroke();
        ctx.beginPath();
        ctx.arc(cx + s * 0.32, cy + s * 0.48, s * 0.14, 0, Math.PI * 2);
        ctx.stroke();
    }

    // Draw a medal/badge icon (outline style).
    function drawMedal(cx, cy, s, color) {
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.8;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        // Outer circle
        ctx.beginPath();
        ctx.arc(cx, cy - s * 0.12, s * 0.5, 0, Math.PI * 2);
        ctx.stroke();
        // Inner circle
        ctx.beginPath();
        ctx.arc(cx, cy - s * 0.12, s * 0.22, 0, Math.PI * 2);
        ctx.stroke();
        // Ribbon tails
        ctx.beginPath();
        ctx.moveTo(cx - s * 0.18, cy + s * 0.38);
        ctx.lineTo(cx - s * 0.18, cy + s * 0.72);
        ctx.lineTo(cx, cy + s * 0.54);
        ctx.lineTo(cx + s * 0.18, cy + s * 0.72);
        ctx.lineTo(cx + s * 0.18, cy + s * 0.38);
        ctx.stroke();
    }

    var iconDrawers = [drawGift, drawCart, drawMedal];

    for (var i = 0; i < count; i++) {
        var startAngle = (segmentAngle * i - 90) * (Math.PI / 180);
        var endAngle = (segmentAngle * (i + 1) - 90) * (Math.PI / 180);
        var fillColor = palette[i % palette.length];

        // Segment fill.
        ctx.beginPath();
        ctx.moveTo(center, center);
        ctx.arc(center, center, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fillStyle = fillColor;
        ctx.fill();

        // Separator line.
        ctx.beginPath();
        ctx.moveTo(center, center);
        ctx.lineTo(
            center + Math.cos(startAngle) * radius,
            center + Math.sin(startAngle) * radius
        );
        ctx.strokeStyle = separatorColor;
        ctx.lineWidth = 2;
        ctx.stroke();

        // Icon position — centered in segment.
        var iconAngle = (startAngle + endAngle) / 2;
        var iconRadius = radius * 0.62;
        var ix = center + Math.cos(iconAngle) * iconRadius;
        var iy = center + Math.sin(iconAngle) * iconRadius;

        var iconColor = i % 2 === 0
            ? "#ffffff"
            : "#7D5B3B";

        iconDrawers[i % iconDrawers.length](ix, iy, 16, iconColor);
    }

    var spinning = false;
    var currentRotation = 0;

    var modal = document.getElementById("wpw-modal");
    var modalPrize = document.getElementById("wpw-modal-prize");
    var modalClose = document.getElementById("wpw-modal-close");
    var modalBackdrop = modal ? modal.querySelector(".wpw-modal-backdrop") : null;

    function openModal(prizeName) {
        if (!modal) return;
        modalPrize.textContent = prizeName;
        modal.setAttribute("aria-hidden", "false");
        modal.classList.add("wpw-modal--open");
    }

    function closeModal() {
        if (!modal) return;
        modal.setAttribute("aria-hidden", "true");
        modal.classList.remove("wpw-modal--open");
    }

    if (modalClose) modalClose.addEventListener("click", closeModal);
    if (modalBackdrop) modalBackdrop.addEventListener("click", closeModal);

    function launchConfetti() {
        var duration = 1500;
        var end = Date.now() + duration;
        var colors = ["#F67521", "#ffffff", "#323232", "#FCF6F0", "#ffdd00"];

        (function frame() {
            confetti({
                particleCount: 6,
                angle: 60,
                spread: 55,
                origin: { x: 0 },
                colors: colors,
                zIndex: 10000,
            });
            confetti({
                particleCount: 6,
                angle: 120,
                spread: 55,
                origin: { x: 1 },
                colors: colors,
                zIndex: 10000,
            });
            if (Date.now() < end) requestAnimationFrame(frame);
        })();
    }

    btn.addEventListener("click", function () {
        if (spinning || btn.disabled) return;

        if (btn.getAttribute("data-logged-in") === "0") {
            window.location.href = btn.getAttribute("data-login-url");
            return;
        }

        spinning = true;
        btn.disabled = true;
        result.textContent = "";

        fetch(wpw_ajax.url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=wpw_spin&nonce=" + encodeURIComponent(wpw_ajax.nonce),
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success) {
                result.textContent = data.data.message;
                spinning = false;
                return;
            }

            var index = data.data.index;
            var prizeName = data.data.prize.name;

            var targetAngle = 360 - (segmentAngle * index + segmentAngle / 2);
            var fullSpins = 360 * 10;
            var finalRotation = currentRotation + fullSpins + targetAngle - (currentRotation % 360);

            wheel.style.transition = "transform 5s cubic-bezier(0.17, 0.67, 0.12, 0.99)";
            wheel.style.transform = "rotate(" + finalRotation + "deg)";
            currentRotation = finalRotation;

            setTimeout(function () {
                result.innerHTML = "<strong>" + escapeHtml(prizeName) + "</strong>";
                spinning = false;
                launchConfetti();
                openModal(prizeName);
            }, 5200);
        })
        .catch(function () {
            result.textContent = "Erreur réseau.";
            spinning = false;
            btn.disabled = false;
        });
    });

    function escapeHtml(text) {
        var div = document.createElement("div");
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
});
