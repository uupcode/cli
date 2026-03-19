// Tell @wordpress/scripts to use resources/ as the source directory.
// This enables automatic block.json discovery and block.json → build/ copying.
process.env.WP_SRC_DIRECTORY = 'resources';

const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = async (env, argv) => {
    const baseEntry = typeof defaultConfig.entry === 'function'
        ? await defaultConfig.entry(env, argv)
        : defaultConfig.entry;

    return {
        ...defaultConfig,
        entry: {
            ...baseEntry,
            admin: './resources/admin.js',
        },
    };
};