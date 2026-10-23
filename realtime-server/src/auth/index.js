const crypto = require('crypto');
const config = require('../config');

/**
 * Verify token issued by PHP backend.
 * Token format: base64url(payload).hmac_sha256(payload, secret)
 * payload: userId.expiresAt or userId.role.expiresAt
 */
function verifyToken(token) {
  if (!token || typeof token !== 'string') {
    return { valid: false, error: 'Token missing or invalid format' };
  }

  // Remove optional Bearer prefix
  const cleanToken = token.replace(/^Bearer\s+/i, '').trim();
  const parts = cleanToken.split('.');
  if (parts.length !== 2) {
    return { valid: false, error: 'Malformed token structure' };
  }

  const [encodedPayload, signature] = parts;

  // Base64url decode
  let payloadStr;
  try {
    const base64 = encodedPayload.replace(/-/g, '+').replace(/_/g, '/');
    const pad = base64.length % 4 === 0 ? '' : '='.repeat(4 - (base64.length % 4));
    payloadStr = Buffer.from(base64 + pad, 'base64').toString('utf8');
  } catch (err) {
    return { valid: false, error: 'Failed to decode payload' };
  }

  // Verify HMAC SHA256 signature
  const expectedSig = crypto
    .createHmac('sha256', config.API_SECRET)
    .update(payloadStr)
    .digest('hex');

  // Constant-time comparison to prevent timing attacks
  const sigBuf = Buffer.from(signature);
  const expBuf = Buffer.from(expectedSig);
  if (sigBuf.length !== expBuf.length || !crypto.timingSafeEqual(sigBuf, expBuf)) {
    return { valid: false, error: 'Invalid token signature' };
  }

  const bits = payloadStr.split('.');
  let userId = 0;
  let role = 'tenant';
  let expiresAt = 0;

  if (bits.length === 3) {
    userId = parseInt(bits[0], 10);
    role = bits[1];
    expiresAt = parseInt(bits[2], 10);
  } else if (bits.length === 2) {
    userId = parseInt(bits[0], 10);
    expiresAt = parseInt(bits[1], 10);
  } else {
    return { valid: false, error: 'Invalid payload content' };
  }

  if (isNaN(userId) || userId <= 0) {
    return { valid: false, error: 'Invalid user id in token' };
  }

  const now = Math.floor(Date.now() / 1000);
  if (expiresAt < now) {
    return { valid: false, error: 'Token has expired' };
  }

  return {
    valid: true,
    user: {
      id: userId,
      role: role || 'tenant'
    }
  };
}

/**
 * Socket.IO authentication middleware for handshake
 */
function authMiddleware(socket, next) {
  const token =
    socket.handshake.auth?.token ||
    socket.handshake.headers?.authorization ||
    socket.handshake.query?.token;

  if (!token) {
    return next(new Error('Authentication required: Token missing'));
  }

  const result = verifyToken(token);
  if (!result.valid) {
    return next(new Error(`Authentication failed: ${result.error}`));
  }

  socket.user = result.user;
  next();
}

module.exports = {
  verifyToken,
  authMiddleware
};
