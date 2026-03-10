<nav>
	<ul class="pagination pagination-large">
	<?php
		echo $this->Paginator->prev(
			__('prev'),
			['tag' => 'li'],
			null,
			[
				'tag' => 'li',
				'class' => 'disabled',
				'disabledTag' => 'a'
			]
		);
		echo $this->Paginator->numbers([
			'separator' => '',
			'currentTag' => 'a',
			'currentClass' => 'active',
			'tag' => 'li',
			'first' => 1,
		]);
		echo $this->Paginator->next(
			__('next'),
			[
				'tag' => 'li',
				'currentClass' => 'disabled'
			],
			null,
			[
				'tag' => 'li',
				'class' => 'disabled',
				'disabledTag' => 'a',
			]
		);
		?>
	</ul>
</nav>
