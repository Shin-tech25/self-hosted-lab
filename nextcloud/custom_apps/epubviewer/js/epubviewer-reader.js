/**
 * EPUB Viewer - Reader Application Script
 */
(function(window, document, $, OC) {
    'use strict';

    /**
     * EPUB Viewer Reader Class
     */
    class EpubViewerReader {
        /**
         * Constructor
         */
        constructor() {
            // Elements
            this.reader = document.getElementById('epubviewer-reader');
            this.readerView = document.getElementById('reader-view');
            this.loadingIndicator = document.querySelector('.loading-indicator');
            this.errorContainer = document.querySelector('.error-container');
            this.errorMessage = document.getElementById('error-message');
            this.currentPageElement = document.getElementById('current-page');
            this.totalPagesElement = document.getElementById('total-pages');
            this.prevPageButton = document.getElementById('prev-page');
            this.nextPageButton = document.getElementById('next-page');
            this.zoomInButton = document.getElementById('zoom-in');
            this.zoomOutButton = document.getElementById('zoom-out');
            this.zoomSelect = document.getElementById('zoom-select');
            this.bookmarkButton = document.getElementById('bookmark-button');
            this.settingsButton = document.getElementById('settings-button');
            this.bookmarkDialog = document.getElementById('bookmark-dialog');
            this.settingsDialog = document.getElementById('settings-dialog');
            
            // Settings
            this.settings = window.EPUBVIEWER_SETTINGS || {};
            this.appName = this.reader.dataset.appname || 'epubviewer';
            this.fileId = parseInt(this.reader.dataset.fileid, 10);
            this.fileName = this.reader.dataset.filename || '';
            this.filePath = this.reader.dataset.filepath || '';
            this.mimeType = this.reader.dataset.mimetype || '';
            this.fileSize = parseInt(this.reader.dataset.filesize, 10) || 0;
            
            // State
            this.currentPage = 1;
            this.totalPages = 0;
            this.currentZoom = 1.0;
            this.bookmarks = [];
            this.preferences = {};
            this.rendition = null;
            this.book = null;
            this.isLoading = true;
            
            // Initialize
            this.init();
        }
        
        /**
         * Initialize the reader
         */
        init() {
            this.bindEvents();
            this.loadPreferences();
            this.loadBookmarks();
            this.loadDocument();
        }
        
        /**
         * Bind event listeners
         */
        bindEvents() {
            // Navigation controls
            this.prevPageButton.addEventListener('click', this.prevPage.bind(this));
            this.nextPageButton.addEventListener('click', this.nextPage.bind(this));
            
            // Zoom controls
            this.zoomInButton.addEventListener('click', this.zoomIn.bind(this));
            this.zoomOutButton.addEventListener('click', this.zoomOut.bind(this));
            this.zoomSelect.addEventListener('change', this.handleZoomChange.bind(this));
            
            // Dialog controls
            this.bookmarkButton.addEventListener('click', this.showBookmarkDialog.bind(this));
            this.settingsButton.addEventListener('click', this.showSettingsDialog.bind(this));
            
            // Close dialog buttons
            document.querySelectorAll('.close-dialog').forEach(button => {
                button.addEventListener('click', this.closeDialogs.bind(this));
            });
            
            // Save bookmark button
            document.getElementById('save-bookmark').addEventListener('click', this.saveBookmark.bind(this));
            
            // Save settings button
            document.getElementById('save-settings').addEventListener('click', this.saveSettings.bind(this));
            
            // Theme select
            document.getElementById('theme-select').addEventListener('change', this.updateThemePreview.bind(this));
            
            // Font size slider
            const fontSizeSlider = document.getElementById('font-size');
            const fontSizeValue = document.getElementById('font-size-value');
            fontSizeSlider.addEventListener('input', () => {
                fontSizeValue.textContent = fontSizeSlider.value + '%';
            });
            
            // Line spacing slider
            const lineSpacingSlider = document.getElementById('line-spacing');
            const lineSpacingValue = document.getElementById('line-spacing-value');
            lineSpacingSlider.addEventListener('input', () => {
                lineSpacingValue.textContent = lineSpacingSlider.value + '%';
            });
            
            // Margin size slider
            const marginSizeSlider = document.getElementById('margin-size');
            const marginSizeValue = document.getElementById('margin-size-value');
            marginSizeSlider.addEventListener('input', () => {
                marginSizeValue.textContent = marginSizeSlider.value + 'px';
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', this.handleKeyDown.bind(this));
        }
        
        /**
         * Load user preferences
         */
        loadPreferences() {
            fetch(OC.generateUrl(`/apps/${this.appName}/api/files/${this.fileId}/preferences/reader`), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load preferences');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.preferences = data.data || {};
                    this.applyPreferences();
                }
            })
            .catch(error => {
                console.error('Error loading preferences:', error);
                // Continue with default preferences
                this.applyPreferences();
            });
        }
        
        /**
         * Apply user preferences
         */
        applyPreferences() {
            // Apply theme
            const theme = this.preferences.theme || this.settings.defaultTheme || 'light';
            this.setTheme(theme);
            
            // Apply zoom
            const zoom = this.preferences.zoom || this.settings.defaultZoom || 1.0;
            this.setZoom(zoom);
            
            // Apply font size
            const fontSize = this.preferences.fontSize || this.settings.defaultFontSize || 100;
            document.getElementById('font-size').value = fontSize;
            document.getElementById('font-size-value').textContent = fontSize + '%';
            
            // Apply line spacing
            const lineSpacing = this.preferences.lineSpacing || 130;
            document.getElementById('line-spacing').value = lineSpacing;
            document.getElementById('line-spacing-value').textContent = lineSpacing + '%';
            
            // Apply margin size
            const marginSize = this.preferences.marginSize || 40;
            document.getElementById('margin-size').value = marginSize;
            document.getElementById('margin-size-value').textContent = marginSize + 'px';
            
            // Set theme select
            document.getElementById('theme-select').value = theme;
        }
        
        /**
         * Load bookmarks
         */
        loadBookmarks() {
            fetch(OC.generateUrl(`/apps/${this.appName}/api/files/${this.fileId}/bookmarks`), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load bookmarks');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.bookmarks = data.data || [];
                }
            })
            .catch(error => {
                console.error('Error loading bookmarks:', error);
            });
        }
        
        /**
         * Load document
         */
        loadDocument() {
            this.setLoading(true);
            
            const contentUrl = OC.generateUrl(`/apps/${this.appName}/api/files/${this.fileId}/content`);
            
            // Determine the file type and load appropriate renderer
            if (this.mimeType.includes('epub')) {
                this.loadEpub(contentUrl);
            } else if (this.mimeType.includes('pdf')) {
                this.loadPdf(contentUrl);
            } else if (this.mimeType.includes('comic') || this.mimeType.includes('cbz') || this.mimeType.includes('cbr')) {
                this.loadComicBook(contentUrl);
            } else {
                this.showError(`Unsupported file type: ${this.mimeType}`);
                this.setLoading(false);
            }
        }
        
        /**
         * Load EPUB document
         * 
         * @param {string} url URL to the EPUB file
         */
        loadEpub(url) {
            // Check if epub.js is loaded
            if (typeof ePub !== 'function') {
                this.loadScript('/apps/epubviewer/js/lib/epub.min.js')
                    .then(() => this.initEpub(url))
                    .catch(error => {
                        console.error('Error loading epub.js:', error);
                        this.showError('Failed to load EPUB reader library.');
                        this.setLoading(false);
                    });
            } else {
                this.initEpub(url);
            }
        }
        
        /**
         * Initialize EPUB reader
         * 
         * @param {string} url URL to the EPUB file
         */
        initEpub(url) {
            try {
                // Create new Book
                this.book = ePub(url);
                
                // Initialize rendition
                this.rendition = this.book.renderTo(this.readerView, {
                    width: '100%',
                    height: '100%',
                    spread: 'auto'
                });
                
                // Load book
                this.rendition.display().then(() => {
                    this.setLoading(false);
                    this.readerView.style.display = 'block';
                    
                    // Get total pages
                    this.book.locations.generate().then(() => {
                        this.totalPages = this.book.locations.total;
                        this.totalPagesElement.textContent = this.totalPages;
                        
                        // Restore last position if available
                        if (this.preferences.lastPosition) {
                            this.book.locations.load(this.preferences.lastPosition);
                            this.rendition.display(this.preferences.lastCfi || 0);
                        }
                    });
                    
                    // Update current page on page change
                    this.rendition.on('relocated', location => {
                        this.currentPage = location.start.location;
                        this.currentPageElement.textContent = this.currentPage;
                        
                        // Save position
                        this.savePosition(location.start.cfi, this.currentPage);
                    });
                    
                    // Apply theme and styles
                    this.applyEpubStyles();
                });
                
                // Handle errors
                this.book.on('openFailed', error => {
                    console.error('Error opening EPUB:', error);
                    this.showError('Failed to open EPUB file.');
                    this.setLoading(false);
                });
            } catch (error) {
                console.error('Error initializing EPUB reader:', error);
                this.showError('Failed to initialize EPUB reader.');
                this.setLoading(false);
            }
        }
        
        /**
         * Apply styles to EPUB content
         */
        applyEpubStyles() {
            if (!this.rendition) return;
            
            const theme = this.preferences.theme || this.settings.defaultTheme || 'light';
            const fontSize = this.preferences.fontSize || this.settings.defaultFontSize || 100;
            const lineSpacing = this.preferences.lineSpacing || 130;
            const marginSize = this.preferences.marginSize || 40;
            
            let styles = {};
            
            // Theme styles
            if (theme === 'dark') {
                styles.body = {
                    color: '#dddddd',
                    background: '#222222'
                };
            } else if (theme === 'sepia') {
                styles.body = {
                    color: '#5b4636',
                    background: '#f4ecd8'
                };
            } else {
                styles.body = {
                    color: '#333333',
                    background: '#ffffff'
                };
            }
            
            // Font size
            styles.body.fontSize = `${fontSize}%`;
            
            // Line spacing
            styles.body.lineHeight = `${lineSpacing}%`;
            
            // Margins
            styles.body.padding = `0 ${marginSize}px`;
            
            // Apply styles
            this.rendition.themes.register('custom', styles);
            this.rendition.themes.select('custom');
        }
        
        /**
         * Load PDF document
         * 
         * @param {string} url URL to the PDF file
         */
        loadPdf(url) {
            // Check if PDF.js is loaded
            if (typeof pdfjsLib === 'undefined') {
                this.loadScript('/apps/epubviewer/js/lib/pdf.min.js')
                    .then(() => this.initPdf(url))
                    .catch(error => {
                        console.error('Error loading PDF.js:', error);
                        this.showError('Failed to load PDF reader library.');
                        this.setLoading(false);
                    });
            } else {
                this.initPdf(url);
            }
        }
        
        /**
         * Initialize PDF reader
         * 
         * @param {string} url URL to the PDF file
         */
        initPdf(url) {
            try {
                // Set worker source
                pdfjsLib.GlobalWorkerOptions.workerSrc = '/apps/epubviewer/js/lib/pdf.worker.min.js';
                
                // Load document
                pdfjsLib.getDocument(url).promise.then(pdf => {
                    this.book = pdf;
                    this.totalPages = pdf.numPages;
                    this.totalPagesElement.textContent = this.totalPages;
                    
                    // Restore last position if available
                    this.currentPage = this.preferences.lastPosition || 1;
                    this.renderPdfPage(this.currentPage);
                    
                    this.setLoading(false);
                    this.readerView.style.display = 'block';
                }).catch(error => {
                    console.error('Error loading PDF:', error);
                    this.showError('Failed to load PDF file.');
                    this.setLoading(false);
                });
            } catch (error) {
                console.error('Error initializing PDF reader:', error);
                this.showError('Failed to initialize PDF reader.');
                this.setLoading(false);
            }
        }
        
        /**
         * Render PDF page
         * 
         * @param {number} pageNumber Page number to render
         */
        renderPdfPage(pageNumber) {
            if (!this.book) return;
            
            // Clear previous content
            this.readerView.innerHTML = '';
            
            // Update current page
            this.currentPage = pageNumber;
            this.currentPageElement.textContent = pageNumber;
            
            // Save position
            this.savePosition(pageNumber, pageNumber);
            
            // Get page
            this.book.getPage(pageNumber).then(page => {
                const scale = this.currentZoom;
                const viewport = page.getViewport({ scale });
                
                // Create canvas
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                
                // Render page
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                
                page.render(renderContext).promise.then(() => {
                    this.readerView.appendChild(canvas);
                });
            });
        }
        
        /**
         * Load comic book
         * 
         * @param {string} url URL to the comic book file
         */
        loadComicBook(url) {
            // For now, we'll use a simple image viewer for comic books
            // In a real implementation, you'd want to use a dedicated comic book reader library
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load comic book');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Create object URL
                    const objectUrl = URL.createObjectURL(blob);
                    
                    // Create image
                    const img = document.createElement('img');
                    img.src = objectUrl;
                    img.style.width = '100%';
                    img.style.height = 'auto';
                    
                    // Add to reader view
                    this.readerView.innerHTML = '';
                    this.readerView.appendChild(img);
                    
                    // Set page info
                    this.currentPage = 1;
                    this.totalPages = 1;
                    this.currentPageElement.textContent = this.currentPage;
                    this.totalPagesElement.textContent = this.totalPages;
                    
                    this.setLoading(false);
                    this.readerView.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading comic book:', error);
                    this.showError('Failed to load comic book file.');
                    this.setLoading(false);
                });
        }
        
        /**
         * Navigate to previous page
         */
        prevPage() {
            if (this.mimeType.includes('epub') && this.rendition) {
                this.rendition.prev();
            } else if (this.mimeType.includes('pdf') && this.book) {
                if (this.currentPage > 1) {
                    this.renderPdfPage(this.currentPage - 1);
                }
            }
        }
        
        /**
         * Navigate to next page
         */
        nextPage() {
            if (this.mimeType.includes('epub') && this.rendition) {
                this.rendition.next();
            } else if (this.mimeType.includes('pdf') && this.book) {
                if (this.currentPage < this.totalPages) {
                    this.renderPdfPage(this.currentPage + 1);
                }
            }
        }
        
        /**
         * Zoom in
         */
        zoomIn() {
            const zoomLevels = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
            let currentIndex = zoomLevels.indexOf(this.currentZoom);
            
            if (currentIndex < zoomLevels.length - 1) {
                this.setZoom(zoomLevels[currentIndex + 1]);
            }
        }
        
        /**
         * Zoom out
         */
        zoomOut() {
            const zoomLevels = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
            let currentIndex = zoomLevels.indexOf(this.currentZoom);
            
            if (currentIndex > 0) {
                this.setZoom(zoomLevels[currentIndex - 1]);
            }
        }
        
        /**
         * Handle zoom change
         */
        handleZoomChange() {
            const zoomValue = this.zoomSelect.value;
            this.setZoom(zoomValue);
        }
        
        /**
         * Set zoom level
         * 
         * @param {number|string} zoom Zoom level
         */
        setZoom(zoom) {
            // Convert string to number if needed
            if (typeof zoom === 'string' && !isNaN(parseFloat(zoom))) {
                zoom = parseFloat(zoom);
            }
            
            this.currentZoom = zoom;
            this.zoomSelect.value = zoom;
            
            // Apply zoom
            if (this.mimeType.includes('epub') && this.rendition) {
                // For EPUB, we adjust the font size
                this.applyEpubStyles();
            } else if (this.mimeType.includes('pdf') && this.book) {
                // For PDF, we re-render the current page
                this.renderPdfPage(this.currentPage);
            }
            
            // Save preference
            this.savePreference('zoom', zoom);
        }
        
        /**
         * Set theme
         * 
         * @param {string} theme Theme name
         */
        setTheme(theme) {
            // Remove existing theme classes
            this.readerView.classList.remove('theme-light', 'theme-dark', 'theme-sepia');
            
            // Add new theme class
            this.readerView.classList.add(`theme-${theme}`);
            
            // Apply to EPUB if loaded
            if (this.mimeType.includes('epub') && this.rendition) {
                this.applyEpubStyles();
            }
            
            // Save preference
            this.savePreference('theme', theme);
        }
        
        /**
         * Update theme preview
         */
        updateThemePreview() {
            const theme = document.getElementById('theme-select').value;
            this.setTheme(theme);
        }
        
        /**
         * Show bookmark dialog
         */
        showBookmarkDialog() {
            // Reset form
            document.getElementById('bookmark-name').value = '';
            document.getElementById('bookmark-notes').value = '';
            
            // Show dialog
            this.bookmarkDialog.style.display = 'block';
        }
        
        /**
         * Show settings dialog
         */
        showSettingsDialog() {
            // Update form with current values
            document.getElementById('theme-select').value = this.preferences.theme || 'light';
            document.getElementById('font-size').value = this.preferences.fontSize || 100;
            document.getElementById('font-size-value').textContent = (this.preferences.fontSize || 100) + '%';
            document.getElementById('line-spacing').value = this.preferences.lineSpacing || 130;
            document.getElementById('line-spacing-value').textContent = (this.preferences.lineSpacing || 130) + '%';
            document.getElementById('margin-size').value = this.preferences.marginSize || 40;
            document.getElementById('margin-size-value').textContent = (this.preferences.marginSize || 40) + 'px';
            
            // Show dialog
            this.settingsDialog.style.display = 'block';
        }
        
        /**
         * Close all dialogs
         */
        closeDialogs() {
            this.bookmarkDialog.style.display = 'none';
            this.settingsDialog.style.display = 'none';
        }
        
        /**
         * Save bookmark
         */
        saveBookmark() {
            const name = document.getElementById('bookmark-name').value.trim();
            const notes = document.getElementById('bookmark-notes').value.trim();
            
            if (!name) {
                OC.Notification.showTemporary(t(this.appName, 'Please enter a bookmark name'));
                return;
            }
            
            let value = '';
            let type = '';
            
            // Get current position
            if (this.mimeType.includes('epub') && this.rendition) {
                value = this.rendition.currentLocation().start.cfi;
                type = 'epub-cfi';
            } else if (this.mimeType.includes('pdf')) {
                value = this.currentPage.toString();
                type = 'pdf-page';
            }
            
            // Save bookmark
            fetch(OC.generateUrl(`/apps/${this.appName}/api/files/${this.fileId}/bookmarks`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                },
                body: JSON.stringify({
                    name: name,
                    value: value,
                    type: type,
                    content: notes
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to save bookmark');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    OC.Notification.showTemporary(t(this.appName, 'Bookmark saved'));
                    this.closeDialogs();
                    this.loadBookmarks(); // Refresh bookmarks
                } else {
                    OC.Notification.showTemporary(data.error?.message || t(this.appName, 'Failed to save bookmark'));
                }
            })
            .catch(error => {
                console.error('Error saving bookmark:', error);
                OC.Notification.showTemporary(t(this.appName, 'Failed to save bookmark'));
            });
        }
        
        /**
         * Save settings
         */
        saveSettings() {
            const theme = document.getElementById('theme-select').value;
            const fontSize = parseInt(document.getElementById('font-size').value, 10);
            const lineSpacing = parseInt(document.getElementById('line-spacing').value, 10);
            const marginSize = parseInt(document.getElementById('margin-size').value, 10);
            
            // Apply settings
            this.setTheme(theme);
            
            // Save preferences
            this.savePreference('theme', theme);
            this.savePreference('fontSize', fontSize);
            this.savePreference('lineSpacing', lineSpacing);
            this.savePreference('marginSize', marginSize);
            
            // Apply to EPUB if loaded
            if (this.mimeType.includes('epub') && this.rendition) {
                this.applyEpubStyles();
            }
            
            // Close dialog
            this.closeDialogs();
            
            OC.Notification.showTemporary(t(this.appName, 'Settings saved'));
        }
        
        /**
         * Save reading position
         * 
         * @param {string|number} cfi Content Fragment Identifier or page number
         * @param {number} position Position in the document
         */
        savePosition(cfi, position) {
            this.savePreference('lastCfi', cfi);
            this.savePreference('lastPosition', position);
        }
        
        /**
         * Save preference
         * 
         * @param {string} name Preference name
         * @param {string|number|boolean} value Preference value
         */
        savePreference(name, value) {
            // Update local preferences
            this.preferences[name] = value;
            
            // Save to server
            fetch(OC.generateUrl(`/apps/${this.appName}/api/files/${this.fileId}/preferences/reader`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                },
                body: JSON.stringify({
                    name: name,
                    value: value.toString()
                })
            })
            .catch(error => {
                console.error('Error saving preference:', error);
            });
        }
        
        /**
         * Handle keyboard shortcuts
         * 
         * @param {KeyboardEvent} event Keyboard event
         */
        handleKeyDown(event) {
            // Ignore if dialogs are open
            if (this.bookmarkDialog.style.display === 'block' || this.settingsDialog.style.display === 'block') {
                return;
            }
            
            switch (event.key) {
                case 'ArrowLeft':
                    this.prevPage();
                    event.preventDefault();
                    break;
                case 'ArrowRight':
                    this.nextPage();
                    event.preventDefault();
                    break;
                case '+':
                    this.zoomIn();
                    event.preventDefault();
                    break;
                case '-':
                    this.zoomOut();
                    event.preventDefault();
                    break;
                case 'b':
                    this.showBookmarkDialog();
                    event.preventDefault();
                    break;
                case 's':
                    this.showSettingsDialog();
                    event.preventDefault();
                    break;
                case 'Escape':
                    this.closeDialogs();
                    event.preventDefault();
                    break;
            }
        }
        
        /**
         * Load script dynamically
         * 
         * @param {string} src Script source URL
         * @returns {Promise} Promise that resolves when the script is loaded
         */
        loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        
        /**
         * Set loading state
         * 
         * @param {boolean} isLoading Whether the reader is loading
         */
        setLoading(isLoading) {
            this.isLoading = isLoading;
            
            if (isLoading) {
                this.loadingIndicator.style.display = 'flex';
                this.readerView.style.display = 'none';
                this.errorContainer.style.display = 'none';
            } else {
                this.loadingIndicator.style.display = 'none';
            }
        }
        
        /**
         * Show error message
         * 
         * @param {string} message Error message
         */
        showError(message) {
            this.errorMessage.textContent = message;
            this.errorContainer.style.display = 'flex';
            this.readerView.style.display = 'none';
            this.loadingIndicator.style.display = 'none';
        }
    }

    // Initialize the reader when the DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        new EpubViewerReader();
    });

})(window, document, jQuery, OC); 