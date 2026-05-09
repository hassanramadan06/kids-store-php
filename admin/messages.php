<?php
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    if (!empty($_POST['mark_read'])) {
        DB::run('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [(int)$_POST['mark_read']]);
        redirect('admin/messages.php');
    }
    if (!empty($_POST['delete'])) {
        DB::run('DELETE FROM contact_messages WHERE id = ?', [(int)$_POST['delete']]);
        flash_set('success', 'Message deleted.');
        redirect('admin/messages.php');
    }
}

$messages = DB::all('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 200');
$admin_page  = 'messages';
$admin_title = 'Messages';
include __DIR__ . '/includes/header.php';
?>
<section class="panel">
  <div class="panel__body">
    <?php if (empty($messages)): ?>
      <p class="muted">No messages yet.</p>
    <?php else: ?>
    <ul class="message-list">
      <?php foreach ($messages as $m): ?>
        <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
          <div class="message-list__head">
            <strong><?= e($m['subject']) ?></strong>
            <span class="muted"><?= e(date('Y-m-d H:i', strtotime($m['created_at']))) ?></span>
          </div>
          <div class="muted small">
            From <?= e($m['name']) ?> &lt;<?= e($m['email']) ?>&gt; · <?= e($m['phone']) ?: '—' ?>
          </div>
          <p><?= nl2br(e($m['message'])) ?></p>
          <div class="row-actions">
            <?php if (!$m['is_read']): ?>
              <form method="post" class="inline-form">
                <?= csrf_field() ?>
                <button class="btn btn--xs" type="submit" name="mark_read" value="<?= (int)$m['id'] ?>">Mark read</button>
              </form>
            <?php endif; ?>
            <form method="post" class="inline-form" onsubmit="return confirm('Delete message?')">
              <?= csrf_field() ?>
              <button class="btn btn--xs btn--danger" type="submit" name="delete" value="<?= (int)$m['id'] ?>">Delete</button>
            </form>
            <a class="btn btn--xs btn--ghost" href="mailto:<?= e($m['email']) ?>">Reply</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
