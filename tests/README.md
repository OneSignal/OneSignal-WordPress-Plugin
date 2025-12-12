# Testing Documentation

## Overview

Testing is broken out into three levels:
1. **Unit Tests** - Tests for isolated functions
2. **Integration Tests** - Tests for WordPress integration and API interactions
3. **End-to-End Tests** - Tests for complete user workflows

---

## Quick Start

### Running Tests

Run all unit + integration tests
```sh
./vendor/bin/phpunit
```

Run only unit tests
```sh
./vendor/bin/phpunit --testsuite unit
```

Run only integration tests
```sh
./vendor/bin/phpunit --testsuite integration
```

Run with detailed output
```sh
./vendor/bin/phpunit --testdox
```

Run E2E Tests
```sh
./tests/e2e/run-tests.sh
```
