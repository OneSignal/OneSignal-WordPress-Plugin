#!/bin/bash

# Helper script to run E2E tests in Docker container
# Usage: ./run-tests.sh [container-name]

CONTAINER_NAME=${1:-"wordpress-https-wordpress-test-1"}
PLUGIN_PATH="/var/www/html/wp-content/plugins/onesignal-free-web-push-notifications/tests/e2e/wp-cli-tests.php"

echo "=========================================="
echo "OneSignal E2E Test Runner"
echo "=========================================="
echo ""
echo "Container: $CONTAINER_NAME"
echo ""

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Error: Test container '$CONTAINER_NAME' is not running"
    echo ""
    echo "To start the test environment:"
    echo "  cd ../../wordpress-https"
    echo "  docker-compose up -d"
    echo ""
    echo "Available containers:"
    docker ps --format "  - {{.Names}}"
    echo ""
    exit 1
fi

echo "✓ E2E container is running"

# Check if WordPress is installed
if ! docker exec $CONTAINER_NAME wp core is-installed --allow-root 2>/dev/null; then
    echo "Error: WordPress is not installed in test container"
    echo ""
    echo "To initialize the test environment:"
    echo "  cd ../../wordpress-https"
    echo "  ./setup"
    echo ""
    exit 1
fi

echo "✓ WordPress is ready"
echo ""
echo "Running E2E tests..."
echo ""
echo "=========================================="
echo ""

# Run the tests
docker exec "$CONTAINER_NAME" wp eval-file "$PLUGIN_PATH" --allow-root

EXIT_CODE=$?

echo ""
echo "=========================================="
if [ $EXIT_CODE -eq 0 ]; then
    echo "✓ E2E Tests Complete"
else
    echo "✗ E2E Tests Failed"
fi
echo "=========================================="
echo ""

exit $EXIT_CODE
