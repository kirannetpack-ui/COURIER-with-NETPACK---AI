const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('[Vercel Build] Starting Netpack AI build process...');

const projectRoot = path.resolve(__dirname, '..');
const vendorAutoload = path.join(projectRoot, 'vendor', 'autoload.php');

// 1. Ensure Composer dependencies are installed if missing (e.g. Git clone in Vercel CI)
if (!fs.existsSync(vendorAutoload)) {
    console.log('[Vercel Build] vendor/autoload.php not found. Preparing PHP & Composer...');
    try {
        const pkgDir = path.dirname(require.resolve('@libphp/almalinux-9-v85/package.json'));
        const phpBin = path.join(pkgDir, 'native', 'php', 'php');
        const phpLibDir = path.join(pkgDir, 'native', 'lib');
        const phpModulesDir = path.join(pkgDir, 'native', 'php', 'modules');
        
        try {
            fs.chmodSync(phpBin, 0o755);
        } catch (_) {}

        // Download composer.phar if not present
        const composerPhar = path.join(projectRoot, 'composer.phar');
        if (!fs.existsSync(composerPhar)) {
            console.log('[Vercel Build] Downloading composer.phar...');
            execSync('curl -sS https://getcomposer.org/download/latest-stable/composer.phar -o composer.phar', {
                cwd: projectRoot,
                stdio: 'inherit'
            });
        }

        const env = {
            ...process.env,
            LD_LIBRARY_PATH: `${phpLibDir}:/usr/lib64:/lib64:${process.env.LD_LIBRARY_PATH || ''}`
        };

        console.log('[Vercel Build] Running composer install --no-dev --optimize-autoloader...');
        execSync(`"${phpBin}" -d extension_dir="${phpModulesDir}" "${composerPhar}" install --no-dev --prefer-dist --optimize-autoloader --no-interaction`, {
            cwd: projectRoot,
            env,
            stdio: 'inherit'
        });
        console.log('[Vercel Build] Composer dependencies successfully installed.');
    } catch (err) {
        console.warn('[Vercel Build] Warning: Composer install step encountered:', err.message);
    }
} else {
    console.log('[Vercel Build] vendor/autoload.php found. Skipping composer install.');
}

// 2. Build Vite assets
console.log('[Vercel Build] Building Vite assets...');
execSync('npx vite build', { cwd: projectRoot, stdio: 'inherit' });

// 3. Ensure both public and dist exist for Vercel Output Directory detection
const publicDir = path.join(projectRoot, 'public');
const distDir = path.join(projectRoot, 'dist');
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}
fs.cpSync(publicDir, distDir, { recursive: true });
console.log('[Vercel Build] Synced public assets to dist directory.');

console.log('[Vercel Build] Build completed successfully.');
