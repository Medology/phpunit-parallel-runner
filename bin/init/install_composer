#!/usr/bin/env bash

set -eu

ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../.." && pwd )"

EXPECTED_VERSION=1.10.1
EXPECTED_CHECKSUM=b6112e167c56731fe2c6b210f7a6c07da544ffe33d31d0e9676c411c59ffffbf55604f19b3ea49b6c43d2f6c54db52a2

# Check for existing installation
if [ -e "$ROOT"/bin/composer ]; then
    echo "Checking Composer version..."
    FOUND_VERSION=$( \
        cd "$ROOT" && \
        ./bin/composer --version | \
        tail -1 | \
        awk '{print $3}' | \
        "$ROOT"/bin/linux-sed -r "s/\x1B\[([0-9]{1,2}(;[0-9]{1,2})?)?[mGK]//g" \
    )

    # Check version of installed composer
    if [ "$FOUND_VERSION" == "$EXPECTED_VERSION" ]; then
        # Version is as expected. We're done.
        echo "Composer ${EXPECTED_VERSION} already installed."
        exit 0
    else
        # Version does not match. Remove it.
        echo "Removing Composer version ${FOUND_VERSION}..."
        rm "$ROOT"/bin/composer
    fi
fi

# Install composer
echo "Installing Composer ${EXPECTED_VERSION}..."
curl -L https://getcomposer.org/download/${EXPECTED_VERSION}/composer.phar > "$ROOT"/bin/composer

echo -n "Checking the hash..."
if [[ ! $(shasum -a 384 "${ROOT}"/bin/composer) = "$EXPECTED_CHECKSUM"* ]]; then
    echo "ERROR: composer's sha384 doesn't match. Redownload it."
    rm "${ROOT}"/bin/composer
    exit 1
else
    echo "passed"
fi

# Fix permissions
echo "Setting composer permissions..."
chmod 755 "$ROOT"/bin/composer

# Ensure ~/.composer exists for the Docker container to mount
echo "Initializing ~/.composer directory..."
if [ ! -d ~/.composer ]; then
    mkdir ~/.composer
fi

echo "Composer v${EXPECTED_VERSION} installation complete."
