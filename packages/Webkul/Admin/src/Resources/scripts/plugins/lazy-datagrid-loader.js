// Lazy Load DataGrid - only load when visible
export function initializeDataGridLazyLoad() {
    if (!window.IntersectionObserver) {
        console.warn('IntersectionObserver not supported');
        return;
    }
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const datagrid = entry.target;
                
                // Trigger datagrid load if not already loading
                if (!datagrid.dataset.loaded && !datagrid.dataset.loading) {
                    datagrid.dataset.loading = 'true';
                    
                    // Trigger Vue component's get() method if available
                    if (window.app && window.app._context?.app?.mount) {
                        // Fire event or trigger via setTimeout to allow DOM ready
                        setTimeout(() => {
                            const event = new CustomEvent('datagrid:load', { bubbles: true });
                            datagrid.dispatchEvent(event);
                            datagrid.dataset.loaded = 'true';
                            delete datagrid.dataset.loading;
                        }, 50);
                    }
                }
                
                // Stop observing this element after it's been triggered
                observer.unobserve(datagrid);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '50px'
    });
    
    // Find all datagrid containers and observe them
    const datagrids = document.querySelectorAll('[data-datagrid-lazy]');
    datagrids.forEach(dg => observer.observe(dg));
    
    return observer;
}

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('[data-datagrid-lazy]').length > 0) {
        initializeDataGridLazyLoad();
    }
});
