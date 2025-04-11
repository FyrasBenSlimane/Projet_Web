document.addEventListener('DOMContentLoaded', function() {
    // Hero section parallax effect
    const heroSection = document.getElementById('home');
    const heroImage = document.querySelector('.hero-image');
    const bubbles = document.querySelectorAll('.bubble');
    const shapes = document.querySelectorAll('.hero-shape');
    
    // Mouse move parallax effect
    heroSection.addEventListener('mousemove', (e) => {
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        
        // Move hero image slightly with mouse
        if (heroImage) {
            heroImage.style.transform = `perspective(1000px) rotateY(${(x - 0.5) * 10}deg) rotateX(${(y - 0.5) * -5}deg)`;
        }
        
        // Move bubbles with parallax effect
        bubbles.forEach((bubble, index) => {
            const speed = 1 + index * 0.5;
            const xPos = (x - 0.5) * speed * 20;
            const yPos = (y - 0.5) * speed * 20;
            bubble.style.transform = `translate(${xPos}px, ${yPos}px)`;
        });
        
        // Move shapes with parallax effect
        shapes.forEach((shape, index) => {
            const speed = 0.5 + index * 0.2;
            const xPos = (x - 0.5) * speed * 40;
            const yPos = (y - 0.5) * speed * 40;
            shape.style.transform = `translate(${xPos}px, ${yPos}px)`;
        });
    });
    
    // Add scroll parallax for the hero section
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY;
        if (scrollPosition < window.innerHeight) {
            const opacity = 1 - (scrollPosition / (window.innerHeight / 1.5));
            const yOffset = scrollPosition * 0.5;
            
            // Move hero elements on scroll
            heroSection.style.transform = `translateY(${yOffset * 0.7}px)`;
            heroImage.style.transform = `translateY(${-yOffset * 0.2}px)`;
            
            // Fade hero elements on scroll
            bubbles.forEach((bubble) => {
                bubble.style.opacity = opacity;
            });
            
            shapes.forEach((shape) => {
                shape.style.opacity = opacity;
            });
        }
    });
    
    // Text animation enhancements
    const words = document.querySelectorAll('.word');
    let currentWordIndex = 0;
    
    function updateTextAnimation() {
        // Reset all words
        words.forEach(word => {
            word.style.opacity = '0';
            word.style.transform = 'translateY(50%)';
        });
        
        // Show current word
        words[currentWordIndex].style.opacity = '1';
        words[currentWordIndex].style.transform = 'translateY(0)';
        
        // Update index for next iteration
        currentWordIndex = (currentWordIndex + 1) % words.length;
    }
    
    // Initialize animation
    updateTextAnimation();
    
    // Set interval for cycling words
    setInterval(updateTextAnimation, 2000);
});
