<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/news.php
 * Description: Admin management panel for Latest News Updates
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/Database.php';
require_once ROOT_PATH . '/services/NewsService.php';

$newsService = new NewsService();

// Handle Form Submissions (Create, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'publish_date' => $_POST['publish_date'] ?? date('Y-m-d'),
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            $imageFile = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK ? $_FILES['image'] : null;
            $success = $newsService->createNews($data, $imageFile);
            if ($success) {
                $_SESSION['flash_success'] = "News post created successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to create news post.";
            }

        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $data = [
                'title' => $_POST['title'] ?? '',
                'publish_date' => $_POST['publish_date'] ?? date('Y-m-d'),
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];
            $imageFile = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK ? $_FILES['image'] : null;
            $success = $newsService->updateNews($id, $data, $imageFile);
            if ($success) {
                $_SESSION['flash_success'] = "News post updated successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to update news post.";
            }

        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $success = $newsService->deleteNews($id);
            if ($success) {
                $_SESSION['flash_success'] = "News post deleted successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to delete news post.";
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "An error occurred: " . $e->getMessage();
    }
    
    // Redirect to clear POST data
    header("Location: news.php");
    exit;
}

// Fetch all news
$newsList = $newsService->getAllNews();

// Page Assets Layouts
$pageStyles = ['assets/css/admin/products.css'];
// We'll leave out modal scripts to keep this script simple and functional without missing dependencies. 
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success m-4"><?php echo $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger m-4"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4">
            <div>
                <nav aria-label=" breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0 small fw-bold">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: #8c3333;">Dashboard</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Latest News</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0 products-page-title">Manage News Updates</h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-add-btn text-white" 
                    style="background-color: #8c3333; border-color: #8c3333;"
                    type="button" data-bs-toggle="modal" data-bs-target="#createNewsModal">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Add News Post
                </button>
            </div>
        </div>

        <div class="px-2 pb-5">
            <div class="mt-4">
                <div class="table-responsive products-table-wrapper rounded shadow-sm border bg-white">
                    <table class="table align-middle mb-0 products-mobile-card-grid table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4">Image</th>
                                <th class="py-3">Title & Date</th>
                                <th class="py-3 d-none d-lg-table-cell">Description</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 pe-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($newsList)): ?>
                                <?php foreach ($newsList as $news): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <?php $imgSrc = !empty($news['image_path']) ? BASE_URL . $news['image_path'] : BASE_URL . 'assets/images/placeholders/product-placeholder.png'; ?>
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="" class="rounded" style="width: 80px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($news['title']); ?></div>
                                            <div class="text-muted small fw-medium mt-1"><i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($news['publish_date'])); ?></div>
                                        </td>
                                        <td class="py-3 d-none d-lg-table-cell text-muted small" style="max-width: 300px;">
                                            <div class="text-truncate" style="max-height: 40px; overflow: hidden;"><?php echo htmlspecialchars($news['description']); ?></div>
                                        </td>
                                        <td class="py-3 text-center">
                                            <?php if ($news['status'] === 'active'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 text-center pe-4">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary shadow-none" 
                                                    title="Edit"
                                                    onclick='populateEditModal(<?php echo htmlspecialchars(json_encode($news), ENT_QUOTES, 'UTF-8'); ?>)'
                                                    data-bs-toggle="modal" data-bs-target="#editNewsModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" action="news.php" class="d-inline-block m-0 p-0" onsubmit="return confirm('Are you sure you want to delete this news post?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-none" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="fs-1 mb-2"><i class="bi bi-newspaper text-secondary"></i></div>
                                        <h5 class="fw-bold">No News Posts Yet</h5>
                                        <p>Click "Add News Post" to create your first dynamic update.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create News Modal -->
<div class="modal fade" id="createNewsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" action="news.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Publish New Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">News Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="Ex: Honoring Excellence in Traditional Sweets">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Publish Date</label>
                            <input type="date" class="form-control w-50" name="publish_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Short Description</label>
                            <textarea class="form-control" name="description" rows="3" required placeholder="A brief summary of the news update..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm border-start border-4" style="border-left-color: #8c3333 !important;">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Cover Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*" required onchange="previewImage(this, 'create_preview_img')">
                                <div class="form-text mt-1">Recommended size: 800x600 pixels (JPEG/PNG)</div>
                                <div class="mt-2 text-center d-none" id="create_preview_container">
                                    <img id="create_preview_img" src="#" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="form-label fw-bold small text-muted">Visibility Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" selected>Active & Visible</option>
                                    <option value="inactive">Draft / Hidden</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-light border shadow-sm px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn px-4 text-white shadow-sm" style="background-color: #8c3333;">Publish Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit News Modal -->
<div class="modal fade" id="editNewsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" action="news.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_news_id">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Update News Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">News Title</label>
                            <input type="text" class="form-control" name="title" id="edit_news_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Publish Date</label>
                            <input type="date" class="form-control w-50" name="publish_date" id="edit_news_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Short Description</label>
                            <textarea class="form-control" name="description" id="edit_news_desc" rows="3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm border-start border-4" style="border-left-color: #8c3333 !important;">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Update Cover Image (Keep empty to retain current)</label>
                                <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'edit_news_preview')">
                                <div class="mt-3">
                                    <label class="form-label fw-bold small text-muted d-block">Current / New Preview</label>
                                    <img id="edit_news_preview" src="" alt="News Image" class="img-thumbnail" style="max-height: 150px;">
                                </div>
                            </div>
                            <div class="col-md-4 mt-3 mt-md-0">
                                <label class="form-label fw-bold small text-muted">Visibility Status</label>
                                <select name="status" id="edit_news_status" class="form-select">
                                    <option value="active">Active & Visible</option>
                                    <option value="inactive">Draft / Hidden</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light border-top p-3">
                <button type="button" class="btn btn-light border shadow-sm px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn px-4 text-white shadow-sm" style="background-color: #8c3333;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function populateEditModal(news) {
    document.getElementById('edit_news_id').value = news.id;
    document.getElementById('edit_news_title').value = news.title;
    document.getElementById('edit_news_date').value = news.publish_date;
    document.getElementById('edit_news_desc').value = news.description;
    document.getElementById('edit_news_status').value = news.status;
    
    // Set preview image
    const preview = document.getElementById('edit_news_preview');
    const baseUrl = '<?php echo BASE_URL; ?>';
    preview.src = news.image_path ? baseUrl + news.image_path : baseUrl + 'assets/images/placeholders/product-placeholder.png';
}

function previewImage(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    const containerId = previewId.replace('_img', '_container');
    const container = document.getElementById(containerId);

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            if (container) container.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
