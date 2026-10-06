<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/schema-guard.php';

$pageTitle = 'Media Library';
$currentNav = 'media-library';
$breadcrumbs = [
    ['label' => 'Dashboard', 'href' => cms_dashboard_href()],
    ['label' => 'Media Library', 'href' => ''],
];

$selfUrl = 'media-library.php';

/**
 * Auto-migration: idempotent table + column self-heal, safe to run on every load.
 */
$mediaSchemaError = null;
try {
    cms_ensure_table(
        $pdo,
        'media_library',
        '`id` INT AUTO_INCREMENT PRIMARY KEY,
         `file_name` VARCHAR(255) NOT NULL,
         `file_path` VARCHAR(500) NOT NULL,
         `file_type` VARCHAR(20) DEFAULT NULL,
         `mime_type` VARCHAR(100) DEFAULT NULL,
         `file_size_kb` INT UNSIGNED DEFAULT NULL,
         `alt_text` VARCHAR(255) DEFAULT NULL,
         `caption` VARCHAR(500) DEFAULT NULL,
         `is_active` TINYINT(1) NOT NULL DEFAULT 1,
         `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
         `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    );
    cms_ensure_column($pdo, 'media_library', 'file_type', 'VARCHAR(20) DEFAULT NULL AFTER `file_path`');
    cms_ensure_column($pdo, 'media_library', 'mime_type', 'VARCHAR(100) DEFAULT NULL AFTER `file_type`');
    cms_ensure_column($pdo, 'media_library', 'file_size_kb', 'INT(10) UNSIGNED DEFAULT NULL AFTER `mime_type`');
    cms_ensure_column($pdo, 'media_library', 'alt_text', 'VARCHAR(255) DEFAULT NULL AFTER `file_size_kb`');
    cms_ensure_column($pdo, 'media_library', 'caption', 'VARCHAR(500) DEFAULT NULL AFTER `alt_text`');
    cms_ensure_column($pdo, 'media_library', 'is_active', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER `caption`');
    cms_ensure_column($pdo, 'media_library', 'updated_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`');
} catch (Throwable $e) {
    $mediaSchemaError = $e->getMessage();
}

$ml_redirect = static function (string $message, string $type = 'success', ?string $query = null) use ($selfUrl): void {
    $_SESSION['cms_flash'] = ['type' => $type, 'message' => $message];
    header('Location: ' . $selfUrl . ($query ? '?' . $query : ''), true, 302);
    exit;
};

$ml_validate = static function (string $fileName, string $filePath, string $fileType, string $fileSizeRaw): ?string {
    if ($fileName === '') {
        return 'File name is required.';
    }
    if ($filePath === '') {
        return 'File path is required.';
    }
    if (!in_array($fileType, ['image', 'document', 'video', 'other'], true)) {
        return 'File type is required.';
    }
    if ($fileSizeRaw !== '' && (!ctype_digit($fileSizeRaw))) {
        return 'File size must be a whole number.';
    }

    return null;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['id'] ?? 0);
        if ($deleteId <= 0) {
            $ml_redirect('Invalid media file.', 'error');
        }
        $delete = $pdo->prepare('DELETE FROM media_library WHERE id = :id');
        $delete->execute(['id' => $deleteId]);
        if ($delete->rowCount() < 1) {
            $ml_redirect('Media file not found or already deleted.', 'error');
        }
        $ml_redirect('Media file deleted successfully.');
    }

    if (!in_array($action, ['create', 'update'], true)) {
        $ml_redirect('Unknown action.', 'error');
    }

    // Where to send the admin back to when this submit fails.
    $errQuery = ($action === 'update' && (int) ($_POST['id'] ?? 0) > 0)
        ? 'edit=' . (int) $_POST['id'] : 'new=1';
    // Optional file upload — falls back to the manual file_path when omitted.
    $uploadedRelPath  = '';
    $uploadedFileName = '';
    $uploadedMime     = '';
    $uploadedSizeKb   = 0;
    $uploadedFileType = '';

    if (
        isset($_FILES['media_file']) && is_array($_FILES['media_file'])
        && (int) ($_FILES['media_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ) {
        $uploadErr = (int) $_FILES['media_file']['error'];
        if ($uploadErr !== UPLOAD_ERR_OK) {
            $ml_redirect('File upload failed (error code ' . $uploadErr . ').', 'error', $errQuery);
        }
        try {
            $saved = cms_handle_media_upload(
                (string) ($_FILES['media_file']['tmp_name'] ?? ''),
                (string) ($_FILES['media_file']['name'] ?? ''),
                (int) ($_FILES['media_file']['size'] ?? 0)
            );
        } catch (RuntimeException $e) {
            $ml_redirect($e->getMessage(), 'error', $errQuery);
        }
        $uploadedRelPath  = $saved['file_path'];
        $uploadedFileName = $saved['file_name'];
        $uploadedMime     = $saved['mime_type'];
        $uploadedSizeKb   = $saved['file_size_kb'];
        $uploadedFileType = $saved['file_type'];
    }

    $fileName    = trim((string) ($_POST['file_name']    ?? ''));
    $filePath    = trim((string) ($_POST['file_path']    ?? ''));
    $fileType    = trim((string) ($_POST['file_type']    ?? ''));
    $mimeType    = trim((string) ($_POST['mime_type']    ?? ''));
    $fileSizeRaw = trim((string) ($_POST['file_size_kb'] ?? ''));
    $altText     = trim((string) ($_POST['alt_text']     ?? ''));
    $caption     = trim((string) ($_POST['caption']      ?? ''));
    $isActive    = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;

    // If a file was uploaded, its values take precedence over (possibly empty) form fields.
    if ($uploadedRelPath !== '') {
        $filePath    = $uploadedRelPath;
        $mimeType    = $uploadedMime;
        $fileSizeRaw = (string) $uploadedSizeKb;
        $fileType    = $uploadedFileType;
        if ($fileName === '') {
            $fileName = $uploadedFileName;
        }
    }

    // Manual path: either a full https:// URL, or a local path that starts
    // with /uploads/ and has no traversal. Local paths get a leading slash.
    $isHttpsUrl = preg_match('#^https://#i', $filePath) === 1;
    if ($filePath !== '' && !$isHttpsUrl) {
        $filePath = '/' . ltrim($filePath, '/');
        if (!app_is_safe_local_media_path($filePath)) {
            $ml_redirect('Invalid file path. Use https:// URL, or a local path starting with /uploads/ without "..".', 'error', $errQuery);
        }
    }
    if (mb_strlen($filePath, 'UTF-8') > 500 || mb_strlen($fileName, 'UTF-8') > 255) {
        $ml_redirect('File path (max 500) or file name (max 255) is too long.', 'error', $errQuery);
    }

    $validationError = $ml_validate($fileName, $filePath, $fileType, $fileSizeRaw);
    if ($validationError !== null) {
        $ml_redirect($validationError, 'error', $errQuery);
    }

    $payload = [
        'file_name' => $fileName,
        'file_path' => $filePath,
        'file_type' => $fileType,
        'mime_type' => $mimeType !== '' ? mb_substr($mimeType, 0, 100, 'UTF-8') : null,
        'file_size_kb' => $fileSizeRaw === '' ? null : (int) $fileSizeRaw,
        'alt_text' => mb_substr($altText, 0, 255, 'UTF-8'),
        'caption' => mb_substr($caption, 0, 500, 'UTF-8'),
        'is_active' => $isActive,
    ];
    if ($action === 'create') {
        $insert = $pdo->prepare(
            'INSERT INTO media_library (
                file_name, file_path, file_type, mime_type, file_size_kb,
                alt_text, caption, is_active, created_at, updated_at
            ) VALUES (
                :file_name, :file_path, :file_type, :mime_type, :file_size_kb,
                :alt_text, :caption, :is_active, NOW(), NOW()
            )'
        );
        $insert->execute($payload);
        $newId = (int) $pdo->lastInsertId();
        $ml_redirect('Media file created successfully.', 'success', 'edit=' . $newId);
    }

    if ($action === 'update') {
        $updateId = (int) ($_POST['id'] ?? 0);
        if ($updateId <= 0) {
            $ml_redirect('Invalid media file.', 'error');
        }
        $update = $pdo->prepare(
            'UPDATE media_library
             SET file_name = :file_name,
                 file_path = :file_path,
                 file_type = :file_type,
                 mime_type = :mime_type,
                 file_size_kb = :file_size_kb,
                 alt_text = :alt_text,
                 caption = :caption,
                 is_active = :is_active,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute($payload + ['id' => $updateId]);
        $ml_redirect('Media file updated successfully.', 'success', 'edit=' . $updateId);
    }
}

$alerts = [];
if (isset($_SESSION['cms_flash']) && is_array($_SESSION['cms_flash'])) {
    $alerts[] = $_SESSION['cms_flash'];
    unset($_SESSION['cms_flash']);
}

if ($mediaSchemaError !== null) {
    $alerts[] = [
        'type' => 'error',
        'raw' => true,
        'message' => 'Media Library belum bisa dipakai sepenuhnya: skema database belum lengkap dan '
            . 'perbaikan otomatis gagal dijalankan (' . cms_esc($mediaSchemaError) . ').',
    ];
}

// ---- View mode: list (default) vs form (?new=1 / ?edit=ID) ----
$editId  = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
$isNew   = isset($_GET['new']) && $editId <= 0;

if ($editId > 0) {
    try {
        $editStmt = $pdo->prepare(
            'SELECT id, file_name, file_path, file_type, mime_type, file_size_kb, alt_text, caption, is_active
             FROM media_library WHERE id = :id LIMIT 1'
        );
        $editStmt->execute(['id' => $editId]);
        $editRow = $editStmt->fetch() ?: null;
    } catch (PDOException $e) {
        $editRow = null;
    }
    if ($editRow === null) {
        $alerts[] = ['type' => 'error', 'message' => 'Media file not found.'];
        $editId = 0;
    }
}
$formMode = $isNew || $editRow !== null;

// ---- List: server-side search + type/status filter + pagination ----
$mediaFiles = [];
$listTotalRows = 0;
$listTotalPages = 1;
$listPage = 1;
$listSearch = '';
$listType = '';
$listStatus = '';
$listPerPage = 24;

if (!$formMode) {
    $listSearch = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
    if (mb_strlen($listSearch, 'UTF-8') > 100) {
        $listSearch = mb_substr($listSearch, 0, 100, 'UTF-8');
    }
    $listType = strtolower(trim((string) ($_GET['type'] ?? '')));
    if (!in_array($listType, ['image', 'document', 'video', 'other'], true)) {
        $listType = '';
    }
    $listStatus = strtolower(trim((string) ($_GET['status'] ?? '')));
    if (!in_array($listStatus, ['active', 'inactive'], true)) {
        $listStatus = '';
    }
    $listPage = max(1, (int) ($_GET['page'] ?? 1));

    $where = [];
    $params = [];
    if ($listSearch !== '') {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $listSearch);
        // Native prepares: each named placeholder may appear only once.
        $where[] = '(m.file_name LIKE :q1 OR m.file_path LIKE :q2)';
        $params['q1'] = '%' . $escaped . '%';
        $params['q2'] = '%' . $escaped . '%';
    }
    if ($listType === 'other') {
        $where[] = "(m.file_type IS NULL OR m.file_type NOT IN ('image','document','video'))";
    } elseif ($listType !== '') {
        $where[] = 'm.file_type = :ftype';
        $params['ftype'] = $listType;
    }
    if ($listStatus !== '') {
        $where[] = 'm.is_active = :fstatus';
        $params['fstatus'] = $listStatus === 'active' ? 1 : 0;
    }
    $whereSql = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

    try {
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM media_library m' . $whereSql);
        $countStmt->execute($params);
        $listTotalRows = (int) $countStmt->fetchColumn();
        $listTotalPages = max(1, (int) ceil($listTotalRows / $listPerPage));
        if ($listPage > $listTotalPages) {
            $listPage = $listTotalPages;
        }

        $listStmt = $pdo->prepare(
            'SELECT m.id, m.file_name, m.file_path, m.file_type, m.mime_type, m.file_size_kb, m.is_active
             FROM media_library m' . $whereSql . '
             ORDER BY m.id DESC LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $k => $v) {
            $listStmt->bindValue(':' . $k, $v);
        }
        $listStmt->bindValue(':limit', $listPerPage, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', ($listPage - 1) * $listPerPage, PDO::PARAM_INT);
        $listStmt->execute();
        $mediaFiles = $listStmt->fetchAll();
    } catch (PDOException $e) {
        $mediaFiles = [];
        if ($mediaSchemaError === null) {
            $alerts[] = ['type' => 'error', 'message' => 'Gagal memuat daftar media: ' . $e->getMessage()];
        }
    }
}

$hasFilter = $listSearch !== '' || $listType !== '' || $listStatus !== '';
$paginateUrl = static function (int $p) use ($selfUrl, $listSearch, $listType, $listStatus): string {
    $q = array_filter(
        ['search' => $listSearch, 'type' => $listType, 'status' => $listStatus, 'page' => $p > 1 ? (string) $p : ''],
        static fn ($v): bool => $v !== ''
    );
    return $selfUrl . ($q !== [] ? '?' . http_build_query($q) : '');
};

$val = static fn (array $row, string $key): string => (string) ($row[$key] ?? '');

require dirname(__DIR__) . '/includes/header.php';
require dirname(__DIR__) . '/includes/sidebar.php';
require dirname(__DIR__) . '/includes/navbar.php';
require dirname(__DIR__) . '/includes/breadcrumb.php';
require dirname(__DIR__) . '/includes/alerts.php';
?>
<style>
.cms-path-upload__preview{display:block;max-width:100%;max-height:100px;margin:6px 0 0;border-radius:8px;object-fit:contain;border:1px solid var(--line)}
.cms-path-upload__preview[hidden]{display:none!important}
.ml-thumb{flex-shrink:0;width:38px;height:38px;object-fit:cover;border-radius:6px;border:1px solid var(--line)}
.ml-thumb--ph{display:flex;align-items:center;justify-content:center;font-size:16px;background:var(--accent-soft);border:1px solid var(--line-subtle);border-radius:6px;width:38px;height:38px;flex-shrink:0}
.ml-fname{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;max-width:100%}
.ml-controls{display:flex;flex-wrap:wrap;gap:8px;padding:10px 14px;border-bottom:1px solid var(--line-subtle);margin:0}
.ml-ctrl-search{flex:1;min-width:120px;padding:7px 10px;border:1px solid var(--line);border-radius:8px;background:var(--input-bg);color:var(--text);font-size:13px;font-family:inherit}
.ml-ctrl-select{padding:7px 10px;border:1px solid var(--line);border-radius:8px;background:var(--input-bg);color:var(--text);font-size:13px;font-family:inherit}
.ml-table-wrap{overflow-x:auto}
.ml-table{table-layout:fixed;width:100%;min-width:700px}
.ml-col-file{width:34%}
.ml-col-type{width:11%}
.ml-col-size{width:12%}
.ml-col-status{width:11%}
.ml-col-actions{width:212px}
.ml-table td{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ml-actions{display:flex;flex-wrap:nowrap;gap:5px;justify-content:flex-end;align-items:center}
.ml-actions .inline-form{display:inline-flex;margin:0}
.ml-hint{font-size:11px;color:var(--muted);display:block;margin-top:4px;line-height:1.45}
.ml-hint code{background:var(--accent-soft);padding:1px 5px;border-radius:3px;font-size:11px}
.pg-pagination{display:flex;flex-wrap:wrap;align-items:center;gap:4px;padding:12px 14px}
.pg-page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border-radius:8px;border:1px solid var(--line);background:var(--surface-soft);color:var(--text);font-size:13px;font-weight:500;text-decoration:none;font-family:inherit}
.pg-page-btn:hover{background:var(--navlink-hover-bg);border-color:var(--navlink-active-border)}
.pg-page-btn--active{background:var(--accent);border-color:var(--accent);color:var(--accent-text);cursor:default}
.pg-page-btn--disabled{color:var(--muted);border-color:var(--line-subtle);cursor:default}
.pg-page-ellipsis{padding:0 4px;color:var(--muted);font-size:13px}
</style>
<section class="admin-stack">
    <div class="toolbar">
        <div class="toolbar__left">
            <h2 class="section-title">Media library</h2>
            <p class="section-lead">Central file store — upload file atau masukkan path file.</p>
        </div>
        <div class="toolbar__right">
            <?php if ($formMode) : ?>
                <a class="admin-btn admin-btn--secondary" href="<?= cms_esc($selfUrl) ?>">Back to List</a>
            <?php else : ?>
                <a class="admin-btn admin-btn--primary" href="<?= cms_esc($selfUrl) ?>?new=1">Add Media Path</a>
            <?php endif; ?>
        </div>
    </div>

<?php if (!$formMode) : ?>
    <div class="panel">
        <div class="panel__head">
            <h3 class="panel__title">Media files</h3>
            <span class="panel__meta"><?= (int) $listTotalRows ?> file(s)</span>
        </div>

        <form method="get" action="<?= cms_esc($selfUrl) ?>" class="ml-controls">
            <input type="search" name="search" class="ml-ctrl-search"
                   placeholder="Search media…" autocomplete="off"
                   value="<?= cms_esc($listSearch) ?>">
            <select name="type" class="ml-ctrl-select">
                <option value="">All types</option>
                <?php foreach (['image' => 'Image', 'document' => 'Document', 'video' => 'Video', 'other' => 'Other'] as $k => $lbl) : ?>
                    <option value="<?= $k ?>"<?= $listType === $k ? ' selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="ml-ctrl-select">
                <option value="">All statuses</option>
                <option value="active"<?= $listStatus === 'active' ? ' selected' : '' ?>>Active</option>
                <option value="inactive"<?= $listStatus === 'inactive' ? ' selected' : '' ?>>Inactive</option>
            </select>
            <button type="submit" class="admin-btn admin-btn--secondary">Filter</button>
        </form>

        <div class="table-wrap ml-table-wrap">
            <table class="admin-table ml-table">
                <colgroup>
                    <col class="ml-col-file">
                    <col class="ml-col-type">
                    <col class="ml-col-size">
                    <col class="ml-col-status">
                    <col class="ml-col-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($mediaFiles === []) : ?>
                        <tr><td colspan="5" class="muted"><?= $hasFilter ? 'No files match your filters.' : 'No media files yet.' ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($mediaFiles as $row) : ?>
                        <?php
                        $rowId     = (int) $row['id'];
                        $rowType   = strtolower($val($row, 'file_type'));
                        $rowMime   = strtolower($val($row, 'mime_type'));
                        $rowFPath  = $val($row, 'file_path');
                        $isImg     = $rowType === 'image' || str_starts_with($rowMime, 'image/');
                        $thumbSrc  = ($isImg && $rowFPath !== '') ? app_asset_preview_url($rowFPath) : '';
                        $isActiveRow = (int) ($row['is_active'] ?? 0) === 1;
                        $badgeKey  = in_array($rowType, ['image', 'document', 'video'], true) ? $rowType : 'other';
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;min-width:0;overflow:hidden">
                                    <?php if ($thumbSrc !== '') : ?>
                                        <img class="ml-thumb" src="<?= cms_esc($thumbSrc) ?>" alt="" loading="lazy" onerror="this.hidden=true">
                                    <?php else : ?>
                                        <div class="ml-thumb ml-thumb--ph" aria-hidden="true">📄</div>
                                    <?php endif; ?>
                                    <span class="ml-fname" title="<?= cms_esc($val($row, 'file_name')) ?>"><?= cms_esc($val($row, 'file_name')) ?></span>
                                </div>
                            </td>
                            <td><span class="ml-type-badge ml-type-badge--<?= $badgeKey ?>"><?= cms_esc($badgeKey) ?></span></td>
                            <td><?= $row['file_size_kb'] !== null && $row['file_size_kb'] !== '' ? cms_esc((string) $row['file_size_kb']) . ' KB' : '—' ?></td>
                            <td><span class="pill pill--<?= $isActiveRow ? 'ok' : 'muted' ?>"><?= $isActiveRow ? 'Active' : 'Inactive' ?></span></td>
                            <td class="table-actions">
                                <div class="ml-actions">
                                    <button type="button" class="admin-btn admin-btn--sm admin-btn--ghost ml-copy-btn"
                                            data-path="<?= cms_esc($rowFPath) ?>" title="Copy path to clipboard">Copy</button>
                                    <a class="admin-btn admin-btn--sm admin-btn--secondary" href="<?= cms_esc($selfUrl) ?>?edit=<?= $rowId ?>">Edit</a>
                                    <form class="inline-form" method="post" action="<?= cms_esc($selfUrl) ?>"
                                          onsubmit="return confirm('Delete this media file?');">
                                        <?= cms_csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $rowId ?>">
                                        <button type="submit" class="admin-btn admin-btn--sm admin-btn--danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($listTotalPages > 1) : ?>
            <nav class="pg-pagination" aria-label="Media pagination">
                <?php if ($listPage > 1) : ?>
                    <a class="pg-page-btn" href="<?= cms_esc($paginateUrl($listPage - 1)) ?>">« Prev</a>
                <?php else : ?>
                    <span class="pg-page-btn pg-page-btn--disabled">« Prev</span>
                <?php endif; ?>
                <?php
                $shown = [];
                for ($i = 1; $i <= $listTotalPages; $i++) {
                    if ($i === 1 || $i === $listTotalPages || abs($i - $listPage) <= 2) {
                        $shown[] = $i;
                    }
                }
                $prevShown = 0;
                foreach ($shown as $i) :
                    if ($prevShown !== 0 && $i - $prevShown > 1) : ?>
                        <span class="pg-page-ellipsis">…</span>
                    <?php endif;
                    if ($i === $listPage) : ?>
                        <span class="pg-page-btn pg-page-btn--active"><?= $i ?></span>
                    <?php else : ?>
                        <a class="pg-page-btn" href="<?= cms_esc($paginateUrl($i)) ?>"><?= $i ?></a>
                    <?php endif;
                    $prevShown = $i;
                endforeach; ?>
                <?php if ($listPage < $listTotalPages) : ?>
                    <a class="pg-page-btn" href="<?= cms_esc($paginateUrl($listPage + 1)) ?>">Next »</a>
                <?php else : ?>
                    <span class="pg-page-btn pg-page-btn--disabled">Next »</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
<?php else : ?>
    <div class="panel" id="media-form">
        <div class="panel__head">
            <h3 class="panel__title"><?= $editRow ? 'Edit media file' : 'New media file' ?></h3>
        </div>
            <form class="form-stack" method="post" action="<?= cms_esc($selfUrl) ?>"
                  enctype="multipart/form-data">
                <?= cms_csrf_field() ?>
                <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
                <?php if ($editRow) : ?>
                    <input type="hidden" name="id" value="<?= (int) $editRow['id'] ?>">
                <?php endif; ?>
                <?php $editFileType = $editRow ? $val($editRow, 'file_type') : ''; ?>

                <label class="field">Upload file
                    <input type="file"
                           name="media_file"
                           id="ml-upload-file"
                           accept=".jpg,.jpeg,.png,.webp,.gif,.pdf">
                    <small class="ml-hint">
                        Allowed: JPG, PNG, WebP, GIF, PDF · Max 5 MB (images), 10 MB (PDF).
                        Uploading auto-fills the fields below.
                        <?php if ($editRow && $val($editRow, 'file_path') !== '') : ?>
                            Leave empty to keep the current file.
                        <?php endif; ?>
                    </small>
                </label>

                <label class="field">File path
                    <input type="text"
                           name="file_path"
                           id="ml-file-path"
                           class="cms-path-upload__input"
                           value="<?= cms_esc($editRow ? $val($editRow, 'file_path') : '') ?>"
                           required
                           placeholder="/uploads/media/YYYY/MM/file.webp"
                           autocomplete="off">
                    <small class="ml-hint">
                        Path starting with /uploads/, or a full https:// URL.
                        Example: <code>/uploads/media/2026/05/photo.webp</code>
                    </small>
                </label>
                <img class="cms-path-upload__preview"
                     id="ml-path-preview"
                     alt=""
                     hidden>

                <label class="field">File name
                    <input type="text"
                           name="file_name"
                           id="ml-file-name"
                           value="<?= cms_esc($editRow ? $val($editRow, 'file_name') : '') ?>"
                           required
                           placeholder="Auto-filled from path, or enter manually">
                    <small class="ml-hint">Auto-filled from the path above. You can edit it.</small>
                </label>

                <label class="field">File type
                    <select name="file_type" required>
                        <option value="">— Select type —</option>
                        <option value="image"    <?= $editFileType === 'image'    ? 'selected' : '' ?>>image</option>
                        <option value="document" <?= $editFileType === 'document' ? 'selected' : '' ?>>document</option>
                        <option value="video"    <?= $editFileType === 'video'    ? 'selected' : '' ?>>video</option>
                        <option value="other"    <?= ($editFileType !== '' && !in_array($editFileType, ['image', 'document', 'video'], true)) ? 'selected' : ($editFileType === 'other' ? 'selected' : '') ?>>other</option>
                    </select>
                </label>

                <label class="field">MIME type
                    <input type="text"
                           name="mime_type"
                           value="<?= cms_esc($editRow ? $val($editRow, 'mime_type') : '') ?>"
                           placeholder="e.g. image/jpeg">
                    <small class="ml-hint">Optional. Helps the media picker recognise image files. Examples: <code>image/jpeg</code>, <code>image/webp</code>, <code>application/pdf</code></small>
                </label>

                <label class="field">File size (KB)
                    <input type="number"
                           name="file_size_kb"
                           min="0" step="1"
                           value="<?= cms_esc($editRow && $editRow['file_size_kb'] !== null ? (string) $editRow['file_size_kb'] : '') ?>"
                           placeholder="e.g. 245">
                    <small class="ml-hint">Optional. For reference only — does not affect functionality.</small>
                </label>

                <label class="field">Alt text
                    <input type="text" name="alt_text" value="<?= cms_esc($editRow ? $val($editRow, 'alt_text') : '') ?>">
                </label>
                <label class="field">Caption
                    <input type="text" name="caption" value="<?= cms_esc($editRow ? $val($editRow, 'caption') : '') ?>">
                </label>
                <label class="field">Status
                    <select name="is_active" required>
                        <option value="1"<?= !$editRow || (int) ($editRow['is_active'] ?? 0) === 1 ? ' selected' : '' ?>>Active</option>
                        <option value="0"<?= $editRow && (int) ($editRow['is_active'] ?? 0) === 0 ? ' selected' : '' ?>>Inactive</option>
                    </select>
                </label>
                <button type="submit" class="admin-btn admin-btn--primary"><?= $editRow ? 'Save changes' : 'Create media file' ?></button>
            </form>
    </div>
<?php endif; ?>
</section>
<?php if ($formMode) : ?>
<script>
(function () {
    // ---- Resolve a relative path to a browser URL for live preview ----
    // Mirrors app_asset_preview_url(): relative paths are prefixed with BASE_URL.
    function previewUrl(path) {
        path = (path || '').trim();
        if (!path) return '';
        if (/^https?:\/\//i.test(path) || path.charAt(0) === '/') return path;
        return '../../' + path.replace(/^(\.\.\/)+/, '').replace(/^\//, '');
    }

    var pathInput   = document.getElementById('ml-file-path');
    var nameInput   = document.getElementById('ml-file-name');
    var pathPreview = document.getElementById('ml-path-preview');
    var uploadInput = document.getElementById('ml-upload-file');

    // ---- Live image preview from file_path ----
    // Hoisted to outer scope so the upload handler can also trigger it.
    function syncPreview() {
        if (!pathInput || !pathPreview) return;
        var url = previewUrl(pathInput.value);
        if (!url) {
            pathPreview.hidden = true;
            pathPreview.removeAttribute('src');
            return;
        }
        pathPreview.src = url;
        pathPreview.hidden = false;
        pathPreview.onerror = function () { pathPreview.hidden = true; };
    }
    if (pathInput) { pathInput.addEventListener('input', syncPreview); syncPreview(); }

    // ---- Auto-fill file_name from file_path basename (manual path typing) ----
    if (pathInput && nameInput) {
        pathInput.addEventListener('input', function () {
            if (nameInput.value.trim() !== '') return;
            var path = pathInput.value.trim();
            if (!path) return;
            var basename = path.replace(/\\/g, '/').split('/').pop() || '';
            if (basename) { nameInput.value = basename; }
        });
    }

    // ---- Auto-fill all form fields when a file is selected for upload ----
    // The server will generate the final path; this shows a realistic preview.
    if (uploadInput) {
        uploadInput.addEventListener('change', function () {
            var file = uploadInput.files && uploadInput.files[0];
            if (!file) return;

            var origName = file.name;
            var dotPos   = origName.lastIndexOf('.');
            var extFull  = dotPos !== -1 ? origName.slice(dotPos).toLowerCase() : '';
            var basePart = (dotPos !== -1 ? origName.slice(0, dotPos) : origName)
                             .toLowerCase()
                             .replace(/[^a-z0-9_-]+/g, '-')
                             .replace(/^-+|-+$/g, '') || 'upload';

            // Build a preview path matching the server-side date-directory pattern
            var now = new Date();
            var yr  = now.getFullYear();
            var mo  = String(now.getMonth() + 1).padStart(2, '0');
            var previewPath = 'uploads/media/' + yr + '/' + mo + '/' + basePart + '-xxxxxxxx' + extFull;

            // Fill file path (placeholder — server sets the real unique name)
            if (pathInput) { pathInput.value = previewPath; syncPreview(); }

            // Fill file name if still empty
            if (nameInput && nameInput.value.trim() === '') {
                nameInput.value = basePart + extFull;
            }

            // Fill MIME type
            var mime   = file.type || '';
            var mimeEl = document.querySelector('[name="mime_type"]');
            if (mimeEl) { mimeEl.value = mime; }

            // Derive and fill file_type
            var typeEl = document.querySelector('[name="file_type"]');
            if (typeEl) {
                var derived = mime.startsWith('image/')       ? 'image'
                            : mime === 'application/pdf'      ? 'document'
                            : '';
                typeEl.value = derived;
            }

            // Fill file size in KB
            var sizeEl = document.querySelector('[name="file_size_kb"]');
            if (sizeEl && file.size) { sizeEl.value = Math.ceil(file.size / 1024); }
        });
    }
})();
</script>
<?php endif; ?>
<?php if (!$formMode) : ?>
<script>
// ---- Copy Path button ----
(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.ml-copy-btn');
        if (!btn) return;
        var path = btn.getAttribute('data-path') || '';
        if (!path) return;
        var orig = btn.textContent;
        function flash() {
            btn.textContent = 'Copied!';
            setTimeout(function () { btn.textContent = orig; }, 1800);
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(path).then(flash);
        } else {
            // Fallback for older/non-secure contexts
            var ta = document.createElement('textarea');
            ta.value = path;
            ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); flash(); } catch (_) {}
            document.body.removeChild(ta);
        }
    });
})();
</script>
<?php endif; ?>
<?php
require dirname(__DIR__) . '/includes/footer.php';
