# Backup Off-System Storage Configuration

This document explains how to configure off-system storage for backups. The backup system automatically stores backups to configured cloud storage services in addition to sending them via email.

## Supported Storage Methods

1. **AWS S3** - Amazon Web Services S3
2. **FTP** - File Transfer Protocol
3. **SFTP** - Secure File Transfer Protocol (SSH)
4. **Google Drive** - Google Drive API (requires additional setup)

## Configuration

Add the following environment variables to your `.env` file:

### AWS S3 Storage

```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

**Requirements:**
- AWS account with S3 access
- S3 bucket created
- IAM user with S3 write permissions

### FTP Storage

```env
BACKUP_FTP_HOST=ftp.example.com
BACKUP_FTP_USERNAME=your_ftp_username
BACKUP_FTP_PASSWORD=your_ftp_password
BACKUP_FTP_PORT=21
BACKUP_FTP_PATH=/backups
```

**Requirements:**
- PHP FTP extension enabled (`php-ftp` or `php-ftp` package)
- FTP server with write access
- Network connectivity to FTP server

### SFTP Storage

```env
BACKUP_SFTP_HOST=sftp.example.com
BACKUP_SFTP_USERNAME=your_sftp_username
BACKUP_SFTP_PASSWORD=your_sftp_password
BACKUP_SFTP_PORT=22
BACKUP_SFTP_PATH=/backups
BACKUP_SFTP_KEY=/path/to/ssh/private/key  # Optional: for key-based authentication
```

**Requirements:**
- PHP SSH2 extension enabled (`php-ssh2` package)
- SFTP server with SSH access
- Network connectivity to SFTP server

**Note:** On Windows, you may need to install the SSH2 extension separately. On Linux, install with:
```bash
sudo apt-get install php-ssh2  # Ubuntu/Debian
sudo yum install php-ssh2      # CentOS/RHEL
```

### Google Drive Storage

```env
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_FOLDER_ID=your_folder_id  # Optional: defaults to root
```

**Requirements:**
- Google Cloud Project with Drive API enabled
- OAuth 2.0 credentials
- Refresh token (obtained through OAuth flow)

**Setup Steps:**

1. **Create Google Cloud Project:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select existing one
   - Enable "Google Drive API"

2. **Create OAuth 2.0 Credentials:**
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "OAuth client ID"
   - Choose "Desktop app" or "Web application"
   - Download the credentials JSON file
   - Note your `client_id` and `client_secret`

3. **Get Refresh Token:**
   - Use Google OAuth 2.0 Playground: https://developers.google.com/oauthplayground/
   - Select "Drive API v3" scopes:
     - `https://www.googleapis.com/auth/drive.file` (for file upload)
     - `https://www.googleapis.com/auth/drive` (for full access)
   - Click "Authorize APIs" and sign in
   - Click "Exchange authorization code for tokens"
   - Copy the "Refresh token"

4. **Get Folder ID (Optional):**
   - Open Google Drive
   - Navigate to the folder where you want backups stored
   - The folder ID is in the URL: `https://drive.google.com/drive/folders/FOLDER_ID_HERE`
   - Copy the `FOLDER_ID_HERE` part

5. **Add to .env:**
   ```env
   GOOGLE_DRIVE_CLIENT_ID=your_client_id.apps.googleusercontent.com
   GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
   GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
   GOOGLE_DRIVE_FOLDER_ID=your_folder_id_or_root
   ```

**Note:** The implementation uses Google Drive API v3 directly via HTTP requests. No additional PHP packages are required.

## How It Works

1. When a backup is created, the system:
   - Creates a password-protected ZIP file containing database and storage files
   - Sends the ZIP file as an email attachment to all administrators
   - Attempts to store the backup to all configured off-system storage locations

2. Storage methods are tried in order:
   - AWS S3 (if configured)
   - FTP (if configured)
   - SFTP (if configured)
   - Google Drive (if configured)

3. Each storage method is independent - if one fails, others will still be attempted.

4. Storage information is logged and included in the backup completion email.

## Email Notifications

All administrators receive an email with:
- The backup ZIP file attached
- Download link from the system
- Information about off-system storage locations (if any)

## Troubleshooting

### FTP/SFTP Connection Issues

- Check network connectivity: `ping ftp.example.com`
- Verify credentials are correct
- Check firewall rules allow outbound connections
- Ensure PHP extensions are installed and enabled

### S3 Upload Issues

- Verify AWS credentials are correct
- Check IAM user has S3 write permissions
- Verify bucket name and region are correct
- Check bucket policy allows uploads

### Email Attachment Issues

- Check email server configuration
- Verify file size limits (some email servers limit attachment size)
- Check PHP `upload_max_filesize` and `post_max_size` settings

## Security Notes

- All backup files are password-protected with password: `Ofisilink`
- FTP passwords are stored in `.env` file - keep it secure
- S3 credentials should use IAM users with minimal required permissions
- SFTP keys should be stored securely with proper file permissions (600)

## Testing

To test backup storage configuration:

```bash
php artisan system:backup-db
```

Check logs at `storage/logs/laravel.log` for storage operation results.

