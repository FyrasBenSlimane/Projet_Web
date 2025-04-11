document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const user = JSON.parse(localStorage.getItem('currentUser'));
    
    // Elements that require authentication
    const authRequiredButtons = document.querySelectorAll('.auth-required');
    
    // Add event listeners to elements that require authentication
    authRequiredButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!user) {
                e.preventDefault();
                showAuthOverlay('login');
                return false;
            }
        });
    });
    
    // Handle service card clicks
    const serviceCards = document.querySelectorAll('.service-card-listing');
    serviceCards.forEach(card => {
        card.addEventListener('click', () => {
            const serviceId = card.getAttribute('data-service-id');
            if (serviceId) {
                window.location.href = `service-details.html?id=${serviceId}`;
            }
        });
    });
    
    // Handle category card clicks
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            const categoryName = card.querySelector('h3').textContent;
            // Redirect to category page or filter results
            window.location.href = `search.html?category=${encodeURIComponent(categoryName)}`;
        });
    });
    
    // Search form functionality
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchInput = searchForm.querySelector('.search-input');
            if (searchInput && searchInput.value.trim()) {
                window.location.href = `search.html?query=${encodeURIComponent(searchInput.value.trim())}`;
            }
        });
    }
    
    // Popular searches functionality
    const popularSearchLinks = document.querySelectorAll('.popular-searches a');
    popularSearchLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const searchTerm = link.textContent;
            window.location.href = `search.html?query=${encodeURIComponent(searchTerm)}`;
        });
    });
});

// Show service details in a modal
function showServiceDetails(serviceId) {
    // This would fetch service details from an API
    console.log(`Fetching details for service ${serviceId}`);
    
    // In a real app, you'd make an API request here
    // For now, just simulate the data
    
    const serviceData = {
        id: serviceId,
        title: "Professional Web Development Service",
        description: "I will create a responsive, modern website for your business with the latest technologies.",
        seller: {
            name: "John D.",
            avatar: "https://via.placeholder.com/50x50",
            rating: 4.9,
            reviews: 231
        },
        price: {
            basic: 99,
            standard: 199,
            premium: 299
        },
        deliveryTime: {
            basic: 3,
            standard: 2,
            premium: 1
        }
    };
    
    // Display the data in a modal
    // This would be implemented with Bootstrap modal or similar
}
