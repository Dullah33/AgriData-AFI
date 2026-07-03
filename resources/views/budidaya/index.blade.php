@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#eff6ff]">
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-8 flex gap-6">
        
        <!-- Sidebar: List Tanaman -->
        <aside class="w-64 bg-white rounded-2xl shadow-md p-6 h-fit sticky top-24">
            <h3 class="text-xl font-bold text-gray-800 mb-4">List Tanaman</h3>
            <div class="space-y-1 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                @foreach($plants as $plant)
                <a href="#plant-{{ $plant->id }}" 
                   class="block px-4 py-2.5 text-gray-700 hover:bg-[#eff6ff] hover:text-[#1e40af] rounded-lg transition text-sm font-medium scroll-link"
                   data-target="plant-{{ $plant->id }}">
                    {{ $plant->nama }}
                </a>
                @endforeach
            </div>
        </aside>

        <!-- Content: Grid Cards -->
        <main class="flex-1">
            <!-- Search Bar -->
            <div class="mb-8">
                <div class="relative max-w-2xl mx-auto">
                    <input type="text" 
                           id="searchInput"
                           placeholder="Cari tanaman..." 
                           class="w-full px-6 py-3 pl-12 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#4f8a5b] bg-white shadow-sm">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                </div>
            </div>

            <!-- Grid Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="plantGrid">
                @foreach($plants as $plant)
                <div id="plant-{{ $plant->id }}" class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 scroll-target group border border-gray-100">
                    <!-- Card Header -->
                    <div class="bg-[#4f8a5b] px-4 py-3">
                        <h3 class="text-white font-bold text-lg text-center">{{ $plant->nama }}</h3>
                    </div>
                    
                    <!-- Card Image -->
                    <div class="h-48 bg-[#eff6ff] flex items-center justify-center overflow-hidden">
                        @if($plant->gambar)
                            <img src="{{ asset('storage/' . $plant->foto) }}" 
                                 alt="{{ $plant->nama }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <i data-lucide="sprout" class="w-16 h-16 text-[#4f8a5b]"></i>
                        @endif
                    </div>

                    <!-- Card Content -->
                    <div class="p-5">
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            {{ $plant->deskripsi ?? 'Panduan budidaya lengkap untuk tanaman ' . $plant->nama }}
                        </p>
                        
                        <!-- Info Tags -->
                        <div class="flex gap-2 mb-4 flex-wrap">
                            <span class="px-3 py-1 bg-[#dcfce7] text-[#166534] text-xs font-semibold rounded-full">
                                {{ $plant->musim ?? 'Semua Musim' }}
                            </span>
                            <span class="px-3 py-1 bg-[#dbeafe] text-[#1e40af] text-xs font-semibold rounded-full">
                                {{ $plant->durasi_panen ?? '3-4 bulan' }}
                            </span>
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('budidaya.show', $plant->id) }}" 
                           class="block w-full text-center px-4 py-2.5 bg-[#4f8a5b] hover:bg-[#2f5d3a] text-white font-semibold rounded-lg transition-all no-underline">
                            Lihat Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-16">
                <i data-lucide="search" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <p class="text-gray-500 text-lg">Tanaman tidak ditemukan</p>
            </div>

            <!-- Pagination -->
            @if($plants->count() > 10)
            <div class="mt-8 flex justify-center items-center gap-2">
                <button class="px-4 py-2 border-2 border-[#1e40af] text-[#1e40af] rounded-lg hover:bg-[#1e40af] hover:text-white transition font-medium">
                    Previous
                </button>
                <button class="px-4 py-2 bg-[#1e40af] text-white rounded-lg font-medium">1</button>
                <button class="px-4 py-2 border-2 border-gray-300 text-gray-600 rounded-lg hover:border-[#1e40af] hover:text-[#1e40af] transition font-medium">2</button>
                <button class="px-4 py-2 border-2 border-gray-300 text-gray-600 rounded-lg hover:border-[#1e40af] hover:text-[#1e40af] transition font-medium">3</button>
                <button class="px-4 py-2 border-2 border-[#1e40af] text-[#1e40af] rounded-lg hover:bg-[#1e40af] hover:text-white transition font-medium">
                    Next
                </button>
            </div>
            @endif
        </main>
    </div>
</div>

<!-- Custom Styles -->
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #eff6ff;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #93c5fd;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #1e40af;
}
.scroll-target {
    scroll-margin-top: 100px;
    transition: all 0.3s ease;
}
.scroll-target:target {
    border: 2px solid #4f8a5b;
    box-shadow: 0 0 0 3px rgba(79, 138, 91, 0.2);
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<!-- Search & Scroll Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll dari sidebar
    document.querySelectorAll('.scroll-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                target.classList.add('ring-2', 'ring-[#4f8a5b]');
                setTimeout(() => target.classList.remove('ring-2', 'ring-[#4f8a5b]'), 2000);
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const plantGrid = document.getElementById('plantGrid');
    const emptyState = document.getElementById('emptyState');
    const cards = plantGrid.querySelectorAll('div[id^="plant-"]');

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        let visibleCount = 0;

        cards.forEach(card => {
            const plantName = card.querySelector('h3').textContent.toLowerCase();
            const plantDesc = card.querySelector('p').textContent.toLowerCase();
            
            if (plantName.includes(searchTerm) || plantDesc.includes(searchTerm)) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    });
});
</script>
@endsection