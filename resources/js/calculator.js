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
        frameColor() {
            return this.frames.find((x) => x.key === this.frame)?.color ?? null;
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
            return this.wallWidth < 520 ? 250 : 460;
        },
        // Pixels per cm: the sofa (~200 cm) fills about two thirds of the wall,
        // zooming out only when a tall painting would not fit above it.
        scale() {
            const byWidth = this.wallWidth / 300;
            const byHeight = (this.wallHeight() - 30) / (95 + this.height + 15);
            return Math.min(byWidth, byHeight);
        },
        paintingStyle() {
            const s = this.scale();
            const color = this.frameColor();
            const border = color ? `border: ${Math.max(3, 4 * s)}px solid ${color};` : '';
            return `width:${this.width * s}px;height:${this.height * s}px;bottom:${95 * s}px;${border}`;
        },
    }));

    // "Try it on your own wall": the visitor's own photo of a wall, with the
    // painting dragged and sized on top. Nothing is uploaded.
    // getImage / getSize / getFrame are functions so it follows the parent's choices.
    Alpine.data('roomTry', (getImage, getSize, getFrame) => ({
        open: false,
        room: null,
        x: 50,
        y: 38,
        widthPct: 28,
        dragging: false,

        img() {
            return getImage();
        },
        ratio() {
            const [w, h] = getSize();
            return h / w;
        },
        frameColor() {
            return getFrame();
        },
        pick(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            if (this.room) URL.revokeObjectURL(this.room);
            this.room = URL.createObjectURL(file);
            this.open = true;
        },
        close() {
            this.open = false;
        },
        destroy() {
            if (this.room) URL.revokeObjectURL(this.room);
        },
        overlay() {
            const color = this.frameColor();
            return `left:${this.x}%;top:${this.y}%;width:${this.widthPct}%;aspect-ratio:1/${this.ratio()};`
                + (color ? `border:4px solid ${color};` : '');
        },
        start(event) {
            this.dragging = true;
            event.target.setPointerCapture?.(event.pointerId);
        },
        move(event) {
            if (!this.dragging) return;
            const box = this.$refs.stage.getBoundingClientRect();
            this.x = Math.min(100, Math.max(0, ((event.clientX - box.left) / box.width) * 100));
            this.y = Math.min(100, Math.max(0, ((event.clientY - box.top) / box.height) * 100));
        },
        stop() {
            this.dragging = false;
        },
    }));


    // Product page: the chosen size hung over a sofa, on a light or dark wall.
    Alpine.data('wallPreview', (width, height, frameColor = null) => ({
        frameColor,
        width,
        height,
        roomWidth: 500,
        wall: 'light',
        walls: [
            { key: 'light', label: 'Svijetli zid', color: '#e9e4dc', light: true, sofa: ['#8c8173', '#9d9284'], floor: '#b9ab95' },
            { key: 'sage', label: 'Zeleni zid', color: '#a9b3a0', light: true, sofa: ['#e6dfd3', '#f1ebe1'], floor: '#8a7a63' },
            { key: 'dark', label: 'Tamni zid', color: '#24211e', light: false, sofa: ['#2f2b27', '#3a3530'], floor: '#151311' },
        ],
        init() {
            this.wall = sessionStorage.getItem('wall') ?? 'light';
            this.$watch('wall', (v) => sessionStorage.setItem('wall', v));
            const room = this.$refs.room;
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
        current() {
            return this.walls.find((w) => w.key === this.wall);
        },
        roomHeight() {
            return Math.round(Math.min(420, Math.max(260, this.roomWidth * 0.72)));
        },
        scale() {
            return Math.min(this.roomWidth / 300, (this.roomHeight() - 25) / (95 + this.height + 15));
        },
        paintingBox() {
            const s = this.scale();
            const border = this.frameColor ? `border:${Math.max(3, 3.5 * s)}px solid ${this.frameColor};` : '';
            return `width:${this.width * s}px;height:${this.height * s}px;bottom:${95 * s}px;${border}`;
        },
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
        copied: false,
        sharing: false,
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
        // Link that opens the order calculator with exactly this style and size.
        orderLink() {
            const params = new URLSearchParams({ stil: this.current().slug, w: this.width, h: this.height });
            return `${orderUrl}?${params}#kalkulator`;
        },
        quote() {
            const lines = [
                `Ponuda — Foco Art`,
                `${this.current().name}, ${this.width} × ${this.height} cm`,
                this.discount > 0
                    ? `Cijena: ${this.fmt(this.finalPrice())} KM (popust ${this.discount}%, redovna ${this.fmt(this.price())} KM)`
                    : `Cijena: ${this.fmt(this.finalPrice())} KM`,
                `Plaćanje pouzećem, pri dostavi.`,
                ``,
                `Naručite ovu veličinu: ${this.orderLink()}`,
            ];
            return lines.join('\n');
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
                g.fillStyle = '#5d554b';
                g.fillRect(px, py, pw, ph);
            }

            // Style, size and price.
            g.textAlign = 'center';
            g.fillStyle = '#5d554b';
            g.font = `400 34px ${sans}`;
            g.fillText(`${this.current().name.toUpperCase()}  ·  ${this.width} × ${this.height} cm`, W / 2, 850);
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
        fileName() {
            return `foco-art-ponuda-${this.current().slug}-${this.width}x${this.height}.png`;
        },
        async share() {
            this.sharing = true;
            try {
                const blob = await this.quoteImage();
                const file = blob ? new File([blob], this.fileName(), { type: 'image/png' }) : null;
                // Phones: share the picture and the text together (WhatsApp, Viber, Messenger…).
                if (file && navigator.canShare?.({ files: [file] })) {
                    await navigator.share({ files: [file], text: this.quote(), title: 'Foco Art — ponuda' });
                    return;
                }
                if (navigator.share) {
                    await navigator.share({ title: 'Foco Art — ponuda', text: this.quote() });
                    return;
                }
                // Computers: save the picture and open WhatsApp with the text.
                if (blob) this.download(blob);
                window.open('https://wa.me/?text=' + encodeURIComponent(this.quote()), '_blank');
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
        async copy() {
            try {
                await navigator.clipboard.writeText(this.quote());
            } catch {
                const t = Object.assign(document.createElement('textarea'), { value: this.quote() });
                document.body.append(t);
                t.select();
                document.execCommand('copy');
                t.remove();
            }
            this.copied = true;
            setTimeout(() => (this.copied = false), 1800);
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
