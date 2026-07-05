let previousAnalysisState = null;

// DOM Elements
const panelHasil = document.getElementById('panelHasil');
const stateIdle = document.getElementById('stateIdle');
const skeletonLoader = document.getElementById('skeletonLoader');
const floatingCard = document.getElementById('floatingLoadingCard');
const progressBar = document.getElementById('progressBar');
const resultContent = document.getElementById('resultContent');
const btnAnalisis = document.getElementById('btnAnalisis');
const selectProvinsi = document.getElementById('provinsi');
const selectKabupaten = document.getElementById('kabupaten');
const selectKecamatan = document.getElementById('kecamatan');
const selectTanaman = document.getElementById('tanamanSelect');

// Step elements
const steps = [
    document.getElementById('step1'),
    document.getElementById('step2'),
    document.getElementById('step3'),
    document.getElementById('step4')
];

// State
let progressInterval = null;

/**
 * Initialize on DOM load
 */
document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
    loadPlants();
    setupEventListeners();
});

/**
 * Setup event listeners for cascading dropdowns
 */
function setupEventListeners() {
    selectProvinsi.addEventListener('change', handleProvinsiChange);
    selectKabupaten.addEventListener('change', handleKabupatenChange);
    selectKecamatan.addEventListener('change', checkFormCompletion);
    selectTanaman.addEventListener('change', checkFormCompletion);
    btnAnalisis.addEventListener('click', jalankanAnalisis);
}

/**
 * Load provinces from API
 */
async function loadProvinces() {
    try {
        const response = await fetch('/api/provinces');
        const data = await response.json();
        
        selectProvinsi.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.name;
                selectProvinsi.appendChild(option);
            });
            selectProvinsi.disabled = false;
        } else {
            selectProvinsi.innerHTML = '<option value="">Gagal memuat provinsi</option>';
            selectProvinsi.disabled = true;
        }
    } catch (error) {
        console.error('Error loading provinces:', error);
        selectProvinsi.innerHTML = '<option value="">Error memuat provinsi</option>';
        selectProvinsi.disabled = true;
    }
}

/**
 * Handle provinsi change - load regencies
 */
async function handleProvinsiChange() {
    const provCode = this.value;
    
    // Reset kabupaten and kecamatan
    selectKabupaten.innerHTML = '<option value="">Memuat kabupaten...</option>';
    selectKabupaten.disabled = true;
    selectKecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
    selectKecamatan.disabled = true;
    
    if (!provCode) {
        selectKabupaten.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
        return;
    }
    
    try {
        const response = await fetch(`/api/regencies/${provCode}`);
        const data = await response.json();
        
        selectKabupaten.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(kab => {
                const option = document.createElement('option');
                option.value = kab.id;
                option.textContent = kab.name;
                selectKabupaten.appendChild(option);
            });
            selectKabupaten.disabled = false;
        } else {
            selectKabupaten.innerHTML = '<option value="">Tidak ada kabupaten</option>';
        }
    } catch (error) {
        console.error('Error loading regencies:', error);
        selectKabupaten.innerHTML = '<option value="">Error memuat kabupaten</option>';
    }
    
    checkFormCompletion();
}

/**
 * Handle kabupaten change - load districts
 */
async function handleKabupatenChange() {
    const kabCode = this.value;
    
    // Reset kecamatan
    selectKecamatan.innerHTML = '<option value="">Memuat kecamatan...</option>';
    selectKecamatan.disabled = true;
    
    if (!kabCode) {
        selectKecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        return;
    }
    
    try {
        const response = await fetch(`/api/districts/${kabCode}`);
        const data = await response.json();
        
        selectKecamatan.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(kec => {
                const option = document.createElement('option');
                option.value = kec.id;
                option.textContent = kec.name;
                selectKecamatan.appendChild(option);
            });
            selectKecamatan.disabled = false;
        } else {
            selectKecamatan.innerHTML = '<option value="">Tidak ada kecamatan</option>';
        }
    } catch (error) {
        console.error('Error loading districts:', error);
        selectKecamatan.innerHTML = '<option value="">Error memuat kecamatan</option>';
    }
    
    checkFormCompletion();
}

/**
 * Load plants from API
 */
async function loadPlants() {
    try {
        const response = await fetch('/api/tanaman');
        const result = await response.json();
        
        if (result.success && Array.isArray(result.data)) {
            result.data.forEach(plant => {
                const option = document.createElement('option');
                option.value = plant.id;
                option.textContent = plant.nama;
                selectTanaman.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading plants:', error);
    }
}

/**
 * Check if form is complete to enable analyze button
 */
function checkFormCompletion() {
    const isComplete = selectProvinsi.value && 
                      selectKabupaten.value && 
                      selectKecamatan.value;
    
    btnAnalisis.disabled = !isComplete;
}

/**
 * Show loading state
 */
function showLoading() {
    stateIdle.classList.add('hidden');
    resultContent.innerHTML = '';
    skeletonLoader.classList.remove('hidden');
    floatingCard.classList.remove('hidden');
    
    // Reset progress
    progressBar.style.width = '0%';
    steps.forEach((step, i) => {
        step.className = 'flex items-center gap-3 text-gray-400';
        const icon = step.querySelector('i');
        icon.className = 'far fa-circle w-4';
    });
}

/**
 * Update progress bar and steps
 */
function updateProgress(percent, activeStepIndex) {
    progressBar.style.width = `${percent}%`;
    
    steps.forEach((step, i) => {
        const icon = step.querySelector('i');
        if (i < activeStepIndex) {
            // Completed
            step.className = 'flex items-center gap-3 text-green-600 font-medium';
            icon.className = 'fas fa-check-circle w-4';
        } else if (i === activeStepIndex) {
            // Active
            step.className = 'flex items-center gap-3 text-blue-600 font-medium';
            icon.className = 'fas fa-circle-notch fa-spin w-4';
        } else {
            // Pending
            step.className = 'flex items-center gap-3 text-gray-400';
            icon.className = 'far fa-circle w-4';
        }
    });
}

/**
 * Hide loading and show results
 */
function hideLoading(htmlContent) {
    skeletonLoader.classList.add('hidden');
    floatingCard.classList.add('hidden');
    resultContent.innerHTML = htmlContent;
    
    // Re-initialize Lucide icons
    if (window.lucide) {
        lucide.createIcons();
    }
}

/**
 * Main analysis function
 */
async function jalankanAnalisis() {
    const provinsi = selectProvinsi;
    const kabupaten = selectKabupaten;
    const kecamatan = selectKecamatan;
    const tanamanId = selectTanaman.value;
    const namaProv = provinsi.options[provinsi.selectedIndex]?.text || '';
    const namaKab = kabupaten.options[kabupaten.selectedIndex]?.text || '';
    const namaKec = kecamatan.options[kecamatan.selectedIndex]?.text || '';

    // Validation
    if (!namaProv || !namaKab || !namaKec) {
        hideLoading(`<div class="p-6 bg-red-50 border border-red-200 rounded-xl"> <p class="text-red-700 font-semibold"> Harap lengkapi Provinsi, Kabupaten, dan Kecamatan</p> </div>`);
        return;
    }

    // Show loading
    showLoading();

    // Start progress simulation
    let currentStep = 0;
    let currentPercent = 0;
    progressInterval = setInterval(() => {
        if (currentStep < 4) {
            currentPercent += 5;
            if (currentPercent >= ((currentStep + 1) * 25)) {
                currentStep++;
            }
            updateProgress(currentPercent, currentStep);
        }
    }, 200);

    try {
        if (!tanamanId) {
            // Smart recommendation (no specific plant selected)
            console.log('Fetching recommendations...');
            const response = await fetch(
                `/api/cuaca/recommendations/${encodeURIComponent(namaProv)}/${encodeURIComponent(namaKab)}/${encodeURIComponent(namaKec)}`
            );
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Recommendations data:', data);
            
            clearInterval(progressInterval);
            updateProgress(100, 3);

            setTimeout(() => {
                hideLoading(renderRecommendations(data));
            }, 500);
        } else {
            // Detailed analysis for specific plant
            console.log('Analyzing specific plant...', {
                provinsi: namaProv,
                kabupaten: namaKab,
                kecamatan: namaKec,
                plant_id: tanamanId
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            console.log('CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
            
            const response = await fetch('/api/cuaca/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    provinsi: namaProv,
                    kabupaten: namaKab,
                    kecamatan: namaKec,
                    plant_id: parseInt(tanamanId)
                })
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Analysis data:', data);
            
            clearInterval(progressInterval);
            updateProgress(100, 3);

            setTimeout(() => {
                hideLoading(renderDetailedAnalysis(data));
            }, 500);
        }
    } catch (error) {
        console.error('Analysis error:', error);
        clearInterval(progressInterval);
        hideLoading(`<div class="p-6 bg-red-50 border border-red-200 rounded-xl"> <h3 class="text-lg font-bold text-red-800 mb-2">Gagal Menganalisis</h3> <p class="text-red-700">${error.message}</p> <button onclick="jalankanAnalisis()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"> 🔄 Coba Lagi </button> </div>`);
    }
}

/**
 * Render smart recommendations (when no plant selected)
 */
function renderRecommendations(data) {
    if (!data.success) {
        return `<div class="p-6 bg-red-50 rounded-xl"><p class="text-red-700">Error: ${data.error}</p></div>`;
    }
    
    previousAnalysisState = {
        type: 'recommendation',
        data: data
    };
    
    const recs = data.recommendations;
    const weather = data.weather;
    
    let html = `
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white p-6 rounded-xl">
                <div class="flex items-center gap-4">
                    ${plant.gambar ? 
                        `<img src="${plant.gambar}" alt="${plant.nama}" class="w-20 h-20 rounded-lg object-cover border-2 border-white/30">` :
                        ''
                    }
                    <div>
                        <h2 class="text-3xl font-bold mb-2">${plant.nama}</h2>
                        <p class="text-emerald-100">${data.location.kecamatan}, ${data.location.kabupaten}</p>
                    </div>
                </div>
            </div>
            
            <!-- Current Weather -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-orange-50 p-4 rounded-xl border border-orange-200">
                    <p class="text-sm text-gray-600 mb-1">Suhu</p>
                    <p class="text-2xl font-bold text-orange-700">${weather.suhu}°C</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                    <p class="text-sm text-gray-600 mb-1">Kelembaban</p>
                    <p class="text-2xl font-bold text-blue-700">${weather.kelembaban}%</p>
                </div>
                <div class="bg-cyan-50 p-4 rounded-xl border border-cyan-200">
                    <p class="text-sm text-gray-600 mb-1">Curah Hujan</p>
                    <p class="text-2xl font-bold text-cyan-700">${weather.curah_hujan}mm</p>
                </div>
            </div>

            <!-- Recommendations List -->
            <div class="space-y-4">
    `;
    
    recs.forEach((rec, index) => {
        const statusColor = getStatusColor(rec.status);
        
        html += `
            <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:border-blue-400 transition-all cursor-pointer" onclick="selectPlantAndAnalyze(${rec.id})">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <!-- GANTI EMOJI DENGAN GAMBAR -->
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            ${rec.gambar ? 
                                `<img src="${rec.gambar}" alt="${rec.nama}" class="w-full h-full object-cover">` :
                                `<div class="w-full h-full flex items-center justify-center text-gray-400 text-2xl">🌱</div>`
                            }
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-1">${index + 1}. ${rec.nama}</h3>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor.bg} ${statusColor.text}">
                                    ${rec.status}
                                </span>
                                <span class="text-sm text-gray-600">Score: ${rec.score}/100</span>
                            </div>
                            <div class="flex gap-4 text-sm">
                                <span class="${rec.match_details.suhu_match ? 'text-green-600' : 'text-red-600'}">
                                    ${rec.match_details.suhu_match ? '✓' : '✗'} Suhu
                                </span>
                                <span class="${rec.match_details.kelembaban_match ? 'text-green-600' : 'text-red-600'}">
                                    ${rec.match_details.kelembaban_match ? '✓' : '✗'} Kelembaban
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 ml-4">
                        <button class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-semibold whitespace-nowrap">
                            Analisis
                        </button>
                        <a href="/budidaya/${rec.id}" 
                           class="px-4 py-2 bg-[#4f8a5b] text-white rounded-lg hover:bg-[#2f5d3a] transition font-semibold text-center no-underline whitespace-nowrap">
                            Budidaya
                        </a>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `</div></div>`;
    return html;
}

/**
 * Render detailed analysis (when plant selected)
 */
function renderDetailedAnalysis(data) {
    if (!data.success) {
        return `<div class="p-6 bg-red-50 rounded-xl"><p class="text-red-700">Error: ${data.error}</p></div>`;
    }
    
    // Simpan state untuk navigasi kembali
    previousAnalysisState = {
        type: 'detailed',
        data: data
    };
    
    const plant = data.plant;
    const weather = data.weather;
    const analysis = data.analysis;
    
    return `
        <div class="space-y-6">
            <!-- Header dengan Tombol Kembali -->
            <div class="flex items-center justify-between">
                <button onclick="kembaliKeRekomendasi()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center gap-2 font-semibold">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali
                </button>
                <button onclick="lihatRekomendasiLain()" 
                    class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition font-semibold">
                    Lihat Tanaman Lain
                </button>
            </div>
            
            <!-- Konten Detail -->
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white p-6 rounded-xl">
                <div class="flex items-center gap-4">
                    ${plant.gambar ? 
                        `<img src="${plant.gambar}" alt="${plant.nama}" class="w-20 h-20 rounded-lg object-cover border-2 border-white/30">` :
                        ''
                    }
                    <div>
                        <h2 class="text-3xl font-bold mb-2">${plant.nama}</h2>
                        <p class="text-emerald-100">${data.location.kecamatan}, ${data.location.kabupaten}</p>
                    </div>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="text-center py-4">
                <div class="inline-block px-6 py-3 rounded-full text-lg font-bold ${getAnalysisStatusBg(analysis.score)}">
                    ${analysis.status} (Score: ${analysis.score}/100)
                </div>
            </div>
            
            <!-- Current Conditions vs Ideal -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-orange-50 p-5 rounded-xl border-2 ${analysis.match_details.suhu.match ? 'border-green-300' : 'border-red-300'}">
                    <p class="text-sm text-gray-600 mb-2">🌡️ Suhu Saat Ini</p>
                    <p class="text-3xl font-bold text-orange-700 mb-1">${weather.suhu}°C</p>
                    <p class="text-sm text-gray-600">Ideal: ${plant.suhu_ideal}</p>
                    <p class="text-sm mt-2 ${analysis.match_details.suhu.match ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'}">
                        ${analysis.match_details.suhu.match ? '✓ Sesuai' : '✗ Tidak Sesuai'}
                    </p>
                </div>
                <div class="bg-blue-50 p-5 rounded-xl border-2 ${analysis.match_details.kelembaban.match ? 'border-green-300' : 'border-red-300'}">
                    <p class="text-sm text-gray-600 mb-2">💧 Kelembaban</p>
                    <p class="text-3xl font-bold text-blue-700 mb-1">${weather.kelembaban}%</p>
                    <p class="text-sm text-gray-600">Ideal: ${plant.kelembapan_ideal}</p>
                    <p class="text-sm mt-2 ${analysis.match_details.kelembaban.match ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'}">
                        ${analysis.match_details.kelembaban.match ? '✓ Sesuai' : '✗ Tidak Sesuai'}
                    </p>
                </div>
            </div>
            
            <!-- Recommendation Text -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border-2 border-green-300">
                <h3 class="text-lg font-bold text-gray-800 mb-3">💡 Rekomendasi:</h3>
                <p class="text-gray-700 leading-relaxed">${analysis.recommendation}</p>
            </div>
            
            <!-- Plant Info -->
            <div class="bg-gray-50 p-6 rounded-xl">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Tanaman</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Musim Tanam</p>
                        <p class="font-semibold text-gray-800">${plant.musim}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Jenis Tanah</p>
                        <p class="font-semibold text-gray-800">${plant.jenis_tanah}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Durasi Panen</p>
                        <p class="font-semibold text-gray-800">${plant.durasi_panen}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Curah Hujan Ideal</p>
                        <p class="font-semibold text-gray-800">${plant.curah_hujan_ideal}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-gray-600 text-sm mb-1">Deskripsi:</p>
                    <p class="text-gray-700 text-sm leading-relaxed">${plant.deskripsi}</p>
                </div>
            </div>

            <!-- TOMBOL BUDIDAYA BARU -->
            <div class="flex gap-3">
                <a href="/budidaya/${plant.id}" 
                class="flex-1 px-6 py-3 bg-[#4f8a5b] hover:bg-[#2f5d3a] text-white font-semibold rounded-xl transition-all text-center no-underline flex items-center justify-center gap-2">
                    Lihat Panduan Budidaya
                </a>
                <a href="/budidaya" 
                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all text-center no-underline">
                    Lihat Semua Tanaman
                </a>
            </div>
        </div>
    `;
}

//Fungsi untuk kembali ke rekomendasi sebelumnya
function kembaliKeRekomendasi() {
    if (previousAnalysisState && previousAnalysisState.type === 'recommendation') {
        hideLoading(renderRecommendations(previousAnalysisState.data));
    } else {
        // Jika tidak ada state, reset ke idle
        resultContent.innerHTML = '';
        skeletonLoader.classList.add('hidden');
        floatingCard.classList.add('hidden');
        stateIdle.classList.remove('hidden');
    }
}

//Fungsi untuk melihat rekomendasi lain (reset form)
function lihatRekomendasiLain() {
    // Reset dropdown tanaman
    selectTanaman.value = '';
    resultContent.innerHTML = '';
    skeletonLoader.classList.add('hidden');
    floatingCard.classList.add('hidden');
    stateIdle.classList.remove('hidden');
}

/**
 * Helper: Get status color
 */
function getStatusColor(status) {
    const colors = {
        'Sangat Cocok': { bg: 'bg-green-100', text: 'text-green-800' },
        'Cocok': { bg: 'bg-emerald-100', text: 'text-emerald-800' },
        'Bisa Ditanam': { bg: 'bg-yellow-100', text: 'text-yellow-800' },
        'Kurang Cocok': { bg: 'bg-red-100', text: 'text-red-800' }
    };
    return colors[status] || { bg: 'bg-gray-100', text: 'text-gray-800' };
}

/**
 * Helper: Get analysis status background
 */
function getAnalysisStatusBg(score) {
    if (score >= 85) return 'bg-green-500 text-white';
    if (score >= 70) return 'bg-emerald-500 text-white';
    if (score >= 50) return 'bg-yellow-500 text-white';
    return 'bg-red-500 text-white';
}
/**
 * Select plant and analyze (for recommendations)
 */
function selectPlantAndAnalyze(plantId) {
    selectTanaman.value = plantId;
    jalankanAnalisis();
}