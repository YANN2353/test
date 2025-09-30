<?php
// db_init.php -> exécuter une fois pour créer la base
$dbFile = __DIR__ . '/laundry.sqlite';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// clients
$db->exec("CREATE TABLE IF NOT EXISTS clients (
id INTEGER PRIMARY KEY AUTOINCREMENT,
nom TEXT NOT NULL,
telephone TEXT,
email TEXT,
adresse TEXT,
created_at TEXT DEFAULT (datetime('now'))
);");

// services
$db->exec("CREATE TABLE IF NOT EXISTS services (
id INTEGER PRIMARY KEY AUTOINCREMENT,
code TEXT UNIQUE,
nom TEXT,
prix REAL
);");


// commandes (orders)
$db->exec("CREATE TABLE IF NOT EXISTS commandes (
id INTEGER PRIMARY KEY AUTOINCREMENT,
client_id INTEGER,
total REAL,
status TEXT DEFAULT 'en attente',
created_at TEXT DEFAULT (datetime('now')),
FOREIGN KEY(client_id) REFERENCES clients(id)
);");


// ligne de commande (items)
$db->exec("CREATE TABLE IF NOT EXISTS commande_items (
id INTEGER PRIMARY KEY AUTOINCREMENT,
commande_id INTEGER,
service_id INTEGER,
quantite INTEGER,
prix_unitaire REAL,
total REAL,
FOREIGN KEY(commande_id) REFERENCES commandes(id),
FOREIGN KEY(service_id) REFERENCES services(id)
);");


// paiements
$db->exec("CREATE TABLE IF NOT EXISTS paiements (
id INTEGER PRIMARY KEY AUTOINCREMENT,
commande_id INTEGER,
montant REAL,
methode TEXT,
created_at TEXT DEFAULT (datetime('now')),
FOREIGN KEY(commande_id) REFERENCES commandes(id)
);");


// Insert default services
$stmt = $db->prepare('SELECT COUNT(*) FROM services');
$count = $stmt->execute() ? $stmt->fetchColumn() : 0;


if ($count == 0) {
$services = [
['code'=>'S-CB','nom'=>'Lavage chaussures','prix'=>8.00],
['code'=>'S-VT','nom'=>'Lavage vêtements (par kg)','prix'=>5.50],
['code'=>'S-RP','nom'=>'Repassage (par pièce)','prix'=>2.50]
];
$ins = $db->prepare('INSERT INTO services(code, nom, prix) VALUES(:code,:nom,:prix)');
foreach ($services as $s) $ins->execute($s);
}


echo "Base et tables initialisées dans: $dbFile\n";

?>