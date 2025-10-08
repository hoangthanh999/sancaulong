// Animate numbers
function animateNumbers() {
    const bookings = document.getElementById('totalBookings');
    const spent = document.getElementById('totalSpent');

    if (!bookings || !spent) return;

    let bookingCount = 0;
    let spentCount = 0;
    const bookingTarget = parseInt(bookings.textContent) || 0;
    const spentTarget = parseInt(spent.textContent.replace(/\D/g, "")) || 0;

    const bookingInterval = setInterval(() => {
        bookingCount++;
        bookings.textContent = bookingCount;
        if (bookingCount >= bookingTarget) clearInterval(bookingInterval);
    }, 50);

    const spentInterval = setInterval(() => {
        spentCount += Math.ceil(spentTarget / 50);
        spent.textContent = spentCount.toLocaleString('vi-VN') + ' VNĐ';
        if (spentCount >= spentTarget) clearInterval(spentInterval);
    }, 30);
}

// Hover effects
document.querySelectorAll('.stat-card, .action-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px) scale(1.02)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Dynamic greeting
function updateGreeting() {
    const hour = new Date().getHours();
    const welcomeText = document.querySelector('.welcome-text h1');
    if (!welcomeText) return;

    if (hour < 12) {
        welcomeText.textContent = 'Chào buổi sáng!';
    } else if (hour < 18) {
        welcomeText.textContent = 'Chào buổi chiều!';
    } else {
        welcomeText.textContent = 'Chào buổi tối!';
    }
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    animateNumbers();
    updateGreeting();

    // Load animation for action cards
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
