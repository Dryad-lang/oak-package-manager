const express = require('express');
const router = express.Router();
const db = require('../database');
const { validatePackageName, validateVersion } = require('../utils/validation');
const logger = require('../utils/logger');
const semver = require('semver');

// Get package information
router.get('/:name', async (req, res) => {
  try {
    const { name } = req.params;
    
    if (!validatePackageName(name)) {
      return res.status(400).json({
        error: 'Invalid package name',
        message: 'Package name must contain only lowercase letters, numbers, hyphens, and underscores'
      });
    }

    const package = await db('packages')
      .where({ name })
      .first();

    if (!package) {
      return res.status(404).json({
        error: 'Package not found',
        message: `Package '${name}' does not exist in the registry`
      });
    }

    // Get all versions for this package
    const versions = await db('package_versions')
      .where({ package_id: package.id })
      .orderBy('created_at', 'desc');

    // Get latest version
    const latestVersion = versions[0];

    // Update download count
    await db('packages')
      .where({ id: package.id })
      .increment('download_count', 1);

    const response = {
      name: package.name,
      version: latestVersion.version,
      description: package.description,
      author: package.author,
      license: package.license,
      homepage: package.homepage,
      repository: package.repository,
      keywords: package.keywords,
      dependencies: latestVersion.dependencies || {},
      dev_dependencies: latestVersion.dev_dependencies || {},
      download_url: `${req.protocol}://${req.get('host')}/packages/${package.name}/${latestVersion.version}.tar.gz`,
      checksum: latestVersion.checksum,
      file_size: latestVersion.file_size,
      published_at: latestVersion.created_at,
      versions: versions.map(v => ({
        version: v.version,
        download_url: `${req.protocol}://${req.get('host')}/packages/${package.name}/${v.version}.tar.gz`,
        checksum: v.checksum,
        published_at: v.created_at,
        deprecated: v.deprecated
      })),
      downloads: {
        total: package.download_count,
        last_month: package.downloads_last_month || 0,
        last_week: package.downloads_last_week || 0
      }
    };

    res.json(response);
  } catch (error) {
    logger.error('Error fetching package:', error);
    res.status(500).json({
      error: 'Internal server error',
      message: 'Failed to fetch package information'
    });
  }
});

// Search packages
router.get('/', async (req, res) => {
  try {
    const { 
      q: query = '', 
      limit = 20, 
      offset = 0,
      sort = 'download_count',
      order = 'desc'
    } = req.query;

    let queryBuilder = db('packages')
      .select(
        'packages.*',
        db.raw('(SELECT version FROM package_versions WHERE package_id = packages.id ORDER BY created_at DESC LIMIT 1) as latest_version'),
        db.raw('(SELECT created_at FROM package_versions WHERE package_id = packages.id ORDER BY created_at DESC LIMIT 1) as last_updated')
      );

    // Apply search filter
    if (query) {
      queryBuilder = queryBuilder.where(function() {
        this.where('name', 'ilike', `%${query}%`)
          .orWhere('description', 'ilike', `%${query}%`)
          .orWhere('keywords', 'ilike', `%${query}%`);
      });
    }

    // Apply sorting
    const validSortFields = ['name', 'download_count', 'created_at'];
    const validOrders = ['asc', 'desc'];
    
    if (validSortFields.includes(sort) && validOrders.includes(order)) {
      queryBuilder = queryBuilder.orderBy(sort, order);
    }

    // Apply pagination
    const packages = await queryBuilder
      .limit(parseInt(limit))
      .offset(parseInt(offset));

    // Get total count for pagination
    const totalCountResult = await db('packages')
      .count('* as count')
      .where(function() {
        if (query) {
          this.where('name', 'ilike', `%${query}%`)
            .orWhere('description', 'ilike', `%${query}%`)
            .orWhere('keywords', 'ilike', `%${query}%`);
        }
      })
      .first();

    const totalCount = parseInt(totalCountResult.count);

    const response = {
      packages: packages.map(pkg => ({
        name: pkg.name,
        version: pkg.latest_version,
        description: pkg.description,
        author: pkg.author,
        keywords: pkg.keywords,
        download_count: pkg.download_count,
        last_updated: pkg.last_updated,
        homepage: pkg.homepage
      })),
      pagination: {
        total: totalCount,
        limit: parseInt(limit),
        offset: parseInt(offset),
        has_more: totalCount > parseInt(offset) + parseInt(limit)
      }
    };

    res.json(response);
  } catch (error) {
    logger.error('Error searching packages:', error);
    res.status(500).json({
      error: 'Internal server error',
      message: 'Failed to search packages'
    });
  }
});

// Get package download statistics
router.get('/:name/stats', async (req, res) => {
  try {
    const { name } = req.params;
    
    const package = await db('packages')
      .where({ name })
      .first();

    if (!package) {
      return res.status(404).json({
        error: 'Package not found'
      });
    }

    // Get download statistics by version
    const versionStats = await db('package_versions')
      .select('version', 'download_count')
      .where({ package_id: package.id })
      .orderBy('created_at', 'desc');

    res.json({
      total_downloads: package.download_count,
      downloads_last_month: package.downloads_last_month || 0,
      downloads_last_week: package.downloads_last_week || 0,
      version_stats: versionStats
    });
  } catch (error) {
    logger.error('Error fetching package stats:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

module.exports = router;