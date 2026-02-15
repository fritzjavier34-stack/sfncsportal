<?php
 include("../../assets/config.php");
 $sql="select * from teachers";
 $result=mysqli_query($conn,$sql);
 if(mysqli_num_rows($result)>0){
 	while($row=mysqli_fetch_assoc($result)){
 		echo "<tr>
      <th scope='row'>".$row['s_no']."</th>
      <td>".$row['fname']."  ".$row['lname']."</td>
      <td>".$row['gender']."</td>
      <td><a href='modal-teacher.php?id=". $row['id'] ."'><button style='height: 35px; width: 100px; background-color: skyblue; color: white; border: none; border-radius: 8px;'>View More</button></a></td>
      
      <td>
          <a href='login_as.php?id=".$row['id']."&role=teacher'>
          <a href='login_as.php?id=".$row['id']."&role=teacher' class='btn btn-primary'>Access UI
          </a>
      </td>
    </tr>";
 	}
 }
?>