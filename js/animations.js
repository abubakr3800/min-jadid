// Animations and Interactive Effects

document.addEventListener('DOMContentLoaded', function() {
    
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe all elements with scroll animation classes
    const scrollElements = document.querySelectorAll(
        '.scroll-fade-in, .scroll-slide-left, .scroll-slide-right, .scroll-scale-in, ' +
        '.fade-in-on-scroll, .slide-in-left-on-scroll, .slide-in-right-on-scroll, .scale-in-on-scroll'
    );
    
    scrollElements.forEach(el => observer.observe(el));

    // Staggered animations for cards and lists
    const staggerElements = document.querySelectorAll('.animate-stagger');
    staggerElements.forEach(container => {
        const children = container.children;
        Array.from(children).forEach((child, index) => {
            child.style.animationDelay = `${index * 0.1}s`;
        });
    });

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Button ripple effect
    const buttons = document.querySelectorAll('.btn-micro');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Card hover effects
    const cards = document.querySelectorAll('.card-hover, .post-card, .card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = 'var(--shadow-xl)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
    });

    // Input focus effects
    const inputs = document.querySelectorAll('.input-focus, .form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary-color)';
            this.style.boxShadow = '0 0 0 3px var(--primary-lighter)';
            this.style.transform = 'translateY(-1px)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = 'var(--border-color)';
            this.style.boxShadow = 'none';
            this.style.transform = 'translateY(0)';
        });
    });

    // Loading animations
    function showLoading(element) {
        element.innerHTML = '<div class="loading-spinner"></div>';
    }

    function hideLoading(element, originalContent) {
        element.innerHTML = originalContent;
    }

    // Form submission animations
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="loading-spinner"></div>';
                submitBtn.disabled = true;
                
                // Re-enable after a delay (for demo purposes)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        });
    });

    // Typing animation for hero titles
    const typingElements = document.querySelectorAll('.animate-typewriter');
    typingElements.forEach(element => {
        const text = element.textContent;
        element.textContent = '';
        element.style.borderRight = '2px solid var(--primary-color)';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            } else {
                element.style.borderRight = 'none';
            }
        };
        
        typeWriter();
    });

    // Parallax effect for hero sections
    const parallaxElements = document.querySelectorAll('.parallax');
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        parallaxElements.forEach(element => {
            const rate = scrolled * -0.5;
            element.style.transform = `translateY(${rate}px)`;
        });
    });

    // Floating animation for icons
    const floatingElements = document.querySelectorAll('.icon-float');
    floatingElements.forEach(element => {
        element.style.animation = 'float 3s ease-in-out infinite';
    });

    // Pulse animation for important elements
    const pulseElements = document.querySelectorAll('.icon-pulse');
    pulseElements.forEach(element => {
        element.style.animation = 'pulse 2s infinite';
    });

    // Shake animation for error states
    function shakeElement(element) {
        element.classList.add('animate-shake');
        setTimeout(() => {
            element.classList.remove('animate-shake');
        }, 800);
    }

    // Notification animations
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification-enter`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.remove('notification-enter');
            notification.classList.add('notification-enter-active');
        }, 10);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('notification-enter-active');
            notification.classList.add('notification-exit-active');
            
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    // Modal animations
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('modal-enter');
            modal.style.display = 'block';
            
            setTimeout(() => {
                modal.classList.remove('modal-enter');
                modal.classList.add('modal-enter-active');
            }, 10);
        }
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('modal-enter-active');
            modal.classList.add('modal-exit-active');
            
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('modal-exit-active');
            }, 300);
        }
    }

    // Progress bar animation
    function animateProgressBar(progressBar, targetValue) {
        const currentValue = 0;
        const increment = targetValue / 100;
        
        const timer = setInterval(() => {
            currentValue += increment;
            progressBar.style.width = currentValue + '%';
            
            if (currentValue >= targetValue) {
                clearInterval(timer);
                progressBar.style.width = targetValue + '%';
            }
        }, 20);
    }

    // Counter animation
    function animateCounter(element, targetValue, duration = 2000) {
        const startValue = 0;
        const increment = targetValue / (duration / 16);
        let currentValue = startValue;
        
        const timer = setInterval(() => {
            currentValue += increment;
            element.textContent = Math.floor(currentValue);
            
            if (currentValue >= targetValue) {
                clearInterval(timer);
                element.textContent = targetValue;
            }
        }, 16);
    }

    // Lazy loading for images
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));

    // Add CSS for ripple effect
    const style = document.createElement('style');
    style.textContent = `
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .btn-micro {
            position: relative;
            overflow: hidden;
        }
    `;
    document.head.appendChild(style);

    // Initialize animations on page load
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 100);

    // Export functions for global use
    window.Animations = {
        showNotification,
        showModal,
        hideModal,
        animateProgressBar,
        animateCounter,
        shakeElement
    };
}); 