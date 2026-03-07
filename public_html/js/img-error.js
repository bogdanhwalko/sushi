(function () {
    const FALLBACK_SRC = '/images/products/no_image.png';

    function applyFallback(img) {
        if (!img || img.tagName !== 'IMG') return;
        if (img.dataset.fallback === '1') return;

        const currentSrc = img.currentSrc || img.src || '';
        if (currentSrc.indexOf(FALLBACK_SRC) !== -1) {
            img.dataset.fallback = '1';
            return;
        }

        img.dataset.fallback = '1';
        img.removeAttribute('srcset');
        img.removeAttribute('sizes');
        img.src = FALLBACK_SRC;
    }

    function bindImage(img) {
        if (!img || img.tagName !== 'IMG') return;
        if (img.dataset.imgErrorBound === '1') return;

        img.dataset.imgErrorBound = '1';
        img.addEventListener('error', function () {
            applyFallback(img);
        }, { once: true });

        if (img.complete && img.naturalWidth === 0) {
            applyFallback(img);
        }
    }

    function bindNode(node) {
        if (!node || node.nodeType !== 1) return;

        if (node.tagName === 'IMG') {
            bindImage(node);
        }

        if (typeof node.querySelectorAll === 'function') {
            node.querySelectorAll('img').forEach(bindImage);
        }
    }

    document.querySelectorAll('img').forEach(bindImage);

    document.addEventListener('error', function (event) {
        const target = event.target;
        if (target && target.tagName === 'IMG') {
            applyFallback(target);
        }
    }, true);

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(bindNode);
        });
    });

    observer.observe(document.documentElement, { childList: true, subtree: true });

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('img').forEach(function (img) {
            if (img.complete && img.naturalWidth === 0) {
                applyFallback(img);
            }
        });
    });
})();
