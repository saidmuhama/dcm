<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

header('Content-Type: application/json');

$course_id = $_POST['course_id'];
$title     = $_POST['course_name'];
$price     = $_POST['course_price'];
$discount  = $_POST['course_discount'];
$desc      = $_POST['course_description'];
$cert      = $_POST['isCertificateOffered'];
$qna       = $_POST['isQandAEnabled'];
$old_course_name = $_POST['old_course_name'];
$library_id = $_POST['library_id'];
$library_key = $_POST['library_key'];

if(strtolower($old_course_name) !== strtolower($title))
{
    $res = App::renameVideoLibrary($library_id, $title, App::getBunnyNetApiKey());

    if ($res['status'] === "success") {
        $thumbnailPath = "";
        // UPLOAD
        if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0){

            $dir = "../uploads/";
            $name = time() . "_" . $_FILES['thumbnail']['name'];
            $path = $dir . $name;

            if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], $path)){
                $thumbnailPath = "uploads/" . $name;
            }
        }

        // UPDATE
        if($thumbnailPath != ""){
            $sql = "UPDATE tbl_courses SET 
                title='$title',
                price='$price',
                discount='$discount',
                description='$desc',
                thumbnail='$thumbnailPath',
                certificate='$cert',
                qna='$qna'
                WHERE id='$course_id'";
        } else {
            $sql = "UPDATE tbl_courses SET 
                title='$title',
                price='$price',
                discount='$discount',
                description='$desc',
                certificate='$cert',
                qna='$qna'
                WHERE id='$course_id'";
        }

        if(mysqli_query($db,$sql)){
            echo json_encode(["status"=>"success","message"=>"Course updated"]);
        }else{
            echo json_encode(["status"=>"error","message"=>"Failed"]);
        }
    } else {
        echo json_encode(["status"=>"error","message"=>"Failed to Update Course/Library Name: " . $res['message']]);

    }
}
else
{
        $thumbnailPath = "";
        // UPLOAD
        if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0){

            $dir = "../uploads/";
            $name = time() . "_" . $_FILES['thumbnail']['name'];
            $path = $dir . $name;

            if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], $path)){
                $thumbnailPath = "uploads/" . $name;
            }
        }

        // UPDATE
        if($thumbnailPath != ""){
            $sql = "UPDATE tbl_courses SET 
                title='$title',
                price='$price',
                discount='$discount',
                description='$desc',
                thumbnail='$thumbnailPath',
                certificate='$cert',
                qna='$qna'
                WHERE id='$course_id'";
        } else {
            $sql = "UPDATE tbl_courses SET 
                title='$title',
                price='$price',
                discount='$discount',
                description='$desc',
                certificate='$cert',
                qna='$qna'
                WHERE id='$course_id'";
        }

        if(mysqli_query($db,$sql)){
            echo json_encode(["status"=>"success","message"=>"Course updated"]);
        }else{
            echo json_encode(["status"=>"error","message"=>"Failed"]);
        }
}