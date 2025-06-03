/**
 * Habit Tracker - Main JavaScript File
 */

// Modern JavaScript Enhancements for Better UX
class HabitTrackerUI {
    constructor() {
        this.init();
    }

    init() {
        this.setupAnimations();
        this.setupLoadingStates();
        this.setupTooltips();
        this.setupNotifications();
        this.setupScrollEffects();
        this.setupHabitCompletions();
    }

    // Setup modern animations
    setupAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('card-animate');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all cards for animation
        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });

        // Add stagger effect to habit cards
        document.querySelectorAll('.habit-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Setup loading states for better UX
    setupLoadingStates() {
        // Add loading skeleton while content loads
        const contentElements = document.querySelectorAll('.stat-card, .progress-card');
        contentElements.forEach(element => {
            if (!element.dataset.loaded) {
                element.classList.add('loading-skeleton');
                setTimeout(() => {
                    element.classList.remove('loading-skeleton');
                    element.classList.add('card-bounce');
                    element.dataset.loaded = 'true';
                }, Math.random() * 500 + 200);
            }
        });
    }

    // Enhanced tooltips
    setupTooltips() {
        // Custom modern tooltips
        document.querySelectorAll('.tooltip-modern').forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });
            
            element.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    }

    // Setup notification enhancements
    setupNotifications() {
        // Auto-hide success messages
        const alerts = document.querySelectorAll('.alert-modern');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Notification badge animation
        const notificationBadges = document.querySelectorAll('.notification-badge');
        notificationBadges.forEach(badge => {
            badge.addEventListener('click', function() {
                this.classList.add('celebrate');
                setTimeout(() => this.classList.remove('celebrate'), 600);
            });
        });
    }

    // Setup scroll effects
    setupScrollEffects() {
        let lastScrollTop = 0;
        const navbar = document.querySelector('.navbar');
        
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (navbar) {
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scrolling down - hide navbar
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    // Scrolling up - show navbar
                    navbar.style.transform = 'translateY(0)';
                }
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });

        // Parallax effect for background elements
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            
            parallaxElements.forEach(element => {
                const speed = element.dataset.parallax || 0.5;
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    }

    // Enhanced habit completion with animations
    setupHabitCompletions() {
        const habitButtons = document.querySelectorAll('.habit-complete-btn');
        
        habitButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Add loading state
                this.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Completing...';
                this.disabled = true;
                
                // Simulate API call (replace with actual form submission)
                setTimeout(() => {
                    this.classList.add('completed');
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Completed!';
                    
                    // Add celebration effect
                    this.classList.add('celebrate');
                    
                    // Create floating success message
                    this.createFloatingMessage('Great job! 🎉');
                    
                    // Update progress bars with animation
                    this.updateProgressBars();
                    
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-check"></i> Mark Complete';
                        this.disabled = false;
                        this.classList.remove('celebrate');
                    }, 2000);
                }, 1000);
            });
        });
    }

    // Create floating success message
    createFloatingMessage(message) {
        const floatingMsg = document.createElement('div');
        floatingMsg.textContent = message;
        floatingMsg.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--success-gradient);
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--radius-lg);
            font-weight: 600;
            z-index: 9999;
            animation: bounce-in 0.5s ease-out;
            box-shadow: var(--shadow-xl);
        `;
        
        document.body.appendChild(floatingMsg);
        
        setTimeout(() => {
            floatingMsg.style.opacity = '0';
            floatingMsg.style.transform = 'translate(-50%, -50%) scale(0.8)';
            setTimeout(() => floatingMsg.remove(), 300);
        }, 2000);
    }

    // Animate progress bars
    updateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const currentWidth = parseInt(bar.style.width) || 0;
            const newWidth = Math.min(currentWidth + 10, 100);
            
            setTimeout(() => {
                bar.style.width = newWidth + '%';
                bar.setAttribute('aria-valuenow', newWidth);
            }, 500);
        });
    }

    // Streak counter animation
    animateStreak(element, newValue) {
        element.style.transform = 'scale(1.2)';
        element.style.background = 'var(--warning-gradient)';
        
        setTimeout(() => {
            element.textContent = newValue;
            element.style.transform = 'scale(1)';
        }, 200);
    }

    // Theme transition animation
    animateThemeChange() {
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    // Mobile-specific enhancements
    initMobileEnhancements() {
        // Touch event handling for better mobile experience
        if ('ontouchstart' in window) {
            document.body.classList.add('touch-device');
            
            // Add touch feedback to cards
            document.querySelectorAll('.habit-card, .stat-card').forEach(card => {
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                card.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        }
        
        // Handle viewport height changes (mobile browsers)
        const setVH = () => {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        };
        
        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', setVH);
        
        // Optimize animations for mobile
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            document.documentElement.style.setProperty('--animation-duration', '0.2s');
        }
    }
    
    // Enhanced theme compatibility
    initThemeCompatibility() {
        const themeObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-bs-theme') {
                    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    this.updateThemeSpecificElements(isDark);
                }
            });
        });
        
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });
        
        // Initial theme setup
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        this.updateThemeSpecificElements(isDark);
    }
    
    updateThemeSpecificElements(isDark) {
        // Update chart colors if charts exist
        const charts = document.querySelectorAll('.chart-container');
        charts.forEach(chart => {
            if (isDark) {
                chart.style.setProperty('--chart-text-color', '#ffffff');
                chart.style.setProperty('--chart-grid-color', '#374151');
            } else {
                chart.style.setProperty('--chart-text-color', '#374151');
                chart.style.setProperty('--chart-grid-color', '#e5e7eb');
            }
        });
        
        // Update loading skeletons
        const skeletons = document.querySelectorAll('.loading-skeleton');
        skeletons.forEach(skeleton => {
            if (isDark) {
                skeleton.style.background = 'linear-gradient(90deg, #2a2a2a 25%, #3a3a3a 50%, #2a2a2a 75%)';
            } else {
                skeleton.style.background = 'linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%)';
            }
        });
    }
    
    // Performance monitoring
    initPerformanceMonitoring() {
        // Monitor long tasks
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    if (entry.duration > 50) {
                        console.warn(`Long task detected: ${entry.duration}ms`);
                    }
                });
            });
            
            observer.observe({ entryTypes: ['longtask'] });
        }
        
        // Lazy load images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                        }
                    }
                });
            });
            
            document.querySelectorAll('img.lazy').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
}

// Enhanced form validation with better UX
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Real-time validation
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    let isValid = true;
    let message = '';
    
    if (required && !value) {
        isValid = false;
        message = 'This field is required.';
    } else if (type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        message = 'Please enter a valid email address.';
    } else if (field.hasAttribute('minlength') && value.length < field.getAttribute('minlength')) {
        isValid = false;
        message = `Minimum length is ${field.getAttribute('minlength')} characters.`;
    }
    
    updateFieldValidation(field, isValid, message);
    return isValid;
}

function updateFieldValidation(field, isValid, message) {
    const feedback = field.parentNode.querySelector('.invalid-feedback') || 
                    document.createElement('div');
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        if (feedback.parentNode) {
            feedback.remove();
        }
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        
        if (!feedback.parentNode) {
            field.parentNode.appendChild(feedback);
        }
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Improved loading states
function showLoadingState(element, text = 'Loading...') {
    const originalContent = element.innerHTML;
    element.dataset.originalContent = originalContent;
    
    element.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            ${text}
        </div>
    `;
    element.disabled = true;
}

function hideLoadingState(element) {
    if (element.dataset.originalContent) {
        element.innerHTML = element.dataset.originalContent;
        delete element.dataset.originalContent;
    }
    element.disabled = false;
}

// Better error handling
function showErrorMessage(message, container = null) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    if (container) {
        container.insertBefore(alert, container.firstChild);
    } else {
        document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Enhanced theme and color scheme handling
function initThemeSystem() {
    // Handle theme selection changes
    document.querySelectorAll('input[name="theme"]').forEach(input => {
        input.addEventListener('change', function() {
            const theme = this.value;
            applyTheme(theme);
        });
    });
    
    // Handle color scheme changes
    document.querySelectorAll('input[name="color_scheme"]').forEach(input => {
        input.addEventListener('change', function() {
            const colorScheme = this.value;
            applyColorScheme(colorScheme);
        });
    });
    
    // Initial theme application
    const checkedTheme = document.querySelector('input[name="theme"]:checked');
    const checkedColorScheme = document.querySelector('input[name="color_scheme"]:checked');
    
    if (checkedTheme) {
        applyTheme(checkedTheme.value);
    }
    
    if (checkedColorScheme) {
        applyColorScheme(checkedColorScheme.value);
    }
}

function applyTheme(theme) {
    const html = document.documentElement;
    
    if (theme === 'dark') {
        html.setAttribute('data-bs-theme', 'dark');
    } else if (theme === 'light') {
        html.setAttribute('data-bs-theme', 'light');
    } else if (theme === 'system') {
        // Check system preference
        const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        html.setAttribute('data-bs-theme', systemDark ? 'dark' : 'light');
        
        // Listen for system theme changes
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener(function(e) {
                if (document.querySelector('input[name="theme"]:checked')?.value === 'system') {
                    html.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                }
            });
        }
    }
    
    // Store theme preference
    localStorage.setItem('habit-tracker-theme', theme);
}

function applyColorScheme(colorScheme) {
    const body = document.body;
    
    // Remove existing color classes
    body.classList.remove('color-default', 'color-teal', 'color-indigo', 'color-rose', 'color-amber', 'color-emerald');
    
    // Add new color class
    body.classList.add(`color-${colorScheme}`);
    
    // Store color scheme preference
    localStorage.setItem('habit-tracker-color-scheme', colorScheme);
    
    // Update CSS custom properties
    const root = document.documentElement;
    const colorSchemes = {
        'default': {
            primary: '#667eea',
            gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            accent: '#4facfe'
        },
        'teal': {
            primary: '#20c997',
            gradient: 'linear-gradient(135deg, #20c997 0%, #0ea5e9 100%)',
            accent: '#06b6d4'
        },
        'indigo': {
            primary: '#6610f2',
            gradient: 'linear-gradient(135deg, #6610f2 0%, #6366f1 100%)',
            accent: '#8b5cf6'
        },
        'rose': {
            primary: '#e83e8c',
            gradient: 'linear-gradient(135deg, #e83e8c 0%, #f472b6 100%)',
            accent: '#ec4899'
        },
        'amber': {
            primary: '#fd7e14',
            gradient: 'linear-gradient(135deg, #fd7e14 0%, #f59e0b 100%)',
            accent: '#f97316'
        },
        'emerald': {
            primary: '#28a745',
            gradient: 'linear-gradient(135deg, #28a745 0%, #10b981 100%)',
            accent: '#059669'
        }
    };
    
    const colors = colorSchemes[colorScheme] || colorSchemes['default'];
    root.style.setProperty('--primary-color', colors.primary);
    root.style.setProperty('--primary-gradient', colors.gradient);
    root.style.setProperty('--accent-color', colors.accent);
}

// Settings page specific enhancements
function initSettingsPage() {
    // Add visual feedback for theme and color selection
    document.querySelectorAll('.theme-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all theme cards
            document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('active'));
            // Add active class to clicked card
            this.classList.add('active');
        });
    });
    
    document.querySelectorAll('.settings-color-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all color options
            document.querySelectorAll('.settings-color-option').forEach(o => o.classList.remove('active'));
            // Add active class to clicked option
            this.classList.add('active');
        });
    });
    
    // Apply initial active states
    const activeTheme = document.querySelector('input[name="theme"]:checked');
    if (activeTheme) {
        const themeCard = activeTheme.closest('label').querySelector('.theme-card');
        if (themeCard) themeCard.classList.add('active');
    }
    
    const activeColor = document.querySelector('input[name="color_scheme"]:checked');
    if (activeColor) {
        const colorOption = activeColor.closest('.settings-color-option');
        if (colorOption) colorOption.classList.add('active');
    }
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initThemeSystem();
    
    // Check if we're on the settings page
    if (document.querySelector('.settings-color-option')) {
        initSettingsPage();
    }
});
