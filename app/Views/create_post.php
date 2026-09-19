<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-sm-0 font-size-18 text-primary"><i class="bx bx-plus me-1"></i> Create New Post</h4>
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
                <form action="/post/store" method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                    <?= \Core\Security\Csrf::getFormField() ?? '' ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Post Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" required>
                        </div>

                        <!-- Category Dropdown -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select a Category...</option>
                                <?php foreach ($categories ?? [] as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Post Status Field -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">Status <span class="text-danger">*</span></label>
                            
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_published" value="published" checked>
                                <label class="form-check-label" for="status_published">Published</label>
                            </div>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft">
                                <label class="form-check-label" for="status_draft">Draft</label>
                            </div>
                            
                            <div class="form-text text-muted small mt-1">Drafts are hidden from public.</div>
                        </div>

                        <!-- Post Banner Upload -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Upload Post Image</label>
                            <div class="d-flex align-items-center gap-2">
                                <!-- Changed name from 'image' to 'banner_image' so controller finds it -->
                                <input type="file" name="banner_image" id="postImage" class="form-control" 
                                    accept="image/png, image/jpeg, image/jpg, image/webp" 
                                    onchange="previewPostImage(event)" required>
                                
                                <!-- Small Inline Preview -->
                                <img id="imagePreview" src="" alt="Preview" style="display: none; width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Post Content</label>
                        <textarea name="content" class="form-control note-editor" rows="12" required></textarea>
                    </div>

                    <div class="text-end mt-4 d-flex justify-content-end align-items-center">
                        <!-- POINT 44: Red XSS Injection Testing Button -->
                        <button type="button" class="btn btn-danger btn-lg px-4 me-3" onclick="injectMaliciousCode()">
                            <i class="bx bx-bug me-1"></i> Push XSS Payload
                        </button>
                        
                        <button type="submit" class="btn btn-primary btn-lg px-5"><i class="bx bx-save me-1"></i> Create Post</button>
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

// POINT 44: XSS Injection Script
function injectMaliciousCode() {
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.value = 'Malicious Post <script>alert("XSS Attack!")<\/script>';
    }

    const contentArea = document.querySelector('textarea[name="content"]');
    if (contentArea) {
        // Works even if Summernote/WYSIWYG is active by targeting its code view if needed, 
        // but raw textarea injection tests the backend parser.
        contentArea.value = 'Attempting XSS: <img src="x" onerror="alert(\'XSS Execution in Content\')">';
    }
    
    alert("Malicious payloads have been pushed to the Title and Content fields. Click 'Create Post' to test the backend firewall.");
}
</script>

<?php 
$content = ob_get_clean(); 
require 'layouts/main.php'; 
?>