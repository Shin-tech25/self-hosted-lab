/**
 * EPUB Viewer - Utility Functions
 */
(function(window, OC) {
    'use strict';

    /**
     * EPUB Viewer Utilities
     */
    window.EpubViewerUtils = {
        /**
         * Format file size to human-readable string
         * 
         * @param {number} bytes File size in bytes
         * @param {number} decimals Number of decimal places
         * @returns {string} Formatted file size
         */
        formatFileSize: function(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        },
        
        /**
         * Format date to human-readable string
         * 
         * @param {string|Date} date Date to format
         * @returns {string} Formatted date
         */
        formatDate: function(date) {
            if (!date) return '';
            
            const dateObj = new Date(date);
            
            // Check if date is valid
            if (isNaN(dateObj.getTime())) {
                return '';
            }
            
            // Format date
            return dateObj.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        /**
         * Get file extension from filename
         * 
         * @param {string} filename Filename
         * @returns {string} File extension
         */
        getFileExtension: function(filename) {
            if (!filename) return '';
            
            return filename.split('.').pop().toLowerCase();
        },
        
        /**
         * Check if file is supported
         * 
         * @param {string} mimeType File MIME type
         * @returns {boolean} Whether the file is supported
         */
        isSupportedFile: function(mimeType) {
            const supportedTypes = [
                'application/epub+zip',
                'application/pdf',
                'application/x-cbr',
                'application/x-cbz',
                'application/vnd.comicbook+zip',
                'application/vnd.comicbook-rar'
            ];
            
            return supportedTypes.includes(mimeType) || 
                   mimeType.includes('epub') || 
                   mimeType.includes('pdf') || 
                   mimeType.includes('comic');
        },
        
        /**
         * Get file icon based on MIME type
         * 
         * @param {string} mimeType File MIME type
         * @returns {string} Icon class
         */
        getFileIcon: function(mimeType) {
            if (mimeType.includes('epub')) {
                return 'icon-book';
            } else if (mimeType.includes('pdf')) {
                return 'icon-file-pdf';
            } else if (mimeType.includes('comic') || mimeType.includes('cbz') || mimeType.includes('cbr')) {
                return 'icon-picture';
            } else {
                return 'icon-file';
            }
        },
        
        /**
         * Escape HTML special characters
         * 
         * @param {string} text Text to escape
         * @returns {string} Escaped text
         */
        escapeHTML: function(text) {
            if (!text) return '';
            
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * Truncate text to specified length
         * 
         * @param {string} text Text to truncate
         * @param {number} length Maximum length
         * @param {string} suffix Suffix to add if truncated
         * @returns {string} Truncated text
         */
        truncateText: function(text, length = 30, suffix = '...') {
            if (!text) return '';
            
            if (text.length <= length) {
                return text;
            }
            
            return text.substring(0, length).trim() + suffix;
        },
        
        /**
         * Convert camelCase to snake_case
         * 
         * @param {string} str String to convert
         * @returns {string} Converted string
         */
        toSnakeCase: function(str) {
            return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
        },
        
        /**
         * Convert snake_case to camelCase
         * 
         * @param {string} str String to convert
         * @returns {string} Converted string
         */
        toCamelCase: function(str) {
            return str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
        },
        
        /**
         * Parse value to appropriate type
         * 
         * @param {string} value Value to parse
         * @returns {string|number|boolean} Parsed value
         */
        parseValue: function(value) {
            if (value === 'true') {
                return true;
            } else if (value === 'false') {
                return false;
            } else if (!isNaN(parseFloat(value)) && isFinite(value)) {
                return parseFloat(value);
            } else {
                return value;
            }
        },
        
        /**
         * Generate a unique ID
         * 
         * @returns {string} Unique ID
         */
        generateUniqueId: function() {
            return 'id_' + Math.random().toString(36).substr(2, 9);
        },
        
        /**
         * Show notification
         * 
         * @param {string} message Notification message
         * @param {string} type Notification type (success, error, warning, info)
         * @param {number} timeout Timeout in milliseconds
         */
        showNotification: function(message, type = 'info', timeout = 5000) {
            if (typeof OC !== 'undefined' && OC.Notification) {
                OC.Notification.showTemporary(message);
            } else {
                // Fallback if OC.Notification is not available
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                // Remove notification after timeout
                setTimeout(() => {
                    notification.classList.add('fadeout');
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, timeout);
            }
        },
        
        /**
         * Debounce function
         * 
         * @param {Function} func Function to debounce
         * @param {number} wait Wait time in milliseconds
         * @returns {Function} Debounced function
         */
        debounce: function(func, wait = 300) {
            let timeout;
            
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        /**
         * Throttle function
         * 
         * @param {Function} func Function to throttle
         * @param {number} limit Limit in milliseconds
         * @returns {Function} Throttled function
         */
        throttle: function(func, limit = 300) {
            let inThrottle;
            
            return function executedFunction(...args) {
                if (!inThrottle) {
                    func(...args);
                    inThrottle = true;
                    setTimeout(() => {
                        inThrottle = false;
                    }, limit);
                }
            };
        },
        
        /**
         * Get URL parameter
         * 
         * @param {string} name Parameter name
         * @returns {string|null} Parameter value
         */
        getUrlParameter: function(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
        },
        
        /**
         * Check if device is mobile
         * 
         * @returns {boolean} Whether the device is mobile
         */
        isMobileDevice: function() {
            return window.innerWidth <= 768;
        },
        
        /**
         * Check if device is touch-enabled
         * 
         * @returns {boolean} Whether the device is touch-enabled
         */
        isTouchDevice: function() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
        }
    };

})(window, OC); 