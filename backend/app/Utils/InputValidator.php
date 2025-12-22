<?php

namespace App\Utils;

class InputValidator
{
    /**
     * Detect phone numbers in text (Moroccan and international formats)
     */
    public static function containsPhoneNumber($text)
    {
        if (empty($text)) return false;
        
        $phonePatterns = [
            '/\b0[567]\d{8}\b/',                    // 0612345678
            '/\b\+?212\s?[567]\d{8}\b/',            // +212612345678
            '/\b0[567][\s.-]?\d{2}[\s.-]?\d{2}[\s.-]?\d{2}[\s.-]?\d{2}\b/',  // 06 12 34 56 78
            '/\b\d{10}\b/',                         // Any 10-digit number
            '/\b\d{2}[\s.-]\d{2}[\s.-]\d{2}[\s.-]\d{2}[\s.-]\d{2}\b/'  // Generic phone format
        ];
        
        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect email addresses in text
     */
    public static function containsEmail($text)
    {
        if (empty($text)) return false;
        
        return preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text);
    }
    
    /**
     * Detect social media handles or URLs
     */
    public static function containsSocialMedia($text)
    {
        if (empty($text)) return false;
        
        $socialPatterns = [
            '/@[a-zA-Z0-9_]{2,}/',                  // @username
            '/\b(facebook|fb|insta|instagram|whatsapp|telegram|snapchat|tiktok)[\s.:]/i',
            '/\bwa\.me\//i',                        // WhatsApp links
        ];
        
        foreach ($socialPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Main validation function
     * Returns ['valid' => bool, 'reason' => string]
     */
    public static function validateUserInput($text, $fieldName = 'ce champ')
    {
        if (empty($text)) {
            return ['valid' => true];
        }
        
        if (self::containsPhoneNumber($text)) {
            return [
                'valid' => false,
                'reason' => "Numéros de téléphone interdits dans {$fieldName}. Veuillez utiliser notre système de messagerie sécurisé."
            ];
        }
        
        if (self::containsEmail($text)) {
            return [
                'valid' => false,
                'reason' => "Adresses email interdites dans {$fieldName}. Gardez vos échanges sur la plateforme."
            ];
        }
        
        if (self::containsSocialMedia($text)) {
            return [
                'valid' => false,
                'reason' => "Réseaux sociaux interdits dans {$fieldName}. Utilisez notre système de messagerie."
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate all values in a constraints array
     */
    public static function validateConstraints($constraints)
    {
        if (!is_array($constraints)) {
            $constraints = json_decode($constraints, true);
        }
        
        if (!is_array($constraints)) {
            return ['valid' => true];
        }
        
        foreach ($constraints as $key => $value) {
            if (is_string($value) && !empty(trim($value))) {
                $validation = self::validateUserInput($value, 'les détails de la demande');
                if (!$validation['valid']) {
                    return $validation;
                }
            }
        }
        
        return ['valid' => true];
    }
}
