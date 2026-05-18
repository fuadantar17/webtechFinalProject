// Auto-dismiss flash messages
document.addEventListener("DOMContentLoaded", function () {
    const msg = document.getElementById("message");
    if (msg && msg.textContent.trim() !== "") {
        setTimeout(() => { msg.style.display = "none"; }, 4000);
    }

    // Confirm delete buttons already use onclick=confirm(), but add class styling
    document.querySelectorAll('a.btn-link.danger').forEach(function (el) {
        el.style.cursor = "pointer";
    });
});
