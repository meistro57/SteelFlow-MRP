#!/bin/bash

# SteelFlow MRP - SSL Certificate Generation Script
# Generates self-signed SSL certificates that work with any IP address
set -e

CERT_DIR="docker/certs"
CERT_FILE="localhost.pem"
KEY_FILE="localhost-key.pem"
DAYS_VALID=365

echo "🔐 Generating Self-Signed SSL Certificates"
echo "=========================================="
echo ""

# Create certs directory if it doesn't exist
mkdir -p "$CERT_DIR"

# Function to display status messages
function status() {
    echo "▶️  $1"
}

function success() {
    echo "✅ $1"
}

# Detect all available IP addresses
status "Detecting available IP addresses..."

# Get all IPv4 addresses (excluding loopback)
IPV4_ADDRESSES=$(ip -4 addr show 2>/dev/null | grep -oP '(?<=inet\s)\d+(\.\d+){3}' | grep -v '127.0.0.1' || echo "")

# Get all IPv6 addresses (excluding loopback)
IPV6_ADDRESSES=$(ip -6 addr show 2>/dev/null | grep -oP '(?<=inet6\s)[0-9a-f:]+' | grep -v '^::1$' | grep -v '^fe80' || echo "")

echo ""
echo "📍 Detected IP addresses:"
echo "   • IPv4 Loopback: 127.0.0.1"
echo "   • IPv6 Loopback: ::1"

if [ -n "$IPV4_ADDRESSES" ]; then
    for ip in $IPV4_ADDRESSES; do
        echo "   • IPv4 Address: $ip"
    done
fi

if [ -n "$IPV6_ADDRESSES" ]; then
    for ip in $IPV6_ADDRESSES; do
        echo "   • IPv6 Address: $ip"
    done
fi

echo ""

# Create OpenSSL configuration file with Subject Alternative Names
status "Creating OpenSSL configuration..."

CONFIG_FILE="$CERT_DIR/openssl.cnf"

cat > "$CONFIG_FILE" << 'EOF'
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
req_extensions = v3_req

[dn]
C=US
ST=State
L=City
O=SteelFlow MRP
OU=Development
CN=localhost

[v3_req]
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[alt_names]
DNS.1 = localhost
DNS.2 = *.localhost
DNS.3 = steelflow.local
DNS.4 = *.steelflow.local
IP.1 = 127.0.0.1
IP.2 = ::1
EOF

# Add detected IPv4 addresses to SAN
IP_INDEX=3
for ip in $IPV4_ADDRESSES; do
    echo "IP.$IP_INDEX = $ip" >> "$CONFIG_FILE"
    IP_INDEX=$((IP_INDEX + 1))
done

# Add detected IPv6 addresses to SAN
for ip in $IPV6_ADDRESSES; do
    echo "IP.$IP_INDEX = $ip" >> "$CONFIG_FILE"
    IP_INDEX=$((IP_INDEX + 1))
done

success "OpenSSL configuration created"

# Generate the certificate and private key
status "Generating SSL certificate and private key..."

openssl req \
    -x509 \
    -nodes \
    -newkey rsa:2048 \
    -keyout "$CERT_DIR/$KEY_FILE" \
    -out "$CERT_DIR/$CERT_FILE" \
    -days $DAYS_VALID \
    -config "$CONFIG_FILE" \
    -extensions v3_req \
    > /dev/null 2>&1

success "Certificate generated: $CERT_DIR/$CERT_FILE"
success "Private key generated: $CERT_DIR/$KEY_FILE"

# Set proper permissions
chmod 644 "$CERT_DIR/$CERT_FILE"
chmod 600 "$CERT_DIR/$KEY_FILE"

success "Permissions set"

# Display certificate information
echo ""
echo "📜 Certificate Details:"
echo "   • Valid for: $DAYS_VALID days"
echo "   • Algorithm: RSA 2048-bit"
echo "   • Hash: SHA-256"
echo ""

# Display Subject Alternative Names
echo "🌐 This certificate is valid for:"
openssl x509 -in "$CERT_DIR/$CERT_FILE" -noout -text | grep -A $((IP_INDEX + 5)) "Subject Alternative Name" || echo "   • localhost and all detected IP addresses"

echo ""
echo "✨ SSL Certificate Generation Complete!"
echo ""
echo "⚠️  SECURITY NOTE:"
echo "   This is a self-signed certificate for development use only."
echo "   Browsers will show a security warning - this is expected."
echo "   For production, use certificates from a trusted CA."
echo ""
echo "📝 Next Steps:"
echo "   1. Update .env: APP_URL=https://localhost (or your IP)"
echo "   2. Restart containers: docker compose restart web"
echo "   3. Access via HTTPS: https://localhost or https://YOUR_IP"
echo ""

# Cleanup
rm -f "$CONFIG_FILE"

success "Setup complete! 🚀"
