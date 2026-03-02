<!-- Draggable Blocks Component -->
<v-draggable-blocks 
    storage-key="{{ $storageKey ?? 'default' }}"
    {{ $attributes }}
>
    {{ $slot }}
</v-draggable-blocks>

@pushOnce('scripts')
    <script type="text/x-template" id="v-draggable-blocks-template">
        <div class="draggable-container" ref="container">
            <slot></slot>
        </div>
    </script>

    <script type="module">
        app.component('v-draggable-blocks', {
            template: '#v-draggable-blocks-template',
            
            props: {
                storageKey: {
                    type: String,
                    default: 'default'
                }
            },

            data() {
                return {
                    blocks: [],
                    draggedElement: null,
                    draggedIndex: null,
                    placeholder: null,
                    isDragging: false,
                };
            },

            mounted() {
                this.$nextTick(() => {
                    this.initDraggable();
                });
            },

            methods: {
                initDraggable() {
                    const container = this.$refs.container;
                    if (!container) return;

                    // Find all draggable blocks (cards with specific class)
                    this.blocks = Array.from(container.querySelectorAll('.draggable-block'));
                    
                    if (this.blocks.length === 0) {
                        // Auto-detect blocks if not marked
                        this.blocks = Array.from(container.querySelectorAll(':scope > div > .bg-white.rounded-2xl, :scope > .bg-white.rounded-2xl'));
                    }

                    // Load saved order
                    this.loadOrder();

                    // Add drag handles and events to each block
                    this.blocks.forEach((block, index) => {
                        this.setupBlock(block, index);
                    });
                },

                setupBlock(block, index) {
                    // Check if already setup
                    if (block.dataset.draggableSetup) return;
                    block.dataset.draggableSetup = 'true';
                    block.dataset.blockIndex = index;

                    // Find or create header
                    let header = block.querySelector('.px-6.py-4, .block-header');
                    if (!header) {
                        header = block.querySelector(':first-child');
                    }

                    if (header && !header.querySelector('.drag-handle')) {
                        // Create drag handle
                        const handle = document.createElement('div');
                        handle.className = 'drag-handle cursor-grab active:cursor-grabbing p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mr-2';
                        handle.innerHTML = `
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                            </svg>
                        `;
                        handle.title = 'Перетащите для изменения порядка';

                        // Add handle to header (at the start)
                        const headerContent = header.querySelector('.flex.items-center.gap-3');
                        if (headerContent) {
                            headerContent.insertBefore(handle, headerContent.firstChild);
                        } else {
                            header.insertBefore(handle, header.firstChild);
                            header.style.display = 'flex';
                            header.style.alignItems = 'center';
                        }

                        // Add drag events
                        handle.addEventListener('mousedown', (e) => this.onDragStart(e, block));
                    }

                    // Add visual feedback classes
                    block.classList.add('draggable-block-item');
                },

                onDragStart(e, block) {
                    e.preventDefault();
                    
                    this.isDragging = true;
                    this.draggedElement = block;
                    this.draggedIndex = parseInt(block.dataset.blockIndex);

                    // Get initial position
                    const rect = block.getBoundingClientRect();
                    const offsetX = e.clientX - rect.left;
                    const offsetY = e.clientY - rect.top;

                    // Create placeholder
                    this.placeholder = document.createElement('div');
                    this.placeholder.className = 'draggable-placeholder bg-violet-100 dark:bg-violet-900/30 border-2 border-dashed border-violet-400 rounded-2xl transition-all duration-200';
                    this.placeholder.style.height = rect.height + 'px';
                    this.placeholder.style.margin = getComputedStyle(block).margin;

                    // Style dragged element
                    block.style.position = 'fixed';
                    block.style.width = rect.width + 'px';
                    block.style.height = rect.height + 'px';
                    block.style.left = rect.left + 'px';
                    block.style.top = rect.top + 'px';
                    block.style.zIndex = '9999';
                    block.style.pointerEvents = 'none';
                    block.style.opacity = '0.9';
                    block.style.transform = 'scale(1.02)';
                    block.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.25)';
                    block.style.transition = 'transform 0.1s, box-shadow 0.1s';
                    block.classList.add('dragging');

                    // Insert placeholder
                    block.parentNode.insertBefore(this.placeholder, block);

                    // Mouse move handler
                    const onMouseMove = (e) => {
                        block.style.left = (e.clientX - offsetX) + 'px';
                        block.style.top = (e.clientY - offsetY) + 'px';

                        // Find drop target
                        const dropTarget = this.findDropTarget(e.clientY);
                        if (dropTarget && dropTarget !== this.placeholder) {
                            const rect = dropTarget.getBoundingClientRect();
                            const middle = rect.top + rect.height / 2;
                            
                            if (e.clientY < middle) {
                                dropTarget.parentNode.insertBefore(this.placeholder, dropTarget);
                            } else {
                                dropTarget.parentNode.insertBefore(this.placeholder, dropTarget.nextSibling);
                            }
                        }
                    };

                    // Mouse up handler
                    const onMouseUp = () => {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                        this.onDragEnd();
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                },

                findDropTarget(y) {
                    const container = this.$refs.container;
                    const blocks = Array.from(container.querySelectorAll('.draggable-block-item:not(.dragging)'));
                    
                    for (const block of blocks) {
                        const rect = block.getBoundingClientRect();
                        if (y >= rect.top && y <= rect.bottom) {
                            return block;
                        }
                    }
                    
                    return null;
                },

                onDragEnd() {
                    if (!this.draggedElement || !this.placeholder) return;

                    // Move element to placeholder position
                    this.placeholder.parentNode.insertBefore(this.draggedElement, this.placeholder);

                    // Remove placeholder
                    this.placeholder.remove();
                    this.placeholder = null;

                    // Reset styles
                    this.draggedElement.style.position = '';
                    this.draggedElement.style.width = '';
                    this.draggedElement.style.height = '';
                    this.draggedElement.style.left = '';
                    this.draggedElement.style.top = '';
                    this.draggedElement.style.zIndex = '';
                    this.draggedElement.style.pointerEvents = '';
                    this.draggedElement.style.opacity = '';
                    this.draggedElement.style.transform = '';
                    this.draggedElement.style.boxShadow = '';
                    this.draggedElement.style.transition = '';
                    this.draggedElement.classList.remove('dragging');

                    // Save new order
                    this.saveOrder();

                    this.draggedElement = null;
                    this.isDragging = false;
                },

                saveOrder() {
                    const container = this.$refs.container;
                    const blocks = Array.from(container.querySelectorAll('.draggable-block-item'));
                    
                    const order = blocks.map((block, index) => {
                        // Try to get a unique identifier
                        const header = block.querySelector('h3');
                        const title = header ? header.textContent.trim() : `block-${index}`;
                        block.dataset.blockIndex = index;
                        return title;
                    });

                    localStorage.setItem(`draggable-order-${this.storageKey}`, JSON.stringify(order));
                    
                    // Show toast
                    this.$emitter.emit('add-flash', { 
                        type: 'success', 
                        message: 'Порядок блоков сохранён' 
                    });
                },

                loadOrder() {
                    const savedOrder = localStorage.getItem(`draggable-order-${this.storageKey}`);
                    if (!savedOrder) return;

                    try {
                        const order = JSON.parse(savedOrder);
                        const container = this.$refs.container;
                        
                        // Find parent containers for left and right columns
                        const leftColumn = container.querySelector('.xl\\:col-span-2 > .space-y-6') || 
                                          container.querySelector('.xl\\:col-span-2');
                        const rightColumn = container.querySelector('.space-y-6:last-child') ||
                                           container.querySelector(':scope > div:last-child');

                        if (!leftColumn && !rightColumn) return;

                        // Reorder blocks based on saved order
                        order.forEach((title) => {
                            this.blocks.forEach(block => {
                                const header = block.querySelector('h3');
                                const blockTitle = header ? header.textContent.trim() : '';
                                
                                if (blockTitle === title) {
                                    const parent = block.parentNode;
                                    parent.appendChild(block);
                                }
                            });
                        });
                    } catch (e) {
                        console.error('Error loading block order:', e);
                    }
                }
            }
        });
    </script>

    <style>
        .draggable-block-item {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .draggable-block-item:hover .drag-handle {
            opacity: 1;
        }
        
        .drag-handle {
            opacity: 0.5;
            transition: opacity 0.2s ease;
        }
        
        .draggable-placeholder {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
    </style>
@endPushOnce
