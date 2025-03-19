<?php
	include "config.php";
 
	if(!empty($_POST['suhu'])  && !empty($_POST['kadar_asap']) && !empty($_POST['status']))
	{
		$suhu = $_POST['suhu'];
		$kadar_asap = $_POST['kadar_asap'];
		$status = $_POST['status'];
 
		$sql = "INSERT INTO tbl_temperature (suhu, kadar_asap, status)
		VALUES ('".$suhu."','".$kadar_asap."','".$status."')";
 
		if ($conn->query($sql) === TRUE) {
			echo "Data berhasil tersimpan.";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
 
	$conn->close();
?>
