<#
.SYNOPSIS
    Dump database lokal Hadirin menjadi satu file SQL siap impor ke phpMyAdmin InfinityFree.

.DESCRIPTION
    Kredensial database dibaca dari file .env di folder yang sama dengan script ini,
    jadi tidak perlu mengetik password lagi. File ditulis langsung oleh mysqldump
    lewat --result-file sehingga tidak ada BOM dan encoding-nya tetap utf8mb4,
    persis yang dibutuhkan phpMyAdmin.

    Pemakaian:
        powershell -ExecutionPolicy Bypass -File .\dump-untuk-hosting.ps1
        powershell -ExecutionPolicy Bypass -File .\dump-untuk-hosting.ps1 -Output C:\temp\hadirin.sql

.PARAMETER Output
    Lokasi file SQL hasil dump. Bisa path relatif (terhadap folder script) atau path
    absolut. Default: hadirin_web.sql di folder script.

.NOTES
    File dump berisi data pengguna dan hash password, jangan di-commit ke git.
    File .gitignore mengabaikan /*.sql.
#>

param(
    [string]$Output = 'hadirin_web.sql'
)

$ErrorActionPreference = 'Stop'

# --- 1. Baca kredensial dari .env ------------------------------------------------
$envPath = Join-Path $PSScriptRoot '.env'

if (-not (Test-Path $envPath)) {
    throw "File .env tidak ditemukan di $envPath"
}

$konfig = @{}

Get-Content $envPath | ForEach-Object {
    if ($_ -match '^\s*([A-Za-z0-9_]+)\s*=\s*(.*)$') {
        $konfig[$Matches[1]] = $Matches[2].Trim().Trim('"', "'")
    }
}

if ($konfig['DB_CONNECTION'] -ne 'mysql') {
    throw "DB_CONNECTION di .env adalah '$($konfig['DB_CONNECTION'])', script ini hanya untuk MySQL."
}

$database = $konfig['DB_DATABASE']
$dbHost   = $konfig['DB_HOST']
$port     = if ($konfig['DB_PORT']) { $konfig['DB_PORT'] } else { '3306' }
$user     = $konfig['DB_USERNAME']

if (-not $database -or -not $user) {
    throw 'DB_DATABASE atau DB_USERNAME kosong di .env'
}

if (-not [System.IO.Path]::IsPathRooted($Output)) {
    $Output = Join-Path $PSScriptRoot $Output
}

$Output = [System.IO.Path]::GetFullPath($Output)

# --- 2. Cari mysqldump -----------------------------------------------------------
$candidates = @()

foreach ($nama in @('mysqldump.exe', 'mysqldump')) {
    $command = Get-Command $nama -ErrorAction SilentlyContinue
    if ($command) {
        $candidates += $command.Source
    }
}

# Laragon dan XAMPP menyimpan versi MySQL dengan nama folder berbeda-beda per versi,
# jadi versi terbaru dipilih lebih dulu.
$candidates += Get-ChildItem 'C:\laragon\bin\mysql\*\bin\mysqldump.exe' -ErrorAction SilentlyContinue |
    Sort-Object -Property @{ Expression = { $_.FullName }; Descending = $true } |
    ForEach-Object { $_.FullName }

$candidates += 'C:\xampp\mysql\bin\mysqldump.exe'
$candidates += 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe'

$mysqldump = $candidates | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1

if (-not $mysqldump) {
    throw 'mysqldump tidak ditemukan. Install MySQL (Laragon/XAMPP) atau jalankan dari terminal yang PATH-nya berisi mysqldump.'
}

# --- 3. Dump ---------------------------------------------------------------------
Write-Host "mysqldump : $mysqldump"
Write-Host "database  : $database @ ${dbHost}:$port"
Write-Host "hasil     : $Output"
Write-Host ''

$argList = @(
    "--host=$dbHost",
    "--port=$port",
    "--user=$user",
    "--password=$($konfig['DB_PASSWORD'])",
    '--default-character-set=utf8mb4',
    '--single-transaction',
    '--add-drop-table',
    '--routines',
    '--triggers',
    '--no-tablespaces',
    '--skip-comments',
    "--result-file=$Output",
    $database
)

& $mysqldump @argList

if ($LASTEXITCODE -ne 0) {
    throw "mysqldump gagal dengan kode $LASTEXITCODE. Cek kredensial di .env dan pastikan MySQL sedang berjalan."
}

if (-not (Test-Path $Output)) {
    throw 'mysqldump selesai tapi file hasil tidak ada.'
}

# --- 4. Ringkasan ----------------------------------------------------------------
$bytes = (Get-Item $Output).Length
$tabel = 0
$terisi = 0
$adaUsers = $false

Get-Content $Output | ForEach-Object {
    if ($_ -like 'CREATE TABLE *') {
        $tabel++
    } elseif ($_ -like 'INSERT INTO *') {
        $terisi++
        # Backtick adalah karakter escape untuk -like di PowerShell, jadi pola
        # tabel dibaca dengan -match (regex) yang menangkap backtick apa adanya.
        if ($_ -match '^INSERT INTO `users` ') {
            $adaUsers = $true
        }
    }
}

Write-Host ('Ukuran    : {0:N2} MB' -f ($bytes / 1MB))
Write-Host "Tabel     : $tabel ($terisi terisi data)"

if (-not $adaUsers) {
    Write-Warning 'Tabel users tidak punya baris data. Setelah diimpor ke hosting, belum ada akun yang bisa login.'
}

Write-Host ''
Write-Host 'Langkah import di InfinityFree:'
Write-Host '  1. Panel InfinityFree -> phpMyAdmin -> pilih database (nama diawali if0_).'
Write-Host '  2. Tab Import -> pilih file ini -> Format: SQL -> tekan Go.'
Write-Host '  3. Selesai, coba login lagi dengan akun yang ada di tabel users.'
