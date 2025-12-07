#!/bin/bash

# Helper script to run E2E tests in Docker container
# Usage: ./run-tests.sh [container-name]

set -e

CONTAINER_NAME=${1:-"wordpress-https-wordpress-1"}
PLUGIN_PATH="/var/www/html/wp-content/plugins/onesignal-free-web-push-notifications/tests/e2e/wp-cli-tests.php"

echo "=========================================="
echo "OneSignal E2E Test Runner"
echo "=========================================="
echo ""
echo "Container: $CONTAINER_NAME"
echo "Plugin Path: $PLUGIN_PATH"
echo ""

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ Error: Container '$CONTAINER_NAME' is not running"
    echo ""
    echo "Available containers:"
    docker ps --format "{{.Names}}"
    echo ""
    echo "Usage: $0 [container-name]"
    exit 1
fi

echo "✓ Container found and running"
echo ""
echo "Running E2E tests..."
echo ""

# Run the tests
docker exec "$CONTAINER_NAME" wp eval-file "$PLUGIN_PATH" --allow-root

echo ""
echo "=========================================="
echo "E2E Tests Complete"
echo "=========================================="
