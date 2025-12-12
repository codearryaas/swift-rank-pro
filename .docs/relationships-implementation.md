# Schema Relationships Implementation - Phase 1

## Overview
This document describes the initial implementation of schema relationships for the Article schema's author field in Schema Engine Pro.

## Implementation Status: ✅ Complete

The author relationship feature has been implemented with the following components:

## Architecture

### 1. **Schema_Reference_Resolver** (`class-schema-reference-resolver.php`)
Resolves reference objects to `@id` values for connected graph structure.

**Key Methods:**
- `resolve($value)` - Resolves a reference object to an @id value
- `process_schema($schema)` - Recursively processes schema arrays to resolve all reference fields
- `is_reference($value)` - Checks if a value is a reference object

**Reference Object Format:**
```json
{
  "type": "reference",
  "source": "user|global_settings|post",
  "id": 123
}
```

**Supported Sources:**
- `user` - WordPress users (resolves to Person @id)
- `global_settings` - Global schemas (Organization, WebSite)
- `post` - WordPress posts (resolves to Article @id)

### 2. **Schema_Relationships_API** (`class-schema-relationships-api.php`)
REST API endpoints for fetching available relationship targets.

**Endpoints:**
- `GET /wp-json/schema-engine-pro/v1/entities` - Get all available entities
  - Parameters: `type` (filter by schema type), `search` (search query)
- `GET /wp-json/schema-engine-pro/v1/users` - Get users only
  - Parameters: `search` (search query)

**Response Format:**
```json
{
  "global": [
    {
      "id": "organization",
      "label": "Site Name (Organization)",
      "type": "Organization",
      "source": "global_settings",
      "icon": "building"
    }
  ],
  "users": [
    {
      "id": 123,
      "label": "John Doe",
      "type": "Person",
      "source": "user",
      "icon": "user",
      "description": "john@example.com"
    }
  ]
}
```

### 3. **Schema_Relationships_Handler** (`class-schema-relationships-handler.php`)
Hooks into the schema output pipeline to resolve reference fields before output.

**Hook:** `schema_engine_output_schema` (priority 5, before variable replacement)

### 4. **Schema_Article_Pro** (`class-article-schema-pro.php`)
Extends the base Article schema with relationship field support.

**Key Features:**
- Replaces `authorName` text field with `author` relationship field
- Supports backward compatibility with custom text input
- Processes reference objects in the Article build process

**Field Definition:**
```php
array(
    'name' => 'author',
    'type' => 'schema_reference',
    'targets' => array('Person'),
    'sources' => array('users', 'global_settings'),
    'allowCustom' => true,
    'customPlaceholder' => '{author_name}'
)
```

## Data Flow

### Input (Post Meta)
```json
{
  "author": {
    "type": "reference",
    "source": "user",
    "id": 5
  }
}
```

### Processing
1. Article schema builder creates inline author object (default behavior)
2. `Schema_Article_Pro::process_article_relationships()` checks for reference
3. `Schema_Reference_Resolver::resolve()` converts to @id reference
4. Output handler includes both Article and Person schemas in @graph

### Output (JSON-LD)
```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "@id": "https://site.com/post/#article",
      "headline": "My Post",
      "author": {
        "@id": "https://site.com/author/john/#person"
      }
    },
    {
      "@type": "Person",
      "@id": "https://site.com/author/john/#person",
      "name": "John Doe",
      "url": "https://site.com/author/john/"
    }
  ]
}
```

## Backward Compatibility

### Custom Text Input
If user enters custom author name instead of selecting a reference:
```json
{
  "author": "Jane Smith",
  "authorUrl": "https://example.com/jane"
}
```

Output:
```json
{
  "@type": "Article",
  "author": {
    "@type": "Person",
    "name": "Jane Smith",
    "url": "https://example.com/jane"
  }
}
```

## Frontend Integration (To Do)

The React UI components need to be created to support the `schema_reference` field type:

### Required React Components:
1. **SchemaReferenceField.js** - Main field component
   - Searchable dropdown (Combobox)
   - Fetches entities from API
   - Supports custom text input

2. **FieldFactory** updates - Register the new field type

### Component Props:
```jsx
<SchemaReferenceField
  name="author"
  label="Author"
  value={referenceObject}
  onChange={handleChange}
  targets={['Person']}
  sources={['users', 'global_settings']}
  allowCustom={true}
  customPlaceholder="{author_name}"
/>
```

## Testing Checklist

- [ ] Test API endpoints return correct user list
- [ ] Test API endpoints return global entities
- [ ] Test reference resolution for users
- [ ] Test reference resolution for global settings
- [ ] Test backward compatibility with text input
- [ ] Test schema output in frontend
- [ ] Test Google Rich Results validation

## Future Enhancements

### Phase 2: Additional Schema Types
- Product schema → brand (Organization/Brand)
- Review schema → itemReviewed (Product/Article/etc.)
- JobPosting schema → hiringOrganization (Organization)

### Phase 3: Post-to-Post Relationships
- Article → mentions → Person/Organization
- Recipe → recipeInstructions → HowToSection

### Phase 4: Visual Graph Editor
- D3.js graph visualization
- Drag-and-drop relationship creation
- Real-time validation

## File Structure
```
schema-engine-pro/
├── includes/
│   ├── class-schema-reference-resolver.php      (NEW)
│   ├── class-schema-relationships-api.php       (NEW)
│   ├── class-schema-relationships-handler.php   (NEW)
│   ├── class-article-schema-pro.php             (NEW)
│   └── ...
└── schema-engine-pro.php                        (MODIFIED)
```

## Filters & Hooks

### New Filters:
- `schema_engine_schema_fields_{SchemaType}` - Modify schema type fields
- `schema_engine_schema_data_{SchemaType}` - Modify schema data before output

### Used Hooks:
- `schema_engine_output_schema` - Process relationships (priority 5)
- `rest_api_init` - Register API endpoints

## Notes

- All reference resolution happens on the server side
- No changes to the base plugin required
- API requires `edit_posts` capability
- User search limited to 50 results for performance
- IDs are globally unique using Schema_Reference_Manager from base plugin
