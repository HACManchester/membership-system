/**
 * Calculate time remaining until a future date and format it for display
 */
export function getTimeRemainingText(expiryDate: string | null): string | null {
    if (!expiryDate) {
        return null;
    }

    const now = new Date();
    const expiry = new Date(expiryDate);
    const diffMs = expiry.getTime() - now.getTime();

    // If expired
    if (diffMs <= 0) {
        return 'expired';
    }

    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);

    if (diffDays >= 1) {
        return diffDays === 1 ? '1 day remaining' : `${diffDays} days remaining`;
    }

    if (diffHours >= 1) {
        return diffHours === 1 ? '1 hour remaining' : `${diffHours} hours remaining`;
    }

    const diffMinutes = Math.floor(diffMs / (1000 * 60));
    if (diffMinutes >= 1) {
        return diffMinutes === 1 ? '1 minute remaining' : `${diffMinutes} minutes remaining`;
    }

    return 'less than 1 minute remaining';
}

/**
 * Check if a date has expired
 */
export function isExpired(expiryDate: string | null): boolean {
    if (!expiryDate) {
        return false;
    }

    return new Date(expiryDate).getTime() <= new Date().getTime();
}