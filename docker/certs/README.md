# SSL/TLS Certificates

This directory holds SSL/TLS certificates for the Nginx web server to enable HTTPS.

## Automatic Setup (Recommended)

SSL certificates are **automatically generated** during installation by running:

```bash
./scripts/install.sh
```

The installation script calls `scripts/generate-ssl-certs.sh` which:
- Detects all available IP addresses on your system
- Generates a self-signed certificate valid for:
  - `localhost` and `*.localhost`
  - `steelflow.local` and `*.steelflow.local`
  - `127.0.0.1` (IPv4 loopback)
  - `::1` (IPv6 loopback)
  - All detected LAN IP addresses (e.g., 192.168.x.x, 10.x.x.x)
- Creates certificates valid for 365 days
- Uses Subject Alternative Names (SAN) for multi-IP support

### Accessing via Any IP Address

The application is **not bound to any specific IP** and will accept HTTPS connections on:
- `https://localhost`
- `https://127.0.0.1`
- `https://YOUR_LAN_IP` (e.g., https://192.168.1.100)
- Any other IP address assigned to your machine

**Note:** Browsers will show a security warning for self-signed certificates. This is expected and safe for development. Click "Advanced" → "Proceed" to continue.

## Manual Certificate Generation

To regenerate certificates (e.g., when IP addresses change):

```bash
chmod +x scripts/generate-ssl-certs.sh
./scripts/generate-ssl-certs.sh
docker compose restart web
```

## Alternative Options

### Option A: mkcert (Trusted Local CA)

For a better development experience without browser warnings:

1. Install `mkcert` for your OS:
   ```bash
   # macOS
   brew install mkcert

   # Linux
   sudo apt install mkcert  # Debian/Ubuntu

   # Windows
   choco install mkcert
   ```

2. Create and trust a local root CA:
   ```bash
   mkcert -install
   ```

3. Generate a certificate:
   ```bash
   cd docker/certs
   mkcert -cert-file localhost.pem -key-file localhost-key.pem \
     localhost 127.0.0.1 ::1 192.168.1.x  # Add your LAN IPs
   ```

4. Restart web container:
   ```bash
   docker compose restart web
   ```

### Option B: Custom OpenSSL Certificate

Generate a custom self-signed certificate:

```bash
openssl req -x509 -nodes -newkey rsa:2048 \
  -keyout docker/certs/localhost-key.pem \
  -out docker/certs/localhost.pem \
  -days 365 \
  -subj "/CN=localhost"
```

## Configuration Notes

- **Certificate Files:** Nginx expects `localhost.pem` and `localhost-key.pem` in this directory
- **APP_URL:** Update `.env` with `APP_URL=https://localhost` (or your IP) when using HTTPS
- **Server Name:** Nginx is configured with `server_name _;` to accept any hostname/IP
- **Protocols:** TLS 1.2 and 1.3 are enabled for security
- **Renaming Files:** If you change filenames, update `docker/nginx.conf` accordingly

## Troubleshooting

### Certificate Not Found Error

If Nginx fails to start with a certificate error:

```bash
# Generate new certificates
./scripts/generate-ssl-certs.sh

# Restart web container
docker compose restart web
```

### Accessing from Another Device

1. Find your machine's IP address:
   ```bash
   # Linux/macOS
   ip addr show  # or: ifconfig

   # Windows
   ipconfig
   ```

2. Access from another device on the same network:
   ```
   https://YOUR_IP_ADDRESS
   ```

3. Accept the browser security warning (self-signed certificate)

### Forcing HTTPS Redirect

To redirect all HTTP traffic to HTTPS, uncomment this line in `docker/nginx.conf`:

```nginx
# return 301 https://$host$request_uri;
```

## Production Deployment

**Important:** Self-signed certificates are for development only!

For production:
1. Use certificates from a trusted Certificate Authority (CA)
2. Consider free options like [Let's Encrypt](https://letsencrypt.org/)
3. Use tools like [Certbot](https://certbot.eff.org/) for automatic renewal
4. Update `docker/nginx.conf` with production certificate paths
