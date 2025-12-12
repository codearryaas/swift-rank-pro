# Schema Engine Pro - Preset Updates

## Summary
Updated schema presets to match plugin's type hierarchy and added e-commerce integrations.

## Changes Made

### 1. Fixed Type Hierarchy (3 presets)
Fixed presets to use parent types with subtype fields instead of direct subtype selection:

- **blog-post**: Added `articleType: 'BlogPosting'` field
- **news-article**: Changed type from `NewsArticle` → `Article`, added `articleType: 'NewsArticle'`
- **restaurant**: Changed type from `Restaurant` → `LocalBusiness`, added `businessType: 'Restaurant'`
- **store**: Changed type from `Store` → `LocalBusiness`, added `businessType: 'Store'`

### 2. WooCommerce Integration (2 presets)
Added presets for WooCommerce products with automatic field population:

#### woocommerce-simple-product
- Type: `Product`
- Uses WooCommerce meta fields: `_sku`, `_price`, `_wc_average_rating`, `_wc_review_count`
- Includes offers with price and availability
- Includes aggregate ratings from WooCommerce reviews

#### woocommerce-variable-product
- Type: `Product`
- Uses `AggregateOffer` for products with variations
- Maps to `_min_variation_price` and `_max_variation_price` meta fields
- Perfect for products with size/color variations

### 3. Easy Digital Downloads Integration (2 presets)
Added presets for EDD digital products:

#### edd-download
- Type: `Product`
- Uses EDD meta fields: `edd_sku`, `edd_price`, `_edd_reviews_average_rating`, `_edd_reviews_count`
- Standard product schema for digital downloads

#### edd-software
- Type: `SoftwareApplication`
- Specialized schema for software products
- Includes `applicationCategory`, `operatingSystem`, `softwareVersion`
- Uses `_edd_sl_version` for software licensing integration
- Maps `downloadUrl` for direct download access

### 4. WP Job Manager Integration (2 presets)
Added presets for job listings:

#### job-listing-full-time
- Type: `JobPosting`
- Employment type: `FULL_TIME`
- Maps to Job Manager meta: `_job_expires`, `_company_name`, `_company_website`, `_company_logo`, `_job_location`, `_job_salary`
- Includes structured `hiringOrganization`, `jobLocation`, and `baseSalary`

#### job-listing-remote
- Type: `JobPosting`
- Includes `jobLocationType: 'TELECOMMUTE'`
- Includes `applicantLocationRequirements`
- Perfect for remote/work-from-home positions

## Total Presets

**Before**: 13 presets
**After**: 19 presets (+6 new)

### Breakdown by Type:
- **Article**: 2 (Blog Post, News Article)
- **VideoObject**: 2 (YouTube Video, Tutorial Video)
- **Product**: 6 (Physical, Digital, WooCommerce Simple, WooCommerce Variable, EDD Download, EDD Software)
- **Event**: 2 (Online Event, In-Person Event)
- **Recipe**: 1 (Simple Recipe)
- **HowTo**: 1 (DIY Guide)
- **Review**: 1 (Product Review)
- **LocalBusiness**: 2 (Restaurant, Store)
- **SoftwareApplication**: 1 (EDD Software)
- **JobPosting**: 2 (Full-Time, Remote)

## Variable System
All presets use the plugin's variable system:
- `{post_title}`, `{post_excerpt}`, `{post_content}`, `{post_date}`, `{post_modified}`
- `{featured_image}`, `{post_url}`
- `{post_author_id}` (for reference resolution)
- `{meta:field_name}` (custom field access)

## Plugin Integration Meta Fields

### WooCommerce
- `_sku` - Product SKU
- `_price` - Product price
- `_min_variation_price` / `_max_variation_price` - Variation price range
- `_wc_average_rating` - Average customer rating
- `_wc_review_count` - Total review count

### Easy Digital Downloads
- `edd_sku` - Product SKU
- `edd_price` - Download price
- `_edd_reviews_average_rating` - Average rating
- `_edd_reviews_count` - Review count
- `_edd_sl_version` - Software licensing version

### WP Job Manager
- `_job_expires` - Job expiration date
- `_company_name` - Hiring company name
- `_company_website` - Company website
- `_company_logo` - Company logo URL
- `_job_location` - Job location
- `_job_salary` - Salary information

## Testing
To test these presets:
1. Ensure Pro plugin is active
2. Edit any post/page
3. Click "Load Preset" button in Schema tab
4. Filter by type or search for specific presets
5. Select preset - fields should auto-populate
6. For WooCommerce/EDD/Job Manager presets, use on respective post types for automatic field population

## Files Modified
- `/includes/class-schema-presets.php` - Updated preset definitions
- Built with `npm run build`
