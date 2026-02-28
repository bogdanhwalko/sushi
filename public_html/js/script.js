document.addEventListener('DOMContentLoaded', () => {
    const productGridContainer = $('#productGrid');
    const productDetailBlock = $('#productModal');
    const productDetailModalContent = productDetailBlock.find('.modal-body');

    const categoryNextBtn = $('.category-next');
    const categoryFilters = $('#categoryFilters');
    const categoryButtons = categoryFilters.find('button');
    const categoryPrevBtn = $('.category-prev');

    const cartButton = $('#cart-button');
    const cartContent = $('#cart-content');
    const drawer = $('#cartDrawer');

    const cartCountSpan = $('#cartCount');
    const checkoutModalEl = $('#checkoutModal');
    const checkoutNameInput = $('#checkoutName');
    const checkoutPhoneInput = $('#checkoutPhone');
    const checkoutCitySelect = $('#checkoutCity');
    const sideMenuEl = document.getElementById('sideMenu');


    var cartUpdateStatus = true;


    let cartModal = bootstrap.Offcanvas.getOrCreateInstance(drawer[0]);
    let checkoutModal = bootstrap.Modal.getOrCreateInstance(checkoutModalEl[0]);
    let productDetailModal = bootstrap.Modal.getOrCreateInstance(productDetailBlock[0]);

    $('.modal').on('hide.bs.modal', function () {
        const focusedEl = this.querySelector(':focus');
        if (focusedEl) focusedEl.blur();
    });

    /* ---BEGIN [Скрол до потрібної секції] BEGIN--- */
    const scrollToSection = (hash) => {
        if (!hash || hash === '#') return;

        const target = document.querySelector(hash);
        if (!target) return;

        const top = target.getBoundingClientRect().top + window.scrollY;
        window.scrollTo({
            top: Math.max(0, top),
            behavior: 'smooth'
        });

        if (window.location.hash !== hash) {
            history.replaceState(null, '', hash);
        }
    };

    if (sideMenuEl) {
        const sideMenu = bootstrap.Offcanvas.getOrCreateInstance(sideMenuEl);

        sideMenuEl.querySelectorAll('a[href^="#"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const hash = link.getAttribute('href');
                const target = hash ? document.querySelector(hash) : null;

                if (!target) return;

                event.preventDefault();

                const handleHidden = () => scrollToSection(hash);

                if (sideMenuEl.classList.contains('show')) {
                    sideMenuEl.addEventListener('hidden.bs.offcanvas', handleHidden, { once: true });
                    sideMenu.hide();
                    return;
                }

                handleHidden();
            });
        });
    }
    /* ---END [Скрол до потрібної секції] END--- */


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
    productDetailBlock.on('click', '.add-to-cart', addToCart);

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
                    cartCountSpan.text(cartCountSpan.text() * 1 + 1);
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
                    if (res.qty > 0) {
                        cartCountSpan.text(cartCountSpan.text() * 1 - res.qty);
                    }

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
                    totalBlock.text(totalBlock.text() * 1 + itemPrice * prefix);

                    cartCountSpan.text(cartCountSpan.text() * 1 + prefix)

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
    productGridContainer.on('click', '.view-details', detailProduct)
    $('#hero-card').on('click', '.view-details', detailProduct)

    function detailProduct(e)
    {
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

        productDetailModal.show();
    }

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
                cartContent.find('#cartItems').empty();
                cartContent.find('#cartTotal').text(0);
                cartCountSpan.text(0);
                el.prop('disabled', false);
            },
            error: function(xhr) {
                console.log('Помилка при спробі очистити корзину, спробуйте ще раз!')
            }
        });
    })
    /* ---END [Очистити корзину] END--- */


    /* ---BEGIN [Вікно замовлення] BEGIN--- */
    cartContent.on('click', '#checkoutBtn', function (e) {
        if (cartContent.find('#cartTotal').text() < 1) {
            showToast('Для того щоб зробити замовлення потрібно мати більше одного товару в кошику!');
            return false;
        }

        if (localStorage.getItem('phone')) {
            checkoutPhoneInput.val(localStorage.getItem('phone'))
        }

        if (localStorage.getItem('name')) {
            checkoutNameInput.val(localStorage.getItem('name'))
        }

        if (localStorage.getItem('city')) {
            checkoutCitySelect.val(localStorage.getItem('city'));
        }

        drawer[0].addEventListener('hidden.bs.offcanvas', function handler() {
            checkoutModal.show();
            drawer[0].removeEventListener('hidden.bs.offcanvas', handler);
        });

        cartModal.hide();
    });

    $('#order-confirm-button').on('click', function (e)
    {
        let name = nameValidator(checkoutNameInput);
        let phone = phoneValidator(checkoutPhoneInput);
        let city = checkoutCitySelect.val();

        if (name === false || phone === false) {
            return;
        }

        localStorage.setItem('phone', phone);
        localStorage.setItem('name', name);
        localStorage.setItem('city', city);

        let el = $(this);
        el.prop('disabled', true);

        $.ajax({
            url: 'ajax/ajax-cart/confirm',
            method: 'GET',
            dataType: 'json',
            data: {phone: phone, name: name, city: city},
            success: function(res) {
                cartContent.find('#cartItems').remove();
                cartContent.find('#cartTotal').text(0);
                cartCountSpan.text(0);
                el.prop('disabled', false);
                checkoutModal.hide();
                showToast('Замовлення успішно підтверджено!');
            },
            error: function(xhr) {
                console.log('Помилка при спробі підтвердити замовлення!')
            }
        });
    })

    /* ---END [Вікно замовлення] END--- */

    function nameValidator(nameInput)
    {
        let name = nameInput.val();

        let trimName = name.trim();
        if (trimName.length < 2 || trimName.length > 150) {
            nameInput.addClass('is-invalid');
            return false;
        }

        nameInput.removeClass('is-invalid');
        return trimName;
    }


    function phoneValidator(phoneInput)
    {
        let phone = phoneInput.val();

        let digits = phone.replace(/\D/g, '');
        if (digits.length < 9 || digits.length > 12) {
            phoneInput.addClass('is-invalid');
            return false;
        }

        phoneInput.removeClass('is-invalid');
        return digits;
    }



    function showToast(message) {
        const toastEl = document.getElementById('cartToast');
        const toastMessageEl = document.getElementById('cartToastMessage');

        if (!toastEl || !toastMessageEl) return;
        toastMessageEl.textContent = message;

        const toast = bootstrap.Toast.getOrCreateInstance(toastEl, {delay: 2200});
        toast.show();
    }

    document.addEventListener('error', function(e) {
        const img = e.target;

        if (img.tagName === 'IMG') {

            if (img.dataset.fallback === '1') return;

            img.dataset.fallback = '1';
            img.src = '/images/products/no_image.png';
        }
    }, true);


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
});