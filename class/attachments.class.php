<?php

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call or for session timeout on our module page
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class Attachments extends SeedObject
{
    /**
     * Attachments constructor.
     * @param DoliDB    $db    Database connector
     */
    public function __construct($db)
    {
		$this->db = $db;
    }
}
