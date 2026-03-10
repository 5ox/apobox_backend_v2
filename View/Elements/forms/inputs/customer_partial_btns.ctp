<?php $hash = ($partial == 'payment_info' ? 'payment' : str_replace('_', '-', $partial)); ?>
<?php $class = ($partial == 'payment_info' ? 'col-sm-10 col-sm-offset-1' : 'col-sm-6 col-sm-offset-3'); ?>

<div class="row">
	<div class="<?php echo $class; ?>">
		<?php
			if (isset($manager) && $manager):
				echo $this->Html->link('Cancel', '#',
					array(
						'class' => 'pull-left btn btn-warning',
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
						'class' => 'pull-left btn btn-warning',
					)
				);
			endif;
			echo $this->Form->button('Update',
				array(
					'type' => 'submit',
					'class' => 'pull-right btn btn-primary'
				)
			);
		?>
	</div>
</div>
