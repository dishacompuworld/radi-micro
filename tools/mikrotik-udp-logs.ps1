$ErrorActionPreference = 'Stop'

$projectRoot = 'C:\inetpub\wwwroot\radi-micro'
Set-Location $projectRoot

php artisan mikrotik:udp-logs
