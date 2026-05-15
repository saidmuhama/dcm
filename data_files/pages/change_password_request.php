<div class="container-fluid">
    <div class="auth-wrapper">
        <div class="row justify-content-center minheight-dynamic" style="--mih-dynamic: calc(100vh - 120px)">
            <div class="col-12 col-md-6 col-xl-4 d-flex flex-column px-0">
                <div class="h-100 py-4 px-3">
                    <div class="row h-100 align-items-center justify-content-center mt-md-3">
                        <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                            <h1 class="mb-0">Update Password</h1>
                            <h4 class="mb-3">Almost done!</h4>
                            <p class="text-secondary">
                                Enter your current password and set a new one to keep your account safe.
                            </p>

                            <div class="position-relative">
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="current_password" placeholder="Enter current password">
                                    <label for="current_password">Current Password</label>
                                </div>
                                <button class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                            <div class="position-relative">
                                <div class="form-floating mb-3"><input type="password" class="form-control"
                                        id="checkstrength" placeholder="Enter your new password"> <label
                                        for="checkstrength">New Password</label></div><button
                                    class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2"><i
                                        class="bi bi-eye"></i></button>
                            </div>
                            <div class="feedback mb-3 px-3">
                                <div class="row">
                                    <div class="col">
                                        <div class="check-strength" id="checksterngthdisplay">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
                                    </div>
                                    <div class="col-auto"><span class="small" id="textpassword"></span> <i
                                            class="bi bi-info-circle text-theme ms-1" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Password should contain atleast 1 capital, 1 alphanumeric & min. 8 characters"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="position-relative">
                                <div class="form-floating mb-4"><input type="password" class="form-control"
                                        id="passwdconfirm" placeholder="Confirm your new password"> <label
                                        for="passwdconfirm">Confirm Password</label></div><button
                                    class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2"><i
                                        class="bi bi-eye"></i></button>
                            </div>
                            <a href="javascript:void(0)" id="changePasswordBtn" class="btn btn-lg btn-theme w-100 mb-4">
                                Change Now
                            </a>
                            <br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

    // ================= TOGGLE PASSWORD =================
    document.querySelectorAll(".btn-square").forEach(btn=>{
        btn.addEventListener("click", function(){

            let input = this.parentElement.querySelector("input");

            if(input){
                input.type = input.type === "password" ? "text" : "password";
            }

        });
    });

    // ================= PASSWORD STRENGTH =================
    const passwordInput = document.getElementById("checkstrength");
    const strengthText = document.getElementById("textpassword");

    passwordInput.addEventListener("keyup", function(){

        let val = this.value;
        let strength = 0;

        if(val.length >= 8) strength++;
        if(/[A-Z]/.test(val)) strength++;
        if(/[0-9]/.test(val)) strength++;
        if(/[^A-Za-z0-9]/.test(val)) strength++;

        let text = ["Weak","Fair","Good","Strong"];
        strengthText.innerText = text[strength-1] || "";

    });

    // ================= CHANGE PASSWORD =================
    document.getElementById("changePasswordBtn").addEventListener("click", function(){

        let current = document.getElementById("current_password").value.trim();
        let password = document.getElementById("checkstrength").value.trim();
        let confirm  = document.getElementById("passwdconfirm").value.trim();

        // VALIDATION
        if(!current){
            Swal.fire("Error","Enter current password","error");
            return;
        }

        if(!password){
            Swal.fire("Error","Enter new password","error");
            return;
        }

        if(password.length < 8){
            Swal.fire("Error","Password must be at least 8 characters","error");
            return;
        }

        if(password !== confirm){
            Swal.fire("Error","Passwords do not match","error");
            return;
        }

        Swal.fire({
            title: "Updating...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("ajax/change_password.php",{
            method:"POST",
            headers:{"Content-Type":"application/json"},
            body: JSON.stringify({
                current_password: current,
                new_password: password
            })
        })
        .then(res=>res.json())
        .then(res=>{
            Swal.close();

            if(res.status === "success"){
                Swal.fire("Success", res.message, "success");
            }else{
                Swal.fire("Error", res.message, "error");
            }
        })
        .catch(()=>{
            Swal.fire("Error","Something went wrong","error");
        });

    });

});
</script>