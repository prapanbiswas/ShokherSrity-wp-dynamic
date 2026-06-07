<?php
defined('ABSPATH') || exit;
if (!current_user_can('manage_options')) wp_die('Access denied');

$nonce    = wp_create_nonce('ss_nonce');
$users    = get_users(['orderby' => 'registered', 'order' => 'ASC']);
$roles    = wp_roles()->get_names();
$cur_user = wp_get_current_user();

$msg = '';
if (!empty($_GET['created']))  $msg = 'success:User created successfully.';
if (!empty($_GET['updated']))  $msg = 'success:User updated successfully.';
if (!empty($_GET['deleted']))  $msg = 'success:User deleted.';
if (!empty($_GET['err']))      $msg = 'error:' . sanitize_text_field(urldecode($_GET['err']));
?>
<div class="wrap ss-admin">

    <div class="ss-page-header">
        <div class="ss-page-header-left">
            <div class="ss-page-icon-wrap">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div>
                <h1 class="ss-page-title">User Management</h1>
                <p class="ss-page-subtitle"><?php echo count($users); ?> account<?php echo count($users) !== 1 ? 's' : ''; ?> registered</p>
            </div>
        </div>
        <div class="ss-page-header-right">
            <button class="ss-btn ss-btn-primary" onclick="openModal('create-user-modal')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add User
            </button>
        </div>
    </div>

    <?php if ($msg): [$type, $text] = explode(':', $msg, 2); ?>
    <div class="ss-notice ss-notice-<?php echo $type === 'success' ? 'success' : 'error'; ?>" style="margin-bottom:1rem;">
        <?php echo esc_html($text); ?>
    </div>
    <?php endif; ?>

    <div class="ss-card">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            All Users
        </div>
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th style="width:140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u):
                $user_roles = $u->roles;
                $role_label = !empty($user_roles) ? ($roles[reset($user_roles)] ?? reset($user_roles)) : 'None';
                $is_current = ($u->ID === $cur_user->ID);
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        <div class="ss-avatar" style="background:<?php echo '#' . substr(md5($u->user_login), 0, 6); ?>;">
                            <?php echo strtoupper(substr($u->display_name ?: $u->user_login, 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:.85rem;color:#1a120a;"><?php echo esc_html($u->display_name ?: $u->user_login); ?></div>
                            <?php if ($is_current): ?><div style="font-size:.72rem;color:#D4AF37;">You</div><?php endif; ?>
                        </div>
                    </div>
                </td>
                <td><code style="font-size:.8rem;background:#f4f1eb;padding:.15rem .4rem;border-radius:4px;"><?php echo esc_html($u->user_login); ?></code></td>
                <td style="font-size:.83rem;color:#4a4040;"><?php echo esc_html($u->user_email); ?></td>
                <td>
                    <span class="ss-badge <?php echo in_array('administrator', $user_roles) ? 'ss-badge-dark' : 'ss-badge-gold'; ?>">
                        <?php echo esc_html($role_label); ?>
                    </span>
                </td>
                <td style="font-size:.8rem;color:#9b9490;"><?php echo esc_html(date('M j, Y', strtotime($u->user_registered))); ?></td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <button class="ss-btn ss-btn-sm ss-btn-outline" onclick="editUser(<?php echo $u->ID; ?>, '<?php echo esc_js($u->display_name); ?>', '<?php echo esc_js($u->user_email); ?>', '<?php echo esc_js(reset($u->roles) ?: 'subscriber'); ?>')">Edit</button>
                        <?php if (!$is_current): ?>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Delete user <?php echo esc_js($u->display_name ?: $u->user_login); ?>? This cannot be undone.');" style="display:inline;">
                            <?php wp_nonce_field('ss_delete_user_' . $u->ID, '_del_nonce'); ?>
                            <input type="hidden" name="action" value="ss_delete_user_action">
                            <input type="hidden" name="uid" value="<?php echo $u->ID; ?>">
                            <button type="submit" class="ss-btn ss-btn-sm ss-btn-danger">Delete</button>
                        </form>
                        <?php else: ?>
                        <span class="ss-btn ss-btn-sm ss-btn-outline" style="opacity:.4;cursor:not-allowed;">Delete</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Permissions reference -->
    <div class="ss-card" style="margin-top:1.25rem;">
        <div class="ss-card-header">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Role Permissions Reference
        </div>
        <table class="ss-table" style="font-size:.8rem;">
            <thead><tr><th>Role</th><th>Admin Panel</th><th>Gallery</th><th>Settings</th><th>Users</th></tr></thead>
            <tbody>
            <tr><td><span class="ss-badge ss-badge-dark">Administrator</span></td><td>Full access</td><td>Full access</td><td>Full access</td><td>Full access</td></tr>
            <tr><td><span class="ss-badge ss-badge-gold">Editor</span></td><td>Limited</td><td>View only</td><td>No access</td><td>No access</td></tr>
            <tr><td><span class="ss-badge" style="background:#f4f1eb;color:#6b6460;">Subscriber</span></td><td>No access</td><td>No access</td><td>No access</td><td>No access</td></tr>
            </tbody>
        </table>
        <p class="ss-field-hint" style="margin-top:.75rem;">Only users with the <strong>Administrator</strong> role can access this admin panel.</p>
    </div>

</div>

<!-- Create User Modal -->
<div class="ss-modal-overlay" id="create-user-modal" style="display:none;" onclick="if(event.target===this)closeModal('create-user-modal')">
    <div class="ss-modal">
        <div class="ss-modal-header">
            <h3 class="ss-modal-title">Add New User</h3>
            <button class="ss-modal-close" onclick="closeModal('create-user-modal')">&times;</button>
        </div>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('ss_create_user', '_create_nonce'); ?>
            <input type="hidden" name="action" value="ss_create_user_action">
            <div class="ss-modal-body">
                <div class="ss-form-row">
                    <div class="ss-form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required placeholder="johndoe" autocomplete="off">
                    </div>
                    <div class="ss-form-group">
                        <label>Display Name</label>
                        <input type="text" name="display_name" placeholder="John Doe">
                    </div>
                </div>
                <div class="ss-form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="ss-form-row">
                    <div class="ss-form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required minlength="8" placeholder="Min 8 characters" autocomplete="new-password">
                    </div>
                    <div class="ss-form-group">
                        <label>Role</label>
                        <select name="role">
                            <option value="administrator">Administrator (full access)</option>
                            <option value="editor">Editor</option>
                            <option value="subscriber" selected>Subscriber</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ss-modal-footer">
                <button type="button" class="ss-btn ss-btn-outline" onclick="closeModal('create-user-modal')">Cancel</button>
                <button type="submit" class="ss-btn ss-btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="ss-modal-overlay" id="edit-user-modal" style="display:none;" onclick="if(event.target===this)closeModal('edit-user-modal')">
    <div class="ss-modal">
        <div class="ss-modal-header">
            <h3 class="ss-modal-title">Edit User</h3>
            <button class="ss-modal-close" onclick="closeModal('edit-user-modal')">&times;</button>
        </div>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('ss_edit_user', '_edit_nonce'); ?>
            <input type="hidden" name="action" value="ss_edit_user_action">
            <input type="hidden" name="uid" id="edit-uid">
            <div class="ss-modal-body">
                <div class="ss-form-group">
                    <label>Display Name</label>
                    <input type="text" name="display_name" id="edit-display-name">
                </div>
                <div class="ss-form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email">
                </div>
                <div class="ss-form-group">
                    <label>Role</label>
                    <select name="role" id="edit-role">
                        <option value="administrator">Administrator</option>
                        <option value="editor">Editor</option>
                        <option value="subscriber">Subscriber</option>
                    </select>
                </div>
                <div class="ss-form-group">
                    <label>New Password <span class="ss-field-hint">(leave blank to keep current)</span></label>
                    <input type="password" name="password" minlength="8" placeholder="Leave blank to keep unchanged" autocomplete="new-password">
                </div>
            </div>
            <div class="ss-modal-footer">
                <button type="button" class="ss-btn ss-btn-outline" onclick="closeModal('edit-user-modal')">Cancel</button>
                <button type="submit" class="ss-btn ss-btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
.ss-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;flex-shrink:0;opacity:.85;}
.ss-btn-sm{padding:.35rem .75rem!important;font-size:.78rem!important;}
</style>
<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
function editUser(id, name, email, role) {
    document.getElementById('edit-uid').value = id;
    document.getElementById('edit-display-name').value = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value = role;
    openModal('edit-user-modal');
}
</script>
