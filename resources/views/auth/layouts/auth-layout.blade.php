<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - AgriData</title>
    
    <!-- Tailwind CSS via CDN (untuk development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Custom Config untuk Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#EFF6FF',
                            100: '#DBEAFE',
                            500: '#3B82F6',
                            600: '#2563EB',
                            700: '#1D4ED8',
                            800: '#1E40AF',
                            900: '#1E3A8A',
                        },
                        agri: {
                            green: '#10B981',
                            gold: '#F59E0B',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '50%': { transform: 'translateY(-10px) rotate(5deg)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS untuk efek khusus -->
    <style>
        /* Glassmorphism Effect */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 32px rgba(31, 38, 135, 0.15),
                0 0 60px rgba(59, 130, 246, 0.2);
        }
        
        /* Gradient Background Panel */
        .panel-gradient {
            background: linear-gradient(135deg, 
                rgba(207, 241, 246, 0.8) 0%, 
                rgba(224, 242, 254, 0.8) 50%,
                rgba(209, 250, 229, 0.8) 100%
            );
        }
        
        /* Glow Effect */
        .glow-ring {
            position: absolute;
            border-radius: 50%;
            border: 2px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.3);
            animation: pulse-slow 4s ease-in-out infinite;
        }
        
        /* Input Focus Effect */
        .input-glow:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Button Gradient */
        .btn-gradient {
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        
        /* Background Image */
        .auth-bg {
            background-image: url('/assets/images/farmer-bg.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        /* Overlay Gradient */
        .bg-overlay {
            background: linear-gradient(135deg, 
                rgba(30, 64, 175, 0.2) 0%, 
                rgba(0, 0, 0, 0.1) 100%
            );
        }
    </style>
</head>
<body class="font-sans antialiased">
    <!-- Background Container -->
    <div class="auth-bg min-h-screen w-full relative overflow-hidden">
        <!-- Overlay -->
        <div class="bg-overlay absolute inset-0"></div>
        
        <!-- Decorative Elements -->
        <!-- Glow Ring di belakang panel -->
        <div class="glow-ring w-[500px] h-[500px] absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
        
        <!-- Floating Leaves -->
        <div class="absolute top-20 left-20 text-agri-green opacity-60 animate-float pointer-events-none">
            <i data-lucide="leaf" class="w-12 h-12"></i>
        </div>
        <div class="absolute top-40 right-32 text-agri-green opacity-50 animate-float-delayed pointer-events-none">
            <i data-lucide="leaf" class="w-8 h-8"></i>
        </div>
        <div class="absolute bottom-40 left-32 text-agri-green opacity-50 animate-float pointer-events-none">
            <i data-lucide="leaf" class="w-10 h-10"></i>
        </div>
        
        <!-- Wheat Icons -->
        <div class="absolute top-32 left-40 text-agri-gold opacity-60 animate-float-delayed pointer-events-none">
            <i data-lucide="wheat" class="w-10 h-10"></i>
        </div>
        <div class="absolute bottom-32 right-40 text-agri-gold opacity-50 animate-float pointer-events-none">
            <i data-lucide="wheat" class="w-12 h-12"></i>
        </div>
        
        <!-- Particles (dots kecil) -->
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white rounded-full opacity-40 animate-pulse-slow pointer-events-none"></div>
        <div class="absolute top-2/3 right-1/3 w-3 h-3 bg-agri-green rounded-full opacity-30 animate-pulse-slow pointer-events-none"></div>
        <div class="absolute bottom-1/4 left-1/3 w-2 h-2 bg-primary-500 rounded-full opacity-40 animate-pulse-slow pointer-events-none"></div>
        
        <!-- Main Content Container -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            @yield('content')
        </div>
    </div>
    
    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
    
    <!-- Custom Scripts -->
    @stack('scripts')
</body>
</html>