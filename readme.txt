# EDD Customer Dashboard Pro

Modern, template-based dashboard interface for Easy Digital Downloads customers with advanced customization options.

## Features

- **Template System**: Modular template structure with easy customization
- **Admin Settings**: Complete control over enabled sections and template selection
- **Responsive Design**: Mobile-friendly interface with modern glassmorphism UI
- **EDD Integration**: Uses native EDD functions for maximum compatibility
- **License Management**: Full license activation/deactivation support (requires EDD Software Licensing)
- **Wishlist Support**: Integrated wishlist management (requires EDD Wish Lists)
- **Analytics Dashboard**: Customer purchase analytics and insights
- **Multi-language Ready**: Translation-ready with proper text domain

## Directory Structure

```
edd-customer-dashboard-pro/
├── edd-customer-dashboard-pro.php     # Main plugin file
├── includes/                          # Core plugin files
│   ├── class-template-loader.php      # Template management
│   ├── class-admin-settings.php       # Admin settings page
│   └── class-dashboard-data.php       # Data handling
├── assets/                            # Admin assets
│   ├── admin.css                      # Admin styles
│   └── admin.js                       # Admin scripts
├── templates/                         # Dashboard templates
│   └── default/                       # Default template
│       ├── dashboard.php              # Main dashboard template
│       ├── sections/                  # Template sections
│       │   ├── header.php             # Dashboard header
│       │   ├── stats.php              # Statistics overview
│       │   ├── navigation.php         # Navigation tabs
│       │   ├── purchases.php          # Purchases section
│       │   ├── downloads.php          # Downloads section
│       │   ├── licenses.php           # License management
│       │   ├── wishlist.php           # Wishlist section
│       │   ├── analytics.php          # Analytics section
│       │   └── support.php            # Support section
│       ├── style.css                  # Template styles
│       ├── script.js                  # Template scripts
│       ├── template.json              # Template metadata
│       └── screenshot.png             # Template preview (optional)
├── languages/                         # Translation files
└── README.md                          # Documentation
```

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Downloads > Dashboard Pro to configure settings
4. Use shortcode `[edd_customer_dashboard_pro]` on any page/post

## Configuration

### Admin Settings

Navigate to **Downloads > Dashboard Pro** to access:

1. **General Settings**
   - Replace EDD Pages: Automatically replace default EDD customer pages
   
2. **Template Settings**
   - Choose active template with live preview
   - Template-specific options
   
3. **Dashboard Sections**
   - Enable/disable individual sections:
     - Purchases
     - Downloads
     - Licenses (requires EDD Software Licensing)
     - Wishlist (requires EDD Wish Lists)
     - Analytics
     - Support

### Shortcode Usage

Primary shortcode:
```
[edd_customer_dashboard_pro]
```

The plugin can also automatically replace default EDD shortcodes:
- `[purchase_history]`
- `[edd_profile_editor]`
- `[download_history]`

## Template Development

### Creating Custom Templates

1. Create a new folder in `/templates/` directory
2. Copy the structure from `/templates/default/`
3. Customize the files as needed
4. Add template metadata in `template.json`

### Template Structure

**Required Files:**
- `dashboard.php` - Main template file
- `style.css` - Template styles
- `template.json` - Template metadata

**Optional Files:**
- `script.js` - Template JavaScript
- `screenshot.png` - Template preview image
- `sections/` - Individual section templates

### Template Data Variables

Available in all template files:
- `$user` - WordPress user object
- `$customer` - EDD customer object
- `$dashboard_data` - Dashboard data helper class
- `$enabled_sections` - Array of enabled sections
- `$settings` - Plugin settings array

### Helper Functions

The `$dashboard_data` object provides methods for:
- `get_customer_purchases($customer, $limit)`
- `get_customer_downloads($customer)`
- `get_customer_licenses($user_id)`
- `get_customer_wishlist($user_id)`
- `get_customer_analytics($customer)`
- `format_currency($amount)`
- `format_date($date)`
- And more...

## Hooks and Filters

### Actions
- `eddcdp_dashboard_loaded` - Fired when dashboard is loaded
- `eddcdp_template_loaded` - Fired when template is loaded
- `eddcdp_section_loaded` - Fired when section is loaded

### Filters
- `eddcdp_template_data` - Filter template data
- `eddcdp_enabled_sections` - Filter enabled sections
- `eddcdp_dashboard_stats` - Filter dashboard statistics
- `eddcdp_purchase_actions` - Filter purchase action buttons

## Requirements

- WordPress 5.0+
- Easy Digital Downloads 3.0+
- PHP 7.4+

## Optional Add-ons

- **EDD Software Licensing** - For license management functionality
- **EDD Wish Lists** - For wishlist functionality
- **EDD Customer Portal** - Enhanced customer features

## Customization Examples

### Custom CSS
```css
/* Override default styles */
.eddcdp-dashboard-container {
    --primary-color: #your-color;
}
```

### Custom Template Section
```php
<?php
// In your custom template section
if (!defined('ABSPATH')) exit;

echo '<h2>Custom Section</h2>';
// Your custom content here
?>
```

### Filter Usage
```php
// Modify dashboard stats
add_filter('eddcdp_dashboard_stats', function($stats, $customer) {
    $stats['custom_stat'] = 'Custom Value';
    return $stats;
}, 10, 2);
```

## Troubleshooting

### Common Issues

1. **Dashboard not showing**: Ensure user is logged in and has EDD customer data
2. **Template not loading**: Check file permissions and template structure
3. **Sections not appearing**: Verify sections are enabled in admin settings
4. **Styling issues**: Check for theme CSS conflicts

### Debug Mode

Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For support and documentation:
- Check the plugin settings page
- Review the template documentation
- Submit support tickets through your preferred method

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Template system implementation
- Admin settings interface
- Default template with all sections
- EDD integration and compatibility