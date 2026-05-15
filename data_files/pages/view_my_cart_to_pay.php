<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-12 col-md">
            <h5>Student Profile</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3 mb-md-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Dashboard</a></li>
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=learning-student-home">Students</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Complete Payment</li>
                </ol>
            </nav>
        </div>
        <div class="col-12 col-md-auto"><button class="btn btn-theme" data-bs-toggle="modal"
                data-bs-target="#add-attendance-modal"><i data-feather="plus"></i> View Courses</button>
        </div>
    </div>
</div>
<div class="container mt-4" id="main-content">
    <div class="row">
        <div class="col-12 col-md-4 col-lg-3 order-1 mb-4">
            <figure class="avatar avatar-150 rounded coverimg align-middle shadow-sm position-relative mb-3">
                <img src="<?php echo $userProfileImage; ?>" alt="">
            </figure>
            <h4><?php echo $fullname; ?> <i class="bi bi-star-fill text-theme-1 theme-yellow small"></i></h4>
            <p><span class="badge badge-light bg-theme-1-subtle text-theme-1 theme-teal">Present</span>
                <span class="badge badge-light bg-theme-1-subtle text-theme-1 theme-orange">In-Campus</span>
            </p>
            <p class="text-secondary mb-1"><i class="bi bi-mortarboard me-2 align-middle"></i>
                11<sup>th</sup> Standard</p>
            <p class="text-secondary mb-1"><i class="bi bi-cake me-2 align-middle"></i> <span
                    class="align-middle">15/09/2000</span></p>
            <p class="text-secondary"><i class="bi bi-geo-alt me-2 align-middle"></i> <span class="align-middle">13th
                    Street. 47 W 13th St, New York, NY 10011, USA.</span></p>
        </div>
        <div class="col-12 col-lg-8 col-xl-9 order-2">
            <div class="row">
                <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row" id="cartContainer">
                                   
                                </div>
                            </div>
                        </div>
            </div>
            
        </div>
       
    </div>
   
</div>
<script>
    //load all Cart Items
document.addEventListener("DOMContentLoaded", function(){
    loadCart();
});
function loadCart(){

    fetch("ajax/ajax_fetch_cart.php")
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("cartContainer").innerHTML = "Cart empty";
            return;
        }

        let html = `<div class="row">`;

        let totalAmount = 0;

        res.data.forEach(course => {

            let thumbnail = course.thumbnail 
                ? course.thumbnail 
                : "uploads/course_default.png";

            let price = Number(course.final_price);
            totalAmount += price;

            html += `
            <div class="col-12 col-md-6 col-lg-6 col-xl-4 mb-4">
                <div class="card adminuiux-card bg-theme-r-gradient text-white">

                    <div class="position-absolute w-100 h-100 coverimg">
                        <img src="${thumbnail}" style="object-fit:cover;">
                    </div>

                    <div class="card-body text-center position-relative">

                        <figure class="avatar avatar-60 mb-3 mx-auto rounded">
                            <img src="${thumbnail}" class="mw-100">
                        </figure>

                        <h5>${course.title}</h5>
                        <p class="small opacity-75">Course</p>

                        <h4>
                            TZS ${price.toLocaleString()}
                        </h4>

                        ${
                            course.discount > 0
                            ? `<small class="text-light">
                                <s>TZS ${Number(course.price).toLocaleString()}</s>
                               </small>`
                            : ''
                        }

                        <!-- ✅ ACTIONS -->
                        <div class="mt-3">
                            <button onclick="choosePayment(${course.id})"
                                class="btn btn-sm btn-success">
                                Pay Now
                            </button>

                            <button onclick="removeFromCart(${course.id})"
                                class="btn btn-sm btn-light text-danger">
                                Remove
                            </button>

                        </div>

                    </div>
                </div>
            </div>
            `;
        });

        // ✅ OPTIONAL: KEEP TOTAL BUT REMOVE GLOBAL PAY BUTTON
        html += `
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-end">

                    <h5>Total Cart Value: 
                        <b class="text-theme-1">
                            TZS ${totalAmount.toLocaleString()}
                        </b>
                    </h5>

                </div>
            </div>
        </div>
        `;

        html += `</div>`;

        document.getElementById("cartContainer").innerHTML = html;

    });
}

 function choosePayment(course_id){

    Swal.fire({
        title: "Select Payment Method",
        html: `
            <div class="d-grid gap-3">

                <button id="mobileMoneyBtn" class="btn btn-success w-100">
                    📱 Pay with Mobile Money
                </button>

                <button id="cardBtn" class="btn btn-primary w-100">
                    💳 Pay with Bank Card
                </button>

            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {

            // 📱 Mobile Money
            document.getElementById("mobileMoneyBtn").onclick = function(){
                Swal.close();
                processPayment(course_id, "MOBILE_MONEY");
            };

            // 💳 Card
            document.getElementById("cardBtn").onclick = function(){
                Swal.close();
                processPayment(course_id, "CARD");
            };

        }
    });
}

function processPayment(course_id, method){

    Swal.fire({
        title: "Processing Payment...",
        text: "Please wait",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch("ajax/ajax_create_order.php", {
        method: "POST", 
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ 
            course_id: course_id,
            payment_method: method
        })
    })
    .then(res => res.json())
    .then(res => {

        if(res.status === "success"){

            Swal.fire({
                title: "Payment Successful 🎉",
                html: `
                    <p>Payment completed via <b>${method.replace("_"," ")}</b></p>

                    <a href="invoice.php?order_id=${res.order_id}" 
                       target="_blank"
                       class="btn btn-success mt-3">
                       Download Invoice
                    </a>
                `,
                icon: "success",
                showConfirmButton: false
            });

            loadCart();

        }else{

            Swal.fire({
                title: "Error",
                text: res.message,
                icon: "error"
            });

        }

    })
    .catch(err => {
        console.error(err);

        Swal.fire({
            title: "Failed",
            text: "Payment failed",
            icon: "error"
        });
    });
}

function payCourse(course_id){

    Swal.fire({
        title: "Confirm Payment",
        text: "Do you want to purchase this course?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Pay Now",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#28a745"
    }).then((result) => {

        if(result.isConfirmed){

            // 🔄 Show loading
            Swal.fire({
                title: "Processing...",
                text: "Please wait while we complete your payment",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("ajax/ajax_create_order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ course_id: course_id })
            })
            .then(res => res.json())
            .then(res => {

                if(res.status === "success"){

                    Swal.fire({
                        title: "Payment Successful 🎉",
                        html: `
                            <p>Your course has been purchased.</p>

                            <a href="print/invoice.php?order_id=${res.order_id}" 
                            target="_blank"
                            class="btn btn-success mt-3">
                            Download Invoice
                            </a>
                        `,
                        showConfirmButton: false
                    });

                    loadCart(); // refresh cart

                }else{

                    Swal.fire({
                        title: "Error",
                        text: res.message || "Something went wrong",
                        icon: "error"
                    });

                }

            })
            .catch(err => {
                console.error(err);

                Swal.fire({
                    title: "Failed",
                    text: "Payment failed. Try again.",
                    icon: "error"
                });
            });
        }

    });
}

function removeFromCart(course_id){

    fetch("ajax/ajax_remove_cart.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: "course_id=" + course_id
    })
    .then(res => res.json())
    .then(() => {
        loadCart();
    });
}
</script>