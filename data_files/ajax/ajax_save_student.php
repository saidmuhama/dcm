<?php
header('Content-Type: application/json');

include('../config/db.php'); 
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;

// IMAGE
$imagePath = "";

if(!empty($data['image'])){

    // Ensure folder exists
    $folder ="../uploads/";

    if(!is_dir($folder)){
        mkdir($folder, 0777, true);
    }

    // Clean base64
    $img = $data['image'];

    if(strpos($img, 'base64,') !== false){
        $img = explode('base64,', $img)[1];
    }

    $img = str_replace(' ', '+', $img);

    // Decode
    $decoded = base64_decode($img);

    if($decoded === false){
        echo json_encode(["status"=>"error","message"=>"Base64 decode failed"]);
        exit;
    }

    // Save file
    $fileName = time() . ".png";
    $filePath = $folder . $fileName;

    if(file_put_contents($filePath, $decoded)){
        $imagePath = "uploads/" . $fileName; // save relative path for DB
    } else {
        echo json_encode(["status"=>"error","message"=>"Failed to save image"]);
        exit;
    }

}

// ================= UPDATE =================
if($id){


    if($imagePath){
        $sql = "UPDATE tbl_students SET end_year=?,start_year=?,sub_academic_level=?,main_academic_level=?,first_name=?,middle_name=?,last_name=?,dob=?,description=?,skill=?,parent_name=?,phone=?,email=?,school=?,course=?,image=? WHERE usr_code=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("siiisssssssssssss",
            $data['end_year'],$data['start_year'],$data['sub_academic_level'],$data['main_academic_level'],
            $data['first_name'],$data['middle_name'],$data['last_name'],$data['dob'],
            $data['description'],$data['skill'],$data['parent_name'],$data['phone'],
            $data['email'],$data['school'],$data['course'],$imagePath,$id
        );
    }else{
        $sql = "UPDATE tbl_students SET end_year=?, start_year=?,sub_academic_level=?,main_academic_level=?,first_name=?,middle_name=?,last_name=?,dob=?,description=?,skill=?,parent_name=?,phone=?,email=?,school=?,course=? WHERE usr_code=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("siiissssssssssss",
            $data['end_year'],$data['start_year'],$data['sub_academic_level'],$data['main_academic_level'],
            $data['first_name'],$data['middle_name'],$data['last_name'],$data['dob'],
            $data['description'],$data['skill'],$data['parent_name'],$data['phone'],
            $data['email'],$data['school'],$data['course'],$id
        );
    }

    $stmt->execute();

    echo json_encode(["status"=>"success","message"=>"Student Information Updated successfully","id"=>$id]);

}else{

// ================= INSERT =================

    $sql = "INSERT INTO tbl_students(usr_code,end_year,start_year,sub_academic_level,main_academic_level,first_name,middle_name,last_name,dob,description,skill,parent_name,phone,email,school,course,image)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssiiissssssssssss",
        $data['usr_code'],$data['end_year'],$data['start_year'],$data['sub_academic_level'],$data['main_academic_level'],$data['first_name'],$data['middle_name'],$data['last_name'],$data['dob'],
        $data['description'],$data['skill'],$data['parent_name'],$data['phone'],
        $data['email'],$data['school'],$data['course'],$imagePath
    );

    $stmt->execute();

    echo json_encode([
        "status"=>"success",
        "message"=>"Student Information added",
        "id"=>$stmt->insert_id
    ]);
}