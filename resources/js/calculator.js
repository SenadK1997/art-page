// ---- Shared by the made-to-measure calculator and the product page wall ----

// Where the painting hangs: floor (14 cm) + sofa (85 cm) + a hand's width above it.
const PAINTING_FLOOR_CM = 14 + 85 + 22;

// Frame materials, lit from the top left like a gallery.
const FRAME_MATERIALS = {
    black: 'linear-gradient(135deg, #4a4642 0%, #2b2926 28%, #1d1b19 62%, #36322e 100%)',
    gold: 'linear-gradient(135deg, #f6dc98 0%, #c9a150 20%, #8d6a2b 42%, #ead08a 60%, #a67d35 80%, #f2d790 100%)',
    silver: 'linear-gradient(135deg, #ffffff 0%, #cfcfcf 22%, #8e8e8e 45%, #ececec 62%, #a2a2a2 82%, #f6f6f6 100%)',
    copper: 'linear-gradient(135deg, #f3c194 0%, #bd7542 25%, #7b4220 48%, #dc9867 66%, #9b552a 86%, #edb17f 100%)',
    white: 'linear-gradient(135deg, #ffffff 0%, #f3f0ea 45%, #dcd6cc 100%)',
    walnut: 'repeating-linear-gradient(97deg, rgba(255,255,255,.05) 0 1px, transparent 1px 5px, rgba(0,0,0,.10) 5px 6px, transparent 6px 11px), linear-gradient(135deg, #93613b 0%, #5c3a21 48%, #3e2615 100%)',
};

const artFraming = {
    frameWidthPx() {
        // About 3.5 cm of moulding, never thinner than a few pixels.
        return this.frame && this.frame !== 'none' && FRAME_MATERIALS[this.frame] ? Math.max(4, 3.5 * this.sceneScale()) : 0;
    },
    artBox() {
        const s = this.sceneScale();
        const fw = this.frameWidthPx();
        const material = FRAME_MATERIALS[this.frame];
        const depth = Math.max(2, 1.2 * s);
        const shadow = `0 ${10 * s}px ${22 * s}px -${4 * s}px rgba(35, 22, 10, .42), 0 ${2 * s}px ${5 * s}px rgba(35, 22, 10, .22)`;
        const look = material
            // Moulding with a bevel: light on the top/left edge, shade on the bottom/right.
            ? `background:${material};padding:${fw}px;box-shadow:${shadow}, inset ${depth / 2}px ${depth / 2}px 0 rgba(255,255,255,.35), inset -${depth / 2}px -${depth / 2}px 0 rgba(0,0,0,.35);`
            // No frame: a stretched canvas with its side edge catching the light.
            : `box-shadow:${shadow}, ${depth}px ${depth}px 0 rgba(0,0,0,.18);`;
        return `width:${this.width * s + 2 * fw}px;height:${this.height * s + 2 * fw}px;bottom:${PAINTING_FLOOR_CM * s}px;${look}`;
    },
    artCanvas() {
        const s = this.sceneScale();
        const lip = this.frameWidthPx() ? `inset 0 0 0 1px rgba(0,0,0,.35), inset ${1.2 * s}px ${1.6 * s}px ${3 * s}px rgba(0,0,0,.38)` : 'inset 0 0 0 1px rgba(0,0,0,.08)';
        const img = this.sceneImage();
        const surface = img
            ? `background-image:url("${img}");`
            : 'background:#ece6da;';
        return `${surface}box-shadow:${lip};`;
    },
};

document.addEventListener('alpine:init', () => {
    Alpine.data('commissionCalculator', (cfg) => ({
        styles: cfg.styles,
        frames: cfg.frames,
        // A shared quote link (?stil=&w=&h=&ram=) opens with exactly that choice.
        styleId: cfg.styles.find((s) => s.slug === cfg.initial?.style)?.id ?? cfg.styles[0]?.id ?? null,
        width: cfg.initial?.width ?? 50,
        height: cfg.initial?.height ?? 70,
        frame: cfg.initial?.frame ?? 'black',
        wallWidth: 600,
        localPhoto: null,
        bar: false,

        init() {
            const wall = this.$refs.wall;
            const measure = () => {
                if (wall.isConnected) this.wallWidth = wall.clientWidth;
            };
            measure();
            this.observer = new ResizeObserver(measure);
            this.observer.observe(wall);
            this.clamp();
            // Each style has its own size limits (set in the admin).
            this.$watch('styleId', () => this.clamp());
            this.watchBuyBar(this.$root, this.$refs.buy);
        },
        destroy() {
            this.observer?.disconnect();
            this.barObserver?.disconnect();
            document.body.classList.remove('has-buy-bar');
            this.clearPhoto();
        },
        // Phones: show the sticky price bar while the calculator is on screen
        // but its own "add to cart" button is not.
        watchBuyBar(area, button) {
            let areaVisible = false;
            let buttonVisible = false;
            const update = () => {
                this.bar = areaVisible && !buttonVisible;
                document.body.classList.toggle('has-buy-bar', this.bar);
            };
            this.barObserver = new IntersectionObserver((entries) => {
                for (const e of entries) {
                    if (e.target === area) areaVisible = e.isIntersecting;
                    if (e.target === button) buttonVisible = e.isIntersecting;
                }
                update();
            });
            this.barObserver.observe(area);
            this.barObserver.observe(button);
        },
        current() {
            return this.styles.find((s) => s.id === this.styleId)
                ?? { name: '', multiplier: 0, image: null, minW: 20, maxW: 200, minH: 20, maxH: 200, photo: false };
        },
        clamp() {
            const s = this.current();
            const c = (v, lo, hi) => Math.min(hi, Math.max(lo, Math.round(Number(v) || lo)));
            this.width = c(this.width, s.minW, s.maxW);
            this.height = c(this.height, s.minH, s.maxH);
        },
        presets() {
            const s = this.current();
            return [[30, 40], [50, 70], [60, 90], [70, 100], [100, 150]]
                .filter(([w, h]) => w >= s.minW && w <= s.maxW && h >= s.minH && h <= s.maxH);
        },
        pickPhoto(event) {
            const file = event.target.files?.[0];
            this.clearPhoto();
            if (file && file.type.startsWith('image/')) this.localPhoto = URL.createObjectURL(file);
        },
        clearPhoto() {
            if (this.localPhoto) URL.revokeObjectURL(this.localPhoto);
            this.localPhoto = null;
        },
        // The customer's own photo when they picked one, else the style sample.
        image() {
            return (this.current().photo && this.localPhoto) || this.current().image;
        },
        // Same rule as App\Support\PriceCalculator: round(w × h / 100 × multiplier)
        price() {
            return Math.round((this.width * this.height) / 100 * this.current().multiplier);
        },
        formatted() {
            return this.fmt(this.price());
        },
        // 7000 -> "7.000" (browsers often lack the bs-BA locale)
        fmt(n) {
            return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        wallHeight() {
            // Smaller on phones, where the wall stays pinned above the controls.
            return this.wallWidth < 520 ? 260 : 470;
        },
        // Pixels per cm: the sofa (~200 cm) fills about two thirds of the wall,
        // zooming out only when a tall painting would not fit above it.
        scale() {
            const byWidth = this.wallWidth / 300;
            const byHeight = (this.wallHeight() - 14) / (PAINTING_FLOOR_CM + this.height + 14);
            return Math.min(byWidth, byHeight);
        },
        // Used by <x-room-scene>.
        sceneScale() {
            return this.scale();
        },
        sceneHeight() {
            return this.wallHeight();
        },
        sceneImage() {
            return this.image();
        },
        ...artFraming,
    }));

    // Product page: the chosen size hung over a sofa, on a light, green or dark wall.
    Alpine.data('wallPreview', (width, height, frame = 'none', image = null) => ({
        width,
        height,
        frame,
        image,
        roomWidth: 500,
        wall: 'light',
        walls: [
            { key: 'light', label: 'Svijetli zid', color: '#ebe4d9' },
            { key: 'sage', label: 'Zeleni zid', color: '#a8b29f' },
            { key: 'dark', label: 'Tamni zid', color: '#2a2724' },
        ],
        init() {
            this.wall = sessionStorage.getItem('wall') ?? 'light';
            this.$watch('wall', (v) => sessionStorage.setItem('wall', v));
            const room = this.$refs.wall;
            const measure = () => {
                if (room.isConnected) this.roomWidth = room.clientWidth;
            };
            measure();
            this.observer = new ResizeObserver(measure);
            this.observer.observe(room);
        },
        destroy() {
            this.observer?.disconnect();
        },
        sceneHeight() {
            return Math.round(Math.min(440, Math.max(270, this.roomWidth * 0.74)));
        },
        sceneScale() {
            return Math.min(this.roomWidth / 300, (this.sceneHeight() - 14) / (PAINTING_FLOOR_CM + this.height + 14));
        },
        sceneImage() {
            return this.image;
        },
        ...artFraming,
    }));

    // Product page on phones: sticky price bar until the real button is visible.
    Alpine.data('buyBar', () => ({
        bar: false,
        init() {
            const button = this.$refs.buy;
            if (!button) return;
            this.observer = new IntersectionObserver(([entry]) => {
                this.bar = !entry.isIntersecting;
                document.body.classList.toggle('has-buy-bar', this.bar);
            });
            this.observer.observe(button);
        },
        destroy() {
            this.observer?.disconnect();
            document.body.classList.remove('has-buy-bar');
        },
    }));
    // /kalkulator: the owner's quick price calculator (installable app).
    Alpine.data('quickCalc', (styles, orderUrl) => ({
        styles,
        styleId: Number(localStorage.getItem('qc.style')) || styles[0]?.id,
        width: Number(localStorage.getItem('qc.w')) || 50,
        height: Number(localStorage.getItem('qc.h')) || 70,
        discount: 0,
        presets: [[30, 40], [40, 50], [50, 70], [60, 80], [60, 90], [70, 100], [80, 120], [100, 150]],
        sharing: false,
        saved: false,
        offline: !navigator.onLine,
        canInstall: false,
        installEvent: null,
        showIosHint: false,

        init() {
            if (!this.styles.some((s) => s.id === this.styleId)) this.styleId = this.styles[0]?.id;
            // Remember the last calculation between visits.
            this.$watch('styleId', (v) => localStorage.setItem('qc.style', v));
            this.$watch('width', (v) => localStorage.setItem('qc.w', v));
            this.$watch('height', (v) => localStorage.setItem('qc.h', v));

            addEventListener('online', () => (this.offline = false));
            addEventListener('offline', () => (this.offline = true));

            // Android/Chrome: offer our own "Instaliraj" button.
            addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.installEvent = e;
                this.canInstall = true;
            });
            addEventListener('appinstalled', () => (this.canInstall = false));

            // iPhone: there is no prompt, so explain "Add to Home Screen" once.
            const standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            const ios = /iphone|ipad|ipod/i.test(navigator.userAgent);
            this.showIosHint = ios && !standalone && !localStorage.getItem('qc.iosHint');
        },
        current() {
            return this.styles.find((s) => s.id === this.styleId) ?? this.styles[0];
        },
        step(dim, delta) {
            this[dim] = Math.max(5, Math.min(999, (Number(this[dim]) || 0) + delta));
        },
        // Same rule as the website: round(w × h / 100 × multiplier)
        priceFor(style) {
            const w = Number(this.width) || 0;
            const h = Number(this.height) || 0;
            return Math.round((w * h) / 100 * style.multiplier);
        },
        price() {
            return this.priceFor(this.current());
        },
        finalPrice() {
            const d = Math.min(90, Math.max(0, Number(this.discount) || 0));
            return Math.round(this.price() * (100 - d) / 100);
        },
        outOfRange() {
            const s = this.current();
            return this.width < s.minW || this.width > s.maxW || this.height < s.minH || this.height > s.maxH;
        },
        fmt(n) {
            return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        // A 1080 × 1350 picture of the quote (the size WhatsApp and Instagram show best).
        async quoteImage() {
            const W = 1080;
            const H = 1350;
            const c = Object.assign(document.createElement('canvas'), { width: W, height: H });
            const g = c.getContext('2d');
            const serif = '"Cormorant Garamond", Georgia, serif';
            const sans = 'Inter, system-ui, sans-serif';
            await Promise.all([
                document.fonts.load(`500 120px ${serif}`),
                document.fonts.load(`400 40px ${sans}`),
            ]).catch(() => {});
            const load = (src) => new Promise((resolve) => {
                if (!src) return resolve(null);
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = () => resolve(null);
                img.src = src;
            });
            const [logo, sample] = await Promise.all([load('/images/logo.png'), load(this.current().image)]);

            // Ivory page, black header like the site.
            g.fillStyle = '#f6f2ea';
            g.fillRect(0, 0, W, H);
            g.fillStyle = '#0e0d0c';
            g.fillRect(0, 0, W, 200);
            if (logo) g.drawImage(logo, 64, 40, 120, 120);
            g.fillStyle = '#ede9e3';
            g.font = `500 54px ${serif}`;
            g.textBaseline = 'middle';
            g.fillText('FOCO', 216, 100);
            g.fillStyle = '#c9a15e';
            g.fillText('ART', 216 + g.measureText('FOCO ').width, 100);
            g.textAlign = 'right';
            g.fillStyle = '#9a938a';
            g.font = `400 28px ${sans}`;
            g.fillText('PONUDA · SLIKA PO MJERI', W - 64, 100);
            g.textAlign = 'left';

            // The canvas drawn to proportion on a wall, with the style sample inside.
            const box = { x: 64, y: 250, w: W - 128, h: 520 };
            g.fillStyle = '#ebe5da';
            g.fillRect(box.x, box.y, box.w, box.h);
            const ratio = this.width / this.height;
            const maxW = box.w - 160;
            const maxH = box.h - 120;
            const pw = ratio > maxW / maxH ? maxW : maxH * ratio;
            const ph = pw / ratio;
            const px = box.x + (box.w - pw) / 2;
            const py = box.y + (box.h - ph) / 2;
            g.shadowColor = 'rgba(60, 40, 20, 0.35)';
            g.shadowBlur = 40;
            g.shadowOffsetY = 18;
            g.fillStyle = '#2b2926'; // matte charcoal frame
            g.fillRect(px - 12, py - 12, pw + 24, ph + 24);
            g.shadowColor = 'transparent';
            if (sample) {
                // Cover-fit the sample into the canvas rectangle.
                const s = Math.max(pw / sample.width, ph / sample.height);
                const sw = pw / s;
                const sh = ph / s;
                g.drawImage(sample, (sample.width - sw) / 2, (sample.height - sh) / 2, sw, sh, px, py, pw, ph);
            } else {
                // Blank canvas colour with a faint weave.
                g.fillStyle = '#f4f0e8';
                g.fillRect(px, py, pw, ph);
                g.strokeStyle = 'rgba(120, 100, 70, 0.05)';
                g.lineWidth = 1;
                for (let x = px; x < px + pw; x += 6) {
                    g.beginPath();
                    g.moveTo(x, py);
                    g.lineTo(x, py + ph);
                    g.stroke();
                }
                for (let y = py; y < py + ph; y += 6) {
                    g.beginPath();
                    g.moveTo(px, y);
                    g.lineTo(px + pw, y);
                    g.stroke();
                }
            }

            // Size as an L of measuring lines inside the canvas:
            // width along the top, height down the left side.
            this.drawSizeLines(g, px, py, pw, ph, sans, Boolean(sample));

            // Style name under the painting, then the price.
            g.textAlign = 'center';
            g.textBaseline = 'middle';
            g.fillStyle = '#1c1a17';
            g.font = `500 64px ${serif}`;
            g.fillText(this.current().name, W / 2, 852);
            g.fillStyle = '#8d6828';
            g.font = `500 150px ${serif}`;
            g.fillText(`${this.fmt(this.finalPrice())} KM`, W / 2, 990);
            if (this.discount > 0) {
                const old = `${this.fmt(this.price())} KM`;
                g.font = `400 36px ${sans}`;
                g.fillStyle = '#8a8073';
                const label = `${old}   ·   popust ${this.discount}%`;
                g.fillText(label, W / 2, 1090);
                const oldW = g.measureText(old).width;
                const startX = W / 2 - g.measureText(label).width / 2;
                g.fillRect(startX, 1090, oldW, 3);
            }

            // Footer.
            g.fillStyle = '#0e0d0c';
            g.fillRect(0, H - 150, W, 150);
            g.fillStyle = '#ede9e3';
            g.font = `400 32px ${sans}`;
            g.fillText('Plaćanje pouzećem  ·  ručno naslikano', W / 2, H - 98);
            g.fillStyle = '#c9a15e';
            g.font = `500 34px ${sans}`;
            g.fillText(new URL(orderUrl).host, W / 2, H - 50);

            return new Promise((resolve) => c.toBlob(resolve, 'image/png'));
        },
        drawSizeLines(g, px, py, pw, ph, font, onImage) {
            const inset = 34;
            const gap = 46; // keeps the two lines from touching in the corner
            const lineColor = onImage ? '#f4f0e8' : '#5d554b';
            const arrow = (x1, y1, x2, y2) => {
                const a = Math.atan2(y2 - y1, x2 - x1);
                const s = 16;
                g.beginPath();
                g.moveTo(x1, y1);
                g.lineTo(x2, y2);
                g.stroke();
                for (const [x, y, dir] of [[x1, y1, a + Math.PI], [x2, y2, a]]) {
                    g.beginPath();
                    g.moveTo(x, y);
                    g.lineTo(x - s * Math.cos(dir - 0.45), y - s * Math.sin(dir - 0.45));
                    g.moveTo(x, y);
                    g.lineTo(x - s * Math.cos(dir + 0.45), y - s * Math.sin(dir + 0.45));
                    g.stroke();
                }
            };
            const label = (text, x, y, rotation) => {
                g.save();
                g.translate(x, y);
                g.rotate(rotation);
                g.font = `500 40px ${font}`;
                g.textAlign = 'center';
                g.textBaseline = 'middle';
                const tw = g.measureText(text).width + 24;
                g.fillStyle = '#f4f0e8';
                g.fillRect(-tw / 2, -26, tw, 52);
                g.fillStyle = '#2b2926';
                g.fillText(text, 0, 2);
                g.restore();
            };

            g.save();
            g.strokeStyle = lineColor;
            g.lineWidth = 3;
            if (onImage) {
                g.shadowColor = 'rgba(0, 0, 0, 0.6)';
                g.shadowBlur = 6;
            }
            const topY = py + inset;
            const leftX = px + inset;
            arrow(px + inset + gap, topY, px + pw - inset, topY);
            arrow(leftX, py + inset + gap, leftX, py + ph - inset);
            g.restore();

            label(`${this.width} cm`, px + inset + gap + (pw - 2 * inset - gap) / 2, topY, 0);
            label(`${this.height} cm`, leftX, py + inset + gap + (ph - 2 * inset - gap) / 2, -Math.PI / 2);
        },
        fileName() {
            return `foco-art-ponuda-${this.current().slug}-${this.width}x${this.height}.png`;
        },
        async share() {
            this.sharing = true;
            try {
                const blob = await this.quoteImage();
                const file = blob ? new File([blob], this.fileName(), { type: 'image/png' }) : null;
                // Phones: share just the picture (WhatsApp, Viber, Messenger…), no link or text.
                if (file && navigator.canShare?.({ files: [file] })) {
                    await navigator.share({ files: [file] });
                    return;
                }
                // Computers can't share pictures: save it so it can be sent by hand.
                if (blob) {
                    this.download(blob);
                    this.saved = true;
                    setTimeout(() => (this.saved = false), 2500);
                }
            } catch (e) {
                if (e?.name !== 'AbortError') console.error(e);
            } finally {
                this.sharing = false;
            }
        },
        async saveImage() {
            const blob = await this.quoteImage();
            if (blob) this.download(blob);
        },
        download(blob) {
            const a = Object.assign(document.createElement('a'), { href: URL.createObjectURL(blob), download: this.fileName() });
            document.body.append(a);
            a.click();
            setTimeout(() => {
                URL.revokeObjectURL(a.href);
                a.remove();
            }, 1000);
        },
        async install() {
            if (!this.installEvent) return;
            this.installEvent.prompt();
            await this.installEvent.userChoice;
            this.installEvent = null;
            this.canInstall = false;
        },
        dismissIosHint() {
            localStorage.setItem('qc.iosHint', '1');
            this.showIosHint = false;
        },
    }));
});
