// Lottery Wheel JavaScript
class LotteryWheel {
    constructor() {
        this.wheel = document.getElementById('lottery-wheel');
        this.spinButton = document.getElementById('spin-button');
        this.spinText = document.getElementById('spin-text');
        this.ticketCount = document.getElementById('ticket-count');
        this.resultModal = document.getElementById('result-modal');
        this.resultText = document.getElementById('result-text');
        
        this.prizes = [
            { name: 'Giảm 10%', description: 'Giảm giá cho đơn hàng tiếp theo', color: '#FF6B6B' },
            { name: 'Giảm 20%', description: 'Giảm giá cho đơn hàng tiếp theo', color: '#4ECDC4' },
            { name: 'Miễn phí vận chuyển', description: 'Áp dụng cho đơn hàng tiếp theo', color: '#45B7D1' },
            { name: 'Tặng kèm phụ kiện', description: 'Nhận thêm phụ kiện miễn phí', color: '#96CEB4' },
            { name: 'Giảm 50%', description: 'Giảm giá lớn cho đơn hàng tiếp theo', color: '#FFEAA7' },
            { name: 'Chúc may mắn lần sau!', description: 'Hãy thử lại lần sau nhé!', color: '#DDA0DD' }
        ];
        
        this.isSpinning = false;
        this.currentRotation = 0;
        
        this.init();
    }
    
    init() {
        this.createWheelSections();
        this.loadTicketCount();
        this.bindEvents();
    }
    
    createWheelSections() {
        const sectionAngle = 360 / this.prizes.length;
        
        this.prizes.forEach((prize, index) => {
            const section = document.createElement('div');
            section.className = 'wheel-section';
            section.style.transform = `rotate(${index * sectionAngle}deg)`;
            section.style.background = `linear-gradient(${index * sectionAngle}deg, ${prize.color}, ${this.darkenColor(prize.color, 20)})`;
            section.style.clipPath = 'polygon(0 0, 100% 0, 50% 100%)';
            section.innerHTML = `
                <div style="transform: rotate(${-index * sectionAngle}deg); font-size: 12px; text-align: center; padding: 10px;">
                    ${prize.name}
                </div>
            `;
            this.wheel.appendChild(section);
        });
    }
    
    darkenColor(color, percent) {
        const num = parseInt(color.replace("#", ""), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) - amt;
        const G = (num >> 8 & 0x00FF) - amt;
        const B = (num & 0x0000FF) - amt;
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }
    
    bindEvents() {
        this.spinButton.addEventListener('click', () => {
            this.spin();
        });
    }
    
    async loadTicketCount() {
        try {
            const response = await fetch('../api/get_lottery_tickets.php', {
                credentials: 'include'
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.ticketCount.textContent = result.data.total_tickets;
                    
                    // Disable spin button if no tickets
                    if (result.data.total_tickets === 0) {
                        this.spinButton.disabled = true;
                        this.spinText.textContent = 'Không có vé quay';
                    }
                }
            }
        } catch (error) {
            console.error('Error loading ticket count:', error);
        }
    }
    
    async spin() {
        if (this.isSpinning || this.ticketCount.textContent === '0') {
            return;
        }
        
        this.isSpinning = true;
        this.spinButton.disabled = true;
        this.spinText.textContent = 'Đang quay...';
        
        // Random rotation (5-10 full rotations + random angle)
        const randomRotations = 5 + Math.random() * 5;
        const randomAngle = Math.random() * 360;
        const totalRotation = randomRotations * 360 + randomAngle;
        
        this.currentRotation += totalRotation;
        this.wheel.style.transform = `rotate(${this.currentRotation}deg)`;
        this.wheel.classList.add('spinning');
        
        // Calculate which prize was selected
        const normalizedAngle = (360 - (randomAngle % 360)) % 360;
        const prizeIndex = Math.floor(normalizedAngle / (360 / this.prizes.length));
        const selectedPrize = this.prizes[prizeIndex];
        
        // Wait for animation to complete
        setTimeout(() => {
            this.wheel.classList.remove('spinning');
            this.showResult(selectedPrize);
            this.useTicket();
        }, 4000);
    }
    
    showResult(prize) {
        this.resultText.textContent = `Bạn đã nhận được: ${prize.name} - ${prize.description}`;
        this.resultModal.classList.remove('hidden');
        
        this.isSpinning = false;
        this.spinButton.disabled = false;
        this.spinText.textContent = 'Quay Ngay!';
    }
    
    async useTicket() {
        try {
            const response = await fetch('../api/use_lottery_ticket.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    // Update ticket count
                    this.loadTicketCount();
                }
            }
        } catch (error) {
            console.error('Error using ticket:', error);
        }
    }
}

// Initialize lottery wheel when page loads
document.addEventListener('DOMContentLoaded', () => {
    new LotteryWheel();
});
