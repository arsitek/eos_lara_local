<script>
    /**
     * Multi-Level Hierarchical Dropdown Component
     *
     * This script handles a complex 4-level hierarchical dropdown with the following features:
     * - Single/Multiple selection modes
     * - Collapsible/Expandable group headers
     * - Real-time search with auto-expansion
     * - Auto-reset to default state
     * - iOS-style visual design
     *
     * Structure: Level 1 > Level 2 > Level 3 > Level 4 (Selectable Options)
     */
    $(document).ready(function() {
        // ====================================================================
        // ELEMENT SELECTORS & INITIALIZATION
        // ====================================================================

        // Main dropdown elements - these control the overall dropdown behavior
        const $trigger      = $('.select-trigger')      // The clickable trigger that opens/closes dropdown
        const $container    = $('.options-container')   // The container that holds all dropdown options
        const $options      = $('.option')              // All selectable options in the dropdown
        const $selectedText = $('.selected-text')       // Display area showing current selection
        const $searchInput  = $('.search-input')        // Search input field for filtering options
        const $noResults    = $('.no-results')          // Message shown when no search results found
        const $optionGroups = $('.option-group')        // All hierarchical groups (levels 1-4)
        const $clearOption  = $('.clear-option')        // Button to clear all selections

        // Selection state management
        let selectedValues  = []                         // Array to store all selected values
        const baseValue     = $options.closest(".selected") // Pre-selected base value (if any)

        function ensureDoubleClickHint($scope = $('.ios-select-multiple')) {
            $scope.each(function() {
                const $select = $(this)
                const $currentContainer = $select.find('.options-container').first()

                if (!$currentContainer.length || $currentContainer.children('.multiple-select-hint').length) return

                // Hint ditambahkan sekali per dropdown agar user tahu opsi double click tanpa mengganggu search.
                const $hint = $('<div/>', {
                    class: 'multiple-select-hint',
                    text: '**Double click pada item untuk memilih dan menutup dropdown.'
                })
                const $searchContainer = $currentContainer.children('.search-container').first()

                if ($searchContainer.length) {
                    $hint.insertAfter($searchContainer)
                } else {
                    $currentContainer.prepend($hint)
                }
            })
        }

        ensureDoubleClickHint()

        function isUnitKerjaSingleMode($select) {
            // Marker ini dipakai halaman tertentu seperti PERKIN agar unit kerja wajib satu pilihan.
            return $select.data('unitkerja-selection') === 'single'
                || (window.location.pathname.includes('/report-perkin') && $select.find('.unitkerja-container').length > 0)
        }

        function setDefaultUnitkerjaText($scope = $('.unitkerja-container')) {
            // Default label unit kerja tidak lagi mengikuti unit kerja dari session.
            $scope.closest('.ios-select-multiple').find('.selected-text').text('Pilih Unitkerja')
        }

        setDefaultUnitkerjaText()
        // ====================================================================
        // INITIALIZATION - Setup default selected values
        // ====================================================================

        // Initialize with any pre-selected values from the DOM
        // This allows the dropdown to start with default selections
        selectedValues.push({
            value: baseValue.data('value'),    // The actual value to be sent to server
            text: baseValue.data('text'),      // Display text for the user
            jenis: baseValue.data('jenis'),    // Type/category of selection (unitkerja, sumberdana, etc.)
            isSingle: baseValue.attr('single') // Whether this type allows single or multiple selection
        })

        // ====================================================================
        // DROPDOWN OPEN/CLOSE FUNCTIONALITY
        // ====================================================================

        // Toggle dropdown visibility when trigger is clicked
        // Uses slideToggle for smooth animation (200ms duration)
        $trigger.on('click', function(e) {
            e.stopPropagation() // Prevent event bubbling to document click handler

            const $currentDropdown = $(this).closest('.ios-select-multiple')
            const $currentContainer = $currentDropdown.find('.options-container')

            ensureDoubleClickHint($currentDropdown)

            // Close all OTHER open dropdowns first
            $('.ios-select-multiple').not($currentDropdown).each(function() {
                const $otherContainer = $(this).find('.options-container')
                if ($otherContainer.is(':visible')) {
                    $otherContainer.slideUp(200)

                    // Reset the other dropdown to default state
                    const $otherSelect = $(this)
                    $otherSelect.find('.search-input').val('')
                    $otherSelect.find('.option').removeClass('hidden')
                    $otherSelect.find('.option-group').show()
                    $otherSelect.find('.group-header').addClass('collapsed')
                    $otherSelect.find('.option-group.level-2, .option-group.level-3, .option-group.level-4').hide()
                    $otherSelect.find('.group-header.collapsed').siblings('.option, .option-group').hide()
                    $otherSelect.find('.no-results').hide()
                }
            })

            // Then toggle the current dropdown
            $currentContainer.slideToggle(200)
        })

        // ====================================================================
        // HIERARCHICAL GROUP COLLAPSE/EXPAND SYSTEM
        // ====================================================================

        // Handle clicking on group headers to expand/collapse sections
        // This creates the tree-like navigation structure
        $(document).on('click', '.group-header', function(e) {
            e.stopPropagation() // Don't trigger dropdown close or parent group toggles

            const $header = $(this)
            const $group = $header.closest('.option-group')

            // Check if this is a selectable header (has selection attributes)
            const isSelectable = $header.hasClass('selectable-header')

            // Handle click on toggle icon for expansion/collapse
            const clickedOnToggle = $(e.target).hasClass('toggle-icon')

            if (isSelectable && !clickedOnToggle) {
                // ====================================================================
                // SELECTABLE HEADER LOGIC - Same as regular option selection
                // ====================================================================

                // Extract data from the clicked header
                const value    = $header.data('value')    // Unique identifier for this option
                const jenis    = $header.data('jenis')    // Type category (unitkerja, sumberdana, etc.)
                const text     = $header.data('text')     // Human readable display text
                const $select  = $header.closest('.ios-select-multiple') // Parent dropdown container
                const isSingle = $header.attr('single') // Selection mode

                // ====================================================================
                // SPECIAL LOGIC FOR SUMBERDANA - Auto-select all children
                // ====================================================================
                if (jenis === 'sumberdana') {

                    // Find all child options (smallest children) under this header
                    const $childOptions = $group.find('.option.sumberdanaOption')
                    // Find all child headers under this header for visual selection
                    const $childHeaders = $group.find('.group-header.selectable-header')

                    // Check if header is currently selected
                    const isHeaderSelected = $header.hasClass('selected')

                    if (isHeaderSelected) {
                        // If header is selected, deselect it and all children
                        $header.removeClass('selected')
                        $childOptions.removeClass('selected')
                        $childHeaders.removeClass('selected')

                        // Remove ONLY child options from selectedValues array (not headers)
                        $childOptions.each(function() {
                            const childValue = $(this).data('value')
                            selectedValues = selectedValues.filter(v => v.value !== childValue || v.jenis !== jenis)
                        })
                    } else {
                        // If header is not selected, select it and all children
                        $header.addClass('selected')
                        $childOptions.addClass('selected')
                        $childHeaders.addClass('selected')

                        // Add ONLY smallest child options to selectedValues (not headers)
                        $childOptions.each(function() {
                            const $childOption = $(this)
                            const childValue = $childOption.data('value')
                            const childText = $childOption.data('text')

                            // Check if not already in selectedValues
                            const exists = selectedValues.some(v => v.value === childValue && v.jenis === jenis)
                            if (!exists) {
                                selectedValues.push({
                                    value: childValue,
                                    text: childText,
                                    jenis,
                                    isSingle: false
                                })
                            }
                        })
                    }

                    // Update display text (will only count smallest children)
                    updateSelectedText($header, $select, jenis, text)
                    return // Exit early for sumberdana
                }

                // ====================================================================
                // SINGLE SELECTION MODE - Only one option can be selected per category
                // ====================================================================
                if ( isSingle == "true" ) {
                    // Find if there's already a selection of this type (jenis)
                    const index = selectedValues.findIndex(v => v.jenis == jenis )

                    // Remove existing selection of same type
                    if (index !== -1) {
                        $select.find(".option, .selectable-header").removeClass('selected')  // Clear all visual selections
                        selectedValues.splice(index, 1)                  // Remove from data array
                        $header.removeClass('selected')                  // Clear current option
                    }

                    // Clear any other selected options of the same type (extra safety)
                    $select.find(".selected").each( function() {
                        if ( $(this).data('jenis') == jenis ) {
                            $(this).removeClass('selected')
                        }
                    })

                    // Add new selection
                    $header.addClass('selected')
                    selectedValues.push({ value, text, jenis, isSingle })
                }

                // Update the display text to reflect current selections
                updateSelectedText( $header, $select, jenis, text );

                // Auto-close dropdown for single selections (better UX)
                if ( isSingle == "true" ) {
                    $select.find('.options-container').slideUp(200)
                }

            } else {
                // ====================================================================
                // REGULAR HEADER EXPANSION/COLLAPSE LOGIC
                // ====================================================================

                // Find ONLY direct children (not nested grandchildren)
                // This prevents accidentally showing/hiding deeper levels
                const $directChildren = $group.children('.option, .option-group').not($header)

                // Toggle the collapsed state class for visual indicators
                $header.toggleClass('collapsed')

                // Animate the expand/collapse with smooth sliding effect
                if ($header.hasClass('collapsed')) {
                    $directChildren.slideUp(200)   // Hide children when collapsed
                } else {
                    $directChildren.slideDown(200) // Show children when expanded
                }
            }
        })

        // ====================================================================
        // OPTION SELECTION LOGIC - The heart of the dropdown functionality
        // ====================================================================

        // Handle clicking on individual selectable options
        // This is where single vs multiple selection logic is implemented
        $(document).on('click', '.option', function(e) {
            e.stopPropagation() // Prevent dropdown from closing when selecting

            // Extract data from the clicked option
            const $option  = $(this)
            const value    = $option.data('value')    // Unique identifier for this option
            const jenis    = $option.data('jenis')    // Type category (unitkerja, sumberdana, etc.)
            const text     = $option.data('text')     // Human readable display text
            const $select  = $( this ).closest('.ios-select-multiple') // Parent dropdown container
            const isSingle = $select.find('.options-container .option').attr('single') // Selection mode

            // ====================================================================
            // SPECIAL CASE: UNIVERSITAS SYIAH KUALA (value "X") EXCLUSIVE SELECT
            // When selected, clear all other unitkerja choices to avoid mixed scope
            // ====================================================================
            const normalizedText       = String(text || '').trim().toLowerCase()
            const isUnitSingleMode     = jenis === 'unitkerja' && isUnitKerjaSingleMode($select)
            const isSelectAllUnitkerja = jenis === 'unitkerja' && !isUnitSingleMode && ( value === 'X' || normalizedText === 'universitas syiah kuala' )
            const isSemuaUnitKerja     = jenis === 'unitkerja' && value === 'semua'
            if ( isSemuaUnitKerja ) {
                // If "Semua Unit Kerja" is selected, treat this as "uncheck all unitkerja options"
                if ($option.hasClass('selected')) {
                    resetSelect($select)
                    $container.slideUp(200)
                    return
                }

                // Deselect all other unitkerja options (including selectable headers) when "Semua Unit Kerja" is chosen
                $select.find('.unitkerjaOption, .selectable-header[data-jenis="unitkerja"]').removeClass('selected')

                // Clear previous unitkerja selections from state
                selectedValues = selectedValues.filter(v => v.jenis !== 'unitkerja')

                // Mark "Semua Unit Kerja" as selected and store in selection state
                $option.addClass('selected')
                if ( value !== undefined && value !== 'X' && !selectedValues.some(v => v.value === value && v.jenis === 'unitkerja') ) {
                    selectedValues.push({ value, text, jenis, isSingle })
                }

                // Update label and close dropdown for clarity
                updateSelectedText( $option, $select, jenis, text )
                $container.slideUp(200)
                return
            }
            if (!isSemuaUnitKerja){
                // If any other unitkerja option is selected, remove "Semua Unit Kerja" selection if it exists to avoid mixed scope
                $select.find('.unitkerjaOption[data-value="semua"]').removeClass('selected')
                selectedValues = selectedValues.filter(v => !(v.jenis === 'unitkerja' && v.value === 'semua'))
                
            }
            if ( isSelectAllUnitkerja ) {
                // If already selected, treat this as "uncheck all selected unitkerja"
                if ($option.hasClass('selected')) {
                    resetSelect($select)
                    $container.slideUp(200)
                    return
                }
                
                // Select ALL unitkerja options (including selectable headers) when Universitas Syiah Kuala is chosen
                const $allUnitkerja = $select.find('.unitkerjaOption, .selectable-header[data-jenis="unitkerja"]')

                // Clear previous unitkerja selections
                selectedValues = selectedValues.filter(v => v.jenis !== 'unitkerja')
                $select.find('.unitkerjaOption, .selectable-header[data-jenis="unitkerja"]').removeClass('selected')

                // Mark all as selected and store in selection state
                $allUnitkerja.each(function(){
                    const $uOpt  = $(this)
                    const val    = $uOpt.data('value')
                    const txt    = $uOpt.data('text')
                    const single = $uOpt.attr('single')
                    if ( val === "semua" ) return;
                    $uOpt.addClass('selected')
                    if ( val !== undefined && val !== 'X' && !selectedValues.some(v => v.value === val && v.jenis === 'unitkerja') ) {
                        selectedValues.push({ value: val, text: txt, jenis: 'unitkerja', isSingle: single })
                    }
                })

                // Update label and close dropdown for clarity
                updateSelectedText( $option, $select, jenis, text )
                $container.slideUp(200)
                return
            }

            // ====================================================================
            // SINGLE SELECTION MODE - Only one option can be selected per category
            // ====================================================================
            if ( isSingle == "true" ) {
                // Find if there's already a selection of this type (jenis)
                const index = selectedValues.findIndex(v => v.jenis == jenis )

                // Remove existing selection of same type
                if (index !== -1) {
                    $select.find(".option").removeClass('selected')  // Clear all visual selections
                    selectedValues.splice(index, 1)                  // Remove from data array
                    $option.removeClass('selected')                  // Clear current option
                }

                // Clear any other selected options of the same type (extra safety)
                $select.find(".selected").each( function() {
                    if ( $(this).data('jenis') == jenis ) {
                        $(this).removeClass('selected')
                    }
                })

                // Add new selection
                $option.addClass('selected')
                selectedValues.push({ value, text, jenis, isSingle })
            }
            // ====================================================================
            // MULTIPLE SELECTION MODE - Multiple options can be selected
            // ====================================================================
            else if ( $option.hasClass('selected') && isSingle == "false" ) {
                // Deselect if already selected (toggle behavior)
                $option.removeClass('selected')
                selectedValues = selectedValues.filter( v => v.value !== value && v.jenis == jenis )
            } else {
                // Add to selection if not already selected
                $option.addClass('selected')
                selectedValues.push({
                    value,
                    text,
                    jenis,
                    isSingle
                })
            }

            // Update the display text to reflect current selections
            updateSelectedText( $option, $select, jenis, text );

            // Auto-close dropdown for single selections (better UX)
            if ( isSingle == "true" ) {
                $container.slideUp(200)
            }
        })

        // ====================================================================
        // DOUBLE CLICK CLOSE FOR SELECTED OPTION
        // ====================================================================

        $(document)
            .off('dblclick.multipleSelectClose', '.options-container .option')
            .on('dblclick.multipleSelectClose', '.options-container .option', function(e) {
                const $option = $(this)
                const $select = $option.closest('.ios-select-multiple')
                const value   = $option.data('value')
                const jenis   = $option.data('jenis')
                const text    = $option.data('text')
                const isSingle = $option.attr('single')

                if (!$option.hasClass('selected')) {
                    // Klik kedua pada mode multiple bisa melakukan toggle off, jadi item dipilih kembali khusus untuk double click.
                    $option.addClass('selected')
                    if (!selectedValues.some(v => v.value === value && v.jenis === jenis)) {
                        selectedValues.push({ value, text, jenis, isSingle })
                    }
                    updateSelectedText($option, $select, jenis, text)
                }

                if (!$option.hasClass('selected')) return

                e.preventDefault()
                e.stopPropagation()

                // Gunakan mekanisme close dropdown yang sudah dipakai komponen, lalu kembalikan fokus ke trigger.
                $select.find('.options-container').slideUp(200, function() {
                    $select.find('.select-trigger').trigger('focus')
                })
            })
        // ====================================================================
        // SELECTED TEXT UPDATE FUNCTION
        // ====================================================================

        /**
         * Updates the dropdown trigger text to show current selection status
         * Handles different display formats based on selection count
         *
         * @param {jQuery} $option - The clicked option element
         * @param {jQuery} $select - The parent dropdown container
         * @param {string} jenis - The type/category of selection
         * @param {string} text - The display text of the selected option
         */
        function updateSelectedText( $option, $select, jenis, text ) {

            const $optionSelected = $select.find('.option.selected')  // All currently selected options
            const $selectedText   = $select.find('.selected-text')    // The display element
            const data            = selectedValues.filter( v => v.jenis == jenis ) // Selections of this type

            // Display logic based on selection count
            if ( data.length === 0 ) {
                // No selections - show placeholder text
                $selectedText.text(`Pilih ${jenis}`)
            } else if (data.length === 1) {
                // Single selection - show the selected item name
                $selectedText.text( data[0].text )
            } else {
                // Multiple selections - show count summary
                if ( data.length > 1 ) {
                    $selectedText.text(`${data.length} ${jenis} dipilih`)
                } else {
                    $selectedText.text( text )
                }
            }
        }

        // ====================================================================
        // CLICK OUTSIDE TO CLOSE - Auto-reset functionality
        // ====================================================================

        /**
         * Handles clicking outside the dropdown to close it and reset to default state
         * This provides a clean user experience by always returning to the original state
         */
        $(document).on('click', function(e) {
            // Check if the click was outside any dropdown
            if (!$(e.target).closest('.ios-select-multiple').length) {
                // Close the dropdown with animation
                $container.slideUp(200)

                // Reset ALL dropdowns to default state (handles multiple dropdowns on same page)
                $('.ios-select-multiple').each(function() {
                    const $select = $(this)

                    // Clear any search filters
                    $select.find('.search-input').val('')

                    // Reset visibility - make all options visible again
                    $select.find('.option').removeClass('hidden')
                    $select.find('.option-group').show()

                    // Collapse all groups back to default hierarchical state
                    $select.find('.group-header').addClass('collapsed')
                    $select.find('.option-group.level-2, .option-group.level-3, .option-group.level-4, .option-group.level-5, .option-group.level-6').hide()
                    $select.find('.group-header.collapsed').siblings('.option, .option-group').hide()

                    // Hide search result messages
                    $select.find('.no-results').hide()
                })
            }
        })

        // ====================================================================
        // REAL-TIME SEARCH FUNCTIONALITY
        // ====================================================================

        /**
         * Advanced search system that provides:
         * - Real-time filtering as user types
         * - Auto-expansion of matching result hierarchies
         * - Clean reset when search is cleared
         * - Smart parent-child relationship handling
         */
        $searchInput.on('input', function () {
            const $select = $(this).closest('.ios-select-multiple')
            const searchTerm = $(this).val().toLowerCase()
            let hasVisibleOptions = false

            // ====================================================================
            // SEARCH RESET - Return to default state when search is cleared
            // ====================================================================
            if (searchTerm === "") {
                // Show all options (remove search filtering)
                $select.find('.option').removeClass('hidden')
                $select.find('.option-group').show()

                // Return to default collapsed hierarchy
                $select.find('.group-header').addClass('collapsed')
                $select.find('.option-group.level-2, .option-group.level-3, .option-group.level-4, .option-group.level-5, .option-group.level-6').hide()
                $select.find('.group-header.collapsed').siblings('.option, .option-group').hide()

                // Hide "no results" message
                $select.find('.no-results').hide()
                return // Exit early - no need to process search logic
            }

            // ====================================================================
            // ACTIVE SEARCH - Filter and display matching results only
            // ====================================================================

            // Start with clean slate - hide everything initially
            $select.find('.option').addClass('hidden')
            $select.find('.option-group').hide()

            // Search through every option for matches
            $select.find('.option').each(function () {
                const $option = $(this)
                const text = $option.text().toLowerCase()

                // Check for matches (includes custom logic for special cases like "gayo lues" -> "galus")
                if (text.includes(searchTerm) || (text.includes("gayo lues") && searchTerm.includes("galus"))) {
                    // Found a match - make it visible
                    $option.removeClass('hidden')
                    hasVisibleOptions = true

                    // Show the complete parent hierarchy for this match
                    // This creates the breadcrumb trail: Level 1 > Level 2 > Level 3 > Matching Option
                    let $parent = $option.closest('.option-group')
                    while ($parent.length) {
                        $parent.show()                                          // Make parent group visible
                        $parent.find('> .group-header').removeClass('collapsed') // Expand parent header
                        $parent = $parent.parent().closest('.option-group')     // Move up one level
                    }
                }
            })

            // ====================================================================
            // CLEAN UP GROUP VISIBILITY - Only show relevant hierarchies
            // ====================================================================

            // Process each group to determine if it should be visible
            $select.find('.option-group').each(function () {
                const $group = $(this)

                // Check if this group contains visible options (direct children)
                const hasVisibleGroupOptions = $group.find('.option:not(.hidden)').length > 0

                // Check if this group contains visible child groups (nested children)
                const hasVisibleChildGroups = $group.find('.option-group').toArray().some(childGroup =>
                    $(childGroup).find('.option:not(.hidden)').length > 0
                )

                // Show group only if it has visible content (options or child groups)
                if (hasVisibleGroupOptions || hasVisibleChildGroups) {
                    $group.show()
                    // Auto-expand groups that contain search results for easy access
                    $group.find('> .group-header').removeClass('collapsed')
                    // Show only the relevant children (matching options and their containers)
                    $group.children('.option:not(.hidden), .option-group').show()
                } else {
                    // Hide empty groups to keep interface clean
                    $group.hide()
                }
            })

            // Show/hide "no results found" message based on search results
            $select.find('.no-results').toggle(!hasVisibleOptions)
        })        // ====================================================================
        // ADDITIONAL EVENT HANDLERS
        // ====================================================================

        /**
         * Prevent dropdown from closing when clicking inside the search input
         * This allows users to position cursor, select text, etc. without interruption
         */
        $searchInput.on('click', function(e) {
            e.stopPropagation() // Don't trigger the document click handler
        })

        /**
         * Clear All Selections - Reset dropdown to unselected state
         * Useful for forms where users need to start over
         */
        const placeholderMap = {
            unitkerja: 'Pilih Unitkerja',
            sumberdana: 'Pilih Sumber dana',
            riwayat: 'Pilih Riwayat'
        }

        const capitalizeFirstLetter = (text = '') => text ? text.charAt(0).toUpperCase() + text.slice(1) : ''

        const resetSelect = ($select) => {
            const jenisList = [...new Set($select.find('.option').map((_, el) => $(el).data('jenis')).get().filter(Boolean))]

            // Remove visual selection
            $select.find('.option, .selectable-header').removeClass('selected')

            // Reset placeholder text and selection state per jenis
            jenisList.forEach( jenis => {
                const placeholder = placeholderMap[jenis] || `Pilih ${capitalizeFirstLetter(jenis)}`
                $select.find('.selected-text').text(placeholder)
                selectedValues = selectedValues.filter(v => v.jenis !== jenis)
            })

            // Reset search + collapse state
            $select.find('.search-input').val('')
            $select.find('.option').removeClass('hidden')
            $select.find('.option-group').show()
            $select.find('.group-header').addClass('collapsed')
            $select.find('.option-group.level-2, .option-group.level-3, .option-group.level-4, .option-group.level-5, .option-group.level-6').hide()
            $select.find('.group-header.collapsed').siblings('.option, .option-group').hide()
            $select.find('.no-results').hide()
        }

        const clearAllDropdowns = () => {
            $('.ios-select-multiple').each((_, el) => resetSelect($(el)))
            // Also clear select2 filter dropdown if present
            $('.filter-data').val('').trigger('change')
        }

        $clearOption.on("click", function() {
            const $select = $(this).closest('.ios-select-multiple')
            resetSelect($select)
        })

        $(document).on('click', '.btn-clear-dropdowns', function(){
            clearAllDropdowns()
        })
        window.methods = {
            getUnitkerja: ( idunit ) => {
                return new Promise( ( resolve, reject ) => {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('rktReportUnit.getUnitkerja') }}",
                        data: { idunit },
                        success: ( res ) => {
                            const { message, data } = res
                            resolve( data )
                        },
                        error: ( err ) => {
                            const message = err.responseJSON.message || "Terjadi kesalahan saat mendapatkan data unit kerja"
                            reject( message )
                        },
                    })
                })
            },
            getSumberdana: ( kodeSd = null ) => {
                return new Promise( ( resolve, reject ) => {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('rktReportUnit.getSumberdana') }}",
                        data: { idunit },
                        success: ( res ) => {
                            resolve( res )
                        },
                        error: ( err ) => {
                            const message = err.responseJSON.message || "Terjadi kesalahan saat mendapatkan data sumberdana"
                            reject( message )
                        },
                    })
                })
            }
        }
        const idunit = "{{ $id_unit ?? session('unitkerja') }}"
        const unitkerjaMap = new Map()
        const unitStartingMap = new Map() // To track which units have starting_unit values
        window.methods.getUnitkerja( idunit ).then( data => {
            const { role } = data
            const allowAutoSelect = !(role === 'superadmin' || role === 'admin' || role == "Pimpinan Unit" || role == "Pimpinan USK" )

            // For superadmin/admin: clear any pre-selected unitkerja text and data
            // if (!allowAutoSelect) {
            //     const $unitSelect = $(".unitkerja-container").closest('.ios-select-multiple')
            //     $unitSelect.find('.option.unitkerjaOption, .selectable-header').removeClass('selected')
            //     selectedValues = selectedValues.filter(v => v.jenis !== 'unitkerja')
            //     $unitSelect.find('.selected-text').text('Pilih Unitkerja')
            // }
            data?.data.forEach( item => {
                const { starting_unit, idunit_1, nama_1, idunit_5, nama_5, idunit_7, nama_7, idunit_9, nama_9, idunit_11, nama_11, idunit_13, nama_13 } = item
                if ( !idunit_1 ) return
                const oneMap = createOrUpdateMap( unitkerjaMap, idunit_1, () => ({ unit: nama_1, five: new Map() }) )

                // Track units with starting_unit values
                if (starting_unit) {
                    unitStartingMap.set(starting_unit, true)
                }

                if ( !idunit_5 ) return
                const fiveMap = createOrUpdateMap( oneMap.five, idunit_5, () => ({ unit: nama_5, seven: new Map() }) )
                if ( !idunit_7 ) return
                const sevenMap = createOrUpdateMap( fiveMap.seven, idunit_7, () => ({ unit: nama_7, nine: new Map() }) )
                if ( !idunit_9 ) return
                const nineMap = createOrUpdateMap( sevenMap.nine, idunit_9, () => ({ unit: nama_9, eleven: new Map() }) )
                if ( !idunit_11 ) return
                const elevenMap = createOrUpdateMap( nineMap.eleven, idunit_11, () => ({ unit: nama_11, thirteen: new Map() }) )
                if ( !idunit_13 ) return
                const thirteenMap = createOrUpdateMap( elevenMap.thirteen, idunit_13, () => ({ unit: nama_13 }) )
            })
            const fragment = document.createDocumentFragment()
            let foundMatch = false
            unitkerjaMap.forEach( (value, level1Id) => {
                const level1       = document.createElement("div")
                const level1Header = document.createElement("div")
                level1.classList.add("option-group", "level-1")
                level1Header.classList.add("group-header", "level-1", "collapsed")
                level1Header.innerHTML = `<span>${value.unit}</span><span class="toggle-icon">▼</span>`;
                level1.appendChild(level1Header);

                // Check if level1 has starting_unit value
                if (unitStartingMap.has(level1Id)) {
                    const option = document.createElement("div")
                    option.classList.add("option", "unitkerjaOption", "level-1")
                    option.setAttribute("data-text", value.unit)
                    option.setAttribute("data-value", level1Id)
                    option.setAttribute("data-jenis", "unitkerja")
                    option.setAttribute("data-group", value.unit)
                    option.setAttribute("single", "true")
                    option.innerHTML = `<span class="checkmark">✓</span><span>${value.unit}</span>`

                    // Auto-select if matches idunit (only when allowed)
                    if (allowAutoSelect && level1Id == idunit) {
                        option.classList.add("selected")
                        level1Header.classList.remove("collapsed")
                        foundMatch = true
                    }

                    level1.appendChild(option)
                }

                // Loop over level-2 items
                value.five.forEach( (fiveValue, level2Id) => {
                    const level2       = document.createElement("div")
                    const level2Header = document.createElement("div")
                    level2.classList.add("option-group", "level-2")
                    level2Header.classList.add("group-header", "level-2", "collapsed")
                    level2Header.innerHTML = `<span>${fiveValue.unit}</span><span class="toggle-icon">▼</span>`
                    level2.appendChild(level2Header)

                    // Append level-2 group to level-1
                    level1.appendChild(level2)

                    // Check if level2 has starting_unit value
                    if (unitStartingMap.has(level2Id)) {
                        const option = document.createElement("div")
                        option.classList.add("option", "unitkerjaOption", "level-2")
                        option.setAttribute("data-text", fiveValue.unit)
                        option.setAttribute("data-value", level2Id)
                        option.setAttribute("data-jenis", "unitkerja")
                        option.setAttribute("data-group", fiveValue.unit)
                        option.setAttribute("single", "true")
                        option.innerHTML = `<span class="checkmark">✓</span><span>${fiveValue.unit}</span>`

                        // Auto-select if matches idunit and expand parent groups (only when allowed)
                        if (allowAutoSelect && level2Id == idunit) {
                            option.classList.add("selected")
                            level1Header.classList.remove("collapsed")
                            level2Header.classList.remove("collapsed")
                            foundMatch = true
                        }

                        level2.appendChild(option)
                    }

                    // Loop over level-3 items
                    fiveValue.seven.forEach( (sevenValue, level3Id) => {
                        const level3       = document.createElement("div")
                        const level3Header = document.createElement("div")
                        level3.classList.add("option-group", "level-3")
                        level3Header.classList.add("group-header", "level-3", "collapsed")
                        level3Header.innerHTML = `<span>${sevenValue.unit}</span><span class="toggle-icon">▼</span>`
                        level3.appendChild(level3Header)

                        // Append level-3 group to level-2
                        level2.appendChild(level3)

                        // Check if level3 has starting_unit value
                        if (unitStartingMap.has(level3Id)) {
                            const option = document.createElement("div")
                            option.classList.add("option", "unitkerjaOption", "level-3")
                            option.setAttribute("data-text", sevenValue.unit)
                            option.setAttribute("data-value", level3Id)
                            option.setAttribute("data-jenis", "unitkerja")
                            option.setAttribute("data-group", sevenValue.unit)
                            option.setAttribute("single", "true")
                            option.innerHTML = `<span class="checkmark">✓</span><span>${sevenValue.unit}</span>`

                            // Auto-select if matches idunit and expand parent groups (only when allowed)
                            if (allowAutoSelect && level3Id == idunit) {
                                option.classList.add("selected")
                                level1Header.classList.remove("collapsed")
                                level2Header.classList.remove("collapsed")
                                level3Header.classList.remove("collapsed")
                                foundMatch = true
                            }

                            level3.appendChild(option)
                        }

                        // Loop over level-4 items
                        sevenValue.nine.forEach( (nineValue, level4Id) => {
                            const level4       = document.createElement("div")
                            const level4Header = document.createElement("div")
                            level4.classList.add("option-group", "level-4")
                            level4Header.classList.add("group-header", "level-4", "collapsed")

                            // Make level-4 header clickable and selectable - Check if it's selectable first
                            const isLevel4Selectable = unitStartingMap.has(level4Id)
                            if (isLevel4Selectable) {
                                level4Header.classList.add("selectable-header")
                                level4Header.setAttribute("data-text", nineValue.unit)
                                level4Header.setAttribute("data-value", level4Id)
                                level4Header.setAttribute("data-jenis", "unitkerja")
                                level4Header.setAttribute("data-group", nineValue.unit)
                                level4Header.setAttribute("single", "true")
                                level4Header.innerHTML = `<span class="checkmark">✓</span><span>${nineValue.unit}</span><span class="toggle-icon">▼</span>`

                                // Auto-select if matches idunit and expand parent groups (only when allowed)
                                if (allowAutoSelect && level4Id == idunit) {
                                    level4Header.classList.add("selected")
                                    level1Header.classList.remove("collapsed")
                                    level2Header.classList.remove("collapsed")
                                    level3Header.classList.remove("collapsed")
                                    level4Header.classList.remove("collapsed")
                                    foundMatch = true
                                }
                            } else {
                                level4Header.innerHTML = `<span>${nineValue.unit}</span><span class="toggle-icon">▼</span>`
                            }

                            level4.appendChild(level4Header)

                            // Append level-4 group to level-3
                            level3.appendChild(level4)

                            // Check if level4 has starting_unit value - Make Level 4 CLICKABLE/SELECTABLE
                            if (unitStartingMap.has(level4Id)) {
                                const option = document.createElement("div")
                                option.classList.add("option", "unitkerjaOption", "level-4")
                                option.setAttribute("data-text", nineValue.unit)
                                option.setAttribute("data-value", level4Id)
                                option.setAttribute("data-jenis", "unitkerja")
                                option.setAttribute("data-group", nineValue.unit)
                                option.setAttribute("single", "true")
                                option.innerHTML = `<span class="checkmark">✓</span><span>${nineValue.unit}</span>`

                                // Auto-select if matches idunit and expand parent groups (only when allowed)
                                if (allowAutoSelect && level4Id == idunit) {
                                    option.classList.add("selected")
                                    level1Header.classList.remove("collapsed")
                                    level2Header.classList.remove("collapsed")
                                    level3Header.classList.remove("collapsed")
                                    level4Header.classList.remove("collapsed")
                                    foundMatch = true
                                }

                                level4.appendChild(option)
                            }

                            // Loop over level-5 items
                            nineValue.eleven.forEach( (elevenValue, level5Id) => {
                                const level5       = document.createElement("div")
                                const level5Header = document.createElement("div")
                                level5.classList.add("option-group", "level-5")
                                level5Header.classList.add("group-header", "level-5", "collapsed")
                                level5Header.innerHTML = `<span>${elevenValue.unit}</span><span class="toggle-icon">▼</span>`
                                level5.appendChild(level5Header)

                                // Append level-5 group to level-4
                                level4.appendChild(level5)

                                // Check if level5 has starting_unit value
                                if (unitStartingMap.has(level5Id)) {
                                    const option = document.createElement("div")
                                    option.classList.add("option", "unitkerjaOption", "level-5")
                                    option.setAttribute("data-text", elevenValue.unit)
                                    option.setAttribute("data-value", level5Id)
                                    option.setAttribute("data-jenis", "unitkerja")
                                    option.setAttribute("data-group", elevenValue.unit)
                                    option.setAttribute("single", "true")
                                    option.innerHTML = `<span class="checkmark">✓</span><span>${elevenValue.unit}</span>`

                                    // Auto-select if matches idunit and expand parent groups (only when allowed)
                                    if (allowAutoSelect && level5Id == idunit) {
                                        option.classList.add("selected")
                                        level1Header.classList.remove("collapsed")
                                        level2Header.classList.remove("collapsed")
                                        level3Header.classList.remove("collapsed")
                                        level4Header.classList.remove("collapsed")
                                        level5Header.classList.remove("collapsed")
                                        foundMatch = true
                                    }

                                    level5.appendChild(option)
                                }

                                // Loop over level-6 items (final selectable level)
                                elevenValue.thirteen.forEach( (thirteenValue, level6Id) => {
                                    // Check if level6 has starting_unit value
                                    if (unitStartingMap.has(level6Id)) {
                                        const option = document.createElement("div")
                                        option.classList.add("option", "unitkerjaOption", "level-6")
                                        option.setAttribute("data-text", thirteenValue.unit)
                                        option.setAttribute("data-value", level6Id)
                                        option.setAttribute("data-jenis", "unitkerja")
                                        option.setAttribute("data-group", elevenValue.unit)
                                        option.setAttribute("single", "true")
                                        option.innerHTML = `<span class="checkmark">✓</span><span>${thirteenValue.unit}</span>`

                                        // Auto-select if matches idunit and expand parent groups (only when allowed)
                                        if (allowAutoSelect && level6Id == idunit) {
                                            option.classList.add("selected")
                                            level1Header.classList.remove("collapsed")
                                            level2Header.classList.remove("collapsed")
                                            level3Header.classList.remove("collapsed")
                                            level4Header.classList.remove("collapsed")
                                            level5Header.classList.remove("collapsed")
                                            foundMatch = true
                                        }

                                        level5.appendChild(option)
                                    }
                                })
                            })
                        })
                    })
                })

                // Finally append the whole level-1 group to the fragment
                fragment.appendChild(level1)
            })
            $(".unitkerja-container").append(fragment)
            setDefaultUnitkerjaText($(".unitkerja-container"))

            // Add dropdown trigger event to show selected data when opened
            $(".unitkerja-container").closest('.ios-select-multiple').find('.select-trigger').on('click', function(e) {
                setTimeout(function() {
                    // Find and expand to show any selected items (both options and selectable headers)
                    const $selectedItems = $(".unitkerjaOption.selected, .selectable-header.selected")
                    if ($selectedItems.length) {
                        $selectedItems.each(function() {
                            const $selectedItem = $(this)
                            // Get all parent groups of the selected item
                            let $currentParent = $selectedItem.closest('.option-group')

                            while ($currentParent.length) {
                                // Expand this level by removing collapsed class and showing children
                                const $header = $currentParent.find('> .group-header')
                                $header.removeClass('collapsed')

                                // Show direct children
                                const $directChildren = $currentParent.children('.option, .option-group').not($header)
                                $directChildren.show()

                                // Move to parent level
                                $currentParent = $currentParent.parent().closest('.option-group')
                            }
                        })

                        // Scroll to first selected item if needed
                        const $container = $(".unitkerja-container").closest('.options-container')
                        if ($container.length && $selectedItems.first().length) {
                            const containerHeight = $container.height()
                            const selectedOffset = $selectedItems.first().position().top
                            if (selectedOffset > containerHeight - 50) {
                                $container.scrollTop(selectedOffset - 100)
                            }
                        }
                    }
                }, 250) // Small delay to ensure dropdown is fully opened
            })

            // If a match was found, update the selected text and show expanded groups
            if (foundMatch) {
                const $selectedItems = $(".unitkerjaOption.selected, .selectable-header.selected")
                if ($selectedItems.length) {
                    const selectedText = $selectedItems.first().data("text")
                    // $(".unitkerja-container").closest('.ios-select-multiple').find('.selected-text').text(selectedText)

                    // Show all expanded groups (remove collapsed class means they should be visible)
                    $(".unitkerja-container .group-header:not(.collapsed)").each(function() {
                        const $header = $(this)
                        const $group = $header.closest('.option-group')
                        const $directChildren = $group.children('.option, .option-group').not($header)
                        $directChildren.show()
                    })
                }
            }
        }).catch( err => {
            console.error("Error fetching sumberdana:", err)
            tata.error("⛔ Error", err)
        })
        const sumberdanaMap = new Map()
        window.methods.getSumberdana( idunit ).then( res => {
            const { tahun } = res
            if ( tahun == "2024" ) return
            res.data.forEach( item => {
                const { kodeSd2, kodeSd4, kodeSd6, kodeSd8, sd2, sd4, sd6, sd8, sd, kd_sumberdana } = item
                const twoMap = createOrUpdateMap( sumberdanaMap, kodeSd2, () => ({ sumberdana: sd2, four: new Map() }) )
                const fourMap = createOrUpdateMap( twoMap.four, kodeSd4, () => ({ sumberdana: sd4, six: new Map() }) )
                const sixMap = createOrUpdateMap( fourMap.six, kodeSd6, () => ({ sumberdana: sd6, eight: new Map() }) )
                const eightMap = createOrUpdateMap( sixMap.eight, kodeSd8, () => ({ sd, kodeSd: kd_sumberdana }) )
            })
            const fragment = document.createDocumentFragment()
            sumberdanaMap.forEach((value, kodeSd2) => {
                const level1       = document.createElement("div")
                const level1Header = document.createElement("div")
                level1.classList.add("option-group", "level-1")
                level1Header.classList.add("group-header", "level-1", "collapsed", "selectable-header")
                level1Header.setAttribute("data-jenis", "sumberdana")
                level1Header.setAttribute("data-text", value.sumberdana)
                level1Header.setAttribute("data-value", kodeSd2)
                level1Header.setAttribute("data-group", value.sumberdana)
                level1Header.setAttribute("single", false)
                level1Header.innerHTML = `<span class="checkmark">✓</span><span>${value.sumberdana}</span><span class="toggle-icon">▼</span>`;
                level1.appendChild(level1Header);

                // Loop over level-2 items
                value.four.forEach((fourValue, kodeSd4) => {
                    const level2       = document.createElement("div")
                    const level2Header = document.createElement("div")
                    level2.classList.add("option-group", "level-2")
                    level2Header.classList.add("group-header", "level-2", "collapsed", "selectable-header")
                    level2Header.setAttribute("data-jenis", "sumberdana")
                    level2Header.setAttribute("data-text", fourValue.sumberdana)
                    level2Header.setAttribute("data-value", kodeSd4)
                    level2Header.setAttribute("data-group", fourValue.sumberdana)
                    level2Header.setAttribute("single", false)
                    level2Header.innerHTML = `<span class="checkmark">✓</span><span>${fourValue.sumberdana}</span><span class="toggle-icon">▼</span>`
                    level2.appendChild(level2Header)

                    // Append level-2 group to level-1
                    level1.appendChild(level2)

                    fourValue.six.forEach((sixValue, kodeSd6) => {
                        const level3       = document.createElement("div")
                        const level3Header = document.createElement("div")
                        level3.classList.add("option-group", "level-3")
                        level3Header.classList.add("group-header", "level-3", "collapsed", "selectable-header")
                        level3Header.setAttribute("data-jenis", "sumberdana")
                        level3Header.setAttribute("data-text", sixValue.sumberdana)
                        level3Header.setAttribute("data-value", kodeSd6)
                        level3Header.setAttribute("data-group", sixValue.sumberdana)
                        level3Header.setAttribute("single", false)
                        level3Header.innerHTML = `<span class="checkmark">✓</span><span>${sixValue.sumberdana}</span><span class="toggle-icon">▼</span>`
                        level3.appendChild(level3Header)

                        // Append level-3 group to level-2
                        level2.appendChild(level3)

                        sixValue.eight.forEach((eightValue) => {
                            const option = document.createElement("div")
                            option.classList.add("option", "sumberdanaOption", "level-4")
                            option.setAttribute("data-text", eightValue.sd)
                            option.setAttribute("data-jenis", "sumberdana")
                            option.setAttribute("data-value", eightValue.kodeSd)
                            option.setAttribute("data-group", sixValue.sumberdana)
                            option.setAttribute("single", false)
                            option.innerHTML = `<span class="checkmark">✓</span><span>${eightValue.sd}</span>`
                            level3.appendChild(option)
                        })
                    })
                })

                // Finally append the whole level-1 group to the fragment
                fragment.appendChild(level1)
            })
            // find group header that has data-key = 41 using jquery
            $(".sumberdana-container").append(fragment)

            // Add dropdown trigger event to show selected data when sumberdana dropdown opens
            $(".sumberdana-container").closest('.ios-select-multiple').find('.select-trigger').on('click', function(e) {
                setTimeout(function() {
                    // Find and expand to show any selected items (including selectable headers)
                    const $selectedOptions = $(".sumberdanaOption.selected, .selectable-header.selected")
                    if ($selectedOptions.length) {
                        $selectedOptions.each(function() {
                            const $selectedOption = $(this)
                            // Get all parent groups of the selected option
                            let $currentParent = $selectedOption.closest('.option-group')

                            while ($currentParent.length) {
                                // Expand this level by removing collapsed class and showing children
                                const $header = $currentParent.find('> .group-header')
                                $header.removeClass('collapsed')

                                // Show direct children
                                const $directChildren = $currentParent.children('.option, .option-group').not($header)
                                $directChildren.show()

                                // Move to parent level
                                $currentParent = $currentParent.parent().closest('.option-group')
                            }
                        })

                        // Scroll to first selected item if needed
                        const $container = $(".sumberdana-container").closest('.options-container')
                        if ($container.length && $selectedOptions.first().length) {
                            const containerHeight = $container.height()
                            const selectedOffset = $selectedOptions.first().position().top
                            if (selectedOffset > containerHeight - 50) {
                                $container.scrollTop(selectedOffset - 100)
                            }
                        }
                    }
                }, 250) // Small delay to ensure dropdown is fully opened
            })
        }).catch( err => {
            console.error("Error fetching sumberdana:", err)
            tata.error("⛔ Error", err)
        })
    })

    // ====================================================================
    // CUSTOMIZATION GUIDE FOR FUTURE DEVELOPMENT
    // ====================================================================

    /**
     * HOW TO CUSTOMIZE THIS DROPDOWN:
     *
     * 1. ADDING NEW SELECTION MODES:
     *    - Modify the selection logic in the '.option' click handler
     *    - Add new conditions alongside isSingle == "true/false"
     *    - Update updateSelectedText() to handle new display formats
     *
     * 2. CHANGING HIERARCHY LEVELS:
     *    - Adjust the CSS selectors for .level-2, .level-3, .level-4, etc.
     *    - Update the search reset logic to include/exclude new levels
     *    - Modify the collapse/expand logic for new hierarchy depth
     *
     * 3. CUSTOM SEARCH LOGIC:
     *    - Add new matching conditions in the search .each() function
     *    - Implement fuzzy search, regex matching, or API-based search
     *    - Customize the "gayo lues" -> "galus" type special case handling
     *
     * 4. ANIMATION CUSTOMIZATION:
     *    - Change slideToggle(200) duration for different animation speeds
     *    - Replace slideUp/slideDown with fadeIn/fadeOut for different effects
     *    - Add CSS transitions for more complex animations
     *
     * 5. MULTI-DROPDOWN SUPPORT:
     *    - The code already supports multiple dropdowns per page
     *    - Each dropdown maintains independent state
     *    - Customize the $('.ios-select-multiple').each() loops as needed
     *
     * 6. DATA INTEGRATION:
     *    - selectedValues array contains all selection data
     *    - Access via selectedValues.filter(v => v.jenis === 'yourType')
     *    - Integrate with forms by reading selectedValues on submit
     *
     * 7. STYLING CUSTOMIZATION:
     *    - All visual styling is in the companion CSS file
     *    - JavaScript only manages classes: 'selected', 'hidden', 'collapsed'
     *    - Add custom classes here and style them in CSS
     */
</script>
