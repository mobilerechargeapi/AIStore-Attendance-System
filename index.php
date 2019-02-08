<?php
/*
  Plugin Name: AIStore Attendance System
  Version: 1.0.0
  Plugin URI: #
  Author: susheelhbti
  Author URI: http://www.aistore2030.com/
  Description: AIStore Attendance System wordpress plugin for Attendance of company employee
  */
 
 
  



 add_action( 'admin_menu', 'aistore2030_register_my_custom_menu_page' );
function aistore2030_register_my_custom_menu_page() {

 
    
    add_menu_page('Attendance', 'Attendance', 'manage_options', 'aistore2030_full_attendance','aistore2030_full_attendance','',71);
    
    
add_submenu_page( 'aistore2030_full_attendance', 'Attendance Page', 'Attendance Page',
    'manage_options', 'aistore2030_daily_attendance', 'aistore2030_daily_attendance');
    
    
    
    
add_submenu_page( 'aistore2030_full_attendance', 'Punch in/out', 'Punch in/out',
    'manage_options', 'aistore2030_punch_in_punch_out', 'aistore2030_punch_in_punch_out');
    
     
      
}
 
 

  function aistore2030_daily_attendance()
  {   
$user = wp_get_current_user();
$id=$user->ID ;
global $wpdb;
$month=date('m');


if (isset($_REQUEST['month'])) {
    $month=$_REQUEST['month'];
} 

$result = $wpdb->get_results("SELECT user_id,display_name,count(user_id ) as working_days FROM `wp1f_attendance` WHERE MONTH(adate)=".$month." GROUP by user_id,display_name");

echo "<h2>Employee working days report monthly for salary preparation </h2>";
 
$url=admin_url("admin.php?page=aistore2030_daily_attendance&month=".date('m', strtotime('-4 month')));
echo "<a href='$url'>".date('F', strtotime('-4 month')) . "</a>  ";
 
$url=admin_url("admin.php?page=aistore2030_daily_attendance&month=".date('m', strtotime('-3 month')));
echo "<a href='$url'>".date('F', strtotime('-3 month')) . "</a>  ";
 
$url=admin_url("admin.php?page=aistore2030_daily_attendance&month=".date('m', strtotime('-2 month')));
echo "<a href='$url'>".date('F', strtotime('-2 month')) . "</a>  ";

$url=admin_url("admin.php?page=aistore2030_daily_attendance&month=".date('m', strtotime('-1 month')));
echo "<a href='$url'>".date('F', strtotime('-1 month')) . "</a>  ";

$url=admin_url("admin.php?page=aistore2030_daily_attendance&month=".date('m'));
echo "<a href='$url'>".date('F') . "</a>  ";

 
?>
 <table class="widefat"> 

 <thead>
    <tr>
         <th>User Id</th>  
        <th>Full Name</th>
              
        <th>Working Days</th>
    </tr>
</thead>
<?php 
foreach($result as $wp_formmaker_submits){

 
  echo "<tr>";
    echo "<td>".$wp_formmaker_submits->user_id."</td>";
    echo "<td>".$wp_formmaker_submits->display_name."</td>";
    echo "<td>".$wp_formmaker_submits->working_days."</td>";
      
	 
   echo "</tr>";
}
  

    echo "</table>";
}








 
  function aistore2030_full_attendance()
  {   
      echo  "<div class='wrap'>" ;
      $user = wp_get_current_user();
      
      $id=$user->ID ;
      
      
    global $wpdb;

 


$user_id=$id;








 if (isset($_REQUEST['user_id'])) {
    
	$user_id = sanitize_text_field($_REQUEST['user_id']);
}



echo  "<h2>Full Attendance  sheet of the company (Recent 60 Records )</h2>" ;



$result = $wpdb->get_results("SELECT distinct display_name,user_id FROM wp1f_attendance order by id desc");

$i=0;

echo  '<table class="widefat"> <tr>' ;

foreach($result as $display_name){

 $i=$i+1;

  $url=admin_url( 'admin.php?page=aistore2030_full_attendance&user_id='.$display_name->user_id   );
  
  
  
    echo  "<td><a href='".$url."' >".$display_name->display_name."</a></td>" ;
    
    if($i==3)
    {
    echo  "</tr><tr>" ;
    $i=0;
    }
    
    
    
}
 echo  "</tr></table>" ;


$result = $wpdb->get_results( $wpdb->prepare( "SELECT * , TIMESTAMPDIFF(HOUR, entrytime, entrytime) AS hours_different FROM wp1f_attendance  where user_id=%d   limit 60",$user_id));
?>

 <table class="widefat"> 
 

 <thead>
    <tr>
        <th>id</th>
        <th>Date</th>       
        <th>Name </th>
        <th>Entry Time </th>
        <th>Entry IP Address </th>
        <th>Exit Time </th>
        <th>Exit IP Address </th> 
        
         <th>Hours Different</th>       
         
    </tr>
</thead> 

<?php 

foreach($result as $wp_formmaker_submits){

 
  echo "<tr>";
    echo "<td>".$wp_formmaker_submits->id."</td>";
    echo "<td>".$wp_formmaker_submits->adate."</td>";
    echo "<td>".$wp_formmaker_submits->display_name."</td>";
     
     echo "<td>".$wp_formmaker_submits->entrytime."</td>";
	 
     echo "<td>".$wp_formmaker_submits->entry_ip_address."</td>";
     echo "<td>".$wp_formmaker_submits->exittime."</td>";
	 
     echo "<td>".$wp_formmaker_submits->exit_ip_address."</td>";
	  
	 
	 
     echo "<td>".$wp_formmaker_submits->hours_different."</td>";
	 
   echo "</tr>";
}
  

    echo "</table>";
    
     
     

}



 

  function aistore2030_punch_in_punch_out()
  {   
  //CREATE UNIQUE INDEX wp1f_attendance_index ON wp1f_attendance (user_id, adate);
  
  
      
      $user = wp_get_current_user();
      
      $id=$user->ID ;
      
        $display_name=$user->display_name  ;
        $ip_address=aistore_getRealIpAddr();
        
    global $wpdb;
    
    
    if ( 
    ! isset( $_POST['punch_nonce'] ) 
    || ! wp_verify_nonce( $_POST['punch_nonce'], 'punch_nonce' ) 
) {
 
    

} else {


$type = sanitize_text_field($_REQUEST['type']);


if($type=="in")
{
	
	
 $wpdb->query( $wpdb->prepare( "INSERT INTO wp1f_attendance (user_id,adate,
display_name,entrytime,entry_ip_address ) VALUES (%d,date(now()),%s,now() ,%s)",array($id, $display_name,$ip_address )));
}

elseif($type=="out")
{
	
	
 $wpdb->query( $wpdb->prepare( "update wp1f_attendance 
 set 
  exittime= now()  ,
  exit_ip_address = %s
 where
  user_id = %d    and 
  adate=  date(now())
  " ,array( $ip_address, $id) )
 );
 

}

}

 ?>


 
 <h2> Attendance  sheet  </h2> 

 
 
 
   <table width="50%" border=1>
        
        <tr><th>Punch IN</th><th>Punch Out</th></tr>
        
        <tr>
        
        <td>
            
            <form method="post" action="">
   <!-- some inputs here ... -->
   <input type="hidden" name="type" value="in" />
   
   <?php wp_nonce_field( 'punch_nonce', 'punch_nonce' ); ?>
   
   <input type="submit" value="Punch In" />
</form>
        </td>
        
        <td>
             <form method="post" action="">
   <!-- some inputs here ... -->
   <input type="hidden" name="type" value="out" /> 
   
   <?php wp_nonce_field( 'punch_nonce', 'punch_nonce' ); ?>
 
   <input type="submit"   value="Punch Out"/>
   
  
  </form>
      
            
        </td></tr>
    </table>
    
       

       
   



 <table class="widefat"> 
 

 <thead>
    <tr>
        <th>Name</th>
               
   
       <th>Entry time</th>       
        <th>Exit time</th>  
    </tr>
</thead> 


<?php  
   
      
  
  

 $result = $wpdb->get_results("SELECT   *  FROM wp1f_attendance WHERE DATE(entrytime) = CURDATE()  order by id desc");

 
 

foreach($result as $wp_formmaker_submits){

 
 echo "<tr>";
 
  
    echo "<td>".$wp_formmaker_submits->display_name."</td>";
     
     echo "<td>".$wp_formmaker_submits->entrytime." (";
	 
     echo "".$wp_formmaker_submits->entry_ip_address." )</td>";
     echo "<td>".$wp_formmaker_submits->exittime." ( ";
	 
     echo "".$wp_formmaker_submits->exit_ip_address." )</td>";
	  
	  
    echo "</tr>";
 
}
   

    echo "</table>";
}



function aistore_getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}


?>
