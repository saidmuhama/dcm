 <div class="card adminuiux-card shadow-sm overflow-hidden z-index-0 mb-4">
    <div class="card-body pb-0">
        <h6 class="mb-3">Lesson Basic Details</h6>
        <div class="row mb-2">
            <div class="col-12 col-md-12 col-lg-12 col-xl-12">
                <div class="form-floating mb-3">
                    <input id="lesson_title" placeholder="Lesson Title" value="What is Agriculture?" required="" class="form-control is-valid"> 
                    <label>Lesson Title</label>
                </div>
                <div class="invalid-feedback">Please enter valid input</div>
            </div>
            
            <div class="col-12 col-sm-12 col-lg-12 col-xl-12 mb-3">
                <div class="form-floating">
                    <select class="form-select" id="content_type">
                        <option>Video</option>
                        <option>PDF</option>
                        <option>Presentation</option>
                        <option value="audio">Audio (MP3 / MP4)</option>
                    </select>
                    <label for="content_type">Content-Type</label>
                </div>
            </div>

            <div class="col-12 col-md-12 col-lg-12 col-xl-12">
                <div class="form-floating mb-3">
                    <input id="video_url" placeholder="https://www.youtube.com/embed/kcckpWgkhP0?si=a1tFwygETeVBZGen"  required="" class="form-control"> 
                    <label>Link Video URL</label>
                </div>
                <div class="invalid-feedback">Please enter valid input</div>
            </div>

            <div class="col-12 col-md-12 col-lg-12 col-xl-12">
                <div class="form-floating mb-3">
                    <input id="upload_file" type="file" required="" class="form-control"> 
                    <label>Upload File</label>
                </div>
                <div class="invalid-feedback">Please Attach File</div>
            </div>

        </div>
        
        <input type="hidden" id="chapter_id" value="<?php echo $_GET['chapter_id']; ?>">
        <h6 class="mb-3">Lesson Description (Optional)</h6>
        <div class="mb-4">
            <textarea id="lesson_description" class="FroalaEditor"></textarea>
        </div>
        

        <h6 class="mb-3">Lesson settings</h6>
        <div class="row">
            <div class="col-12 col-md-6 col-lg-4 col-xl-4 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="isFreePreviewLesson" checked=""> 
                    <label class="form-check-label" for="isFreePreviewLesson">Is free preview lesson?</label>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xl-4 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="enableDiscussions" checked=""> 
                    <label class="form-check-label" for="enableDiscussions">Enable discussions</label>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xl-4 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="isDownloadable"> 
                    <label class="form-check-label" for="isDownloadable">Is downloadable?</label>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col">
                <button id="saveLessonBtn" class="btn btn-theme">Save Lesson</button>
            </div>
            <div class="col-auto">
                <button class="btn btn-link theme-red">Cancel</button>
            </div>
        </div>
    </div>
</div>