document.addEventListener('DOMContentLoaded', () => {

    document.addEventListener('error', function(e) {
        const img = e.target;

        if (img.tagName === 'IMG') {

            if (img.dataset.fallback === '1') return;

            img.dataset.fallback = '1';
            img.src = '/images/products/no_image.png';
        }
    }, true);

});