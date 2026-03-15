<?php
/**
 * Admin - Manage Signatories
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

requireAdminLogin();

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', __('invalid_request'));
        redirect('signatories.php');
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $position = trim($_POST['position'] ?? '');

            if (empty($name) || empty($position)) {
                setFlash('danger', __('field_required'));
                break;
            }

            if (!isset($_FILES['signature_image']) || $_FILES['signature_image']['error'] === UPLOAD_ERR_NO_FILE) {
                setFlash('danger', __('upload_png_only'));
                break;
            }

            $filename = uploadSignatureImage($_FILES['signature_image']);
            if (!$filename) {
                setFlash('danger', __('upload_error'));
                break;
            }

            $stmt = $db->prepare('INSERT INTO signatories (name, position, signature_image, status) VALUES (:name, :position, :image, :status)');
            $stmt->execute([
                ':name' => $name,
                ':position' => $position,
                ':image' => $filename,
                ':status' => 'active',
            ]);

            setFlash('success', __('signatory_added'));
            break;

        case 'edit':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $position = trim($_POST['position'] ?? '');

            if (empty($name) || empty($position) || $id <= 0) {
                setFlash('danger', __('field_required'));
                break;
            }

            // Check if new image uploaded
            if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] === UPLOAD_ERR_OK) {
                $filename = uploadSignatureImage($_FILES['signature_image']);
                if (!$filename) {
                    setFlash('danger', __('upload_error'));
                    break;
                }

                // Delete old image
                $stmt = $db->prepare('SELECT signature_image FROM signatories WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $old = $stmt->fetchColumn();
                if ($old) {
                    deleteSignatureImage($old);
                }

                $stmt = $db->prepare('UPDATE signatories SET name = :name, position = :position, signature_image = :image WHERE id = :id');
                $stmt->execute([
                    ':name' => $name,
                    ':position' => $position,
                    ':image' => $filename,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $db->prepare('UPDATE signatories SET name = :name, position = :position WHERE id = :id');
                $stmt->execute([
                    ':name' => $name,
                    ':position' => $position,
                    ':id' => $id,
                ]);
            }

            setFlash('success', __('signatory_updated'));
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare('SELECT signature_image FROM signatories WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $image = $stmt->fetchColumn();
                if ($image) {
                    deleteSignatureImage($image);
                }

                $stmt = $db->prepare('DELETE FROM signatories WHERE id = :id');
                $stmt->execute([':id' => $id]);
                setFlash('success', __('signatory_deleted'));
            }
            break;

        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            $newStatus = $_POST['new_status'] === 'active' ? 'active' : 'inactive';
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE signatories SET status = :status WHERE id = :id');
                $stmt->execute([':status' => $newStatus, ':id' => $id]);
                setFlash('success', __('signatory_status_updated'));
            }
            break;
    }

    redirect('signatories.php');
}

// Get all signatories
$signatories = $db->query('SELECT * FROM signatories ORDER BY created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-pen-nib"></i> <?php echo __('signatories'); ?></h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <i class="fas fa-plus"></i> <?php echo __('add_signatory'); ?>
    </button>
</div>

<?php
$flash = getFlash();
if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo sanitize($flash['message']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($signatories)): ?>
            <div class="empty-state">
                <i class="fas fa-pen-nib"></i>
                <h3><?php echo __('no_signatories'); ?></h3>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('signatory_name'); ?></th>
                            <th><?php echo __('signatory_position'); ?></th>
                            <th><?php echo __('signature_image'); ?></th>
                            <th><?php echo __('signatory_status'); ?></th>
                            <th><?php echo __('created_at'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($signatories as $i => $sig): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><strong><?php echo sanitize($sig['name']); ?></strong></td>
                                <td><?php echo sanitize($sig['position']); ?></td>
                                <td>
                                    <img src="../uploads/signatures/<?php echo sanitize($sig['signature_image']); ?>"
                                         alt="<?php echo sanitize($sig['name']); ?>"
                                         class="sig-preview">
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $sig['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                        <?php echo $sig['status'] === 'active' ? __('active') : __('inactive'); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($sig['created_at']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info"
                                                onclick="editSignatory(<?php echo htmlspecialchars(json_encode($sig)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form method="POST" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $sig['id']; ?>">
                                            <input type="hidden" name="new_status"
                                                   value="<?php echo $sig['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                            <button type="submit"
                                                    class="btn btn-sm btn-<?php echo $sig['status'] === 'active' ? 'warning' : 'success'; ?>">
                                                <i class="fas fa-<?php echo $sig['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        </form>

                                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $sig['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Signatory Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h2><?php echo __('add_signatory'); ?></h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="add_name"><?php echo __('signatory_name'); ?></label>
                    <input type="text" id="add_name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="add_position"><?php echo __('signatory_position'); ?></label>
                    <input type="text" id="add_position" name="position" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="add_image"><?php echo __('signature_image'); ?> (PNG)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="add_image" name="signature_image" accept=".png,image/png"
                               data-preview="add_preview" required>
                    </div>
                    <img id="add_preview" src="" alt="" style="display:none; margin-top:10px; max-height:100px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">
                    <?php echo __('cancel'); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo __('save'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Signatory Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2><?php echo __('edit_signatory'); ?></h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label for="edit_name"><?php echo __('signatory_name'); ?></label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_position"><?php echo __('signatory_position'); ?></label>
                    <input type="text" id="edit_position" name="position" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_image"><?php echo __('signature_image'); ?> (PNG) - <?php echo __('signatory_status'); ?>: </label>
                    <img id="edit_current_image" src="" alt="" style="max-height:80px; margin-bottom:10px;">
                    <div class="file-input-wrapper">
                        <input type="file" id="edit_image" name="signature_image" accept=".png,image/png"
                               data-preview="edit_preview">
                    </div>
                    <img id="edit_preview" src="" alt="" style="display:none; margin-top:10px; max-height:100px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">
                    <?php echo __('cancel'); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo __('save'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editSignatory(sig) {
    document.getElementById('edit_id').value = sig.id;
    document.getElementById('edit_name').value = sig.name;
    document.getElementById('edit_position').value = sig.position;
    document.getElementById('edit_current_image').src = '../uploads/signatures/' + sig.signature_image;
    document.getElementById('edit_preview').style.display = 'none';
    openModal('editModal');
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
