<?php if (!empty($u)): ?>
	<?php $this->append('navbar-collapsing-list-items'); ?>
		<li>
			<?= $this->Html->link('Dashboard', [
				'controller' => 'admins',
				'action' => 'index',
				$this->request->prefix => true,
			]); ?>
		</li>
		<?php if ($u['role'] === 'manager'): ?>
			<li>
				<?= $this->Html->link('Reports', [
					'controller' => 'reports',
					'action' => 'index',
					$this->request->prefix => true,
				]); ?>
			</li>
			<li>
				<?= $this->Html->link('Logs', [
					'controller' => 'logs',
					'action' => 'view',
					$this->request->prefix => true,
				]); ?>
			</li>
			<li>
				<?= $this->Html->link('Affiliate Links', [
					'controller' => 'affiliate_links',
					'action' => 'index',
					$this->request->prefix => true,
				]); ?>
			</li>
		<?php endif; ?>
	<?php $this->end(); ?>
<?php endif; ?>
<?php $this->append('navbar-pull-right-list-items'); ?>
		<?php if (!empty($u)): ?>
			<li class="dropdown dropdown-form settings-dropdown">
				<?= $this->Html->link(
					'<i class="fa fa-cog"></i>',
					'#',
					[
						'escape' => false,
						'title' => 'Settings',
						'class' => 'settings-btn dropdown-toggle',
						'data-toggle' => 'dropdown',
				]); ?>
				<div class="dropdown-menu">
					<div class="form-horizontal">
						<div class="form-group">
							<label>Printer IP Address:</label>
							<input id="Settings.local.printer_ip" />
						</div>
						<div class="form-group" id="Settings.local.scale_id">
							Scale ID:
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" name="scale-id" id="scale-id-apo1" value="apo1" checked>
									<a href="https://apo1.aposcales.autoploy.com:1880/scale/read" target="_blank">APO 1</a>
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" name="scale-id" id="scale-id-apo2" value="apo2">
									<a href="https://apo2.aposcales.autoploy.com:1880/scale/read" target="_blank">APO 2</a>
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" name="scale-id" id="scale-id-apo3" value="apo3">
									<a href="https://apo3.aposcales.autoploy.com:1880/scale/read" target="_blank">APO 3</a>
								</label>
							</div>
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" name="scale-id" id="scale-id-legacy" value="legacy">
									Legacy
								</label>
							</div>
						</div>
						<div class="form-group" id="Settings.local.scale_status">
							Scale Status:
							<div class="radio">
								<label class="radio-inline">
									<input type="radio" name="scale-status" id="scale-status-on" value="On" checked>
									On
								</label>
								<label class="radio-inline">
									<input type="radio" name="scale-status" id="scale-status-off" value="Off">
									Off
								</label>
							</div>
						</div>
					</div>
				</div>
			</li>
			<li>
				<?= $this->Html->link(__('Logout'), [
					'controller' => 'admins',
					'action' => 'logout',
					$this->request->prefix => false,
				]);?>
			</li>
		<?php else: ?>
			<li>
				<?= $this->Html->link(__('Login'), [
					'controller' => 'admins',
					'action' => 'login',
				]);?>
			</li>
		<?php endif; ?>
<?php $this->end(); ?>
<?= $this->element('js-import', ['js' => 'layouts/admin/navbar_items']); ?>
