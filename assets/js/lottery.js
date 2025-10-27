/**
 * Slot Machine Lottery System
 * File: assets/js/lottery.js
 * Description: Vertical scrolling slot machine with smooth animations
 */

// Prize configuration - loaded from database
let prizes = [];
let isSpinning = false;
let availableTickets = 0;

// Load reward templates from database
async function loadRewardTemplates() {
    try {
        const response = await fetch('../api/get_reward_templates_public.php');
        const data = await response.json();
        
        if (data.success && data.templates && data.templates.length > 0) {
            prizes = data.templates.map((template, index) => ({
                id: template.id,
                name: template.reward_name,
                icon: getRewardIcon(template.reward_type),
                type: template.reward_type,
                value: template.reward_value,
                description: template.reward_description,
                quantity: template.reward_quantity,
                image: template.reward_image
            }));
        } else {
            // Default prizes if no templates found
            prizes = [
                { id: 1, name: 'Giảm 10%', icon: '🎁', type: 'voucher', value: '10' },
                { id: 2, name: 'Giảm 20%', icon: '🎉', type: 'voucher', value: '20' },
                { id: 3, name: 'Miễn phí vận chuyển', icon: '🚚', type: 'gift', value: 'Free' },
                { id: 4, name: 'Tặng kèm phụ kiện', icon: '🎁', type: 'gift', value: 'Gift' },
                { id: 5, name: 'Giảm 50%', icon: '💎', type: 'voucher', value: '50' },
                { id: 6, name: 'Chúc may mắn lần sau!', icon: '😢', type: 'gift', value: 'None' }
            ];
        }
        
        // Initialize slot machine with prizes
        initSlotMachine();
    } catch (error) {
        console.error('Error loading reward templates:', error);
        // Use default prizes on error
        prizes = [
            { id: 1, name: 'Giảm 10%', icon: '🎁', type: 'voucher', value: '10' },
            { id: 2, name: 'Giảm 20%', icon: '🎉', type: 'voucher', value: '20' },
            { id: 3, name: 'Miễn phí vận chuyển', icon: '🚚', type: 'gift', value: 'Free' },
            { id: 4, name: 'Tặng kèm phụ kiện', icon: '🎁', type: 'gift', value: 'Gift' },
            { id: 5, name: 'Giảm 50%', icon: '💎', type: 'voucher', value: '50' },
            { id: 6, name: 'Chúc may mắn lần sau!', icon: '😢', type: 'gift', value: 'None' }
        ];
        initSlotMachine();
    }
}

// Get icon based on reward type
function getRewardIcon(type) {
    const icons = {
        'voucher': '🎁',
        'cash': '💰',
        'gift': '🎁'
    };
    return icons[type] || '🎁';
}

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
    
    // Update prize display on sidebar
    updatePrizeDisplay();
}

// Update prize display in sidebar
function updatePrizeDisplay() {
    const prizeContainer = document.querySelector('.space-y-4');
    if (!prizeContainer || !prizes || prizes.length === 0) return;
    
    let html = '';
    prizes.forEach(prize => {
        html += `
            <div class="prize-item">
                <h3>${prize.icon} ${prize.name}</h3>
                <p>${prize.description || 'Phần thưởng đặc biệt'}</p>
            </div>
        `;
    });
    
    prizeContainer.innerHTML = html;
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
        
        // Kiểm tra xem có reward không
        const wonPrize = result.data?.reward;
        if (!wonPrize) {
            throw new Error('Không nhận được thông tin phần thưởng từ server');
        }
        
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
    loadRewardTemplates();
    loadTickets();
    
    // Add spin button event
    const spinButton = document.getElementById('spin-button');
    if (spinButton) {
        spinButton.addEventListener('click', spinSlot);
    }
});
