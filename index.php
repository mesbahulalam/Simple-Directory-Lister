<?php
/* Configuration */
$hide = array('.htaccess'); //Hide these files
$hide_ext = array('ico','db'); //Hide these extensions
/* The main thing */
error_reporting(E_ALL & ~E_NOTICE);
clearstatcache();
define('DS', DIRECTORY_SEPARATOR);
$dir = '';
$root = dirname(__FILE__);
if($_GET['dir'] AND stripos(realpath($_GET['dir']),$root)===0){
	$dir = str_ireplace($root.DS, '', realpath($_GET['dir'])).DS;
}
// Read the requested directory and save the files to an array
foreach(glob($dir.'*') as $item){
	$ext = strtolower(substr($item, strrpos($item, '.')+1));
	if(@!in_array($item, $hide) AND @!in_array($ext, $hide_ext)){
		if(is_dir($item)){
			$items['dir'][] = $item;
		}else{
			$items['file'][] = $item;
		}
	}
}
// Sorting the list to look pretty
@natcasesort($items['dir']);
@natcasesort($items['file']);
function formatbytes($bytes, $precision = 2){
	$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes, $precision) . '' . $units[$pow];
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
	body {
		max-width: 900px;
		margin: 0 auto;
		padding: 10px;
	}
	a, a:link, a:active {text-decoration:none}
	#Input {
		box-sizing: border-box;
		width: 100%;
		font-size: 16px;
		padding: 12px 20px 12px 40px;
		border: 1px solid #ddd;
		margin-bottom: 12px;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
		width: 100%;
		border: 1px solid #ddd;
	}

	th, td {
		text-align: left;
		padding: 16px;
	}
	th {
		cursor: pointer;
	}
	tr:nth-child(even) {
		background-color: #f2f2f2
	}
	tr {
		transition: 0.2s;
	}
	/*tr:hover {
		background-color: #e1e1e1;
		box-sizing: border-box;
	}*/
	ul.breadcrumb {
		padding: 10px 16px;
		list-style: none;
		background-color: #eee;
	}
	ul.breadcrumb li {
		display: inline;
		font-size: 18px;
	}
	ul.breadcrumb li+li:before {
		padding: 8px;
		color: black;
		content: "/\00a0";
	}
	ul.breadcrumb li a {
		color: #0275d8;
		text-decoration: none;
	}
	ul.breadcrumb li a:hover {
		color: #01447e;
		text-decoration: underline;
	}
	</style>
	<title>Directory Listing Of <?php echo DS.$dir; ?></title>
</head>
<body>

<!--h2>You Are Here</h2>
<ul class="breadcrumb">
  <li><a href="?dir=">Home</a></li>
</ul-->
<h2>Index of <?php echo DS.$dir; ?></h2>
<input type="text" id="Input" onkeyup="filterTable()" placeholder="Search for file.." title="Type in a name">
<noscript><style>#Input{display:none}</style></noscript>
<table id="myTable">
  <tr><th onclick="sortTable(0)">Name</th><th onclick="sortTable(1)">Size</th><th onclick="sortTable(2)">Date Modified</th></tr>
  <?php
	if(count($items['dir'])>0){
		foreach($items['dir'] as $item){
		echo '
  <tr><td><a href="?dir='.$item.'">'.basename($item).'</a></td><td>&nbsp;</td><td>'.date ("M d Y h:i:s A", filemtime($item)).'</td></tr>';
		}
	}
	if(count($items['file'])>0){
		foreach($items['file'] as $item){
		echo '
  <tr><td><a href="'.$item.'">'.basename($item).'</a></td><td>'.formatbytes(filesize($item)).'</td><td>'.date ("M d Y h:i:s A", filemtime($item)).'</td></tr>';
		}
	}
  ?>
</table>
<script>
function sortTable(n) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById("myTable");
    switching = true;
    //Set the sorting direction to ascending:
    dir = "asc";
    /*Make a loop that will continue until
	no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        rows = table.getElementsByTagName("TR");
        /*Loop through all table rows (except the
		first, which contains table headers):*/
        for (i = 1; i < (rows.length - 1); i++) {
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare,
			one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            /*check if the two rows should switch place,
			based on the direction, asc or desc:*/
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
			and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            //Each time a switch is done, increase this count by 1:
            switchcount++;
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

function filterTable() {
    var input, filter, table, tr, td, i;
    input = document.getElementById("Input");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>
</body>
</html>
