<?php
/**
 * ========================================================
 * ADMIN PORTAL FOOTER COMPONENT (GURUKUL)
 * ========================================================
 */
?>
            </main> <!-- Close admin-viewport -->
        </div> <!-- Close admin-main -->
    </div> <!-- Close admin-wrapper -->
    <!-- Reusable Theme switcher & Form Component Upgrader -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initThemeToggle();
            initFloatingLabels();
        });

        // 1. Unified Theme switching manager
        function initThemeToggle() {
            const toggleBtn = document.getElementById('admin-theme-toggle');
            if (!toggleBtn) return;

            toggleBtn.addEventListener('click', () => {
                const body = document.body;
                body.classList.toggle('light-theme-active');
                
                if (body.classList.contains('light-theme-active')) {
                    localStorage.setItem('gurukul_admin_theme', 'light');
                } else {
                    localStorage.setItem('gurukul_admin_theme', 'dark');
                }
            });
        }

        // 2. Dynamic Floating labels engine (skipping static Option A fields)
        function initFloatingLabels() {
            const groups = document.querySelectorAll('.form-group, .floating-group, .floating-container');
            
            groups.forEach(group => {
                // Return immediately if configured as a standard static form element (Option A override)
                if (group.classList.contains('no-float') || group.closest('.no-float-form')) {
                    return;
                }

                const input = group.querySelector('input, textarea, select');
                const label = group.querySelector('label');
                if (!input || !label) return;
                
                // Ensure inputs are not hidden or submit/reset buttons
                if (input.type === 'submit' || input.type === 'hidden' || input.type === 'button' || input.type === 'reset') return;
                
                // Upgrade classes dynamically
                group.classList.add('floating-container');
                input.classList.add('floating-input-field');
                label.classList.add('floating-label-text');
                
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
    </script>
</body>
</html>
