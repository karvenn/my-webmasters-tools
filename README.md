# Webmasters Tools - UAT Forms Platform

A comprehensive web platform for managing User Acceptance Testing (UAT) feedback forms with embeddable widgets.

## Features

### Core Functionality
- **UAT Forms Management**: Create and manage forms for different websites
- **Embeddable Widget**: JavaScript widget that can be embedded on any website
- **Form Submissions**: Collect user feedback with attachments, priority levels, and detailed metadata
- **Status Workflow**: Manage submissions through different stages (New, WIP, Agency Review, Client Review, On Hold, Concluded)
- **File Storage**: Support for Cloudflare R2 storage with local fallback

### Technical Features
- **Cross-Origin Support**: Full CORS support for embedding on external websites
- **Metadata Capture**: Automatically captures browser, device, screen, performance, and error information
- **Dark Mode**: Forced light mode for consistent experience
- **Responsive Design**: Works on desktop, tablet, and mobile devices

## Technology Stack

- **Backend**: Laravel 12 with PHP 8.2+
- **Frontend**: Vue.js 3 with TypeScript
- **Styling**: Tailwind CSS with shadcn/ui components
- **Database**: PostgreSQL
- **Storage**: Cloudflare R2 (S3-compatible) with local fallback
- **Authentication**: Laravel Breeze

## Installation

1. Clone the repository:
```bash
git clone https://github.com/karvenn/my-webmasters-tools.git
cd my-webmasters-tools
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Copy environment file and configure:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env`

5. Configure Cloudflare R2 (optional):
```env
R2_ACCESS_KEY_ID=your_key_id
R2_SECRET_ACCESS_KEY=your_secret_key
R2_BUCKET=your_bucket_name
R2_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
R2_URL=https://your_public_url.r2.dev
```

6. Run migrations:
```bash
php artisan migrate
```

7. Create a user:
```bash
php artisan db:seed --class=CreateUserSeeder
```

8. Build assets:
```bash
npm run build
```

9. Start the development server:
```bash
php artisan serve
npm run dev
```

## Usage

### Creating a Form
1. Login to the dashboard
2. Navigate to "UAT Forms"
3. Click "Create New Form"
4. Enter website name and URL
5. Copy the embed code

### Embedding the Widget
Add the embed code to your website's HTML before the closing `</body>` tag:
```html
<script src="http://your-domain.com/embed/widget.js?token=your_token"></script>
```

### Widget Features
- Floating "Report Issue" button
- Form fields: Issue description, attachments, priority, name, email
- Automatic metadata capture
- Local storage for name/email persistence

### Managing Submissions
- View all submissions in table format
- Click any row to open detailed view
- Update status through workflow stages
- View technical metadata in collapsible accordions
- Delete submissions if needed

## API Endpoints

- `GET /embed/widget.js?token={token}` - Get widget JavaScript
- `POST /api/submit/{token}` - Submit form data

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is proprietary software.

## Support

For support, please open an issue in the GitHub repository.

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>