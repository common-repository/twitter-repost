<?php
//
/* View file */
//========================
 ?>
<div class="wrap">
	<div id="icon-plugins" class="icon32"><br></div>
	<h2>Twitter repost photos plugin</h2>
	<h3>Get photos from your tweets sorted:</h3>
	<form method="post" action="">
		<table style="margin-top:10px;">
			<tr>
				<td><label for="username">Username (without @)<span> *</span>: </label></td>
				<td><input id="username" maxlength="45" size="30" name="username" value="" /></td>
			</tr>
			<tr>
				<td><label for="amount">Amount of tweets (reccommend from 10 to 20)<span> *</span>: </label></td>
				<td><input id="amount" maxlength="45" size="30" name="amount" value="10" /></td>
			</tr>
			<tr>
			<tr>
				<td><label for="category">Category to post in<span> *</span>: </label></td>
				<td><?= $stream->get_categories_multi($some_arr["category"]) ?></td>
			</tr>
				<td><input class="button-primary" type="submit" name="add" value="Add"></td>
				<td></td>
			</tr>
		</table>
	</form>
		<div class="spacer" style="margin-top:25px;"></div>
	<h3>Twitter streams:</h3>
	<table class="widefat" >
	<thead>
	    <tr>
	        <th>Username</th>
	        <th>Amount of tweets</th>
	        <th>Categories</th>
	        <th>Delete Stream</th>
	    </tr>
	</thead>
	<tfoot>
	    <tr>
	        <th>Username</th>
	        <th>Amount of tweets</th>
	        <th>Categories</th>
	        <th>Delete Stream</th>
	    </tr>
	</tfoot>
	<tbody>
		<?php
		if ($stream)
		{
			foreach ($streams as $key => $stream_here)
			{
		?>
			<tr>
	     <td><?= '@' .$stream_here->user ?></td>
	     <td><?= $stream_here->amount ?></td>
	     <td><?= $stream->get_categories($stream_here->category); ?></td>
	     <td><a href="<?= $_SERVER['REQUEST_URI'] ?>&delete=<?= $stream_here->id ?>">Delete stream</a></td>
	   </tr>
				<?
			}
		}
		 ?>
	</tbody>
	</table>
</div>