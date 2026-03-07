document.addEventListener('DOMContentLoaded', () => {
    const categoryFilters = $('#categoryFilters');

    const categoryNextBtn = $('.category-next');
    const categoryPrevBtn = $('.category-prev');
    const scrollTopBtn = $('#menuScrollTop');

    const initCategoryNav = () => {
        $('.category-nav').each(function () {
            if (!categoryFilters.length || !categoryPrevBtn.length || !categoryNextBtn.length) return;

            const el = categoryFilters[0];
            const getScrollStep = () => Math.max(160, Math.round(el.clientWidth * 0.6));

            const updateNavState = () => {
                const maxScroll = el.scrollWidth - el.clientWidth;
                const current = Math.round(el.scrollLeft);

                const hasScroll = maxScroll > 1;

                categoryPrevBtn.prop('disabled', !hasScroll || current <= 0);
                categoryNextBtn.prop('disabled', !hasScroll || current >= maxScroll - 1);
            };

            const scrollToX = (x) => {
                const maxScroll = el.scrollWidth - el.clientWidth;
                const target = Math.max(0, Math.min(x, maxScroll));

                categoryFilters.stop(true).animate(
                    { scrollLeft: target },
                    250,
                    updateNavState
                );
            };

            categoryPrevBtn.off('click.categoryNav').on('click.categoryNav', function () {
                scrollToX(el.scrollLeft - getScrollStep());
            });

            categoryNextBtn.off('click.categoryNav').on('click.categoryNav', function () {
                scrollToX(el.scrollLeft + getScrollStep());
            });

            let rafPending = false;
            categoryFilters.off('scroll.categoryNav').on('scroll.categoryNav', function () {
                if (rafPending) return;
                rafPending = true;

                requestAnimationFrame(() => {
                    rafPending = false;
                    updateNavState();
                });
            });

            $(window).off('resize.categoryNav').on('resize.categoryNav', updateNavState);

            updateNavState();
        });
    };

    const initScrollTopButton = () => {
        if (!scrollTopBtn.length) return;

        const toggleButtonState = () => {
            scrollTopBtn.toggleClass('is-visible', window.scrollY > 420);
        };

        let rafPending = false;
        $(window).off('scroll.menuScrollTop').on('scroll.menuScrollTop', function () {
            if (rafPending) return;
            rafPending = true;

            requestAnimationFrame(() => {
                rafPending = false;
                toggleButtonState();
            });
        });

        scrollTopBtn.off('click.menuScrollTop').on('click.menuScrollTop', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        toggleButtonState();
    };

    initCategoryNav();
    initScrollTopButton();
});
