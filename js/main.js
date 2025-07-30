// ===== MAIN JAVASCRIPT FILE =====

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all main functionality
    initNavigation();
    initSmoothScrolling();
    initScrollEffects();
    initAnimations();
    initDashboardCharts();
    initContactForm();
    initNotifications();
});

// ===== NAVIGATION FUNCTIONALITY =====
function initNavigation() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navbar = document.getElementById('navbar');
    const navLinks = document.querySelectorAll('.nav-link');

    // Mobile menu toggle
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Active navigation link highlighting
    window.addEventListener('scroll', function() {
        let current = '';
        const sections = document.querySelectorAll('section[id]');
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (window.scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}

// ===== SMOOTH SCROLLING =====
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 70; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ===== SCROLL EFFECTS =====
function initScrollEffects() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const animateElements = document.querySelectorAll('.feature-card, .portfolio-item, .blog-post, .gallery-item');
    animateElements.forEach(el => observer.observe(el));
}

// ===== ANIMATIONS =====
function initAnimations() {
    // Typing animation for hero code
    const codeLines = document.querySelectorAll('.code-line');
    if (codeLines.length > 0) {
        codeLines.forEach((line, index) => {
            line.style.opacity = '0';
            line.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                line.style.transition = 'all 0.5s ease';
                line.style.opacity = '1';
                line.style.transform = 'translateX(0)';
            }, index * 200);
        });
    }

    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        
        if (hero) {
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    });
}

// ===== DASHBOARD CHARTS =====
function initDashboardCharts() {
    // Project Statistics Chart
    const projectsCtx = document.getElementById('projects-chart');
    if (projectsCtx) {
        new Chart(projectsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Planned'],
                datasets: [{
                    data: [12, 8, 5],
                    backgroundColor: [
                        '#10b981',
                        '#6366f1',
                        '#f59e0b'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                }
            }
        });
    }

    // Technology Usage Chart
    const techCtx = document.getElementById('tech-chart');
    if (techCtx) {
        new Chart(techCtx, {
            type: 'bar',
            data: {
                labels: ['HTML/CSS', 'JavaScript', 'PHP', 'Python', 'Ruby', 'C#', 'Perl'],
                datasets: [{
                    label: 'Projects',
                    data: [15, 12, 8, 6, 4, 3, 2],
                    backgroundColor: [
                        '#6366f1',
                        '#f59e0b',
                        '#8b5cf6',
                        '#10b981',
                        '#ef4444',
                        '#06b6d4',
                        '#84cc16'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10
                    }
                }
            }
        });
    }
}

// ===== CONTACT FORM =====
function initContactForm() {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Basic validation
            if (!data.name || !data.email || !data.subject || !data.message) {
                showNotification('Please fill in all fields', 'error');
                return;
            }
            
            if (!isValidEmail(data.email)) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Sending...';
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual backend call)
            setTimeout(() => {
                showNotification('Message sent successfully!', 'success');
                this.reset();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
    }
}

// ===== NOTIFICATION SYSTEM =====
function initNotifications() {
    // Create notification container if it doesn't exist
    if (!document.querySelector('.notification-container')) {
        const container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const container = document.querySelector('.notification-container');
    const notification = document.createElement('div');
    
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    container.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide and remove notification
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// ===== UTILITY FUNCTIONS =====

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Format date
function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Get random number between min and max
function getRandomNumber(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// ===== GLOBAL EVENT LISTENERS =====

// Handle window resize
window.addEventListener('resize', debounce(function() {
    // Reinitialize charts on resize
    if (typeof Chart !== 'undefined' && Chart.instances) {
        Object.values(Chart.instances).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }
}, 250));

// Handle keyboard navigation
document.addEventListener('keydown', function(e) {
    // Escape key to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            modal.classList.remove('show');
        });
    }
    
    // Enter key to submit forms
    if (e.key === 'Enter' && e.target.matches('input, textarea')) {
        const form = e.target.closest('form');
        if (form && !e.target.matches('textarea')) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.click();
            }
        }
    }
});

// Handle page visibility change
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        document.body.classList.add('page-hidden');
    } else {
        document.body.classList.remove('page-hidden');
    }
});

// ===== PERFORMANCE OPTIMIZATION =====

// Lazy load images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading when DOM is ready
if ('IntersectionObserver' in window) {
    initLazyLoading();
}

// ===== ERROR HANDLING =====

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    showNotification('An error occurred. Please try again.', 'error');
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showNotification('An error occurred. Please try again.', 'error');
});

// ===== EXPORT FUNCTIONS FOR OTHER MODULES =====
window.MainJS = {
    showNotification,
    isValidEmail,
    debounce,
    throttle,
    formatDate,
    formatNumber,
    getRandomNumber,
    isInViewport
}; 