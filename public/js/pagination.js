document.addEventListener("DOMContentLoaded", function () {
    document.body.addEventListener("click", function (event) {
        if (event.target.tagName === "A" && event.target.closest(".pagination")) {
            event.preventDefault();
            let url = event.target.href;

            fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            })
            .then(response => response.text())
            .then(html => {
                document.querySelector("#diagnostics-container").innerHTML = html;
                window.history.pushState(null, "", url); // Update URL without reloading
            })
            .catch(error => console.error("Pagination error:", error));
        }
    });
});
