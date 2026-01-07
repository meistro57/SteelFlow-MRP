# Local TLS Certificates

This directory holds development TLS certificates for the Nginx container.

## Option A: mkcert (recommended)

1. Install `mkcert` for your OS.
2. Create and trust a local root CA:

```bash
mkcert -install
```

3. Generate a certificate for your dev host (adjust the hostnames if needed):

```bash
mkcert -cert-file localhost.pem -key-file localhost-key.pem localhost 127.0.0.1 ::1
```

## Option B: OpenSSL (self-signed)

Generate a one-year self-signed certificate:

```bash
openssl req -x509 -nodes -newkey rsa:2048 \
  -keyout localhost-key.pem \
  -out localhost.pem \
  -days 365 \
  -subj "/CN=localhost"
```

## Notes

- The Nginx config expects `localhost.pem` and `localhost-key.pem` in this folder.
- Update `.env` and set `APP_URL=https://<host>` when enabling TLS locally.
- If you change filenames, update `docker/nginx.conf` accordingly.
