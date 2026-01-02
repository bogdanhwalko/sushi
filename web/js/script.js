const productData = {
  'signature-set': {
    name: 'UMI Signature Set',
    price: '649 ₴',
    priceValue: 649,
    description: '24 шт. Лосось, тунець, вугор, сніжний краб та фірмовий цитрусовий соус.',
    weight: '820 г',
    pieces: '24 шт',
    cities: ['kyiv', 'lviv'],
    image: 'assets/sushi-signature.svg'
  },
  philadelphia: {
    name: 'Філадельфія',
    price: '389 ₴',
    priceValue: 389,
    description: 'Класичний рол з лососем, вершковим сиром та кунжутом.',
    weight: '260 г',
    pieces: '8 шт',
    cities: ['kyiv', 'lviv'],
    image: 'assets/sushi-philadelphia.svg'
  },
  dragon: {
    name: 'Дракон',
    price: '429 ₴',
    priceValue: 429,
    description: 'Вугор, авокадо, соус теріякі та хрусткий кунжут у карамелізованій глазурі.',
    weight: '280 г',
    pieces: '8 шт',
    cities: ['kyiv', 'odesa'],
    image: 'assets/sushi-dragon.svg'
  },
  veggie: {
    name: 'Веггі сет',
    price: '329 ₴',
    priceValue: 329,
    description: 'Авокадо, тофу, огірок та горіховий соус для легкого зеленого профілю.',
    weight: '540 г',
    pieces: '16 шт',
    cities: ['lviv', 'odesa'],
    image: 'assets/sushi-veggie.svg'
  },
  haru: {
    name: 'Сет Хару',
    price: '749 ₴',
    priceValue: 749,
    description: 'Лосось, сніжний краб, сир, 32 шт для компаній та свят.',
    weight: '980 г',
    pieces: '32 шт',
    cities: ['kyiv'],
    image: 'assets/sushi-haru.svg'
  },
  spring: {
    name: 'Спрінг роли',
    price: '279 ₴',
    priceValue: 279,
    description: 'Креветка, манго, чилі та соус спайсі-майо для балансу свіжості й пікантності.',
    weight: '190 г',
    pieces: '6 шт',
    cities: ['all'],
    image: 'assets/sushi-spring.svg'
  },
  'sake-maki': {
    name: 'Саке маві',
    price: '309 ₴',
    priceValue: 309,
    description: 'Лосось, огірок, такуан та легкий цитрусовий акцент з юдзу.',
    weight: '240 г',
    pieces: '8 шт',
    cities: ['lviv'],
    image: 'assets/sushi-sake.svg'
  }
};

const cityMap = {
  all: { label: 'Всі міста', address: 'Київ, Львів, Одеса' },
  kyiv: { label: 'Київ', address: 'Київ, вул. Антоновича, 90' },
  lviv: { label: 'Львів', address: 'Львів, вул. Кульпарківська, 65' },
  odesa: { label: 'Одеса', address: 'Одеса, Французький бульвар, 15' }
};

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
  const cartCountMobileEl = document.getElementById('cartCountMobile');
  const addToCartBtn = document.getElementById('addToCartBtn');
  const cartFeedback = document.getElementById('cartFeedback');
  const categoryFilters = document.getElementById('categoryFilters');
  const cartItemsContainer = document.getElementById('cartItems');
  const cartTotalEl = document.getElementById('cartTotal');
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

  let activeProductId = null;
  let currentCategory = 'all';
  const cart = {};

  // Bootstrap modal if available; otherwise fallback to manual toggling.
  let bsModal = null;
  try {
    bsModal = window.bootstrap && modalElement ? new bootstrap.Modal(modalElement) : null;
  } catch (e) {
    bsModal = null;
  }

  let checkoutModal = null;
  try {
    checkoutModal = window.bootstrap && checkoutModalEl ? new bootstrap.Modal(checkoutModalEl) : null;
  } catch (e) {
    checkoutModal = null;
  }

  let consultModal = null;
  try {
    consultModal = window.bootstrap && consultModalEl ? new bootstrap.Modal(consultModalEl) : null;
  } catch (e) {
    consultModal = null;
  }

  let cityWelcomeModal = null;
  try {
    cityWelcomeModal = window.bootstrap && cityWelcomeModalEl ? new bootstrap.Modal(cityWelcomeModalEl) : null;
  } catch (e) {
    cityWelcomeModal = null;
  }

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

  const hideCheckoutModal = () => {
    if (checkoutModal) {
      checkoutModal.hide();
      return;
    }
    if (!checkoutModalEl) return;
    checkoutModalEl.classList.remove('show');
    checkoutModalEl.style.display = 'none';
    checkoutModalEl.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    if (checkoutBackdropEl && checkoutBackdropEl.parentNode) {
      checkoutBackdropEl.parentNode.removeChild(checkoutBackdropEl);
    }
    checkoutBackdropEl = null;
  };

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
    { start: 12 * 60, end: 15 * 60 },
    { start: 19 * 60, end: 22 * 60 }
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

  const filterProducts = (city = citySelector?.value || 'all') => {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach((card) => {
      const cities = (card.dataset.cities || '').split(',');
      const category = card.dataset.category || 'all';
      const matchesCity = city === 'all' || cities.includes(city) || cities.includes('all');
      const matchesCategory = currentCategory === 'all' || category === currentCategory;
      const isVisible = matchesCity && matchesCategory;
      card.style.display = isVisible ? '' : 'none';
    });
  };

  const openModal = (productId) => {
    const product = productData[productId];
    if (!product) return;
    activeProductId = productId;

    const city = citySelector?.value || 'all';
    const available = product.cities.includes('all') || product.cities.includes(city);
    const availabilityText = available
      ? `Доступно у: ${product.cities.includes('all') ? 'всі міста' : product.cities.map((c) => cityMap[c]?.label).join(', ')}`
      : 'Немає у вибраному місті, спробуйте інший філіал.';

    modalTitle.textContent = product.name;
    modalDescription.textContent = product.description;
    modalPrice.textContent = product.price;
    modalWeight.textContent = product.weight;
    modalPieces.textContent = product.pieces;
    modalImage.setAttribute('src', product.image);
    modalImage.setAttribute('alt', product.name);
    modalAvailability.textContent = availabilityText;
    if (cartFeedback) cartFeedback.style.display = 'none';

    if (bsModal) {
      bsModal.show();
    } else {
      showFallbackModal();
    }
  };

  const updateCartBadgeAndTotal = () => {
    const totalQty = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
    const totalPrice = Object.entries(cart).reduce((sum, [id, qty]) => {
      const product = productData[id];
      if (!product) return sum;
      return sum + (product.priceValue || 0) * qty;
    }, 0);

    if (cartCountEl) cartCountEl.textContent = totalQty;
    if (cartCountSideEl) cartCountSideEl.textContent = totalQty;
    if (cartCountMobileEl) cartCountMobileEl.textContent = totalQty;
    if (cartTotalEl) cartTotalEl.textContent = `${totalPrice.toFixed(0)} ₴`;
  };

  const renderCart = () => {
    if (!cartItemsContainer) return;
    cartItemsContainer.innerHTML = '';
    const items = Object.entries(cart);
    if (items.length === 0) {
      cartItemsContainer.innerHTML = '<p class="text-muted small mb-0">Кошик порожній. Додайте роли з меню.</p>';
      updateCartBadgeAndTotal();
      return;
    }

    items.forEach(([id, qty]) => {
      const product = productData[id];
      if (!product) return;
      const lineTotal = (product.priceValue || 0) * qty;
      const line = document.createElement('div');
      line.className = 'cart-line d-flex gap-3 align-items-center';
      line.dataset.product = id;
      line.innerHTML = `
        <img class="cart-thumb" src="${product.image}" alt="${product.name}">
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <h6 class="fw-semibold mb-1">${product.name}</h6>
            <div class="price">${lineTotal.toFixed(0)} ₴</div>
          </div>
          <div class="text-muted small">${product.price} • ${product.weight}</div>
          <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
            <div class="qty-control" aria-label="Кількість">
              <button class="qty-btn" type="button" data-action="dec" aria-label="Зменшити">-</button>
              <span class="qty-value">${qty}</span>
              <button class="qty-btn" type="button" data-action="inc" aria-label="Збільшити">+</button>
            </div>
            <button class="btn btn-link text-danger text-decoration-none p-0 small remove-item" type="button" data-product="${id}">Прибрати</button>
          </div>
        </div>
      `;
      cartItemsContainer.appendChild(line);
    });
    updateCartBadgeAndTotal();
  };

  const addToCart = (productId, showFeedback = false) => {
    if (!productData[productId]) return;
    cart[productId] = (cart[productId] || 0) + 1;
    renderCart();
    if (showFeedback && cartFeedback) {
      cartFeedback.style.display = 'inline';
      setTimeout(() => {
        cartFeedback.style.display = 'none';
      }, 1500);
    }
    showToast(`${productData[productId].name} додано у кошик.`);
  };

  const adjustQuantity = (productId, delta) => {
    if (!cart[productId]) return;
    const nextQty = cart[productId] + delta;
    if (nextQty <= 0) {
      delete cart[productId];
    } else {
      cart[productId] = nextQty;
    }
    renderCart();
  };

  const removeFromCart = (productId) => {
    if (!cart[productId]) return;
    delete cart[productId];
    renderCart();
  };

  const clearCart = () => {
    Object.keys(cart).forEach((k) => delete cart[k]);
    renderCart();
  };

  citySelector?.addEventListener('change', (e) => {
    const city = e.target.value;
    setCity(city, true);
  });

  sideCitySelector?.addEventListener('change', (e) => {
    const city = e.target.value;
    setCity(city, true);
  });

  document.addEventListener('click', (e) => {
    const detailBtn = e.target.closest('.view-details');
    if (detailBtn && detailBtn.dataset.product) {
      openModal(detailBtn.dataset.product);
    }
    const addBtn = e.target.closest('.add-to-cart');
    if (addBtn && addBtn.dataset.product) {
      addToCart(addBtn.dataset.product);
    }
    if (!window.bootstrap) {
      const isClose = e.target.matches('[data-bs-dismiss="modal"]') || e.target.classList.contains('btn-close');
      if (isClose && e.target.closest('#productModal')) {
        hideFallbackModal();
      }
      if (isClose && e.target.closest('#checkoutModal')) {
        hideCheckoutModal();
      }
      if (isClose && e.target.closest('#consultModal')) {
        hideConsultModal();
      }
    }
    const qtyBtn = e.target.closest('.qty-btn');
    if (qtyBtn) {
      const line = qtyBtn.closest('.cart-line');
      const productId = line?.dataset.product;
      if (productId) {
        adjustQuantity(productId, qtyBtn.dataset.action === 'inc' ? 1 : -1);
      }
    }
    const removeBtn = e.target.closest('.remove-item');
    if (removeBtn && removeBtn.dataset.product) {
      removeFromCart(removeBtn.dataset.product);
    }
  });

  if (closeBtn && !bsModal) {
    closeBtn.addEventListener('click', hideFallbackModal);
  }

  addToCartBtn?.addEventListener('click', () => {
    if (!activeProductId) return;
    addToCart(activeProductId, true);
  });

  clearCartBtn?.addEventListener('click', clearCart);

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

  const cartDrawer = document.getElementById('cartDrawer');
  if (cartDrawer) {
    cartDrawer.addEventListener('show.bs.offcanvas', renderCart);
  }

  const sideMenu = document.getElementById('sideMenu');
  if (sideMenu) {
    let pendingScrollTarget = null;

    sideMenu.addEventListener('hidden.bs.offcanvas', () => {
      if (!pendingScrollTarget) return;
      const targetEl = document.querySelector(pendingScrollTarget);
      if (targetEl) {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      pendingScrollTarget = null;
    });

    sideMenu.addEventListener('click', (e) => {
      const link = e.target.closest('a[href^="#"]');
      if (!link) return;
      const targetId = link.getAttribute('href');
      const targetEl = targetId && targetId !== '#' ? document.querySelector(targetId) : null;
      if (targetEl) {
        e.preventDefault();
        pendingScrollTarget = targetId;
      }

      if (window.bootstrap && typeof bootstrap.Offcanvas === 'function') {
        const instance = bootstrap.Offcanvas.getInstance(sideMenu) || new bootstrap.Offcanvas(sideMenu);
        instance.hide();
      } else {
        sideMenu.classList.remove('show');
        sideMenu.setAttribute('aria-hidden', 'true');
        const backdrop = document.querySelector('.offcanvas-backdrop');
        if (backdrop) backdrop.remove();
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        if (pendingScrollTarget) {
          const fallbackTarget = document.querySelector(pendingScrollTarget);
          if (fallbackTarget) {
            fallbackTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
          pendingScrollTarget = null;
        }
      }
    });
  }

  const renderCategoryFilters = () => {
    if (!categoryFilters) return;
    const cards = Array.from(document.querySelectorAll('.product-card'));
    const categorySet = new Set(['all']);
    cards.forEach((card) => {
      const cat = card.dataset.category;
      if (cat) categorySet.add(cat);
    });
    categoryFilters.innerHTML = '';
    categorySet.forEach((cat) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-outline-dark btn-sm';
      btn.dataset.category = cat;
      btn.textContent = cat === 'all' ? 'Усе' : cat.charAt(0).toUpperCase() + cat.slice(1);
      if (cat === currentCategory) btn.classList.add('active');
      categoryFilters.appendChild(btn);
    });

    categoryFilters.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-category]');
      if (!btn) return;
      currentCategory = btn.dataset.category || 'all';
      categoryFilters.querySelectorAll('button').forEach((b) => {
        b.classList.toggle('active', b === btn);
      });
      filterProducts();
    });
  };

  const storedCity = getStoredCity();
  const initialCity = storedCity && cityMap[storedCity] ? storedCity : (citySelector?.value || 'all');
  renderCategoryFilters();
  setCity(initialCity, !!storedCity);
  if (!storedCity) {
    showCityWelcomeModal();
  }
  initSnow();
  updateHappyHours();
  setInterval(updateHappyHours, 60000);
  renderCart();
  updateCartBadgeAndTotal();
});

function showToast(message) {
  const toastEl = document.getElementById('cartToast');
  const toastMessageEl = document.getElementById('cartToastMessage');
  if (!toastEl || !toastMessageEl) return;
  toastMessageEl.textContent = message;

  if (window.bootstrap && typeof bootstrap.Toast === 'function') {
    const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2200 });
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
