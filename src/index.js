import { addFilter, doAction } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import OpeningHoursField from './components/OpeningHoursField';
import LicenseTab from './components/LicenseTab';
import CustomSchemaBuilder from './components/CustomSchemaBuilder';
import SchemaPresetModal from './components/SchemaPresetModal';



/**
 * Mark Pro as activated and configure upgrade URL
 */
window.swiftRankConfig = window.swiftRankConfig || {};
window.swiftRankConfig.isProActivated = true;
window.swiftRankConfig.upgradeUrl = 'https://developer.developer.developer/swift-rank-pro';

/**
 * Pro-specific variables are now registered in PHP via Schema_Variable_Replacer_Pro
 * See: includes/class-schema-variable-replacer-pro.php
 */

/**
 * Enable Pro fields by removing the isPro flag restriction.
 * The base plugin includes openingHours and priceRange with isPro: true,
 * which shows upgrade notice. When Pro is active, we remove that flag.
 */
const enableProFields = (fieldsConfig, schemaType, fields) => {
	return fieldsConfig.map(field => {
		if (field.isPro) {
			// Remove isPro flag so the field renders normally
			const { isPro, ...fieldWithoutPro } = field;
			return fieldWithoutPro;
		}
		return field;
	});
};

addFilter(
	'swift_rank_extend_fields',
	'swift-rank-pro/enable-pro-fields',
	enableProFields,
	10,
	3
);

// Expose OpeningHoursField component globally for FieldRenderer to use
window.swiftRankProComponents = window.swiftRankProComponents || {};
window.swiftRankProComponents.OpeningHoursField = OpeningHoursField;
window.swiftRankProComponents.CustomSchemaBuilder = CustomSchemaBuilder;
window.swiftRankProComponents.SchemaPresetModal = SchemaPresetModal;

/**
 * Add License tab to settings page
 */
addFilter(
	'swift_rank_settings_tabs',
	'swift-rank-pro/add-license-tab',
	(tabs) => {
		// Insert License tab before the Help tab
		const helpTabIndex = tabs.findIndex(tab => tab.name === 'help');
		const licenseTab = {
			name: 'license',
			title: __('License', 'swift-rank-pro'),
			component: LicenseTab,
		};

		if (helpTabIndex !== -1) {
			tabs.splice(helpTabIndex, 0, licenseTab);
		} else {
			tabs.push(licenseTab);
		}

		return tabs;
	}
);

// Notify base plugin that Pro components are now available
doAction('swift_rank_pro_loaded');
