<?php require_once '../includes/auth_check.php'; ?>
<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/database.php'; ?>
<?php
$db = Database::getInstance()->getConnection();

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$total = $db->query("SELECT COUNT(*) as c FROM transactions")->fetch()['c'];
$total_pages = max(1, ceil($total / $per_page));

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$where = '';
$params = [];
if ($filter === 'completed') { $where = "WHERE status='completed'"; }
elseif ($filter === 'pending') { $where = "WHERE status='pending'"; }
elseif ($filter === 'failed') { $where = "WHERE status='failed'"; }

if (!empty($search)) {
    $search_cond = "(transaction_id LIKE ? OR payer_name LIKE ? OR payer_email LIKE ?)";
    $where = $where ? "$where AND $search_cond" : "WHERE $search_cond";
    $params = array_fill(0, 3, "%$search%");
}

$stmt = $db->prepare("SELECT * FROM transactions $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$all_params = array_merge($params, [$per_page, $offset]);
$stmt->execute($all_params);
$txns = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-list"></i> All Transactions</h3>
    <a href="/bdpay/admin/" class="btn btn-outline-primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Transaction ID, Name or Email" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Status</label>
                <select name="filter" class="form-select">
                    <option value="">All</option>
                    <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= $filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="?" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-success w-100" onclick="exportCSV()"><i class="bi bi-download"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="txnTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Transaction ID</th>
                        <th>Payer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($txns) > 0): $i = $offset + 1; foreach ($txns as $t): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><code><?= htmlspecialchars($t['transaction_id']) ?></code></td>
                        <td><?= htmlspecialchars($t['payer_name']) ?></td>
                        <td><small><?= htmlspecialchars($t['payer_email']) ?></small></td>
                        <td class="fw-bold">$<?= number_format($t['amount'], 2) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($t['payment_method']) ?></span></td>
                        <td>
                            <?php if ($t['status'] === 'completed'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span>
                            <?php elseif ($t['status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass"></i> Pending</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($t['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($t['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No transactions found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-transparent">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
function exportCSV() {
    let csv = "Transaction ID,Payer,Email,Amount,Method,Status,Date\n";
    document.querySelectorAll('#txnTable tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 7) return;
        const data = [
            cells[1].textContent.trim(),
            cells[2].textContent.trim(),
            cells[3].textContent.trim(),
            cells[4].textContent.trim(),
            cells[5].textContent.trim(),
            cells[6].textContent.trim(),
            cells[7].textContent.trim()
        ];
        csv += data.map(v => `"${v}"`).join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'transactions.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<?php require_once '../includes/footer.php'; ?>
