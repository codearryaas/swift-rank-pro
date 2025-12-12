import { useState, useMemo } from '@wordpress/element';
import { Modal, Button, SearchControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import Icon from '../../../swift-rank/src/components/Icon';
import './SchemaPresetModal.scss';

const SchemaPresetModal = ({ isOpen, onClose, onSelectPreset }) => {
	const [selectedType, setSelectedType] = useState('all');
	const [searchQuery, setSearchQuery] = useState('');

	// Get presets from localized data
	const allPresets = window.swiftRankProPresets?.presets || [];
	const presetTypesMetadata = window.swiftRankProPresets?.types || {};

	// Convert type metadata object to array for iteration
	const presetTypes = Object.values(presetTypesMetadata);

	// Filter presets based on selected type and search query
	const filteredPresets = useMemo(() => {
		let filtered = allPresets;

		// Filter by type
		if (selectedType !== 'all') {
			filtered = filtered.filter(preset => preset.type === selectedType);
		}

		// Filter by search query
		if (searchQuery) {
			const query = searchQuery.toLowerCase();
			filtered = filtered.filter(preset =>
				preset.name.toLowerCase().includes(query) ||
				preset.description.toLowerCase().includes(query)
			);
		}

		return filtered;
	}, [allPresets, selectedType, searchQuery]);

	const handlePresetClick = (preset) => {
		onSelectPreset(preset);
		onClose();
	};

	if (!isOpen) return null;

	return (
		<Modal
			title={__('Choose Schema Preset', 'swift-rank-pro')}
			onRequestClose={onClose}
			className="schema-preset-modal"
			style={{ maxWidth: '1200px' }}
		>
			<div className="schema-preset-modal-content">
			{/* Left Panel - Types Filter */}
			<div className="preset-types-panel">
					<div className="panel-header">
						<h3>
							{__('Types', 'swift-rank-pro')}
						</h3>
					</div>
					<div className="types-list">
						<button
							className={`type-item ${selectedType === 'all' ? 'active' : ''}`}
							onClick={() => setSelectedType('all')}
						>
							<Icon name="layout-grid" size={16} />
							<span>{__('All Types', 'swift-rank-pro')}</span>
							<span className="count">
								{allPresets.length}
							</span>
						</button>
						{presetTypes.map(typeInfo => {
							const count = allPresets.filter(p => p.type === typeInfo.value).length;
							const isActive = selectedType === typeInfo.value;
							return (
								<button
									key={typeInfo.value}
									className={`type-item ${isActive ? 'active' : ''}`}
									onClick={() => setSelectedType(typeInfo.value)}
								>
									<Icon name={typeInfo.icon || 'file-text'} size={16} />
									<span>{typeInfo.label}</span>
									<span className="count">
										{count}
									</span>
								</button>
							);
						})}
					</div>
				</div>

				{/* Right Panel - Presets Grid */}
				<div className="preset-grid-panel">
					<div className="panel-header">
						<SearchControl
							value={searchQuery}
							onChange={setSearchQuery}
							placeholder={__('Search presets...', 'swift-rank-pro')}
						/>
					</div>

					<div className="presets-grid">
						{filteredPresets.length === 0 ? (
							<div className="no-presets">
								<Icon name="search" size={48} />
								<p>
									{__('No presets found', 'swift-rank-pro')}
								</p>
							</div>
						) : (
							filteredPresets.map(preset => (
								<button
									key={preset.id}
									className="preset-card"
									onClick={() => handlePresetClick(preset)}
								>
									<div className="preset-content">
										<h4>
											{preset.name}
										</h4>
										<p>
											{preset.description}
										</p>
									<span className="preset-type">
										{presetTypesMetadata[preset.type]?.label || preset.type}
									</span>
									</div>
								</button>
							))
						)}
					</div>
				</div>
			</div>
		</Modal>
	);
};

export default SchemaPresetModal;
