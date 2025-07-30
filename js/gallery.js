// ===== GALLERY JAVASCRIPT =====

class PhotoGallery {
    constructor() {
        this.galleryGrid = document.getElementById('gallery-grid');
        this.currentImageIndex = 0;
        this.images = [];
        this.lightbox = null;
        
        this.init();
    }

    init() {
        this.loadGalleryData();
        this.createGallery();
        this.createLightbox();
        this.bindEvents();
    }

    loadGalleryData() {
        // Sample gallery data - replace with actual images
        this.images = [
            {
                id: 1,
                title: "Web Development",
                description: "Modern web development showcase with responsive design",
                image: "🌐",
                category: "web",
                featured: true
            },
            {
                id: 2,
                title: "Mobile App Design",
                description: "Cross-platform mobile application interface",
                image: "📱",
                category: "mobile",
                featured: false
            },
            {
                id: 3,
                title: "Data Visualization",
                description: "Interactive charts and data analysis dashboard",
                image: "📊",
                category: "data",
                featured: true
            },
            {
                id: 4,
                title: "UI/UX Design",
                description: "User interface and experience design concepts",
                image: "🎨",
                category: "design",
                featured: false
            },
            {
                id: 5,
                title: "E-commerce Platform",
                description: "Online shopping platform with modern features",
                image: "🛒",
                category: "web",
                featured: true
            },
            {
                id: 6,
                title: "Game Development",
                description: "Interactive gaming experience with modern graphics",
                image: "🎮",
                category: "gaming",
                featured: false
            },
            {
                id: 7,
                title: "Social Media App",
                description: "Social networking application with real-time features",
                image: "📲",
                category: "mobile",
                featured: false
            },
            {
                id: 8,
                title: "Dashboard Analytics",
                description: "Business intelligence and analytics platform",
                image: "📈",
                category: "data",
                featured: true
            },
            {
                id: 9,
                title: "Portfolio Website",
                description: "Professional portfolio showcasing creative work",
                image: "💼",
                category: "web",
                featured: false
            },
            {
                id: 10,
                title: "Weather App",
                description: "Real-time weather application with location services",
                image: "🌤️",
                category: "mobile",
                featured: false
            },
            {
                id: 11,
                title: "Task Management",
                description: "Project management and task tracking system",
                image: "📋",
                category: "web",
                featured: false
            },
            {
                id: 12,
                title: "Music Player",
                description: "Streaming music application with playlist features",
                image: "🎵",
                category: "mobile",
                featured: false
            }
        ];
    }

    createGallery() {
        if (!this.galleryGrid) return;

        this.galleryGrid.innerHTML = this.images.map((image, index) => 
            this.createGalleryItem(image, index)
        ).join('');
    }

    createGalleryItem(image, index) {
        return `
            <div class="gallery-item" data-index="${index}" data-category="${image.category}">
                <div class="gallery-image">
                    <div class="gallery-overlay">
                        <i class="fas fa-expand-alt"></i>
                    </div>
                    <span style="font-size: 3rem;">${image.image}</span>
                </div>
                <div class="gallery-caption">
                    <h4>${image.title}</h4>
                    <p>${image.description}</p>
                    ${image.featured ? '<span class="badge badge-accent">Featured</span>' : ''}
                </div>
            </div>
        `;
    }

    createLightbox() {
        this.lightbox = document.createElement('div');
        this.lightbox.className = 'lightbox';
        this.lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-content">
                <button class="lightbox-close">&times;</button>
                <button class="lightbox-prev">&lt;</button>
                <button class="lightbox-next">&gt;</button>
                <div class="lightbox-image-container">
                    <div class="lightbox-image"></div>
                    <div class="lightbox-info">
                        <h3 class="lightbox-title"></h3>
                        <p class="lightbox-description"></p>
                        <div class="lightbox-counter"></div>
                    </div>
                </div>
            </div>
        `;

        // Add lightbox styles
        this.addLightboxStyles();
        
        document.body.appendChild(this.lightbox);
    }

    addLightboxStyles() {
        const lightboxStyles = `
            .lightbox {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 3000;
                background: rgba(0, 0, 0, 0.9);
                -webkit-backdrop-filter: blur(10px);
                backdrop-filter: blur(10px);
            }

            .lightbox.show {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .lightbox-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }

            .lightbox-content {
                position: relative;
                max-width: 90vw;
                max-height: 90vh;
                background: var(--background-primary);
                border-radius: var(--radius-xl);
                overflow: hidden;
                box-shadow: var(--shadow-xl);
                animation: lightboxSlideIn 0.3s ease-out;
            }

            @keyframes lightboxSlideIn {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .lightbox-close {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: rgba(0, 0, 0, 0.5);
                color: white;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                font-size: 1.5rem;
                cursor: pointer;
                z-index: 10;
                transition: all 0.3s ease;
            }

            .lightbox-close:hover {
                background: rgba(0, 0, 0, 0.8);
                transform: scale(1.1);
            }

            .lightbox-prev,
            .lightbox-next {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(0, 0, 0, 0.5);
                color: white;
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
                cursor: pointer;
                z-index: 10;
                transition: all 0.3s ease;
            }

            .lightbox-prev {
                left: 1rem;
            }

            .lightbox-next {
                right: 1rem;
            }

            .lightbox-prev:hover,
            .lightbox-next:hover {
                background: rgba(0, 0, 0, 0.8);
                transform: translateY(-50%) scale(1.1);
            }

            .lightbox-image-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 2rem;
                text-align: center;
            }

            .lightbox-image {
                font-size: 8rem;
                margin-bottom: 1rem;
            }

            .lightbox-info {
                max-width: 500px;
            }

            .lightbox-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: 0.5rem;
            }

            .lightbox-description {
                color: var(--text-secondary);
                line-height: 1.6;
                margin-bottom: 1rem;
            }

            .lightbox-counter {
                font-size: 0.875rem;
                color: var(--text-light);
            }

            .gallery-caption {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
                color: white;
                padding: 1rem;
                transform: translateY(100%);
                transition: transform 0.3s ease;
            }

            .gallery-item:hover .gallery-caption {
                transform: translateY(0);
            }

            .gallery-caption h4 {
                margin-bottom: 0.25rem;
                font-size: 1rem;
            }

            .gallery-caption p {
                font-size: 0.875rem;
                opacity: 0.9;
                margin-bottom: 0.5rem;
            }

            @media (max-width: 768px) {
                .lightbox-content {
                    max-width: 95vw;
                    max-height: 95vh;
                }

                .lightbox-image {
                    font-size: 4rem;
                }

                .lightbox-prev,
                .lightbox-next {
                    width: 40px;
                    height: 40px;
                    font-size: 1rem;
                }
            }
        `;

        if (!document.querySelector('#lightbox-styles')) {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'lightbox-styles';
            styleSheet.textContent = lightboxStyles;
            document.head.appendChild(styleSheet);
        }
    }

    bindEvents() {
        // Gallery item clicks
        if (this.galleryGrid) {
            this.galleryGrid.addEventListener('click', (e) => {
                const galleryItem = e.target.closest('.gallery-item');
                if (galleryItem) {
                    const index = parseInt(galleryItem.getAttribute('data-index'));
                    this.openLightbox(index);
                }
            });
        }

        // Lightbox events
        if (this.lightbox) {
            // Close button
            const closeBtn = this.lightbox.querySelector('.lightbox-close');
            closeBtn.addEventListener('click', () => {
                this.closeLightbox();
            });

            // Navigation buttons
            const prevBtn = this.lightbox.querySelector('.lightbox-prev');
            const nextBtn = this.lightbox.querySelector('.lightbox-next');
            
            prevBtn.addEventListener('click', () => {
                this.previousImage();
            });
            
            nextBtn.addEventListener('click', () => {
                this.nextImage();
            });

            // Overlay click to close
            const overlay = this.lightbox.querySelector('.lightbox-overlay');
            overlay.addEventListener('click', () => {
                this.closeLightbox();
            });

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (this.lightbox.classList.contains('show')) {
                    switch (e.key) {
                        case 'Escape':
                            this.closeLightbox();
                            break;
                        case 'ArrowLeft':
                            this.previousImage();
                            break;
                        case 'ArrowRight':
                            this.nextImage();
                            break;
                    }
                }
            });
        }
    }

    openLightbox(index) {
        this.currentImageIndex = index;
        this.updateLightboxContent();
        this.lightbox.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    closeLightbox() {
        this.lightbox.classList.remove('show');
        document.body.style.overflow = '';
    }

    previousImage() {
        this.currentImageIndex = (this.currentImageIndex - 1 + this.images.length) % this.images.length;
        this.updateLightboxContent();
    }

    nextImage() {
        this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length;
        this.updateLightboxContent();
    }

    updateLightboxContent() {
        const image = this.images[this.currentImageIndex];
        const imageElement = this.lightbox.querySelector('.lightbox-image');
        const titleElement = this.lightbox.querySelector('.lightbox-title');
        const descriptionElement = this.lightbox.querySelector('.lightbox-description');
        const counterElement = this.lightbox.querySelector('.lightbox-counter');

        imageElement.innerHTML = `<span style="font-size: 8rem;">${image.image}</span>`;
        titleElement.textContent = image.title;
        descriptionElement.textContent = image.description;
        counterElement.textContent = `${this.currentImageIndex + 1} of ${this.images.length}`;
    }

    // Public methods for external use
    filterByCategory(category) {
        const items = this.galleryGrid.querySelectorAll('.gallery-item');
        items.forEach(item => {
            const itemCategory = item.getAttribute('data-category');
            if (category === 'all' || itemCategory === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    searchImages(query) {
        const items = this.galleryGrid.querySelectorAll('.gallery-item');
        const searchTerm = query.toLowerCase();
        
        items.forEach((item, index) => {
            const image = this.images[index];
            const matches = image.title.toLowerCase().includes(searchTerm) ||
                           image.description.toLowerCase().includes(searchTerm) ||
                           image.category.toLowerCase().includes(searchTerm);
            
            item.style.display = matches ? 'block' : 'none';
        });
    }

    getImageById(id) {
        return this.images.find(image => image.id === id);
    }

    getImagesByCategory(category) {
        return this.images.filter(image => image.category === category);
    }

    getFeaturedImages() {
        return this.images.filter(image => image.featured);
    }

    addImage(imageData) {
        this.images.push({
            id: this.images.length + 1,
            ...imageData
        });
        this.createGallery();
    }

    removeImage(id) {
        this.images = this.images.filter(image => image.id !== id);
        this.createGallery();
    }

    // Slideshow functionality
    startSlideshow(interval = 3000) {
        this.slideshowInterval = setInterval(() => {
            this.nextImage();
        }, interval);
    }

    stopSlideshow() {
        if (this.slideshowInterval) {
            clearInterval(this.slideshowInterval);
            this.slideshowInterval = null;
        }
    }
}

// Initialize gallery when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if gallery section exists
    const gallerySection = document.getElementById('gallery');
    if (gallerySection) {
        window.photoGallery = new PhotoGallery();
    }
});

// Export for use in other modules
window.PhotoGallery = PhotoGallery; 