<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_student_login();

$pdo = db();

$etudiantId = (int)$_SESSION['etudiant_id'];

$stmt = $pdo->prepare(
    'SELECT e.id_etudiant, e.matricule, e.nom, e.prenom, e.email, f.nom AS faculte_nom,
            c.id_compte, c.solde,
            ca.uid_rfid, ca.statut AS carte_statut
     FROM etudiants e
     JOIN facultes f ON f.id_faculte = e.id_faculte
     JOIN comptes c ON c.id_etudiant = e.id_etudiant
     LEFT JOIN cartes ca ON ca.id_etudiant = e.id_etudiant
     WHERE e.id_etudiant = :id
     LIMIT 1'
);
$stmt->execute([':id' => $etudiantId]);
$info = $stmt->fetch();

$historyStmt = $pdo->prepare(
    'SELECT type, montant, statut, reference, date_trans
     FROM transferts
     WHERE id_compte = :id_compte
     ORDER BY date_trans DESC
     LIMIT 10'
);
$historyStmt->execute([':id_compte' => (int)$info['id_compte']]);
$transferts = $historyStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

// Helper: status badge HTML
function statusBadge(string $statut): string {
    return match (strtoupper($statut)) {
        'REUSSI', 'SUCCES', 'PAYE' => '<span class="badge badge-success">' . htmlspecialchars($statut) . '</span>',
        'ECHEC', 'ANNULE'          => '<span class="badge badge-danger">'  . htmlspecialchars($statut) . '</span>',
        'EN_ATTENTE'               => '<span class="badge badge-warning">' . htmlspecialchars($statut) . '</span>',
        default                    => '<span class="badge badge-muted">'   . htmlspecialchars($statut) . '</span>',
    };
}
?>

<div class="topbar">
  <div class="brand"><i class="fa-solid fa-bolt-lightning"></i><?php echo htmlspecialchars(APP_NAME); ?></div>
  <div class="topbar-right">
    <span class="badge">
      <i class="fa-solid fa-user-graduate"></i>
      <?php echo htmlspecialchars((string)$info['prenom'] . ' ' . (string)$info['nom']); ?>
    </span>
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/logout.php">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion
    </a>
  </div>
</div>

<div class="container">

  <div class="page-header">
    <h1 class="h1">Dashboard</h1>
    <div class="h2" style="margin-bottom:0; text-transform:none; letter-spacing:0; font-size:14px; font-weight:500; color:var(--muted);">
      <?php echo htmlspecialchars((string)$info['faculte_nom']); ?>
      &nbsp;·&nbsp;
      Matricule&nbsp;: <strong style="color:var(--text);"> <?php echo htmlspecialchars((string)$info['matricule']); ?></strong>
    </div>
  </div>

  <div class="grid">

    <!-- SOLDE -->
    <div class="card col-6 card-accent">
      <div class="h2"><i class="fa-solid fa-wallet" style="margin-right:5px;"></i> Solde du compte</div>
      <div class="stat-value">
        <span style="font-size:20px;font-weight:600;color:var(--muted);">TND&nbsp;</span><?php echo number_format((float)$info['solde'], 2, '.', '&thinsp;'); ?>
      </div>
      <div class="stat-label" style="margin-bottom:20px;">Solde disponible</div>
      <div class="btn-group" style="margin-top:0;">
        <a class="btn btn-primary" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/paiement_buvette.php">
          <i class="fa-solid fa-barcode"></i> Payer buvette
        </a>
        <a class="btn btn-success" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/paiement_resto.php">
          <i class="fa-solid fa-utensils"></i> Payer resto
        </a>
      </div>
    </div>

    <!-- CARTE RFID -->
    <div class="card col-6">
      <div class="h2"><i class="fa-solid fa-id-card" style="margin-right:5px;"></i> Carte RFID</div>
      <div class="info-row">
        <span class="key">UID</span>
        <span class="val"><?php echo htmlspecialchars(mask_uid((string)($info['uid_rfid'] ?? ''))); ?></span>
      </div>
      <div class="info-row">
        <span class="key">Statut</span>
        <span class="val"><?php echo statusBadge((string)($info['carte_statut'] ?? 'non attribuée')); ?></span>
      </div>
      <div class="btn-group">
        <a class="btn" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/carte.php">
          <i class="fa-solid fa-id-card"></i> Détails carte
        </a>
        <a class="btn" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/historique.php">
          <i class="fa-solid fa-clock-rotate-left"></i> Historique
        </a>
      </div>
    </div>

    <!-- DERNIÈRES TRANSACTIONS -->
    <div class="card col-12">
      <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Dernières transactions</div>
      <?php if (empty($transferts)): ?>
        <div style="padding:24px 0; text-align:center; color:var(--muted); font-size:13.5px;">
          <i class="fa-solid fa-inbox" style="font-size:24px; display:block; margin-bottom:8px; opacity:0.5;"></i>
          Aucune transaction pour l'instant.
        </div>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Référence</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transferts as $t): ?>
                <tr>
                  <td style="color:var(--muted); white-space:nowrap;"><?php echo htmlspecialchars((string)$t['date_trans']); ?></td>
                  <td>
                    <span style="font-weight:600; color:var(--accent-light); font-size:12px; text-transform:uppercase; letter-spacing:0.04em;">
                      <?php echo htmlspecialchars((string)$t['type']); ?>
                    </span>
                  </td>
                  <td style="font-family:var(--font-head); font-weight:700; white-space:nowrap;">
                    TND <?php echo number_format((float)$t['montant'], 2, '.', '&thinsp;'); ?>
                  </td>
                  <td><?php echo statusBadge((string)$t['statut']); ?></td>
                  <td style="color:var(--muted); font-size:12px; font-family:monospace;">
                    <?php echo htmlspecialchars((string)($t['reference'] ?? '—')); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="margin-top:12px; text-align:right;">
          <a class="btn btn-sm" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/historique.php">
            Voir tout l'historique <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
