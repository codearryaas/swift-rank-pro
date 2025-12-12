import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextControl, SelectControl, Card, CardHeader, CardBody } from '@wordpress/components';
import Icon from '../../../swift-rank/src/components/Icon';

// Helper to get value at path
const getValueAtPath = (obj, path) => {
    let current = obj;
    for (const key of path) {
        if (current === undefined) return undefined;
        current = current[key];
    }
    return current;
};

// Helper to set value at path (immutable)
const setValueAtPath = (obj, path, value) => {
    if (path.length === 0) return value;

    const [head, ...tail] = path;
    const newObj = Array.isArray(obj) ? [...obj] : { ...obj };

    newObj[head] = setValueAtPath(obj[head], tail, value);
    return newObj;
};

// Helper to add property
const addPropertyToPath = (obj, path, key, value) => {
    const target = getValueAtPath(obj, path);
    if (typeof target !== 'object' || target === null) return obj;

    const newTarget = Array.isArray(target)
        ? [...target, value]
        : { ...target, [key]: value };

    return setValueAtPath(obj, path, newTarget);
};

// Helper to remove property
const removePropertyFromPath = (obj, path, key) => {
    const target = getValueAtPath(obj, path);
    if (typeof target !== 'object' || target === null) return obj;

    let newTarget;
    if (Array.isArray(target)) {
        newTarget = target.filter((_, index) => index !== key);
    } else {
        newTarget = { ...target };
        delete newTarget[key];
    }

    return setValueAtPath(obj, path, newTarget);
};

// Get icon for data type
const getTypeIcon = (data) => {
    if (Array.isArray(data)) return 'list-checks';
    if (typeof data === 'object' && data !== null) return 'file-text';
    if (typeof data === 'string') return 'file-text';
    if (typeof data === 'number') return 'hash';
    if (typeof data === 'boolean') return 'check';
    return 'circle';
};

const SchemaNode = ({ data, path, onSelect, selectedPath, onToggle, expandedPaths }) => {
    const isSelected = JSON.stringify(path) === JSON.stringify(selectedPath);
    const isExpanded = expandedPaths.has(JSON.stringify(path));
    const isObject = typeof data === 'object' && data !== null && !Array.isArray(data);
    const isArray = Array.isArray(data);
    const hasChildren = isObject || isArray;

    const label = path.length > 0 ? path[path.length - 1] : 'root';
    const type = isArray ? 'Array' : (isObject ? (data['@type'] || 'Object') : typeof data);
    const icon = getTypeIcon(data);

    return (
        <div className="schema-node-wrapper">
            <div
                className={`schema-node-row ${isSelected ? 'is-selected' : ''}`}
                onClick={(e) => {
                    e.stopPropagation();
                    onSelect(path);
                }}
                style={{
                    padding: '10px 12px',
                    paddingLeft: `${path.length * 24 + 12}px`,
                    cursor: 'pointer',
                    backgroundColor: isSelected ? '#f0f6fc' : 'transparent',
                    borderLeft: isSelected ? '3px solid #2271b1' : '3px solid transparent',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px',
                    transition: 'all 0.15s ease',
                    borderRadius: '2px',
                    margin: '2px 4px'
                }}
                onMouseEnter={(e) => {
                    if (!isSelected) {
                        e.currentTarget.style.backgroundColor = '#f9f9f9';
                    }
                }}
                onMouseLeave={(e) => {
                    if (!isSelected) {
                        e.currentTarget.style.backgroundColor = 'transparent';
                    }
                }}
            >
                {hasChildren && (
                    <span
                        onClick={(e) => {
                            e.stopPropagation();
                            onToggle(path);
                        }}
                        style={{
                            cursor: 'pointer',
                            flexShrink: 0,
                            display: 'inline-flex',
                            alignItems: 'center'
                        }}
                    >
                        <Icon
                            name={isExpanded ? 'chevron-down' : 'chevron-right'}
                            size={16}
                            color="#666"
                        />
                    </span>
                )}
                {!hasChildren && <span style={{ width: '16px', flexShrink: 0 }}></span>}

                <Icon name={icon} size={16} style={{ color: '#2271b1', flexShrink: 0 }} />

                <span style={{ fontWeight: 500, flex: 1, minWidth: 0, overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {label}
                </span>

                <span style={{
                    fontSize: '11px',
                    color: '#666',
                    background: isSelected ? '#e5f2ff' : '#f0f0f0',
                    padding: '3px 8px',
                    borderRadius: '10px',
                    fontWeight: 500,
                    flexShrink: 0
                }}>
                    {type}
                </span>
            </div>

            {hasChildren && isExpanded && (
                <div className="schema-node-children">
                    {Object.entries(data).map(([key, value]) => {
                        return (
                            <SchemaNode
                                key={key}
                                data={value}
                                path={[...path, isArray ? parseInt(key) : key]}
                                onSelect={onSelect}
                                selectedPath={selectedPath}
                                onToggle={onToggle}
                                expandedPaths={expandedPaths}
                            />
                        );
                    })}
                </div>
            )}
        </div>
    );
};

const CustomSchemaBuilder = ({ value, onChange }) => {
    const [schema, setSchema] = useState(() => {
        try {
            return value ? JSON.parse(value) : { '@context': 'https://schema.org', '@type': 'Thing' };
        } catch (e) {
            return { '@context': 'https://schema.org', '@type': 'Thing' };
        }
    });

    const [selectedPath, setSelectedPath] = useState([]);
    const [expandedPaths, setExpandedPaths] = useState(new Set([JSON.stringify([])]));
    const [newPropName, setNewPropName] = useState('');
    const [newPropType, setNewPropType] = useState('text');

    // Access shared components
    const VariablesPopup = window.swiftRankComponents?.VariablesPopup;

    useEffect(() => {
        onChange(JSON.stringify(schema));
    }, [schema]);

    const handleToggle = (path) => {
        const pathStr = JSON.stringify(path);
        const newExpanded = new Set(expandedPaths);
        if (newExpanded.has(pathStr)) {
            newExpanded.delete(pathStr);
        } else {
            newExpanded.add(pathStr);
        }
        setExpandedPaths(newExpanded);
    };

    const selectedData = getValueAtPath(schema, selectedPath);
    const selectedKey = selectedPath.length > 0 ? selectedPath[selectedPath.length - 1] : 'root';
    const isRoot = selectedPath.length === 0;
    const isContainer = typeof selectedData === 'object' && selectedData !== null;

    const handleAddProperty = () => {
        if (!newPropName && !Array.isArray(selectedData)) return;

        let initialValue = '';
        if (newPropType === 'object') initialValue = { '@type': 'Thing' };
        if (newPropType === 'array') initialValue = [];

        const key = Array.isArray(selectedData) ? selectedData.length : newPropName;

        const newSchema = addPropertyToPath(schema, selectedPath, key, initialValue);
        setSchema(newSchema);
        setNewPropName('');

        // Expand the parent
        const pathStr = JSON.stringify(selectedPath);
        if (!expandedPaths.has(pathStr)) {
            const newExpanded = new Set(expandedPaths);
            newExpanded.add(pathStr);
            setExpandedPaths(newExpanded);
        }
    };

    const handleDeleteNode = () => {
        if (isRoot) return;
        const parentPath = selectedPath.slice(0, -1);
        const key = selectedPath[selectedPath.length - 1];
        const newSchema = removePropertyFromPath(schema, parentPath, key);
        setSchema(newSchema);
        setSelectedPath(parentPath);
    };

    const handleValueChange = (val) => {
        const newSchema = setValueAtPath(schema, selectedPath, val);
        setSchema(newSchema);
    };

    return (
        <div className="custom-schema-builder" style={{ border: '1px solid #dcdcde', borderRadius: '4px', background: '#fff', overflow: 'hidden' }}>
            {/* Toolbar */}
            <div className="builder-toolbar" style={{
                padding: '16px 20px',
                borderBottom: '1px solid #dcdcde',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                background: 'linear-gradient(to bottom, #f9f9f9, #f3f3f3)'
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <Icon name="settings" size={20} style={{ color: '#2271b1' }} />
                    <h3 style={{ margin: 0, fontSize: '15px', fontWeight: 600, color: '#1d2327' }}>
                        {__('Custom Schema Builder', 'swift-rank-pro')}
                    </h3>
                </div>
                <div style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px',
                    fontSize: '12px',
                    color: '#646970',
                    background: '#fff',
                    padding: '6px 12px',
                    borderRadius: '4px',
                    border: '1px solid #dcdcde'
                }}>
                    <Icon name="globe" size={14} />
                    <span>{selectedPath.length > 0 ? selectedPath.join(' › ') : __('Root', 'swift-rank-pro')}</span>
                </div>
            </div>

            {/* Main Content */}
            <div className="builder-body" style={{ display: 'flex', height: '500px' }}>
                {/* Structure Panel */}
                <div className="structure-panel" style={{
                    width: '40%',
                    borderRight: '1px solid #dcdcde',
                    overflowY: 'auto',
                    background: '#fff'
                }}>
                    <div style={{ padding: '12px 8px', background: '#f6f7f7', borderBottom: '1px solid #dcdcde' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px', fontSize: '13px', fontWeight: 600, color: '#50575e' }}>
                            <Icon name="globe" size={16} />
                            {__('Schema Structure', 'swift-rank-pro')}
                        </div>
                    </div>
                    <SchemaNode
                        data={schema}
                        path={[]}
                        onSelect={setSelectedPath}
                        selectedPath={selectedPath}
                        onToggle={handleToggle}
                        expandedPaths={expandedPaths}
                    />
                </div>

                {/* Inspector Panel */}
                <div className="inspector-panel" style={{
                    flex: 1,
                    padding: '20px',
                    overflowY: 'auto',
                    background: '#f6f7f7'
                }}>
                    <Card style={{ marginBottom: '16px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)' }}>
                        <CardHeader style={{
                            display: 'flex',
                            justifyContent: 'space-between',
                            alignItems: 'center',
                            padding: '12px 16px',
                            borderBottom: '1px solid #f0f0f1'
                        }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                <Icon name={isRoot ? 'file-text' : getTypeIcon(selectedData)} size={18} style={{ color: '#2271b1' }} />
                                <h4 style={{ margin: 0, fontSize: '14px', fontWeight: 600 }}>
                                    {isRoot ? __('Root Object', 'swift-rank-pro') : selectedKey}
                                </h4>
                            </div>
                            {!isRoot && (
                                <Button
                                    isDestructive
                                    variant="tertiary"
                                    size="small"
                                    onClick={handleDeleteNode}
                                >
                                    <Icon name="trash-2" size={16} />
                                    {__('Delete', 'swift-rank-pro')}
                                </Button>
                            )}
                        </CardHeader>

                        <CardBody style={{ padding: '16px' }}>
                            {isContainer ? (
                                <div className="add-property-section">
                                    <div style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '8px',
                                        marginBottom: '12px',
                                        paddingBottom: '12px',
                                        borderBottom: '1px solid #f0f0f1'
                                    }}>
                                        <Icon name="plus" size={16} style={{ color: '#2271b1' }} />
                                        <h5 style={{ margin: 0, fontSize: '13px', fontWeight: 600 }}>
                                            {Array.isArray(selectedData) ? __('Add Array Item', 'swift-rank-pro') : __('Add Property', 'swift-rank-pro')}
                                        </h5>
                                    </div>

                                    <div>
                                        {!Array.isArray(selectedData) && (
                                            <div style={{ marginBottom: '16px' }}>
                                                <TextControl
                                                    label={__('Property Name', 'swift-rank-pro')}
                                                    value={newPropName}
                                                    onChange={setNewPropName}
                                                    placeholder="e.g., author, datePublished"
                                                    help={__('Use camelCase for property names', 'swift-rank-pro')}
                                                />
                                            </div>
                                        )}
                                        <div style={{ display: 'flex', gap: '12px', alignItems: 'flex-end', marginBottom: '16px' }}>
                                            <div style={{ flex: 1, maxWidth: '200px' }}>
                                                <SelectControl
                                                    label={__('Value Type', 'swift-rank-pro')}
                                                    value={newPropType}
                                                    onChange={setNewPropType}
                                                    options={[
                                                        { label: __('Text', 'swift-rank-pro'), value: 'text' },
                                                        { label: __('Object', 'swift-rank-pro'), value: 'object' },
                                                        { label: __('Array', 'swift-rank-pro'), value: 'array' }
                                                    ]}
                                                />
                                            </div>
                                            <div style={{ marginBottom: '3px' }}>
                                                <Button
                                                    variant="primary"
                                                    onClick={handleAddProperty}
                                                    disabled={!Array.isArray(selectedData) && !newPropName}
                                                >
                                                    <Icon name="plus" size={16} />
                                                    {__('Add', 'swift-rank-pro')}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Edit @type if object */}
                                    {!Array.isArray(selectedData) && (
                                        <div style={{
                                            marginTop: '16px',
                                            paddingTop: '16px',
                                            borderTop: '1px solid #f0f0f1'
                                        }}>
                                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                                                <Icon name="brackets" size={16} style={{ color: '#2271b1' }} />
                                                <span style={{ fontSize: '13px', fontWeight: 600 }}>
                                                    {__('Schema Type', 'swift-rank-pro')}
                                                </span>
                                            </div>
                                            <TextControl
                                                label="@type"
                                                value={selectedData['@type'] || ''}
                                                onChange={(val) => {
                                                    const newSchema = setValueAtPath(schema, [...selectedPath, '@type'], val);
                                                    setSchema(newSchema);
                                                }}
                                                help={__('Schema.org type (e.g., Article, Person, Organization)', 'swift-rank-pro')}
                                                placeholder="Thing"
                                            />
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="edit-value-section">
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                                        <Icon name="pencil" size={16} style={{ color: '#2271b1' }} />
                                        <span style={{ fontSize: '13px', fontWeight: 600 }}>
                                            {__('Edit Value', 'swift-rank-pro')}
                                        </span>
                                    </div>
                                    <div style={{ display: 'flex', gap: '12px', alignItems: 'flex-end' }}>
                                        <div style={{ flex: 1 }}>
                                            <TextControl
                                                label={__('Value', 'swift-rank-pro')}
                                                value={selectedData}
                                                onChange={handleValueChange}
                                                help={__('Use variables like {post_title} for dynamic content', 'swift-rank-pro')}
                                            />
                                        </div>
                                        {VariablesPopup && (
                                            <div style={{ marginBottom: '33px' }}>
                                                <VariablesPopup
                                                    onSelect={(variable) => {
                                                        handleValueChange(selectedData + variable);
                                                    }}
                                                />
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </CardBody>
                    </Card>
                </div>
            </div>

            {/* JSON Preview */}
            <div className="preview-panel" style={{
                padding: '16px',
                background: '#282c34',
                color: '#abb2bf',
                overflow: 'auto',
                maxHeight: '200px',
                borderTop: '1px solid #dcdcde',
                fontFamily: 'Consolas, Monaco, monospace'
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px', color: '#61dafb' }}>
                    <Icon name="code" size={16} />
                    <span style={{ fontSize: '12px', fontWeight: 600 }}>
                        {__('JSON Preview', 'swift-rank-pro')}
                    </span>
                </div>
                <pre style={{ margin: 0, fontSize: '12px', whiteSpace: 'pre-wrap', lineHeight: '1.6' }}>
                    {JSON.stringify(schema, null, 2)}
                </pre>
            </div>
        </div>
    );
};

export default CustomSchemaBuilder;
