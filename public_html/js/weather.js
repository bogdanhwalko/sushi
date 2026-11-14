/**
 * Сніг або дощ поверх сторінки (налаштовується в адмінці → «Сніг і дощ»).
 *
 * Одне <canvas id="weatherLayer" data-mode="snow|rain" data-intensity="1..3" data-mobile="0|1">,
 * яке виводить WeatherEffect::render(). Не перехоплює кліки, стоїть на паузі у фоновій вкладці,
 * не запускається, якщо в системі ввімкнено «менше анімацій».
 *
 * window.siteWeather.set({mode, intensity, mobile, force}) — для живого перегляду в адмінці.
 */
(function () {
    'use strict';

    const MOBILE_MAX = 767.98;
    const reduceMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };

    let canvas = null;
    let ctx = null;
    let opts = { mode: 'off', intensity: 2, mobile: true, force: false };
    let items = [];
    let width = 0;
    let height = 0;
    let frame = 0;
    let lastTime = 0;

    const rand = (min, max) => min + Math.random() * (max - min);

    const active = () => opts.mode !== 'off'
        && (opts.mobile || window.innerWidth > MOBILE_MAX)
        && (opts.force || !reduceMotion.matches);

    // кількість частинок: від інтенсивності й розміру екрана (на телефоні менше, на великому екрані більше)
    const targetCount = () => {
        const base = opts.mode === 'snow' ? 70 : 120;
        const factor = [0, 0.45, 1, 1.9][opts.intensity] || 1;
        const area = Math.max(0.4, Math.min(1.4, Math.sqrt((width * height) / (1440 * 900))));
        return Math.round(base * factor * area);
    };

    const makeSnow = (anywhere) => ({
        x: rand(0, width),
        y: anywhere ? rand(0, height) : rand(-40, -5),
        r: rand(1.2, 3.6),
        vy: rand(28, 70),
        sway: rand(10, 35),
        phase: rand(0, Math.PI * 2),
        speed: rand(0.4, 1.2),
    });

    const makeDrop = (anywhere) => {
        const vy = rand(650, 1050);
        return {
            x: rand(-60, width),
            y: anywhere ? rand(0, height) : rand(-80, -10),
            len: rand(12, 24),
            vy: vy,
            vx: vy * 0.12,
        };
    };

    const make = (anywhere) => (opts.mode === 'snow' ? makeSnow(anywhere) : makeDrop(anywhere));

    const resize = () => {
        if (!canvas) return;
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const need = targetCount();
        while (items.length < need) items.push(make(true));
        items.length = need;
    };

    // біла сніжинка з тонким сіро-блакитним контуром — видно і на темній шапці, і на білому фоні.
    // Три групи прозорості, кожна — один шлях: швидше, ніж малювати кожну окремо.
    const drawSnow = (dt, t) => {
        ctx.fillStyle = '#fff';
        ctx.strokeStyle = 'rgba(120, 140, 170, 0.55)';
        ctx.lineWidth = 0.8;
        for (let g = 0; g < 3; g++) {
            ctx.globalAlpha = 0.55 + g * 0.2;
            ctx.beginPath();
            for (let i = g; i < items.length; i += 3) {
                const f = items[i];
                f.y += f.vy * dt;
                if (f.y - f.r > height) {
                    items[i] = makeSnow(false);
                    continue;
                }
                const x = f.x + Math.sin(t * f.speed + f.phase) * f.sway;
                ctx.moveTo(x + f.r, f.y);
                ctx.arc(x, f.y, f.r, 0, Math.PI * 2);
            }
            ctx.fill();
            ctx.stroke();
        }
        ctx.globalAlpha = 1;
    };

    const drawRain = (dt) => {
        ctx.lineWidth = 1.1;
        ctx.lineCap = 'round';
        // кілька груп за прозорістю — менше перемикань стилю, ніж окремо кожна крапля
        for (let g = 0; g < 3; g++) {
            ctx.strokeStyle = 'rgba(140, 170, 215, ' + (0.3 + g * 0.15) + ')';
            ctx.beginPath();
            for (let i = g; i < items.length; i += 3) {
                const d = items[i];
                d.y += d.vy * dt;
                d.x += d.vx * dt;
                if (d.y - d.len > height || d.x > width + 40) {
                    items[i] = makeDrop(false);
                    continue;
                }
                const k = d.len / d.vy;
                ctx.moveTo(d.x, d.y);
                ctx.lineTo(d.x - d.vx * k, d.y - d.len);
            }
            ctx.stroke();
        }
    };

    const tick = (now) => {
        frame = window.requestAnimationFrame(tick);
        const dt = Math.min(0.05, lastTime ? (now - lastTime) / 1000 : 0);
        lastTime = now;

        ctx.clearRect(0, 0, width, height);
        if (opts.mode === 'snow') {
            drawSnow(dt, now / 1000);
        } else {
            drawRain(dt);
        }
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
