# GitHub Actions Deployment Setup

This document explains how to configure the GitHub Actions deployment workflow for the eightyfourem theme.

## Required GitHub Secrets

You need to configure the following secrets in your GitHub repository settings:

1. **Navigate to Repository Settings**
   - Go to your repository on GitHub
   - Click on "Settings" tab
   - Select "Secrets and variables" → "Actions" from the left sidebar

2. **Add the following secret:**

### `DEPLOY_SSH_KEY_84EM_THEME`
Your private SSH key for authentication to the production server.
```bash
# Generate a new SSH key pair if needed:
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key

# Copy the private key content:
cat ~/.ssh/github_deploy_key
```
Add the public key (`~/.ssh/github_deploy_key.pub`) to the server's `~/.ssh/authorized_keys` file on the production server.

**Note:** The workflow is configured to connect to:
- Host: `35.224.70.159`
- Port: `59939`
- User: `g84emcom`
- Path: `/www/g84emcom_126/public/wp-content/themes/eightyfourem/`

These values are hardcoded in the workflow for security. Only the SSH key needs to be configured as a secret.

## Workflow Triggers

The deployment workflow will trigger automatically when:
- A pull request is **merged** into the `main` branch
- The PR contains changes to files in the `themes/eightyfourem/` directory or the workflow file itself
- **Note:** The deployment only runs on merged PRs, not on direct pushes to main

You can also manually trigger the deployment:
1. Go to the "Actions" tab in your repository
2. Select "Deploy Theme to Production" workflow
3. Click "Run workflow" button

## Build Process

The deployment workflow automatically:
1. Checks out the code
2. Sets up Node.js 20
3. Installs npm dependencies (`npm ci`)
4. Builds and minifies assets (`npm run build`)
5. Deploys the built theme to production via rsync

**Note:** The minified `.min.css` and `.min.js` files are excluded from git (via `.gitignore`) and built fresh during each deployment. This ensures production always has the latest optimized assets without committing build artifacts to the repository.

## Migration from Shell Script

After setting up the GitHub Action:
1. The `deploy.sh` script is no longer needed for deployments
2. You can remove it or keep it as a backup for local deployments
3. All deployments should be done through GitHub Actions for consistency and audit trail

## Security Notes

- Never commit SSH keys or credentials directly to the repository
- Regularly rotate SSH keys used for deployment
- Consider using GitHub's OpenID Connect (OIDC) for even more secure deployments
- Restrict which branches can trigger deployments through branch protection rules