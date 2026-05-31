/**
 * ==========================================
 * PREMIUM DYNAMIC WEB INTERACTIONS (GURUKUL)
 * ==========================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Theme Manager
    initThemeManager();

    // Initialize Page Preloader
    initPagePreloader();

    // Initialize Smooth Page Transitions
    initPageTransitions();

    // Initialize Scroll Progress Bar
    initScrollProgress();

    // Initialize Sticky Header
    initStickyHeader();

    // Initialize Mobile Navigation Drawer
    initMobileDrawer();

    // Initialize IntersectionObserver Scroll Reveals
    initScrollReveals();

    // Initialize Dynamic Counter Animation (Observer-bound)
    initStatsCounters();

    // Initialize Mouse Parallax coordinate tracking
    initMouseParallax();

    // Initialize Back to Top Controller
    initBackToTop();

    // Set Current Year in Footer
    initFooterYear();

    // Initialize Global Floating Label System
    initFloatingLabels();
});

/**
 * 1. PERSISTENT THEME MANAGER (DARK / LIGHT STYLE)
 */
function initThemeManager() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (!themeToggleBtn) return;

    // Check saved theme or default to light
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-theme-active');
        updateThemeToggleIcon(true);
    } else {
        updateThemeToggleIcon(false);
    }

    themeToggleBtn.addEventListener('click', () => {
        const isDark = document.body.classList.toggle('dark-theme-active');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeToggleIcon(isDark);
    });
}

function updateThemeToggleIcon(isDark) {
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (!themeToggleBtn) return;

    if (isDark) {
        themeToggleBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path>
            </svg>
        `;
        themeToggleBtn.title = "Switch to Light Mode";
    } else {
        themeToggleBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
            </svg>
        `;
        themeToggleBtn.title = "Switch to Dark Mode";
    }
}

/**
 * 2. STICKY NAVBAR CONTROLLER
 */
function initStickyHeader() {
    const header = document.querySelector('.header');
    if (!header) return;

    const scrollThreshold = 30;

    const handleScroll = () => {
        if (window.scrollY > scrollThreshold) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll);
    // Execute on load in case of direct deep link reload
    handleScroll();
}

/**
 * 3. MOBILE MENU / SLIDING GLASS DRAWER OVERLAY
 */
function initMobileDrawer() {
    const trigger = document.getElementById('menu-trigger');
    const drawer = document.getElementById('mobile-drawer');
    const links = document.querySelectorAll('.drawer-link');

    if (!trigger || !drawer) return;

    const toggleMenu = () => {
        trigger.classList.toggle('active');
        drawer.classList.toggle('active');
        document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
    };

    trigger.addEventListener('click', toggleMenu);

    // Close menu when navigation drawers are clicked
    links.forEach(link => {
        link.addEventListener('click', () => {
            trigger.classList.remove('active');
            drawer.classList.remove('active');
            document.body.style.overflow = '';
        });
    });

    // Close menu if viewport scales larger than 992px
    window.addEventListener('resize', () => {
        if (window.innerWidth > 992 && drawer.classList.contains('active')) {
            trigger.classList.remove('active');
            drawer.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

/**
 * 4. LIGHTWEIGHT SCROLL ENTRANCE OBSERVER
 */
function initScrollReveals() {
    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length === 0) return;

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Stop observing once animated to enhance scrolling speeds
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: "0px 0px -40px 0px"
    });

    revealElements.forEach(el => revealObserver.observe(el));
}

/**
 * 5. ANIMATED NUMERICAL STAT COUNTERS
 */
function initStatsCounters() {
    const counterElements = document.querySelectorAll('.counter-value');
    if (counterElements.length === 0) return;

    const countTo = (element) => {
        const target = parseInt(element.getAttribute('data-target'), 10);
        if (isNaN(target)) return;

        const duration = 2000; // Total count duration in ms
        const frameRate = 1000 / 60; // 60 FPS
        const totalFrames = Math.round(duration / frameRate);
        let frame = 0;

        const animate = () => {
            frame++;
            // Cubic ease out count progress
            const progress = frame / totalFrames;
            const easeOutProgress = 1 - Math.pow(1 - progress, 3);
            const currentCount = Math.round(easeOutProgress * target);

            element.textContent = currentCount;

            if (frame < totalFrames) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = target; // Safeguard final count
            }
        };

        requestAnimationFrame(animate);
    };

    const counterObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                countTo(entry.target);
                observer.unobserve(entry.target); // Count once
            }
        });
    }, {
        threshold: 0.5
    });

    counterElements.forEach(el => counterObserver.observe(el));
}

/**
 * 6. BACK-TO-TOP TRIGGER
 */
function initBackToTop() {
    const btn = document.getElementById('back-to-top');
    if (!btn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    });

    btn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * 7. AUTOMATED FOOTER YEAR STAMP
 */
function initFooterYear() {
    const yearSpan = document.getElementById('footer-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
}

/**
 * 8. SCROLL PROGRESS INDICATOR
 */
function initScrollProgress() {
    const progress = document.getElementById('scroll-progress');
    if (!progress) return;
    
    const calculateProgress = () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
        progress.style.width = scrolled + "%";
    };

    window.addEventListener('scroll', calculateProgress);
    calculateProgress();
}

/**
 * 9. PAGE PRELOADER CONTROLLER
 */
function initPagePreloader() {
    const preloader = document.getElementById('page-preloader');
    if (!preloader) return;
    
    // Dissolve preloader on complete load
    window.addEventListener('load', () => {
        dismissPreloader(preloader);
    });
    
    // Fail-safe backup load
    setTimeout(() => {
        dismissPreloader(preloader);
    }, 1500);
}

function dismissPreloader(preloader) {
    if (preloader.classList.contains('preloader-done')) return;
    
    setTimeout(() => {
        preloader.classList.add('preloader-done');
        
        // Sweeping out the page shutter page loader
        const shutter = document.querySelector('.page-shutter');
        if (shutter) {
            shutter.classList.add('shutter-dismiss');
            setTimeout(() => {
                shutter.classList.remove('shutter-dismiss');
            }, 600);
        }
    }, 300); // 300ms premium brand crest flash
}

/**
 * 10. SMOOTH SHUTTER PAGE TRANSITIONS
 */
function initPageTransitions() {
    const shutter = document.querySelector('.page-shutter');
    if (!shutter) return;

    // Detect all internal navigation links pointing to local HTML resources
    const links = document.querySelectorAll('a[href$=".html"], a[href^="./"][href$=".html"]');
    
    links.forEach(link => {
        if (link.target === '_blank') return;
        
        link.addEventListener('click', (e) => {
            const destination = link.getAttribute('href');
            if (!destination) return;
            
            // Skip hash scrolling links
            if (destination.includes('#') && destination.split('#')[0] === window.location.pathname.split('/').pop()) {
                return;
            }
            
            e.preventDefault();
            
            // Close mobile drawer if active first
            const trigger = document.getElementById('menu-trigger');
            const drawer = document.getElementById('mobile-drawer');
            if (drawer && drawer.classList.contains('active')) {
                trigger.classList.remove('active');
                drawer.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Trigger horizontal/vertical full screen colored shutter slide in
            shutter.classList.remove('shutter-dismiss');
            shutter.classList.add('shutter-active');
            
            setTimeout(() => {
                window.location.href = destination;
            }, 600);
        });
    });
}

/**
 * 11. MOUSE CURSOR PARALLAX COORDINATE CONTROLLER
 */
function initMouseParallax() {
    const parallaxSections = document.querySelectorAll('.hero, .page-hero');
    if (parallaxSections.length === 0) return;
    
    parallaxSections.forEach(sect => {
        const layers = sect.querySelectorAll('.parallax-layer');
        if (layers.length === 0) return;
        
        sect.addEventListener('mousemove', (e) => {
            const rect = sect.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            layers.forEach((layer, idx) => {
                // Alternating layer movement speeds
                const factor = (idx + 1) * 0.035;
                const tx = x * factor;
                const ty = y * factor;
                layer.style.transform = `translate(${tx}px, ${ty}px)`;
            });
        });
        
        sect.addEventListener('mouseleave', () => {
            layers.forEach(layer => {
                layer.style.transform = 'translate(0, 0)';
            });
        });
    });
}

/**
 * 12. CENTRALIZED REUSABLE FLOATING LABEL CONTROLLER
 */
function initFloatingLabels() {
    const groups = document.querySelectorAll('.form-group, .floating-group, .floating-container');
    
    groups.forEach(group => {
        const input = group.querySelector('input, textarea, select');
        const label = group.querySelector('label');
        if (!input || !label) return;
        
        // Ensure inputs are not hidden or submit buttons
        if (input.type === 'submit' || input.type === 'hidden' || input.type === 'button') return;
        
        // Upgrade classes dynamically
        group.classList.add('floating-container');
        input.classList.add('floating-input-field');
        label.classList.add('floating-label-text');
        
        // If it's a file input, we want it to always be considered filled so label doesn't overlap chosen file
        const isFile = input.type === 'file';
        
        const checkValue = () => {
            if (isFile || (input.value && input.value.trim() !== '')) {
                group.classList.add('is-filled');
            } else {
                group.classList.remove('is-filled');
            }
        };
        
        // Monitor events
        input.addEventListener('focus', () => {
            group.classList.add('is-focused');
        });
        
        input.addEventListener('blur', () => {
            group.classList.remove('is-focused');
            checkValue();
        });
        
        input.addEventListener('input', checkValue);
        input.addEventListener('change', checkValue);
        
        // Run initially
        checkValue();
        
        // Support browser autofill detection
        setTimeout(checkValue, 100);
        setTimeout(checkValue, 500);
    });
}


