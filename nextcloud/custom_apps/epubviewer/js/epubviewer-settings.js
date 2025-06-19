/**
 * EPUB Viewer - Settings Application Script
 */
(function(window, document, $, OC) {
    'use strict';

    /**
     * EPUB Viewer Settings Class
     */
    class EpubViewerSettings {
        /**
         * Constructor
         */
        constructor() {
            // Elements
            this.settingsContainer = document.getElementById('epubviewer-settings');
            this.settingsForm = document.getElementById('settings-form');
            this.resetButton = document.getElementById('reset-settings');
            this.saveButton = document.getElementById('save-settings');
            this.statusMessage = document.getElementById('status-message');
            
            // Settings
            this.appName = this.settingsContainer.dataset.appname || 'epubviewer';
            this.settings = {};
            this.defaultSettings = {};
            
            // Initialize
            this.init();
        }
        
        /**
         * Initialize the settings
         */
        init() {
            this.bindEvents();
            this.loadSettings();
        }
        
        /**
         * Bind event listeners
         */
        bindEvents() {
            // Save settings
            this.saveButton.addEventListener('click', this.saveSettings.bind(this));
            
            // Reset settings
            this.resetButton.addEventListener('click', this.resetSettings.bind(this));
            
            // Toggle switches
            document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', this.updateToggleState.bind(this));
            });
            
            // Range sliders
            document.querySelectorAll('input[type="range"]').forEach(slider => {
                const valueDisplay = document.getElementById(`${slider.id}-value`);
                if (valueDisplay) {
                    slider.addEventListener('input', () => {
                        let suffix = '';
                        
                        // Add appropriate suffix based on setting type
                        if (slider.id.includes('zoom')) {
                            suffix = 'x';
                        } else if (slider.id.includes('size')) {
                            suffix = 'px';
                        } else {
                            suffix = '%';
                        }
                        
                        valueDisplay.textContent = slider.value + suffix;
                    });
                }
            });
        }
        
        /**
         * Load settings from server
         */
        loadSettings() {
            fetch(OC.generateUrl(`/apps/${this.appName}/api/settings`), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load settings');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.settings = data.data || {};
                    this.defaultSettings = data.defaults || {};
                    this.populateForm();
                } else {
                    this.showStatus(data.error?.message || 'Failed to load settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
                this.showStatus('Failed to load settings from server', 'error');
            });
        }
        
        /**
         * Populate form with current settings
         */
        populateForm() {
            // Process each form element
            this.settingsForm.querySelectorAll('[name]').forEach(element => {
                const name = element.name;
                const value = this.settings[name] !== undefined ? this.settings[name] : this.defaultSettings[name];
                
                if (value === undefined) {
                    return;
                }
                
                // Set value based on element type
                if (element.type === 'checkbox') {
                    element.checked = this.parseValue(value);
                    this.updateToggleState({ target: element });
                } else if (element.type === 'range') {
                    element.value = this.parseValue(value);
                    
                    // Update value display
                    const valueDisplay = document.getElementById(`${element.id}-value`);
                    if (valueDisplay) {
                        let suffix = '';
                        
                        // Add appropriate suffix based on setting type
                        if (element.id.includes('zoom')) {
                            suffix = 'x';
                        } else if (element.id.includes('size')) {
                            suffix = 'px';
                        } else {
                            suffix = '%';
                        }
                        
                        valueDisplay.textContent = element.value + suffix;
                    }
                } else if (element.tagName === 'SELECT') {
                    element.value = value;
                } else {
                    element.value = value;
                }
            });
        }
        
        /**
         * Save settings to server
         */
        saveSettings() {
            // Disable save button
            this.saveButton.disabled = true;
            
            // Collect form data
            const formData = {};
            this.settingsForm.querySelectorAll('[name]').forEach(element => {
                const name = element.name;
                let value;
                
                if (element.type === 'checkbox') {
                    value = element.checked;
                } else {
                    value = element.value;
                }
                
                formData[name] = value;
            });
            
            // Save each setting individually
            const savePromises = Object.entries(formData).map(([key, value]) => {
                return this.saveSetting(key, value);
            });
            
            // Wait for all settings to be saved
            Promise.all(savePromises)
                .then(results => {
                    // Check if all settings were saved successfully
                    const allSuccess = results.every(result => result);
                    
                    if (allSuccess) {
                        this.showStatus('Settings saved successfully', 'success');
                    } else {
                        this.showStatus('Some settings could not be saved', 'error');
                    }
                    
                    // Re-enable save button
                    this.saveButton.disabled = false;
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    this.showStatus('Failed to save settings', 'error');
                    this.saveButton.disabled = false;
                });
        }
        
        /**
         * Save a single setting
         * 
         * @param {string} key Setting key
         * @param {string|number|boolean} value Setting value
         * @returns {Promise<boolean>} Promise that resolves to true if the setting was saved successfully
         */
        saveSetting(key, value) {
            return fetch(OC.generateUrl(`/apps/${this.appName}/api/settings`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                },
                body: JSON.stringify({
                    key: key,
                    value: value.toString()
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Failed to save setting: ${key}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update local settings
                    this.settings[key] = value;
                    return true;
                } else {
                    console.error(`Failed to save setting ${key}:`, data.error?.message);
                    return false;
                }
            })
            .catch(error => {
                console.error(`Error saving setting ${key}:`, error);
                return false;
            });
        }
        
        /**
         * Reset settings to defaults
         */
        resetSettings() {
            if (!confirm(t(this.appName, 'Are you sure you want to reset all settings to default values?'))) {
                return;
            }
            
            // Disable reset button
            this.resetButton.disabled = true;
            
            fetch(OC.generateUrl(`/apps/${this.appName}/api/settings`), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to reset settings');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.showStatus('Settings reset to defaults', 'success');
                    this.settings = {};
                    this.loadSettings(); // Reload settings from server
                } else {
                    this.showStatus(data.error?.message || 'Failed to reset settings', 'error');
                }
                
                // Re-enable reset button
                this.resetButton.disabled = false;
            })
            .catch(error => {
                console.error('Error resetting settings:', error);
                this.showStatus('Failed to reset settings', 'error');
                this.resetButton.disabled = false;
            });
        }
        
        /**
         * Update toggle switch state
         * 
         * @param {Event} event Change event
         */
        updateToggleState(event) {
            const checkbox = event.target;
            const toggleSwitch = checkbox.closest('.toggle-switch');
            
            if (toggleSwitch) {
                if (checkbox.checked) {
                    toggleSwitch.classList.add('active');
                } else {
                    toggleSwitch.classList.remove('active');
                }
            }
        }
        
        /**
         * Show status message
         * 
         * @param {string} message Status message
         * @param {string} type Message type (success or error)
         */
        showStatus(message, type = 'success') {
            this.statusMessage.textContent = message;
            this.statusMessage.className = `status-message ${type}`;
            this.statusMessage.style.display = 'block';
            
            // Hide message after 5 seconds
            setTimeout(() => {
                this.statusMessage.style.display = 'none';
            }, 5000);
        }
        
        /**
         * Parse value to appropriate type
         * 
         * @param {string|number|boolean} value Value to parse
         * @returns {string|number|boolean} Parsed value
         */
        parseValue(value) {
            if (value === 'true') {
                return true;
            } else if (value === 'false') {
                return false;
            } else if (!isNaN(parseFloat(value)) && isFinite(value)) {
                return parseFloat(value);
            } else {
                return value;
            }
        }
    }

    // Initialize the settings when the DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        new EpubViewerSettings();
    });

})(window, document, jQuery, OC); 