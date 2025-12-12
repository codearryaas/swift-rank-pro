<?php
/**
 * Event Schema Builder
 *
 * @package Swift_Rank_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schema_Event class
 *
 * Builds Event schema type.
 */
class Schema_Event implements Schema_Builder_Interface
{

    /**
     * Build event schema from fields
     *
     * @param array $fields Field values.
     * @return array Schema array (without @context).
     */
    public function build($fields)
    {
        // Use eventType if set, otherwise default to Event
        $event_type = !empty($fields['eventType']) ? $fields['eventType'] : 'Event';

        // Get field values with fallback to variables
        $name = !empty($fields['name']) ? $fields['name'] : '{post_title}';
        $start_date = !empty($fields['startDate']) ? $fields['startDate'] : '';
        $end_date = !empty($fields['endDate']) ? $fields['endDate'] : '';

        $schema = array(
            '@type' => $event_type,
            'name' => $name,
            'url' => !empty($fields['url']) ? $fields['url'] : '{post_url}',
        );

        // Description
        if (!empty($fields['description'])) {
            $schema['description'] = $fields['description'];
        }

        // Start Date (required)
        if (!empty($start_date)) {
            $schema['startDate'] = $start_date;
        }

        // End Date
        if (!empty($end_date)) {
            $schema['endDate'] = $end_date;
        }

        // Handle image - use featured image as default
        $image_url = !empty($fields['imageUrl']) ? $fields['imageUrl'] : (!empty($fields['image']) ? $fields['image'] : '{featured_image}');
        if (!empty($image_url)) {
            $schema['image'] = $image_url;
        }

        // Location
        if (!empty($fields['locationName'])) {
            $location = array(
                '@type' => !empty($fields['locationType']) ? $fields['locationType'] : 'Place',
                'name' => $fields['locationName'],
            );

            // Add address if provided
            if (!empty($fields['locationAddress'])) {
                $location['address'] = $fields['locationAddress'];
            }

            $schema['location'] = $location;
        }

        // Organizer
        if (!empty($fields['organizerName'])) {
            $organizer = array(
                '@type' => !empty($fields['organizerType']) ? $fields['organizerType'] : 'Organization',
                'name' => $fields['organizerName'],
            );

            // Add organizer URL if provided
            if (!empty($fields['organizerUrl'])) {
                $organizer['url'] = $fields['organizerUrl'];
            }

            $schema['organizer'] = $organizer;
        }

        // Offers
        if (!empty($fields['offerPrice'])) {
            $offer = array(
                '@type' => 'Offer',
                'price' => $fields['offerPrice'],
                'priceCurrency' => !empty($fields['offerCurrency']) ? $fields['offerCurrency'] : 'USD',
            );

            // Add availability if provided
            if (!empty($fields['offerAvailability'])) {
                $offer['availability'] = $fields['offerAvailability'];
            }

            // Add valid from date if provided
            if (!empty($fields['offerValidFrom'])) {
                $offer['validFrom'] = $fields['offerValidFrom'];
            }

            // Add URL if provided
            if (!empty($fields['offerUrl'])) {
                $offer['url'] = $fields['offerUrl'];
            }

            $schema['offers'] = $offer;
        }

        // Event Status
        if (!empty($fields['eventStatus'])) {
            $schema['eventStatus'] = $fields['eventStatus'];
        }

        // Event Attendance Mode
        if (!empty($fields['eventAttendanceMode'])) {
            $schema['eventAttendanceMode'] = $fields['eventAttendanceMode'];
        }

        // Performer
        if (!empty($fields['performerName'])) {
            $performer = array(
                '@type' => !empty($fields['performerType']) ? $fields['performerType'] : 'Person',
                'name' => $fields['performerName'],
            );

            $schema['performer'] = $performer;
        }

        return $schema;
    }

    /**
     * Get schema.org structure for Event type
     *
     * @return array Schema.org structure specification.
     */
    public function get_schema_structure()
    {
        return array(
            '@type' => 'Event',
            '@context' => 'https://schema.org',
            'label' => __('Event', 'swift-rank'),
            'description' => __('An event happening at a certain time and location.', 'swift-rank'),
            'url' => 'https://schema.org/Event',
            'icon' => 'calendar',
            'subtypes' => array(
                'Event' => __('Event - General event', 'swift-rank-pro'),
                'BusinessEvent' => __('BusinessEvent - Business-related events', 'swift-rank-pro'),
                'ChildrensEvent' => __('ChildrensEvent - Events for children', 'swift-rank-pro'),
                'ComedyEvent' => __('ComedyEvent - Comedy shows and performances', 'swift-rank-pro'),
                'CourseInstance' => __('CourseInstance - Educational courses', 'swift-rank-pro'),
                'DanceEvent' => __('DanceEvent - Dance performances', 'swift-rank-pro'),
                'EducationEvent' => __('EducationEvent - Educational events', 'swift-rank-pro'),
                'ExhibitionEvent' => __('ExhibitionEvent - Exhibitions and displays', 'swift-rank-pro'),
                'Festival' => __('Festival - Festivals and celebrations', 'swift-rank-pro'),
                'FoodEvent' => __('FoodEvent - Food-related events', 'swift-rank-pro'),
                'LiteraryEvent' => __('LiteraryEvent - Literary events', 'swift-rank-pro'),
                'MusicEvent' => __('MusicEvent - Music concerts and performances', 'swift-rank-pro'),
                'SaleEvent' => __('SaleEvent - Sales and promotional events', 'swift-rank-pro'),
                'ScreeningEvent' => __('ScreeningEvent - Film screenings', 'swift-rank-pro'),
                'SocialEvent' => __('SocialEvent - Social gatherings', 'swift-rank-pro'),
                'SportsEvent' => __('SportsEvent - Sports events and games', 'swift-rank-pro'),
                'TheaterEvent' => __('TheaterEvent - Theater performances', 'swift-rank-pro'),
                'VisualArtsEvent' => __('VisualArtsEvent - Visual arts events', 'swift-rank-pro'),
            ),
        );
    }

    /**
     * Get field definitions for the admin UI
     *
     * @return array Array of field configurations for React components.
     */
    public function get_fields()
    {
        return array(
            array(
                'name' => 'eventType',
                'label' => __('Event Sub-Type', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('The specific type of event. Different types may have slightly different schema properties and appear differently in search results.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Event', 'swift-rank-pro'),
                        'value' => 'Event',
                        'description' => __('General event', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('BusinessEvent', 'swift-rank-pro'),
                        'value' => 'BusinessEvent',
                        'description' => __('Business-related events', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('ChildrensEvent', 'swift-rank-pro'),
                        'value' => 'ChildrensEvent',
                        'description' => __('Events for children', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('ComedyEvent', 'swift-rank-pro'),
                        'value' => 'ComedyEvent',
                        'description' => __('Comedy shows and performances', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('CourseInstance', 'swift-rank-pro'),
                        'value' => 'CourseInstance',
                        'description' => __('Educational courses', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('DanceEvent', 'swift-rank-pro'),
                        'value' => 'DanceEvent',
                        'description' => __('Dance performances', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('EducationEvent', 'swift-rank-pro'),
                        'value' => 'EducationEvent',
                        'description' => __('Educational events', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('ExhibitionEvent', 'swift-rank-pro'),
                        'value' => 'ExhibitionEvent',
                        'description' => __('Exhibitions and displays', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('Festival', 'swift-rank-pro'),
                        'value' => 'Festival',
                        'description' => __('Festivals and celebrations', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('FoodEvent', 'swift-rank-pro'),
                        'value' => 'FoodEvent',
                        'description' => __('Food-related events', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('LiteraryEvent', 'swift-rank-pro'),
                        'value' => 'LiteraryEvent',
                        'description' => __('Literary events', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('MusicEvent', 'swift-rank-pro'),
                        'value' => 'MusicEvent',
                        'description' => __('Music concerts and performances', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('SaleEvent', 'swift-rank-pro'),
                        'value' => 'SaleEvent',
                        'description' => __('Sales and promotional events', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('ScreeningEvent', 'swift-rank-pro'),
                        'value' => 'ScreeningEvent',
                        'description' => __('Film screenings', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('SocialEvent', 'swift-rank-pro'),
                        'value' => 'SocialEvent',
                        'description' => __('Social gatherings', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('SportsEvent', 'swift-rank-pro'),
                        'value' => 'SportsEvent',
                        'description' => __('Sports events and games', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('TheaterEvent', 'swift-rank-pro'),
                        'value' => 'TheaterEvent',
                        'description' => __('Theater performances', 'swift-rank-pro'),
                    ),
                    array(
                        'label' => __('VisualArtsEvent', 'swift-rank-pro'),
                        'value' => 'VisualArtsEvent',
                        'description' => __('Visual arts events', 'swift-rank-pro'),
                    ),
                ),
                'default' => 'Event',
            ),
            array(
                'name' => 'name',
                'label' => __('Event Name', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Event name. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => '{post_title}',
                'options' => array(
                    array(
                        'label' => __('Post Title', 'swift-rank-pro'),
                        'value' => '{post_title}',
                    ),
                ),
                'default' => '{post_title}',
                'required' => true,
            ),
            array(
                'name' => 'url',
                'label' => __('URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Event URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
                'placeholder' => '{post_url}',
                'options' => array(
                    array(
                        'label' => __('Post URL', 'swift-rank-pro'),
                        'value' => '{post_url}',
                    ),
                ),
                'default' => '{post_url}',
            ),
            array(
                'name' => 'description',
                'label' => __('Description', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'customType' => 'textarea',
                'rows' => 4,
                'tooltip' => __('Event description. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => '{post_excerpt}',
                'options' => array(
                    array(
                        'label' => __('Post Excerpt', 'swift-rank-pro'),
                        'value' => '{post_excerpt}',
                    ),
                    array(
                        'label' => __('Post Content', 'swift-rank-pro'),
                        'value' => '{post_content}',
                    ),
                ),
                'default' => '{post_excerpt}',
            ),
            array(
                'name' => 'startDate',
                'label' => __('Start Date', 'swift-rank-pro'),
                'type' => 'datetime',
                'tooltip' => __('The start date and time of the event in ISO 8601 format (e.g., 2024-12-25T19:00:00).', 'swift-rank-pro'),
                'placeholder' => '2024-12-25T19:00:00',
                'required' => true,
            ),
            array(
                'name' => 'endDate',
                'label' => __('End Date', 'swift-rank-pro'),
                'type' => 'datetime',
                'tooltip' => __('The end date and time of the event in ISO 8601 format (e.g., 2024-12-25T22:00:00).', 'swift-rank-pro'),
                'placeholder' => '2024-12-25T22:00:00',
            ),
            array(
                'name' => 'imageUrl',
                'label' => __('Image URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Event image. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
                'placeholder' => '{featured_image}',
                'options' => array(
                    array(
                        'label' => __('Featured Image', 'swift-rank-pro'),
                        'value' => '{featured_image}',
                    ),
                ),
                'default' => '{featured_image}',
                'required' => true,
            ),
            array(
                'name' => 'eventStatus',
                'label' => __('Event Status', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('The status of the event.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Scheduled', 'swift-rank-pro'),
                        'value' => 'https://schema.org/EventScheduled',
                    ),
                    array(
                        'label' => __('Cancelled', 'swift-rank-pro'),
                        'value' => 'https://schema.org/EventCancelled',
                    ),
                    array(
                        'label' => __('Postponed', 'swift-rank-pro'),
                        'value' => 'https://schema.org/EventPostponed',
                    ),
                    array(
                        'label' => __('Rescheduled', 'swift-rank-pro'),
                        'value' => 'https://schema.org/EventRescheduled',
                    ),
                    array(
                        'label' => __('Moved Online', 'swift-rank-pro'),
                        'value' => 'https://schema.org/EventMovedOnline',
                    ),
                ),
                'default' => 'https://schema.org/EventScheduled',
            ),
            array(
                'name' => 'eventAttendanceMode',
                'label' => __('Event Attendance Mode', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('How the event will be attended (online, offline, or mixed).', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Offline (Physical Location)', 'swift-rank-pro'),
                        'value' => 'https://schema.org/OfflineEventAttendanceMode',
                    ),
                    array(
                        'label' => __('Online', 'swift-rank-pro'),
                        'value' => 'https://schema.org/OnlineEventAttendanceMode',
                    ),
                    array(
                        'label' => __('Mixed (Online and Offline)', 'swift-rank-pro'),
                        'value' => 'https://schema.org/MixedEventAttendanceMode',
                    ),
                ),
                'default' => 'https://schema.org/OfflineEventAttendanceMode',
            ),
            array(
                'name' => 'locationType',
                'label' => __('Location Type', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('The type of location for the event.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Place', 'swift-rank-pro'),
                        'value' => 'Place',
                    ),
                    array(
                        'label' => __('VirtualLocation', 'swift-rank-pro'),
                        'value' => 'VirtualLocation',
                    ),
                ),
                'default' => 'Place',
            ),
            array(
                'name' => 'locationName',
                'label' => __('Location Name', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Location name. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => 'Madison Square Garden',
                'options' => array(
                    array(
                        'label' => __('Site Name', 'swift-rank-pro'),
                        'value' => '{site_name}',
                    ),
                ),
                'required' => true,
            ),
            array(
                'name' => 'locationAddress',
                'label' => __('Location Address', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Location address. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => '4 Pennsylvania Plaza, New York, NY 10001',
                'options' => array(
                    array(
                        'label' => __('Site Description', 'swift-rank-pro'),
                        'value' => '{site_description}',
                    ),
                ),
            ),
            array(
                'name' => 'organizerType',
                'label' => __('Organizer Type', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('Whether the organizer is a person or organization.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Organization', 'swift-rank-pro'),
                        'value' => 'Organization',
                    ),
                    array(
                        'label' => __('Person', 'swift-rank-pro'),
                        'value' => 'Person',
                    ),
                ),
                'default' => 'Organization',
            ),
            array(
                'name' => 'organizerName',
                'label' => __('Organizer Name', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Organizer name. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => 'Event Company Inc.',
                'options' => array(
                    array(
                        'label' => __('Site Name', 'swift-rank-pro'),
                        'value' => '{site_name}',
                    ),
                    array(
                        'label' => __('Author Name', 'swift-rank-pro'),
                        'value' => '{author_name}',
                    ),
                ),
            ),
            array(
                'name' => 'organizerUrl',
                'label' => __('Organizer URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Organizer URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
                'placeholder' => 'https://example.com',
                'options' => array(
                    array(
                        'label' => __('Site URL', 'swift-rank-pro'),
                        'value' => '{site_url}',
                    ),
                ),
            ),
            array(
                'name' => 'performerType',
                'label' => __('Performer Type', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('Whether the performer is a person or organization.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('Person', 'swift-rank-pro'),
                        'value' => 'Person',
                    ),
                    array(
                        'label' => __('PerformingGroup', 'swift-rank-pro'),
                        'value' => 'PerformingGroup',
                    ),
                    array(
                        'label' => __('Organization', 'swift-rank-pro'),
                        'value' => 'Organization',
                    ),
                ),
                'default' => 'Person',
            ),
            array(
                'name' => 'performerName',
                'label' => __('Performer Name', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Performer name. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => 'John Doe',
                'options' => array(
                    array(
                        'label' => __('Author Name', 'swift-rank-pro'),
                        'value' => '{author_name}',
                    ),
                ),
            ),
            array(
                'name' => 'offerPrice',
                'label' => __('Offer Price', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Ticket price. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => '25.00',
                'options' => array(
                    array(
                        'label' => __('WooCommerce Price', 'swift-rank-pro'),
                        'value' => '{woo_product_price}',
                    ),
                ),
            ),
            array(
                'name' => 'offerCurrency',
                'label' => __('Offer Currency', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Thinking of a currency. Click pencil icon to use variables.', 'swift-rank-pro'),
                'placeholder' => 'USD',
                'options' => array(
                    array(
                        'label' => __('WooCommerce Currency', 'swift-rank-pro'),
                        'value' => '{woo_product_currency}',
                    ),
                    array(
                        'label' => __('USD', 'swift-rank-pro'),
                        'value' => 'USD',
                    ),
                    array(
                        'label' => __('EUR', 'swift-rank-pro'),
                        'value' => 'EUR',
                    ),
                ),
                'default' => 'USD',
            ),
            array(
                'name' => 'offerAvailability',
                'label' => __('Offer Availability', 'swift-rank-pro'),
                'type' => 'select',
                'tooltip' => __('The availability status of the event tickets.', 'swift-rank-pro'),
                'options' => array(
                    array(
                        'label' => __('In Stock', 'swift-rank-pro'),
                        'value' => 'https://schema.org/InStock',
                    ),
                    array(
                        'label' => __('Sold Out', 'swift-rank-pro'),
                        'value' => 'https://schema.org/SoldOut',
                    ),
                    array(
                        'label' => __('Pre-Order', 'swift-rank-pro'),
                        'value' => 'https://schema.org/PreOrder',
                    ),
                ),
                'default' => 'https://schema.org/InStock',
            ),
            array(
                'name' => 'offerUrl',
                'label' => __('Offer URL', 'swift-rank-pro'),
                'type' => 'select',
                'allowCustom' => true,
                'tooltip' => __('Ticket purchase URL. Click pencil icon to enter custom URL.', 'swift-rank-pro'),
                'placeholder' => 'https://example.com/tickets',
                'options' => array(
                    array(
                        'label' => __('Post URL', 'swift-rank-pro'),
                        'value' => '{post_url}',
                    ),
                ),
            ),
            array(
                'name' => 'offerValidFrom',
                'label' => __('Offer Valid From', 'swift-rank-pro'),
                'type' => 'datetime',
                'tooltip' => __('The date when tickets become available for purchase.', 'swift-rank-pro'),
                'placeholder' => '2024-11-01T09:00:00',
            ),
        );
    }

}
