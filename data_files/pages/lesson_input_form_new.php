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
                        <option value="">Select Content Type</option>
                        <option value="video">Video</option>
                        <option value="pdf">PDF</option>
                        <option value="presentation">Presentation</option>
                        <option value="audio">Audio (MP3 / MP4)</option>
                    </select>
                    <label for="content_type">Content-Type</label>
                </div>
            </div>
            

        </div>
        
        <input type="hidden" id="chapter_id" value="<?php echo $_GET['chapter_id']; ?>">

    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col">
                <button id="saveLessonBtnNew" class="btn btn-theme">Create & Save Lesson</button>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-link theme-red" onclick="document.getElementById('chapterContents').innerHTML = ''">Cancel</button>
            </div>
        </div>
    </div>
</div>