/**
 * License Tab Component
 *
 * @package Swift_Rank_Pro
 */

import { __ } from '@wordpress/i18n';
import Icon from '../../../swift-rank/src/components/Icon';

const LicenseTab = () => {
    const licenseData = window.swiftRankProLicense || {};
    const isLicenseActive = licenseData.status === 'active';

    return (
        <div className="swift-rank-license-tab">
            <h2>{__('License', 'swift-rank-pro')}</h2>
            <p className="description">
                {__('Your Swift Rank Pro license status and information.', 'swift-rank-pro')}
            </p>

            <table className="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            {__('License Status', 'swift-rank-pro')}
                        </th>
                        <td>
                            {isLicenseActive ? (
                                <span className="swift-rank-license-status active">
                                    <Icon name="check" size={20} color="#00a32a" />
                                    {__('Active', 'swift-rank-pro')}
                                </span>
                            ) : (
                                <span className="swift-rank-license-status inactive">
                                    <Icon name="shield" size={20} color="#d63638" />
                                    {__('Inactive', 'swift-rank-pro')}
                                </span>
                            )}
                        </td>
                    </tr>
                    {licenseData.plan && (
                        <tr>
                            <th scope="row">
                                {__('Plan', 'swift-rank-pro')}
                            </th>
                            <td>{licenseData.plan}</td>
                        </tr>
                    )}
                    {licenseData.expiration && (
                        <tr>
                            <th scope="row">
                                {__('Expiration', 'swift-rank-pro')}
                            </th>
                            <td>{licenseData.expiration}</td>
                        </tr>
                    )}
                    <tr>
                        <th scope="row">
                            {__('Account', 'swift-rank-pro')}
                        </th>
                        <td>
                            {isLicenseActive && licenseData.accountUrl ? (
                                <a
                                    href={licenseData.accountUrl}
                                    className="button"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {__('Manage Account', 'swift-rank-pro')}
                                </a>
                            ) : (
                                <a
                                    href={licenseData.activationUrl || '#'}
                                    className="button button-primary"
                                >
                                    {__('Activate License', 'swift-rank-pro')}
                                </a>
                            )}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div className="swift-rank-license-info">
                <h3>{__('License Benefits', 'swift-rank-pro')}</h3>
                <ul>
                    <li>
                        <Icon name="check" size={16} color="#00a32a" />
                        {__('Automatic plugin updates', 'swift-rank-pro')}
                    </li>
                    <li>
                        <Icon name="check" size={16} color="#00a32a" />
                        {__('Premium support', 'swift-rank-pro')}
                    </li>
                    <li>
                        <Icon name="check" size={16} color="#00a32a" />
                        {__('All Pro schema types', 'swift-rank-pro')}
                    </li>
                    <li>
                        <Icon name="check" size={16} color="#00a32a" />
                        {__('Advanced variables & conditions', 'swift-rank-pro')}
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default LicenseTab;
