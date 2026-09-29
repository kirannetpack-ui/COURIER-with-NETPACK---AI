/**
 * Vercel Serverless Function Bridge for PHP / Laravel
 * Powers high-performance PHP 8.5 execution on Vercel Node Runtime
 */

const path = require('path');
const fs = require('fs');
const http = require('http');
const net = require('net');
const { spawn } = require('child_process');

let phpServer = null;
const phpServerPort = 8000;
let startPromise = null;

function waitForPort(port, timeoutMs = 4000) {
    const startTime = Date.now();
    return new Promise((resolve, reject) => {
        function check() {
            const client = net.connect({ port, host: '127.0.0.1' }, () => {
                client.destroy();
                resolve(true);
            });
            client.on('error', (err) => {
                client.destroy();
                if (Date.now() - startTime > timeoutMs) {
                    reject(new Error(`Timeout waiting for PHP server on port ${port}: ${err.message}`));
                } else {
                    setTimeout(check, 25);
                }
            });
        }
        check();
    });
}

async function ensurePhpServer() {
    if (phpServer && !phpServer.killed) {
        return;
    }
    if (startPromise) {
        return startPromise;
    }

    startPromise = (async () => {
        const pkgDir = path.dirname(require.resolve('@libphp/almalinux-9-v85/package.json'));
        const phpBinDir = path.join(pkgDir, 'native', 'php');
        const phpBin = path.join(phpBinDir, 'php');
        const phpModulesDir = path.join(phpBinDir, 'modules');
        const phpLibDir = path.join(pkgDir, 'native', 'lib');
        const phpIni = path.join(phpBinDir, 'php.ini');

        try {
            fs.chmodSync(phpBin, 0o755);
        } catch (_) {}

        // Ensure required writable storage paths exist in /tmp
        const dirs = [
            '/tmp/storage',
            '/tmp/storage/app',
            '/tmp/storage/app/public',
            '/tmp/storage/framework',
            '/tmp/storage/framework/cache',
            '/tmp/storage/framework/cache/data',
            '/tmp/storage/framework/sessions',
            '/tmp/storage/framework/testing',
            '/tmp/storage/framework/views',
            '/tmp/storage/logs'
        ];
        for (const dir of dirs) {
            if (!fs.existsSync(dir)) {
                try {
                    fs.mkdirSync(dir, { recursive: true });
                } catch (_) {}
            }
        }

        const projectRoot = path.resolve(__dirname, '..');
        const publicDir = path.join(projectRoot, 'public');
        const routerScript = path.join(projectRoot, 'server.php');

        // Ensure SQLite database exists (copy pre-migrated database if available)
        const dbPath = process.env.DB_DATABASE || '/tmp/database.sqlite';
        const sourceDb = path.join(projectRoot, 'database', 'database.sqlite');
        if (!fs.existsSync(dbPath)) {
            try {
                if (fs.existsSync(sourceDb) && fs.statSync(sourceDb).size > 0) {
                    fs.copyFileSync(sourceDb, dbPath);
                    console.log(`[Bridge] Copied pre-seeded SQLite database (${fs.statSync(dbPath).size} bytes) to ${dbPath}`);
                } else {
                    fs.writeFileSync(dbPath, '');
                }
            } catch (err) {
                console.warn('[Bridge] SQLite database initialization warning:', err.message);
            }
        }

        // Prepare custom php.ini in /tmp with resolved extension_dir
        const customPhpIni = '/tmp/php.ini';
        try {
            let iniContent = fs.readFileSync(phpIni, 'utf8');
            iniContent = iniContent.replace(/^extension_dir\s*=.*$/m, `extension_dir = "${phpModulesDir}"`);
            fs.writeFileSync(customPhpIni, iniContent, 'utf8');
            console.log(`[Bridge] Prepared custom php.ini at ${customPhpIni} pointing to modules: ${phpModulesDir}`);
        } catch (e) {
            console.error('[Bridge] Failed to write custom php.ini:', e);
        }

        const env = {
            ...process.env,
            PATH: `${phpBinDir}:${process.env.PATH}`,
            PHP_INI_EXTENSION_DIR: phpModulesDir,
            LD_LIBRARY_PATH: `${phpLibDir}:/usr/lib64:/lib64:${process.env.LD_LIBRARY_PATH || ''}`,
            APP_STORAGE: '/tmp/storage',
            VIEW_COMPILED_PATH: '/tmp/storage/framework/views',
            SESSION_DRIVER: process.env.SESSION_DRIVER || 'cookie',
            CACHE_DRIVER: process.env.CACHE_DRIVER || 'array',
            LOG_CHANNEL: process.env.LOG_CHANNEL || 'stderr',
            DB_CONNECTION: process.env.DB_CONNECTION || 'sqlite',
            DB_DATABASE: dbPath
        };

        const args = [
            '-c', fs.existsSync(customPhpIni) ? customPhpIni : phpIni,
            '-d', `extension_dir=${phpModulesDir}`,
            '-d', 'display_errors=0',
            '-d', 'display_startup_errors=0',
            '-S', `127.0.0.1:${phpServerPort}`,
            '-t', publicDir,
            routerScript
        ];

        console.log(`[Bridge] Starting PHP server: ${phpBin} on port ${phpServerPort}`);
        phpServer = spawn(phpBin, args, {
            env,
            cwd: publicDir,
            stdio: ['pipe', 'pipe', 'pipe']
        });

        phpServer.stdout.on('data', (chunk) => {
            console.log(`[PHP stdout] ${chunk.toString().trim()}`);
        });

        phpServer.stderr.on('data', (chunk) => {
            console.error(`[PHP stderr] ${chunk.toString().trim()}`);
        });

        phpServer.on('exit', (code, signal) => {
            console.warn(`[Bridge] PHP process exited with code=${code}, signal=${signal}`);
            phpServer = null;
        });

        process.on('exit', () => {
            if (phpServer) {
                try {
                    phpServer.kill();
                } catch (_) {}
            }
        });

        await waitForPort(phpServerPort, 5000);
        console.log('[Bridge] PHP built-in server is ready.');
    })();

    try {
        await startPromise;
    } finally {
        startPromise = null;
    }
}

module.exports = async (req, res) => {
    try {
        await ensurePhpServer();

        const host = req.headers.host || 'courier-with-netpack-ai.vercel.app';
        const options = {
            hostname: '127.0.0.1',
            port: phpServerPort,
            path: req.url || '/',
            method: req.method,
            headers: {
                ...req.headers,
                host,
                'x-forwarded-host': host,
                'x-forwarded-proto': 'https',
                'x-forwarded-for': req.headers['x-forwarded-for'] || req.socket.remoteAddress
            }
        };

        const proxyReq = http.request(options, (proxyRes) => {
            console.log(`[Bridge] ${req.method} ${req.url || '/'} -> HTTP ${proxyRes.statusCode}`);
            res.writeHead(proxyRes.statusCode, proxyRes.headers);
            proxyRes.pipe(res);
        });

        proxyReq.on('error', (err) => {
            console.error('[Bridge] Proxy request error:', err);
            if (!res.headersSent) {
                res.writeHead(502, { 'Content-Type': 'text/plain; charset=utf-8' });
                res.end(`Bad Gateway: Error communicating with PHP backend (${err.message})`);
            }
        });

        req.pipe(proxyReq);
    } catch (err) {
        console.error('[Bridge] Fatal invocation error:', err);
        if (!res.headersSent) {
            res.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' });
            res.end(`Serverless Bridge Error: ${err.message}`);
        }
    }
};
