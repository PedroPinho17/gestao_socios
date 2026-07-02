# Executa a suite PHPUnit no Windows com extensões SQLite activadas.
# Uso:
#   .\scripts\run-tests.ps1
#   .\scripts\run-tests.ps1 -Coverage
param(
    [switch] $Coverage
)

$phpIni = "C:\Users\pedro\Documents\php-8.5.0-nts-Win32-vs17-x64"
$pcovPath = Join-Path $phpIni "ext\php_pcov.dll"
$pcovEnabled = $Coverage -and (Test-Path $pcovPath)

$phpArgs = @(
    "-d", "extension_dir=$phpIni\ext",
    "-d", "extension=php_sqlite3",
    "-d", "extension=php_pdo_sqlite"
)

if ($Coverage) {
    if ($pcovEnabled) {
        $phpArgs += @("-d", "extension=php_pcov", "-d", "pcov.directory=$PWD")
    } else {
        Write-Warning "PCOV não encontrado em $pcovPath. A correr testes sem cobertura."
    }
}

$phpArgs += "vendor/bin/phpunit"
$phpArgs += "--no-coverage"

if ($pcovEnabled) {
    $phpArgs = $phpArgs | Where-Object { $_ -ne "--no-coverage" }
    New-Item -ItemType Directory -Force -Path "build/coverage" | Out-Null
    $phpArgs += @("--coverage-text", "--coverage-cobertura=build/coverage/cobertura.xml")
}

& php @phpArgs @args
