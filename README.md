# 📊 Page View Counter - Analytics Free

> A lightweight, privacy-friendly WordPress plugin that tracks page and post views without external analytics or cookies.

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.1.0-orange.svg)](https://github.com/samratemily/page-view-counter)

## ✨ Features

### 🔒 Privacy First
- **No External Analytics** - All data stays on your server
- **No Cookies** - Completely cookie-free tracking
- **GDPR Compliant** - Respects user privacy
- **Bot Detection** - Filters out search engine crawlers

### 📈 Smart Tracking
- **Real-time View Counting** - Instant view tracking for posts and pages
- **Admin User Exclusion** - Doesn't count admin visits
- **Last Viewed Timestamps** - Track when content was last accessed
- **Automatic Increment** - Seamless background counting

### 🎛️ Admin Dashboard
- **Statistics Overview** - Total views, average views, most popular content
- **Sortable Data Table** - Sort by views, title, or last viewed date
- **Bulk Actions** - Reset multiple post views at once
- **Individual Controls** - Reset views for specific posts
- **Post Type Filtering** - Filter between posts and pages

### 🎨 User Interface
- **Clean Admin Interface** - Intuitive and user-friendly design
- **Meta Box Integration** - View counts in post edit screens
- **Column Integration** - View counts in posts/pages list
- **Responsive Design** - Works on all screen sizes

## 🚀 Installation

### Method 1: Manual Installation
1. Download the plugin files
2. Upload the `page-view-counter` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to 'Page Views' in your admin menu

### Method 2: WordPress Admin
1. Go to `Plugins > Add New`
2. Search for "Page View Counter - Analytics Free"
3. Click "Install Now" and then "Activate"

## 📖 Usage

### Basic Usage
Once activated, the plugin automatically starts tracking page views. No configuration needed!

### Admin Dashboard
Access the main dashboard via **WordPress Admin > Page Views**

#### Statistics Overview
- **Total Views** - Sum of all page views
- **Posts with Views** - Number of posts that have been viewed
- **Average Views** - Average views per post
- **Most Viewed** - Your top-performing content

#### Data Management
- **Sort & Filter** - Organize data by views, title, or date
- **Bulk Reset** - Select multiple posts and reset their view counts
- **Individual Reset** - Reset views for specific posts
- **Export Ready** - Data is stored in WordPress database

### Theme Integration
Display view counts in your theme using these helper functions:

```php
// Get view count for current post
$views = PageViewCounter::get_view_count();

// Get view count for specific post
$views = PageViewCounter::get_view_count(123);

// Display formatted view count
PageViewCounter::display_view_count(); // Outputs: "Views: 1,234"

// Display with custom text
PageViewCounter::display_view_count(null, 'Page views: '); // Outputs: "Page views: 1,234"
```

### Meta Box
View statistics appear in the post/page edit screen sidebar:
- Total view count
- Last viewed timestamp
- Reset button for individual posts

### Admin Columns
View counts appear in the posts and pages list tables for quick reference.

## 🛠️ Technical Details

### System Requirements
- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher

### Database Storage
The plugin stores data using WordPress meta fields:
- `_pvc_view_count` - Stores the view count
- `_pvc_last_viewed` - Stores the last viewed timestamp

### Performance
- **Lightweight** - Minimal impact on page load times
- **Efficient Queries** - Optimized database operations
- **Caching Friendly** - Compatible with caching plugins
- **No External Requests** - All processing happens locally

### Security Features
- **Nonce Verification** - All AJAX requests are secured
- **Capability Checks** - Proper user permission validation
- **Input Sanitization** - All user inputs are sanitized
- **SQL Injection Protection** - Uses WordPress prepared statements

## 🎨 Customization

### CSS Customization
The plugin includes CSS classes for easy styling:

```css
/* View counter display */
.pvc-view-count {
    font-size: 14px;
    color: #666;
}

/* Admin stats summary */
.pvc-stats-summary {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
}

/* Admin columns */
.column-pvc_views {
    width: 80px;
    text-align: center;
}
```

### JavaScript Hooks
The plugin provides JavaScript functions for custom integrations:
- `pvcResetViews(postId)` - Reset views for a specific post
- `pvcBulkAction()` - Handle bulk actions
- `pvcResetSingleView(postId)` - Admin page single reset

## 🔧 Developer Hooks

### Actions
```php
// Before view count is incremented
do_action('pvc_before_view_increment', $post_id);

// After view count is incremented
do_action('pvc_after_view_increment', $post_id, $new_count);
```

### Filters
```php
// Modify view count before display
$count = apply_filters('pvc_display_count', $count, $post_id);

// Modify bot detection patterns
$bots = apply_filters('pvc_bot_patterns', $bots);
```

## 📊 Screenshots

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)
*Clean, intuitive admin interface with statistics overview*

### Statistics Overview
![Statistics](screenshots/statistics.png)
*Comprehensive view statistics and analytics*

### Post Edit Meta Box
![Meta Box](screenshots/meta-box.png)
*View counts integrated into post edit screens*

## 🤝 Contributing

We welcome contributions! Please feel free to submit a Pull Request.

### Development Setup
1. Clone the repository
2. Install WordPress development environment
3. Activate the plugin
4. Make your changes
5. Test thoroughly
6. Submit a pull request

### Coding Standards
- Follow WordPress Coding Standards
- Include PHPDoc comments
- Write secure, sanitized code
- Test on multiple WordPress versions

## 📝 Changelog

### Version 1.1.0
- ✨ Complete plugin rebranding
- 🔒 Enhanced security features
- 🎨 Improved admin interface
- 📱 Responsive design updates
- 🐛 Bug fixes and optimizations

### Version 1.0.0
- 🎉 Initial release
- 📊 Basic view counting
- 👤 Admin dashboard
- 🔧 Theme integration functions

## 🆘 Support

### Documentation
- [Installation Guide](docs/installation.md)
- [Theme Integration](docs/theme-integration.md)
- [Troubleshooting](docs/troubleshooting.md)

### Getting Help
- 📧 Email: support@samratemily.com
- 🐛 Issues: [GitHub Issues](https://github.com/samratemily/page-view-counter/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/samratemily/page-view-counter/discussions)

## 📄 License

This plugin is licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

```
Page View Counter - Analytics Free
Copyright (C) 2024 SamratEmily

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🙏 Acknowledgments

- WordPress community for the amazing platform
- Contributors and testers
- Users who provide feedback and suggestions

---

<div align="center">

**Made with ❤️ by [SamratEmily](https://github.com/samratemily)**

[⭐ Star this project](https://github.com/samratemily/page-view-counter) | [🐛 Report Bug](https://github.com/samratemily/page-view-counter/issues) | [💡 Request Feature](https://github.com/samratemily/page-view-counter/issues)

</div>