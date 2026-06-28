const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const version = '1.1.10';
const pluginSlug = 'karasu-woo-pannel';
const distDir = path.join(__dirname, 'dist');
const buildDir = path.join(distDir, pluginSlug);
const zipFile = path.join(distDir, `${pluginSlug}-${version}.zip`);

// Ensure clean dist dir
if (fs.existsSync(distDir)) {
    fs.rmSync(distDir, { recursive: true, force: true });
}
fs.mkdirSync(buildDir, { recursive: true });

// Read .distignore or use default ignores
const ignoreFile = path.join(__dirname, '.distignore');
const ignores = [
    'docs/',
    'package.json',
    'package-lock.json',
    'tailwind.config.js',
    'INSTALL.md',
    'README.md',
    'node_modules/',
    '.git/',
    '.github/',
    '.gitignore',
    '.distignore',
    'src/',
    'karasu-woo-pannel/',
    'dist/',
    'build.js'
];
if (fs.existsSync(ignoreFile)) {
    ignores.length = 0; // Clear default ignores if file exists
    fs.readFileSync(ignoreFile, 'utf8')
        .split('\n')
        .map(line => line.trim())
        .filter(line => line && !line.startsWith('#'))
        .forEach(line => ignores.push(line));
}

// Helper to check if file/folder should be ignored
function shouldIgnore(relativeFilePath) {
    const normalizedPath = relativeFilePath.replace(/\\/g, '/');
    return ignores.some(ignore => {
        if (ignore.endsWith('/')) {
            const cleanIgnore = ignore.slice(0, -1);
            return normalizedPath === cleanIgnore || normalizedPath.startsWith(cleanIgnore + '/');
        }
        return normalizedPath === ignore || normalizedPath.startsWith(ignore + '/');
    });
}

// Copy directory recursively
function copyRecursive(src, dest, relPath = '') {
    const stats = fs.statSync(src);
    if (stats.isDirectory()) {
        if (relPath && shouldIgnore(relPath + '/')) {
            return;
        }
        if (!fs.existsSync(dest)) {
            fs.mkdirSync(dest, { recursive: true });
        }
        const files = fs.readdirSync(src);
        for (const file of files) {
            copyRecursive(path.join(src, file), path.join(dest, file), relPath ? `${relPath}/${file}` : file);
        }
    } else {
        if (shouldIgnore(relPath)) {
            return;
        }
        fs.copyFileSync(src, dest);
    }
}

console.log('Copying files...');
copyRecursive(__dirname, buildDir);

console.log('Zipping package...');
try {
    if (process.platform === 'win32') {
        // Zip using PowerShell Compress-Archive
        execSync(`powershell -Command "Compress-Archive -Path '${buildDir}' -DestinationPath '${zipFile}' -Force"`);
    } else {
        execSync(`cd dist && zip -r "${pluginSlug}-${version}.zip" "${pluginSlug}"`);
    }
    console.log(`Successfully built ${zipFile}`);
} catch (err) {
    console.error('Error creating zip archive:', err.message);
}
