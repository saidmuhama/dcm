<!-- Create New Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1"
    aria-labelledby="lgmodalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-modal">

            <!-- HEADER -->
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="lgmodalLabel">Choose a product</h5>
                    <small class="text-muted">Select a product to add contents</small>
                </div>
                <button type="button" class="btn-close custom-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- BODY -->
            <div class="modal-body pt-2">
                <div class="p-2">
                    <?php include('pages/modal_lunch_content.php'); ?>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4"
                    data-bs-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-theme px-4 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Create Course Name Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1" aria-labelledby="standardmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <p class="modal-title h5" id="standardmodalLabel">Course Name</p><button type="button"
                    class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="row g-3 needs-validation was-validated" novalidate>
            <div class="modal-body">
                <div class="col-md-12 position-relative">
                    <input type="text" class="form-control" id="validationTooltip01" placeholder="Modern Agriculture" required>
                    <div class="valid-tooltip">
                        Looks good!
                    </div>
                    <div class="invalid-tooltip">
                        Please provide a valid Course name.
                    </div>
                </div>
           

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> 
                <button type="submit" id="saveCourseBtn" class="btn btn-theme">Save & Proceed</button>
            </div>
             </form>
        </div>
    </div>
</div>


<div class="modal fade" id="createChapterModal" tabindex="-1" aria-labelledby="standardmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <p class="modal-title h5" id="standardmodalLabel">Add Chapter</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
           <form id="addChapterForm" class="row g-3 needs-validation" novalidate>
            <div class="modal-body">
                <div class="col-md-12 position-relative">
                    <input type="text" class="form-control" id="validationTooltip02" placeholder="Introduction to islamic Banking" required>
                    <div class="valid-tooltip">
                        Looks good!
                    </div>
                    <div class="invalid-tooltip">
                        Please provide a valid Chapter name.
                    </div>
                </div>
           

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button> 
                <button type="submit" id="saveChapterBtn" class="btn btn-sm btn-theme">
                    Save & Proceed
                </button>
            </div>
             </form>
        </div>
    </div>
</div>