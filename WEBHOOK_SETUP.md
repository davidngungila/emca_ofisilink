# GitHub Webhook Setup for Automatic Deployment

This guide explains how to set up automatic deployment using GitHub webhooks.

## Overview

When you push code to the `main` branch on GitHub, a webhook will automatically:
1. Fetch the latest changes
2. Merge them into the server
3. Run database migrations
4. Clear all caches
5. Optimize the application

## Setup Instructions

### 1. Generate Webhook Secret (Optional but Recommended)

Add to your `.env` file:
```bash
WEBHOOK_SECRET=your-random-secret-key-here
```

Generate a random secret:
```bash
php artisan tinker
>>> Str::random(40)
```

### 2. Configure GitHub Webhook

1. Go to your GitHub repository
2. Click **Settings** → **Webhooks** → **Add webhook**
3. Configure the webhook:
   - **Payload URL**: `https://your-domain.com/webhook/github`
   - **Content type**: `application/json`
   - **Secret**: (The secret you set in `.env` as `WEBHOOK_SECRET`)
   - **Events**: Select "Just the push event"
   - **Active**: ✓ Checked
4. Click **Add webhook**

### 3. Test the Webhook

#### Option A: Test via GitHub
1. Make a small change and push to `main` branch
2. Check GitHub webhook delivery logs
3. Check Laravel logs: `storage/logs/laravel.log`

#### Option B: Test via API (Requires Authentication)
```bash
curl -X POST https://your-domain.com/webhook/test \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### 4. Server Requirements

- Git must be installed and configured
- PHP must have `proc_open` enabled
- The web server user must have write permissions to the project directory
- Git must be configured with proper credentials (SSH keys or token)

### 5. Security Considerations

1. **Webhook Secret**: Always use a secret to verify webhook authenticity
2. **HTTPS Only**: Only use HTTPS for webhook URLs
3. **IP Whitelisting**: Consider whitelisting GitHub IPs in your firewall
4. **Logging**: All webhook events are logged for security auditing

### 6. GitHub IP Ranges

If you need to whitelist GitHub IPs, they publish their ranges at:
https://api.github.com/meta

You can add these to your `.htaccess` or server firewall.

### 7. Troubleshooting

#### Webhook not triggering
- Check GitHub webhook delivery logs
- Verify the webhook URL is accessible
- Check Laravel logs for errors

#### Deployment fails
- Check file permissions
- Verify Git is configured correctly
- Check PHP error logs
- Ensure all required PHP extensions are installed

#### Signature verification fails
- Verify `WEBHOOK_SECRET` in `.env` matches GitHub webhook secret
- Check that the secret is not empty

### 8. Manual Deployment

If webhook fails, you can manually trigger deployment:

```bash
cd /path/to/your/project
./deploy-live.sh
```

Or use the test endpoint (requires authentication):
```bash
POST /webhook/test
```

## Webhook Payload Example

GitHub sends a JSON payload like this:
```json
{
  "ref": "refs/heads/main",
  "commits": [
    {
      "id": "abc123",
      "message": "Update feature",
      "author": {
        "name": "Developer",
        "email": "dev@example.com"
      }
    }
  ],
  "repository": {
    "name": "your-repo",
    "full_name": "username/your-repo"
  }
}
```

## Logs

All webhook activities are logged to:
- `storage/logs/laravel.log`

Look for entries like:
- "Webhook triggered deployment"
- "Webhook deployment failed"
- "Git fetch failed"
- etc.

## Notes

- Only pushes to `main` or `master` branch trigger deployment
- The webhook handler automatically handles merge conflicts
- Migrations run with `--force` flag (be careful in production)
- Caches are cleared automatically after each deployment
- The deployment process has a 5-minute timeout per step

