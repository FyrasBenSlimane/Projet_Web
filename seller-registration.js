document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    const user = JSON.parse(localStorage.getItem('currentUser'));
    if (!user) {
        window.location.href = 'index.html?login=required';
        return;
    }

    // Check if there's a becomeSeller parameter in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const becomeSeller = urlParams.get('becomeSeller');
    
    if (becomeSeller === 'true') {
        // Show the seller registration form
        showSellerRegistrationForm();
    }

    // Handle form submission
    const sellerForm = document.getElementById('sellerRegistrationForm');
    if (sellerForm) {
        sellerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(sellerForm);
            const sellerData = {
                category: formData.get('category'),
                skills: formData.get('skills').split(',').map(skill => skill.trim()),
                description: formData.get('description'),
                experience: formData.get('experience')
            };
            
            // Update user in localStorage
            user.isSeller = true;
            user.sellerProfile = sellerData;
            localStorage.setItem('currentUser', JSON.stringify(user));
            
            // Show success message and redirect
            alert('Your seller profile has been created! You can now offer services.');
            window.location.href = 'dashboard.html';
        });
    }
});

function showSellerRegistrationForm() {
    // Get the container for the form
    const container = document.querySelector('.dashboard-content') || document.querySelector('main');
    
    if (container) {
        // Create the form HTML
        const formHTML = `
            <div class="seller-registration-card">
                <h2>Become a Seller</h2>
                <p>Complete the form below to start offering your services on our platform.</p>
                
                <form id="sellerRegistrationForm" class="seller-form">
                    <div class="form-group">
                        <label for="category">Primary Service Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <option value="web-development">Web Development</option>
                            <option value="design">Design & Creative</option>
                            <option value="marketing">Digital Marketing</option>
                            <option value="writing">Writing & Translation</option>
                            <option value="video">Video & Animation</option>
                            <option value="music">Music & Audio</option>
                            <option value="programming">Programming & Tech</option>
                            <option value="business">Business</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="skills">Skills (comma separated)</label>
                        <input type="text" id="skills" name="skills" class="form-control" required placeholder="e.g. JavaScript, HTML, CSS, React">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Professional Description</label>
                        <textarea id="description" name="description" class="form-control" required rows="4" placeholder="Tell buyers about your professional background, experience, and skills"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Years of Experience</label>
                        <select id="experience" name="experience" class="form-control" required>
                            <option value="">Select experience level</option>
                            <option value="1">Less than 1 year</option>
                            <option value="2">1-2 years</option>
                            <option value="3">3-5 years</option>
                            <option value="4">5-10 years</option>
                            <option value="5">10+ years</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Create Seller Profile</button>
                </form>
            </div>
        `;
        
        // Insert the form at the beginning of the container
        container.insertAdjacentHTML('afterbegin', formHTML);
    }
}
