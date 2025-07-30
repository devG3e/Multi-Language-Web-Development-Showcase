// ===== MEMORY GAME JAVASCRIPT =====

class MemoryGame {
    constructor() {
        this.cards = [];
        this.flippedCards = [];
        this.matchedPairs = 0;
        this.moves = 0;
        this.score = 0;
        this.gameStarted = false;
        this.gameTimer = null;
        this.startTime = null;
        this.totalPairs = 8;
        
        // Score persistence
        this.highScore = this.loadHighScore();
        this.totalGamesPlayed = this.loadTotalGames();
        this.totalScore = this.loadTotalScore();
        
        // Game board elements
        this.gameBoard = document.getElementById('game-board');
        this.scoreElement = document.getElementById('score');
        this.movesElement = document.getElementById('moves');
        this.timerElement = document.getElementById('timer');
        this.newGameBtn = document.getElementById('new-game-btn');
        this.highScoreElement = document.getElementById('high-score');
        
        // Card symbols for the game
        this.symbols = ['🎮', '🎲', '🎯', '🎪', '🎨', '🎭', '🎪', '🎯'];
        
        this.init();
    }

    init() {
        this.createCards();
        this.bindEvents();
        this.updateDisplay();
        this.updateHighScoreDisplay();
    }

    // Score persistence methods
    loadHighScore() {
        return parseInt(localStorage.getItem('memoryGameHighScore')) || 0;
    }

    saveHighScore(score) {
        if (score > this.highScore) {
            this.highScore = score;
            localStorage.setItem('memoryGameHighScore', score.toString());
            return true;
        }
        return false;
    }

    loadTotalGames() {
        return parseInt(localStorage.getItem('memoryGameTotalGames')) || 0;
    }

    saveTotalGames() {
        this.totalGamesPlayed++;
        localStorage.setItem('memoryGameTotalGames', this.totalGamesPlayed.toString());
    }

    loadTotalScore() {
        return parseInt(localStorage.getItem('memoryGameTotalScore')) || 0;
    }

    saveTotalScore(score) {
        this.totalScore += score;
        localStorage.setItem('memoryGameTotalScore', this.totalScore.toString());
    }

    updateHighScoreDisplay() {
        if (this.highScoreElement) {
            this.highScoreElement.textContent = this.highScore;
        }
    }

    createCards() {
        // Create pairs of cards
        const cardPairs = [];
        this.symbols.forEach((symbol, index) => {
            cardPairs.push({ id: index * 2, symbol: symbol, matched: false });
            cardPairs.push({ id: index * 2 + 1, symbol: symbol, matched: false });
        });

        // Shuffle the cards
        this.cards = this.shuffleArray(cardPairs);
        
        // Clear the game board
        this.gameBoard.innerHTML = '';
        
        // Create card elements
        this.cards.forEach((card, index) => {
            const cardElement = this.createCardElement(card, index);
            this.gameBoard.appendChild(cardElement);
        });
    }

    createCardElement(card, index) {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'game-card';
        cardDiv.setAttribute('data-index', index);
        cardDiv.setAttribute('data-id', card.id);
        
        cardDiv.innerHTML = `
            <div class="game-card-front">
                <i class="fas fa-question"></i>
            </div>
            <div class="game-card-back">
                ${card.symbol}
            </div>
        `;
        
        return cardDiv;
    }

    bindEvents() {
        // Card click events
        this.gameBoard.addEventListener('click', (e) => {
            const cardElement = e.target.closest('.game-card');
            if (cardElement && !cardElement.classList.contains('flipped') && !cardElement.classList.contains('matched')) {
                this.flipCard(cardElement);
            }
        });

        // New game button
        if (this.newGameBtn) {
            this.newGameBtn.addEventListener('click', () => {
                this.resetGame();
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'r' || e.key === 'R') {
                this.resetGame();
            }
            if (e.key === 's' || e.key === 'S') {
                this.showStatistics();
            }
        });
    }

    flipCard(cardElement) {
        // Start the game timer on first card flip
        if (!this.gameStarted) {
            this.startGame();
        }

        // Prevent flipping if already processing
        if (this.flippedCards.length >= 2) {
            return;
        }

        // Flip the card
        cardElement.classList.add('flipped');
        this.flippedCards.push(cardElement);

        // Check if we have two cards flipped
        if (this.flippedCards.length === 2) {
            this.moves++;
            this.updateDisplay();
            this.checkMatch();
        }
    }

    checkMatch() {
        const [card1, card2] = this.flippedCards;
        const card1Id = card1.getAttribute('data-id');
        const card2Id = card2.getAttribute('data-id');

        // Check if cards match
        if (card1Id === card2Id) {
            // Match found
            setTimeout(() => {
                card1.classList.add('matched');
                card2.classList.add('matched');
                this.matchedPairs++;
                this.score += 10;
                this.updateDisplay();
                this.flippedCards = [];

                // Check if game is complete
                if (this.matchedPairs === this.totalPairs) {
                    this.endGame();
                }
            }, 500);
        } else {
            // No match
            setTimeout(() => {
                card1.classList.remove('flipped');
                card2.classList.remove('flipped');
                this.flippedCards = [];
            }, 1000);
        }
    }

    startGame() {
        this.gameStarted = true;
        this.startTime = Date.now();
        this.gameTimer = setInterval(() => {
            this.updateTimer();
        }, 1000);
    }

    updateTimer() {
        if (this.startTime) {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (this.timerElement) {
                this.timerElement.textContent = timeString;
            }
        }
    }

    endGame() {
        clearInterval(this.gameTimer);
        this.gameStarted = false;
        
        // Calculate final score based on time and moves
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const timeBonus = Math.max(0, 300 - elapsed) * 2; // Bonus for completing under 5 minutes
        const moveBonus = Math.max(0, 50 - this.moves) * 5; // Bonus for fewer moves
        this.score += timeBonus + moveBonus;
        
        // Save game statistics
        this.saveTotalGames();
        this.saveTotalScore(this.score);
        const isNewHighScore = this.saveHighScore(this.score);
        
        this.updateDisplay();
        this.updateHighScoreDisplay();
        
        // Show completion modal
        setTimeout(() => {
            this.showCompletionModal(isNewHighScore);
        }, 500);
    }

    showCompletionModal(isNewHighScore = false) {
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div class="modal-header">
                    <h2>🎉 Congratulations! 🎉</h2>
                </div>
                <div class="modal-body">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <i class="fas fa-trophy" style="font-size: 4rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                        <h3>You completed the memory game!</h3>
                        ${isNewHighScore ? '<div class="new-high-score">🏆 NEW HIGH SCORE! 🏆</div>' : ''}
                    </div>
                    <div class="game-stats">
                        <div class="stat-item">
                            <span class="stat-label">Final Score:</span>
                            <span class="stat-value">${this.score}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">High Score:</span>
                            <span class="stat-value">${this.highScore}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Time:</span>
                            <span class="stat-value">${timeString}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Moves:</span>
                            <span class="stat-value">${this.moves}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Pairs Found:</span>
                            <span class="stat-value">${this.matchedPairs}/${this.totalPairs}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Games Played:</span>
                            <span class="stat-value">${this.totalGamesPlayed}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Score:</span>
                            <span class="stat-value">${this.totalScore}</span>
                        </div>
                    </div>
                    <div class="performance-rating">
                        ${this.getPerformanceRating()}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="window.memoryGame.resetGame()">
                        <i class="fas fa-redo"></i> Play Again
                    </button>
                    <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal functionality
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => {
            modal.remove();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    getPerformanceRating() {
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
        const efficiency = (this.moves / this.totalPairs) + (elapsed / 60);
        
        if (efficiency < 3) {
            return '<div class="rating excellent">⭐ Excellent Performance! ⭐</div>';
        } else if (efficiency < 5) {
            return '<div class="rating good">👍 Good Job!</div>';
        } else if (efficiency < 7) {
            return '<div class="rating average">😊 Nice Try!</div>';
        } else {
            return '<div class="rating poor">💪 Keep Practicing!</div>';
        }
    }

    resetGame() {
        // Clear timer
        if (this.gameTimer) {
            clearInterval(this.gameTimer);
        }

        // Reset game state
        this.flippedCards = [];
        this.matchedPairs = 0;
        this.moves = 0;
        this.score = 0;
        this.gameStarted = false;
        this.startTime = null;

        // Recreate cards
        this.createCards();
        this.updateDisplay();

        // Close any open modals
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => modal.remove());
    }

    updateDisplay() {
        if (this.scoreElement) {
            this.scoreElement.textContent = this.score;
        }
        if (this.movesElement) {
            this.movesElement.textContent = this.moves;
        }
    }

    shuffleArray(array) {
        const shuffled = [...array];
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        return shuffled;
    }

    // Public methods for external use
    getGameStats() {
        return {
            score: this.score,
            moves: this.moves,
            matchedPairs: this.matchedPairs,
            totalPairs: this.totalPairs,
            gameStarted: this.gameStarted,
            startTime: this.startTime
        };
    }

    pauseGame() {
        if (this.gameTimer) {
            clearInterval(this.gameTimer);
        }
    }

    resumeGame() {
        if (this.gameStarted && this.startTime) {
            this.gameTimer = setInterval(() => {
                this.updateTimer();
            }, 1000);
        }
    }

    setDifficulty(level) {
        const difficulties = {
            easy: { pairs: 6, symbols: ['🎮', '🎲', '🎯', '🎪', '🎨', '🎭'] },
            medium: { pairs: 8, symbols: ['🎮', '🎲', '🎯', '🎪', '🎨', '🎭', '🎪', '🎯'] },
            hard: { pairs: 12, symbols: ['🎮', '🎲', '🎯', '🎪', '🎨', '🎭', '🎪', '🎯', '🎪', '🎨', '🎭', '🎪'] }
        };

        const difficulty = difficulties[level];
        if (difficulty) {
            this.totalPairs = difficulty.pairs;
            this.symbols = difficulty.symbols;
            this.resetGame();
        }
    }

    resetStatistics() {
        this.highScore = 0;
        this.totalGamesPlayed = 0;
        this.totalScore = 0;
        
        localStorage.removeItem('memoryGameHighScore');
        localStorage.removeItem('memoryGameTotalGames');
        localStorage.removeItem('memoryGameTotalScore');
        
        this.updateHighScoreDisplay();
        this.updateDisplay();
    }

    showStatistics() {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div class="modal-header">
                    <h2>📊 Game Statistics</h2>
                </div>
                <div class="modal-body">
                    <div class="game-stats">
                        <div class="stat-item">
                            <span class="stat-label">High Score:</span>
                            <span class="stat-value">${this.highScore}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Games Played:</span>
                            <span class="stat-value">${this.totalGamesPlayed}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Score:</span>
                            <span class="stat-value">${this.totalScore}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Average Score:</span>
                            <span class="stat-value">${this.totalGamesPlayed > 0 ? Math.round(this.totalScore / this.totalGamesPlayed) : 0}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="window.memoryGame.resetStatistics(); this.closest('.modal').remove();">
                        <i class="fas fa-trash"></i> Reset Statistics
                    </button>
                    <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal functionality
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => {
            modal.remove();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
}

// Initialize game when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if game section exists
    const gameSection = document.getElementById('game');
    if (gameSection) {
        window.memoryGame = new MemoryGame();
    }
});

// Add CSS for game-specific styles
const gameStyles = `
    .game-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .new-high-score {
        background: linear-gradient(45deg, #f59e0b, #fbbf24);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        font-weight: bold;
        font-size: 1.1rem;
        margin: 1rem 0;
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
        margin: 1rem 0;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        background: var(--background-secondary);
        border-radius: var(--radius-md);
    }

    .stat-label {
        font-weight: 500;
        color: var(--text-secondary);
    }

    .stat-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    .performance-rating {
        text-align: center;
        margin-top: 1rem;
        padding: 1rem;
        border-radius: var(--radius-md);
    }

    .rating.excellent {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
        font-weight: 600;
    }

    .rating.good {
        background: rgba(99, 102, 241, 0.1);
        color: #3730a3;
        font-weight: 600;
    }

    .rating.average {
        background: rgba(245, 158, 11, 0.1);
        color: #92400e;
        font-weight: 600;
    }

    .rating.poor {
        background: rgba(239, 68, 68, 0.1);
        color: #991b1b;
        font-weight: 600;
    }

    .game-card {
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .game-card:hover {
        transform: scale(1.05);
    }

    .game-card.flipped {
        transform: rotateY(180deg);
    }

    .game-card.matched {
        cursor: default;
        opacity: 0.7;
    }

    .game-card.matched:hover {
        transform: none;
    }
`;

// Inject styles if not already present
if (!document.querySelector('#game-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'game-styles';
    styleSheet.textContent = gameStyles;
    document.head.appendChild(styleSheet);
}

// Export for use in other modules
window.MemoryGame = MemoryGame; 