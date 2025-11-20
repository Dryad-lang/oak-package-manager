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
  origin: process.env.ALLOWED_ORIGINS?.split(',') || ['http://oak.dryadlang.org:3001'],
  credentials: true
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Rate limiting middleware
app.use(async (req, res, next) => {
  try {
    await rateLimiter.consume(req.ip);
    next();
  } catch (rejRes) {
    logger.warn(`Rate limit exceeded for IP: ${req.ip}`);
    res.status(429).json({ 
      error: 'Rate limit exceeded',
      retryAfter: rejRes.msBeforeNext 
    });
  }
});

// Request logging middleware
app.use((req, res, next) => {
  logger.info(`${req.method} ${req.url} - ${req.ip}`);
  next();
});

// Import routes
const packagesRouter = require('./routes/packages');
const authRouter = require('./routes/auth');
const healthRouter = require('./routes/health');
const uploadRouter = require('./routes/upload');

// Routes
app.use('/api/packages', packagesRouter);
app.use('/api/auth', authRouter);
app.use('/api/health', healthRouter);
app.use('/api/upload', uploadRouter);

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    name: 'Dryad Registry API',
    version: '1.0.0',
    description: 'Official package registry for the Dryad programming language',
    endpoints: {
      packages: '/api/packages',
      auth: '/api/auth',
      upload: '/api/upload',
      health: '/api/health'
    },
    documentation: 'https://docs.dryad-lang.org/registry-api'
  });
});

// Global error handler
app.use((err, req, res, next) => {
  logger.error('Unhandled error:', err);
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
  logger.info(`Dryad Registry API server running on port ${PORT}`);
  logger.info(`Environment: ${process.env.NODE_ENV || 'development'}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM received, shutting down gracefully');
  redisClient.quit();
  process.exit(0);
});

process.on('SIGINT', () => {
  logger.info('SIGINT received, shutting down gracefully');
  redisClient.quit();
  process.exit(0);
});

module.exports = app;