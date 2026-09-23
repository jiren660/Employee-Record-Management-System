/**
 * Reusable JavaScript Functions for UI & Input Handling
 * Western Mindanao State University - College of Computing Studies
 * Reference: 03-Simple-Web-Application-Development-with-API-implementation.pdf (Page 2)
 */

/**
 * Validate standard email format
 */
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(String(email).toLowerCase());
}

/**
 * Validate Philippine mobile number format (e.g., 09XXXXXXXXX or +639XXXXXXXXX)
 */
function isValidMobile(mobile) {
  const clean = mobile.replace(/[^0-9+]/g, '');
  return clean.length >= 10 && clean.length <= 15;
}

/**
 * Format string to Title Case
 */
function toTitleCase(str) {
  if (!str) return '';
  return str.replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
}

/**
 * Format ISO date string to human-readable format
 */
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const d = new Date(dateString);
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}
