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
  
  // Convert decimal to years and months
  const years = Math.floor(num);
  const decimalPart = num - years;
  const months = Math.round(decimalPart * 12);
  
  // Case: Only months (e.g. 0.5 -> 6 mois)
  if (years === 0) {
    return `${months} mois`;
  }
  
  // Case: Years and months (e.g. 2.5 -> 2 ans et 6 mois)
  if (months > 0) {
    const yearUnit = years >= 2 ? "ans" : "an";
    return `${years} ${yearUnit} et ${months} mois`;
  }
  
  // Case: Exact years (e.g. 2.0 -> 2 ans)
  const unit = years >= 2 ? "ans" : "an";
  return `${years} ${unit}`;
};
