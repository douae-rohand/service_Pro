/**
 * Experience Formatter Utilities
 * Handles conversion between Years/Months and Decimal Years, and formatting for display.
 */

// Convert Years and Months to Decimal Years
// Example: 2 years, 6 months -> 2.5
export const calculateDecimalExperience = (years, months) => {
  const y = parseInt(years || 0);
  const m = parseInt(months || 0);
  // (Years * 12 + Months) / 12
  const totalMonths = (y * 12) + m;
  const decimalYears = totalMonths / 12;
  // Return with 2 decimal places fixed if needed, but keeping as number is better
  return parseFloat(decimalYears.toFixed(2));
};

// Format decimal experience for display
// Return "X ans" or "X mois"
export const formatExperience = (value) => {
  if (value === undefined || value === null || value === 'Pas' || value === '') {
    return "Pas";
  }
  
  // If value is already a string containing "ans" or "mois", just return it cleaned (hack for legacy data)
  if (typeof value === 'string' && (value.includes('ans') || value.includes('mois'))) {
    return value.replace(" d'expérience", "").replace(" d'éxperience", "");
  }

  const num = parseFloat(value);
  
  if (isNaN(num)) return value; // Return original if not a number
  
  // If less than 1 year, display in months
  if (num < 1 && num > 0) {
    const months = Math.round(num * 12);
    return `${months} mois`;
  }
  
  // If >= 1 year
  // Number() handles removing trailing zeros automatically e.g. "2.5", "2"
  // French grammar: >= 2 is plural, < 2 is singular? 
  // Usage varies for 1.5, but "1.5 ans" is clearer. "1 an" for exactly 1.
  const unit = (num >= 2 || (num > 1 && num % 1 !== 0)) ? "ans" : "an";
  
  return `${Number(num)} ${unit}`;
};
