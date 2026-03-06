// Global Body Overflow Manager - prevents multiple repaints
window.BodyOverflowManager = (() => {
    let stack = 0;
    
    return {
        push() {
            stack++;
            if (stack === 1) {
                document.body.style.overflow = 'hidden';
                document.body.style.transition = 'overflow 0.1s ease-out';
            }
        },
        
        pop() {
            stack = Math.max(0, stack - 1);
            if (stack === 0) {
                document.body.style.overflow = '';
                document.body.style.transition = '';
            }
        },
        
        reset() {
            stack = 0;
            document.body.style.overflow = '';
            document.body.style.transition = '';
        }
    };
})();

// Plugin for Vue
export default {
    install(app) {
        app.config.globalProperties.$bodyOverflow = window.BodyOverflowManager;
        
        // Auto-cleanup on app unmount
        window.addEventListener('beforeunload', () => {
            window.BodyOverflowManager.reset();
        });
    }
};
