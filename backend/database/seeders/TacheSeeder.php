<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TacheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tache')->insert([
            // ============================================
            // JARDINAGE TASKS (service_id = 1)
            // ============================================
            [
                'service_id' => 1,
                'nom_tache' => 'Tonte de pelouse',
                'description' => 'Entretien régulier de votre gazon',
                'image_url' => 'https://images.unsplash.com/photo-1723811898182-aff0c2eca53f?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxsYXduJTIwbW93aW5nJTIwZ2FyZGVufGVufDF8fHx8MTc2NDY2MTk1OXww&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'nom_tache' => 'Taille de haies',
                'description' => 'Façonnage et entretien de vos haies',
                'image_url' => 'https://images.unsplash.com/photo-1558904541-efa843a96f01?w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'nom_tache' => 'Plantation de fleurs',
                'description' => 'Création et aménagement de massifs',
                'image_url' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'nom_tache' => 'Élagage d\'arbres',
                'description' => 'Taille et soin des arbres',
                'image_url' => 'https://images.unsplash.com/photo-1626828476637-5bd713ef9f22?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx0cmVlJTIwcHJ1bmluZ3xlbnwxfHx8fDE3NjQ1ODY0NTJ8MA&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'nom_tache' => 'Désherbage',
                'description' => 'Élimination des mauvaises herbes',
                'image_url' => 'https://images.unsplash.com/photo-1706033844083-91d7a6fdab12?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx3ZWVkaW5nJTIwZ2FyZGVufGVufDF8fHx8MTc2NDY2MTk2MXww&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 1,
                'nom_tache' => 'Entretien de potager',
                'description' => 'Soin et maintenance de votre potager',
                'image_url' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // MÉNAGE TASKS (service_id = 2)
            // ============================================
            [
                'service_id' => 2,
                'nom_tache' => 'Ménage résidentiel & régulier',
                'description' => 'Nettoyage complet, entretien régulier, dépoussiérage, sols, cuisine, salle de bain',
                'image_url' => 'https://images.unsplash.com/photo-1758273238415-01ec03d9ef27?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxyZXNpZGVudGlhbCUyMGhvdXNlJTIwY2xlYW5pbmd8ZW58MXx8fHwxNzY0Njk5ODU4fDA&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'nom_tache' => 'Nettoyage en profondeur (Deep Cleaning)',
                'description' => 'Désinfection totale, nettoyage sous/derrière meubles, détartrage, plinthes, lavage des murs',
                'image_url' => 'https://images.unsplash.com/photo-1737372805905-be0b91ec86fb?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxkZWVwJTIwY2xlYW5pbmclMjBob21lfGVufDF8fHx8MTc2NDY5OTg1OHww&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'nom_tache' => 'Nettoyage spécial : déménagement & post-travaux',
                'description' => 'Avant/après déménagement, après rénovation, élimination poussières fines et résidus',
                'image_url' => 'https://images.unsplash.com/photo-1631304137068-16117132ee8b?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxtb3ZpbmclMjBob3VzZSUyMGNsZWFuaW5nfGVufDF8fHx8MTc2NDYyMzY3M3ww&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'nom_tache' => 'Lavage vitres & surfaces spécialisées',
                'description' => 'Vitres intérieur/extérieur, marbre, bois nobles, soins textiles, moquettes & tapis',
                'image_url' => 'https://images.unsplash.com/photo-1761689502577-0013be84f1bf?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx3aW5kb3clMjBjbGVhbmluZyUyMHByb2Zlc3Npb25hbHxlbnwxfHx8fDE3NjQ2NzUyMDl8MA&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'nom_tache' => 'Nettoyage mobilier & textiles',
                'description' => 'Shampooing canapé, rénovation tissus/cuir, fauteuils, matelas, blanchisserie & repassage',
                'image_url' => 'https://images.unsplash.com/photo-1654511830326-63a577771a7e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxmdXJuaXR1cmUlMjB1cGhvbHN0ZXJ5JTIwY2xlYW5pbmd8ZW58MXx8fHwxNzY0Njk5ODY0fDA&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_id' => 2,
                'nom_tache' => 'Nettoyage professionnel (bureaux & commerces)',
                'description' => 'Entretien quotidien bureaux, désinfection postes de travail, vidage poubelles, salles de réunion',
                'image_url' => 'https://images.unsplash.com/photo-1762235634143-6d350fe349e8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxvZmZpY2UlMjBjb21tZXJjaWFsJTIwY2xlYW5pbmd8ZW58MXx8fHwxNzY0Njk5ODYwfDA&ixlib=rb-4.1.0&q=80&w=1080',
                'status' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}