<?php
// index.php
$db = new PDO('sqlite:' . __DIR__ . '/laundry.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// charger services et clients
$services = $db->query('SELECT * FROM services')->fetchAll(PDO::FETCH_ASSOC);
$clients = $db->query('SELECT * FROM clients ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);


// messages flash simples
session_start();
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    

<header><h1>Blanchisserie - Gestion</h1></header>
<main>
<?php if($flash): ?><div class="flash"><?=htmlspecialchars($flash)?></div><?php endif; ?>

<section class="col">
    <h2>Nouveau client</h2>
    <form action="actions.php" method="post">
        <input type="hidden" name="action" value="add_client">
        <label>Nom <input name="nom" required></label>
        <label>Téléphone <input name="telephone"></label>
        <label>Email <input name="email" type="email"></label>
        <label>Adresse <input name="adresse"></label>
        <button>Enregistrer client</button>
    </form>
</section>

<section class="col">
<h2>Nouvelle commande</h2>
<form action="actions.php" method="post">
<input type="hidden" name="action" value="create_commande">
<label>Client
<select name="client_id" required>
<option value="">-- choisir --</option>
<?php foreach($clients as $c): ?>
<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
<?php endforeach; ?>
</select>
</label>


<div class="services">
<?php foreach($services as $s): ?>
<div class="service-row">
<label>
<input type="checkbox" name="service_ids[]" value="<?= $s['id'] ?>"> <?=htmlspecialchars($s['nom'])?> (<?=number_format($s['prix'],2)?>)
</label>
<label>Quantité <input type="number" name="qty_<?= $s['id'] ?>" value="1" min="1"></label>
</div>
<?php endforeach; ?>
</div>


<button>Créer commande</button>
</form>
</section>


<section class="col">
<h2>Commandes récentes</h2>
<table>
<tr><th>ID</th><th>Client</th><th>Total</th><th>Status</th><th></th></tr>
<?php
$cmds = $db->query('SELECT c.id,c.total,c.status,c.created_at, cl.nom as client FROM commandes c LEFT JOIN clients cl ON c.client_id=cl.id ORDER BY c.id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
foreach($cmds as $cm): ?>
<tr>
<td><?= $cm['id'] ?></td>
<td><?= htmlspecialchars($cm['client']) ?></td>
<td><?= number_format($cm['total'],2) ?>€</td>
<td><?= htmlspecialchars($cm['status']) ?></td>
<td>
<a href="invoice.php?id=<?= $cm['id'] ?>" target="_blank">Facture</a> |
<a href="actions.php?action=mark_paid&commande_id=<?= $cm['id'] ?>">Marquer payé</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</section>


</main>
</body>
</html>
