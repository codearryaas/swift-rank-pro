import { __ } from '@wordpress/i18n';
import { useState, useEffect, useRef } from '@wordpress/element';
import { Button } from '@wordpress/components';
import Icon from '../../../swift-rank/src/components/Icon';

const OpeningHoursField = ({ value, onChange, label, tooltip, isOverridden, onReset }) => {
	const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

	// Get Tooltip component from base plugin
	const Tooltip = window.swiftRankComponents?.Tooltip || null;

	// Track if user has interacted with the field
	const hasUserInteracted = useRef(false);

	// Default hours - all days closed by default
	const getDefaultHours = () => {
		const defaultHours = {};
		daysOfWeek.forEach(day => {
			defaultHours[day] = {
				closed: true,
				opens: '09:00',
				closes: '17:00'
			};
		});
		return defaultHours;
	};

	// Initialize state with default hours or existing value
	const [hours, setHours] = useState(() => {
		if (value && typeof value === 'object' && Object.keys(value).length > 0) {
			return value;
		}
		return getDefaultHours();
	});

	// Update parent when hours change - only if user interacted
	useEffect(() => {
		if (hasUserInteracted.current) {
			onChange(hours);
		}
	}, [hours]);

	// Sync with external value changes (e.g., reset)
	useEffect(() => {
		if (value && typeof value === 'object' && Object.keys(value).length > 0) {
			// Only sync if not from user interaction
			if (!hasUserInteracted.current) {
				setHours(value);
			}
		}
	}, [value]);

	const handleClosedToggle = (day) => {
		hasUserInteracted.current = true;
		setHours({
			...hours,
			[day]: {
				...hours[day],
				closed: !hours[day].closed
			}
		});
	};

	const handleTimeChange = (day, field, timeValue) => {
		hasUserInteracted.current = true;
		setHours({
			...hours,
			[day]: {
				...hours[day],
				[field]: timeValue
			}
		});
	};

	return (
		<div className={`schema-field ${isOverridden ? 'has-override' : ''}`} style={{ marginBottom: '20px' }}>
			<div className="field-header" style={{ marginBottom: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
				<label className="field-label" style={{ display: 'flex', alignItems: 'center', gap: '6px', fontWeight: 600, fontSize: '13px', color: '#1d2327' }}>
					{label}
					{Tooltip && tooltip && <Tooltip text={tooltip} />}
				</label>
				{isOverridden && onReset && (
					<div className="field-actions">
						<Button
							variant="tertiary"
							isDestructive
							onClick={onReset}
							className="reset-btn field-action-btn"
							label={__('Reset to default', 'swift-rank')}
						>
							<Icon name="refresh-cw" size={16} />
						</Button>
					</div>
				)}
			</div>
			<div className="swift-rank-opening-hours-container">
				{daysOfWeek.map(day => {
					const dayData = hours[day] || { closed: false, opens: '09:00', closes: '17:00' };
					const isClosed = dayData.closed;

					return (
						<div
							key={day}
							className={`swift-rank-hours-row ${isClosed ? 'closed' : ''}`}
						>
							<label className="day-label">{day}</label>
							<label style={{ display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px' }}>
								<input
									type="checkbox"
									checked={isClosed}
									onChange={() => handleClosedToggle(day)}
									className="swift-rank-closed-checkbox"
								/>
								<span className="swift-rank-closed-label">Closed</span>
							</label>
							<div>
								<label style={{ display: 'block', marginBottom: '2px', fontSize: '11px', color: '#646970' }}>
									Opens
								</label>
								<input
									type="time"
									value={dayData.opens}
									onChange={(e) => handleTimeChange(day, 'opens', e.target.value)}
									className="regular-text swift-rank-opens-time"
									disabled={isClosed}
								/>
							</div>
							<div>
								<label style={{ display: 'block', marginBottom: '2px', fontSize: '11px', color: '#646970' }}>
									Closes
								</label>
								<input
									type="time"
									value={dayData.closes}
									onChange={(e) => handleTimeChange(day, 'closes', e.target.value)}
									className="regular-text swift-rank-closes-time"
									disabled={isClosed}
								/>
							</div>
						</div>
					);
				})}
			</div>
			<p className="description" style={{ marginTop: '12px' }}>
				{__('Check "Closed" for days when your business is not open. Times are in 24-hour format.', 'swift-rank')}
			</p>
		</div>
	);
};

export default OpeningHoursField;
