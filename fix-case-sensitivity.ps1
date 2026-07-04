# fix-case-sensitivity.ps1
#
# Jalankan script ini dari root folder project (tempat file "artisan" berada)
# setiap kali curiga ada bug "View not found" atau "Class not found" yang
# harusnya sudah pernah diperbaiki tapi muncul lagi.
#
# Kenapa ini bisa terjadi berulang:
# Windows/Mac tidak membedakan huruf besar/kecil pada nama file, tapi
# server produksi (Linux) MEMBEDAKAN. Jadi "Dashboardcontroller.php" dan
# "DashboardController.php" dianggap SAMA oleh Windows, tapi dianggap
# BERBEDA oleh Linux. Kalau file lama tidak benar-benar dihapus (cuma
# ditimpa isinya), git tidak akan mendeteksi ada perubahan nama karena
# dari sudut pandang Windows memang tidak ada yang berubah.
#
# Cara pakai: buka PowerShell di folder project, lalu jalankan:
#   .\fix-case-sensitivity.ps1

Write-Host "=== Cek bug case-sensitivity ===" -ForegroundColor Cyan

$anyFixed = $false

# 1. DashboardController
$wrongDashboard = "app\Http\Controllers\Dashboardcontroller.php"
$rightDashboard = "app\Http\Controllers\DashboardController.php"
if (Test-Path $wrongDashboard) {
    if (Test-Path $rightDashboard) {
        Write-Host "Ketemu DUA file dashboard controller. Hapus yang salah nama: $wrongDashboard" -ForegroundColor Yellow
        Remove-Item $wrongDashboard -Force
    } else {
        git mv $wrongDashboard $rightDashboard
        Write-Host "FIXED: Dashboardcontroller.php -> DashboardController.php" -ForegroundColor Green
    }
    $anyFixed = $true
}

# 2. Folder resources/views/Admin (harus huruf kecil semua)
$wrongAdminFolder = "resources\views\Admin"
$rightAdminFolder = "resources\views\admin"
if (Test-Path $wrongAdminFolder) {
    if (Test-Path $rightAdminFolder) {
        Write-Host "Folder 'Admin' dan 'admin' berdua ada. Pindahkan isi 'Admin' ke 'admin' secara manual, lalu hapus folder 'Admin'." -ForegroundColor Yellow
    } else {
        git mv $wrongAdminFolder $rightAdminFolder
        Write-Host "FIXED: resources/views/Admin -> resources/views/admin" -ForegroundColor Green
    }
    $anyFixed = $true
}

# 3. View lahan yang sempat ke-rename
$wrongLahanView = "resources\views\petani\lahan\no-profile.blade.php"
$rightLahanView = "resources\views\petani\lahan\belum-profil.blade.php"
if ((Test-Path $wrongLahanView) -and -not (Test-Path $rightLahanView)) {
    git mv $wrongLahanView $rightLahanView
    Write-Host "FIXED: no-profile.blade.php -> belum-profil.blade.php" -ForegroundColor Green
    $anyFixed = $true
}

# 4. Cek semua pemanggilan view('...') di controller vs file fisik di disk
#    (case-sensitive), supaya ketauan kalau ada bug baru yang serupa.
Write-Host "`n=== Cek semua view() cocok dengan file fisik (case-sensitive) ===" -ForegroundColor Cyan
$viewCalls = Select-String -Path "app\Http\Controllers\*.php","app\Http\Controllers\**\*.php" -Pattern "view\(['""]([a-zA-Z0-9_.\-]+)['""]" -AllMatches |
    ForEach-Object { $_.Matches } | ForEach-Object { $_.Groups[1].Value } | Sort-Object -Unique

$missingCount = 0
foreach ($v in $viewCalls) {
    $path = "resources\views\" + ($v -replace '\.', '\') + ".blade.php"
    if (-not (Test-Path $path)) {
        Write-Host "MISSING: view('$v') -> $path" -ForegroundColor Red
        $missingCount++
    }
}

if ($missingCount -eq 0) {
    Write-Host "Semua pemanggilan view() valid, tidak ada yang hilang." -ForegroundColor Green
} else {
    Write-Host "`nAda $missingCount pemanggilan view() yang filenya tidak ketemu. Cek nama file/foldernya (huruf besar-kecil, typo, dsb)." -ForegroundColor Red
}

if ($anyFixed) {
    Write-Host "`nJangan lupa commit perubahan ini:" -ForegroundColor Cyan
    Write-Host "  git add -A" -ForegroundColor White
    Write-Host "  git commit -m 'fix: case-sensitivity'" -ForegroundColor White
    Write-Host "  git push" -ForegroundColor White
}
