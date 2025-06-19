/**
 * EPUB Viewer - Bookmarks Manager
 */
(function(window, document, $, OC) {
    'use strict';

    /**
     * EPUB Viewer Bookmarks Manager
     */
    class EpubViewerBookmarks {
        /**
         * Constructor
         * 
         * @param {Object} options Configuration options
         */
        constructor(options = {}) {
            // Options
            this.options = Object.assign({
                appName: 'epubviewer',
                fileId: null,
                container: '#bookmarks-container',
                template: '#bookmark-template',
                emptyMessage: '#no-bookmarks-message',
                deleteAllButton: '#delete-all-bookmarks',
                onBookmarkClick: null,
                onBookmarkDelete: null,
                onAllBookmarksDeleted: null
            }, options);
            
            // Elements
            this.container = document.querySelector(this.options.container);
            this.template = document.querySelector(this.options.template);
            this.emptyMessage = document.querySelector(this.options.emptyMessage);
            this.deleteAllButton = document.querySelector(this.options.deleteAllButton);
            
            // State
            this.bookmarks = [];
            this.isLoading = false;
            
            // Initialize
            if (this.container && this.options.fileId) {
                this.init();
            }
        }
        
        /**
         * Initialize the bookmarks manager
         */
        init() {
            this.bindEvents();
            this.loadBookmarks();
        }
        
        /**
         * Bind event listeners
         */
        bindEvents() {
            // Delete all bookmarks
            if (this.deleteAllButton) {
                this.deleteAllButton.addEventListener('click', this.handleDeleteAllBookmarks.bind(this));
            }
            
            // Delegate event for bookmark actions
            if (this.container) {
                this.container.addEventListener('click', this.handleBookmarkAction.bind(this));
            }
        }
        
        /**
         * Load bookmarks from server
         */
        loadBookmarks() {
            if (this.isLoading || !this.options.fileId) return;
            
            this.isLoading = true;
            this.showLoading();
            
            fetch(OC.generateUrl(`/apps/${this.options.appName}/api/files/${this.options.fileId}/bookmarks`), {
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
                this.isLoading = false;
                
                if (data.success) {
                    this.bookmarks = data.data || [];
                    this.renderBookmarks();
                } else {
                    this.showError(data.error?.message || 'Failed to load bookmarks');
                }
            })
            .catch(error => {
                this.isLoading = false;
                console.error('Error loading bookmarks:', error);
                this.showError('Failed to load bookmarks from server');
            });
        }
        
        /**
         * Render bookmarks
         */
        renderBookmarks() {
            if (!this.container) return;
            
            // Clear container
            this.container.innerHTML = '';
            
            // Show empty message if no bookmarks
            if (this.bookmarks.length === 0) {
                if (this.emptyMessage) {
                    this.emptyMessage.style.display = 'block';
                }
                if (this.deleteAllButton) {
                    this.deleteAllButton.style.display = 'none';
                }
                return;
            }
            
            // Hide empty message
            if (this.emptyMessage) {
                this.emptyMessage.style.display = 'none';
            }
            if (this.deleteAllButton) {
                this.deleteAllButton.style.display = 'block';
            }
            
            // Render each bookmark
            this.bookmarks.forEach(bookmark => {
                const bookmarkElement = this.createBookmarkElement(bookmark);
                this.container.appendChild(bookmarkElement);
            });
        }
        
        /**
         * Create bookmark element
         * 
         * @param {Object} bookmark Bookmark data
         * @returns {HTMLElement} Bookmark element
         */
        createBookmarkElement(bookmark) {
            let element;
            
            // Use template if available
            if (this.template && this.template.content) {
                element = document.importNode(this.template.content, true).firstElementChild;
            } else {
                // Create element manually
                element = document.createElement('div');
                element.className = 'bookmark-item';
                element.innerHTML = `
                    <div class="bookmark-content">
                        <div class="bookmark-title">${EpubViewerUtils.escapeHTML(bookmark.name)}</div>
                        <div class="bookmark-date">${EpubViewerUtils.formatDate(bookmark.created)}</div>
                        ${bookmark.content ? `<div class="bookmark-notes">${EpubViewerUtils.escapeHTML(bookmark.content)}</div>` : ''}
                    </div>
                    <div class="bookmark-actions">
                        <button class="bookmark-goto" data-id="${bookmark.id}" data-value="${bookmark.value}" data-type="${bookmark.type}">
                            <span class="icon icon-play"></span>
                        </button>
                        <button class="bookmark-delete" data-id="${bookmark.id}">
                            <span class="icon icon-delete"></span>
                        </button>
                    </div>
                `;
            }
            
            // Set data attributes
            element.dataset.id = bookmark.id;
            element.dataset.value = bookmark.value;
            element.dataset.type = bookmark.type;
            
            // Fill template if used
            if (this.template) {
                element.querySelector('.bookmark-title').textContent = bookmark.name;
                element.querySelector('.bookmark-date').textContent = EpubViewerUtils.formatDate(bookmark.created);
                
                const notesElement = element.querySelector('.bookmark-notes');
                if (notesElement && bookmark.content) {
                    notesElement.textContent = bookmark.content;
                    notesElement.style.display = 'block';
                } else if (notesElement) {
                    notesElement.style.display = 'none';
                }
            }
            
            return element;
        }
        
        /**
         * Handle bookmark action
         * 
         * @param {Event} event Click event
         */
        handleBookmarkAction(event) {
            const target = event.target.closest('.bookmark-goto, .bookmark-delete');
            if (!target) return;
            
            const bookmarkId = parseInt(target.dataset.id, 10);
            const bookmark = this.bookmarks.find(b => b.id === bookmarkId);
            
            if (!bookmark) return;
            
            if (target.classList.contains('bookmark-goto')) {
                // Go to bookmark
                if (typeof this.options.onBookmarkClick === 'function') {
                    this.options.onBookmarkClick(bookmark);
                }
            } else if (target.classList.contains('bookmark-delete')) {
                // Delete bookmark
                this.deleteBookmark(bookmarkId);
            }
        }
        
        /**
         * Delete bookmark
         * 
         * @param {number} bookmarkId Bookmark ID
         */
        deleteBookmark(bookmarkId) {
            if (!confirm(t(this.options.appName, 'Are you sure you want to delete this bookmark?'))) {
                return;
            }
            
            fetch(OC.generateUrl(`/apps/${this.options.appName}/api/files/${this.options.fileId}/bookmarks/${bookmarkId}`), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to delete bookmark');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove bookmark from array
                    this.bookmarks = this.bookmarks.filter(b => b.id !== bookmarkId);
                    
                    // Remove bookmark element
                    const element = this.container.querySelector(`[data-id="${bookmarkId}"]`);
                    if (element) {
                        element.remove();
                    }
                    
                    // Show empty message if no bookmarks
                    if (this.bookmarks.length === 0) {
                        if (this.emptyMessage) {
                            this.emptyMessage.style.display = 'block';
                        }
                        if (this.deleteAllButton) {
                            this.deleteAllButton.style.display = 'none';
                        }
                    }
                    
                    // Callback
                    if (typeof this.options.onBookmarkDelete === 'function') {
                        this.options.onBookmarkDelete(bookmarkId);
                    }
                    
                    // Show success message
                    EpubViewerUtils.showNotification(t(this.options.appName, 'Bookmark deleted'), 'success');
                } else {
                    this.showError(data.error?.message || 'Failed to delete bookmark');
                }
            })
            .catch(error => {
                console.error('Error deleting bookmark:', error);
                this.showError('Failed to delete bookmark');
            });
        }
        
        /**
         * Handle delete all bookmarks
         */
        handleDeleteAllBookmarks() {
            if (!confirm(t(this.options.appName, 'Are you sure you want to delete all bookmarks?'))) {
                return;
            }
            
            fetch(OC.generateUrl(`/apps/${this.options.appName}/api/files/${this.options.fileId}/bookmarks`), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to delete all bookmarks');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Clear bookmarks
                    this.bookmarks = [];
                    
                    // Clear container
                    this.container.innerHTML = '';
                    
                    // Show empty message
                    if (this.emptyMessage) {
                        this.emptyMessage.style.display = 'block';
                    }
                    if (this.deleteAllButton) {
                        this.deleteAllButton.style.display = 'none';
                    }
                    
                    // Callback
                    if (typeof this.options.onAllBookmarksDeleted === 'function') {
                        this.options.onAllBookmarksDeleted();
                    }
                    
                    // Show success message
                    EpubViewerUtils.showNotification(t(this.options.appName, 'All bookmarks deleted'), 'success');
                } else {
                    this.showError(data.error?.message || 'Failed to delete all bookmarks');
                }
            })
            .catch(error => {
                console.error('Error deleting all bookmarks:', error);
                this.showError('Failed to delete all bookmarks');
            });
        }
        
        /**
         * Add bookmark
         * 
         * @param {Object} bookmark Bookmark data
         */
        addBookmark(bookmark) {
            // Add to array
            this.bookmarks.push(bookmark);
            
            // Add to DOM
            const bookmarkElement = this.createBookmarkElement(bookmark);
            this.container.appendChild(bookmarkElement);
            
            // Hide empty message
            if (this.emptyMessage) {
                this.emptyMessage.style.display = 'none';
            }
            if (this.deleteAllButton) {
                this.deleteAllButton.style.display = 'block';
            }
        }
        
        /**
         * Show loading indicator
         */
        showLoading() {
            // Clear container
            this.container.innerHTML = '';
            
            // Create loading indicator
            const loading = document.createElement('div');
            loading.className = 'loading-indicator';
            loading.innerHTML = '<div class="spinner"></div><span>Loading bookmarks...</span>';
            
            this.container.appendChild(loading);
        }
        
        /**
         * Show error message
         * 
         * @param {string} message Error message
         */
        showError(message) {
            // Show notification
            EpubViewerUtils.showNotification(message, 'error');
            
            // Clear container
            this.container.innerHTML = '';
            
            // Create error message
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = message;
            
            this.container.appendChild(error);
        }
    }

    // Export class
    window.EpubViewerBookmarks = EpubViewerBookmarks;

})(window, document, jQuery, OC); 