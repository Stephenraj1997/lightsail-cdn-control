/**
 * Admin JavaScript for Lightsail CDN Control
 *
 * @package Lightsail_CDN_Control
 * @since 1.0
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Toggle visibility of auto-clear post types row
         */
        const autoToggle = $('#lightsail_auto_clear_enabled');
        const postTypesRow = $('#post-types-row');
        
        if (autoToggle.length && postTypesRow.length) {
            autoToggle.on('change', function() {
                if (this.checked) {
                    postTypesRow.fadeIn(200);
                } else {
                    postTypesRow.fadeOut(200);
                }
            });
        }
        
        /**
         * Select All / Deselect All buttons for post types
         */
        $('#select-all-post-types').on('click', function(e) {
            e.preventDefault();
            $('.post-type-checkbox').prop('checked', true);
        });
        
        $('#deselect-all-post-types').on('click', function(e) {
            e.preventDefault();
            $('.post-type-checkbox').prop('checked', false);
        });
        
        /**
         * Toggle visibility of scheduled clear settings
         */
        const scheduleToggle = $('#lightsail_scheduled_clear_enabled');
        const scheduleSettingsRow = $('#schedule-settings-row');
        const scheduleTimeRow = $('#schedule-time-row');
        const scheduleDayRow = $('#schedule-day-row');
        const scheduleDateRow = $('#schedule-date-row');
        const frequencySelect = $('#lightsail_scheduled_clear_frequency');
        
        if (scheduleToggle.length) {
            scheduleToggle.on('change', function() {
                if (this.checked) {
                    scheduleSettingsRow.fadeIn(200);
                    scheduleTimeRow.fadeIn(200);
                    // Show day or date row based on frequency
                    updateFrequencyVisibility();
                } else {
                    scheduleSettingsRow.fadeOut(200);
                    scheduleTimeRow.fadeOut(200);
                    scheduleDayRow.fadeOut(200);
                    scheduleDateRow.fadeOut(200);
                }
            });
        }
        
        /**
         * Show/hide day or date selector based on frequency
         */
        function updateFrequencyVisibility() {
            const frequency = frequencySelect.val();
            const isEnabled = scheduleToggle.is(':checked');
            
            if (!isEnabled) {
                return;
            }
            
            if (frequency === 'weekly') {
                scheduleDayRow.fadeIn(200);
                scheduleDateRow.fadeOut(200);
            } else if (frequency === 'monthly') {
                scheduleDayRow.fadeOut(200);
                scheduleDateRow.fadeIn(200);
            } else {
                scheduleDayRow.fadeOut(200);
                scheduleDateRow.fadeOut(200);
            }
        }
        
        if (frequencySelect.length) {
            frequencySelect.on('change', updateFrequencyVisibility);
        }
        
        /**
         * Live countdown timer
         */
        const countdownEl = $('#countdown-timer');
        
        if (countdownEl.length && typeof lightsailCDN !== 'undefined' && lightsailCDN.nextRun > 0) {
            // Convert to milliseconds
            const nextRunTime = lightsailCDN.nextRun * 1000;
            let refreshingStartTime = null;
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = nextRunTime - now;
                
                // If countdown is over, show refreshing message
                if (distance < 0) {
                    // Start tracking when we first showed "Refreshing..."
                    if (refreshingStartTime === null) {
                        refreshingStartTime = now;
                    }
                    
                    const refreshingDuration = now - refreshingStartTime;
                    
                    // After 30 seconds of showing "Refreshing...", reload to get new schedule
                    if (refreshingDuration > 30000) {
                        location.reload();
                        return;
                    }
                    
                    countdownEl.html('Refreshing...');
                    return;
                }
                
                // Calculate time components
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Format with leading zeros for minutes and seconds
                const formattedMinutes = String(minutes).padStart(2, '0');
                const formattedSeconds = String(seconds).padStart(2, '0');
                
                // Build countdown string
                let countdownText = '';
                
                if (days > 0) {
                    countdownText += days + 'd ';
                }
                
                if (hours > 0 || days > 0) {
                    countdownText += hours + 'h ';
                }
                
                // Always show minutes and seconds in MM:SS format
                countdownText += formattedMinutes + ':' + formattedSeconds;
                
                countdownEl.html(countdownText);
            }
            
            // Update immediately
            updateCountdown();
            
            // Update every second
            setInterval(updateCountdown, 1000);
        }
        
    });
    
})(jQuery);
