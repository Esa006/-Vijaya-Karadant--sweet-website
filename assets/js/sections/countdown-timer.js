/**
 * Sweets Website
 * =============================================================
 * File: assets/js/sections/countdown-timer.js
 * Description: Self-correcting logic for the Festival Countdown Timer
 * =============================================================
 */

(function() {
    'use strict';

    function initCountdown() {
        // Set a target date (e.g., 5 days from now for demo purposes)
        // In a real app, this would come from a database or config
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 5);
        targetDate.setHours(targetDate.getHours() + 15);
        targetDate.setMinutes(targetDate.getMinutes() + 40);

        const daysVal = document.getElementById('timer-days');
        const hoursVal = document.getElementById('timer-hours');
        const minsVal = document.getElementById('timer-mins');
        const secsVal = document.getElementById('timer-secs');

        if (!daysVal || !hoursVal || !minsVal || !secsVal) return;

        function updateTimer() {
            const now = new Date().getTime();
            const distance = targetDate.getTime() - now;

            if (distance < 0) {
                // Timer expired
                document.querySelector('.c-countdown__timer').innerHTML = "OFFER EXPIRED";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            daysVal.innerText = days;
            hoursVal.innerText = hours.toString().padStart(2, '0');
            minsVal.innerText = minutes.toString().padStart(2, '0');
            secsVal.innerText = seconds.toString().padStart(2, '0');
        }

        // Initial call
        updateTimer();
        
        // Update every second
        setInterval(updateTimer, 1000);
    }

    document.addEventListener('DOMContentLoaded', initCountdown);
})();
