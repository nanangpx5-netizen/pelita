<?php
/**
 * GitHub Updater Configuration
 * @package PELITA
 * @version 1.0.0
 */

// Token Akses GitHub (Personal Access Token)
defined('GITHUB_TOKEN') or define('GITHUB_TOKEN', getenv('GITHUB_TOKEN') ?: '');

// Repository target
defined('GITHUB_OWNER') or define('GITHUB_OWNER', getenv('GITHUB_OWNER') ?: '');
defined('GITHUB_REPO') or define('GITHUB_REPO', getenv('GITHUB_REPO') ?: '');

// Organisasi GitHub (jika ingin memantau organisasi)
defined('GITHUB_ORG') or define('GITHUB_ORG', getenv('GITHUB_ORG') ?: '');

// Base URL API GitHub
defined('GITHUB_API_BASE') or define('GITHUB_API_BASE', 'https://api.github.com');

// User-Agent (wajib untuk GitHub API)
defined('GITHUB_USER_AGENT') or define('GITHUB_USER_AGENT', 'PELITA-Updater/1.0');

// Interval polling (dalam detik) - minimum 60 untuk menghormati rate limit
defined('GITHUB_POLL_INTERVAL') or define('GITHUB_POLL_INTERVAL', (int)(getenv('GITHUB_POLL_INTERVAL') ?: 300));

// Cache timeout (dalam detik)
defined('GITHUB_CACHE_TTL') or define('GITHUB_CACHE_TTL', (int)(getenv('GITHUB_CACHE_TTL') ?: 120));

// Maksimum item per halaman
defined('GITHUB_PER_PAGE') or define('GITHUB_PER_PAGE', (int)(getenv('GITHUB_PER_PAGE') ?: 30));

// Tipe data yang akan diambil (comma-separated: commits, releases, issues, pulls)
defined('GITHUB_FETCH_TYPES') or define('GITHUB_FETCH_TYPES', getenv('GITHUB_FETCH_TYPES') ?: 'commits,releases');

// Direktori penyimpanan cache/data
defined('GITHUB_DATA_DIR') or define('GITHUB_DATA_DIR', dirname(__DIR__) . '/logs/github');
