<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-12 col-md">
            <h5>Add Student</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3 mb-md-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Dashboard</a></li>
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Teacher</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Manage Profile</li>
                </ol>
            </nav>
        </div>

        <!-- Profile Completion Progress -->
         <?php 
         $profile_completion = App::getProfileCompletionStatus($usr_code,$user_role); 
         ?>
        <div class="col-12 col-md-auto">
            <div class="row align-items-center">
                <div class="col text-md-end order-2 order-md-1">
                    <h6 class="mb-1"><?php echo $profile_completion; ?>% Profile Complete</h6>
                    <p class="text-secondary small">Provide Academic Details</p>
                </div>
                <div class="col-auto order-1 order-md-2">
                    <div class="width-50 position-relative">
                        <div id="circleprogressblue1"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="container mt-4" id="main-content">
    <div class="card adminuiux-card shadow-sm overflow-hidden mb-4" id="smartwizard2">
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="#step-1">
                    <div class="num">1</div>
                    <div>
                        <p class="h5 mb-0">Instructor/Teacher Details</p>
                        <p class="opacity-75 small">Name and Details</p>
                    </div>
                </a></li>
            <li class="nav-item"><a class="nav-link" href="#step-2">
                    <div class="num">2</div>
                    <div>
                        <p class="h5 mb-0">Contact</p>
                        <p class="opacity-75 small">Guide to reach you</p>
                    </div>
                </a></li>
            <li class="nav-item"><a class="nav-link" href="#step-3">
                    <div class="num">3</div>
                    <div>
                        <p class="h5 mb-0">Education</p>
                        <p class="opacity-75 small">Your Education Experience</p>
                    </div>
                </a></li>
        </ul>
        <div class="card-body pt-4 pb-0">
            <div class="tab-content">
                <div id="step-11" class="tab-pane px-0" role="tabpanel" aria-labelledby="step-11">
                    <div class="row">
                       <div class="col-12 col-lg-3 text-center mb-3">
                            <figure class="maxwidth-250 height-260 coverimg rounded position-relative mx-auto mb-3">
                            
                                <img id="previewImage" src="<?php echo $userProfileImage;?>" alt="">

                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <button type="button"
                                        class="btn btn-square btn-accent rounded-circle"
                                        onclick="document.getElementById('imageInput').click()">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                </div>

                                <!-- INPUT -->
                                <input type="file" id="imageInput" class="d-none" accept="image/*">
                                <input type="hidden" name="profile_image_base64" id="profile_image_base64">
                            </figure>

                            <!-- LIVE PREVIEW -->
                            <p class="h5" id="previewName"><?php echo $fullname;?></p>
                            <p class="small text-secondary" id="previewEmail"><?php echo $roleTitle;?></p>
                        </div>
                        <div class="col">
                            <p class="h6 py-2 mb-2">Basic Details</p>
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="namef"
                                            placeholder="Enter First Name" value="Said"> <label
                                            for="namef">First Name</label></div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="namem"
                                            placeholder="Enter Middle Name" value=""> <label
                                            for="namem">Middle Name (Optional)</label></div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="namel"
                                            placeholder="Enter Last Name " value="Muhama"> <label
                                            for="namel">Last Name</label></div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="input-group mb-3"><span
                                            class="input-group-text text-secondary" id="calendarpciker"
                                            onclick="$(this).next().find('input').click()"><i
                                                data-feather="calendar"></i></span>
                                        <div class="form-floating"><input class="form-control"
                                                id="datepicker" placeholder="Enter Last Name "
                                                value="11/05/2001"> <label for="namel">Date of Birth</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="h6 py-2 mb-2">About You</p>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-floating mb-3"><textarea
                                            class="form-control height-100" id="describe3"
                                            placeholder="Enter description about you"></textarea> <label
                                            for="describe3">Short description about you</label></div>
                                </div>
                            </div>
                            <p class="h6 py-2 mb-2">Specialities/Skillset</p>
                            <div class="row align-items-center">
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="tags1"
                                            placeholder="Enter your skills" value="Coding"> <label
                                            for="tags1">Skill 1</label></div>
                                </div>
                                <div class="col-auto"><button class="btn btn-link btn-square mb-3"><i
                                            class="bi bi-plus-lg"></i></button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-21" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-21">
                    <div class="row align-items-center mb-2">
                        <div class="col">
                            <p class="h6 py-2">Provide Official Information</p>
                        </div>
                    </div>
                    <div class="row align-items-center mb-1">
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <div class="form-floating mb-3"><input class="form-control" id="namefull"
                                    placeholder="Enter Full Name" value="Said Muhama"> <label
                                    for="namefull">Your Full Name</label></div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <div class="form-floating mb-3"><input class="form-control" id="phoneon2"
                                    placeholder="Enter phone" value="+255 767131788"> <label
                                    for="phoneon2">Enter Phone</label></div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <div class="form-floating mb-3"><input type="email" class="form-control"
                                    id="emailaddresson1" placeholder="Enter Email Address"
                                    disabled="disabled"> <label
                                    for="emailaddresson1">Email Address</label></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-lg-3">
                            <p class="h6 py-2 mb-2">Locate on Map</p><iframe
                                src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d788.4385190507815!2d-122.4278138198206!3d37.772364180178094!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1712990839970!5m2!1sen!2sin"
                                height="280" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                class="w-100 rounded mb-3"></iframe>
                        </div>
                        <div class="col">
                            <div class="row align-items-center mb-2">
                                <div class="col">
                                    <p class="h6 py-2">Your Address</p>
                                </div>
                                <div class="col-auto"></div>
                            </div>
                           
                            <div class="row mb-2">
                            
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="street"
                                            placeholder="Enter Street" value="Muhama Street"> <label
                                            for="street">Street</label></div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control"
                                            id="locality" placeholder="Enter locality" value="Madale"> <label
                                            for="locality">Locality</label></div>
                                </div>
                                
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="town"
                                            placeholder="Enter Town" value="Kinondoni"> <label
                                            for="town">Town</label></div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3"><input class="form-control" id="city"
                                            placeholder="Enter City" value="Dar es salaam"> <label
                                            for="city">City</label></div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-4">
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="country"
                                            placeholder="Enter Country" value="Tanzania"> 
                                            <label  for="country">Country</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="step-31" class="tab-pane px-0 pb-0" role="tabpanel" aria-labelledby="step-31">
                    
                    <div class="row align-items-center mb-2">
                        <div class="col">
                            <p class="h6 py-2">Current Eduction Details</p>
                        </div>
                        <div class="col-auto"></div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg col-xl mb-3">
                            <div class="form-floating">
                                <select class="form-select" id="main_academic_level">
                                    <option>Select Degree</option>
                                    <option value="Bachelor">Bachelor</option>
                                    <option value="Masters">Masters</option>
                                    <option value="Phd">Phd</option>
                                </select> 
                                <label for="uni-2a">Degree Level</label></div>
                        </div>
                        <div class="col-12 col-md-6 col-lg col-xl mb-3">
                            <div class="form-floating">
                                <input class="form-control" id="sub_academic_level" placeholder="Enter University / College" value="">
                                <label for="course-2a">University / College</label></div>
                        </div>

                        <div class="col-12 col-md-6 col-lg col-xl mb-3">
                            <div class="form-floating">
                                <input class="form-control" id="degree_title" placeholder="Masters in Information Security" value="">
                                <label for="degree_title">Degree Pursued</label></div>
                        </div>
                        
                        <div class="col-6 col-sm-5 col-lg-2 col-xl-2 mb-3">
                            <div class="form-floating"><select class="form-select" id="start-year-2a">
                                    <option>2020</option>
                                    <option>2021</option>
                                    <option>2022</option>
                                    <option>2023</option>
                                    <option>2024</option>
                                    <option>2025</option>
                                    <option selected="selected">2026</option>
                                </select> <label for="start-year-2a">Start Year</label></div>
                        </div>
                        <div class="col-6 col-sm-5 col-lg-2 col-xl-2 mb-3">
                            <div class="form-floating"><select class="form-select" id="end-year-2a">
                                    <option>Completed</option>
                                    <option>Continuing</option>
                                    <option selected="selected">Passed</option>
                                </select> <label for="end-year-2a">Status</label></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="progress bg-theme-1-subtle rounded-0">
            <div class="progress-bar bg-theme-accent-l-gradient h-100 rounded-0" role="progressbar"
                style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>


<script>

"use strict"; 
document.addEventListener("DOMContentLoaded", (function () 
{ 
    $("#smartwizard2").smartWizard({ 
        toolbar: { 
            extraHtml: '<a class="btn btn-outline-accent float-start" href="../data_files/?view=3002">Skip</a>' 
        } 
    }); 
    $("#smartwizard2").on("showStep", (function (t, e, a, r, n) { 
        "last" === n ? $(".finish-btn").show() : $(".finish-btn").hide() 
    })); 
    new ProgressBar.Circle(circleprogressblue1, { 
        color: "#015EC2", 
        strokeWidth: 10, 
        trailWidth: 10, 
        easing: "easeInOut", 
        trailColor: "rgba(66, 157, 255, 0.15)", 
        duration: 1400, 
        text: { autoStyleContainer: !1 }, 
        from: { color: "#015EC2", width: 10 }, 
        to: { color: "#015EC2", width: 10 }, 
        step: function (t, e) { 
            e.path.setAttribute("stroke", t.color), 
            e.path.setAttribute("stroke-width", t.width); 
            var a = Math.round(100 * e.value()); 
            0 === a ? e.setText("") : e.setText(a + "<small>%<small>") 
        } 
    }).animate(.<?php echo $profile_completion; ?>) 
}));


document.addEventListener("DOMContentLoaded", function(){

    const input = document.getElementById("imageInput");
    const preview = document.getElementById("previewImage");
    const base64Input = document.getElementById("profile_image_base64");

    input.addEventListener("change", function(){

        const file = this.files[0];

        if(!file) return;

        const reader = new FileReader();

        reader.onload = function(e){

            const base64 = e.target.result;

            // ✅ update img preview
            preview.src = base64;

            // ✅ update background (for UI theme)
            if(preview.parentElement){
                preview.parentElement.style.backgroundImage = `url('${base64}')`;
            }

            // ✅ STORE BASE64 FOR PHP
            if(base64Input){
                base64Input.value = base64;
            }

            console.log("Base64 stored and preview updated ✅");

        };

        reader.readAsDataURL(file);
    });

});


let base64Image = "";

// ================= IMAGE BASE64 =================
document.getElementById("imageInput").addEventListener("change", function(){
    const file = this.files[0];
    if(!file) return;

    const reader = new FileReader();
    reader.onload = function(e){
        base64Image = e.target.result;

        document.getElementById("previewImage").src = base64Image;
        document.getElementById("previewImage").parentElement.style.backgroundImage = `url('${base64Image}')`;
    };
    reader.readAsDataURL(file);
});

// ================= STEP CONTROL =================
let steps = ["step-11","step-21","step-31"];
let currentStep = 0;

function showStep(i){
    currentStep = i;

    steps.forEach((id,index)=>{
        document.getElementById(id).style.display = (index === i) ? "block" : "none";
    });

    let percent = ((i+1)/steps.length)*100;
    document.querySelector(".progress-bar").style.width = percent+"%";
}

// init
showStep(0);

// nav click
document.querySelectorAll("#smartwizard2 .nav-link").forEach((el,i)=>{
    el.addEventListener("click", function(e){
        e.preventDefault();
        showStep(i);
    });
});

// ================= COLLECT DATA =================
function getData(){
    return {
        id: "<?php echo $_SESSION['usr_code']; ?>",

        first_name: document.getElementById("namef").value,
        middle_name: document.getElementById("namem").value,
        last_name: document.getElementById("namel").value,
        dob: document.getElementById("datepicker").value,
        description: document.getElementById("describe3").value,
        skill: document.getElementById("tags1").value,

        parent_name: document.getElementById("namefull").value,
        phone: document.getElementById("phoneon2").value,
        email: document.getElementById("emailaddresson1").value,

        end_year: document.getElementById("end-year-2a").value,
        start_year: document.getElementById("start-year-2a").value,
        sub_academic_level: document.getElementById("sub_academic_level").value,
        main_academic_level: document.getElementById("main_academic_level").value,
        course:  document.getElementById('degree_title').value,
        city:    document.getElementById("city").value,
        town:    document.getElementById("town").value,
        street:  document.getElementById("street").value,
        country: document.getElementById("country").value,

        image: base64Image
    };
}

// ================= SAVE BUTTON =================
const saveBtn = document.createElement("button");
saveBtn.className = "btn btn-success m-3";
saveBtn.innerText = "Save Instructor Details";
document.getElementById("smartwizard2").appendChild(saveBtn);

saveBtn.addEventListener("click", function(){

    let data = getData();

    Swal.fire({
        title: "Saving...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch("ajax/ajax_save_teacher.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify(data)
    })
    .then(res=>res.json())
    .then(res=>{
        Swal.close();

        if(res.status === "success"){
            Swal.fire("Success", res.message, "success")
            .then(()=>{
                window.location.href = "?view=3002";
            });
        }else{
            Swal.fire("Error", res.message, "error");
        }
    })
    .catch(()=>{
        Swal.fire("Error","Something went wrong","error");
    });

});


// ================= LOAD EXISTING =================
window.addEventListener("DOMContentLoaded", function(){

    const id = "<?php echo $_SESSION['usr_code']; ?>";

    if(!id) return;

    fetch("ajax/ajax_get_teacher.php?id="+id)
    .then(res=>res.json())
    .then(res=>{

        if(!res) return;

        document.getElementById("namef").value = res.first_name;
        document.getElementById("namem").value = res.middle_name;
        document.getElementById("namel").value = res.last_name;
        document.getElementById("datepicker").value = res.dob;
        document.getElementById("describe3").value = res.description;
        document.getElementById("tags1").value = res.skill;

        document.getElementById("namefull").value = res.parent_name;
        document.getElementById("phoneon2").value = res.phone;
        document.getElementById("emailaddresson1").value = res.email;

        document.getElementById("uni-1a").value = res.school;
        document.getElementById("degree_title").value = res.course;

        document.getElementById("end-year-2a").value = res.end_year;
        document.getElementById("start-year-2a").value = res.start_year;

        document.getElementById("sub_academic_level").value = res.sub_academic_level;
        document.getElementById("main_academic_level").value = res.main_academic_level;

        document.getElementById("country").value = res.country;
        document.getElementById("city").value = res.city;
        document.getElementById("town").value = res.town;
        document.getElementById("street").value = res.street;

        if(res.image){
            document.getElementById("previewImage").src = res.image;
            document.getElementById("previewImage").parentElement.style.backgroundImage = `url('${res.image}')`;
        }

    });

});
</script>