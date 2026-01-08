# Network Access Setup for SteelFlow MRP

## Problem
By default, WSL2 Docker containers are only accessible from `localhost` on the Windows host machine. Other devices on your network cannot access the application.

## Solution
Set up Windows port forwarding to make the application accessible from other devices on your local network.

## Quick Setup

### Option 1: Run PowerShell Script (Recommended)

1. Open **PowerShell as Administrator** (right-click PowerShell → "Run as administrator")
2. Navigate to the project directory:
   ```powershell
   cd C:\path\to\SteelFlow-MRP
   ```
3. Run the setup script:
   ```powershell
   .\setup-network-access.ps1
   ```

### Option 2: Manual Setup

Run these commands in PowerShell as Administrator:

```powershell
# Get WSL IP
$wslIp = (wsl hostname -I).Trim()

# Forward HTTP port
netsh interface portproxy add v4tov4 listenport=80 listenaddress=0.0.0.0 connectport=80 connectaddress=$wslIp

# Forward HTTPS port
netsh interface portproxy add v4tov4 listenport=443 listenaddress=0.0.0.0 connectport=443 connectaddress=$wslIp

# Forward Vite HMR port
netsh interface portproxy add v4tov4 listenport=5173 listenaddress=0.0.0.0 connectport=5173 connectaddress=$wslIp

# Add firewall rules
New-NetFirewallRule -DisplayName "SteelFlow MRP HTTP" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "SteelFlow MRP HTTPS" -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "SteelFlow MRP Vite" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow
```

## Access from Other Devices

Once setup is complete, other devices on your network can access:

- **Your Windows PC IP:** 192.168.1.118
- **Application URL:** http://192.168.1.118
- **HTTPS URL:** https://192.168.1.118

## Verify Setup

Check active port forwarding rules:
```powershell
netsh interface portproxy show all
```

You should see:
```
Listen on ipv4:             Connect to ipv4:

Address         Port        Address         Port
--------------- ----------  --------------- ----------
0.0.0.0         80          172.x.x.x       80
0.0.0.0         443         172.x.x.x       443
0.0.0.0         5173        172.x.x.x       5173
```

## Troubleshooting

### Port forwarding not working?
1. Make sure you ran PowerShell as Administrator
2. Check Windows Firewall is not blocking the ports
3. Verify Docker containers are running: `docker compose ps`

### Reset after Windows reboot
Port forwarding rules are lost after Windows restarts. Simply run the setup script again.

### Remove port forwarding
```powershell
netsh interface portproxy delete v4tov4 listenport=80 listenaddress=0.0.0.0
netsh interface portproxy delete v4tov4 listenport=443 listenaddress=0.0.0.0
netsh interface portproxy delete v4tov4 listenport=5173 listenaddress=0.0.0.0
```

### Check firewall rules
```powershell
Get-NetFirewallRule | Where-Object {$_.DisplayName -like "*SteelFlow*"}
```

## Notes

- **Security:** These rules allow devices on your local network to access the application. Be cautious on public/shared networks.
- **IP Changes:** If your Windows PC gets a new IP address (e.g., via DHCP), you don't need to reconfigure - the port forwarding works with any IP assigned to your Windows PC.
- **WSL IP Changes:** If WSL2 restarts and gets a new IP, you'll need to run the setup script again.
- **Persistent Setup:** For automatic setup on boot, you can create a scheduled task to run the script at startup.

