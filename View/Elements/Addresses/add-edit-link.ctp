<?php
if (empty($address['address_book_id'])) {
	echo $this->Html->link('Add', array(
		'controller' => 'addresses',
		'action' => 'add',
	));
} else {
	echo $this->Html->link('Edit', array(
		'controller' => 'addresses',
		'action' => 'edit',
		'id' => $address['address_book_id'],
	));
}
