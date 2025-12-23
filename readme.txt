=== Swift Rank Pro ===
Contributors: racase
Tags: schema, structured data, seo, json-ld, rich snippets
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add Schema.org structured data to your WordPress site with reusable schema templates and dynamic variables.

== Description ==

Swift Rank helps you add professional Schema.org structured data to your WordPress website using the recommended JSON-LD format. Improve your search engine visibility and enable rich snippets in Google search results with powerful schema templates.

= Key Features =

* **[Schema Templates](https://toolpress.net/documentation/how-to-add-schema-templates-in-swift-rank/)** - Create reusable schema templates with conditional display rules
* **Multiple Schema Types** - Article, BlogPosting, NewsArticle, Event, FAQPage, HowTo, and more
* **JSON-LD Format** - Uses Google's recommended JSON-LD structured data format
* **Dynamic Variables System** - Insert WordPress data dynamically into schema fields
* **Condition-Based Display** - Apply templates to specific post types, categories, or individual posts
* **Media Library Integration** - Upload images directly from WordPress
* **Clean Output** - Automatically removes empty values from schema output
* **User-Friendly Interface** - Intuitive admin panel with tooltips and help documentation

= Schema Types Supported =

**Article Types**
Article, BlogPosting, and NewsArticle schemas for blog posts, news, and general content.

**FAQPage**
Structured data for FAQ pages with question and answer pairs.

**HowTo**
Step-by-step guide schema with tools, supplies, and individual steps.

**Event**
Event schema for conferences, meetups, and scheduled events.

= Why Use Swift Rank? =

Schema markup helps search engines understand your content better, which can lead to:

* Enhanced search results with rich snippets
* Better visibility in search results
* Improved click-through rates from search results
* Better representation in voice search results

= Dynamic Variables =

Swift Rank includes a powerful variables system:

* `{post_title}` - Current post/page title
* `{post_url}` - Current post/page URL
* `{post_excerpt}` - Post excerpt
* `{featured_image}` - Featured image URL
* `{post_date}` - Publication date
* `{author_name}` - Author display name
* `{site_name}` - Your WordPress site name
* `{site_url}` - Your site home URL
* And many more...

Variables update automatically when your content changes, keeping your schema data current without manual updates.

= Easy to Use =

1. Install and activate the plugin
2. Go to Swift Rank → Add New Template
3. Choose your schema type
4. Configure fields and conditions
5. Save and test with Google Rich Results Test

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin dashboard
2. Navigate to Plugins → Add New
3. Search for "Swift Rank"
4. Click "Install Now" on the Swift Rank plugin
5. Activate the plugin

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin dashboard
3. Navigate to Plugins → Add New → Upload Plugin
4. Choose the downloaded ZIP file
5. Click "Install Now"
6. Activate the plugin

= After Activation =

1. Go to Swift Rank → Add New Template
2. Choose your schema type (Article, FAQPage, etc.)
3. Configure the schema fields
4. Set display conditions
5. Click "Publish"
6. Visit a matching page and view source to verify schema output
7. Test with Google Rich Results Test: https://search.google.com/test/rich-results

== Frequently Asked Questions ==

= What is Schema.org structured data? =

Schema.org is a collaborative project that provides a collection of schemas (structured data markup) that webmasters can use to mark up their pages. Search engines like Google use this data to better understand your content and display rich results.

= Does this plugin guarantee rich snippets in Google? =

No plugin can guarantee rich snippets. Schema markup helps Google understand your content, but Google decides when and how to display rich results based on many factors including content quality and relevance.

= How do schema templates work? =

Create a template, select a schema type, configure the fields, and set conditions for which posts should use it. The template automatically applies to matching content.

= How do I verify my schema is working? =

1. Visit a page where your template applies
2. View page source (Ctrl+U or Cmd+U)
3. Look for `<!-- Swift Rank -->` comment
4. Test with Google Rich Results Test: https://search.google.com/test/rich-results
5. Validate with Schema.org validator: https://validator.schema.org/

= What are variables and how do I use them? =

Variables are placeholders that automatically insert dynamic WordPress data. Click the "Insert Variable" button next to any field, select a variable, and it will be added to your field. Variables update automatically when your content changes.

== Screenshots ==

1. Schema Template Editor - Create and configure schema templates

== Changelog ==

= 1.0.0 - 2024-11-27 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Swift Rank. Add professional Schema.org structured data to your WordPress site.

== Support ==

For support, feature requests, or bug reports, please visit:
* Documentation: See the Help tab in plugin settings
* Website: https://toolpress.net/support
* WordPress Support Forum: https://wordpress.org/support/plugin/swift-rank/