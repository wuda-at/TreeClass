<?php
  /*******************************************************
    Test environment for demonstrating the purpose and 
    functions of the Tree Class.
  ********************************************************

    Copyright (C) 2015  http://wuda.at/ and http://wudablog.de1.cc/

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
  ********************************************************/
  include_once('../wuda.cls_tree.php');

	$database = 'database.db';
	$deftable = 'tree';
  
	// Create instance
	$obj = new tree($database, $deftable);

  // Parameters
  $text = isset($_GET['itm_txt']) ? $_GET['itm_txt'] : "";
  $id = isset($_GET['itm_nbr']) ? $_GET['itm_nbr'] : "";
  
	// Check parameters
  $text = htmlspecialchars(trim($text));
  $id = htmlspecialchars(trim($id));
 
  if (is_numeric($id)) {
    $id = (int)$id;
    if ($id <= 0) $id = 1; // default node = 1

    // Actions
    //---- Add Node
    if (isset($_GET['sub_add'])) {
      if ($text != "") {
        $obj->addItem($text, $id);
        header('Location: '.$_SERVER['SCRIPT_NAME']);
        exit;
      }
    }    
    //---- Delete Node
    if (isset($_GET['sub_del'])) {
      if ($id > 1) {
        $obj->deleteItem($id);
        header('Location: '.$_SERVER['SCRIPT_NAME']);
        exit;
      }
    }
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
	<title>wudablog - Tree Example</title>
	<style>
		* {font-family: Arial, sans-serif;}
    body {width: 80%; margin: 0 auto;}
		h1 {font-size: 1em;}
    label {display: inline-block; width: 8em; }
		#tree {background: #eee; padding: .5em; border: 1px solid #ccc; border-radius: 5px;}
    #tree li {margin-bottom: .25em;}
    input {padding: .25em;}
	</style>
</head>
<body>
	<h1>wudablog - Tree Example</h1>
	<div id="tree">
		<?php
			// Output
			$obj->loadTree();
			echo $obj->getHTML();
		?>
	</div>
  <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <p><label>Node-Text:</label><input type="text" name="itm_txt" value="" /></p>
    <p><label>Parent-ID:</label><input style="width: 40px;" type="text" name="itm_nbr" value="1"/></p>
    <input type="submit" name="sub_add" value="Add Node" style="color: green;" />
    <input type="submit" name="sub_del" value="Delete Node ID"  style="color: red;"  />
  </form>
  <hr/>
	<small>Copyright &copy; <a href="http://wuda.at">wuda</a> and <a href="http://wudablog.de1.cc">wudablog</a></small>
</body>
</html>