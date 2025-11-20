/**
 * Dynamic Aurora Background
 * Replaces legacy slideshow with lightweight animated canvas + gradients
 */
class DynamicAuroraBackground {
    constructor() {
        this.container = document.getElementById('slideshow-background');
        if (!this.container) return;

        this.particles = [];
        this.prefersReducedMotion = typeof window.matchMedia === 'function'
            ? window.matchMedia('(prefers-reduced-motion: reduce)')
            : null;
        this.maxParticles = window.innerWidth < 768 ? 35 : 75;
        this.animationFrame = null;
        this.resizeObserver = null;

        this.setupBaseLayers();
        this.setupCanvas();
        this.initParticles();
        this.bindEvents();
        this.animate = this.animate.bind(this);
        requestAnimationFrame(this.animate);
    }

    setupBaseLayers() {
        const aurora = document.createElement('div');
        aurora.className = 'aurora-layer';
        this.container.appendChild(aurora);

        const grid = document.createElement('div');
        grid.className = 'grid-glow';
        this.container.appendChild(grid);

        const noise = document.createElement('div');
        noise.className = 'noise-layer';
        this.container.appendChild(noise);
    }

    setupCanvas() {
        this.canvas = document.createElement('canvas');
        this.canvas.className = 'particle-layer';
        this.ctx = this.canvas.getContext('2d');
        this.container.appendChild(this.canvas);

        const overlay = document.createElement('div');
        overlay.className = 'slideshow-overlay';
        this.container.appendChild(overlay);

        this.resizeCanvas();
    }

    bindEvents() {
        this.handleResize = this.resizeCanvas.bind(this);
        window.addEventListener('resize', this.handleResize);

        if (this.prefersReducedMotion && typeof this.prefersReducedMotion.addEventListener === 'function') {
            this.prefersReducedMotion.addEventListener('change', (event) => {
                if (event.matches) {
                    cancelAnimationFrame(this.animationFrame);
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                } else {
                    this.initParticles();
                    this.animate();
                }
            });
        }

        // Adjust particle density when viewport changes significantly
        if (typeof ResizeObserver !== 'undefined') {
            this.resizeObserver = new ResizeObserver(() => {
                this.maxParticles = window.innerWidth < 768 ? 35 : 75;
                this.initParticles();
            });
            this.resizeObserver.observe(this.container);
        }
    }

    resizeCanvas() {
        const { width, height } = this.container.getBoundingClientRect();
        this.canvas.width = width;
        this.canvas.height = height;
    }

    initParticles() {
        this.particles = [];
        for (let i = 0; i < this.maxParticles; i++) {
            this.particles.push(this.createParticle());
        }
    }

    createParticle() {
        const speedFactor = Math.random() * 0.6 + 0.2;
        return {
            x: Math.random() * this.canvas.width,
            y: Math.random() * this.canvas.height,
            size: Math.random() * 2 + 0.8,
            alpha: Math.random() * 0.6 + 0.2,
            vx: (Math.random() - 0.5) * speedFactor,
            vy: (Math.random() - 0.5) * speedFactor,
            color: Math.random() > 0.5 ? '#34d399' : '#60a5fa',
            twinkleOffset: Math.random() * Math.PI * 2
        };
    }

    updateParticle(particle) {
        particle.x += particle.vx;
        particle.y += particle.vy;

        if (particle.x < -50 || particle.x > this.canvas.width + 50 || particle.y < -50 || particle.y > this.canvas.height + 50) {
            Object.assign(particle, this.createParticle());
            return;
        }

        particle.twinkleOffset += 0.02;
        particle.alpha = 0.25 + Math.abs(Math.sin(particle.twinkleOffset)) * 0.45;
    }

    drawParticle(particle) {
        const gradient = this.ctx.createRadialGradient(particle.x, particle.y, 0, particle.x, particle.y, particle.size * 6);
        gradient.addColorStop(0, `${particle.color}cc`);
        gradient.addColorStop(1, `${particle.color}00`);

        this.ctx.fillStyle = gradient;
        this.ctx.beginPath();
        this.ctx.arc(particle.x, particle.y, particle.size * 6, 0, Math.PI * 2);
        this.ctx.fill();
    }

    connectParticles(particle, others) {
        for (let i = 0; i < others.length; i++) {
            const other = others[i];
            const dx = particle.x - other.x;
            const dy = particle.y - other.y;
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance < 140) {
                this.ctx.strokeStyle = `rgba(255,255,255,${0.02 * (1 - distance / 140)})`;
                this.ctx.lineWidth = 0.6;
                this.ctx.beginPath();
                this.ctx.moveTo(particle.x, particle.y);
                this.ctx.lineTo(other.x, other.y);
                this.ctx.stroke();
            }
        }
    }

    animate() {
        if (!this.ctx || (this.prefersReducedMotion && this.prefersReducedMotion.matches)) {
            return;
        }

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.particles.forEach((particle, index) => {
            this.updateParticle(particle);
            this.drawParticle(particle);
            this.connectParticles(particle, this.particles.slice(index + 1));
        });

        this.animationFrame = requestAnimationFrame(this.animate);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.dynamicAuroraBackground = new DynamicAuroraBackground();
});

