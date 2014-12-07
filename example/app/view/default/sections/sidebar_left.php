<div class="logo_main">
	<a href="<?php echo $data->mainurl; ?>"></a>
</div>
<div class="profile">
	<img src="<?php echo $data->templateurl.$data->profile['photo']; ?>" />
	<br />
	<?php echo $data->profile['name']; ?>
	<?php echo $data->profile['email']; ?>
</div>
<div class="office">
<?php if(isset($data->office['office'])): ?>
	<?php echo $data->office['office']; ?>
	<?php echo $data->office['street']; ?>
	<?php echo $data->office['phone']; ?>
<?php endif; ?>
</div>
<div class="status">
	<?php foreach($data->status as $status): ?>
		<div class="bg<?php echo $status['color']; ?>">
			<span><?php echo $status['status']; ?></span>
		</div>
	<?php endforeach; ?>
</div>
<div class="offer">
	<?php foreach($data->offer as $service): ?>
		<div class="service service<?php echo $service['id']?>" >
			<?php echo $service['name']; ?><span class="<?php echo str_replace(' ', '_', strtolower(str_replace('á', 'a', $service['name']))); ?>"></span>			
		</div>
	<?php endforeach; ?>
</div>