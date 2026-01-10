# setup-network-access.ps1
# Setup network access for SteelFlow MRP from other devices
# Run this in PowerShell as Administrator

# Get WSL IP
$wslIp = (wsl hostname -I).Trim()
Write-Host "WSL IP: $wslIp" -ForegroundColor Green

# Get Windows IP
$windowsIp = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias 'Wi-Fi','Ethernet' | Where-Object {$_.IPAddress -like '192.168.*' -or $_.IPAddress -like '10.*'}).IPAddress | Select-Object -First 1
Write-Host "Windows IP: $windowsIp" -ForegroundColor Green

# Update local .env for mobile access (APP_URL + Vite HMR host)
$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$envPath = Join-Path $projectRoot ".env"

function Set-EnvValue {
    param (
        [string]$Path,
        [string]$Key,
        [string]$Value
    )

    if (-not (Test-Path $Path)) {
        return $false
    }

    $lines = Get-Content $Path
    $pattern = "^\s*$Key="
    $updated = $false

    $lines = $lines | ForEach-Object {
        if ($_ -match $pattern) {
            $updated = $true
            "$Key=$Value"
        } else {
            $_
        }
    }

    if (-not $updated) {
        $lines += "$Key=$Value"
    }

    Set-Content -Path $Path -Value $lines
    return $true
}

if ($windowsIp) {
    if (Test-Path $envPath) {
        $appUrl = "http://$windowsIp"
        $viteDevServerUrl = "http://$windowsIp:5173"

        Write-Host "`nUpdating .env for mobile access..." -ForegroundColor Yellow
        Set-EnvValue -Path $envPath -Key "APP_URL" -Value $appUrl | Out-Null
        Set-EnvValue -Path $envPath -Key "VITE_HMR_HOST" -Value $windowsIp | Out-Null
        Set-EnvValue -Path $envPath -Key "VITE_DEV_SERVER_URL" -Value $viteDevServerUrl | Out-Null
        Write-Host "Updated APP_URL, VITE_HMR_HOST, and VITE_DEV_SERVER_URL in .env" -ForegroundColor Green
    } else {
        Write-Host "`nNo .env found at $envPath. Skipping APP_URL/Vite updates." -ForegroundColor Yellow
    }
} else {
    Write-Host "`nUnable to detect Windows IP. Skipping .env updates." -ForegroundColor Red
}

# Remove existing port proxies
Write-Host "`nRemoving existing port forwarding rules..." -ForegroundColor Yellow
netsh interface portproxy delete v4tov4 listenport=80 listenaddress=0.0.0.0 2>$null
netsh interface portproxy delete v4tov4 listenport=443 listenaddress=0.0.0.0 2>$null
netsh interface portproxy delete v4tov4 listenport=5173 listenaddress=0.0.0.0 2>$null

# Add port forwarding for HTTP (80)
Write-Host "Setting up port forwarding for HTTP (80)..." -ForegroundColor Yellow
netsh interface portproxy add v4tov4 listenport=80 listenaddress=0.0.0.0 connectport=80 connectaddress=$wslIp

# Add port forwarding for HTTPS (443)
Write-Host "Setting up port forwarding for HTTPS (443)..." -ForegroundColor Yellow
netsh interface portproxy add v4tov4 listenport=443 listenaddress=0.0.0.0 connectport=443 connectaddress=$wslIp

# Add port forwarding for Vite (5173)
Write-Host "Setting up port forwarding for Vite HMR (5173)..." -ForegroundColor Yellow
netsh interface portproxy add v4tov4 listenport=5173 listenaddress=0.0.0.0 connectport=5173 connectaddress=$wslIp

# Configure Windows Firewall
Write-Host "`nConfiguring Windows Firewall..." -ForegroundColor Yellow
New-NetFirewallRule -DisplayName "SteelFlow MRP HTTP" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow -ErrorAction SilentlyContinue
New-NetFirewallRule -DisplayName "SteelFlow MRP HTTPS" -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow -ErrorAction SilentlyContinue
New-NetFirewallRule -DisplayName "SteelFlow MRP Vite" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow -ErrorAction SilentlyContinue

# Display current port forwarding rules
Write-Host "`nCurrent port forwarding rules:" -ForegroundColor Green
netsh interface portproxy show all

Write-Host "`n✅ Setup complete!" -ForegroundColor Green
Write-Host "`nYou can now access SteelFlow MRP from other devices using:" -ForegroundColor Cyan
Write-Host "  http://$windowsIp" -ForegroundColor White
Write-Host "  https://$windowsIp" -ForegroundColor White
Write-Host "`nNote: These rules will reset after a Windows restart." -ForegroundColor Yellow
Write-Host "Run this script again after reboot if needed." -ForegroundColor Yellow
