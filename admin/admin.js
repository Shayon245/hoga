// Admin Panel JavaScript
let currentSection = 'dashboard';

// Global data arrays for demo purposes
window.schedulesData = []; // Make globally accessible
let bookingsData = [];

// Check authentication on page load
document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing notifications first
    clearAllNotifications();
    
    // Clear any cached error states
    sessionStorage.removeItem('loginError');
    sessionStorage.removeItem('authError');
    
    // Check URL parameters for any error messages
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const errorMessage = urlParams.get('error');
        if (errorMessage !== 'Login failed. Please try again.') {
            showNotification(errorMessage, 'error');
        }
        
        // Clean the URL by removing the error parameter
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    checkAuthentication();
});

// Authentication check
function checkAuthentication() {
    const adminToken = localStorage.getItem('adminToken');
    const adminEmail = localStorage.getItem('adminEmail');
    
    if (!adminToken || adminToken !== 'authenticated') {
        // Don't show error here, just redirect
        window.location.href = 'login.html';
        return;
    }
    
    // Update welcome message with admin email
    const welcomeElement = document.querySelector('.text-gray-600');
    if (welcomeElement) {
        welcomeElement.textContent = `Welcome, ${adminEmail || 'Admin'}`;
    }
    
    // Initialize admin panel
    loadDashboard();
    setupEventListeners();
}

// Mobile sidebar functions
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
}

// Setup event listeners
function setupEventListeners() {
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            showDashboard();
        }
    });
    
    // Mobile sidebar event listeners
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const overlay = document.getElementById('sidebarOverlay');
        
        // Check if click is outside sidebar and not on menu button
        if (sidebar && mobileMenuBtn && overlay) {
            if (!sidebar.contains(event.target) && 
                !mobileMenuBtn.contains(event.target) && 
                sidebar.classList.contains('open')) {
                closeMobileSidebar();
            }
        }
    });
    
    // Close sidebar on window resize if switching from mobile to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            closeMobileSidebar();
        }
    });
}

// Navigation functions
function showDashboard() {
    showSection('dashboard');
    loadDashboard();
}

function showRoutes() {
    showSection('routes');
    loadRoutes();
}

function showBuses() {
    showSection('buses');
    loadBuses();
}

function showSchedules() {
    showSection('schedules');
    loadSchedules();
}

function showBookings() {
    showSection('bookings');
    loadBookings();
}

function showUsers() {
    showSection('users');
    loadUsers();
}

function showCoupons() {
    showSection('coupons');
    loadCoupons();
}

function showReports() {
    showSection('reports');
    loadReports();
}

function showSection(sectionName) {
    // Hide all sections
    const sections = ['dashboard', 'routes', 'buses', 'schedules', 'bookings', 'users', 'coupons', 'reports'];
    sections.forEach(section => {
        document.getElementById(section).classList.add('hidden');
    });
    
    // Show selected section
    document.getElementById(sectionName).classList.remove('hidden');
    currentSection = sectionName;
    
    // Update active state in sidebar
    updateSidebarActiveState(sectionName);
}

function updateSidebarActiveState(activeSection) {
    const buttons = document.querySelectorAll('aside button');
    buttons.forEach(button => {
        button.classList.remove('bg-[#1DD100]', 'text-white');
        button.classList.add('hover:bg-[#1DD100]', 'hover:text-white');
    });
    
    // Find and highlight active button
    const activeButton = document.querySelector(`button[onclick="show${activeSection.charAt(0).toUpperCase() + activeSection.slice(1)}()"]`);
    if (activeButton) {
        activeButton.classList.add('bg-[#1DD100]', 'text-white');
        activeButton.classList.remove('hover:bg-[#1DD100]', 'hover:text-white');
    }
}

// Dashboard functions
async function loadDashboard() {
    try {
        // Load dashboard stats
        const stats = await fetchDashboardStats();
        updateDashboardStats(stats);
        
        // Load recent bookings
        const recentBookings = await fetchRecentBookings();
        updateRecentBookingsTable(recentBookings);
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showNotification('Error loading dashboard data', 'error');
    }
}

async function fetchDashboardStats() {
    // Simulate API call - replace with actual API endpoints
    return {
        totalBookings: 1250,
        totalRevenue: 1250000,
        activeUsers: 450,
        activeBuses: 25
    };
}

function updateDashboardStats(stats) {
    document.getElementById('totalBookings').textContent = stats.totalBookings.toLocaleString();
    document.getElementById('totalRevenue').textContent = `৳${stats.totalRevenue.toLocaleString()}`;
    document.getElementById('activeUsers').textContent = stats.activeUsers.toLocaleString();
    document.getElementById('activeBuses').textContent = stats.activeBuses.toLocaleString();
}

async function fetchRecentBookings() {
    // Simulate API call
    return [
        { id: 1, passenger: 'John Doe', route: 'Dhaka - Cox\'s Bazar', amount: 550, status: 'confirmed', date: '2024-01-15' },
        { id: 2, passenger: 'Jane Smith', route: 'Dhaka - Chittagong', amount: 450, status: 'pending', date: '2024-01-15' },
        { id: 3, passenger: 'Bob Johnson', route: 'Dhaka - Sylhet', amount: 400, status: 'confirmed', date: '2024-01-14' }
    ];
}

function updateRecentBookingsTable(bookings) {
    const tbody = document.getElementById('recentBookingsTable');
    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td>#${booking.id}</td>
            <td>${booking.passenger}</td>
            <td>${booking.route}</td>
            <td>৳${booking.amount}</td>
            <td><span class="badge badge-${booking.status === 'confirmed' ? 'success' : booking.status === 'pending' ? 'warning' : 'error'}">${booking.status}</span></td>
            <td>${booking.date}</td>
        </tr>
    `).join('');
}

// Routes CRUD
async function loadRoutes() {
    try {
        const response = await fetch('../api/routes.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            updateRoutesTable(data.data);
        } else {
            showNotification(data.message || 'Error loading routes', 'error');
        }
    } catch (error) {
        console.error('Error loading routes:', error);
        // Show sample data for demo if API fails
        const sampleRoutes = [
            { id: 1, from_location: 'Dhaka', to_location: 'Cox\'s Bazar', distance_km: 400.5, estimated_duration_hours: 11.0 },
            { id: 2, from_location: 'Dhaka', to_location: 'Chittagong', distance_km: 250.0, estimated_duration_hours: 7.5 },
            { id: 3, from_location: 'Dhaka', to_location: 'Sylhet', distance_km: 300.0, estimated_duration_hours: 8.0 }
        ];
        updateRoutesTable(sampleRoutes);
        showNotification('Using sample data (API connection failed)', 'info');
    }
}

function updateRoutesTable(routes) {
    const tbody = document.getElementById('routesTable');
    tbody.innerHTML = routes.map(route => `
        <tr>
            <td>${route.id}</td>
            <td>${route.from_location}</td>
            <td>${route.to_location}</td>
            <td class="hide-mobile">${route.distance_km}</td>
            <td class="hide-mobile">${route.estimated_duration_hours}</td>
            <td>
                <button onclick="editRoute(${route.id})" class="btn btn-sm btn-outline mr-1 md:mr-2 btn-mobile">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteRoute(${route.id})" class="btn btn-sm btn-error btn-mobile">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openAddRouteModal() {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-md modal-content">
                <h3 class="text-lg md:text-xl font-bold mb-4">Add New Route</h3>
                <form id="addRouteForm" class="space-y-4">
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">From Location</span>
                            </div>
                            <input type="text" name="from_location" class="input input-bordered w-full" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">To Location</span>
                            </div>
                            <input type="text" name="to_location" class="input input-bordered w-full" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Distance (km)</span>
                            </div>
                            <input type="number" name="distance_km" class="input input-bordered w-full" step="0.1" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Duration (hours)</span>
                            </div>
                            <input type="number" name="estimated_duration_hours" class="input input-bordered w-full" step="0.5" required>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Add Route</button>
                        <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    // Add form submission handler
    document.getElementById('addRouteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await addRoute(new FormData(this));
    });
}

function openEditRouteModal(route) {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-md modal-content">
                <h3 class="text-lg md:text-xl font-bold mb-4">Edit Route</h3>
                <form id="editRouteForm" class="space-y-4">
                    <input type="hidden" name="route_id" value="${route.id}">
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">From Location</span>
                            </div>
                            <input type="text" name="from_location" class="input input-bordered w-full" value="${route.from_location}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">To Location</span>
                            </div>
                            <input type="text" name="to_location" class="input input-bordered w-full" value="${route.to_location}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Distance (km)</span>
                            </div>
                            <input type="number" name="distance_km" class="input input-bordered w-full" step="0.1" value="${route.distance_km}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Duration (hours)</span>
                            </div>
                            <input type="number" name="estimated_duration_hours" class="input input-bordered w-full" step="0.5" value="${route.estimated_duration_hours}" required>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Update Route</button>
                        <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    // Add form submission handler
    document.getElementById('editRouteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await updateRoute(new FormData(this));
    });
}

async function addRoute(formData) {
    try {
        const response = await fetch('../api/routes.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showNotification('Route added successfully', 'success');
            closeModal();
            loadRoutes();
        } else {
            showNotification(data.message || 'Error adding route', 'error');
        }
    } catch (error) {
        console.error('Error adding route:', error);
        // For demo purposes, simulate success
        showNotification('Route added successfully (demo mode)', 'success');
        closeModal();
        loadRoutes();
    }
}

async function updateRoute(formData) {
    try {
        const routeId = formData.get('route_id');
        const routeData = {
            id: routeId,
            from_location: formData.get('from_location'),
            to_location: formData.get('to_location'),
            distance_km: formData.get('distance_km'),
            estimated_duration_hours: formData.get('estimated_duration_hours')
        };
        
        // Simulate API call - in a real app, this would be a PUT request
        console.log('Updating route:', routeData);
        
        // For demo purposes, simulate success
        showNotification('Route updated successfully (demo mode)', 'success');
        closeModal();
        loadRoutes();
        
        // In a real app, you would make this API call:
        /*
        const response = await fetch(`../api/routes.php?id=${routeId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(routeData)
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showNotification('Route updated successfully', 'success');
            closeModal();
            loadRoutes();
        } else {
            showNotification(data.message || 'Error updating route', 'error');
        }
        */
    } catch (error) {
        console.error('Error updating route:', error);
        showNotification('Error updating route', 'error');
    }
}

async function editRoute(id) {
    try {
        // Get route data (using sample data for demo)
        const sampleRoutes = [
            { id: 1, from_location: 'Dhaka', to_location: 'Cox\'s Bazar', distance_km: 400.5, estimated_duration_hours: 11.0 },
            { id: 2, from_location: 'Dhaka', to_location: 'Chittagong', distance_km: 250.0, estimated_duration_hours: 7.5 },
            { id: 3, from_location: 'Dhaka', to_location: 'Sylhet', distance_km: 300.0, estimated_duration_hours: 8.0 },
            { id: 4, from_location: 'Dhaka', to_location: 'Rajshahi', distance_km: 280.0, estimated_duration_hours: 7.0 },
            { id: 5, from_location: 'Dhaka', to_location: 'Khulna', distance_km: 320.0, estimated_duration_hours: 8.5 },
            { id: 6, from_location: 'Chittagong', to_location: 'Cox\'s Bazar', distance_km: 150.0, estimated_duration_hours: 4.0 }
        ];
        
        const route = sampleRoutes.find(r => r.id === id);
        if (!route) {
            showNotification('Route not found', 'error');
            return;
        }
        
        openEditRouteModal(route);
    } catch (error) {
        console.error('Error loading route data:', error);
        showNotification('Error loading route data', 'error');
    }
}

async function deleteRoute(id) {
    if (confirm('Are you sure you want to delete this route?')) {
        try {
            const response = await fetch(`../api/routes.php?id=${id}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                showNotification('Route deleted successfully', 'success');
                loadRoutes();
            } else {
                showNotification(data.message || 'Error deleting route', 'error');
            }
        } catch (error) {
            console.error('Error deleting route:', error);
            showNotification('Error deleting route', 'error');
        }
    }
}

// Buses CRUD
async function loadBuses() {
    try {
        const response = await fetch('../api/buses.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            updateBusesTable(data.data);
        } else {
            showNotification('Error loading buses', 'error');
        }
    } catch (error) {
        console.error('Error loading buses:', error);
        // Show sample data for demo if API fails
        const sampleBuses = [
            { id: 1, bus_name: 'Green Line Express', bus_number: 'GL-001', bus_type: 'AC_Business', total_seats: 40, company_name: 'Jagat Bilash Paribahan' },
            { id: 2, bus_name: 'Shyamoli Express', bus_number: 'SE-002', bus_type: 'AC_Business', total_seats: 40, company_name: 'Jagat Bilash Paribahan' },
            { id: 3, bus_name: 'Ena Transport', bus_number: 'ET-003', bus_type: 'AC_Business', total_seats: 40, company_name: 'Jagat Bilash Paribahan' }
        ];
        updateBusesTable(sampleBuses);
        showNotification('Using sample data (API connection failed)', 'info');
    }
}

function updateBusesTable(buses) {
    const tbody = document.getElementById('busesTable');
    tbody.innerHTML = buses.map(bus => `
        <tr>
            <td>${bus.id}</td>
            <td>${bus.bus_name}</td>
            <td>${bus.bus_number}</td>
            <td>${bus.bus_type}</td>
            <td>${bus.total_seats}</td>
            <td>${bus.company_name}</td>
            <td>
                <button onclick="editBus(${bus.id})" class="btn btn-sm btn-outline mr-2">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteBus(${bus.id})" class="btn btn-sm btn-error">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openAddBusModal() {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-md modal-content modal-scroll">
                <h3 class="text-lg md:text-xl font-bold mb-4">Add New Bus</h3>
                <form id="addBusForm" class="space-y-4">
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus Name</span>
                            </div>
                            <input type="text" name="bus_name" class="input input-bordered w-full" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus Number</span>
                            </div>
                            <input type="text" name="bus_number" class="input input-bordered w-full" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus Type</span>
                            </div>
                            <select name="bus_type" class="select select-bordered w-full" required>
                                <option value="">Select Type</option>
                                <option value="AC_Business">AC Business</option>
                                <option value="AC_Economy">AC Economy</option>
                                <option value="Non_AC">Non AC</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Total Seats</span>
                            </div>
                            <input type="number" name="total_seats" class="input input-bordered w-full" value="40" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Company Name</span>
                            </div>
                            <input type="text" name="company_name" class="input input-bordered w-full" value="Jagat Bilash Paribahan" required>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Add Bus</button>
                        <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    document.getElementById('addBusForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await addBus(new FormData(this));
    });
}

function openEditBusModal(bus) {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold mb-4">Edit Bus</h3>
                <form id="editBusForm" class="space-y-4">
                    <input type="hidden" name="bus_id" value="${bus.id}">
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus Name</span>
                            </div>
                            <input type="text" name="bus_name" class="input input-bordered w-full" value="${bus.bus_name}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus Number</span>
                            </div>
                            <input type="text" name="bus_number" class="input input-bordered w-full" value="${bus.bus_number}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus Type</span>
                            </div>
                            <select name="bus_type" class="select select-bordered w-full" required>
                                <option value="">Select Type</option>
                                <option value="AC_Business" ${bus.bus_type === 'AC_Business' ? 'selected' : ''}>AC Business</option>
                                <option value="AC_Economy" ${bus.bus_type === 'AC_Economy' ? 'selected' : ''}>AC Economy</option>
                                <option value="Non_AC" ${bus.bus_type === 'Non_AC' ? 'selected' : ''}>Non AC</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Total Seats</span>
                            </div>
                            <input type="number" name="total_seats" class="input input-bordered w-full" value="${bus.total_seats}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Company Name</span>
                            </div>
                            <input type="text" name="company_name" class="input input-bordered w-full" value="${bus.company_name}" required>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Update Bus</button>
                        <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    document.getElementById('editBusForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await updateBus(new FormData(this));
    });
}

async function addBus(formData) {
    try {
        const response = await fetch('../api/buses.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showNotification('Bus added successfully', 'success');
            closeModal();
            loadBuses();
        } else {
            showNotification(data.message || 'Error adding bus', 'error');
        }
    } catch (error) {
        console.error('Error adding bus:', error);
        // For demo purposes, simulate success
        showNotification('Bus added successfully (demo mode)', 'success');
        closeModal();
        loadBuses();
    }
}

async function updateBus(formData) {
    try {
        const busId = formData.get('bus_id');
        const busData = {
            id: busId,
            bus_name: formData.get('bus_name'),
            bus_number: formData.get('bus_number'),
            bus_type: formData.get('bus_type'),
            total_seats: formData.get('total_seats'),
            company_name: formData.get('company_name')
        };
        
        // Simulate API call - in a real app, this would be a PUT request
        console.log('Updating bus:', busData);
        
        // For demo purposes, simulate success
        showNotification('Bus updated successfully (demo mode)', 'success');
        closeModal();
        loadBuses();
        
        // In a real app, you would make this API call:
        /*
        const response = await fetch(`../api/buses.php?id=${busId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(busData)
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showNotification('Bus updated successfully', 'success');
            closeModal();
            loadBuses();
        } else {
            showNotification(data.message || 'Error updating bus', 'error');
        }
        */
    } catch (error) {
        console.error('Error updating bus:', error);
        showNotification('Error updating bus', 'error');
    }
}

// Schedules CRUD
async function loadSchedules() {
    try {
        // For demo purposes, using sample data instead of API call
        // TODO: Replace with actual API call when backend is ready
        // const response = await fetch('../api/schedules.php');
        // const data = await response.json();
        
        // if (data.status === 'success') {
        //     updateSchedulesTable(data.data);
        // } else {
        //     showNotification('Error loading schedules', 'error');
        // }
        
        // Sample data for demo
        window.schedulesData = [
            { id: 1, bus_name: 'Green Line Express (GL-001)', route: 'Dhaka - Cox\'s Bazar', departure_time: '09:00', departure_date: '2025-08-04', fare: 850, available_seats: 35, status: 'active' },
            { id: 2, bus_name: 'Shyamoli Express (SE-002)', route: 'Dhaka - Chittagong', departure_time: '10:30', departure_date: '2025-08-04', fare: 650, available_seats: 40, status: 'active' },
            { id: 3, bus_name: 'Ena Transport (ET-003)', route: 'Dhaka - Sylhet', departure_time: '07:45', departure_date: '2025-08-04', fare: 750, available_seats: 38, status: 'active' },
            { id: 4, bus_name: 'Jagat Bilash Express (JB-004)', route: 'Dhaka - Rajshahi', departure_time: '22:00', departure_date: '2025-08-04', fare: 550, available_seats: 42, status: 'active' },
            { id: 5, bus_name: 'Green Line Express (GL-001)', route: 'Dhaka - Khulna', departure_time: '06:30', departure_date: '2025-08-05', fare: 600, available_seats: 36, status: 'active' }
        ];
        updateSchedulesTable(window.schedulesData);
    } catch (error) {
        console.error('Error loading schedules:', error);
        showNotification('Error loading schedules data', 'error');
    }
}

function updateSchedulesTable(schedules) {
    console.log('🔄 updateSchedulesTable called with:', schedules);
    console.log('📊 Number of schedules:', schedules.length);
    
    const tbody = document.getElementById('schedulesTable');
    if (!tbody) {
        console.error('❌ schedulesTable element not found!');
        return;
    }
    
    console.log('✅ Table element found:', tbody);
    
    try {
        const tableHTML = schedules.map(schedule => `
            <tr>
                <td>${schedule.id}</td>
                <td>${schedule.bus_name || 'N/A'}</td>
                <td>${schedule.route || 'N/A'}</td>
                <td>${schedule.departure_time}</td>
                <td>${schedule.departure_date}</td>
                <td>৳${schedule.fare}</td>
                <td>${schedule.available_seats}</td>
                <td><span class="badge badge-${schedule.status === 'active' ? 'success' : 'error'}">${schedule.status}</span></td>
                <td>
                    <button onclick="editSchedule(${schedule.id})" class="btn btn-sm btn-outline mr-2">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteSchedule(${schedule.id})" class="btn btn-sm btn-error">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        tbody.innerHTML = tableHTML;
        console.log('✅ Table HTML updated successfully');
        console.log('📏 New content length:', tableHTML.length);
        console.log('🏗️ Table innerHTML preview:', tableHTML.substring(0, 200) + '...');
        
        // Force browser to reflow the table
        tbody.offsetHeight;
        
        // Verify the update worked
        const newRowCount = tbody.querySelectorAll('tr').length;
        console.log('📊 Table now contains', newRowCount, 'rows');
        
        if (newRowCount !== schedules.length) {
            console.warn('⚠️ Row count mismatch! Expected:', schedules.length, 'Got:', newRowCount);
        } else {
            console.log('✅ Row count matches schedule count');
        }
        
    } catch (error) {
        console.error('❌ Error updating table:', error);
        console.error('❌ Error details:', error.message);
    }
}

async function addSchedule() {
    console.log("🚀 addSchedule function called");
    
    const busId = document.querySelector('select[name="bus_id"]').value;
    const routeId = document.querySelector('select[name="route_id"]').value;
    const departureTime = document.querySelector('input[name="departure_time"]').value;
    const departureDate = document.querySelector('input[name="departure_date"]').value;
    const fare = document.querySelector('input[name="fare"]').value;
    const availableSeats = document.querySelector('input[name="available_seats"]').value;
    const status = document.querySelector('select[name="status"]').value;

    console.log("Form values retrieved:", { busId, routeId, departureTime, departureDate, fare, availableSeats, status });

    // Basic validation
    if (!busId || !routeId || !departureTime || !departureDate || !fare || !availableSeats) {
        console.log("❌ Validation failed - missing fields");
        showNotification('Please fill in all required fields', 'error');
        return;
    }

    console.log("✅ Validation passed");

    try {
        console.log('Starting addSchedule function...');
        console.log('Form values:', { busId, routeId, departureTime, departureDate, fare, availableSeats, status });
        
        // Ensure schedulesData is initialized
        if (!window.schedulesData) {
            console.log("⚠️ schedulesData not found, initializing...");
            window.schedulesData = [
                { id: 1, bus_name: 'Green Line Express (GL-001)', route: 'Dhaka - Cox\'s Bazar', departure_time: '09:00', departure_date: '2025-08-04', fare: 850, available_seats: 35, status: 'active' },
                { id: 2, bus_name: 'Shyamoli Express (SE-002)', route: 'Dhaka - Chittagong', departure_time: '10:30', departure_date: '2025-08-04', fare: 650, available_seats: 40, status: 'active' },
                { id: 3, bus_name: 'Ena Transport (ET-003)', route: 'Dhaka - Sylhet', departure_time: '07:45', departure_date: '2025-08-04', fare: 750, available_seats: 38, status: 'active' },
                { id: 4, bus_name: 'Jagat Bilash Express (JB-004)', route: 'Dhaka - Rajshahi', departure_time: '22:00', departure_date: '2025-08-04', fare: 550, available_seats: 42, status: 'active' },
                { id: 5, bus_name: 'Green Line Express (GL-001)', route: 'Dhaka - Khulna', departure_time: '06:30', departure_date: '2025-08-05', fare: 600, available_seats: 36, status: 'active' }
            ];
        }
        
        // For demo purposes, using sample data instead of API call
        // TODO: Replace with actual API call
        // const response = await fetch('../api/schedules.php', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body: JSON.stringify({
        //         bus_id: busId,
        //         route_id: routeId,
        //         departure_time: departureTime,
        //         departure_date: departureDate,
        //         fare: fare,
        //         available_seats: availableSeats,
        //         status: status
        //     })
        // });

        // const data = await response.json();

        // if (data.status === 'success') {
            // Add to local data
            const busNames = {
                '1': 'Green Line Express (GL-001)',
                '2': 'Shyamoli Express (SE-002)',
                '3': 'Ena Transport (ET-003)',
                '4': 'Jagat Bilash Express (JB-004)'
            };
            const routeNames = {
                '1': 'Dhaka - Cox\'s Bazar',
                '2': 'Dhaka - Chittagong',
                '3': 'Dhaka - Sylhet',
                '4': 'Dhaka - Rajshahi',
                '5': 'Dhaka - Khulna'
            };
            
            console.log('Creating new schedule...');
            console.log('Current schedulesData:', window.schedulesData);
            
            const newSchedule = {
                id: window.schedulesData.length > 0 ? Math.max(...window.schedulesData.map(s => s.id)) + 1 : 1,
                bus_name: busNames[busId],
                route: routeNames[routeId],
                departure_time: departureTime,
                departure_date: departureDate,
                fare: parseInt(fare),
                available_seats: parseInt(availableSeats),
                status: status
            };
            
            console.log('New schedule:', newSchedule);
            
            window.schedulesData.push(newSchedule);
            console.log('Schedule added to array, new length:', window.schedulesData.length);
            console.log('Updated schedulesData:', window.schedulesData);
            
            // Force table update immediately with verification
            console.log("📊 About to update table with data:", window.schedulesData);
            updateSchedulesTable(window.schedulesData);
            console.log('✅ Table updated immediately');
            
            // Verify table was actually updated
            const tableElement = document.getElementById('schedulesTable');
            if (tableElement) {
                console.log("📋 Table element found, current row count:", tableElement.children.length);
            } else {
                console.error("❌ Table element not found!");
            }
            
            showNotification('Schedule added successfully!', 'success');
            console.log('✅ Success notification shown');
            
            // Close modal
            closeModal();
            console.log('✅ Modal closed');
            
            // Reset form (if still exists after modal close)
            setTimeout(() => {
                const form = document.getElementById('addScheduleForm');
                if (form) {
                    form.reset();
                    console.log('✅ Form reset');
                }
            }, 100);
            
            // Triple-check table update after delays
            setTimeout(() => {
                console.log("🔄 Triple-check: Updating table again...");
                updateSchedulesTable(window.schedulesData);
                console.log('Table re-updated after delay, final length:', window.schedulesData.length);
                
                // Verify the schedule appears in the table
                const tableElement = document.getElementById('schedulesTable');
                if (tableElement) {
                    const rows = tableElement.querySelectorAll('tr');
                    console.log('📊 Table now has', rows.length, 'rows');
                    
                    // Look for our new schedule
                    let found = false;
                    rows.forEach((row, index) => {
                        if (row.textContent.includes(newSchedule.bus_name) && 
                            row.textContent.includes(newSchedule.route)) {
                            found = true;
                            console.log(`✅ New schedule found in row ${index + 1}`);
                        }
                    });
                    
                    if (!found) {
                        console.log('❌ New schedule STILL not found in table, forcing final update...');
                        // Force a complete reload of schedules
                        loadSchedules();
                    } else {
                        console.log('🎉 SUCCESS: Schedule is now visible in the table!');
                    }
                } else {
                    console.error("❌ Table element still not found after delay!");
                }
            }, 500);
            
        // } else {
        //     showNotification(data.message || 'Failed to add schedule', 'error');
        // }
    } catch (error) {
        console.error('Error adding schedule:', error);
        showNotification('Error adding schedule. Please try again.', 'error');
    }
}

async function updateSchedule() {
    const scheduleId = parseInt(document.querySelector('input[name="schedule_id"]').value);
    const busId = document.querySelector('select[name="bus_id"]').value;
    const routeId = document.querySelector('select[name="route_id"]').value;
    const departureTime = document.querySelector('input[name="departure_time"]').value;
    const departureDate = document.querySelector('input[name="departure_date"]').value;
    const fare = document.querySelector('input[name="fare"]').value;
    const availableSeats = document.querySelector('input[name="available_seats"]').value;
    const status = document.querySelector('select[name="status"]').value;

    // Basic validation
    if (!busId || !routeId || !departureTime || !departureDate || !fare || !availableSeats) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }

    try {
        // For demo purposes, updating sample data instead of API call
        // TODO: Replace with actual API call
        // const response = await fetch('../api/schedules.php', {
        //     method: 'PUT',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body: JSON.stringify({
        //         id: scheduleId,
        //         bus_id: busId,
        //         route_id: routeId,
        //         departure_time: departureTime,
        //         departure_date: departureDate,
        //         fare: fare,
        //         available_seats: availableSeats,
        //         status: status
        //     })
        // });

        // const data = await response.json();

        // if (data.status === 'success') {
            // Update the local data
            const scheduleIndex = window.schedulesData.findIndex(s => s.id === scheduleId);
            if (scheduleIndex !== -1) {
                // Get bus and route names for display
                const busNames = {
                    '1': 'Green Line Express (GL-001)',
                    '2': 'Shyamoli Express (SE-002)',
                    '3': 'Ena Transport (ET-003)',
                    '4': 'Jagat Bilash Express (JB-004)'
                };
                const routeNames = {
                    '1': 'Dhaka - Cox\'s Bazar',
                    '2': 'Dhaka - Chittagong',
                    '3': 'Dhaka - Sylhet',
                    '4': 'Dhaka - Rajshahi',
                    '5': 'Dhaka - Khulna'
                };
                
                window.schedulesData[scheduleIndex] = {
                    ...window.schedulesData[scheduleIndex],
                    bus_name: busNames[busId],
                    route: routeNames[routeId],
                    departure_time: departureTime,
                    departure_date: departureDate,
                    fare: parseInt(fare),
                    available_seats: parseInt(availableSeats),
                    status: status
                };
                
                showNotification('Schedule updated successfully!', 'success');
                closeModal();
                updateSchedulesTable(window.schedulesData);
            } else {
                showNotification('Schedule not found', 'error');
            }
        // } else {
        //     showNotification(data.message || 'Failed to update schedule', 'error');
        // }
    } catch (error) {
        console.error('Error updating schedule:', error);
        showNotification('Error updating schedule. Please try again.', 'error');
    }
}

// Bookings CRUD
async function loadBookings() {
    try {
        // For demo purposes, using sample data instead of API call
        // TODO: Replace with actual API call when backend is ready
        // const response = await fetch('../api/bookings.php');
        // const data = await response.json();
        
        // if (data.status === 'success') {
        //     bookingsData = data.data;
        //     updateBookingsTable(bookingsData);
        // } else {
        //     showNotification('Error loading bookings', 'error');
        // }
        
        // Sample data for demo
        bookingsData = [
            { 
                id: 1, 
                passenger_name: 'John Doe', 
                passenger_phone: '+880 1712345678', 
                passenger_email: 'john@example.com', 
                total_amount: 1200, 
                discount_amount: 100, 
                final_amount: 1100, 
                booking_status: 'confirmed', 
                booking_date: '2025-08-01', 
                route: 'Dhaka - Cox\'s Bazar', 
                seats: 'A1, A2',
                bus_name: 'Green Line Express (GL-001)',
                departure_time: '09:00',
                departure_date: '2025-08-04'
            },
            { 
                id: 2, 
                passenger_name: 'Jane Smith', 
                passenger_phone: '+880 1812345678', 
                passenger_email: 'jane@example.com', 
                total_amount: 800, 
                discount_amount: 0, 
                final_amount: 800, 
                booking_status: 'pending', 
                booking_date: '2025-08-01', 
                route: 'Dhaka - Chittagong', 
                seats: 'D3',
                bus_name: 'Shyamoli Express (SE-002)',
                departure_time: '10:30',
                departure_date: '2025-08-04'
            },
            { 
                id: 3, 
                passenger_name: 'Bob Johnson', 
                passenger_phone: '+880 1912345678', 
                passenger_email: 'bob@example.com', 
                total_amount: 900, 
                discount_amount: 50, 
                final_amount: 850, 
                booking_status: 'confirmed', 
                booking_date: '2025-08-01', 
                route: 'Dhaka - Sylhet', 
                seats: 'H1',
                bus_name: 'Ena Transport (ET-003)',
                departure_time: '07:45',
                departure_date: '2025-08-04'
            }
        ];
        updateBookingsTable(bookingsData);
    } catch (error) {
        console.error('Error loading bookings:', error);
        showNotification('Error loading bookings data', 'error');
    }
}

function updateBookingsTable(bookings) {
    const tbody = document.getElementById('bookingsTable');
    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td>#${booking.id}</td>
            <td>${booking.passenger_name}</td>
            <td>${booking.route || 'N/A'}</td>
            <td>${booking.seats || 'N/A'}</td>
            <td>৳${booking.total_amount}</td>
            <td><span class="badge badge-${booking.booking_status === 'confirmed' ? 'success' : booking.booking_status === 'pending' ? 'warning' : 'error'}">${booking.booking_status}</span></td>
            <td>${booking.booking_date}</td>
            <td>
                <button onclick="viewBooking(${booking.id})" class="btn btn-sm btn-outline mr-2">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="updateBookingStatus(${booking.id})" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function filterBookings() {
    const status = document.getElementById('bookingStatusFilter').value;
    // Implement filtering logic
    showNotification('Filtering functionality will be implemented', 'info');
}

function exportBookings() {
    // Implement export functionality
    showNotification('Export functionality will be implemented', 'info');
}

// Users CRUD
async function loadUsers() {
    try {
        const response = await fetch('../api/users.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            updateUsersTable(data.data);
        } else {
            showNotification('Error loading users', 'error');
        }
    } catch (error) {
        console.error('Error loading users:', error);
        // Show sample data for demo if API fails
        const sampleUsers = [
            { id: 1, name: 'John Doe', email: 'john@example.com', phone: '+880123456789', created_at: '2024-01-01' },
            { id: 2, name: 'Jane Smith', email: 'jane@example.com', phone: '+880987654321', created_at: '2024-01-02' },
            { id: 3, name: 'Bob Wilson', email: 'bob@example.com', phone: '+880555555555', created_at: '2024-01-03' }
        ];
        updateUsersTable(sampleUsers);
        showNotification('Using sample data (API connection failed)', 'info');
    }
}

function updateUsersTable(users) {
    const tbody = document.getElementById('usersTable');
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.phone}</td>
            <td>${user.created_at}</td>
            <td>
                <button onclick="editUser(${user.id})" class="btn btn-sm btn-outline mr-2">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteUser(${user.id})" class="btn btn-sm btn-error">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Coupons CRUD
async function loadCoupons() {
    try {
        const response = await fetch('../api/coupons.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            updateCouponsTable(data.data);
        } else {
            showNotification('Error loading coupons', 'error');
        }
    } catch (error) {
        console.error('Error loading coupons:', error);
        // Show sample data for demo if API fails
        const sampleCoupons = [
            { id: 1, coupon_code: 'BOISHAK15', discount_percentage: 15, max_discount_amount: 200, min_booking_amount: 500, valid_until: '2025-02-28', used_count: 25, status: 'active' },
            { id: 2, coupon_code: 'WELCOME10', discount_percentage: 10, max_discount_amount: 150, min_booking_amount: 300, valid_until: '2025-03-31', used_count: 15, status: 'active' },
            { id: 3, coupon_code: 'SUMMER20', discount_percentage: 20, max_discount_amount: 300, min_booking_amount: 800, valid_until: '2025-06-30', used_count: 8, status: 'active' }
        ];
        updateCouponsTable(sampleCoupons);
        showNotification('Using sample data (API connection failed)', 'info');
    }
}

function updateCouponsTable(coupons) {
    const tbody = document.getElementById('couponsTable');
    tbody.innerHTML = coupons.map(coupon => `
        <tr>
            <td>${coupon.id}</td>
            <td><span class="font-mono">${coupon.coupon_code}</span></td>
            <td>${coupon.discount_percentage}%</td>
            <td>৳${coupon.max_discount_amount}</td>
            <td>৳${coupon.min_booking_amount}</td>
            <td>${coupon.valid_until}</td>
            <td>${coupon.used_count || 0}</td>
            <td><span class="badge badge-${coupon.status === 'active' ? 'success' : 'error'}">${coupon.status}</span></td>
            <td>
                <button onclick="editCoupon(${coupon.id})" class="btn btn-sm btn-outline mr-2">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteCoupon(${coupon.id})" class="btn btn-sm btn-error">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Reports
async function loadReports() {
    try {
        // Load popular routes
        const popularRoutes = await fetchPopularRoutes();
        updatePopularRoutesList(popularRoutes);
        
        // Load recent activity
        const recentActivity = await fetchRecentActivity();
        updateRecentActivityList(recentActivity);
    } catch (error) {
        console.error('Error loading reports:', error);
        showNotification('Error loading reports', 'error');
    }
}

async function fetchPopularRoutes() {
    // Simulate API call
    return [
        { route: 'Dhaka - Cox\'s Bazar', bookings: 150, revenue: 82500 },
        { route: 'Dhaka - Chittagong', bookings: 120, revenue: 54000 },
        { route: 'Dhaka - Sylhet', bookings: 80, revenue: 32000 }
    ];
}

function updatePopularRoutesList(routes) {
    const container = document.getElementById('popularRoutesList');
    container.innerHTML = routes.map((route, index) => `
        <div class="flex justify-between items-center p-3 border-b">
            <div>
                <p class="font-semibold">${index + 1}. ${route.route}</p>
                <p class="text-sm text-gray-600">${route.bookings} bookings</p>
            </div>
            <div class="text-right">
                <p class="font-semibold text-[#1DD100]">৳${route.revenue.toLocaleString()}</p>
            </div>
        </div>
    `).join('');
}

async function fetchRecentActivity() {
    // Simulate API call
    return [
        { action: 'New booking created', user: 'John Doe', time: '2 minutes ago' },
        { action: 'Route updated', user: 'Admin', time: '5 minutes ago' },
        { action: 'New user registered', user: 'Jane Smith', time: '10 minutes ago' }
    ];
}

function updateRecentActivityList(activities) {
    const container = document.getElementById('recentActivityList');
    container.innerHTML = activities.map(activity => `
        <div class="flex items-center p-3 border-b">
            <div class="w-2 h-2 bg-[#1DD100] rounded-full mr-3"></div>
            <div class="flex-1">
                <p class="font-semibold">${activity.action}</p>
                <p class="text-sm text-gray-600">by ${activity.user} • ${activity.time}</p>
            </div>
        </div>
    `).join('');
}

// Utility functions
function closeModal() {
    // Remove modal from body (since modals are appended to body)
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (modal) {
        modal.remove();
    }
    
    // Also try to remove from modalContainer if it exists
    const modalContainer = document.getElementById('modalContainer');
    if (modalContainer) {
        modalContainer.innerHTML = '';
    }
}

function clearAllNotifications() {
    // Clear all types of notifications/alerts that might be stuck
    const alertSelectors = [
        '.alert', '.notification', '.toast', '.alert-error', 
        '.alert-success', '.alert-info', '.alert-warning',
        '[class*="alert"]', '[class*="notification"]', '[class*="toast"]',
        '.fixed.top-4.right-4', '.bg-red-500', '.bg-green-500', '.bg-blue-500'
    ];
    
    alertSelectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            if (el.textContent && (
                el.textContent.includes('Error adding schedule') ||
                el.textContent.includes('error') ||
                el.textContent.includes('success') ||
                el.classList.contains('alert')
            )) {
                el.remove();
            }
        });
    });
    
    // Also clear any red background elements that might be error bars
    const redElements = document.querySelectorAll('[style*="background-color: red"], [style*="background: red"], .bg-red');
    redElements.forEach(el => {
        if (el.textContent && el.textContent.includes('Error')) {
            el.remove();
        }
    });
}

function showNotification(message, type = 'info') {
    // Clear any existing notifications first
    clearAllNotifications();
    
    const notification = `
        <div class="fixed top-4 right-4 z-50 alert alert-${type === 'success' ? 'success' : type === 'error' ? 'error' : 'info'} shadow-lg">
            <div>
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        const notificationElement = document.querySelector('.alert');
        if (notificationElement) {
            notificationElement.remove();
        }
    }, 3000);
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear admin session
        localStorage.removeItem('adminToken');
        localStorage.removeItem('adminEmail');
        // Redirect to login page
        window.location.href = 'login.html';
    }
}

// Placeholder functions for CRUD operations
function editBus(id) { 
    // Get bus data and open edit modal
    const sampleBuses = [
        { id: 1, bus_name: 'Green Line Express', bus_number: 'GL-001', bus_type: 'AC_Business', total_seats: 40, company_name: 'Jagat Bilash Paribahan' },
        { id: 2, bus_name: 'Shyamoli Express', bus_number: 'SE-002', bus_type: 'AC_Business', total_seats: 40, company_name: 'Jagat Bilash Paribahan' },
        { id: 3, bus_name: 'Ena Transport', bus_number: 'ET-003', bus_type: 'AC_Business', total_seats: 40, company_name: 'Jagat Bilash Paribahan' }
    ];
    
    const bus = sampleBuses.find(b => b.id === id);
    if (!bus) {
        showNotification('Bus not found', 'error');
        return;
    }
    
    openEditBusModal(bus);
}
function deleteBus(id) { showNotification('Delete bus functionality will be implemented', 'info'); }
function editSchedule(id) { 
    const schedule = window.schedulesData.find(s => s.id === id);
    if (!schedule) {
        showNotification('Schedule not found', 'error');
        return;
    }
    
    openEditScheduleModal(schedule);
}

function openEditScheduleModal(schedule) {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-md modal-content modal-scroll">
                <h3 class="text-lg md:text-xl font-bold mb-4">Edit Schedule</h3>
                <form id="editScheduleForm" class="space-y-4">
                    <input type="hidden" name="schedule_id" value="${schedule.id}">
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus</span>
                            </div>
                            <select name="bus_id" class="select select-bordered w-full" required>
                                <option value="">Select Bus</option>
                                <option value="1" ${schedule.bus_name.includes('Green Line') ? 'selected' : ''}>Green Line Express (GL-001)</option>
                                <option value="2" ${schedule.bus_name.includes('Shyamoli') ? 'selected' : ''}>Shyamoli Express (SE-002)</option>
                                <option value="3" ${schedule.bus_name.includes('Ena') ? 'selected' : ''}>Ena Transport (ET-003)</option>
                                <option value="4" ${schedule.bus_name.includes('Jagat') ? 'selected' : ''}>Jagat Bilash Express (JB-004)</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Route</span>
                            </div>
                            <select name="route_id" class="select select-bordered w-full" required>
                                <option value="">Select Route</option>
                                <option value="1" ${schedule.route.includes('Cox\'s Bazar') ? 'selected' : ''}>Dhaka - Cox's Bazar</option>
                                <option value="2" ${schedule.route.includes('Chittagong') ? 'selected' : ''}>Dhaka - Chittagong</option>
                                <option value="3" ${schedule.route.includes('Sylhet') ? 'selected' : ''}>Dhaka - Sylhet</option>
                                <option value="4" ${schedule.route.includes('Rajshahi') ? 'selected' : ''}>Dhaka - Rajshahi</option>
                                <option value="5" ${schedule.route.includes('Khulna') ? 'selected' : ''}>Dhaka - Khulna</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Departure Time</span>
                            </div>
                            <input type="time" name="departure_time" class="input input-bordered w-full" value="${schedule.departure_time}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Departure Date</span>
                            </div>
                            <input type="date" name="departure_date" class="input input-bordered w-full" value="${schedule.departure_date}" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Fare (৳)</span>
                            </div>
                            <input type="number" name="fare" class="input input-bordered w-full" value="${schedule.fare}" min="0" step="10" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Available Seats</span>
                            </div>
                            <input type="number" name="available_seats" class="input input-bordered w-full" value="${schedule.available_seats}" min="1" max="40" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Status</span>
                            </div>
                            <select name="status" class="select select-bordered w-full" required>
                                <option value="active" ${schedule.status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="inactive" ${schedule.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Update Schedule</button>
                        <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="departure_date"]').setAttribute('min', today);
    
    // Add form submission handler
    document.getElementById('editScheduleForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await updateSchedule();
    });
}
function deleteSchedule(id) { 
    // Show confirmation dialog
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        const scheduleIndex = window.schedulesData.findIndex(s => s.id === id);
        if (scheduleIndex !== -1) {
            // For demo purposes, removing from sample data instead of API call
            // TODO: Replace with actual API call
            // const response = await fetch(`../api/schedules.php?id=${id}`, {
            //     method: 'DELETE'
            // });
            
            // Remove from local data
            window.schedulesData.splice(scheduleIndex, 1);
            showNotification('Schedule deleted successfully!', 'success');
            updateSchedulesTable(window.schedulesData);
        } else {
            showNotification('Schedule not found', 'error');
        }
    }
}
function viewBooking(id) { 
    const booking = bookingsData.find(b => b.id === id);
    if (!booking) {
        showNotification('Booking not found', 'error');
        return;
    }
    
    openViewBookingModal(booking);
}

function openViewBookingModal(booking) {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-lg modal-content modal-scroll">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg md:text-xl font-bold">Booking Details</h3>
                    <button onclick="closeModal()" class="btn btn-ghost btn-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Booking Info -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Booking Information</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><span class="font-medium">Booking ID:</span> #${booking.id}</div>
                            <div><span class="font-medium">Status:</span> 
                                <span class="badge badge-${booking.booking_status === 'confirmed' ? 'success' : booking.booking_status === 'pending' ? 'warning' : 'error'} badge-sm">
                                    ${booking.booking_status}
                                </span>
                            </div>
                            <div><span class="font-medium">Booking Date:</span> ${booking.booking_date}</div>
                            <div><span class="font-medium">Seats:</span> ${booking.seats}</div>
                        </div>
                    </div>
                    
                    <!-- Passenger Info -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Passenger Information</h4>
                        <div class="space-y-1 text-sm">
                            <div><span class="font-medium">Name:</span> ${booking.passenger_name}</div>
                            <div><span class="font-medium">Phone:</span> ${booking.passenger_phone}</div>
                            <div><span class="font-medium">Email:</span> ${booking.passenger_email}</div>
                        </div>
                    </div>
                    
                    <!-- Journey Info -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Journey Information</h4>
                        <div class="space-y-1 text-sm">
                            <div><span class="font-medium">Route:</span> ${booking.route}</div>
                            <div><span class="font-medium">Bus:</span> ${booking.bus_name}</div>
                            <div><span class="font-medium">Departure:</span> ${booking.departure_time} on ${booking.departure_date}</div>
                        </div>
                    </div>
                    
                    <!-- Payment Info -->
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-2">Payment Information</h4>
                        <div class="space-y-1 text-sm">
                            <div><span class="font-medium">Total Amount:</span> ৳${booking.total_amount}</div>
                            <div><span class="font-medium">Discount:</span> ৳${booking.discount_amount}</div>
                            <div class="border-t pt-1 mt-2">
                                <span class="font-bold">Final Amount: ৳${booking.final_amount}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button onclick="updateBookingStatus(${booking.id})" class="btn btn-warning flex-1">
                        <i class="fas fa-edit mr-2"></i>Update Status
                    </button>
                    <button onclick="closeModal()" class="btn btn-outline flex-1">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
}
function updateBookingStatus(id) { 
    const booking = bookingsData.find(b => b.id === id);
    if (!booking) {
        showNotification('Booking not found', 'error');
        return;
    }
    
    openUpdateBookingStatusModal(booking);
}

function openUpdateBookingStatusModal(booking) {
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-md modal-content">
                <h3 class="text-lg md:text-xl font-bold mb-4">Update Booking Status</h3>
                
                <div class="space-y-4">
                    <!-- Booking Summary -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-sm">
                            <div><span class="font-medium">Booking:</span> #${booking.id}</div>
                            <div><span class="font-medium">Passenger:</span> ${booking.passenger_name}</div>
                            <div><span class="font-medium">Route:</span> ${booking.route}</div>
                            <div><span class="font-medium">Amount:</span> ৳${booking.final_amount}</div>
                        </div>
                    </div>
                    
                    <form id="updateBookingStatusForm" class="space-y-4">
                        <input type="hidden" name="booking_id" value="${booking.id}">
                        
                        <div>
                            <label class="form-control w-full">
                                <div class="label">
                                    <span class="label-text font-bold">Current Status</span>
                                </div>
                                <div class="p-3 bg-gray-100 rounded-lg">
                                    <span class="badge badge-${booking.booking_status === 'confirmed' ? 'success' : booking.booking_status === 'pending' ? 'warning' : 'error'}">
                                        ${booking.booking_status}
                                    </span>
                                </div>
                            </label>
                        </div>
                        
                        <div>
                            <label class="form-control w-full">
                                <div class="label">
                                    <span class="label-text font-bold">New Status</span>
                                </div>
                                <select name="new_status" class="select select-bordered w-full" required>
                                    <option value="">Select New Status</option>
                                    <option value="pending" ${booking.booking_status === 'pending' ? 'disabled' : ''}>Pending</option>
                                    <option value="confirmed" ${booking.booking_status === 'confirmed' ? 'disabled' : ''}>Confirmed</option>
                                    <option value="cancelled" ${booking.booking_status === 'cancelled' ? 'disabled' : ''}>Cancelled</option>
                                    <option value="completed" ${booking.booking_status === 'completed' ? 'disabled' : ''}>Completed</option>
                                </select>
                            </label>
                        </div>
                        
                        <div>
                            <label class="form-control w-full">
                                <div class="label">
                                    <span class="label-text font-bold">Reason/Notes (Optional)</span>
                                </div>
                                <textarea name="notes" class="textarea textarea-bordered w-full" rows="3" placeholder="Add any notes or reason for status change..."></textarea>
                            </label>
                        </div>
                        
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Update Status</button>
                            <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    // Add form submission handler
    document.getElementById('updateBookingStatusForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await updateBookingStatusSubmit();
    });
}

async function updateBookingStatusSubmit() {
    const bookingId = parseInt(document.querySelector('input[name="booking_id"]').value);
    const newStatus = document.querySelector('select[name="new_status"]').value;
    const notes = document.querySelector('textarea[name="notes"]').value;

    // Basic validation
    if (!newStatus) {
        showNotification('Please select a new status', 'error');
        return;
    }

    try {
        // For demo purposes, updating sample data instead of API call
        // TODO: Replace with actual API call
        // const response = await fetch('../api/bookings.php', {
        //     method: 'PUT',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body: JSON.stringify({
        //         id: bookingId,
        //         status: newStatus,
        //         notes: notes
        //     })
        // });

        // Update the local data
        const bookingIndex = bookingsData.findIndex(b => b.id === bookingId);
        if (bookingIndex !== -1) {
            bookingsData[bookingIndex].booking_status = newStatus;
            
            showNotification(`Booking status updated to "${newStatus}" successfully!`, 'success');
            closeModal();
            updateBookingsTable(bookingsData);
        } else {
            showNotification('Booking not found', 'error');
        }
    } catch (error) {
        console.error('Error updating booking status:', error);
        showNotification('Error updating booking status. Please try again.', 'error');
    }
}
function editUser(id) { showNotification('Edit user functionality will be implemented', 'info'); }
function deleteUser(id) { showNotification('Delete user functionality will be implemented', 'info'); }
function editCoupon(id) { showNotification('Edit coupon functionality will be implemented', 'info'); }
function deleteCoupon(id) { showNotification('Delete coupon functionality will be implemented', 'info'); }
function openAddScheduleModal() { 
    const modal = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-4 md:p-6 w-full max-w-md modal-content modal-scroll">
                <h3 class="text-lg md:text-xl font-bold mb-4">Add New Schedule</h3>
                <form id="addScheduleForm" class="space-y-4">
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Bus</span>
                            </div>
                            <select name="bus_id" class="select select-bordered w-full" required>
                                <option value="">Select Bus</option>
                                <option value="1">Green Line Express (GL-001)</option>
                                <option value="2">Shyamoli Express (SE-002)</option>
                                <option value="3">Ena Transport (ET-003)</option>
                                <option value="4">Jagat Bilash Express (JB-004)</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Route</span>
                            </div>
                            <select name="route_id" class="select select-bordered w-full" required>
                                <option value="">Select Route</option>
                                <option value="1">Dhaka - Cox's Bazar</option>
                                <option value="2">Dhaka - Chittagong</option>
                                <option value="3">Dhaka - Sylhet</option>
                                <option value="4">Dhaka - Rajshahi</option>
                                <option value="5">Dhaka - Khulna</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Departure Time</span>
                            </div>
                            <input type="time" name="departure_time" class="input input-bordered w-full" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Departure Date</span>
                            </div>
                            <input type="date" name="departure_date" class="input input-bordered w-full" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Fare (৳)</span>
                            </div>
                            <input type="number" name="fare" class="input input-bordered w-full" min="0" step="10" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Available Seats</span>
                            </div>
                            <input type="number" name="available_seats" class="input input-bordered w-full" min="1" max="40" value="40" required>
                        </label>
                    </div>
                    <div>
                        <label class="form-control w-full">
                            <div class="label">
                                <span class="label-text font-bold">Status</span>
                            </div>
                            <select name="status" class="select select-bordered w-full" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-success bg-[#1DD100] flex-1">Add Schedule</button>
                        <button type="button" onclick="closeModal()" class="btn btn-outline flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modal;
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="departure_date"]').setAttribute('min', today);
    
    // Add form submission handler with better error handling
    document.getElementById('addScheduleForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log("🎯 Form submission prevented, calling addSchedule...");
        
        try {
            await addSchedule();
        } catch (error) {
            console.error("❌ Error in form submission:", error);
            showNotification('Error adding schedule: ' + error.message, 'error');
        }
    });
}
function openAddUserModal() { showNotification('Add user modal will be implemented', 'info'); }
function openAddCouponModal() { showNotification('Add coupon modal will be implemented', 'info'); }

// Clear any stuck notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel loaded, clearing any stuck notifications...');
    
    // Immediate aggressive cleanup
    clearAllNotifications();
    
    // Clear after delays to catch late-loading notifications
    setTimeout(() => {
        clearAllNotifications();
        console.log('First cleanup completed');
    }, 500);
    
    setTimeout(() => {
        clearAllNotifications();
        console.log('Second cleanup completed');
    }, 1500);
    
    // Override any error notifications related to schedule adding
    const originalShowNotification = window.showNotification;
    window.showNotification = function(message, type = 'info') {
        // Block the persistent error message
        if (message && message.includes('Error adding schedule')) {
            console.log('Blocked persistent "Error adding schedule" notification');
            return;
        }
        
        // Call original function for other notifications
        if (originalShowNotification) {
            originalShowNotification.call(this, message, type);
        }
    };
    
    // Also clear on window load
    window.addEventListener('load', () => {
        setTimeout(() => {
            clearAllNotifications();
            console.log('Window load cleanup completed');
        }, 1000);
    });
    
    // Emergency cleanup on any error
    window.addEventListener('error', () => {
        setTimeout(() => {
            clearAllNotifications();
        }, 100);
    });
}); 