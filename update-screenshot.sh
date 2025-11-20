#!/bin/bash
# Update theme screenshot.png from 84em.com
# Scrolls page to trigger lazy loading, then crops to WordPress standard 1200x900

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCREENSHOT_PATH="$SCRIPT_DIR/screenshot.png"

echo "Taking screenshot of https://84em.com..."
export LD_LIBRARY_PATH=/usr/lib/x86_64-linux-gnu:$LD_LIBRARY_PATH
puppeteer screenshot https://84em.com "$SCREENSHOT_PATH" --viewport 1200x900 --wait-until networkidle0 --scroll

echo "Cropping to 1200x900..."
python3 -c "
from PIL import Image
img = Image.open('$SCREENSHOT_PATH')
cropped = img.crop((0, 0, 1200, 900))
cropped.save('$SCREENSHOT_PATH')
"

echo "Done. Screenshot saved to $SCREENSHOT_PATH"
