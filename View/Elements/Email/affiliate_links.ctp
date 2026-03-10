<?php if (isset($affiliateLinks) && is_array($affiliateLinks)): ?>
	<ul>
		<?php foreach ($affiliateLinks as $link): ?>
			<li>
				<?= $this->Html->link($link['AffiliateLink']['title'], $link['AffiliateLink']['url']) ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
