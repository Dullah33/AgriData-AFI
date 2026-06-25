<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agri Data - Smart Insights. Stronger Harvests.</title>
    
    <!-- Custom Style Sheet in Assets Folder -->
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
</head>
<body>

    <!-- Header / Navigation -->
    <header class="header">
        <nav class="navbar" id="navbar">
            <a href="#" class="logo">
                <!-- Leaf Icon -->
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C12 2 6 7 6 12C6 15.3137 8.68629 18 12 18C15.3137 18 18 15.3137 18 12C18 7 12 2 12 2Z" fill="#1a6df8"/>
                    <path d="M12 2C12 2 15 6 15 10C15 12.2091 13.2091 14 12 14C10.7909 14 9 12.2091 9 10C9 6 12 2 12 2Z" fill="#a5f3fc"/>
                    <path d="M12 6V12" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Agri Data
            </a>
            
            <!-- Hamburger Toggle (Mobile Only) -->
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle Navigation">
                <svg class="hamburger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <svg class="close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            
            <ul class="nav-menu" id="nav-menu">
                <li><a href="#" class="nav-link active">Home</a></li>
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#" class="nav-link">Resources</a></li>
                <li><a href="#" class="nav-link">About Us</a></li>
                <li><a href="#" class="nav-link">Contact</a></li>
                <!-- Mobile Only Actions -->
                @auth
                    <li class="mobile-actions">
                        <span class="nav-user-name" style="font-size: 1.05rem; font-weight: 600; color: var(--text-main); text-align: center; width: 100%; display: block; margin-bottom: 4px;">Halo, {{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" style="width: 100%;">
                            @csrf
                            <button type="submit" class="btn btn-ghost" style="width: 100%;">Log Out</button>
                        </form>
                    </li>
                @else
                    <li class="mobile-actions">
                        <a href="{{ route('login') }}" class="btn btn-ghost">Log In</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
                    </li>
                @endauth
            </ul>
            
            <!-- Desktop Only Actions -->
            <div class="nav-actions desktop-only">
                @auth
                    <span class="nav-user-name" style="font-size: 0.95rem; font-weight: 600; color: var(--text-main); margin-right: 8px;">Halo, {{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-ghost">Log Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost">Log In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-badge">
                    <!-- Leaf Icon -->
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 3.5 1 9.8a7 7 0 0 1-9 8.2z"/>
                        <path d="M9 22v-4h4"/>
                    </svg>
                    Data-Driven Agriculture for a Better Tomorrow
                </div>
                
                <h1 class="hero-title">
                    Smart Insights.<br>Stronger Harvests.
                </h1>
                
                <p class="hero-subtitle">
                    Agri Data empowers farmers and stakeholders with real-time insights, AI technology, and accurate data for better agricultural decisions.
                </p>
                
                <div class="hero-buttons">
                    <a href="#features" class="btn btn-primary">
                        Explore Features
                        <!-- Arrow Right Icon -->
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    <a href="#" class="btn btn-secondary">
                        <!-- Play Icon -->
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        Watch Demo
                    </a>
                </div>
                
                <!-- Glassmorphism Stats Card -->
                <div class="stats-container">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <!-- Users Icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">10K+</span>
                            <span class="stat-label">Farmers Empowered</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <!-- Leaf Icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 22s8-4 12-4 8 4 8 4V2S14 6 10 6 2 2 2 2z"/>
                                <path d="M12 18V6"/>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">50+</span>
                            <span class="stat-label">Crops Monitored</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <!-- Bar Chart Icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">99.9%</span>
                            <span class="stat-label">Data Accuracy</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hero-image-wrapper">
                <img src="{{ asset('assets/images/hero_farmer.png') }}" alt="Petani Menggunakan Tablet" class="hero-image">
            </div>
        </section>
        
        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="features-container">
                <div class="section-header">
                    <span class="section-badge">Our Features</span>
                    <h2 class="section-title">Powerful Tools for Modern Agriculture</h2>
                    <p class="section-desc">Explore our solutions built to support farmers with data, AI, and real-time updates.</p>
                </div>
                
                <div class="features-grid">
                    
                    <!-- Card 1: Farmer Database -->
                    <div class="feature-card">
                        <div class="card-img-wrapper">
                            <img src="{{ asset('assets/images/feature_db.png') }}" alt="Database Petani" class="card-img">
                            <div class="card-badge">
                                <!-- Users Icon -->
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Farmer Database</h3>
                            <p class="card-desc">Access a comprehensive database of farmers, their crops, and farming practices to improve collaboration and support.</p>
                            
                            <ul class="card-list">
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Farmer Profiles
                                </li>
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Crop Information
                                </li>
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Regional Insights
                                </li>
                            </ul>
                            
                            <a href="#" class="card-link">
                                Learn More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card 2: Plant Disease AI Scanner -->
                    <div class="feature-card">
                        <div class="card-img-wrapper">
                            <img src="{{ asset('assets/images/feature_scanner.png') }}" alt="AI Scanner Penyakit" class="card-img">
                            <div class="card-badge">
                                <!-- Scan / Focus Icon -->
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                                    <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                                    <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                                    <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Plant Disease AI Scanner</h3>
                            <p class="card-desc">Detect plant diseases instantly using advanced AI technology and get recommendations for treatment.</p>
                            
                            <ul class="card-list">
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Instant Disease Detection
                                </li>
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Treatment Recommendations
                                </li>
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    AI-Powered Accuracy
                                </li>
                            </ul>
                            
                            <a href="#" class="card-link">
                                Learn More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card 3: Live Weather Updates -->
                    <div class="feature-card">
                        <div class="card-img-wrapper">
                            <img src="{{ asset('assets/images/feature_weather.png') }}" alt="Prakiraan Cuaca" class="card-img">
                            <div class="card-badge">
                                <!-- Cloud-Sun Icon -->
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v2"></path>
                                    <path d="M12 20v2"></path>
                                    <path d="M4.93 4.93l1.41 1.41"></path>
                                    <path d="M17.66 17.66l1.41 1.41"></path>
                                    <path d="M2 12h2"></path>
                                    <path d="M20 12h2"></path>
                                    <path d="M6.34 17.66l-1.41 1.41"></path>
                                    <path d="M19.07 4.93l-1.41 1.41"></path>
                                    <path d="M20 17.58A5 5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.25"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">Live Weather Updates</h3>
                            <p class="card-desc">Get real-time weather updates and forecasts to plan your farming activities with confidence.</p>
                            
                            <ul class="card-list">
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Real-Time Weather
                                </li>
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    7-Day Forecast
                                </li>
                                <li class="card-list-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Weather Alerts
                                </li>
                            </ul>
                            
                            <a href="#" class="card-link">
                                Learn More
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
        
        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-bar">
                <div class="cta-content">
                    <div class="cta-icon-wrapper">
                        <!-- Sprout Leaf Icon -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2a15 15 0 0 1 12 15v5H0v-5A15 15 0 0 1 12 2z"></path>
                            <path d="M12 22V12"></path>
                            <path d="M12 12c-4 0-7-3-7-7"></path>
                            <path d="M12 12c4 0 7-3 7-7"></path>
                        </svg>
                    </div>
                    <div class="cta-text-wrapper">
                        <h2 class="cta-title">Join Agri Data Today</h2>
                        <p class="cta-desc">Be part of a smarter farming community.</p>
                    </div>
                </div>
                <div class="cta-action">
                    <a href="#" class="btn btn-white">
                        Get Started
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
        
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-logo">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C12 2 6 7 6 12C6 15.3137 8.68629 18 12 18C15.3137 18 18 15.3137 18 12C18 7 12 2 12 2Z" fill="#1a6df8"/>
                    <path d="M12 2C12 2 15 6 15 10C15 12.2091 13.2091 14 12 14C10.7909 14 9 12.2091 9 10C9 6 12 2 12 2Z" fill="#a5f3fc"/>
                    <path d="M12 6V12" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Agri Data
            </div>
            <ul class="footer-nav">
                <li><a href="#" class="footer-nav-link">Home</a></li>
                <li><a href="#features" class="footer-nav-link">Features</a></li>
                <li><a href="#" class="footer-nav-link">Resources</a></li>
                <li><a href="#" class="footer-nav-link">About Us</a></li>
                <li><a href="#" class="footer-nav-link">Contact</a></li>
            </ul>
            <p>&copy; {{ date('Y') }} Dinas Pertanian & Agri Data. Hak Cipta Dilindungi Undang-Undang.</p>
        </div>
    </footer>

    <!-- Mobile Navigation Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('nav-toggle');
            const navMenu = document.getElementById('nav-menu');
            const hamburgerIcon = navToggle.querySelector('.hamburger-icon');
            const closeIcon = navToggle.querySelector('.close-icon');

            navToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                const isActive = navMenu.classList.contains('active');
                
                if (isActive) {
                    hamburgerIcon.style.display = 'none';
                    closeIcon.style.display = 'block';
                } else {
                    hamburgerIcon.style.display = 'block';
                    closeIcon.style.display = 'none';
                }
            });

            // Close menu when clicking a link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                    hamburgerIcon.style.display = 'block';
                    closeIcon.style.display = 'none';
                });
            });
        });
    </script>

</body>
</html>
