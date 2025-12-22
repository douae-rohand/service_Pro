/**
 * Utility functions to detect and block personal information in user input
 */

/**
 * Detect phone numbers in text (Moroccan and international formats)
 * Patterns: 0612345678, +212612345678, 06 12 34 56 78, etc.
 */
export function containsPhoneNumber(text) {
    if (!text) return false;

    const phonePatterns = [
        /\b0[567]\d{8}\b/g,                    // 0612345678
        /\b\+?212\s?[567]\d{8}\b/g,            // +212612345678
        /\b0[567][\s.-]?\d{2}[\s.-]?\d{2}[\s.-]?\d{2}[\s.-]?\d{2}\b/g,  // 06 12 34 56 78
        /\b\d{10}\b/g,                         // Any 10-digit number
        /\b\d{2}[\s.-]\d{2}[\s.-]\d{2}[\s.-]\d{2}[\s.-]\d{2}\b/g  // Generic phone format
    ];

    return phonePatterns.some(pattern => pattern.test(text));
}

/**
 * Detect email addresses in text
 */
export function containsEmail(text) {
    if (!text) return false;

    const emailPattern = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;
    return emailPattern.test(text);
}

/**
 * Detect social media handles or URLs
 */
export function containsSocialMedia(text) {
    if (!text) return false;

    const socialPatterns = [
        /@[a-zA-Z0-9_]{2,}/g,                  // @username
        /\b(facebook|fb|insta|instagram|whatsapp|telegram|snapchat|tiktok)[\s.:]/gi,
        /\bwa\.me\//gi,                         // WhatsApp links
    ];

    return socialPatterns.some(pattern => pattern.test(text));
}

/**
 * Main validation function
 * Returns { valid: boolean, reason: string }
 */
export function validateUserInput(text, fieldName = 'ce champ') {
    if (!text) return { valid: true };

    if (containsPhoneNumber(text)) {
        return {
            valid: false,
            reason: `⚠️ Numéros de téléphone interdits dans ${fieldName}. Veuillez utiliser notre système de messagerie sécurisé.`
        };
    }

    if (containsEmail(text)) {
        return {
            valid: false,
            reason: `⚠️ Adresses email interdites dans ${fieldName}. Gardez vos échanges sur la plateforme.`
        };
    }

    if (containsSocialMedia(text)) {
        return {
            valid: false,
            reason: `⚠️ Réseaux sociaux interdits dans ${fieldName}. Utilisez notre système de messagerie.`
        };
    }

    return { valid: true };
}

/**
 * Clean text by masking detected personal information
 */
export function maskPersonalInfo(text) {
    if (!text) return text;

    let cleaned = text;

    // Mask phone numbers
    cleaned = cleaned.replace(/\b0[567]\d{8}\b/g, '[TÉLÉPHONE MASQUÉ]');
    cleaned = cleaned.replace(/\b\+?212\s?[567]\d{8}\b/g, '[TÉLÉPHONE MASQUÉ]');

    // Mask emails
    cleaned = cleaned.replace(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g, '[EMAIL MASQUÉ]');

    return cleaned;
}
