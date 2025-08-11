// Quick test to verify schedule addition is working
console.log("🔧 Testing schedule addition functionality...");

// Check if we're in the admin panel
if (window.location.pathname.includes('admin')) {
    console.log("✅ In admin panel");
    
    // Check if schedulesData exists
    if (typeof schedulesData !== 'undefined') {
        console.log("✅ schedulesData found:", schedulesData.length, "schedules");
        console.log("Current schedules:", schedulesData);
    } else {
        console.log("❌ schedulesData not found");
    }
    
    // Check if functions exist
    console.log("addSchedule function exists:", typeof addSchedule === 'function');
    console.log("updateSchedulesTable function exists:", typeof updateSchedulesTable === 'function');
    
    // Test manual schedule addition
    if (typeof schedulesData !== 'undefined' && typeof updateSchedulesTable === 'function') {
        console.log("🧪 Testing manual schedule addition...");
        
        const testSchedule = {
            id: 100,
            bus_name: 'TEST BUS (Manual)',
            route: 'TEST ROUTE',
            departure_time: '15:00',
            departure_date: '2025-08-12',
            fare: 999,
            available_seats: 50,
            status: 'active'
        };
        
        console.log("Adding test schedule:", testSchedule);
        schedulesData.push(testSchedule);
        console.log("New schedulesData length:", schedulesData.length);
        
        updateSchedulesTable(schedulesData);
        console.log("✅ Table updated with test schedule");
        
        // Check if it appeared
        setTimeout(() => {
            const table = document.getElementById('schedulesTable');
            if (table) {
                const rows = table.querySelectorAll('tr');
                console.log("Current table rows:", rows.length);
                
                let found = false;
                rows.forEach(row => {
                    if (row.textContent.includes('TEST BUS')) {
                        found = true;
                    }
                });
                
                if (found) {
                    console.log("✅ TEST schedule found in table!");
                } else {
                    console.log("❌ TEST schedule NOT found in table");
                }
            }
        }, 100);
    }
    
} else {
    console.log("❌ Not in admin panel");
}
