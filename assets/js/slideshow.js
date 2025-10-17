/**
 * Background Slideshow Manager
 * Supports both images and videos with smooth transitions
 */

class BackgroundSlideshow {
    constructor(options = {}) {
        this.container = document.getElementById('slideshow-background');
        if (!this.container) return;

        this.slides = options.slides || [];
        this.interval = options.interval || 6000; // 6 seconds per slide
        this.currentIndex = 0;
        this.isPaused = false;
        this.timer = null;
        this.progressTimer = null;
        
        this.init();
    }

    init() {
        if (this.slides.length === 0) return;

        // Create slideshow structure
        this.createSlideElements();
        this.createControls();
        this.createProgressBar();
        
        // Start slideshow
        this.showSlide(0);
        this.startAutoPlay();
        
        // Handle visibility change (pause when tab is hidden)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else if (!this.isPaused) {
                this.play();
            }
        });
    }

    createSlideElements() {
        this.slides.forEach((slide, index) => {
            const slideDiv = document.createElement('div');
            slideDiv.className = 'slideshow-slide';
            slideDiv.dataset.index = index;

            if (slide.type === 'video') {
                const video = document.createElement('video');
                video.src = slide.src;
                video.muted = true;
                video.loop = true;
                video.playsInline = true;
                video.autoplay = false;
                
                // Preload metadata
                video.preload = 'metadata';
                
                slideDiv.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = slide.src;
                img.alt = slide.alt || 'Background slide';
                img.loading = index === 0 ? 'eager' : 'lazy';
                
                // Only apply full viewport styles for background slideshow images
                if (this.container.id === 'slideshow-background') {
                    img.style.width = '100vw';
                    img.style.height = '100vh';
                    img.style.objectFit = 'cover';
                    img.style.objectPosition = 'center';
                    img.style.position = 'absolute';
                    img.style.top = '0';
                    img.style.left = '0';
                }
                
                // Handle image load errors
                img.onerror = function() {
                    console.warn('Failed to load slideshow image:', slide.src);
                };
                
                slideDiv.appendChild(img);
            }

            this.container.appendChild(slideDiv);
        });

        // Add overlay
        const overlay = document.createElement('div');
        overlay.className = 'slideshow-overlay';
        this.container.appendChild(overlay);
    }

    createControls() {
        const controls = document.createElement('div');
        controls.className = 'slideshow-controls';
        controls.innerHTML = `
            <button class="slideshow-control-btn" id="slideshow-prev" title="Previous slide">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button class="slideshow-control-btn" id="slideshow-play-pause" title="Pause">
                <svg id="play-icon" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg id="pause-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
            <button class="slideshow-control-btn" id="slideshow-next" title="Next slide">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        `;

        document.body.appendChild(controls);

        // Event listeners
        document.getElementById('slideshow-prev').addEventListener('click', () => this.prev());
        document.getElementById('slideshow-next').addEventListener('click', () => this.next());
        document.getElementById('slideshow-play-pause').addEventListener('click', () => this.togglePlayPause());
    }

    createProgressBar() {
        const progressContainer = document.createElement('div');
        progressContainer.className = 'slideshow-progress';
        progressContainer.innerHTML = '<div class="slideshow-progress-bar"></div>';
        document.body.appendChild(progressContainer);
        
        this.progressBar = document.querySelector('.slideshow-progress-bar');
    }

    showSlide(index) {
        const slides = this.container.querySelectorAll('.slideshow-slide');
        
        // Remove active class from all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
            const video = slide.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });

        // Add active class to current slide
        const currentSlide = slides[index];
        if (currentSlide) {
            currentSlide.classList.add('active');
            
            // Play video if it's a video slide
            const video = currentSlide.querySelector('video');
            if (video) {
                video.play().catch(e => console.log('Video autoplay prevented:', e));
            }
        }

        this.currentIndex = index;
    }

    next() {
        const nextIndex = (this.currentIndex + 1) % this.slides.length;
        this.showSlide(nextIndex);
        this.resetProgress();
    }

    prev() {
        const prevIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.showSlide(prevIndex);
        this.resetProgress();
    }

    play() {
        if (this.isPaused) {
            this.isPaused = false;
            this.startAutoPlay();
            this.updatePlayPauseIcon();
        }
    }

    pause() {
        this.isPaused = true;
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        if (this.progressTimer) {
            clearInterval(this.progressTimer);
            this.progressTimer = null;
        }
        this.updatePlayPauseIcon();
    }

    togglePlayPause() {
        if (this.isPaused) {
            this.play();
        } else {
            this.pause();
        }
    }

    updatePlayPauseIcon() {
        const playIcon = document.getElementById('play-icon');
        const pauseIcon = document.getElementById('pause-icon');
        
        if (this.isPaused) {
            playIcon.classList.remove('hidden');
            pauseIcon.classList.add('hidden');
        } else {
            playIcon.classList.add('hidden');
            pauseIcon.classList.remove('hidden');
        }
    }

    startAutoPlay() {
        this.timer = setInterval(() => {
            this.next();
        }, this.interval);

        this.startProgressBar();
    }

    startProgressBar() {
        let progress = 0;
        const step = 100 / (this.interval / 50); // Update every 50ms

        if (this.progressTimer) {
            clearInterval(this.progressTimer);
        }

        this.progressTimer = setInterval(() => {
            progress += step;
            if (progress >= 100) {
                progress = 100;
            }
            if (this.progressBar) {
                this.progressBar.style.width = progress + '%';
            }
        }, 50);
    }

    resetProgress() {
        if (this.progressBar) {
            this.progressBar.style.width = '0%';
        }
        if (!this.isPaused) {
            this.startProgressBar();
        }
    }

    destroy() {
        if (this.timer) {
            clearInterval(this.timer);
        }
        if (this.progressTimer) {
            clearInterval(this.progressTimer);
        }
        
        // Clean up DOM elements
        const controls = document.querySelector('.slideshow-controls');
        const progress = document.querySelector('.slideshow-progress');
        if (controls) controls.remove();
        if (progress) progress.remove();
    }
}

// Initialize slideshow when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if slideshow container exists
    if (document.getElementById('slideshow-background')) {
        // Slideshow configuration will be defined in each page
        if (typeof slideshowConfig !== 'undefined') {
            window.backgroundSlideshow = new BackgroundSlideshow(slideshowConfig);
        }
    }
});

