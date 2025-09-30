<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=<?php
$id = intval($_GET['id'] ?? 0);
$db = new PDO('sqlite:' . __DIR__ . '/laundry.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$cmd = $db->prepare('SELECT c.*, cl.nom as client, cl.telephone, cl.email FROM commandes c LEFT JOIN clients cl ON c.client_id=cl.id WHERE c.id=:id');
$cmd->execute(['id'=>$id]);
$c = $cmd->fetch(PDO::FETCH_ASSOC);
$items = $db->prepare('SELECT ci.*, s.nom FROM commande_items ci LEFT JOIN services s ON ci.service_id=s.id WHERE ci.commande_id=:id');
$items->execute(['id'=>$id]);
$items = $items->fetchAll(PDO::FETCH_ASSOC);
$paiements = $db->prepare('SELECT * FROM paiements WHERE commande_id=:id'); $paiements->execute(['id'=>$id]); $paiements = $paiements->fetchAll(PDO::FETCH_ASSOC);


if (!$c) { echo 'Commande introuvable'; exit; }


totalPaiements = 0;
foreach ($paiements as $p) { $totalPaiements += $p['montant']; }


?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Facture #<?= $c['id'] ?></title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="invoice">
<header><h1>Facture #<?= $c['id'] ?></h1></header>
<section>
<strong>Client:</strong> <?= htmlspecialchars($c['client']) ?> <br>, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>