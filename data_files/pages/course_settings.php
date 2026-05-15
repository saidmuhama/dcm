<?php
include('../config/db.php');

$course_id = $_GET['course_id'] ?? 0;

$q = mysqli_query($db,"SELECT * FROM tbl_courses WHERE id='$course_id'");
$data = mysqli_fetch_assoc($q);
?>

<div class="card adminuiux-card shadow-sm overflow-hidden z-index-0 mb-4">
    <div class="card-body pb-0">
        <h6 class="mb-3">Course Settings</h6>

        <div class="row mb-2">

            <div class="col-12">
                <div class="form-floating mb-3">
                    <input id="course_name" value="<?php echo $data['title']; ?>" class="form-control" required>
                    <label>Course Name</label>
                </div>
            </div>

            <input type="hidden" id="old_course_name" value="<?php echo $data['title']; ?>">
            <div class="col-12">
                <div class="form-floating mb-3">
                    <input id="course_thumbnail" type="file" class="form-control">
                    <label>Upload Thumbnail</label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-floating mb-3">
                    <input id="course_price" value="<?php echo $data['price']; ?>" class="form-control" required>
                    <label>Course Price</label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-floating mb-3">
                    <input id="course_discount" value="<?php echo $data['discount']; ?>" class="form-control">
                    <label>Course Discount (%)</label>
                </div>
            </div>

        </div>

        <input type="hidden" id="course_id"   value="<?php echo $course_id; ?>">
        <input type="hidden" id="library_id"  value="<?php echo $data['library_id']; ?>">
        <input type="hidden" id="library_key" value="<?php echo $data['library_key']; ?>">

        <h6 class="mb-3">Course Description</h6>
        <div class="mb-4">
            <textarea id="course_description"><?php echo $data['description']; ?></textarea>
        </div>

        <h6 class="mb-3">Course Settings</h6>
        <div class="row">

            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="isCertificateOffered" <?php echo $data['certificate'] == 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label">Certificate Offered</label>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="isQandAEnabled" <?php echo $data['qna'] == 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label">Q&A Enabled</label>
                </div>
            </div>

        </div>

    </div>

    <div class="card-footer">
        <button id="saveCourseSettingsBtn" class="btn btn-theme">
            Update Course Settings
        </button>
        
        <button id="deleteCourseBtn" class="btn btn-danger pull-right">
            Delete Course
        </button>
    </div>
</div>
