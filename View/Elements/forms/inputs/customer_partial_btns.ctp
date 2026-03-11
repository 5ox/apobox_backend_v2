<?php $hash = ($partial == 'payment_info' ? 'payment' : str_replace('_', '-', $partial)); ?>
<?php $class = ($partial == 'payment_info' ? 'col-sm-10 offset-sm-1' : 'col-sm-6 offset-sm-3'); ?>

<div class="row">
	<div class="<?php echo $class; ?>">
		<?php
			if (isset($manager) && $manager):
				echo $this->Html->link('Cancel', '#',
					array(
						'class' => 'float-start btn btn-warning',
						'onclick' => 'history.go(-1);'
					)
				);
			else:
				echo $this->Html->link('Cancel',
					array(
						'action' => 'account',
						'#' => $hash,
					),
					array(
						'class' => 'float-start btn btn-warning',
					)
				);
			endif;
			echo $this->Form->button('Update',
				array(
					'type' => 'submit',
					'class' => 'float-end btn btn-primary'
				)
			);
		?>
	</div>
</div>
