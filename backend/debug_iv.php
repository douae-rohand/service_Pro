$tache = App\Models\Tache::has('intervenants')->with(['intervenants.utilisateur'])->first();
if ($tache) {
    echo "Tache ID: " . $tache->id . "\n";
    foreach ($tache->intervenants as $iv) {
        echo "Intervenant ID: " . $iv->id . "\n";
        echo "User Name: " . ($iv->utilisateur ? $iv->utilisateur->nom : 'NULL') . "\n";
        echo "Pivot Price: " . ($iv->pivot ? $iv->pivot->prix_tache : 'NULL') . "\n";
    }
} else {
    echo "No tache with intervenants found.\n";
}
