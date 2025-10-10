/**
 * BilliardPro Dashboard Enhancements
 * Provides improved responsiveness, accessibility, and user experience
 */

class DashboardEnhancements {
    constructor() {
        this.init();
        this.setupResponsiveFeatures();
        this.setupAccessibilityFeatures();
        this.setupRealTimeUpdates();
        this.setupTouchEnhancements();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
        } else {
            this.initializeComponents();
        }
    }

    initializeComponents() {
        this.enhanceTableCards();
        this.setupKeyboardNavigation();
        this.setupLoadingStates();
        this.setupAnimations();
        this.setupViewportDetection();
    }

    /**
     * Enhanced table card interactions
     */
    enhanceTableCards() {
        const tableCards = document.querySelectorAll('.table-card, [class*="bg-gradient-to-br"]');

        tableCards.forEach(card => {
            // Add enhanced hover effects for touch devices
            if ('ontouchstart' in window) {
                card.addEventListener('touchstart', (e) => {
                    card.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        card.style.transform = '';
                    }, 150);
                });
            }

            // Add ripple effect on click
            card.addEventListener('click', (e) => {
                this.createRippleEffect(e, card);
            });

            // Add status-specific enhancements
            if (card.classList.contains('available') || card.className.includes('from-green')) {
                this.addAvailableTableEnhancements(card);
            } else if (card.classList.contains('occupied') || card.className.includes('from-red')) {
                this.addOccupiedTableEnhancements(card);
            } else if (card.classList.contains('maintenance') || card.className.includes('from-gray')) {
                this.addMaintenanceTableEnhancements(card);
            }
        });
    }

    /**
     * Add ripple effect animation
     */
    createRippleEffect(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
            z-index: 1;
        `;

        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);

        // Add ripple animation keyframes if not exists
        if (!document.querySelector('#ripple-keyframes')) {
            const style = document.createElement('style');
            style.id = 'ripple-keyframes';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    /**
     * Enhanced features for available tables
     */
    addAvailableTableEnhancements(card) {
        // Add pulse animation for available tables
        card.classList.add('pulse-glow');

        // Add ready indicator
        const readyIndicator = document.createElement('div');
        readyIndicator.className = 'absolute top-4 right-4 w-3 h-3 bg-green-300 rounded-full animate-pulse';
        readyIndicator.setAttribute('aria-label', 'Table ready for use');
        card.appendChild(readyIndicator);
    }

    /**
     * Enhanced features for occupied tables
     */
    addOccupiedTableEnhancements(card) {
        // Add occupied pulse animation
        card.classList.add('pulse-glow-occupied');

        // Add active session indicator
        const activeIndicator = document.createElement('div');
        activeIndicator.className = 'absolute top-4 right-4 w-3 h-3 bg-red-300 rounded-full animate-pulse';
        activeIndicator.setAttribute('aria-label', 'Table currently in use');
        card.appendChild(activeIndicator);
    }

    /**
     * Enhanced features for maintenance tables
     */
    addMaintenanceTableEnhancements(card) {
        // Add maintenance warning indicator
        const maintenanceIndicator = document.createElement('div');
        maintenanceIndicator.className = 'absolute top-4 right-4 flex items-center justify-center';
        maintenanceIndicator.innerHTML = `
            <div class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center" aria-label="Table under maintenance">
                <svg class="w-3 h-3 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
        `;
        card.appendChild(maintenanceIndicator);
    }

    /**
     * Setup responsive features
     */
    setupResponsiveFeatures() {
        // Responsive table grid adjustments
        this.setupResponsiveGrid();

        // Touch-friendly interactions for mobile
        this.setupTouchOptimizations();

        // Responsive typography scaling
        this.setupResponsiveTypography();
    }

    /**
     * Enhanced responsive grid system
     */
    setupResponsiveGrid() {
        const grid = document.querySelector('.table-grid, [class*="grid grid-cols"]');
        if (!grid) return;

        const observer = new ResizeObserver(entries => {
            for (let entry of entries) {
                const width = entry.contentRect.width;

                // Adjust grid columns based on width
                if (width < 640) {
                    grid.style.gridTemplateColumns = '1fr';
                    grid.style.gap = '1rem';
                } else if (width < 768) {
                    grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    grid.style.gap = '1.25rem';
                } else if (width < 1024) {
                    grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
                    grid.style.gap = '1.5rem';
                } else if (width < 1280) {
                    grid.style.gridTemplateColumns = 'repeat(4, 1fr)';
                    grid.style.gap = '1.75rem';
                } else {
                    grid.style.gridTemplateColumns = 'repeat(5, 1fr)';
                    grid.style.gap = '2rem';
                }
            }
        });

        observer.observe(grid);
    }

    /**
     * Touch optimizations for mobile devices
     */
    setupTouchOptimizations() {
        // Increase touch targets for mobile
        if ('ontouchstart' in window) {
            const buttons = document.querySelectorAll('button, .btn');
            buttons.forEach(button => {
                button.style.minHeight = '44px';
                button.style.minWidth = '44px';
            });

            // Add touch feedback
            const interactiveElements = document.querySelectorAll('.table-card, button, .btn');
            interactiveElements.forEach(element => {
                element.addEventListener('touchstart', () => {
                    element.style.transform = 'scale(0.95)';
                });

                element.addEventListener('touchend', () => {
                    setTimeout(() => {
                        element.style.transform = '';
                    }, 150);
                });
            });
        }
    }

    /**
     * Responsive typography scaling
     */
    setupResponsiveTypography() {
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');

        headings.forEach(heading => {
            // Adjust font sizes based on screen size
            const observer = new ResizeObserver(entries => {
                for (let entry of entries) {
                    const width = entry.contentRect.width;

                    if (width < 640) {
                        heading.style.fontSize = 'clamp(1.25rem, 4vw, 1.5rem)';
                    } else if (width < 768) {
                        heading.style.fontSize = 'clamp(1.5rem, 4vw, 1.75rem)';
                    } else {
                        heading.style.fontSize = '';
                    }
                }
            });

            observer.observe(document.body);
        });
    }

    /**
     * Setup accessibility features
     */
    setupAccessibilityFeatures() {
        // Add ARIA labels and roles
        this.enhanceAriaLabels();

        // Setup keyboard navigation
        this.setupKeyboardNavigation();

        // Setup focus management
        this.setupFocusManagement();

        // Setup screen reader announcements
        this.setupScreenReaderAnnouncements();
    }

    /**
     * Enhanced ARIA labels for better accessibility
     */
    enhanceAriaLabels() {
        // Add labels to table cards
        const tableCards = document.querySelectorAll('.table-card, [class*="bg-gradient-to-br"]');
        tableCards.forEach(card => {
            const tableName = card.querySelector('h2')?.textContent || 'Table';
            const tableStatus = card.querySelector('.status-badge')?.textContent || 'Unknown';

            card.setAttribute('role', 'button');
            card.setAttribute('tabindex', '0');
            card.setAttribute('aria-label', `${tableName}, Status: ${tableStatus}`);

            // Add status-specific ARIA labels
            if (card.className.includes('available') || card.className.includes('from-green')) {
                card.setAttribute('aria-label', `${tableName}, Available table. Click to start session`);
            } else if (card.className.includes('occupied') || card.className.includes('from-red')) {
                card.setAttribute('aria-label', `${tableName}, Occupied table. Click to end session`);
            } else if (card.className.includes('maintenance') || card.className.includes('from-gray')) {
                card.setAttribute('aria-label', `${tableName}, Under maintenance. Not available`);
            }
        });

        // Add labels to summary cards
        const summaryCards = document.querySelectorAll('.summary-card');
        summaryCards.forEach(card => {
            const title = card.querySelector('.summary-title')?.textContent || 'Summary';
            const value = card.querySelector('.summary-value')?.textContent || '0';

            card.setAttribute('aria-label', `${title}: ${value}`);
        });
    }

    /**
     * Enhanced keyboard navigation
     */
    setupKeyboardNavigation() {
        const tableCards = document.querySelectorAll('.table-card, [class*="bg-gradient-to-br"]');

        tableCards.forEach((card, index) => {
            card.addEventListener('keydown', (e) => {
                switch (e.key) {
                    case 'Enter':
                    case ' ':
                        e.preventDefault();
                        card.click();
                        break;

                    case 'ArrowRight':
                        e.preventDefault();
                        this.focusNextCard(index, tableCards);
                        break;

                    case 'ArrowLeft':
                        e.preventDefault();
                        this.focusPreviousCard(index, tableCards);
                        break;

                    case 'ArrowDown':
                        e.preventDefault();
                        this.focusCardBelow(index, tableCards);
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        this.focusCardAbove(index, tableCards);
                        break;
                }
            });
        });
    }

    /**
     * Focus management for keyboard navigation
     */
    focusNextCard(currentIndex, cards) {
        const nextIndex = (currentIndex + 1) % cards.length;
        cards[nextIndex].focus();
    }

    focusPreviousCard(currentIndex, cards) {
        const prevIndex = currentIndex === 0 ? cards.length - 1 : currentIndex - 1;
        cards[prevIndex].focus();
    }

    focusCardBelow(currentIndex, cards) {
        // Assume 5 columns on desktop, 3 on tablet, 2 on mobile
        const columns = window.innerWidth > 1024 ? 5 : window.innerWidth > 768 ? 3 : 2;
        const nextRowIndex = currentIndex + columns;
        if (nextRowIndex < cards.length) {
            cards[nextRowIndex].focus();
        }
    }

    focusCardAbove(currentIndex, cards) {
        // Assume 5 columns on desktop, 3 on tablet, 2 on mobile
        const columns = window.innerWidth > 1024 ? 5 : window.innerWidth > 768 ? 3 : 2;
        const prevRowIndex = currentIndex - columns;
        if (prevRowIndex >= 0) {
            cards[prevRowIndex].focus();
        }
    }

    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Add focus indicators
        const style = document.createElement('style');
        style.textContent = `
            .table-card:focus,
            [class*="bg-gradient-to-br"]:focus {
                outline: none;
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5), var(--card-shadow);
                animation: focus-pulse 2s ease-in-out infinite;
            }

            @keyframes focus-pulse {
                0%, 100% {
                    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5), var(--card-shadow);
                }
                50% {
                    box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.3), var(--card-shadow);
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Setup screen reader announcements
     */
    setupScreenReaderAnnouncements() {
        // Create announcement region for screen readers
        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.className = 'sr-only';
        announcer.id = 'dashboard-announcer';
        document.body.appendChild(announcer);

        // Announce table status changes
        this.announceTableChanges();
    }

    /**
     * Announce table status changes to screen readers
     */
    announceTableChanges() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    const tableCards = document.querySelectorAll('.table-card, [class*="bg-gradient-to-br"]');
                    const availableCount = document.querySelectorAll('[class*="from-green"], .available').length;
                    const occupiedCount = document.querySelectorAll('[class*="from-red"], .occupied').length;
                    const maintenanceCount = document.querySelectorAll('[class*="from-gray"], .maintenance').length;

                    const announcement = `Table status updated. Available: ${availableCount}, Occupied: ${occupiedCount}, Maintenance: ${maintenanceCount}`;
                    this.announceToScreenReader(announcement);
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });
    }

    /**
     * Announce message to screen readers
     */
    announceToScreenReader(message) {
        const announcer = document.getElementById('dashboard-announcer');
        if (announcer) {
            announcer.textContent = message;
            setTimeout(() => {
                announcer.textContent = '';
            }, 1000);
        }
    }

    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        // Update time display every second
        this.updateTimeDisplay();

        // Setup periodic table status updates
        this.setupPeriodicUpdates();
    }

    /**
     * Update time display in real-time
     */
    updateTimeDisplay() {
        const timeElements = document.querySelectorAll('.time-value, [class*="text-3xl"]');

        setInterval(() => {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });

            timeElements.forEach(element => {
                if (element.textContent.match(/^\d{2}:\d{2}:\d{2}$/)) {
                    element.textContent = timeString;
                }
            });
        }, 1000);
    }

    /**
     * Setup periodic updates for table timers
     */
    setupPeriodicUpdates() {
        // Update occupied table timers every minute
        setInterval(() => {
            const occupiedTables = document.querySelectorAll('.table-occupied, [class*="from-red"]');

            occupiedTables.forEach(table => {
                const timerElement = table.querySelector('.timer-display');
                if (timerElement) {
                    // This would typically fetch updated time from server
                    // For now, we'll just add a subtle animation
                    timerElement.style.animation = 'pulse 1s ease-in-out';
                    setTimeout(() => {
                        timerElement.style.animation = '';
                    }, 1000);
                }
            });
        }, 60000); // Update every minute
    }

    /**
     * Setup loading states
     */
    setupLoadingStates() {
        // Add loading indicators for dynamic content
        this.addLoadingIndicators();

        // Setup skeleton loading for table cards
        this.setupSkeletonLoading();
    }

    /**
     * Add loading indicators
     */
    addLoadingIndicators() {
        const loadingElements = document.querySelectorAll('[wire:loading]');

        loadingElements.forEach(element => {
            element.addEventListener('loading', () => {
                element.classList.add('loading-shimmer');
                element.setAttribute('aria-busy', 'true');
            });

            element.addEventListener('loaded', () => {
                element.classList.remove('loading-shimmer');
                element.setAttribute('aria-busy', 'false');
            });
        });
    }

    /**
     * Setup skeleton loading for table cards
     */
    setupSkeletonLoading() {
        // Add skeleton loading CSS
        const style = document.createElement('style');
        style.textContent = `
            .skeleton {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
            }

            @keyframes loading {
                0% {
                    background-position: 200% 0;
                }
                100% {
                    background-position: -200% 0;
                }
            }

            .skeleton-text {
                height: 1em;
                border-radius: 4px;
                margin-bottom: 8px;
            }

            .skeleton-title {
                height: 1.5em;
                width: 60%;
                margin-bottom: 12px;
            }

            .skeleton-rate {
                height: 3em;
                width: 80%;
                border-radius: 8px;
                margin-bottom: 16px;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Setup animations and micro-interactions
     */
    setupAnimations() {
        // Add entrance animations for cards
        this.setupEntranceAnimations();

        // Add hover animations
        this.setupHoverAnimations();

        // Add scroll-triggered animations
        this.setupScrollAnimations();
    }

    /**
     * Setup entrance animations
     */
    setupEntranceAnimations() {
        const cards = document.querySelectorAll('.table-card, .summary-card');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0) scale(1)';
                    }, index * 100);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px) scale(0.95)';
            card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(card);
        });
    }

    /**
     * Setup hover animations
     */
    setupHoverAnimations() {
        const cards = document.querySelectorAll('.table-card, .summary-card');

        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-4px) scale(1.02)';
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
    }

    /**
     * Setup scroll-triggered animations
     */
    setupScrollAnimations() {
        const animateOnScroll = (elements, animationClass) => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add(animationClass);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.2
            });

            elements.forEach(element => {
                observer.observe(element);
            });
        };

        // Add fade-in animation for sections
        const sections = document.querySelectorAll('section, .grid, .flex');
        animateOnScroll(sections, 'animate-fade-in');
    }

    /**
     * Setup touch enhancements for mobile
     */
    setupTouchEnhancements() {
        // Add touch feedback for all interactive elements
        const interactiveElements = document.querySelectorAll(
            'button, .btn, .table-card, [role="button"]'
        );

        interactiveElements.forEach(element => {
            // Add touch action styles
            element.style.touchAction = 'manipulation';

            // Add active state styles for touch
            element.addEventListener('touchstart', () => {
                element.style.transform = 'scale(0.95)';
            });

            element.addEventListener('touchend', () => {
                setTimeout(() => {
                    element.style.transform = '';
                }, 150);
            });
        });

        // Prevent double-tap zoom on buttons
        let lastTouchTime = 0;
        document.addEventListener('touchend', (e) => {
            const now = Date.now();
            if (now - lastTouchTime <= 300) {
                e.preventDefault();
            }
            lastTouchTime = now;
        }, { passive: false });
    }

    /**
     * Setup viewport detection for responsive features
     */
    setupViewportDetection() {
        let currentViewport = this.getViewport();

        window.addEventListener('resize', () => {
            const newViewport = this.getViewport();

            if (newViewport !== currentViewport) {
                this.handleViewportChange(currentViewport, newViewport);
                currentViewport = newViewport;
            }
        });
    }

    /**
     * Get current viewport size category
     */
    getViewport() {
        const width = window.innerWidth;

        if (width < 640) return 'mobile';
        if (width < 768) return 'mobile-landscape';
        if (width < 1024) return 'tablet';
        if (width < 1280) return 'desktop';
        return 'desktop-large';
    }

    /**
     * Handle viewport changes
     */
    handleViewportChange(oldViewport, newViewport) {
        // Announce viewport change to screen readers
        this.announceToScreenReader(`Layout changed to ${newViewport} view`);

        // Adjust touch targets based on viewport
        if (newViewport === 'mobile' && !('ontouchstart' in window)) {
            // Desktop user resized to mobile view
            this.announceToScreenReader('Touch interaction recommended for this view');
        }
    }
}

// Initialize dashboard enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DashboardEnhancements();
});

// Export for potential use in other scripts
window.DashboardEnhancements = DashboardEnhancements;