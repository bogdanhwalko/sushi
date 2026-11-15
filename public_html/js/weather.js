/**
 * Погодні та святкові ефекти поверх сторінки (налаштовуються в адмінці → «Сніг і дощ»).
 *
 * Одне <canvas id="weatherLayer" data-mode data-intensity data-mobile data-color>,
 * яке виводить WeatherEffect::render(). Режими: snow (сніг-крапки), flakes (сніжинки),
 * rain (дощ), leaves (осіннє листя), sakura (пелюстки сакури), confetti (конфеті).
 * data-color — колір для снігу, сніжинок і дощу (порожньо — стандартний); інші мають свою палітру.
 *
 * Не перехоплює кліки, стоїть на паузі у фоновій вкладці, не запускається,
 * якщо в системі ввімкнено «менше анімацій».
 *
 * window.siteWeather.set({mode, intensity, mobile, color, force}) — для живого перегляду в адмінці.
 */
(function () {
    'use strict';

    const MOBILE_MAX = 767.98;
    const TAU = Math.PI * 2;

    /** стандартні кольори режимів, які можна перефарбувати (як WeatherEffect::DEFAULT_COLORS) */
    const DEFAULT_COLORS = { snow: '#ffffff', flakes: '#ffffff', rain: '#8caad7' };

    const PALETTES = {
        leaves: ['#d9622b', '#c2402a', '#e8a33a', '#b5652d', '#e07b2a', '#9c4a1f'],
        sakura: ['#f7b6c8', '#f9cfdc', '#f29bb5', '#fbe1ea', '#f5a3bd'],
        confetti: ['#dd4c2a', '#f2b632', '#3bb273', '#3a86ff', '#c86bfa', '#ff6fa8'],
    };

    /** скільки частинок при середній інтенсивності на екрані ~1440×900 */
    const BASE_COUNT = { snow: 70, flakes: 38, rain: 120, leaves: 22, sakura: 34, confetti: 70 };

    const reduceMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };

    let canvas = null;
    let ctx = null;
    let opts = { mode: 'off', intensity: 2, mobile: true, color: '', force: false };
    let items = [];
    let width = 0;
    let height = 0;
    let frame = 0;
    let lastTime = 0;
    let paint = { fill: '#fff', outline: 'rgba(0,0,0,.3)', rgb: '255, 255, 255' };

    const rand = (min, max) => min + Math.random() * (max - min);
    const pick = (list) => list[Math.floor(Math.random() * list.length)];

    const hexToRgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.substr(i, 2), 16));

    // колір частинок + темніший контур, щоб світлий сніг було видно й на білому фоні
    const updatePaint = () => {
        const color = /^#[0-9a-f]{6}$/i.test(opts.color || '') ? opts.color : (DEFAULT_COLORS[opts.mode] || '#ffffff');
        const rgb = hexToRgb(color);
        const dark = rgb.map((c) => Math.round(c * 0.55));
        paint = {
            fill: color,
            rgb: rgb.join(', '),
            outline: 'rgba(' + dark.join(', ') + ', 0.6)',
        };
    };

    const active = () => opts.mode in BASE_COUNT
        && (opts.mobile || window.innerWidth > MOBILE_MAX)
        && (opts.force || !reduceMotion.matches);

    const targetCount = () => {
        const factor = [0, 0.45, 1, 1.9][opts.intensity] || 1;
        const area = Math.max(0.4, Math.min(1.4, Math.sqrt((width * height) / (1440 * 900))));
        return Math.round(BASE_COUNT[opts.mode] * factor * area);
    };

    /** нова частинка: anywhere — у будь-якому місці екрана (на старті), інакше — над верхнім краєм */
    const make = (anywhere) => {
        const p = {
            x: rand(0, width),
            y: anywhere ? rand(0, height) : rand(-60, -10),
            vx: 0,
            vy: 50,
            r: 3,
            sway: 0,
            phase: rand(0, TAU),
            speed: rand(0.4, 1.2),
            rot: rand(0, TAU),
            vrot: 0,
            flip: 0,
            color: '',
            group: Math.floor(Math.random() * 3),
        };
        switch (opts.mode) {
            case 'snow':
                Object.assign(p, { r: rand(1.2, 3.6), vy: rand(28, 70), sway: rand(10, 35) });
                break;
            case 'flakes':
                Object.assign(p, { r: rand(4, 9), vy: rand(25, 55), sway: rand(15, 40), vrot: rand(-0.8, 0.8) });
                break;
            case 'rain':
                Object.assign(p, { x: rand(-60, width), r: rand(12, 24), vy: rand(650, 1050) });
                p.vx = p.vy * 0.12;
                break;
            case 'leaves':
                Object.assign(p, { r: rand(9, 15), vy: rand(40, 85), sway: rand(40, 90), vrot: rand(-1.5, 1.5), flip: rand(1, 3), color: pick(PALETTES.leaves) });
                break;
            case 'sakura':
                Object.assign(p, { x: rand(-80, width), r: rand(5, 9), vy: rand(30, 65), vx: rand(10, 35), sway: rand(20, 50), vrot: rand(-1.2, 1.2), flip: rand(1.5, 3.5), color: pick(PALETTES.sakura) });
                break;
            case 'confetti':
                Object.assign(p, { r: rand(3, 6), vy: rand(60, 120), sway: rand(10, 30), vrot: rand(-4, 4), flip: rand(3, 7), color: pick(PALETTES.confetti) });
                break;
        }
        return p;
    };

    /** рух усіх частинок; ті, що вилетіли за екран, з'являються знову зверху */
    const move = (dt) => {
        for (let i = 0; i < items.length; i++) {
            const p = items[i];
            p.y += p.vy * dt;
            p.x += p.vx * dt;
            p.rot += p.vrot * dt;
            if (p.y - p.r * 2 > height || p.x - p.sway > width + 60) {
                items[i] = make(false);
            }
        }
    };

    const posX = (p, t) => p.x + Math.sin(t * p.speed + p.phase) * p.sway;

    // ---------- малювання ----------
    // прості форми — групами (один шлях на групу прозорості), складні — кожна окремо з поворотом

    const drawSnow = (t) => {
        ctx.fillStyle = paint.fill;
        ctx.strokeStyle = paint.outline;
        ctx.lineWidth = 0.8;
        for (let g = 0; g < 3; g++) {
            ctx.globalAlpha = 0.55 + g * 0.2;
            ctx.beginPath();
            for (const p of items) {
                if (p.group !== g) continue;
                const x = posX(p, t);
                ctx.moveTo(x + p.r, p.y);
                ctx.arc(x, p.y, p.r, 0, TAU);
            }
            ctx.fill();
            ctx.stroke();
        }
        ctx.globalAlpha = 1;
    };

    const flakePath = (x, y, r, rot) => {
        for (let k = 0; k < 6; k++) {
            const a = rot + (k * Math.PI) / 3;
            const cos = Math.cos(a), sin = Math.sin(a);
            ctx.moveTo(x, y);
            ctx.lineTo(x + cos * r, y + sin * r);
            // гілочки на промені
            const bx = x + cos * r * 0.55, by = y + sin * r * 0.55;
            for (const side of [-1, 1]) {
                const b = a + side * 0.8;
                ctx.moveTo(bx, by);
                ctx.lineTo(bx + Math.cos(b) * r * 0.32, by + Math.sin(b) * r * 0.32);
            }
        }
    };

    const drawFlakes = (t) => {
        ctx.lineCap = 'round';
        for (let g = 0; g < 3; g++) {
            ctx.beginPath();
            for (const p of items) {
                if (p.group === g) flakePath(posX(p, t), p.y, p.r, p.rot);
            }
            ctx.globalAlpha = 0.6 + g * 0.2;
            ctx.strokeStyle = paint.outline;
            ctx.lineWidth = 3;
            ctx.stroke();
            ctx.strokeStyle = paint.fill;
            ctx.lineWidth = 1.4;
            ctx.stroke();
        }
        ctx.globalAlpha = 1;
    };

    const drawRain = () => {
        ctx.lineWidth = 1.1;
        ctx.lineCap = 'round';
        for (let g = 0; g < 3; g++) {
            ctx.strokeStyle = 'rgba(' + paint.rgb + ', ' + (0.35 + g * 0.17) + ')';
            ctx.beginPath();
            for (const p of items) {
                if (p.group !== g) continue;
                const k = p.r / p.vy;
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(p.x - p.vx * k, p.y - p.r);
            }
            ctx.stroke();
        }
    };

    /**
     * Кожна частинка — у своїй системі координат: зсув, поворот, «перевертання» (стискання по осі).
     * minFlip — наскільки найтоншою стає частинка при перевертанні (листя й пелюстки не стають «ниткою»).
     */
    const drawShapes = (t, shape, minFlip) => {
        for (const p of items) {
            const f = Math.cos(t * p.flip + p.phase);
            ctx.save();
            ctx.translate(posX(p, t), p.y);
            ctx.rotate(p.rot);
            ctx.scale(1, (f < 0 ? -1 : 1) * Math.max(minFlip, Math.abs(f)));
            ctx.fillStyle = p.color;
            shape(p.r);
            ctx.restore();
        }
    };

    const leaf = (r) => {
        ctx.beginPath();
        ctx.moveTo(-r, 0);
        ctx.quadraticCurveTo(0, -r * 0.9, r, 0);
        ctx.quadraticCurveTo(0, r * 0.9, -r, 0);
        ctx.fill();
        // прожилка й черешок
        ctx.strokeStyle = 'rgba(80, 30, 10, 0.45)';
        ctx.lineWidth = 0.8;
        ctx.beginPath();
        ctx.moveTo(-r * 1.25, 0);
        ctx.lineTo(r * 0.85, 0);
        ctx.stroke();
    };

    const petal = (r) => {
        ctx.globalAlpha = 0.92;
        ctx.beginPath();
        ctx.moveTo(0, r);
        ctx.quadraticCurveTo(r * 0.95, 0, r * 0.38, -r * 0.9);
        ctx.lineTo(0, -r * 0.6);   // виїмка на кінчику пелюстки
        ctx.lineTo(-r * 0.38, -r * 0.9);
        ctx.quadraticCurveTo(-r * 0.95, 0, 0, r);
        ctx.fill();
        ctx.strokeStyle = 'rgba(190, 80, 115, 0.4)';
        ctx.lineWidth = 0.7;
        ctx.stroke();
        ctx.globalAlpha = 1;
    };

    const confettiPiece = (r) => {
        ctx.fillRect(-r / 2, -r, r, r * 2);
    };

    const tick = (now) => {
        frame = window.requestAnimationFrame(tick);
        const dt = Math.min(0.05, lastTime ? (now - lastTime) / 1000 : 0);
        lastTime = now;
        const t = now / 1000;

        move(dt);
        ctx.clearRect(0, 0, width, height);
        switch (opts.mode) {
            case 'snow': drawSnow(t); break;
            case 'flakes': drawFlakes(t); break;
            case 'rain': drawRain(); break;
            case 'leaves': drawShapes(t, leaf, 0.35); break;
            case 'sakura': drawShapes(t, petal, 0.3); break;
            case 'confetti': drawShapes(t, confettiPiece, 0.05); break;
        }
    };

    const resize = () => {
        if (!canvas) return;
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        if (!active()) return;
        const need = targetCount();
        while (items.length < need) items.push(make(true));
        items.length = need;
    };

    const stop = () => {
        if (frame) window.cancelAnimationFrame(frame);
        frame = 0;
        lastTime = 0;
        if (ctx) ctx.clearRect(0, 0, width, height);
    };

    const restart = () => {
        stop();
        items = [];
        if (!canvas || !active()) {
            if (canvas) canvas.style.display = 'none';
            return;
        }
        canvas.style.display = '';
        updatePaint();
        resize();
        frame = window.requestAnimationFrame(tick);
    };

    const init = () => {
        if (canvas) return;
        const el = document.getElementById('weatherLayer');
        if (!el || !el.getContext) return;
        canvas = el;
        ctx = canvas.getContext('2d');
        opts.mode = canvas.dataset.mode || 'off';
        opts.intensity = parseInt(canvas.dataset.intensity, 10) || 2;
        opts.mobile = canvas.dataset.mobile !== '0';
        opts.color = canvas.dataset.color || '';
        restart();

        let resizeTimer = 0;
        window.addEventListener('resize', () => {
            window.clearTimeout(resizeTimer);
            // перетин межі «телефон/ПК» може ввімкнути чи вимкнути ефект
            resizeTimer = window.setTimeout(() => (active() === !!frame ? resize() : restart()), 150);
        });
        if (reduceMotion.addEventListener) reduceMotion.addEventListener('change', restart);
    };

    window.siteWeather = {
        set: (next) => {
            init();
            if (!canvas) return;
            opts = Object.assign({}, opts, next);
            restart();
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
