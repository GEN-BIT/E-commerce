// Product search/filter interactions - Stage 6
// Product gallery - clicking a thumbnail swaps the main image.
// Plain <img> based, no framework needed.
document.addEventListener('click', function (e) {
    if (e.target.matches('.gallery-thumb')) {
        const main = document.getElementById('mainImage');
        if (!main) return;

        main.src = e.target.dataset.full;

        document.querySelectorAll('.gallery-thumb').forEach(function (t) {
            t.classList.remove('active');
        });
        e.target.classList.add('active');
    }
});
