/**
 * Slot Machine Lottery System
 * File: assets/js/lottery.js
 * Description: Vertical scrolling slot machine with smooth animations
 */

// Prize configuration
const prizes = [
    { id: 1, name: 'Giảm 10%', icon: '🎁', type: 'discount', value: '10%' },
    { id: 2, name: 'Giảm 20%', icon: '🎉', type: 'discount', value: '20%' },
    { id: 3, name: 'Miễn phí vận chuyển', icon: '🚚', type: 'free_shipping', value: 'Free' },
    { id: 4, name: 'Tặng kèm phụ kiện', icon: '🎁', type: 'accessory', value: 'Gift' },
    { id: 5, name: 'Giảm 50%', icon: '💎', type: 'discount', value: '50%' },
    { id: 6, name: 'Chúc may mắn lần sau!', icon: '😢', type: 'no_prize', value: 'None' }
];

let isSpinning = false;
let availableTickets = 0;

// Initialize slot machine
function initSlotMachine() {
    const reel = document.getElementById('slot-reel');
    if (!reel) return;
    
    // Create extended reel (repeat prizes multiple times for smooth scrolling)
    const repeats = 10;
    let reelHTML = '';
    
    for (let i = 0; i < repeats; i++) {
        prizes.forEach(prize => {
            reelHTML += `
                <div class="slot-item" data-prize-id="${prize.id}">
                    <div class="slot-item-icon">${prize.icon}</div>
                    <div class="slot-item-text">${prize.name}</div>
                </div>
            `;
        });
    }
    
    reel.innerHTML = reelHTML;
    
    // Set initial position
    reel.style.top = '0px';
}

// Load user tickets
async function loadTickets() {
    try {
        const response = await fetch('../api/get_lottery_tickets.php', {
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            // API trả về total_tickets, không phải available_count
            availableTickets = result.data.total_tickets || 0;
            updateTicketDisplay();
        }
    } catch (error) {
        console.error('Error loading tickets:', error);
    }
}

// Update ticket display
function updateTicketDisplay() {
    const ticketCount = document.getElementById('ticket-count');
    if (ticketCount) {
        ticketCount.textContent = availableTickets;
    }
}

// Spin the slot machine
async function spinSlot() {
    if (isSpinning) return;
    
    if (availableTickets <= 0) {
        alert('Bạn không có vé quay! Hãy mua hàng để nhận vé.');
        return;
    }
    
    isSpinning = true;
    const spinButton = document.getElementById('spin-button');
    const spinText = document.getElementById('spin-text');
    const reel = document.getElementById('slot-reel');
    
    // Disable button
    spinButton.disabled = true;
    spinText.textContent = 'Đang quay...';
    
    try {
        // Call API to use ticket and get result
        const response = await fetch('../api/use_lottery_ticket.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Không thể sử dụng vé');
        }
        
        const wonPrize = result.data.reward;
        
        // Find prize index
        const prizeIndex = prizes.findIndex(p => p.name === wonPrize.reward_name);
        const targetPrize = prizeIndex >= 0 ? prizes[prizeIndex] : prizes[5]; // Default to "no prize"
        
        // Calculate scroll distance
        const itemHeight = 180; // Height of each slot item
        const totalItems = prizes.length * 10; // Total items in reel
        
        // Start with fast spinning
        reel.classList.add('spinning');
        
        // Spin through multiple cycles
        const spinCycles = 5;
        const finalPosition = (spinCycles * prizes.length + prizeIndex) * itemHeight;
        
        // Fast spin for 2 seconds
        let currentPos = 0;
        const fastSpinInterval = setInterval(() => {
            currentPos -= itemHeight;
            if (currentPos <= -totalItems * itemHeight) {
                currentPos = 0;
            }
            reel.style.top = currentPos + 'px';
        }, 50);
        
        // After 2 seconds, slow down and land on prize
        setTimeout(() => {
            clearInterval(fastSpinInterval);
            reel.classList.remove('spinning');
            
            // Smooth deceleration to final position
            reel.style.top = -finalPosition + 'px';
            
            // Show result after animation
            setTimeout(() => {
                showResult(wonPrize);
                availableTickets--;
                updateTicketDisplay();
                
                // Reset button
                spinButton.disabled = false;
                spinText.textContent = 'Quay Ngay!';
                isSpinning = false;
                
                // Reset reel position for next spin
                setTimeout(() => {
                    reel.style.transition = 'none';
                    reel.style.top = '0px';
                    setTimeout(() => {
                        reel.style.transition = 'top 3s cubic-bezier(0.25, 0.1, 0.25, 1)';
                    }, 50);
                }, 500);
            }, 3000);
        }, 2000);
        
    } catch (error) {
        console.error('Spin error:', error);
        alert(error.message || 'Có lỗi xảy ra khi quay thưởng');
        
        // Reset button
        spinButton.disabled = false;
        spinText.textContent = 'Quay Ngay!';
        isSpinning = false;
    }
}

// Show result modal
function showResult(reward) {
    const modal = document.getElementById('result-modal');
    const resultText = document.getElementById('result-text');
    
    if (reward.reward_type === 'no_prize') {
        resultText.innerHTML = `
            <div class="text-4xl mb-4">😢</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mb-2">Chúc may mắn lần sau!</div>
            <div class="text-gray-600 dark:text-gray-400">Đừng bỏ cuộc, hãy thử lại nhé!</div>
        `;
    } else {
        resultText.innerHTML = `
            <div class="text-4xl mb-4">🎉</div>
            <div class="text-xl font-bold text-gray-800 dark:text-white mb-2">Chúc mừng!</div>
            <div class="text-lg text-purple-600 dark:text-purple-400 font-semibold mb-2">${reward.reward_name}</div>
            <div class="text-gray-600 dark:text-gray-400">${reward.reward_value || 'Phần thưởng đặc biệt'}</div>
        `;
    }
    
    modal.classList.remove('hidden');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initSlotMachine();
    loadTickets();
    
    // Add spin button event
    const spinButton = document.getElementById('spin-button');
    if (spinButton) {
        spinButton.addEventListener('click', spinSlot);
    }
});
