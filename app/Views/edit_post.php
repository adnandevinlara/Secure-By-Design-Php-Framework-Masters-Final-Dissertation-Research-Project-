<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-edit me-1"></i> Edit Blog Post</h4>
            <div class="page-title-right">
                <a href="/posts" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Back to Posts</a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="/post/update" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <!-- Hidden field to tell the controller WHICH post to update -->
                    <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id'], ENT_QUOTES) ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Post Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <!-- Category Dropdown -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">Select a Category...</option>
                                <?php foreach ($categories ?? [] as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $post['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Post Status Field -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Status <span class="text-danger">*</span></label>
                            
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_published" value="published" <?= ($post['status'] ?? '') === 'published' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_published">Published</label>
                            </div>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft" <?= ($post['status'] ?? '') === 'draft' || ($post['status'] ?? '') === 'hidden' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="status_draft">Draft</label>
                            </div>
                            
                            <div class="form-text text-muted small mt-1">Drafts are hidden from public.</div>
                        </div>

                        <!-- Post Banner Upload -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Upload Post Image</label>
                            <div class="d-flex align-items-center gap-2">
                                <!-- Changed name from 'image' to 'banner_image' -->
                                <input type="file" name="banner_image" id="postImage" class="form-control" 
                                    accept="image/png, image/jpeg, image/jpg, image/webp" 
                                    onchange="previewPostImage(event)">
                                
                                <!-- Small Inline Preview Tag: Shows existing image if it exists -->
                                <?php if (!empty($post['banner_image'])): ?>
                                    <img id="imagePreview" src="<?= htmlspecialchars($post['banner_image'], ENT_QUOTES, 'UTF-8') ?>" alt="Preview" style="display: block; width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;" />
                                <?php else: ?>
                                    <img id="imagePreview" src="" alt="Preview" style="display: none; width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;" />
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Post Content</label>
                        <textarea name="content" class="form-control note-editor" rows="12" required><?= htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-info btn-lg px-5"><i class="bx bx-save me-1"></i> Update Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Logic to show the small inline image preview
function previewPostImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>