#!/bin/sh
# This command synchronizes the local theme directory with a remote server using rsync
# -a: archive mode (preserves permissions, timestamps, etc.)
# -v: verbose output showing the transfer progress
# -z: compresses the data during transfer
# Source: Local theme directory at /home/andrew/workspace/84em/app/public/wp-content/themes/eightfourem/
# Destination: Remote server (84em) at /www/g84emcom_126/public/wp-content/themes/eightfourem/

echo "Deploying to remote server..."
rsync -avz --exclude 'deploy.sh' --exclude '.git' --exclude 'node_modules'  /home/andrew/workspace/84em/app/public/wp-content/themes/eightfourem/ 84em:/www/g84emcom_126/public/wp-content/themes/eightfourem/
