document.addEventListener('alpine:init', () => {
    Alpine.data('commissionCalculator', (cfg) => ({
        styles: cfg.styles,
        frames: cfg.frames,
        styleId: cfg.styles[0]?.id ?? null,
        width: 50,
        height: 70,
        frame: 'black',
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
});
