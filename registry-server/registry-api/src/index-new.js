const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');
const dotenv = require('dotenv');
const path = require('path');
const fs = require('fs').promises;

// Load environment variables
dotenv.config();

const app = express();
const PORT = process.env.PORT || 4000;
const PACKAGES_DIR = process.env.PACKAGES_DIR || '/app/packages';
const UPLOADS_DIR = process.env.UPLOADS_DIR || '/app/uploads';

// Ensure directories exist
async function ensureDirectories() {
  try {
    await fs.mkdir(PACKAGES_DIR, { recursive: true });
    await fs.mkdir(UPLOADS_DIR, { recursive: true });
    console.log('Directories initialized');
  } catch (error) {
    console.error('Error creating directories:', error);
  }
}

// Initialize directories
ensureDirectories();

// Middleware
app.use(helmet({
  crossOriginResourcePolicy: { policy: "cross-origin" }
}));
app.use(compression());
app.use(cors({
  origin: process.env.CORS_ORIGINS?.split(',') || [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost:8000',
    'http://127.0.0.1:8000'
  ],
  credentials: true
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Simple rate limiting without Redis dependency
const rateLimitMap = new Map();
app.use((req, res, next) => {
  const clientIp = req.ip || req.connection.remoteAddress;
  const now = Date.now();
  const windowMs = 60 * 1000; // 1 minute
  const maxRequests = 100;

  if (!rateLimitMap.has(clientIp)) {
    rateLimitMap.set(clientIp, { count: 1, resetTime: now + windowMs });
    return next();
  }

  const clientData = rateLimitMap.get(clientIp);
  if (now > clientData.resetTime) {
    rateLimitMap.set(clientIp, { count: 1, resetTime: now + windowMs });
    return next();
  }

  if (clientData.count >= maxRequests) {
    return res.status(429).json({ 
      error: 'Rate limit exceeded',
      message: 'Too many requests, please try again later'
    });
  }

  clientData.count++;
  next();
});

// Simple packages store (in production, use a proper database)
let packagesStore = {};
let packageStats = {};

// Load existing packages from registry folder
async function loadExistingPackages() {
  try {
    const registryPath = path.join(__dirname, '../../../registry/api/packages');
    const files = await fs.readdir(registryPath);
    
    for (const file of files) {
      if (file.endsWith('.json')) {
        const packageData = JSON.parse(await fs.readFile(path.join(registryPath, file), 'utf8'));
        packagesStore[packageData.name] = packageData;
        packageStats[packageData.name] = {
          downloads: Math.floor(Math.random() * 10000),
          weeklyDownloads: Math.floor(Math.random() * 1000),
          lastDownload: new Date().toISOString()
        };
      }
    }
    console.log(`Loaded ${Object.keys(packagesStore).length} packages from registry`);
  } catch (error) {
    console.log('No existing packages found, starting with empty registry');
  }
}

loadExistingPackages();

// API Routes
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'healthy', 
    timestamp: new Date().toISOString(),
    packagesCount: Object.keys(packagesStore).length
  });
});

// Get all packages
app.get('/api/packages', (req, res) => {
  const { search, sort = 'popularity', limit = 20, offset = 0 } = req.query;
  let packages = Object.values(packagesStore);

  // Search functionality
  if (search) {
    const searchLower = search.toLowerCase();
    packages = packages.filter(pkg => 
      pkg.name.toLowerCase().includes(searchLower) ||
      pkg.description?.toLowerCase().includes(searchLower) ||
      pkg.keywords?.some(keyword => keyword.toLowerCase().includes(searchLower))
    );
  }

  // Add stats to packages
  packages = packages.map(pkg => ({
    ...pkg,
    downloads: packageStats[pkg.name]?.downloads || 0,
    weeklyDownloads: packageStats[pkg.name]?.weeklyDownloads || 0
  }));

  // Sort packages
  switch (sort) {
    case 'popularity':
      packages.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
      break;
    case 'recent':
      packages.sort((a, b) => new Date(b.updated_at || 0) - new Date(a.updated_at || 0));
      break;
    case 'name':
      packages.sort((a, b) => a.name.localeCompare(b.name));
      break;
  }

  // Pagination
  const total = packages.length;
  packages = packages.slice(Number(offset), Number(offset) + Number(limit));

  res.json({
    packages,
    total,
    limit: Number(limit),
    offset: Number(offset)
  });
});

// Get specific package
app.get('/api/packages/:name', (req, res) => {
  const packageName = req.params.name;
  const packageData = packagesStore[packageName];
  
  if (!packageData) {
    return res.status(404).json({ error: 'Package not found' });
  }

  // Increment download count
  if (!packageStats[packageName]) {
    packageStats[packageName] = { downloads: 0, weeklyDownloads: 0 };
  }
  packageStats[packageName].downloads++;
  packageStats[packageName].weeklyDownloads++;
  packageStats[packageName].lastDownload = new Date().toISOString();

  res.json({
    ...packageData,
    downloads: packageStats[packageName].downloads,
    weeklyDownloads: packageStats[packageName].weeklyDownloads
  });
});

// Search packages
app.get('/api/search', (req, res) => {
  const { q, sort = 'relevance', limit = 20 } = req.query;
  
  if (!q) {
    return res.json({ packages: [], total: 0 });
  }

  let packages = Object.values(packagesStore);
  const searchLower = q.toLowerCase();
  
  // Advanced search with relevance scoring
  packages = packages.map(pkg => {
    let score = 0;
    
    // Name match (highest weight)
    if (pkg.name.toLowerCase().includes(searchLower)) {
      score += pkg.name.toLowerCase() === searchLower ? 100 : 50;
    }
    
    // Description match
    if (pkg.description?.toLowerCase().includes(searchLower)) {
      score += 20;
    }
    
    // Keywords match
    if (pkg.keywords?.some(keyword => keyword.toLowerCase().includes(searchLower))) {
      score += 15;
    }
    
    // Author match
    if (pkg.author?.toLowerCase().includes(searchLower)) {
      score += 10;
    }

    return { ...pkg, relevanceScore: score };
  }).filter(pkg => pkg.relevanceScore > 0);

  // Sort by relevance or other criteria
  if (sort === 'relevance') {
    packages.sort((a, b) => b.relevanceScore - a.relevanceScore);
  } else if (sort === 'popularity') {
    packages.sort((a, b) => (packageStats[b.name]?.downloads || 0) - (packageStats[a.name]?.downloads || 0));
  }

  packages = packages.slice(0, Number(limit));

  res.json({
    packages,
    total: packages.length,
    query: q
  });
});

// Stats endpoint
app.get('/api/stats', (req, res) => {
  const totalPackages = Object.keys(packagesStore).length;
  const totalDownloads = Object.values(packageStats).reduce((sum, stats) => sum + (stats.downloads || 0), 0);
  const weeklyDownloads = Object.values(packageStats).reduce((sum, stats) => sum + (stats.weeklyDownloads || 0), 0);
  
  res.json({
    total_packages: totalPackages,
    total_downloads: totalDownloads,
    weekly_downloads: weeklyDownloads,
    total_users: Math.floor(totalDownloads / 10) // Estimated users
  });
});

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    name: 'Dryad Registry API',
    version: '1.0.0',
    description: 'Official package registry for the Dryad programming language',
    endpoints: {
      packages: '/api/packages',
      search: '/api/search',
      stats: '/api/stats',
      health: '/api/health'
    },
    packagesCount: Object.keys(packagesStore).length
  });
});

// Global error handler
app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({
    error: 'Internal server error',
    message: process.env.NODE_ENV === 'development' ? err.message : 'Something went wrong'
  });
});

// 404 handler
app.use('*', (req, res) => {
  res.status(404).json({
    error: 'Endpoint not found',
    message: `Route ${req.originalUrl} not found`
  });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`🌳 Dryad Registry API server running on port ${PORT}`);
  console.log(`📦 Loaded ${Object.keys(packagesStore).length} packages`);
  console.log(`🌐 Environment: ${process.env.NODE_ENV || 'development'}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM received, shutting down gracefully');
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('SIGINT received, shutting down gracefully');
  process.exit(0);
});

module.exports = app;