<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_student_login();

$pdo = db();
$etudiantId = (int)$_SESSION['etudiant_id'];

$stmt = $pdo->prepare('SELECT c.id_compte FROM comptes c WHERE c.id_etudiant = :id LIMIT 1');
$stmt->execute([':id' => $etudiantId]);
$row = $stmt->fetch();
$idCompte = (int)($row['id_compte'] ?? 0);

$historyStmt = $pdo->prepare(
    'SELECT type, montant, statut, reference, solde_avant, solde_apres, date_trans, description
     FROM transferts
     WHERE id_compte = :id_compte
     ORDER BY date_trans DESC
     LIMIT 100'
);
$historyStmt->execute([':id_compte' => $idCompte]);
$transferts = $historyStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

function statusBadgeClass(string $statut): string {
    $s = strtoupper(trim($statut));
    return match ($s) {
        'SUCCES', 'SUCCESS', 'OK', 'VALIDE', 'VALIDEE', 'VALIDÉ', 'VALIDÉE' => 'badge-success',
        'ECHEC', 'ECHEC ', 'FAILED', 'REFUSE', 'REFUSEE', 'ANNULÉ', 'ANNULEE' => 'badge-danger',
        'EN_ATTENTE', 'PENDING', 'EN COURS' => 'badge-blue',
        default => 'badge-muted',
    };
}

function typeBadgeClass(string $type): string {
    $t = strtoupper(trim($type));
    return match ($t) {
        'DEBIT', 'RETRAIT', 'ACHAT' => 'badge-danger',
        'CREDIT', 'DEPOT', 'RECHARGE', 'RECHARGEMENT' => 'badge-success',
        default => 'badge-muted',
    };
}
?>

<div class="topbar">
  <div class="brand"><i class="fa-solid fa-bolt-lightning"></i><?php echo htmlspecialchars(APP_NAME); ?></div>
  <div class="topbar-right">
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/dashboard.php">
      <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(BASE_URL); ?>/etudiant/logout.php">
      <i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion
    </a>
  </div>
</div>

<div class="container">

  <div class="page-header">
    <h1 class="h1">Historique des transactions</h1>
    <div class="text-muted" style="margin-top:6px;">Dernières 100 opérations</div>
  </div>

  <?php if ($idCompte <= 0): ?>
    <div class="alert alert-warning">
      <i class="fa-solid fa-circle-exclamation"></i>
      Compte introuvable pour cet étudiant.
    </div>
  <?php elseif (empty($transferts)): ?>
    <div class="card" style="color:var(--muted);">Aucune transaction trouvée.</div>
  <?php else: ?>
    <div class="card">
      <div class="card-title"><i class="fa-solid fa-receipt"></i> Transactions</div>
      <table class="table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Solde</th>
            <th>Référence</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transferts as $t): ?>
            <?php
            $type = (string)($t['type'] ?? '');
            $statut = (string)($t['statut'] ?? '');
            $montant = (float)($t['montant'] ?? 0);
            $isDebit = in_array(strtoupper(trim($type)), ['DEBIT', 'RETRAIT', 'ACHAT'], true);
            ?>
            <tr>
              <td><?php echo htmlspecialchars((string)$t['date_trans']); ?></td>
              <td>
                <span class="badge <?php echo htmlspecialchars(typeBadgeClass($type)); ?>">
                  <?php echo htmlspecialchars($type ?: '—'); ?>
                </span>
              </td>
              <td style="font-family:monospace;<?php echo $isDebit ? 'color:var(--danger);' : 'color:var(--success);'; ?>">
                <?php echo ($isDebit ? '-' : '+') . ' ' . number_format($montant, 2, '.', ' '); ?>
              </td>
              <td>
                <span class="badge <?php echo htmlspecialchars(statusBadgeClass($statut)); ?>">
                  <?php echo htmlspecialchars($statut ?: '—'); ?>
                </span>
              </td>
              <td style="font-family:monospace;">
                <?php if ($t['solde_avant'] !== null && $t['solde_apres'] !== null): ?>
                  <?php echo number_format((float)$t['solde_avant'], 2, '.', ' '); ?> → <?php echo number_format((float)$t['solde_apres'], 2, '.', ' '); ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td style="font-family:monospace;">
                <?php echo htmlspecialchars((string)($t['reference'] ?? '')); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
