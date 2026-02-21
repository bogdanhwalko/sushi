const cityMap = window.cityMap && typeof window.cityMap === 'object' ? window.cityMap : {};

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
    const clearCartBtn = document.getElementById('clearCartBtn');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const checkoutModalEl = document.getElementById('checkoutModal');
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

    var cartUpdateStatus = true;

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


    // Bootstrap modal if available; otherwise fallback to manual toggling.

    let backdropEl = null;
    const showFallbackModal = () => {
        if (!modalElement) return;
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        backdropEl = document.createElement('div');
        backdropEl.className = 'modal-backdrop fade show';
        document.body.appendChild(backdropEl);
    };

    const hideFallbackModal = () => {
        if (!modalElement) return;
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        modalElement.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (backdropEl && backdropEl.parentNode) {
            backdropEl.parentNode.removeChild(backdropEl);
        }
        backdropEl = null;
    };

    let checkoutBackdropEl = null;
    const showCheckoutModal = () => {
        if (checkoutModal) {
            checkoutModal.show();
            return;
        }
        if (!checkoutModalEl) return;
        checkoutModalEl.classList.add('show');
        checkoutModalEl.style.display = 'block';
        checkoutModalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        checkoutBackdropEl = document.createElement('div');
        checkoutBackdropEl.className = 'modal-backdrop fade show';
        document.body.appendChild(checkoutBackdropEl);
    };

    checkoutBtn?.addEventListener('click', () => {
        if (Object.keys(cart).length === 0) {
            showToast('Кошик порожній. Додайте товари перед оформленням.');
            return;
        }
        resetCheckoutForm();
        if (window.bootstrap) {
            const cartDrawerEl = document.getElementById('cartDrawer');
            const drawerInstance = cartDrawerEl ? bootstrap.Offcanvas.getInstance(cartDrawerEl) : null;
            drawerInstance?.hide();
        }
        showCheckoutModal();
    });

    // const hideCheckoutModal = () => {
    //     if (checkoutModal) {
    //         checkoutModal.hide();
    //         return;
    //     }
    //     if (!checkoutModalEl) return;
    //     checkoutModalEl.classList.remove('show');
    //     checkoutModalEl.style.display = 'none';
    //     checkoutModalEl.setAttribute('aria-hidden', 'true');
    //     document.body.classList.remove('modal-open');
    //     if (checkoutBackdropEl && checkoutBackdropEl.parentNode) {
    //         checkoutBackdropEl.parentNode.removeChild(checkoutBackdropEl);
    //     }
    //     checkoutBackdropEl = null;
    // };

    const resetCheckoutForm = () => {
        if (!checkoutForm) return;
        checkoutForm.reset();
        if (checkoutNameEl) checkoutNameEl.classList.remove('is-invalid');
        if (checkoutPhoneEl) checkoutPhoneEl.classList.remove('is-invalid');
    };

    const CITY_STORAGE_KEY = 'umiCity';

    const getStoredCity = () => {
        try {
            return localStorage.getItem(CITY_STORAGE_KEY);
        } catch (e) {
            return null;
        }
    };

    const setStoredCity = (city) => {
        try {
            localStorage.setItem(CITY_STORAGE_KEY, city);
        } catch (e) {
            // Ignore storage errors (privacy mode, blocked, etc.)
        }
    };

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

    let cityBackdropEl = null;
    const showCityWelcomeModal = () => {
        if (cityWelcomeModal) {
            cityWelcomeModal.show();
            return;
        }
        if (!cityWelcomeModalEl) return;
        cityWelcomeModalEl.classList.add('show');
        cityWelcomeModalEl.style.display = 'block';
        cityWelcomeModalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        cityBackdropEl = document.createElement('div');
        cityBackdropEl.className = 'modal-backdrop fade show';
        document.body.appendChild(cityBackdropEl);
    };

    const hideCityWelcomeModal = () => {
        if (cityWelcomeModal) {
            cityWelcomeModal.hide();
            return;
        }
        if (!cityWelcomeModalEl) return;
        cityWelcomeModalEl.classList.remove('show');
        cityWelcomeModalEl.style.display = 'none';
        cityWelcomeModalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (cityBackdropEl && cityBackdropEl.parentNode) {
            cityBackdropEl.parentNode.removeChild(cityBackdropEl);
        }
        cityBackdropEl = null;
    };

    const setCity = (city, persist) => {
        const normalized = cityMap[city] ? city : 'all';
        updateCityUI(normalized);
        filterProducts(normalized);
        if (persist) setStoredCity(normalized);
    };

    let consultBackdropEl = null;
    const showConsultModal = () => {
        if (consultModal) {
            consultModal.show();
            return;
        }
        if (!consultModalEl) return;
        consultModalEl.classList.add('show');
        consultModalEl.style.display = 'block';
        consultModalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        consultBackdropEl = document.createElement('div');
        consultBackdropEl.className = 'modal-backdrop fade show';
        document.body.appendChild(consultBackdropEl);
    };

    const hideConsultModal = () => {
        if (consultModal) {
            consultModal.hide();
            return;
        }
        if (!consultModalEl) return;
        consultModalEl.classList.remove('show');
        consultModalEl.style.display = 'none';
        consultModalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (consultBackdropEl && consultBackdropEl.parentNode) {
            consultBackdropEl.parentNode.removeChild(consultBackdropEl);
        }
        consultBackdropEl = null;
    };

    const resetConsultForm = () => {
        if (!consultForm) return;
        consultForm.reset();
        if (consultPhoneEl) consultPhoneEl.classList.remove('is-invalid');
    };

    const isValidName = (value) => {
        const clean = value.trim();
        if (!clean) return false;
        const parts = clean.split(/\s+/);
        if (parts.length < 2) return false;
        return parts.every((part) => /^[A-Za-zА-Яа-яІіЇїЄєҐґ'-]{2,}$/.test(part));
    };

    const isValidPhone = (value) => {
        const digits = value.replace(/\D/g, '');
        return digits.length >= 10;
    };

    const updateCityUI = (city) => {
        cityLabel.textContent = cityMap[city]?.label || cityMap.all.label;
        addressLabel.textContent = cityMap[city]?.address || cityMap.all.address;
        if (sideCitySelector && sideCitySelector.value !== city) {
            sideCitySelector.value = city;
        }
        if (citySelector && citySelector.value !== city) {
            citySelector.value = city;
        }
        if (cityWelcomeSelect && cityWelcomeSelect.value !== city) {
            cityWelcomeSelect.value = city;
        }
    };

    const openModal = (productId) => {

        const city = citySelector?.value || 'all';
        const cities = Array.isArray(product.cities) && product.cities.length ? product.cities : ['all'];
        const available = cities.includes('all') || cities.includes(city);
        const availabilityText = available
            ? `Доступно у: ${cities.includes('all') ? 'всі міста' : cities.map((c) => cityMap[c]?.label).join(', ')}`
            : 'Немає у вибраному місті, спробуйте інший філіал.';

        modalTitle.textContent = product.name || '';
        modalDescription.textContent = product.description || '';
        const priceText = product.price || (product.priceValue ? String(product.priceValue) : '');
        modalPrice.textContent = priceText;
        if (modalWeight) {
            modalWeight.textContent = product.weight || '';
            modalWeight.style.display = product.weight ? '' : 'none';
        }
        if (modalPieces) {
            modalPieces.textContent = product.pieces || '';
            modalPieces.style.display = product.pieces ? '' : 'none';
        }
        modalImage.setAttribute('src', product.image || '');
        modalImage.setAttribute('alt', product.name || '');
        modalAvailability.textContent = availabilityText;
        if (cartFeedback) cartFeedback.style.display = 'none';

        if (bsModal) {
            bsModal.show();
        } else {
            showFallbackModal();
        }
    };



    consultBtn?.addEventListener('click', () => {
        resetConsultForm();
        showConsultModal();
    });

    checkoutNameEl?.addEventListener('input', () => {
        checkoutNameEl.classList.remove('is-invalid');
    });

    checkoutPhoneEl?.addEventListener('input', () => {
        checkoutPhoneEl.classList.remove('is-invalid');
    });

    consultPhoneEl?.addEventListener('input', () => {
        consultPhoneEl.classList.remove('is-invalid');
    });

    checkoutForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const nameValue = checkoutNameEl ? checkoutNameEl.value : '';
        const phoneValue = checkoutPhoneEl ? checkoutPhoneEl.value : '';
        const nameOk = isValidName(nameValue);
        const phoneOk = isValidPhone(phoneValue);
        if (checkoutNameEl) checkoutNameEl.classList.toggle('is-invalid', !nameOk);
        if (checkoutPhoneEl) checkoutPhoneEl.classList.toggle('is-invalid', !phoneOk);
        if (!nameOk || !phoneOk) return;
        showToast('Замовлення прийнято. Очікуйте дзвінок!');
        hideCheckoutModal();
        clearCart();
    });

    consultForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const phoneValue = consultPhoneEl ? consultPhoneEl.value : '';
        const phoneOk = isValidPhone(phoneValue);
        if (consultPhoneEl) consultPhoneEl.classList.toggle('is-invalid', !phoneOk);
        if (!phoneOk) return;
        showToast('Дякуємо! Ми зателефонуємо найближчим часом.');
        hideConsultModal();
    });

    cityWelcomeSelect?.addEventListener('change', () => {
        cityWelcomeSelect.classList.remove('is-invalid');
    });

    cityWelcomeForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const selectedCity = cityWelcomeSelect ? cityWelcomeSelect.value : '';
        if (!selectedCity) {
            if (cityWelcomeSelect) cityWelcomeSelect.classList.add('is-invalid');
            return;
        }
        setCity(selectedCity, true);
        hideCityWelcomeModal();
    });

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


    if (false) {
        showCityWelcomeModal();
    }
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
