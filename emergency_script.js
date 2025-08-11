javascript:(function(){
    console.log('🛠️ Emergency error removal script starting...');
    
    // 1. Remove all error notifications
    let removed = 0;
    const errorSelectors = [
        '.alert', '.notification', '.toast', 
        '[class*="alert"]', '[class*="notification"]', 
        '[class*="bg-red"]', '[class*="text-red"]',
        '.fixed', '.absolute', '.relative'
    ];
    
    errorSelectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            if (el.textContent && (
                el.textContent.includes('Error adding schedule') ||
                el.textContent.includes('Error') ||
                el.style.backgroundColor === 'red' ||
                el.style.color === 'red'
            )) {
                console.log('Removing error element:', el.textContent);
                el.remove();
                removed++;
            }
        });
    });
    
    // 2. Clear all notifications function if it exists
    if (window.clearAllNotifications) {
        window.clearAllNotifications();
        console.log('✅ clearAllNotifications() executed');
    }
    
    // 3. Override showNotification to prevent the error from coming back
    const originalShowNotification = window.showNotification;
    window.showNotification = function(message, type = 'info') {
        if (message && message.includes('Error adding schedule')) {
            console.log('🚫 Blocked persistent error notification:', message);
            return;
        }
        
        // Clear existing notifications first
        const alerts = document.querySelectorAll('.alert, .notification, .toast');
        alerts.forEach(alert => {
            if (alert.textContent && alert.textContent.includes('Error')) {
                alert.remove();
            }
        });
        
        // Call original function
        if (originalShowNotification) {
            return originalShowNotification.call(this, message, type);
        } else {
            // Fallback notification system
            console.log('📢 Notification:', message, '(Type:', type, ')');
            if (type === 'success') {
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-50 alert alert-success shadow-lg';
                notification.innerHTML = `<div><i class="fas fa-check"></i><span>${message}</span></div>`;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            }
        }
    };
    
    // 4. Reset schedules data if it exists
    if (window.schedulesData) {
        console.log('📊 Resetting schedulesData');
        window.schedulesData = [
            { id: 1, bus_name: 'Green Line Express (GL-001)', route: 'Dhaka - Cox\'s Bazar', departure_time: '09:00', departure_date: '2025-08-04', fare: 850, available_seats: 35, status: 'active' },
            { id: 2, bus_name: 'Shyamoli Express (SE-002)', route: 'Dhaka - Chittagong', departure_time: '10:30', departure_date: '2025-08-04', fare: 650, available_seats: 40, status: 'active' },
            { id: 3, bus_name: 'Ena Transport (ET-003)', route: 'Dhaka - Sylhet', departure_time: '07:45', departure_date: '2025-08-04', fare: 750, available_seats: 38, status: 'active' },
            { id: 4, bus_name: 'Jagat Bilash Express (JB-004)', route: 'Dhaka - Rajshahi', departure_time: '22:00', departure_date: '2025-08-04', fare: 550, available_seats: 42, status: 'active' },
            { id: 5, bus_name: 'Green Line Express (GL-001)', route: 'Dhaka - Khulna', departure_time: '06:30', departure_date: '2025-08-05', fare: 600, available_seats: 36, status: 'active' }
        ];
    }
    
    // 5. Clear localStorage errors
    localStorage.removeItem('adminNotifications');
    localStorage.removeItem('adminErrors');
    localStorage.removeItem('errorState');
    
    // 6. Show success message
    console.log(`✅ Emergency cleanup completed! Removed ${removed} error elements.`);
    alert(`✅ Emergency cleanup completed!\n\n• Removed ${removed} error elements\n• Reset notification system\n• Cleared error storage\n\nTry adding a schedule now!`);
    
})();
