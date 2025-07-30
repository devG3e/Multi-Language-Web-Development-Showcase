
// ===== PORTFOLIO JAVASCRIPT =====

// Portfolio data
const portfolioData = [
    {
        id: 1,
        title: "E-Commerce Platform",
        description: "A full-stack e-commerce solution built with modern web technologies. Features include user authentication, product management, shopping cart, and payment processing.",
        image: "🛒",
        technologies: ["React", "Node.js", "MongoDB", "Stripe"],
        category: "web-app",
        demoUrl: "#",
        codeUrl: "#",
        featured: true
    },
    {
        id: 2,
        title: "Task Management System",
        description: "A comprehensive task management application with real-time collaboration, project tracking, and team management features.",
        image: "📋",
        technologies: ["Vue.js", "Python", "PostgreSQL", "Socket.io"],
        category: "web-app",
        demoUrl: "#",
        codeUrl: "#",
        featured: true
    },
    {
        id: 3,
        title: "Data Visualization Dashboard",
        description: "Interactive dashboard for data analysis and visualization with real-time charts, graphs, and reporting capabilities.",
        image: "📊",
        technologies: ["D3.js", "Python", "Flask", "Chart.js"],
        category: "data",
        demoUrl: "#",
        codeUrl: "#",
        featured: false
    },
    {
        id: 4,
        title: "Mobile Fitness App",
        description: "Cross-platform mobile application for fitness tracking, workout planning, and health monitoring.",
        image: "💪",
        technologies: ["React Native", "Firebase", "Redux", "Expo"],
        category: "mobile",
        demoUrl: "#",
        codeUrl: "#",
        featured: true
    },
    {
        id: 5,
        title: "Blog Platform",
        description: "Content management system for blogging with rich text editor, SEO optimization, and social media integration.",
        image: "📝",
        technologies: ["PHP", "MySQL", "JavaScript", "Bootstrap"],
        category: "web-app",
        demoUrl: "#",
        codeUrl: "#",
        featured: false
    },
    {
        id: 6,
        title: "Weather Application",
        description: "Real-time weather application with location-based forecasts, interactive maps, and weather alerts.",
        image: "🌤️",
        technologies: ["JavaScript", "OpenWeather API", "Leaflet.js", "CSS3"],
        category: "web-app",
        demoUrl: "#",
        codeUrl: "#",
        featured: false
    },
    {
        id: 7,
        title: "Inventory Management System",
        description: "Enterprise-level inventory management solution with barcode scanning, reporting, and analytics.",
        image: "📦",
        technologies: ["C#", "ASP.NET", "SQL Server", "Entity Framework"],
        category: "enterprise",
        demoUrl: "#",
        codeUrl: "#",
        featured: true
    },
    {
        id: 8,
        title: "Code Snippet Repository",
        description: "Web-based platform for sharing and organizing code snippets with syntax highlighting and search functionality.",
        image: "💻",
        technologies: ["Perl", "MySQL", "JavaScript", "Prism.js"],
        category: "tools",
        demoUrl: "#",
        codeUrl: "#",
        featured: false
    },
    {
        id: 9,
        title: "Social Media Dashboard",
        description: "Analytics dashboard for social media management with scheduling, monitoring, and reporting features.",
        image: "📱",
        technologies: ["Ruby on Rails", "PostgreSQL", "Redis", "Sidekiq"],
        category: "web-app",
        demoUrl: "#",
        codeUrl: "#",
        featured: false
    }
];

// Categories for filtering
const categories = [
    { id: 'all', name: 'All Projects', count: portfolioData.length },
    { id: 'web-app', name: 'Web Applications', count: portfolioData.filter(p => p.category === 'web-app').length },
    { id: 'mobile', name: 'Mobile Apps', count: portfolioData.filter(p => p.category === 'mobile').length },
    { id: 'data', name: 'Data & Analytics', count: portfolioData.filter(p => p.category === 'data').length },
    { id: 'enterprise', name: 'Enterprise', count: portfolioData.filter(p => p.category === 'enterprise').length },
    { id: 'tools', name: 'Developer Tools', count: portfolioData.filter(p => p.category === 'tools').length }
];

// Portfolio class
class Portfolio {
    constructor() {
        this.currentFilter = 'all';
        this.currentSearch = '';
        this.portfolioGrid = document.getElementById('portfolio-grid');
        this.init();
    }

    init() {
        this.createFilterButtons();
        this.createSearchBar();
        this.renderPortfolio();
        this.bindEvents();
    }

    createFilterButtons() {
        const filterContainer = document.createElement('div');
        filterContainer.className = 'filter-tags';
        filterContainer.id = 'portfolio-filters';

        categories.forEach(category => {
            const filterBtn = document.createElement('button');
            filterBtn.className = `filter-tag ${category.id === 'all' ? 'active' : ''}`;
            filterBtn.setAttribute('data-filter', category.id);
            filterBtn.textContent = `${category.name} (${category.count})`;
            filterContainer.appendChild(filterBtn);
        });

        // Insert before portfolio grid
        this.portfolioGrid.parentNode.insertBefore(filterContainer, this.portfolioGrid);
    }

    createSearchBar() {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'search-bar';
        searchContainer.innerHTML = `
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="portfolio-search" placeholder="Search projects...">
        `;

        // Insert before filters
        const filters = document.getElementById('portfolio-filters');
        filters.parentNode.insertBefore(searchContainer, filters);
    }

    renderPortfolio() {
        const filteredData = this.getFilteredData();
        
        if (filteredData.length === 0) {
            this.portfolioGrid.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3>No projects found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                </div>
            `;
            return;
        }

        this.portfolioGrid.innerHTML = filteredData.map(project => this.createProjectCard(project)).join('');
    }

    createProjectCard(project) {
        const techTags = project.technologies.map(tech => `<span>${tech}</span>`).join('');
        
        return `
            <div class="portfolio-item" data-category="${project.category}" data-id="${project.id}">
                <div class="portfolio-image">
                    <div class="portfolio-overlay">
                        <i class="fas fa-external-link-alt"></i>
                    </div>
                    <span style="font-size: 4rem;">${project.image}</span>
                </div>
                <div class="portfolio-content">
                    <h3 class="portfolio-title">${project.title}</h3>
                    <p class="portfolio-description">${project.description}</p>
                    <div class="portfolio-tech">
                        ${techTags}
                    </div>
                    <div class="portfolio-links">
                        <a href="${project.demoUrl}" class="demo" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Demo
                        </a>
                        <a href="${project.codeUrl}" class="code" target="_blank">
                            <i class="fab fa-github"></i> Code
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    getFilteredData() {
        let filtered = portfolioData;

        // Apply category filter
        if (this.currentFilter !== 'all') {
            filtered = filtered.filter(project => project.category === this.currentFilter);
        }

        // Apply search filter
        if (this.currentSearch.trim()) {
            const searchTerm = this.currentSearch.toLowerCase();
            filtered = filtered.filter(project => 
                project.title.toLowerCase().includes(searchTerm) ||
                project.description.toLowerCase().includes(searchTerm) ||
                project.technologies.some(tech => tech.toLowerCase().includes(searchTerm))
            );
        }

        return filtered;
    }

    bindEvents() {
        // Filter button clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('filter-tag')) {
                this.handleFilterClick(e.target);
            }
        });

        // Search input
        const searchInput = document.getElementById('portfolio-search');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.handleSearch(e.target.value);
            }, 300));
        }

        // Portfolio item clicks
        this.portfolioGrid.addEventListener('click', (e) => {
            const portfolioItem = e.target.closest('.portfolio-item');
            if (portfolioItem) {
                this.handlePortfolioClick(portfolioItem);
            }
        });
    }

    handleFilterClick(filterBtn) {
        // Update active filter
        document.querySelectorAll('.filter-tag').forEach(btn => btn.classList.remove('active'));
        filterBtn.classList.add('active');

        // Update current filter
        this.currentFilter = filterBtn.getAttribute('data-filter');

        // Re-render portfolio
        this.renderPortfolio();

        // Update URL
        this.updateURL();
    }

    handleSearch(searchTerm) {
        this.currentSearch = searchTerm;
        this.renderPortfolio();
        this.updateURL();
    }

    handlePortfolioClick(portfolioItem) {
        const projectId = portfolioItem.getAttribute('data-id');
        const project = portfolioData.find(p => p.id == projectId);
        
        if (project) {
            this.showProjectModal(project);
        }
    }

    showProjectModal(project) {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div class="modal-header">
                    <h2>${project.title}</h2>
                    <div class="project-meta">
                        <span class="badge badge-primary">${project.category}</span>
                        ${project.featured ? '<span class="badge badge-accent">Featured</span>' : ''}
                    </div>
                </div>
                <div class="modal-body">
                    <div class="project-image" style="text-align: center; margin-bottom: 1rem;">
                        <span style="font-size: 4rem;">${project.image}</span>
                    </div>
                    <p>${project.description}</p>
                    <div class="project-tech">
                        <h4>Technologies Used:</h4>
                        <div class="tech-tags">
                            ${project.technologies.map(tech => `<span class="tech-tag">${tech}</span>`).join('')}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="${project.demoUrl}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Demo
                    </a>
                    <a href="${project.codeUrl}" class="btn btn-secondary" target="_blank">
                        <i class="fab fa-github"></i> View Code
                    </a>
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

    updateURL() {
        const params = new URLSearchParams();
        if (this.currentFilter !== 'all') {
            params.set('filter', this.currentFilter);
        }
        if (this.currentSearch) {
            params.set('search', this.currentSearch);
        }

        const newURL = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
        window.history.pushState({}, '', newURL);
    }

    // Debounce utility function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public methods for external use
    filterByCategory(category) {
        const filterBtn = document.querySelector(`[data-filter="${category}"]`);
        if (filterBtn) {
            this.handleFilterClick(filterBtn);
        }
    }

    searchProjects(searchTerm) {
        const searchInput = document.getElementById('portfolio-search');
        if (searchInput) {
            searchInput.value = searchTerm;
            this.handleSearch(searchTerm);
        }
    }

    getProjectById(id) {
        return portfolioData.find(project => project.id == id);
    }

    getProjectsByCategory(category) {
        return portfolioData.filter(project => project.category === category);
    }

    getFeaturedProjects() {
        return portfolioData.filter(project => project.featured);
    }
}

// Initialize portfolio when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if portfolio section exists
    const portfolioSection = document.getElementById('portfolio');
    if (portfolioSection) {
        window.portfolio = new Portfolio();
    }
});

// Export for use in other modules
window.Portfolio = Portfolio; 