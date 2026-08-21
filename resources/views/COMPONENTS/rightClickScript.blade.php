<script>
    $( document ).ready( function(){
        const $targetElement = $('.target-element')
        const $contextMenu   = $('.context-menu')
        let longPressTimer;
        let isContextMenuVisible = false

        // Show context menu function
        function showContextMenu(x, y) {

            const padding = 10
            const menuWidth = $contextMenu.outerWidth()
            const menuHeight = $contextMenu.outerHeight()
            const windowWidth = $(window).width()
            const windowHeight = $(window).height()
            // Adjust positions to keep menu within viewport
            x = Math.min(Math.max(padding, x), windowWidth - menuWidth - padding)
            y = Math.min(Math.max(padding, y), windowHeight - menuHeight - padding)

            $contextMenu
                .css({
                    left: x + 'px',
                    top: y + 'px'
                }).addClass('visible')
                isContextMenuVisible = true
            }

            // Hide context menu function
            function hideContextMenu() {
                $contextMenu.removeClass('visible')
                isContextMenuVisible = false
            }

            // Disable default context menu
            $(document).on('contextmenu', function(e) {
                e.preventDefault()
            })

            // Handle right click
            $( document ).on('mouseup', ".target-element", function(e) {
                if (e.button === 2) {
                    e.preventDefault()
                    e.stopPropagation()
                    showContextMenu(e.clientX, e.clientY)
                }
            })

            // Handle long press
            let touchStartX, touchStartY
            $targetElement.on('touchstart', function(e) {
                const touch = e.originalEvent.touches[0]
                touchStartX = touch.clientX
                touchStartY = touch.clientY

                longPressTimer = setTimeout(function() {
                    showContextMenu(touch.clientX, touch.clientY)
                    e.preventDefault()
                }, 450)
            }).on('touchend touchcancel', function() {
                clearTimeout(longPressTimer)
            }).on('touchmove', function(e) {
                const touch = e.originalEvent.touches[0]
                const moveThreshold = 10

            if (Math.abs(touch.clientX - touchStartX) > moveThreshold ||Math.abs(touch.clientY - touchStartY) > moveThreshold ) {
                clearTimeout(longPressTimer)
            }
        });

        // Handle menu item clicks
        $('.context-menu-item').on('click', function() {
            hideContextMenu()
        })

        // Close menu when clicking outside
        $(document).on('mousedown touchstart', function(e) {
            if (isContextMenuVisible && !$(e.target).closest('.context-menu').length) {
                hideContextMenu()
            }
        })

        // Close menu when scrolling
        $(window).on('scroll', function() {
            if (isContextMenuVisible) {
                hideContextMenu()
            }
        })

        // Prevent text selection
        $targetElement.on('selectstart', function(e) {
            e.preventDefault()
        })

        // Handle window resize
        $(window).on('resize', function() {
            if (isContextMenuVisible) {
                hideContextMenu()
            }
        })
    })
</script>
