document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('productModal');
    const citySelector = document.getElementById('citySelector');
    const sideCitySelector = document.getElementById('sideCitySelector');
    const cityLabel = document.getElementById('cityLabel');
    const addressLabel = document.getElementById('addressLabel');
    const modalTitle = document.getElementById('modalTitle');
    const modalAvailability = document.getElementById('modalAvailability');
    const modalDescription = document.getElementById('modalDescription');
    const modalPrice = document.getElementById('modalPrice');
    const modalWeight = document.getElementById('modalWeight');
    const modalPieces = document.getElementById('modalPieces');
    const modalImage = document.getElementById('modalImage');
    const closeBtn = modalElement?.querySelector('.btn-close');
    const cartCountEl = document.getElementById('cartCount');
    const cartCountSideEl = document.getElementById('cartCountSide');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const cartFeedback = document.getElementById('cartFeedback');


    const checkoutForm = document.getElementById('checkoutForm');
    const checkoutNameEl = document.getElementById('checkoutName');
    const checkoutPhoneEl = document.getElementById('checkoutPhone');
    const consultModalEl = document.getElementById('consultModal');
    const consultForm = document.getElementById('consultForm');
    const consultPhoneEl = document.getElementById('consultPhone');
    const consultBtn = document.getElementById('consultBtn');
    const cityWelcomeModalEl = document.getElementById('cityWelcomeModal');
    const cityWelcomeForm = document.getElementById('cityWelcomeForm');
    const cityWelcomeSelect = document.getElementById('cityWelcomeSelect');
    const cityWelcomeBtn = document.getElementById('cityWelcomeBtn');
    const cartToastEl = document.getElementById('cartToast');
    const cartToastMessageEl = document.getElementById('cartToastMessage');
    const snowLayer = document.getElementById('snowLayer');
    const snowToggle = document.getElementById('snowToggle');
    const happyDot = document.getElementById('happyDot');
    const happyStatus = document.getElementById('happyStatus');

    const productGridContainer = $('#productGrid');
    const productDetailModal = $('#productModal');
    const productDetailModalContent = productDetailModal.find('.modal-body');

    const categoryNextBtn = $('.category-next');
    const categoryFilters = $('#categoryFilters');
    const categoryButtons = categoryFilters.find('button');
    const categoryPrevBtn = $('.category-prev');

    const cartItemsBlock = $('#cartItems');
    const cartButton = $('#cart-button');
    const cartContent = $('#cart-content');
    const drawer = $('#cartDrawer');

    const clearCartBtn = $('#clearCartBtn');
    const checkoutBtn = $('#checkoutBtn');
    const checkoutModalEl = $('#checkoutModal');

    var cartUpdateStatus = true;


    let cartModal = bootstrap.Offcanvas.getOrCreateInstance(drawer[0]);
    let checkoutModal = bootstrap.Modal.getOrCreateInstance(checkoutModalEl[0]);


    /* ---BEGIN [прокрутка категорій] BEGIN--- */
    const initCategoryNav = () => {
        $('.category-nav').each(function () {
            if (!categoryFilters.length || !categoryPrevBtn.length || !categoryNextBtn.length) return;

            const el = categoryFilters[0];
            const getScrollStep = () => Math.max(160, Math.round(el.clientWidth * 0.6));

            const updateNavState = () => {
                const maxScroll = el.scrollWidth - el.clientWidth;
                const current = Math.round(el.scrollLeft);

                // якщо скролу нема — вимикаємо обидві кнопки
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

            // чистимо старі хендлери, щоб init можна було викликати повторно
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
    /* ---END [прокрутка категорій] END--- */


    /* ---BEGIN [завантаження товарів] BEGIN--- */
    function loadProducts(category = 0)
    {
        showPreloader(productGridContainer);

        $.ajax({
            url: 'ajax/ajax-product/get-by-filters',
            method: 'GET',
            dataType: 'html',
            data: {
                categoryId: category
            },
            success: function(resp) {
                productGridContainer.html(resp)
            },
            error: function(xhr) {
                console.log('Помилка завантаження продуктів')
            }
        });
    }
    /* ---END [завантаження товарів] END--- */


    /* ---BEGIN [Додавання товарів до кошика] BEGIN--- */
    productGridContainer.on('click', '.add-to-cart', addToCart);
    productDetailModal.on('click', '.add-to-cart', addToCart);

    function addToCart()
    {
        let parentBlock = $(this).closest('.product-card');
        cartUpdate(parentBlock.data('product'), true)
    }

    function cartUpdate(product_id, qtyStatus = true)
    {
        $.ajax({
            url: 'ajax/ajax-cart/add-product',
            method: 'GET',
            dataType: 'json',
            data: {product_id: product_id, qtyStatus: qtyStatus},
            success: function(res) {
                if (res.errors.length < 1) {
                    showToast('Товар додано до кошика');
                    cartUpdateStatus = true;
                    return true;
                }

                showToast('Трапилась помилка при додаванні товару в корзину.');
            },
            error: function(xhr) {
                console.log('Трапилась помилка при додаванні товару в корзину.')
            }
        });

        return false;
    }

    /* ---END [Додавання товарів до кошика] END--- */


    /* ---BEGIN [Видалення товару з кошика] BEGIN--- */
    cartContent.on('click', '.remove-item', function (e) {
        let el = $(this);
        let parentBlock = el.closest('.cart-item');
        el.remove();

        $.ajax({
            url: 'ajax/ajax-cart/delete-item',
            method: 'GET',
            dataType: 'json',
            data: {item_id: parentBlock.data('item_id')},
            success: function(res) {
                if (res.status) {
                    let totalBlock = cartContent.find('#cartTotal');
                    totalBlock.text(totalBlock.text() - parentBlock.find('.total-item-price').text());

                    parentBlock.animate(
                        { opacity: 0, height: 0, marginTop: 0, marginBottom: 0, paddingTop: 0, paddingBottom: 0 },
                        250,
                        function () { $(this).remove(); }
                    );

                    showToast('Товар успішно видалено.');
                    return true;
                }

                showToast('Трапилась помилка при видаленні товару в корзини, зверніться у підтримку.');
            },
            error: function(xhr) {
                console.log('Трапилась помилка при видаленні товару з корзини.')
            }
        });


    });
    /* ---END [Видалення товару з кошика] END--- */


    /* ---BEGIN [Інкремент, декремент товару] BEGIN--- */
    cartContent.on('click', '.qty-btn', function (e) {
        let el = $(this);
        let parentBlock = el.closest('.cart-item');

        let qtyBlock = parentBlock.find('.qty-value');
        let prefix = el.data('type') === 'inc' ? 1 : -1;
        let qty = Number(qtyBlock.text()) + prefix;

        if (qty > 0 && qty <= 20) {
            el.prop('disabled', true);

            $.ajax({
                url: 'ajax/ajax-cart/change-qty',
                method: 'GET',
                dataType: 'html',
                data: {item_id: parentBlock.data('item_id'), qty: qty},
                success: function(res) {
                    qtyBlock.text(qty);

                    let totalPriceItemBlock = parentBlock.find('.total-item-price')
                    let itemPrice = parentBlock.find('.item-price').text();
                    totalPriceItemBlock.text(itemPrice * qty);

                    let totalBlock = cartContent.find('#cartTotal');
                    totalBlock.text(Number(totalBlock.text()) + itemPrice * prefix);
                    el.prop('disabled', false);
                },
                error: function(xhr) {
                    console.log('Трапилась помилка при завантаженні детальної інформації про товар.')
                },
            });
        }
    });
    /* ---BEGIN [Інкремент, декремент товару] BEGIN--- */


    /* ---BEGIN [Детальна інформація про товар] BEGIN--- */
    productGridContainer.on('click', '.view-details', (e) => {
        let parentBlock = $(e.target).closest('.product-card');
        showPreloader(productDetailModalContent)

        $.ajax({
            url: 'ajax/ajax-product/detail',
            method: 'GET',
            dataType: 'html',
            data: {productId: parentBlock.data('product')},
            success: function(res) {
                if (res.length > 0) {
                    productDetailModalContent.html(res)
                }
            },
            error: function(xhr) {
                console.log('Трапилась помилка при завантаженні детальної інформації про товар.')
            }
        });

        productDetailModal.modal('show');
    })
    /* ---END [Детальна інформація про товар] END--- */


    /* ---BEGIN [Зміна категорії] BEGIN--- */
    categoryFilters.on('click', 'button', function (e) {
        let clicked = $(this);

        categoryButtons.removeClass('active');
        clicked.addClass('active');

        loadProducts(clicked.data('id'));
    });
    /* ---END [Зміна категорії] END--- */


    /* ---BEGIN [Відкриття корзини] BEGIN--- */
    cartButton.on('click', function (e) {
        if (cartUpdateStatus) {
            showPreloader(cartContent);

            $.ajax({
                url: 'ajax/ajax-cart/cart',
                method: 'GET',
                dataType: 'html',
                success: function(res) {
                    cartUpdateStatus = false;
                    cartContent.html(res)
                },
                error: function(xhr) {
                    console.log('Трапилась помилка при завантаженні корзини.')
                }
            });
        }

        bootstrap.Offcanvas.getOrCreateInstance(drawer[0]).show();
    });
    /* ---END [Відкриття корзини] END--- */


    /* ---BEGIN [Очистити корзину] BEGIN--- */
    cartContent.on('click', '#clearCartBtn', function (e) {
        let el = $(this);
        el.prop('disabled', true);

        $.ajax({
            url: 'ajax/ajax-cart/clear-cart',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                cartContent.find('#cartItems').remove();
                cartContent.find('#cartTotal').text(0);
                el.prop('disabled', false);
            },
            error: function(xhr) {
                console.log('Помилка при спробі очистити корзину, спробуйте ще раз!')
            }
        });
    })
    /* ---END [Очистити корзину] END--- */

    cartContent.on('click', '#checkoutBtn', function (e) {
        cartModal.hide();
        checkoutModal.show();
    });

    // cartContent.on('hidden.bs.modal', '#checkoutBtn', function () {
    //     document.body.focus();
    // });


    // $(document).on('hidden.bs.modal', '.modal', function () {
    //     document.getElementById('link-logo')?.focus();
    // });
    //
    // $(document).on('hidden.bs.offcanvas', '.offcanvas', function () {
    //     document.getElementById('link-logo')?.focus();
    // });




    // Bootstrap modal if available; otherwise fallback to manual toggling.

    const SNOW_STORAGE_KEY = 'umiSnowEnabled';

    const getStoredSnow = () => {
        try {
            return localStorage.getItem(SNOW_STORAGE_KEY);
        } catch (e) {
            return null;
        }
    };

    const setStoredSnow = (value) => {
        try {
            localStorage.setItem(SNOW_STORAGE_KEY, value ? 'true' : 'false');
        } catch (e) {
            // Ignore storage errors (privacy mode, blocked, etc.)
        }
    };

    const updateSnowToggle = (enabled) => {
        if (!snowToggle) return;
        snowToggle.setAttribute('aria-pressed', String(enabled));
        snowToggle.classList.toggle('is-off', !enabled);
        snowToggle.title = enabled ? 'Сніг увімкнено' : 'Сніг вимкнено';
    };

    const buildSnow = () => {
        if (!snowLayer || snowLayer.dataset.ready === 'true') return;
        const width = window.innerWidth || 1200;
        const count = Math.max(24, Math.min(90, Math.floor(width / 14)));
        for (let i = 0; i < count; i += 1) {
            const flake = document.createElement('span');
            flake.className = 'snowflake';
            const size = (Math.random() * 3.5 + 2).toFixed(1);
            const x = (Math.random() * 100).toFixed(2) + 'vw';
            const drift = (Math.random() * 40 - 20).toFixed(1) + 'px';
            const speed = (Math.random() * 12 + 8).toFixed(1) + 's';
            const delay = (-Math.random() * 20).toFixed(1) + 's';
            const opacity = (Math.random() * 0.5 + 0.35).toFixed(2);
            flake.style.setProperty('--size', `${size}px`);
            flake.style.setProperty('--x', x);
            flake.style.setProperty('--drift', drift);
            flake.style.setProperty('--speed', speed);
            flake.style.setProperty('--delay', delay);
            flake.style.setProperty('--opacity', opacity);
            snowLayer.appendChild(flake);
        }
        snowLayer.dataset.ready = 'true';
    };

    const setSnowEnabled = (enabled, persist) => {
        document.body.classList.toggle('snow-hidden', !enabled);
        if (enabled) {
            buildSnow();
        }
        if (persist) {
            setStoredSnow(enabled);
        }
        updateSnowToggle(enabled);
    };

    const initSnow = () => {
        const stored = getStoredSnow();
        const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const enabled = stored === null ? !prefersReduced : stored === 'true';
        setSnowEnabled(enabled, false);
    };

    const HAPPY_HOURS = [
        {start: 12 * 60, end: 15 * 60},
        {start: 19 * 60, end: 22 * 60}
    ];

    const minutesToLabel = (minutes) => {
        const h = String(Math.floor(minutes / 60)).padStart(2, '0');
        const m = String(minutes % 60).padStart(2, '0');
        return `${h}:${m}`;
    };

    const buildRangeLabel = (range) => `${minutesToLabel(range.start)}–${minutesToLabel(range.end)}`;

    const updateHappyHours = () => {
        if (!happyStatus || !happyDot) return;
        const now = new Date();
        const minutes = now.getHours() * 60 + now.getMinutes();
        const active = HAPPY_HOURS.find((range) => minutes >= range.start && minutes < range.end);
        if (active) {
            happyStatus.textContent = `Діють зараз • -20% до ${minutesToLabel(active.end)}`;
            happyDot.classList.add('is-active');
            happyStatus.classList.add('is-active');
            return;
        }
        const next = HAPPY_HOURS.find((range) => range.start > minutes);
        if (next) {
            happyStatus.textContent = `Наступні ${buildRangeLabel(next)}`;
        } else {
            happyStatus.textContent = `Завтра ${buildRangeLabel(HAPPY_HOURS[0])}`;
        }
        happyDot.classList.remove('is-active');
        happyStatus.classList.remove('is-active');
    };



    snowToggle?.addEventListener('click', () => {
        const enabled = !document.body.classList.contains('snow-hidden');
        setSnowEnabled(!enabled, true);
    });


    function showPreloader(block)
    {
        block.html(`<div class="text-center py-4">
            <div class="spinner-border text-dark" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading...</div>
        </div>`);
    }


    initCategoryNav();
    loadProducts();

    initSnow();
    updateHappyHours();
    setInterval(updateHappyHours, 60000);
});

function showToast(message) {
    const toastEl = document.getElementById('cartToast');
    const toastMessageEl = document.getElementById('cartToastMessage');
    if (!toastEl || !toastMessageEl) return;
    toastMessageEl.textContent = message;

    if (window.bootstrap && typeof bootstrap.Toast === 'function') {
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, {delay: 2200});
        toast.show();
        return;
    }

    // Fallback: basic show/hide if Bootstrap JS is unavailable.
    toastEl.classList.add('show');
    toastEl.style.display = 'block';
    setTimeout(() => {
        toastEl.classList.remove('show');
        toastEl.style.display = 'none';
    }, 2200);
}
