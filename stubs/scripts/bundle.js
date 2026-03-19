#!/usr/bin/env node

const path     = require('path');
const fs       = require('fs');
const { execSync } = require('child_process');
const archiver = require('archiver');

const { name: slug } = require('../package.json');
const rootDir = path.resolve(__dirname, '..');
const distDir = path.join(rootDir, 'dist');
const zipPath = path.join(distDir, `${slug}.zip`);

const exclude = new Set([
    'node_modules',
    'resources',
    'tests',
    'dist',
    'scripts',
    '.git',
    '.gitignore',
    'webpack.config.js',
    'package.json',
    'package-lock.json',
    'composer.json',
    'composer.lock',
    'phpunit.xml',
]);

// 1. Build assets
console.log('Building assets...');
execSync('npm run build', { stdio: 'inherit', cwd: rootDir });

// 2. Generate POT file
console.log('\nGenerating POT file...');
execSync('npm run i18n:pot', { stdio: 'inherit', cwd: rootDir });

// 3. Generate JSON files for JS translations
console.log('\nGenerating JSON translation files...');
execSync('npm run i18n:json', { stdio: 'inherit', cwd: rootDir });

// 4. Install production PHP deps
console.log('\nInstalling production dependencies...');
execSync('composer install --no-dev --optimize-autoloader', { stdio: 'inherit', cwd: rootDir });

// 5. Create zip
fs.mkdirSync(distDir, { recursive: true });

const output  = fs.createWriteStream(zipPath);
const archive = archiver('zip', { zlib: { level: 9 } });

archive.on('error', err => { throw err; });
archive.pipe(output);

for (const file of fs.readdirSync(rootDir)) {
    if (exclude.has(file)) continue;

    const abs = path.join(rootDir, file);
    fs.statSync(abs).isDirectory()
        ? archive.directory(abs, `${slug}/${file}`)
        : archive.file(abs, { name: `${slug}/${file}` });
}

archive.finalize();

output.on('close', () => {
    const mb = (archive.pointer() / 1024 / 1024).toFixed(2);
    console.log(`\n✓ dist/${slug}.zip  (${mb} MB)`);

    // 6. Restore dev dependencies
    console.log('\nRestoring dev dependencies...');
    execSync('composer install', { stdio: 'inherit', cwd: rootDir });
});