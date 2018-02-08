

<!DOCTYPE html>
<html>
<head>
	<title>CDR</title>
	<script type="text/javascript" src="http://code.jquery.com/jquery-3.3.1.min.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<style>
	*, ::after, ::before {
	    box-sizing: border-box;
	    border-radius: 0px !important;
	}

	@media only screen and (max-width: 600px) {
		.card{
			margin: 0px !important;

		}
		table{
			display:  block ;
		    width: 100%;
		    overflow-x: auto;
		}
	}
	.loader {
	  border: 16px solid rgba(255, 193, 7, 0.3);
	  border-radius: 50% !important;
	  border-top: 16px solid #17a2b8;
	  width: 120px;
	  height: 120px;
	  -webkit-animation: spin 2s linear infinite; /* Safari */
	  animation: spin 2s linear infinite;
	  margin-bottom: 20px;
	  margin-top: 20px;
	}

	/* Safari */
	@-webkit-keyframes spin {
	  0% { -webkit-transform: rotate(0deg); }
	  100% { -webkit-transform: rotate(360deg); }
	}

	@keyframes spin {
	  0% { transform: rotate(0deg); }
	  100% { transform: rotate(360deg); }
	}
	th{
		cursor: pointer;
		width: 1px
	}
	.pe{
		pointer-events:none;
	}
	.card{
		margin: 10px;
    	margin-left: 20%;
   		margin-right: 20%;
	}

	body,form{

background: #007991;  /* fallback for old browsers */
background: -webkit-linear-gradient(to right, #78ffd6, #007991);  /* Chrome 10-25, Safari 5.1-6 */
background: linear-gradient(to right, #78ffd6, #007991); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

	}

</style>

</head>

<body>

	<div class="card" >
	<form method="POST" action="" >
		<input class="form-control col-md-8" style="float: left;" type="date" name="date" value="<?php if(isset($_POST['date'])){ echo $_POST['date']; } else{ echo date("Y-m-d",strtotime("-1 days")); }; ?>">
		<input class="btn btn-info col-md-4 sbtn" type="submit" name="submit">
	</form>
</body>
<style type="text/css">
	footer{
		width: 100%;
		position: fixed;
		bottom: 0;
		height: 30px;
		left: 0px;
		background: white;
		vertical-align: middle;		
	}
</style>
<footer>
	<center>
		<font>© 2018 Adalen Vladi</font>
	</center>
</footer>
</html>

<?php
if (isset($_POST['submit'])) {


	$con=mysqli_connect('192.168.1.231','cdr','cdr2016','cdrdb');
	
	$astDB=mysqli_connect('192.168.1.231','cron','1234','asterisk');

	$astResult=mysqli_query($astDB,"SELECT user,full_name,phone_login,custom_one from vicidial_users where custom_one=\"retention\" ");

	$users='';
	$usersA=array();
	while ($userR=$astResult->fetch_assoc()) {
		$phone=str_replace('cc','', $userR['user']) ;
		//echo $userR['phone_login'];
		$users=$users."src='".$phone."' or ";
		//$push = array($phone => $userR['full_name'] );
		$usersA[$phone]=$userR['full_name'];
	};
	$users=preg_replace('/\W\w+\s*(\W*)$/', '$1', $users);

	$query="SELECT  src,count(*) AS total_calls, sum(duration), sum(billsec) AS total_duration FROM cdr WHERE ($users) and DATE(`calldate`)=DATE('".$_POST['date']."') group by src ";

	$result=mysqli_query($con,$query);

	echo "<table id='table' class='table table-striped table-hover '><thead><th onclick='sortTable(0)'>Full Name</th><th onclick='sortTable(1)'>User</th><th onclick='sortTable(2)'>Calls</th><th onclick='sortTable(3)'>Minutes</th><th onclick='sortTable(4)'>AVG Minutes</th></thead>";

	while ($row=$result->fetch_assoc()) {
		$min=$row['total_duration']/60;
		$sec=$row['total_duration'] % 60;
		$avgmin=$row['total_duration']/$row['total_calls']/60;
		$avgsec=$row['total_duration']/$row['total_calls']%60;
		echo"<tr>";
			echo "<td>".$usersA[$row['src']]."</td><td>".$row['src']."</td><td>".$row['total_calls']."</td><td>".(int)$min.".".(int)$sec."</td><td>".(int)$avgmin.".".(int)$avgsec."</td>";
		echo "</tr>";
	}	
}

?>
<?php if ($_POST['date']) { ?>

<a style="float: right;margin: 30px" class="btn btn-warning ebtn" onclick="exportTableToCSV('<?php echo $_POST['date'] ?>.csv')">Export</a>

<?php } ?>

<center><div class="loader"></div></center>
</div>




<script type="text/javascript">


function downloadCSV(csv, filename) {
    var csvFile;
    var downloadLink;

    // CSV file
    csvFile = new Blob([csv], {type: "text/csv"});

    // Download link
    downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Hide download link
    downloadLink.style.display = "none";

    // Add the link to DOM
    document.body.appendChild(downloadLink);

    // Click download link
    downloadLink.click();
}

function exportTableToCSV(filename) {
    var csv = [];
	var rows = document.querySelectorAll("table tr");
	
    for (var i = 0; i < rows.length; i++) {
		var row = [], cols = rows[i].querySelectorAll("td, th");
		
        for (var j = 0; j < cols.length; j++) 
            row.push(cols[j].innerText);
        
		csv.push(row.join(","));		
	}

    // Download CSV file
    downloadCSV(csv.join("\n"), filename);
}


function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("table");
  switching = true;
  //Set the sorting direction to ascending:
  dir = "asc"; 
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.getElementsByTagName("tr");
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("td")[n];
      y = rows[i + 1].getElementsByTagName("td")[n];
      /*check if the two rows should switch place,
      based on the direction, asc or desc:*/
      if (n==3 || n==4) {

	      if (dir == "asc") {
	        if (parseFloat(x.innerHTML) > parseFloat(y.innerHTML) ) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      } else if (dir == "desc") {
	        if (parseFloat(x.innerHTML) < parseFloat(y.innerHTML) ) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      }
      } else {
      if (dir == "asc") {
	        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      } else if (dir == "desc") {
	        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
	          //if so, mark as a switch and break the loop:
	          shouldSwitch= true;
	          break;
	        }
	      }
	  }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      //Each time a switch is done, increase this count by 1:
      switchcount ++;      
    } else {
      /*If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again.*/
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}


$(document).ready(function(){
    $('.loader').css('display','none');
});

$('.sbtn').on('click', function(event) {
	$('.loader').css('display','block');
	$('.ebtn').css('opacity',0.3);
	$('.ebtn').addClass('pe');

});
</script>

<?php if ($_POST['date']) { ?>

<script type="text/javascript">
	 sortTable(0);
</script>
<?php } ?>
