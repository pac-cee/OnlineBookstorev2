class ProgressTracker {
    constructor() {
        this.charts = {};
        this.initializeProgress();
    }

    initializeProgress() {
        this.initializeCharts();
        this.initializeAchievements();
        this.animateStats();
    }

    initializeCharts() {
        // Reading Progress Chart
        this.createProgressChart('readingProgress', {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            data: [10, 15, 12, 25, 18, 30],
            title: 'Reading Progress'
        });

        // Quiz Performance Chart
        this.createProgressChart('quizProgress', {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            data: [75, 82, 88, 95],
            title: 'Quiz Performance'
        });
    }

    createProgressChart(elementId, config) {
        const chartElement = document.getElementById(elementId);
        if (!chartElement) return;

        const ctx = chartElement.getContext('2d');
        this.charts[elementId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: config.labels,
                datasets: [{
                    label: config.title,
                    data: config.data,
                    borderColor: getComputedStyle(document.documentElement)
                        .getPropertyValue('--primary').trim(),
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(108, 99, 255, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: config.title
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    animateStats() {
        const stats = document.querySelectorAll('.stat-value');
        stats.forEach(stat => {
            const target = parseInt(stat.dataset.value);
            this.animateNumber(stat, target);
        });
    }

    animateNumber(element, target) {
        let current = 0;
        const increment = target / 50; // Adjust for animation speed
        const interval = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            element.textContent = Math.round(current);
        }, 20);
    }

    initializeAchievements() {
        const achievements = document.querySelectorAll('.achievement-card');
        achievements.forEach(achievement => {
            if (!achievement.classList.contains('locked')) {
                achievement.addEventListener('click', () => {
                    this.showAchievementDetails(achievement);
                });
            }
        });
    }

    showAchievementDetails(achievement) {
        const title = achievement.querySelector('.achievement-title').textContent;
        const desc = achievement.querySelector('.achievement-desc').textContent;
        const date = achievement.dataset.date;

        const modal = document.createElement('div');
        modal.className = 'achievement-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>${title}</h3>
                <p>${desc}</p>
                <p class="achievement-date">Unlocked on: ${date}</p>
                <button class="modal-close">Close</button>
            </div>
        `;

        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('show'), 10);

        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.classList.remove('show');
            setTimeout(() => modal.remove(), 300);
        });
    }

    updateProgress(progressData) {
        // Update progress bars
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const target = bar.dataset.target;
            if (progressData[target]) {
                const fill = bar.querySelector('.progress-fill');
                fill.style.width = progressData[target] + '%';
            }
        });

        // Update charts
        Object.keys(this.charts).forEach(chartId => {
            if (progressData[chartId]) {
                this.charts[chartId].data.datasets[0].data = progressData[chartId];
                this.charts[chartId].update();
            }
        });
    }
}

// Initialize progress tracking when document is ready
document.addEventListener('DOMContentLoaded', () => {
    const progressTracker = new ProgressTracker();

    // Example of updating progress with new data
    // This would typically come from an API call
    const updateButton = document.querySelector('.update-progress');
    if (updateButton) {
        updateButton.addEventListener('click', async () => {
            try {
                const response = await fetch('update_progress.php');
                const data = await response.json();
                progressTracker.updateProgress(data);
            } catch (error) {
                console.error('Error updating progress:', error);
            }
        });
    }
});
