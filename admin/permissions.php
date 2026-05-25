<?php
/**
 * Sweets Website
 * =============================================================
 * File: permissions.php
 * Description: Premium Admin Permissions Management UI
 * =============================================================
 */

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once REPOS_PATH . '/PermissionRepository.php';

use App\Repositories\PermissionRepository;

// Ensure only authorized users can access this page
if (!hasPermission('permissions:manage')) {
    header("Location: dashboard.php");
    exit;
}

$repo = new PermissionRepository();
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentRoleSlug = getCurrentAdminRoleSlug();

// Filter visible roles sidebar to only show roles <= current admin's role priority
$allRoles = $repo->getAllRoles();
$roles = array_filter($allRoles, function($r) use ($currentRoleSlug) {
    return \App\Policy\AdminPolicy::getPriority($currentRoleSlug) >= \App\Policy\AdminPolicy::getPriority($r['slug']);
});
$roles = array_values($roles); // re-index

// Filter dropdown list for assigning roles to only show roles < current admin's role priority
$assignableRoles = array_filter($allRoles, function($r) use ($currentRoleSlug) {
    return \App\Policy\AdminPolicy::getPriority($currentRoleSlug) > \App\Policy\AdminPolicy::getPriority($r['slug']);
});
$assignableRoles = array_values($assignableRoles); // re-index

$permissions = $repo->getAllPermissions();
$admins = $repo->getAdminUsersWithRoles($currentUserId, $currentRoleSlug);

// Statistics
$totalRoles = count($roles);
$totalPermissions = count($permissions);
$totalAdmins = count($admins);
$superAdmins = count(array_filter($admins, fn($a) => ($a['role_slug'] ?? '') === 'super_admin'));

?>

<!-- Link Separate CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pages/admin-permissions.css">

<div class="main-content perm-page-body">
    <?php require_once 'includes/topbar.php'; ?>

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-perm">
        <div>
            <h1>Admin Permissions</h1>
            <div class="breadcrumb-custom-perm">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <a href="settings.php">Settings</a>
                <span>/</span>
                <span>Admin Permissions</span>
            </div>
        </div>
        <button class="btn-add-permission" onclick="showToast('✅ Use the form below to manage existing permissions')">
            <i class="fas fa-plus"></i> System Overview
        </button>
    </div>

    <!-- ===== STAT CARDS ===== -->
    <div class="stats-section-perm">
        <a href="#sectionRoles" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-card-perm">
                <div class="stat-icon-perm blue"><i class="fas fa-shield-halved"></i></div>
                <div class="stat-content-perm">
                    <div class="stat-label">Total Roles</div>
                    <div class="stat-value"><?= $totalRoles ?></div>
                    <div class="stat-sub">Defined access levels</div>
                </div>
            </div>
        </a>

        <a href="#sectionPermissions" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-card-perm">
                <div class="stat-icon-perm green"><i class="fas fa-key"></i></div>
                <div class="stat-content-perm">
                    <div class="stat-label">Permissions</div>
                    <div class="stat-value"><?= $totalPermissions ?></div>
                    <div class="stat-sub">Granular system keys</div>
                </div>
            </div>
        </a>

        <a href="#sectionAdmins" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-card-perm">
                <div class="stat-icon-perm orange"><i class="fas fa-user-group"></i></div>
                <div class="stat-content-perm">
                    <div class="stat-label">Assigned Admins</div>
                    <div class="stat-value"><?= $totalAdmins ?></div>
                    <div class="stat-sub">Total staff with access</div>
                </div>
            </div>
        </a>

        <?php if ($currentRoleSlug === 'super_admin'): ?>
        <a href="#sectionAdmins" style="text-decoration: none; color: inherit; display: block;">
            <div class="stat-card-perm">
                <div class="stat-icon-perm purple"><i class="fas fa-user-shield"></i></div>
                <div class="stat-content-perm">
                    <div class="stat-label">Super Admins</div>
                    <div class="stat-value"><?= $superAdmins ?></div>
                    <div class="stat-sub">Full system control</div>
                </div>
            </div>
        </a>
        <?php endif; ?>
    </div>

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="main-layout-perm">
        <!-- ===== ROLES SIDEBAR ===== -->
        <div class="roles-panel" id="sectionRoles">
            <div class="roles-header">
                <h3>Roles</h3>
                <button class="btn-add-role" onclick="showToast('➕ Role creation is handled via migration for safety.')">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="roles-search">
                <input type="text" placeholder="Search roles..." id="roleSearch" oninput="filterRoles()">
            </div>

            <ul class="roles-list" id="rolesList">
                <?php foreach ($roles as $index => $role): ?>
                    <li class="role-item <?= $index === 0 ? 'active' : '' ?>" 
                        data-id="<?= $role['id'] ?>" 
                        data-slug="<?= $role['slug'] ?>"
                        onclick="selectRole('<?= htmlspecialchars($role['name']) ?>', <?= $role['id'] ?>, '<?= $role['slug'] ?>', this)">
                        <div class="role-info">
                            <div class="role-name">
                                <?= htmlspecialchars($role['name']) ?>
                                <?php if ($role['slug'] === 'super_admin'): ?>
                                    <span class="badge-super">Super</span>
                                <?php endif; ?>
                            </div>
                            <div class="role-users"><?= htmlspecialchars($role['description']) ?></div>
                        </div>
                        <i class="fas fa-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- ===== PERMISSIONS PANEL ===== -->
        <div class="permissions-panel" id="sectionPermissions">
            <div class="permissions-header">
                <div>
                    <div class="perm-title">Permissions for: <span id="selectedRoleName"><?= htmlspecialchars($roles[0]['name'] ?? 'None') ?></span></div>
                    <div class="perm-subtitle">Manage granular capabilities for the selected role</div>
                </div>
                <select class="module-filter" id="moduleFilter">
                    <option value="">All Modules</option>
                    <?php 
                    $uniqueModules = [];
                    foreach ($permissions as $p) {
                        $parts = explode(':', $p['key_name']);
                        if (count($parts) === 2) {
                            $uniqueModules[$parts[0]] = true;
                        }
                    }
                    foreach (array_keys($uniqueModules) as $mod): ?>
                        <option value="<?= htmlspecialchars($mod) ?>"><?= ucfirst(htmlspecialchars($mod)) ?></option>
                    <?php endforeach; ?>
                    <option value="System">System</option>
                </select>
            </div>

            <!-- Read-Only Notice -->
            <div id="roleReadOnlyNotice" style="display: none;" class="alert alert-warning border-0 shadow-sm mx-4 my-3 py-3">
                <i class="fas fa-lock me-2"></i> <strong>Read-Only:</strong> This role is at or above your access level and cannot be modified.
            </div>

            <div class="permissions-table-wrapper">
                <table class="permissions-table">
                    <thead>
                        <tr>
                            <th>Module / Feature</th>
                            <th class="text-center">View</th>
                            <th class="text-center">Create</th>
                            <th class="text-center">Edit</th>
                            <th class="text-center">Delete</th>
                            <th class="text-center">Export</th>
                        </tr>
                    </thead>
                    <tbody id="permTableBody">
                        <?php 
                        // Group permissions by prefix (e.g. "products:")
                        $groupedPerms = [];
                        foreach ($permissions as $p) {
                            $parts = explode(':', $p['key_name']);
                            if (count($parts) === 2) {
                                $module = $parts[0];
                                $action = $parts[1];
                                if (!isset($groupedPerms[$module])) {
                                    $groupedPerms[$module] = ['name' => ucfirst($module)];
                                }
                                $groupedPerms[$module][$action] = $p['key_name'];
                            }
                        }

                        foreach ($groupedPerms as $module => $actions): ?>
                            <tr class="perm-row" data-module="<?= htmlspecialchars($module) ?>">
                                <td class="perm-name-cell">
                                    <div class="perm-name"><?= htmlspecialchars($actions['name']) ?> Management</div>
                                    <div class="perm-desc">Manage granular access for <?= htmlspecialchars($module) ?></div>
                                </td>
                                <?php foreach (['view', 'create', 'edit', 'delete', 'export'] as $action): ?>
                                    <td class="text-center">
                                        <?php if (isset($actions[$action])): ?>
                                            <div class="custom-check-perm" 
                                                 data-key="<?= $actions[$action] ?>"
                                                 onclick="toggleCheck(this)"></div>
                                        <?php else: ?>
                                            <span class="text-muted opacity-25">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- System Permissions (Single Row) -->
                        <tr class="perm-row" data-module="System">
                             <td class="perm-name-cell">
                                <div class="perm-name">System & Settings</div>
                                <div class="perm-desc">Critical system-wide configurations</div>
                            </td>
                            <td colspan="5">
                                <div class="d-flex gap-4 ps-3">
                                    <?php 
                                    $systemPerms = array_filter($permissions, fn($p) => !strpos($p['key_name'], ':'));
                                    foreach ($systemPerms as $p): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="custom-check-perm" 
                                                 data-key="<?= $p['key_name'] ?>"
                                                 onclick="toggleCheck(this)"></div>
                                            <span class="small fw-bold"><?= htmlspecialchars($p['name']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Full Access Message (for Super Admin) -->
            <div id="superAdminNotice" style="display: none;" class="p-5 text-center">
                <i class="fas fa-shield-check fa-4x text-maroon mb-4"></i>
                <h2 class="fw-800">Full Access Granted</h2>
                <p class="text-muted">Users assigned to the Super Admin role have unrestricted access to all system modules and settings by default.</p>
            </div>

            <div class="permissions-footer" id="permFooter">
                <div class="d-flex gap-2">
                    <button class="btn-reset-perm" onclick="resetUI()">Reset</button>
                    <button class="btn btn-outline-dark rounded-pill px-3 fw-bold" onclick="selectAllVisible()">Check All</button>
                    <button class="btn btn-outline-secondary rounded-pill px-3" onclick="deselectAllVisible()">Uncheck All</button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light rounded-pill px-4" onclick="location.reload()">Cancel</button>
                    <button class="btn-save-perm" onclick="savePermissions()">Save Access Map</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== USER ASSIGNMENT SECTION ===== -->
    <div class="container-fluid px-4 px-md-5 mb-5" id="sectionAdmins">
        <div class="permissions-panel">
            <div class="permissions-header">
                <div>
                    <div class="perm-title">Admin Staff Assignments</div>
                    <div class="perm-subtitle">Assign roles to individual administrators</div>
                </div>
                <button class="btn text-white rounded-pill px-4 fw-bold" style="background-color: #7a1f1f; border: none; transition: 0.3s;" onmouseover="this.style.backgroundColor='#5a1414'" onmouseout="this.style.backgroundColor='#7a1f1f'" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="fas fa-user-plus me-2"></i> Add Admin
                </button>
            </div>
            <div class="table-responsive">
                <table class="table admin-table m-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-4">Admin Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Reassign Role</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin):
                            $status = strtolower($admin['status'] ?? 'active');
                            $statusBadge = match($status) {
                                'active'         => ['color' => '#198754', 'bg' => '#d1e7dd', 'label' => 'Active'],
                                'suspended'      => ['color' => '#dc3545', 'bg' => '#f8d7da', 'label' => 'Suspended'],
                                'pending_invite' => ['color' => '#fd7e14', 'bg' => '#ffe5d0', 'label' => 'Pending Invite'],
                                'inactive'       => ['color' => '#6c757d', 'bg' => '#e2e3e5', 'label' => 'Inactive'],
                                default          => ['color' => '#6c757d', 'bg' => '#e2e3e5', 'label' => ucfirst($status)]
                            };
                            $isSelf = ($admin['id'] == ($_SESSION['user_id'] ?? 0));
                        ?>
                            <tr id="admin-row-<?= $admin['id'] ?>">
                                <td class="ps-4 fw-bold align-middle">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:36px;height:36px;border-radius:50%;background:#f5e6e6;display:flex;align-items:center;justify-content:center;font-weight:800;color:#7a1f1f;font-size:.85rem;flex-shrink:0;">
                                            <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($admin['full_name']) ?>
                                        <?= $isSelf ? '<span class="badge bg-secondary ms-1" style="font-size:.65rem;">You</span>' : '' ?>
                                    </div>
                                </td>
                                <td class="align-middle text-muted"><?= htmlspecialchars($admin['email']) ?></td>
                                <td class="align-middle">
                                    <span class="badge rounded-pill px-3 py-2" style="background:#f5e6e6;color:#7a1f1f;">
                                        <?= htmlspecialchars($admin['role_name'] ?? 'Not Assigned') ?>
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $statusBadge['bg'] ?>;color:<?= $statusBadge['color'] ?>;">
                                        <?= $statusBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="align-middle text-muted small">
                                    <?= !empty($admin['last_login_at']) ? date('d M Y, h:i A', strtotime($admin['last_login_at'])) : '<em>Never</em>' ?>
                                </td>
                                <td class="align-middle">
                                    <?php if (!$isSelf): ?>
                                    <select class="form-select form-select-sm d-inline-block w-auto"
                                            onchange="updateUserRole(<?= $admin['id'] ?>, this.value)">
                                        <option value="">Choose Role</option>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= $r['id'] ?>" <?= ($admin['role_id'] == $r['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($r['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                </td>
                                <td class="text-end pe-4 align-middle">
                                    <?php if (!$isSelf): ?>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <?php if ($status === 'suspended'): ?>
                                            <button onclick="adminAction(<?= $admin['id'] ?>, 'reactivate')"
                                                class="btn btn-sm btn-success rounded-pill px-3">
                                                <i class="bi bi-check-circle me-1"></i> Reactivate
                                            </button>
                                        <?php elseif ($status === 'pending_invite'): ?>
                                            <button onclick="adminAction(<?= $admin['id'] ?>, 'resend_invite')"
                                                class="btn btn-sm btn-warning rounded-pill px-3">
                                                <i class="bi bi-envelope me-1"></i> Resend Invite
                                            </button>
                                        <?php else: ?>
                                            <button onclick="adminAction(<?= $admin['id'] ?>, 'suspend')"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-pause-circle me-1"></i> Suspend
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="confirmRemoveAccess(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['full_name']) ?>')"
                                            class="btn btn-sm btn-danger rounded-pill px-3">
                                            <i class="bi bi-trash me-1"></i> Remove
                                        </button>
                                    </div>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addAdminForm" onsubmit="submitAddAdmin(event)">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control form-control-lg bg-light" id="adminName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control form-control-lg bg-light" id="adminEmail" required>
                        <div class="form-text">If this email belongs to an existing customer, their account will be upgraded.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="sendInviteEmail" checked>
                            <label class="form-check-label fw-bold" for="sendInviteEmail">Send Invite Email</label>
                        </div>
                        <div class="form-text mt-2">The new admin will receive an email to securely set their password.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Assign Role</label>
                        <select class="form-select form-select-lg bg-light" id="adminRole" required>
                            <option value="">Choose a Role...</option>
                            <?php foreach ($assignableRoles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn text-white w-100 rounded-pill py-3 fw-bold" style="background-color: #7a1f1f; border: none; transition: 0.3s;" onmouseover="this.style.backgroundColor='#5a1414'" onmouseout="this.style.backgroundColor='#7a1f1f'" id="btnAddAdminSubmit">
                        Create Admin Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let currentRoleId = <?= $roles[0]['id'] ?? 0 ?>;
    let currentRoleSlug = '<?= $roles[0]['slug'] ?? '' ?>';
    let currentUserPriority = <?= \App\Policy\AdminPolicy::getPriority($currentRoleSlug) ?>;
    const rolePriorities = {
        'super_admin': 100,
        'manager': 70,
        'editor': 40,
        'staff': 10
    };

    async function selectRole(name, id, slug, element) {
        // UI Updates
        document.querySelectorAll('.role-item').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        
        currentRoleId = id;
        currentRoleSlug = slug;
        document.getElementById('selectedRoleName').textContent = name;

        // Determine if target role is read-only
        let targetPriority = rolePriorities[slug] || 0;
        let isReadOnly = targetPriority >= currentUserPriority;

        const noticeEl = document.getElementById('roleReadOnlyNotice');
        const saveBtn = document.querySelector('.btn-save-perm');
        const resetBtn = document.querySelector('.btn-reset-perm');
        const checkAllBtn = document.querySelector('button[onclick="selectAllVisible()"]');
        const uncheckAllBtn = document.querySelector('button[onclick="deselectAllVisible()"]');

        if (isReadOnly) {
            if (noticeEl) noticeEl.style.display = 'block';
            if (saveBtn) saveBtn.style.display = 'none';
            if (resetBtn) resetBtn.style.display = 'none';
            if (checkAllBtn) checkAllBtn.style.display = 'none';
            if (uncheckAllBtn) uncheckAllBtn.style.display = 'none';
        } else {
            if (noticeEl) noticeEl.style.display = 'none';
            if (saveBtn) saveBtn.style.display = 'inline-block';
            if (resetBtn) resetBtn.style.display = 'inline-block';
            if (checkAllBtn) checkAllBtn.style.display = 'inline-block';
            if (uncheckAllBtn) uncheckAllBtn.style.display = 'inline-block';
        }

        if (slug === 'super_admin') {
            document.querySelector('.permissions-table-wrapper').style.display = 'none';
            document.getElementById('superAdminNotice').style.display = 'block';
            document.getElementById('permFooter').style.display = 'none';
            if (noticeEl) noticeEl.style.display = 'none';
        } else {
            document.querySelector('.permissions-table-wrapper').style.display = 'block';
            document.getElementById('superAdminNotice').style.display = 'none';
            document.getElementById('permFooter').style.display = 'flex';
            await loadRolePermissions(id);
        }
    }

    function filterPermissions() {
        const mod = document.getElementById('moduleFilter').value;
        document.querySelectorAll('.perm-row').forEach(row => {
            if (!mod || row.dataset.module === mod) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Attach listener
    document.getElementById('moduleFilter').addEventListener('change', filterPermissions);

    function toggleCheck(el) {
        if (currentRoleSlug === 'super_admin') return;
        // Check if read-only
        let targetPriority = rolePriorities[currentRoleSlug] || 0;
        if (targetPriority >= currentUserPriority) {
            showToast('⚠️ Cannot modify permissions of roles equal to or higher than your own.');
            return;
        }
        el.classList.toggle('checked');
    }

    async function loadRolePermissions(roleId) {
        // Clear current
        document.querySelectorAll('.custom-check-perm').forEach(c => c.classList.remove('checked'));
        
        try {
            const resp = await fetch(`api/permissions.php?action=get_role_permissions&role_id=${roleId}`);
            const data = await resp.json();
            if (data.status === 'success') {
                const keys = data.permissions;
                document.querySelectorAll('.custom-check-perm').forEach(c => {
                    if (keys.includes(c.dataset.key)) {
                        c.classList.add('checked');
                    }
                });
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function savePermissions() {
        const selectedKeys = Array.from(document.querySelectorAll('.custom-check-perm.checked'))
                                  .map(c => c.dataset.key);
        
        showToast('Saving access map...');
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const resp = await fetch('api/permissions.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    action: 'sync_role_permissions',
                    role_id: currentRoleId,
                    permissions: selectedKeys
                })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                showToast('✅ Permissions synchronized successfully');
            } else {
                showToast('❌ Error: ' + data.message);
            }
        } catch (e) {
            showToast('❌ Network error');
        }
    }

    async function updateUserRole(userId, roleId) {
        if (!roleId) return;
        showToast('Updating user role...');
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const resp = await fetch('api/permissions.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    action: 'assign_role',
                    user_id: userId,
                    role_id: roleId
                })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                showToast('✅ Role assigned successfully');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (e) {
            showToast('❌ Error updating role');
        }
    }

    async function submitAddAdmin(e) {
        e.preventDefault();
        const btn = document.getElementById('btnAddAdminSubmit');
        const ogText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        const name = document.getElementById('adminName').value;
        const email = document.getElementById('adminEmail').value;
        const sendInvite = document.getElementById('sendInviteEmail').checked;
        const roleId = document.getElementById('adminRole').value;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const resp = await fetch('api/permissions.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    action: 'create_admin',
                    name: name,
                    email: email,
                    send_invite: sendInvite,
                    role_id: roleId
                })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                showToast('✅ Admin successfully added/upgraded!');
                const modalEl = document.getElementById('addAdminModal');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('❌ Error: ' + data.message);
                btn.innerHTML = ogText;
                btn.disabled = false;
            }
        } catch (err) {
            showToast('❌ Network error');
            btn.innerHTML = ogText;
            btn.disabled = false;
        }
    }

    function filterRoles() {
        const q = document.getElementById('roleSearch').value.toLowerCase();
        document.querySelectorAll('.role-item').forEach(item => {
            const name = item.querySelector('.role-name').textContent.toLowerCase();
            item.style.display = name.includes(q) ? 'flex' : 'none';
        });
    }

    function selectAllVisible() {
        if (currentRoleSlug === 'super_admin') return;
        let targetPriority = rolePriorities[currentRoleSlug] || 0;
        if (targetPriority >= currentUserPriority) {
            showToast('⚠️ Cannot modify permissions of roles equal to or higher than your own.');
            return;
        }
        document.querySelectorAll('.perm-row').forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelectorAll('.custom-check-perm').forEach(c => c.classList.add('checked'));
            }
        });
    }

    function deselectAllVisible() {
        if (currentRoleSlug === 'super_admin') return;
        let targetPriority = rolePriorities[currentRoleSlug] || 0;
        if (targetPriority >= currentUserPriority) {
            showToast('⚠️ Cannot modify permissions of roles equal to or higher than your own.');
            return;
        }
        document.querySelectorAll('.perm-row').forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelectorAll('.custom-check-perm').forEach(c => c.classList.remove('checked'));
            }
        });
    }

    function resetUI() {
        loadRolePermissions(currentRoleId);
        showToast('Selection reset to current values');
    }

    function showToast(msg) {
        if (window.Toastify) {
            Toastify({ text: msg, duration: 3000, gravity: "bottom", position: "right", style: { background: "#7a1f1f" } }).showToast();
        } else {
            // Basic fallback
            const t = document.createElement('div');
            t.style = "position:fixed;bottom:20px;right:20px;background:#7a1f1f;color:white;padding:12px 24px;border-radius:12px;z-index:9999;box-shadow:0 10px 30px rgba(0,0,0,0.2)";
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
    }

    // Init first load
    document.addEventListener('DOMContentLoaded', () => {
        // Run selectRole on the active element to initialize UI correctly!
        const activeRoleEl = document.querySelector('.role-item.active');
        if (activeRoleEl) {
            activeRoleEl.click();
        } else if (currentRoleSlug !== 'super_admin') {
            loadRolePermissions(currentRoleId);
        } else {
            document.querySelector('.permissions-table-wrapper').style.display = 'none';
            document.getElementById('superAdminNotice').style.display = 'block';
            document.getElementById('permFooter').style.display = 'none';
        }
    });

    async function adminAction(userId, action) {
        const labels = {
            suspend: { msg: 'Suspend this admin? They will be locked out immediately.', confirm: 'Yes, Suspend' },
            reactivate: { msg: 'Reactivate this admin account?', confirm: 'Yes, Reactivate' },
            resend_invite: { msg: 'Resend the invite email to this admin?', confirm: 'Yes, Resend' }
        };
        const lbl = labels[action] || { msg: 'Are you sure?', confirm: 'Yes' };
        if (!confirm(lbl.msg)) return;

        showToast('⏳ Processing...');
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const resp = await fetch('api/permissions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ action: action, user_id: userId })
            });
            const data = await resp.json();
            if (data.status === 'success') {
                showToast('✅ ' + data.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast('❌ ' + data.message);
            }
        } catch (e) {
            showToast('❌ Network error');
        }
    }

    function confirmRemoveAccess(userId, name) {
        if (!confirm(`⚠️ Remove ALL admin access for "${name}"?\n\nThis will revoke their admin role and block their account. This action cannot be undone easily.`)) return;
        adminAction(userId, 'remove_access');
    }
</script>

<?php require_once 'includes/footer.php'; ?>
