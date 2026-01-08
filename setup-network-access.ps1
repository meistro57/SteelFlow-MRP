# Setup network access for SteelFlow MRP from other devices
# Run this in PowerShell as Administrator

# Get WSL IP
$wslIp = (wsl hostname -I).Trim()
Write-Host "WSL IP: $wslIp" -ForegroundColor Green

# Get Windows IP
$windowsIp = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias 'Wi-Fi','Ethernet' | Where-Object {$_.IPAddress -like '192.168.*' -or $_.IPAddress -like '10.*'}).IPAddress | Select-Object -First 1
Write-Host "Windows IP: $windowsIp" -ForegroundColor Green

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
