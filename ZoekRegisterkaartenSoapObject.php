<?php

namespace App\Apis\Rechtspraak;

class ZoekRegisterkaartenSoapObject
{
	public $voorvoegsel;
	public $achternaam;
	public $geboorte;
	private $birthday;

	public function __construct($name, $birthday, $voorvoegsel = null)
	{
		$this->voorvoegsel = $voorvoegsel; // ToDo; normalize name, so we don't send any non utf-8 chars...
		$this->achternaam = $name; // ToDo; normalize name, so we don't send any non utf-8 chars...
		$this->birthday = $birthday; // ToDo; normalize birthday to be sure we have the correct format...
	}

	/**
	 * Use this object to create the XML needed for the request.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return '<ZoekRegisterkaarten xmlns="ccbr.rechtspraak.nl/v1">' .
		           '<voorvoegsel>' . $this->voorvoegsel . '</voorvoegsel>'.
				   '<achternaam>' . $this->achternaam . '</achternaam>' .
				   '<geboorte xmlns:b="ccbr.rechtspraak.nl/v1/CcbrDataservice/berichten" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">' .
						'<b:Datum>' . $this->birthday . '</b:Datum>' .
						'<b:Jaar i:nil="true"/>' .
				   '</geboorte>' .
			   '</ZoekRegisterkaarten>';
	}
}
