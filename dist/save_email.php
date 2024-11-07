<?php
// Vérifie si une adresse email a été reçue
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'])) {
    echo json_encode(['message' => 'Aucune adresse email reçue.']);
    exit();
}

$email = $data['email'];

// Valide le format de l'email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['message' => 'Adresse email non valide.']);
    exit();
}

try {
    // Connexion à la base de données SQLite
    $db = new PDO('sqlite:email.sql');

    // Crée la table si elle n'existe pas
    $db->exec("CREATE TABLE IF NOT EXISTS emails (email TEXT UNIQUE)");

    // Vérifie si l'email existe déjà
    $query = $db->prepare("SELECT email FROM emails WHERE email = :email");
    $query->bindParam(':email', $email);
    $query->execute();

    if ($query->fetchColumn()) {
        echo json_encode(['message' => 'L\'adresse email existe déjà dans la base de données.']);
    } else {
        // Ajoute l'email et trie par ordre alphabétique
        $insert = $db->prepare("INSERT INTO emails (email) VALUES (:email)");
        $insert->bindParam(':email', $email);
        $insert->execute();

        // Tri des emails en ordre alphabétique
        $emails = $db->query("SELECT email FROM emails ORDER BY email ASC")->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['message' => 'Adresse email ajoutée avec succès.', 'emails' => $emails]);
    }
} catch (PDOException $e) {
    echo json_encode(['message' => 'Erreur lors de l\'ajout de l\'email : ' . $e->getMessage()]);
}
// Pour exécuter ce code, assure-toi d’avoir un serveur local (comme XAMPP ou WAMP) et d’activer l’extension SQLite.
?>
