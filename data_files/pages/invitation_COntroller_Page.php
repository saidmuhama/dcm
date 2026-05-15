<div class="container py-4" id="main-content">

    <!-- HERO SECTION -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">

        <div class="card-body p-4 p-lg-5"
            style="background: linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);">

            <div class="row align-items-center">

                <div class="col-lg-8 text-white">

                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25 mb-3"
                        style="width:70px;height:70px;">

                        <i class="bi bi-people-fill fs-2"></i>

                    </div>

                    <h1 class="fw-bold mb-2">
                        Welcome,
                        <span class="text-warning">
                            <?php echo $fullname; ?>
                        </span>
                    </h1>

                    <p class="mb-0 fs-5 opacity-75">
                        Invite friends, students, and colleagues to explore and learn this course together.
                    </p>

                </div>

                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

                    <button class="btn btn-light btn-lg rounded-pill px-4 shadow-sm"
                        data-bs-toggle="collapse"
                        data-bs-target="#inviteFormCard">

                        <i class="bi bi-person-plus-fill me-2"></i>
                        Invite Student
                    </button>

                </div>

            </div>

        </div>

    </div>


    <!-- MAIN CARD -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <!-- HEADER -->
        <div class="card-header bg-white border-0 py-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

                <div>

                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-envelope-paper-fill text-primary me-2"></i>
                        Course Invitations
                    </h4>

                    <p class="text-muted mb-0">
                        Manage and track invited students
                    </p>

                </div>

                <div class="mt-3 mt-md-0">

                    <span class="badge bg-light text-dark rounded-pill px-3 py-2 shadow-sm">
                        <i class="bi bi-people me-1"></i>
                        Active Invitations
                    </span>

                </div>

            </div>

        </div>


        <!-- BODY -->
        <div class="card-body bg-light p-3 p-lg-4">

            <!-- INVITE FORM -->
            <div class="collapse mb-4" id="inviteFormCard">

                <div class="card border-0 shadow rounded-4 overflow-hidden">

                    <div class="card-body p-4">

                        <div class="d-flex align-items-center mb-4">

                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3"
                                style="width:55px;height:55px;">

                                <i class="bi bi-send-fill text-primary fs-4"></i>

                            </div>

                            <div>

                                <h5 class="fw-bold mb-1">
                                    Invite New Student
                                </h5>

                                <small class="text-muted">
                                    Fill student details and send invitation
                                </small>

                            </div>

                        </div>


                        <form id="inviteForm">

                            <div class="row">

                                <!-- FIRST NAME -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-semibold">
                                        First Name
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-person"></i>
                                        </span>

                                        <input type="text"
                                            id="first_name"
                                            class="form-control border-start-0 rounded-end-3"
                                            placeholder="Enter first name">

                                    </div>

                                </div>

                                <!-- LAST NAME -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-semibold">
                                        Last Name
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-person-badge"></i>
                                        </span>

                                        <input type="text"
                                            id="last_name"
                                            class="form-control border-start-0 rounded-end-3"
                                            placeholder="Enter last name">

                                    </div>

                                </div>

                                <!-- PHONE -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-semibold">
                                        Phone Number
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-telephone"></i>
                                        </span>

                                        <input type="text"
                                            id="phone"
                                            class="form-control border-start-0 rounded-end-3"
                                            placeholder="2557XXXXXXXX">

                                    </div>

                                </div>

                                <!-- CODE -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-semibold">
                                        Invitation Code
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-shield-lock"></i>
                                        </span>

                                        <input type="text"
                                            id="invitation_code"
                                            class="form-control border-start-0 rounded-end-3 fw-bold"
                                            readonly>

                                    </div>

                                </div>

                            </div>

                            <!-- BUTTON -->
                            <div class="mt-2">

                                <button type="button"
                                    onclick="saveInvitee()"
                                    class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">

                                    <i class="bi bi-send-check-fill me-2"></i>
                                    Send Invitation

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            <!-- TABLE -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table align-middle table-hover mb-0">

                            <thead class="table-light">

                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Student</th>
                                    <th>Phone</th>
                                    <th>Invitation Code</th>
                                    <th>Status</th>
                                    <th class="pe-4">Date</th>
                                </tr>

                            </thead>

                            <tbody id="inviteeList">

                                <!-- AJAX DATA -->

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

                        <script>

                        // =============================
                        // GENERATE INVITATION CODE
                        // =============================
                        document.addEventListener("DOMContentLoaded", () => {

                            generateInvitationCode();
                            loadInvitees();

                        });

                        function generateInvitationCode(){

                            let code = 'REF-' + Math.random().toString(36).substring(2,8).toUpperCase();

                            document.getElementById("invitation_code").value = code;
                        }


                        // =============================
                        // SAVE INVITEE
                        // =============================
                        function saveInvitee() {

                        const params = new URLSearchParams(window.location.search);
                        let course_id = params.get("course_id");

                        let first_name = document.getElementById("first_name").value.trim();
                        let last_name  = document.getElementById("last_name").value.trim();
                        let phone      = document.getElementById("phone").value.trim();
                      
                        // Remove spaces and non-digits
                        phone = phone.replace(/\D/g, "");
                        // Convert to 255 format
                        if (phone.startsWith("0")) {
                            phone = "255" + phone.substring(1);
                        } else if (!phone.startsWith("255")) {
                            phone = "255" + phone;
                        }

                        let code       = document.getElementById("invitation_code").value.trim();

                        if (!first_name || !phone || !code || !course_id) {
                            Swal.fire("Error", "Please fill all required fields", "error");
                            return;
                        }

                        fetch("ajax/ajax_save_invitee.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                first_name: first_name,
                                last_name: last_name,
                                phone: phone,
                                course_id: course_id,
                                invitation_code: code
                            })
                        })

                        .then(res => res.json())
                        .then(res => {

                            if (res.status === "success") {

                                Swal.fire("Success", res.message, "success");

                                document.getElementById("inviteForm").reset();

                                loadInvitees();

                            } else {

                                Swal.fire("Error", res.message || "Failed", "error");
                            }

                        })

                        .catch(err => {

                            console.error(err);

                            Swal.fire("Error", "Server error occurred", "error");
                        });
                    }



                        // =============================
                        // LOAD INVITED USERS
                        // =============================
                        function loadInvitees(){

                            const params = new URLSearchParams(window.location.search);

                            let course_id = params.get("course_id");

                            fetch("ajax/ajax_fetch_invitees.php?course_id=" + course_id)

                            .then(res => res.json())

                            .then(res => {

                                let html = "";

                                if(res.data.length === 0){

                                    html = `
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">

                                            <i class="bi bi-people fs-1 d-block mb-2"></i>

                                            No invitees found

                                        </td>
                                    </tr>
                                    `;

                                    document.getElementById("inviteeList").innerHTML = html;

                                    return;
                                }

                                res.data.forEach((row,index)=>{

                                    html += `
                                    <tr>

                                        <td>${index+1}</td>

                                        <td>
                                            <div class="fw-bold">
                                                ${row.first_name} ${row.last_name}
                                            </div>

                                            <small class="text-muted">
                                                Invited by ${row.invited_by_name}
                                            </small>
                                        </td>

                                        <td>${row.phone}</td>

                                        <td>
                                            <span class="badge bg-primary rounded-pill px-3">
                                                ${row.invitation_code}
                                            </span>
                                        </td>

                                        <td>
                                            ${
                                                row.status == 1
                                                ? `<span class="badge bg-success">Joined</span>`
                                                : `<span class="badge bg-warning text-dark">Pending</span>`
                                            }
                                        </td>

                                        <td>
                                            <small class="text-muted">
                                                ${row.created_at}
                                            </small>
                                        </td>

                                    </tr>
                                    `;
                                });

                                document.getElementById("inviteeList").innerHTML = html;

                            });
                        }

                        </script>

                </div>
</div>