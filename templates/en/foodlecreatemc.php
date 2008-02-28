<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<form method="post" action="multiplechoice.php">

	<h1>Create new foodle</h1>

	<p>
		[ <a href="index.php">schedule an event</a> | multiple choice ]
	</p>

	<p>Name: 
	<input type="text" name="name" value=""<?php
	if (isset($this->data['name'])) echo $this->data['name'];
	?>"/>

	<p>Description: <br />
	<textarea style="width: 400px; height: 100px" name="descr"><?php
	if (isset($this->data['descr'])) echo $this->data['descr'];
	?></textarea>

	
	<h2>Create choices</h2>
	
	<table>
		<tr>
			<th><input type="text" name="c1" size="10" /></th>
			<td><input type="text" name="c11" size="10" /></td>
			<td><input type="text" name="c12" size="10" /></td>
			<td><input type="text" name="c13" size="10" /></td>
			<td><input type="text" name="c14" size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c2"  size="10" /></th>
			<td><input type="text" name="c21"  size="10" /></td>
			<td><input type="text" name="c22"  size="10" /></td>
			<td><input type="text" name="c23"  size="10" /></td>
			<td><input type="text" name="c24"  size="10" /></td>
		</tr2
		<tr>
			<th><input type="text" name="c3"  size="10" /></th>
			<td><input type="text" name="c31"  size="10" /></td>
			<td><input type="text" name="c32"  size="10" /></td>
			<td><input type="text" name="c33"  size="10" /></td>
			<td><input type="text" name="c34"  size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c4"  size="10" /></th>
			<td><input type="text" name="c41"  size="10" /></td>
			<td><input type="text" name="c42"  size="10" /></td>
			<td><input type="text" name="c43" size="10"  /></td>
			<td><input type="text" name="c44"  size="10" /></td>
		</tr>
	</table>
	


	<p>
	<input type="submit" name="save" value="Create a new foodle" />

</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>