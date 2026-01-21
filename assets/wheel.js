document.addEventListener("DOMContentLoaded", function () {
    const wheel = document.getElementById("wp-wheel");
    const btn = document.getElementById("wp-wheel-spin");

    btn.addEventListener("click", () => {
        const randomDegree = 3600 + Math.floor(Math.random() * 360);
        wheel.style.transform = `rotate(${randomDegree}deg)`;
    });
});
