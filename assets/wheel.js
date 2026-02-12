document.addEventListener("DOMContentLoaded", function () {
    var wheel = document.getElementById("wp-wheel");
    var btn = document.getElementById("wp-wheel-spin");
    var result = document.getElementById("wpw-result");
    var canvas = document.getElementById("wpw-canvas");

    if (!wheel || !btn || !canvas) return;

    var prizes = JSON.parse(wheel.getAttribute("data-prizes") || "[]");
    var count = prizes.length;

    if (count === 0) return;

    // Muted, modern palette — alternates between two tones.
    var palette = [
        ["#2d3436", "#636e72"],
        ["#0984e3", "#74b9ff"],
        ["#00b894", "#55efc4"],
        ["#e17055", "#fab1a0"],
        ["#6c5ce7", "#a29bfe"],
        ["#fdcb6e", "#ffeaa7"],
        ["#e84393", "#fd79a8"],
        ["#00cec9", "#81ecec"],
    ];

    var segmentAngle = 360 / count;
    var ctx = canvas.getContext("2d");
    var size = 360;
    canvas.width = size * 2;
    canvas.height = size * 2;
    ctx.scale(2, 2);
    var center = size / 2;
    var radius = size / 2;

    for (var i = 0; i < count; i++) {
        var startAngle = (segmentAngle * i - 90) * (Math.PI / 180);
        var endAngle = (segmentAngle * (i + 1) - 90) * (Math.PI / 180);
        var color = palette[i % palette.length];

        // Segment fill.
        ctx.beginPath();
        ctx.moveTo(center, center);
        ctx.arc(center, center, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fillStyle = color[0];
        ctx.fill();

        // Thin separator line.
        ctx.beginPath();
        ctx.moveTo(center, center);
        ctx.lineTo(
            center + Math.cos(startAngle) * radius,
            center + Math.sin(startAngle) * radius
        );
        ctx.strokeStyle = "rgba(255, 255, 255, 0.25)";
        ctx.lineWidth = 1;
        ctx.stroke();

        // Text label.
        var textAngle = (startAngle + endAngle) / 2;
        var textRadius = radius * 0.6;
        var x = center + Math.cos(textAngle) * textRadius;
        var y = center + Math.sin(textAngle) * textRadius;

        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(textAngle + Math.PI / 2);
        ctx.fillStyle = "#fff";
        ctx.font = "600 12px Inter, -apple-system, BlinkMacSystemFont, sans-serif";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";

        var name = prizes[i].name;
        if (name.length > 14) {
            name = name.substring(0, 12) + "\u2026";
        }
        ctx.fillText(name, 0, 0);
        ctx.restore();
    }

    var spinning = false;
    var currentRotation = 0;

    btn.addEventListener("click", function () {
        if (spinning || btn.disabled) return;
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
            }, 5200);
        })
        .catch(function () {
            result.textContent = "Network error.";
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
