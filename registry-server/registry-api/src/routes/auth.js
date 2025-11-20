const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const db = require('../database');
const { validateEmail, validateUsername } = require('../utils/validation');
const logger = require('../utils/logger');

// Register new user
router.post('/register', async (req, res) => {
  try {
    const { username, email, password, full_name } = req.body;

    // Validate input
    if (!username || !email || !password) {
      return res.status(400).json({
        error: 'Missing required fields',
        message: 'Username, email, and password are required'
      });
    }

    if (!validateUsername(username)) {
      return res.status(400).json({
        error: 'Invalid username',
        message: 'Username must be 3-20 characters, alphanumeric with hyphens and underscores'
      });
    }

    if (!validateEmail(email)) {
      return res.status(400).json({
        error: 'Invalid email',
        message: 'Please provide a valid email address'
      });
    }

    if (password.length < 8) {
      return res.status(400).json({
        error: 'Invalid password',
        message: 'Password must be at least 8 characters long'
      });
    }

    // Check if user already exists
    const existingUser = await db('users')
      .where({ username })
      .orWhere({ email })
      .first();

    if (existingUser) {
      return res.status(409).json({
        error: 'User already exists',
        message: 'A user with this username or email already exists'
      });
    }

    // Hash password
    const saltRounds = 12;
    const passwordHash = await bcrypt.hash(password, saltRounds);

    // Create user
    const [userId] = await db('users')
      .insert({
        username,
        email,
        password_hash: passwordHash,
        full_name: full_name || null,
        role: 'user',
        email_verified: false,
        created_at: new Date(),
        updated_at: new Date()
      })
      .returning('id');

    logger.info(`New user registered: ${username} (${email})`);

    res.status(201).json({
      success: true,
      message: 'User registered successfully',
      user: {
        id: userId.id,
        username,
        email,
        full_name: full_name || null
      }
    });

  } catch (error) {
    logger.error('Error registering user:', error);
    res.status(500).json({
      error: 'Registration failed',
      message: 'An error occurred during registration'
    });
  }
});

// Login user
router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({
        error: 'Missing credentials',
        message: 'Username and password are required'
      });
    }

    // Find user
    const user = await db('users')
      .where({ username })
      .orWhere({ email: username })
      .first();

    if (!user) {
      return res.status(401).json({
        error: 'Invalid credentials',
        message: 'Username or password is incorrect'
      });
    }

    // Verify password
    const passwordValid = await bcrypt.compare(password, user.password_hash);
    if (!passwordValid) {
      return res.status(401).json({
        error: 'Invalid credentials',
        message: 'Username or password is incorrect'
      });
    }

    // Generate JWT token
    const token = jwt.sign(
      { 
        userId: user.id, 
        username: user.username,
        role: user.role 
      },
      process.env.JWT_SECRET,
      { expiresIn: '7d' }
    );

    // Update last login
    await db('users')
      .where({ id: user.id })
      .update({ last_login: new Date() });

    logger.info(`User logged in: ${username}`);

    res.json({
      success: true,
      message: 'Login successful',
      token,
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        full_name: user.full_name,
        role: user.role
      }
    });

  } catch (error) {
    logger.error('Error logging in user:', error);
    res.status(500).json({
      error: 'Login failed',
      message: 'An error occurred during login'
    });
  }
});

// Get current user profile
router.get('/profile', require('../middleware/auth').authenticateToken, async (req, res) => {
  try {
    const user = await db('users')
      .select('id', 'username', 'email', 'full_name', 'role', 'created_at', 'last_login')
      .where({ id: req.user.id })
      .first();

    if (!user) {
      return res.status(404).json({
        error: 'User not found'
      });
    }

    res.json({
      user
    });

  } catch (error) {
    logger.error('Error fetching user profile:', error);
    res.status(500).json({
      error: 'Failed to fetch profile'
    });
  }
});

// Update user profile
router.put('/profile', require('../middleware/auth').authenticateToken, async (req, res) => {
  try {
    const { full_name, email } = req.body;
    const updates = {};

    if (full_name !== undefined) {
      updates.full_name = full_name;
    }

    if (email !== undefined) {
      if (!validateEmail(email)) {
        return res.status(400).json({
          error: 'Invalid email',
          message: 'Please provide a valid email address'
        });
      }

      // Check if email is already taken by another user
      const existingUser = await db('users')
        .where({ email })
        .whereNot({ id: req.user.id })
        .first();

      if (existingUser) {
        return res.status(409).json({
          error: 'Email already taken',
          message: 'This email is already associated with another account'
        });
      }

      updates.email = email;
    }

    if (Object.keys(updates).length === 0) {
      return res.status(400).json({
        error: 'No updates provided'
      });
    }

    updates.updated_at = new Date();

    await db('users')
      .where({ id: req.user.id })
      .update(updates);

    logger.info(`User profile updated: ${req.user.username}`);

    res.json({
      success: true,
      message: 'Profile updated successfully'
    });

  } catch (error) {
    logger.error('Error updating user profile:', error);
    res.status(500).json({
      error: 'Failed to update profile'
    });
  }
});

// Change password
router.put('/password', require('../middleware/auth').authenticateToken, async (req, res) => {
  try {
    const { current_password, new_password } = req.body;

    if (!current_password || !new_password) {
      return res.status(400).json({
        error: 'Missing required fields',
        message: 'Current password and new password are required'
      });
    }

    if (new_password.length < 8) {
      return res.status(400).json({
        error: 'Invalid password',
        message: 'New password must be at least 8 characters long'
      });
    }

    // Get current user
    const user = await db('users').where({ id: req.user.id }).first();

    // Verify current password
    const currentPasswordValid = await bcrypt.compare(current_password, user.password_hash);
    if (!currentPasswordValid) {
      return res.status(401).json({
        error: 'Invalid current password'
      });
    }

    // Hash new password
    const saltRounds = 12;
    const newPasswordHash = await bcrypt.hash(new_password, saltRounds);

    // Update password
    await db('users')
      .where({ id: req.user.id })
      .update({
        password_hash: newPasswordHash,
        updated_at: new Date()
      });

    logger.info(`Password changed for user: ${req.user.username}`);

    res.json({
      success: true,
      message: 'Password changed successfully'
    });

  } catch (error) {
    logger.error('Error changing password:', error);
    res.status(500).json({
      error: 'Failed to change password'
    });
  }
});

module.exports = router;