/**
 * EPUB Viewer - Main Application Script
 */
(function(window, document, $, OC) {
    'use strict';

    /**
     * EPUB Viewer Library Class
     */
    class EpubViewerLibrary {
        /**
         * Constructor
         */
        constructor() {
            // Elements
            this.app = document.getElementById('epubviewer-app');
            this.fileList = document.getElementById('file-list');
            this.searchInput = document.getElementById('search-input');
            this.searchButton = document.getElementById('search-button');
            this.loadingIndicator = document.querySelector('.loading-indicator');
            this.fileListContainer = document.querySelector('.file-list-container');
            this.emptyState = document.querySelector('.empty-state');
            
            // Settings
            this.settings = window.EPUBVIEWER_SETTINGS || {};
            this.appName = this.app.dataset.appname || 'epubviewer';
            
            // State
            this.files = [];
            this.filteredFiles = [];
            this.isLoading = true;
            
            // Initialize
            this.init();
        }
        
        /**
         * Initialize the library
         */
        init() {
            this.bindEvents();
            this.loadFiles();
        }
        
        /**
         * Bind event listeners
         */
        bindEvents() {
            // Search functionality
            this.searchInput.addEventListener('input', this.handleSearch.bind(this));
            this.searchButton.addEventListener('click', this.handleSearch.bind(this));
            
            // Navigation toggle for mobile
            const navToggle = document.querySelector('.app-navigation-toggle');
            if (navToggle) {
                navToggle.addEventListener('click', this.toggleNavigation.bind(this));
            }
        }
        
        /**
         * Toggle navigation menu (mobile)
         */
        toggleNavigation() {
            const appNavigation = document.getElementById('app-navigation');
            if (appNavigation) {
                appNavigation.classList.toggle('hidden');
            }
        }
        
        /**
         * Load files from the server
         */
        loadFiles() {
            this.setLoading(true);
            
            // Make API request to get files
            fetch(OC.generateUrl(`/apps/${this.appName}/api/files`), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load files');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.files = data.data || [];
                    this.filteredFiles = [...this.files];
                    this.renderFiles();
                } else {
                    this.showError(data.error?.message || 'Failed to load files');
                }
            })
            .catch(error => {
                console.error('Error loading files:', error);
                this.showError('Failed to load files. Please try again later.');
            })
            .finally(() => {
                this.setLoading(false);
            });
        }
        
        /**
         * Render files in the file list
         */
        renderFiles() {
            // Clear existing content
            this.fileList.querySelector('tbody').innerHTML = '';
            
            if (this.filteredFiles.length === 0) {
                this.fileListContainer.style.display = 'none';
                this.emptyState.style.display = 'flex';
                return;
            }
            
            this.fileListContainer.style.display = 'block';
            this.emptyState.style.display = 'none';
            
            // Create and append file rows
            const tbody = this.fileList.querySelector('tbody');
            
            this.filteredFiles.forEach(file => {
                const row = document.createElement('tr');
                
                // File name column
                const nameCell = document.createElement('td');
                const nameLink = document.createElement('a');
                nameLink.href = OC.generateUrl(`/apps/${this.appName}/reader/${file.id}`);
                nameLink.textContent = file.name;
                nameLink.title = file.path;
                nameCell.appendChild(nameLink);
                row.appendChild(nameCell);
                
                // File size column
                const sizeCell = document.createElement('td');
                sizeCell.textContent = this.formatFileSize(file.size);
                row.appendChild(sizeCell);
                
                // Modified date column
                const modifiedCell = document.createElement('td');
                modifiedCell.textContent = this.formatDate(file.mtime);
                row.appendChild(modifiedCell);
                
                // Actions column
                const actionsCell = document.createElement('td');
                actionsCell.className = 'file-actions';
                
                // View action
                const viewButton = document.createElement('a');
                viewButton.href = OC.generateUrl(`/apps/${this.appName}/reader/${file.id}`);
                viewButton.className = 'button';
                viewButton.title = t(this.appName, 'Open');
                viewButton.innerHTML = '<span class="icon-view"></span>';
                actionsCell.appendChild(viewButton);
                
                // Download action
                const downloadButton = document.createElement('a');
                downloadButton.href = OC.generateUrl(`/apps/${this.appName}/api/files/${file.id}/download`);
                downloadButton.className = 'button';
                downloadButton.title = t(this.appName, 'Download');
                downloadButton.innerHTML = '<span class="icon-download"></span>';
                actionsCell.appendChild(downloadButton);
                
                row.appendChild(actionsCell);
                
                tbody.appendChild(row);
            });
        }
        
        /**
         * Handle search input
         */
        handleSearch() {
            const searchTerm = this.searchInput.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                this.filteredFiles = [...this.files];
            } else {
                this.filteredFiles = this.files.filter(file => 
                    file.name.toLowerCase().includes(searchTerm) || 
                    file.path.toLowerCase().includes(searchTerm)
                );
            }
            
            this.renderFiles();
        }
        
        /**
         * Set loading state
         * 
         * @param {boolean} isLoading Whether the app is loading
         */
        setLoading(isLoading) {
            this.isLoading = isLoading;
            
            if (isLoading) {
                this.loadingIndicator.style.display = 'flex';
                this.fileListContainer.style.display = 'none';
                this.emptyState.style.display = 'none';
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
            const errorElement = document.querySelector('.empty-state h3');
            const errorDescription = document.querySelector('.empty-state p');
            
            if (errorElement && errorDescription) {
                errorElement.textContent = t(this.appName, 'Error loading files');
                errorDescription.textContent = message;
                this.emptyState.style.display = 'flex';
                this.fileListContainer.style.display = 'none';
            } else {
                OC.Notification.showTemporary(message);
            }
        }
        
        /**
         * Format file size for display
         * 
         * @param {number} bytes File size in bytes
         * @returns {string} Formatted file size
         */
        formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            
            return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + units[i];
        }
        
        /**
         * Format date for display
         * 
         * @param {number|string} timestamp Timestamp or date string
         * @returns {string} Formatted date
         */
        formatDate(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
    }

    // Initialize the library when the DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        new EpubViewerLibrary();
    });

})(window, document, jQuery, OC); 