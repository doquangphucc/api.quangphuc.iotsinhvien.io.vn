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
            
            // Always add "Chúc may mắn lần sau!" to prizes list if not already present
            const hasNoPrize = prizes.some(p => p.name === 'Chúc may mắn lần sau!');
            if (!hasNoPrize) {
                prizes.push({
                    id: -1, // Special ID for "no prize"
                    name: 'Chúc may mắn lần sau!',
                    icon: '😢',
                    type: 'gift',
                    value: null,
                    description: 'Hãy thử lại lần sau nhé!',
                    quantity: null,
                    image: null
                });
            }
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

// Update prize display in sidebar only (not main content)
function updatePrizeDisplay() {
    // Only update sidebar - find the prizes sidebar container
    const sidebarContainer = document.querySelector('.lg\\:col-span-1');
    if (!sidebarContainer) return;
    
    const prizeContainer = sidebarContainer.querySelector('.space-y-4');
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
    const maxQuantityEl = document.getElementById('max-spin-quantity');
    const spinQuantityInput = document.getElementById('spin-quantity');
    
    if (ticketCount) {
        ticketCount.textContent = availableTickets;
    }
    
    if (maxQuantityEl) {
        maxQuantityEl.textContent = availableTickets;
        // Update max attribute of input
        if (spinQuantityInput) {
            spinQuantityInput.setAttribute('max', availableTickets);
        }
    }
}

// Handle spin mode change
function handleSpinModeChange() {
    const mode = document.querySelector('input[name="spin-mode"]:checked')?.value || 'single';
    const customContainer = document.getElementById('custom-quantity-container');
    
    if (mode === 'custom') {
        customContainer.classList.remove('hidden');
        const input = document.getElementById('spin-quantity');
        if (input) {
            input.focus();
            input.value = Math.min(1, availableTickets);
        }
    } else {
        customContainer.classList.add('hidden');
    }
}


// Spin the slot machine
async function spinSlot() {
    if (isSpinning) return;
    
    // Reload tickets to get latest count before spinning
    await loadTickets();
    
    if (availableTickets <= 0) {
        showWarning('Bạn không có vé quay! Hãy mua hàng để nhận vé.');
        return;
    }
    
    // Get spin mode and quantity
    const mode = document.querySelector('input[name="spin-mode"]:checked')?.value || 'single';
    let quantity = 1;
    
    if (mode === 'all') {
        // Quay tất cả vé còn lại
        quantity = availableTickets;
    } else if (mode === 'custom') {
        // Quay số lượng tự nhập
        const spinQuantityInput = document.getElementById('spin-quantity');
        quantity = parseInt(spinQuantityInput?.value || 1);
        
        if (quantity > availableTickets) {
            showWarning(`Bạn chỉ có ${availableTickets} vé, không đủ để quay ${quantity} lần!`);
            return;
        }
        
        if (quantity < 1) {
            showWarning('Số lượng quay phải lớn hơn 0!');
            return;
        }
    } else {
        // mode === 'single' - Quay từng vé
        quantity = 1;
    }
    
    isSpinning = true;
    const spinButton = document.getElementById('spin-button');
    const spinText = document.getElementById('spin-text');
    const reel = document.getElementById('slot-reel');
    
    // Disable button
    spinButton.disabled = true;
    spinText.textContent = `Đang quay ${quantity} vé...`;
    
    try {
        // Call API to use tickets and get results
        const response = await fetch('../api/use_lottery_ticket.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ quantity: quantity }),
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Không thể sử dụng vé');
        }
        
        // Get rewards from response
        const rewards = result.data?.rewards || [];
        const ticketsUsed = result.data?.tickets_used || quantity;
        
        if (rewards.length === 0) {
            throw new Error('Không nhận được thông tin phần thưởng từ server');
        }
        
        // Update available tickets
        availableTickets -= ticketsUsed;
        updateTicketDisplay();
        
        // For multiple spins, show summary; for single spin, show animation
        if (quantity === 1) {
            // Single spin - show animation
            const wonPrize = rewards[0];
            
            // Find prize index - check exact name match first
            let prizeIndex = prizes.findIndex(p => p.name === wonPrize.reward_name);
            
            // If not found and it's "Chúc may mắn lần sau!", try to find it by checking for "may mắn"
            if (prizeIndex < 0 && (wonPrize.reward_name === 'Chúc may mắn lần sau!' || wonPrize.reward_name?.includes('may mắn'))) {
                prizeIndex = prizes.findIndex(p => p.name === 'Chúc may mắn lần sau!');
            }
            
            // If still not found, use last prize (should be "may mắn lần sau" if added above)
            if (prizeIndex < 0) {
                prizeIndex = prizes.length - 1;
            }
            
            const targetPrize = prizes[prizeIndex];
            
            // Calculate scroll distance
            const itemHeight = 180;
            const totalItems = prizes.length * 10;
            
            // Ensure prizeIndex is valid
            if (prizeIndex < 0 || prizeIndex >= prizes.length) {
                prizeIndex = prizes.length - 1; // Fallback to last prize (should be "may mắn lần sau")
            }
            
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
        } else {
            // Multiple spins - show summary immediately
            showMultipleResults(rewards, quantity);
            
            // Reset button
            spinButton.disabled = false;
            spinText.textContent = 'Quay Ngay!';
            isSpinning = false;
        }
        
    } catch (error) {
        console.error('Spin error:', error);
        showError(error.message || 'Có lỗi xảy ra khi quay thưởng');
        
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
    
    if (reward.reward_type === 'no_prize' || reward.reward_name === 'Chúc may mắn lần sau!') {
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

// Show multiple results summary
function showMultipleResults(rewards, quantity) {
    const modal = document.getElementById('result-modal');
    const resultText = document.getElementById('result-text');
    
    // Count rewards by type
    const rewardCounts = {};
    let totalWon = 0;
    
    rewards.forEach(reward => {
        const name = reward.reward_name || 'Không xác định';
        if (name === 'Chúc may mắn lần sau!') {
            rewardCounts['Chúc may mắn lần sau!'] = (rewardCounts['Chúc may mắn lần sau!'] || 0) + 1;
        } else {
            if (!rewardCounts[name]) {
                rewardCounts[name] = { count: 0, value: reward.reward_value };
            }
            rewardCounts[name].count++;
            totalWon++;
        }
    });
    
    let summaryHTML = `
        <div class="text-4xl mb-4">🎉</div>
        <div class="text-xl font-bold text-gray-800 dark:text-white mb-4">Đã quay thành công ${quantity} vé!</div>
        <div class="text-left space-y-2 mb-4 max-h-60 overflow-y-auto">
    `;
    
    Object.entries(rewardCounts).forEach(([name, data]) => {
        const count = typeof data === 'number' ? data : data.count;
        const value = typeof data === 'number' ? null : data.value;
        const isNoPrize = name === 'Chúc may mắn lần sau!';
        
        summaryHTML += `
            <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                <span class="text-sm ${isNoPrize ? 'text-gray-600 dark:text-gray-400' : 'text-purple-600 dark:text-purple-400 font-semibold'}">
                    ${isNoPrize ? '😢' : '🎁'} ${name}
                </span>
                <span class="text-sm font-bold text-gray-800 dark:text-white">x${count}</span>
            </div>
        `;
    });
    
    summaryHTML += `
        </div>
        <div class="text-center">
            <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                Tổng phần thưởng: ${totalWon}/${quantity}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Kiểm tra phần thưởng chi tiết tại <a href="my-rewards.html" class="text-purple-600 dark:text-purple-400 underline">Phần Thưởng Của Tôi</a>
            </div>
        </div>
    `;
    
    resultText.innerHTML = summaryHTML;
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
    
    // Initialize spin mode handlers
    const spinModeInputs = document.querySelectorAll('input[name="spin-mode"]');
    spinModeInputs.forEach(input => {
        input.addEventListener('change', handleSpinModeChange);
    });
    
    // Initialize custom quantity input max attribute
    const spinQuantityInput = document.getElementById('spin-quantity');
    if (spinQuantityInput && availableTickets > 0) {
        spinQuantityInput.setAttribute('max', availableTickets);
    }
});
