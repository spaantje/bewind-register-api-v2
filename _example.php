<?php

$voorvoegsel = null;
$achternaam = 'Test';
$geboortedatum = '1990-03-22';

$api = new CurateleEnBewindregisterApi;
$result = $api->search($achternaam, $geboortedatum, $voorvoegsel);

if ( ! isset($result->ZoekRegisterkaartenResult->Registerkaarten->ZoekRegisterkaart)) {
	exit(sprintf('Geen resultaten met naam "%s en geboortedatum %s"', $achternaam, $geboortedatum));
}

$ZoekRegisterkaarten = $result->ZoekRegisterkaartenResult->Registerkaarten->ZoekRegisterkaart;

if ( ! is_array($ZoekRegisterkaarten)) {
	$ZoekRegisterkaarten = [$ZoekRegisterkaarten];
}

foreach ($ZoekRegisterkaarten as $ZoekRegisterkaart) {
	$data = $api->registerkaart($ZoekRegisterkaart->Registerkaartidentificatie->RegisterkaartAanduiding);

	// todo: doe iets met de data
}


