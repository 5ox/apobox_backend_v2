<div class="row">
	<div class="col-md-offset-2 col-md-8">
		<fieldset id="CustomPackageRequest">
			<?= $this->element('CustomPackageRequests/manager_form_parts'); ?>
			<div class="row">
				<div class="col-md-12">
					<?= $this->Form->input('tracking_id', [
						'disabled' => !in_array('tracking_id', $allowedFields),
						'type' => 'text',
						'label' => [
							'text' => 'Inbound Tracking Number',
							'class' => 'col-sm-4 control-label',
						],
					]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-offset-4 col-md-6">
					<p>
						This is the FedEx, UPS or DHL inbound tracking number provided by the company you ordered from. Please make sure your tracking number is accurate. It is the only way for us to track inbound custom requests.
					</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?= $this->Form->input('package_repack', [
						'disabled' => !in_array('package_repack', $allowedFields),
						'type' => 'select',
						'default' => 'yes',
						'label' => [
							'text' => 'Repack To Be APO/FPO Friendly',
							'class' => 'col-sm-4 control-label',
						],
						'options' => [
							'yes' => 'Yes',
							'no' => 'No',
						]
					]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?= $this->Form->input('mail_class', [
						'disabled' => !in_array('mail_class', $allowedFields),
						'type' => 'select',
						'default' => 'priority',
						'label' => [
							'text' => 'Mail Class',
							'class' => 'col-sm-4 control-label',
						],
						'options' => [
							'priority' => 'Priority',
							'parcel' => 'Parcel Post',
						]
					]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?= $this->Form->input('insurance', [
						'disabled' => !in_array('insurance', $allowedFields),
						'type' => 'select',
						'default' => 'Default',
						'label' => [
							'text' => 'Insurance Coverage',
							'class' => 'col-sm-4 control-label',
						],
						'options' => [
							'Default',
							'Custom',
						]
					]); ?>
				</div>
			</div>
			<div id="insurance-coverage" class="row hidden">
				<div class="col-md-12">
					<?= $this->Form->input('insurance_coverage', [
						'disabled' => !in_array('insurance_coverage', $allowedFields),
						'label' => [
							'text' => 'Custom Insurance Amount',
							'class' => 'col-sm-4 control-label',
						],
					]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<?= $this->Form->input('instructions', [
						'disabled' => !in_array('instructions', $allowedFields),
						'type' => 'textarea',
						'label' => [
							'text' => 'Special Requests',
							'class' => 'col-sm-4 control-label',
						],
					]); ?>
				</div>
			</div>
		</fieldset>
	</div>
</div>
<?= $this->element('js-import', ['js' => 'elements/custom_package_requests/form']); ?>
