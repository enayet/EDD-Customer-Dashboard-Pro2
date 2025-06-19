=== EDD Customer Dashboard Pro ===
Contributors: theweblab
Tags: easy-digital-downloads, edd, customer-dashboard, downloads, e-commerce
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern, template-based dashboard interface for Easy Digital Downloads customers with advanced customization options.

== Description ==

EDD Customer Dashboard Pro transforms the default Easy Digital Downloads customer experience with a modern, intuitive dashboard interface. Built with a powerful template system, it provides customers with a beautiful and functional way to manage their purchases, downloads, licenses, and more.

= Key Features =

* **Template System**: Modular template structure with easy customization
* **Admin Settings**: Complete control over enabled sections and template selection
* **Responsive Design**: Mobile-friendly interface with modern glassmorphism UI
* **EDD Integration**: Uses native EDD functions for maximum compatibility
* **License Management**: Full license activation/deactivation support (requires EDD Software Licensing)
* **Wishlist Support**: Integrated wishlist management (requires EDD Wish Lists)
* **Analytics Dashboard**: Customer purchase analytics and insights
* **Multi-language Ready**: Translation-ready with proper text domain

= Dashboard Sections =

* **Purchases**: Clean order history with detailed information
* **Downloads**: Download history with remaining download counts
* **Licenses**: Software license management with site activation
* **Wishlist**: Save items for later purchase
* **Analytics**: Purchase insights and customer statistics
* **Support**: Integrated support center with FAQ

= Template Development =

Create custom dashboard templates by copying the default template structure and customizing as needed. Templates support:

* Custom CSS and JavaScript
* Individual section templates
* Template metadata and screenshots
* Requirements checking for EDD add-ons

= Requirements =

* WordPress 6.0 or higher
* Easy Digital Downloads 3.0 or higher
* PHP 7.4 or higher

= Optional Add-ons =

* **EDD Software Licensing** - For license management functionality
* **EDD Wish Lists** - For wishlist functionality

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Downloads > Dashboard Pro to configure settings
4. Use shortcode `[edd_customer_dashboard_pro]` on any page/post

= Configuration =

Navigate to **Downloads > Dashboard Pro** to access:

1. **General Settings**
   - Replace EDD Pages: Automatically replace default EDD customer pages
   
2. **Template Settings**
   - Choose active template with live preview
   - Template-specific options
   
3. **Dashboard Sections**
   - Enable/disable individual sections

== Frequently Asked Questions ==

= How do I display the dashboard? =

Use the shortcode `[edd_customer_dashboard_pro]` on any page or post where you want the dashboard to appear.

= Can I replace the default EDD customer pages? =

Yes! Enable "Replace EDD Pages" in the settings to automatically replace default EDD shortcodes with the new dashboard.

= How do I create a custom template? =

1. Create a new folder in `/templates/` directory
2. Copy the structure from `/templates/default/`
3. Customize the files as needed
4. Add template metadata in `template.json`

= What EDD add-ons are supported? =

The plugin works with core EDD and optionally integrates with:
* EDD Software Licensing (for license management)
* EDD Wish Lists (for wishlist functionality)

= Is the plugin translation ready? =

Yes! The plugin is fully translation-ready with proper text domain and all strings are translatable.

== Screenshots ==

1. Modern dashboard overview with glassmorphism design
2. Purchase history with detailed order information
3. License management with site activation
4. Admin settings panel with template selection
5. Mobile-responsive design

== Changelog ==

= 1.0.0 =
* Initial release
* Template system implementation
* Admin settings interface
* Default template with all sections
* EDD integration and compatibility
* License management support
* Wishlist integration
* Analytics dashboard
* Support center
* Mobile-responsive design

== Upgrade Notice ==

= 1.0.0 =
Initial release of EDD Customer Dashboard Pro. Transforms your EDD customer experience with a modern, template-based dashboard interface.

== Developer Information ==

= Template Development =

Create custom templates by following the template structure:

Required Files:
* `dashboard.php` - Main template file
* `style.css` - Template styles
* `template.json` - Template metadata

Optional Files:
* `script.js` - Template JavaScript
* `screenshot.png` - Template preview image
* `sections/` - Individual section templates

= Hooks and Filters =

**Actions:**
* `eddcdp_dashboard_loaded` - Fired when dashboard is loaded
* `eddcdp_template_loaded` - Fired when template is loaded
* `eddcdp_section_loaded` - Fired when section is loaded

**Filters:**
* `eddcdp_template_data` - Filter template data
* `eddcdp_enabled_sections` - Filter enabled sections
* `eddcdp_dashboard_stats` - Filter dashboard statistics

= Support =

For support and documentation, please visit our website or submit a support ticket through your preferred method.