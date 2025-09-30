<?php
session_start();
$db = new PDO('sqlite:' . __DIR__ . '/laundry.sqlite');
dbSetAttr:
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$action = $_REQUEST['action'] ?? $_GET['action'] ?? null;
if (!$action) { header('Location: index.php'); exit; }


if ($action === 'add_client') {
$stmt = $db->prepare('INSERT INTO clients(nom,telephone,email,adresse) VALUES(:nom,:telephone,:email,:adresse)');
$stmt->execute([ 'nom'=>$_POST['nom'], 'telephone'=>$_POST['telephone'], 'email'=>$_POST['email'], 'adresse'=>$_POST['adresse'] ]);
$_SESSION['flash'] = 'Client ajouté.';
header('Location: index.php'); exit;
}

if ($action === 'create_commande') {
$client_id = $_POST['client_id'];
$service_ids = $_POST['service_ids'] ?? [];
if (!$client_id || empty($service_ids)) { $_SESSION['flash']='Sélectionnez un client et au moins un service.'; header('Location: index.php'); exit; }


// begin transaction
$db->beginTransaction();
$insertOrder = $db->prepare('INSERT INTO commandes(client_id,total,status) VALUES(:client_id,0,:status)');
$insertOrder->execute(['client_id'=>$client_id,'status'=>'en attente']);
$commande_id = $db->lastInsertId();


$total = 0;
foreach($service_ids as $sid) {
$qty = max(1, (int)($_POST['qty_'.$sid] ?? 1));
$s = $db->prepare('SELECT * FROM services WHERE id=:id'); $s->execute(['id'=>$sid]); $serv = $s->fetch(PDO::FETCH_ASSOC);
if (!$serv) continue;
$lineTotal = $serv['prix'] * $qty;
$ins = $db->prepare('INSERT INTO commande_items(commande_id,service_id,quantite,prix_unitaire,total) VALUES(:cid,:sid,:q,:p,:t)');
$ins->execute(['cid'=>$commande_id,'sid'=>$sid,'q'=>$qty,'p'=>$serv['prix'],'t'=>$lineTotal]);
$total += $lineTotal;
}


$upd = $db->prepare('UPDATE commandes SET total=:total WHERE id=:id');
$upd->execute(['total'=>$total,'id'=>$commande_id]);
$db->commit();


$_SESSION['flash'] = 'Commande créée (ID: '.$commande_id.')';
header('Location: index.php'); exit;
}


if ($action === 'mark_paid') {
$id = $_GET['commande_id'];
$stmt = $db->prepare('UPDATE commandes SET status = "payé" WHERE id=:id');
$stmt->execute(['id'=>$id]);
$_SESSION['flash']='Commande marquée payée.';
header('Location: index.php'); exit;
}


// enregistrer un paiement (ex: depuis facture)
if ($action === 'add_payment') {
$cid = $_POST['commande_id'];
$montant = floatval($_POST['montant']);
$methode = $_POST['methode'] ?? 'espèces';
$ins = $db->prepare('INSERT INTO paiements(commande_id,montant,methode) VALUES(:cid,:m,:met)');
$ins->execute(['cid'=>$cid,'m'=>$montant,'met'=>$methode]);
// éventuellement marquer payé si montant >= total
$tot = $db->query('SELECT total FROM commandes WHERE id='.intval($cid))->fetchColumn();
$somme = $db->query('SELECT IFNULL(SUM(montant),0) FROM paiements WHERE commande_id='.intval($cid))->fetchColumn();
if ($somme >= $tot) {
$db->prepare('UPDATE commandes SET status="payé" WHERE id=:id')->execute(['id'=>$cid]);
}
$_SESSION['flash']='Paiement enregistré.';
header('Location: index.php'); exit;
}


header('Location: index.php');
