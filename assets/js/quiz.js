class QuizManager {
    constructor() {
        this.currentQuestion = 0;
        this.answers = [];
        this.questions = [];
        this.timeLeft = 0;
        this.timer = null;
        
        this.initializeQuiz();
    }

    initializeQuiz() {
        this.loadQuestions();
        this.setupEventListeners();
        this.updateProgress();
        this.startTimer();
    }

    loadQuestions() {
        const quizContainer = document.querySelector('.quiz-container');
        if (!quizContainer) return;

        // Get quiz data from data attribute or fetch from server
        const quizData = quizContainer.dataset.quiz;
        if (quizData) {
            this.questions = JSON.parse(quizData);
            this.renderQuestion();
        }
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            const nextButton = document.querySelector('.quiz-button.next');
            const prevButton = document.querySelector('.quiz-button.prev');
            const submitButton = document.querySelector('.quiz-button.submit');

            if (nextButton) {
                nextButton.addEventListener('click', () => this.nextQuestion());
            }
            if (prevButton) {
                prevButton.addEventListener('click', () => this.previousQuestion());
            }
            if (submitButton) {
                submitButton.addEventListener('click', () => this.submitQuiz());
            }

            // Setup option selection
            document.querySelectorAll('.option-button').forEach(button => {
                button.addEventListener('click', (e) => this.selectOption(e));
            });
        });
    }

    renderQuestion() {
        const question = this.questions[this.currentQuestion];
        if (!question) return;

        const questionText = document.querySelector('.question-text');
        const optionsGrid = document.querySelector('.options-grid');

        if (questionText) {
            questionText.textContent = question.text;
        }

        if (optionsGrid) {
            optionsGrid.innerHTML = question.options.map((option, index) => `
                <button class="option-button ${this.answers[this.currentQuestion] === index ? 'selected' : ''}" 
                        data-index="${index}">
                    ${option}
                </button>
            `).join('');

            // Reattach event listeners
            optionsGrid.querySelectorAll('.option-button').forEach(button => {
                button.addEventListener('click', (e) => this.selectOption(e));
            });
        }

        this.updateProgress();
        this.updateNavigation();
    }

    selectOption(event) {
        const selectedIndex = parseInt(event.target.dataset.index);
        this.answers[this.currentQuestion] = selectedIndex;

        // Update UI
        document.querySelectorAll('.option-button').forEach(button => {
            button.classList.remove('selected');
        });
        event.target.classList.add('selected');

        // Enable next button
        const nextButton = document.querySelector('.quiz-button.next');
        if (nextButton) {
            nextButton.disabled = false;
        }
    }

    nextQuestion() {
        if (this.currentQuestion < this.questions.length - 1) {
            this.currentQuestion++;
            this.renderQuestion();
        }
    }

    previousQuestion() {
        if (this.currentQuestion > 0) {
            this.currentQuestion--;
            this.renderQuestion();
        }
    }

    updateProgress() {
        const progress = ((this.currentQuestion + 1) / this.questions.length) * 100;
        const progressBar = document.querySelector('.progress-fill');
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }

        const progressText = document.querySelector('.progress-text');
        if (progressText) {
            progressText.textContent = `Question ${this.currentQuestion + 1} of ${this.questions.length}`;
        }
    }

    updateNavigation() {
        const prevButton = document.querySelector('.quiz-button.prev');
        const nextButton = document.querySelector('.quiz-button.next');
        const submitButton = document.querySelector('.quiz-button.submit');

        if (prevButton) {
            prevButton.disabled = this.currentQuestion === 0;
        }

        if (nextButton) {
            nextButton.style.display = this.currentQuestion === this.questions.length - 1 ? 'none' : 'block';
        }

        if (submitButton) {
            submitButton.style.display = this.currentQuestion === this.questions.length - 1 ? 'block' : 'none';
        }
    }

    startTimer() {
        const timerDisplay = document.querySelector('.timer-display');
        if (!timerDisplay) return;

        this.timeLeft = parseInt(timerDisplay.dataset.time) || 600; // 10 minutes default
        this.updateTimerDisplay();

        this.timer = setInterval(() => {
            this.timeLeft--;
            this.updateTimerDisplay();

            if (this.timeLeft <= 0) {
                this.submitQuiz();
            }
        }, 1000);
    }

    updateTimerDisplay() {
        const timerDisplay = document.querySelector('.timer-display');
        if (!timerDisplay) return;

        const minutes = Math.floor(this.timeLeft / 60);
        const seconds = this.timeLeft % 60;
        timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (this.timeLeft <= 60) {
            timerDisplay.classList.add('warning');
        }
    }

    async submitQuiz() {
        clearInterval(this.timer);

        const unanswered = this.answers.length < this.questions.length;
        if (unanswered && !confirm('You have unanswered questions. Submit anyway?')) {
            return;
        }

        try {
            const response = await fetch('submit_quiz.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    answers: this.answers,
                    timeSpent: 600 - this.timeLeft
                })
            });

            if (response.ok) {
                const result = await response.json();
                this.showResults(result);
            } else {
                throw new Error('Failed to submit quiz');
            }
        } catch (error) {
            console.error('Error submitting quiz:', error);
            alert('Failed to submit quiz. Please try again.');
        }
    }

    showResults(result) {
        const quizContainer = document.querySelector('.quiz-container');
        if (!quizContainer) return;

        const percentage = Math.round((result.score / this.questions.length) * 100);
        
        quizContainer.innerHTML = `
            <div class="quiz-results">
                <h2>Quiz Complete!</h2>
                <div class="score-circle">${percentage}%</div>
                <p class="result-message">
                    You answered ${result.score} out of ${this.questions.length} questions correctly.
                </p>
                <div class="quiz-controls">
                    <a href="quiz.php" class="quiz-button">Take Another Quiz</a>
                    <a href="quiz_results.php" class="quiz-button next">View All Results</a>
                </div>
            </div>
        `;
    }
}

// Initialize quiz when document is ready
document.addEventListener('DOMContentLoaded', () => {
    const quiz = new QuizManager();
});
