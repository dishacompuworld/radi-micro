param(
    [int]$ServerId = 1,
    [string]$ServiceName = 'RadiMikrotikUdp',
    [string]$DisplayName = 'Radi MikroTik UDP Logger',
    [string]$NssmPath = 'C:\nssm\nssm.exe'
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path $NssmPath)) {
    $tempZip = Join-Path $env:TEMP 'nssm-2.24.zip'
    $extractDir = Join-Path $env:TEMP 'nssm-2.24'

    if (-not (Test-Path $tempZip)) {
        Write-Host 'Downloading NSSM...'
        Invoke-WebRequest -Uri 'https://nssm.cc/release/nssm-2.24.zip' -OutFile $tempZip
    }

    if (-not (Test-Path $extractDir)) {
        Expand-Archive -Path $tempZip -DestinationPath $extractDir -Force
    }

    $NssmPath = Join-Path $extractDir 'nssm-2.24\win64\nssm.exe'
}

if (-not (Test-Path $NssmPath)) {
    throw "NSSM not found at $NssmPath"
}

$projectRoot = 'C:\inetpub\wwwroot\radi-micro'
$phpExe = (Get-Command php -ErrorAction Stop).Source
$artisan = Join-Path $projectRoot 'artisan'
$command = '"' + $phpExe + '" "' + $artisan + '" mikrotik:udp-logs --server=' + $ServerId

Write-Host "Using NSSM: $NssmPath"
Write-Host "Service command: $command"

if (Get-Service -Name $ServiceName -ErrorAction SilentlyContinue) {
    Write-Host "Removing existing service: $ServiceName"
    & $NssmPath remove $ServiceName confirm
}

& $NssmPath install $ServiceName $phpExe
& $NssmPath set $ServiceName AppParameters "artisan mikrotik:udp-logs --server=$ServerId"
& $NssmPath set $ServiceName AppDirectory $projectRoot
& $NssmPath set $ServiceName Description 'Radi MikroTik UDP log listener for MikroTik syslog on port 515'
& $NssmPath set $ServiceName DisplayName $DisplayName
& $NssmPath set $ServiceName Start SERVICE_AUTO_START

Write-Host "Starting service: $ServiceName"
Start-Service -Name $ServiceName -ErrorAction Stop

Get-Service -Name $ServiceName | Format-List Name, DisplayName, Status, StartType

Write-Host "Service installed successfully."
