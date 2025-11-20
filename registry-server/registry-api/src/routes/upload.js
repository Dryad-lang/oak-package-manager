const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs').promises;
const crypto = require('crypto');
const tar = require('tar');
const db = require('../database');
const { authenticateToken } = require('../middleware/auth');
const { validatePackageJson } = require('../utils/validation');
const logger = require('../utils/logger');
const semver = require('semver');

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: async (req, file, cb) => {
    const uploadDir = '/app/uploads';
    try {
      await fs.mkdir(uploadDir, { recursive: true });
      cb(null, uploadDir);
    } catch (error) {
      cb(error);
    }
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, file.fieldname + '-' + uniqueSuffix + '.tar.gz');
  }
});

const upload = multer({
  storage: storage,
  limits: {
    fileSize: 100 * 1024 * 1024, // 100MB max
  },
  fileFilter: (req, file, cb) => {
    if (file.mimetype === 'application/gzip' || file.mimetype === 'application/x-gzip' || 
        file.originalname.endsWith('.tar.gz') || file.originalname.endsWith('.tgz')) {
      cb(null, true);
    } else {
      cb(new Error('Only .tar.gz files are allowed'), false);
    }
  }
});

// Upload new package
router.post('/', authenticateToken, upload.single('package'), async (req, res) => {
  let tempFilePath = null;
  let extractPath = null;
  
  try {
    if (!req.file) {
      return res.status(400).json({
        error: 'No file uploaded',
        message: 'Please upload a .tar.gz package file'
      });
    }

    tempFilePath = req.file.path;
    const filename = req.file.filename;
    
    // Create temporary extraction directory
    extractPath = path.join('/app/uploads/extract', path.basename(filename, '.tar.gz'));
    await fs.mkdir(extractPath, { recursive: true });
    
    // Extract and validate the package
    try {
      await tar.x({
        file: tempFilePath,
        cwd: extractPath
      });
    } catch (error) {
      return res.status(400).json({
        error: 'Invalid package file',
        message: 'Could not extract the uploaded tar.gz file'
      });
    }

    // Look for oaklibs.json in the extracted content
    const files = await fs.readdir(extractPath);
    let packageDir = extractPath;
    let oaklibsPath = path.join(packageDir, 'oaklibs.json');
    
    // If there's only one directory, check inside it
    if (files.length === 1) {
      const stat = await fs.stat(path.join(extractPath, files[0]));
      if (stat.isDirectory()) {
        packageDir = path.join(extractPath, files[0]);
        oaklibsPath = path.join(packageDir, 'oaklibs.json');
      }
    }

    // Check if oaklibs.json exists
    try {
      await fs.access(oaklibsPath);
    } catch (error) {
      return res.status(400).json({
        error: 'Invalid package structure',
        message: 'Package must contain an oaklibs.json file'
      });
    }

    // Read and validate oaklibs.json
    const oaklibsContent = await fs.readFile(oaklibsPath, 'utf8');
    let packageJson;
    
    try {
      packageJson = JSON.parse(oaklibsContent);
    } catch (error) {
      return res.status(400).json({
        error: 'Invalid oaklibs.json',
        message: 'oaklibs.json contains invalid JSON'
      });
    }

    // Validate package.json structure
    const validation = validatePackageJson(packageJson);
    if (!validation.valid) {
      return res.status(400).json({
        error: 'Invalid package configuration',
        message: validation.error
      });
    }

    // Check if user has permission to publish this package
    const existingPackage = await db('packages').where({ name: packageJson.name }).first();
    if (existingPackage && existingPackage.owner_id !== req.user.id) {
      return res.status(403).json({
        error: 'Permission denied',
        message: 'You do not have permission to publish to this package'
      });
    }

    // Validate version using semver
    if (!semver.valid(packageJson.version)) {
      return res.status(400).json({
        error: 'Invalid version',
        message: 'Version must be a valid semantic version (e.g., 1.0.0)'
      });
    }

    // Check if this version already exists
    if (existingPackage) {
      const existingVersion = await db('package_versions')
        .where({ 
          package_id: existingPackage.id, 
          version: packageJson.version 
        })
        .first();
        
      if (existingVersion) {
        return res.status(409).json({
          error: 'Version already exists',
          message: `Version ${packageJson.version} already exists for package ${packageJson.name}`
        });
      }
    }

    // Calculate file checksum
    const fileBuffer = await fs.readFile(tempFilePath);
    const checksum = crypto.createHash('sha256').update(fileBuffer).digest('hex');
    const fileSize = fileBuffer.length;

    // Move package to final location
    const packagesDir = '/app/packages';
    const packagePath = path.join(packagesDir, packageJson.name);
    const finalFilePath = path.join(packagePath, `${packageJson.version}.tar.gz`);
    
    await fs.mkdir(packagePath, { recursive: true });
    await fs.copyFile(tempFilePath, finalFilePath);

    // Start database transaction
    await db.transaction(async (trx) => {
      let packageId;
      
      if (existingPackage) {
        // Update existing package
        packageId = existingPackage.id;
        await trx('packages')
          .where({ id: packageId })
          .update({
            description: packageJson.description,
            license: packageJson.license,
            homepage: packageJson.homepage,
            repository: packageJson.repository ? JSON.stringify(packageJson.repository) : null,
            keywords: packageJson.keywords ? packageJson.keywords.join(',') : null,
            updated_at: new Date()
          });
      } else {
        // Create new package
        const [newPackage] = await trx('packages')
          .insert({
            name: packageJson.name,
            description: packageJson.description,
            author: packageJson.author,
            license: packageJson.license,
            homepage: packageJson.homepage,
            repository: packageJson.repository ? JSON.stringify(packageJson.repository) : null,
            keywords: packageJson.keywords ? packageJson.keywords.join(',') : null,
            owner_id: req.user.id,
            download_count: 0,
            created_at: new Date(),
            updated_at: new Date()
          })
          .returning('id');
        
        packageId = newPackage.id;
      }

      // Insert new version
      await trx('package_versions').insert({
        package_id: packageId,
        version: packageJson.version,
        dependencies: JSON.stringify(packageJson.dependencies || {}),
        dev_dependencies: JSON.stringify(packageJson.dev_dependencies || {}),
        checksum: `sha256:${checksum}`,
        file_size: fileSize,
        file_path: finalFilePath,
        download_count: 0,
        created_at: new Date()
      });
    });

    logger.info(`Package uploaded: ${packageJson.name}@${packageJson.version} by user ${req.user.username}`);

    res.status(201).json({
      success: true,
      message: `Package ${packageJson.name}@${packageJson.version} published successfully`,
      package: {
        name: packageJson.name,
        version: packageJson.version,
        checksum: `sha256:${checksum}`,
        size: fileSize,
        download_url: `${req.protocol}://${req.get('host')}/packages/${packageJson.name}/${packageJson.version}.tar.gz`
      }
    });

  } catch (error) {
    logger.error('Error uploading package:', error);
    res.status(500).json({
      error: 'Upload failed',
      message: 'An error occurred while processing the package'
    });
  } finally {
    // Clean up temporary files
    try {
      if (tempFilePath) await fs.unlink(tempFilePath);
      if (extractPath) await fs.rmdir(extractPath, { recursive: true });
    } catch (cleanupError) {
      logger.warn('Error cleaning up temporary files:', cleanupError);
    }
  }
});

// Unpublish a package version
router.delete('/:name/:version', authenticateToken, async (req, res) => {
  try {
    const { name, version } = req.params;

    // Check if package exists and user has permission
    const package = await db('packages').where({ name }).first();
    if (!package) {
      return res.status(404).json({
        error: 'Package not found'
      });
    }

    if (package.owner_id !== req.user.id && req.user.role !== 'admin') {
      return res.status(403).json({
        error: 'Permission denied',
        message: 'You do not have permission to unpublish this package'
      });
    }

    // Check if version exists
    const packageVersion = await db('package_versions')
      .where({ package_id: package.id, version })
      .first();

    if (!packageVersion) {
      return res.status(404).json({
        error: 'Version not found'
      });
    }

    // Delete the version
    await db('package_versions')
      .where({ package_id: package.id, version })
      .del();

    // Delete the file
    try {
      await fs.unlink(packageVersion.file_path);
    } catch (error) {
      logger.warn(`Could not delete file ${packageVersion.file_path}:`, error);
    }

    // Check if this was the last version
    const remainingVersions = await db('package_versions')
      .where({ package_id: package.id })
      .count('* as count')
      .first();

    if (remainingVersions.count === 0) {
      // Delete the entire package if no versions remain
      await db('packages').where({ id: package.id }).del();
    }

    logger.info(`Package unpublished: ${name}@${version} by user ${req.user.username}`);

    res.json({
      success: true,
      message: `Package ${name}@${version} has been unpublished`
    });

  } catch (error) {
    logger.error('Error unpublishing package:', error);
    res.status(500).json({
      error: 'Unpublish failed',
      message: 'An error occurred while unpublishing the package'
    });
  }
});

module.exports = router;