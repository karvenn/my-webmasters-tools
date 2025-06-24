# Cloudflare R2 Setup Guide for UAT Forms

## Prerequisites
1. A Cloudflare account
2. R2 storage enabled on your Cloudflare account

## Step 1: Create R2 Bucket
1. Log in to your Cloudflare dashboard
2. Navigate to R2 Object Storage
3. Click "Create bucket"
4. Name your bucket (e.g., `webmasters-uat-forms`)
5. Choose your region
6. Click "Create bucket"

## Step 2: Generate API Tokens
1. In the R2 dashboard, go to "Manage R2 API Tokens"
2. Click "Create API token"
3. Give it a name (e.g., `webmasters-uat-token`)
4. Under "Permissions", select:
   - Object Read & Write
   - Allow edit permissions
5. Under "Specify bucket(s)", select your bucket
6. Set TTL or leave as forever
7. Click "Create API Token"
8. **IMPORTANT**: Save the displayed credentials:
   - Access Key ID
   - Secret Access Key
   - Endpoint (usually: `https://<account_id>.r2.cloudflarestorage.com`)

## Step 3: Configure Public Access (Optional)
If you want files to be publicly accessible:
1. Go to your bucket settings
2. Navigate to "Settings" > "Public access"
3. Add a custom domain or use the R2.dev subdomain
4. Save the public URL

## Step 4: Configure Laravel Application
Add these values to your `.env` file:

```env
R2_ACCESS_KEY_ID=your_access_key_id_here
R2_SECRET_ACCESS_KEY=your_secret_access_key_here
R2_BUCKET=your_bucket_name_here
R2_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://your_public_url_here
```

Example:
```env
R2_ACCESS_KEY_ID=a1b2c3d4e5f6g7h8i9j0
R2_SECRET_ACCESS_KEY=your_secret_key_here
R2_BUCKET=webmasters-uat-forms
R2_ENDPOINT=https://123456789.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://uat-forms.yourdomain.com
```

## Step 5: Test Configuration
After adding the configuration:
1. Clear Laravel cache: `php artisan config:clear`
2. Test by submitting a form with attachments

## Troubleshooting
- If uploads fail, check Laravel logs in `storage/logs/laravel.log`
- Ensure your API token has the correct permissions
- Verify the endpoint URL is correct (no trailing slash)
- Check that the bucket name matches exactly

## Alternative: Local Storage
If you want to use local storage instead of R2 temporarily:
1. We can modify the code to use local disk storage
2. Files will be stored in `storage/app/public/uat-forms/`
3. Run `php artisan storage:link` to make them accessible