<?php

namespace App\Apis\Rechtspraak;

use SoapVar;
use stdClass;
use SoapClient;
use SoapHeader;

class CurateleEnBewindregisterApi
{
	/** @var SoapClient */
	protected $soap;

	/** @var string */
	protected $wsdl = 'https://ccbrservice.rechtspraak.nl/ccbrdataservice.svc?wsdl';

	/** @var RechtspraakApi */
	protected $tokenApi;

	public function __construct()
	{
		$this->tokenApi = new RechtspraakApi;

		$this->soap = new SoapClient($this->wsdl, [
			'uri' => 'ccbr.rechtspraak.nl/v1',
			'location' => 'https://ccbrservice.rechtspraak.nl/ccbrdataservice.svc',
			'soap_version' => SOAP_1_2,
			'exceptions' => false,
			'trace' => true,
			'cache_wsdl' => WSDL_CACHE_MEMORY,
			'stream_context' => stream_context_create([
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true,
				],
			]),
		]);
	}

	/**
	 * Try to find a client based on the name / birthday
	 *
	 * @param string $name
	 * @param string $birthday
	 * @return mixed
	 * @throws \SoapFault
	 */
	public function search($name, $birthday)
	{
		$auth = new stdClass();
		$auth->Security = new SoapVar($this->tokenApi->token(), XSD_ANYXML, null, null, null, null);

		$security = new SoapVar($auth, SOAP_ENC_OBJECT, null, null, null, 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');

		$this->soap->__setSoapHeaders([
			new SoapHeader('http://www.w3.org/2005/08/addressing', 'Action', 'ccbr.rechtspraak.nl/v1/CcbrDataservice/ZoekRegisterkaarten'),
			new SoapHeader('http://www.w3.org/2005/08/addressing', 'To', 'https://ccbrservice.rechtspraak.nl/ccbrdataservice.svc'),
			new SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', $security),
		]);

		return $this->soap->ZoekRegisterkaarten(
			new SoapVar(new ZoekRegisterkaartenSoapObject($name, $birthday), XSD_ANYXML, null, null, null, null)
		);
	}

	/**
	 * Try to find the detials based on the Registerkaart Aanduiding
	 *
	 * @param $RegisterkaartAanduiding
	 * @return mixed
	 * @throws \SoapFault
	 */
	public function registerkaart($RegisterkaartAanduiding)
	{
		$auth = new stdClass();
		$auth->Security = new SoapVar($this->tokenApi->token(), XSD_ANYXML, null, null, null, null);

		$security = new SoapVar($auth, SOAP_ENC_OBJECT, null, null, null, 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');

		$this->soap->__setSoapHeaders([
			new SoapHeader('http://www.w3.org/2005/08/addressing', 'Action', 'ccbr.rechtspraak.nl/v1/CcbrDataservice/RaadpleegRegisterkaart'),
			new SoapHeader('http://www.w3.org/2005/08/addressing', 'To', 'https://ccbrservice.rechtspraak.nl/ccbrdataservice.svc'),
			new SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', $security),
		]);

		$xml = '<RaadpleegRegisterkaart xmlns="ccbr.rechtspraak.nl/v1"><registerkaartAanduiding>' . $RegisterkaartAanduiding . '</registerkaartAanduiding></RaadpleegRegisterkaart>';

		$ZoekRegisterkaarten = new SoapVar($xml, XSD_ANYXML, null, null, null, null);

		return $this->soap->RaadpleegRegisterkaart($ZoekRegisterkaarten);
	}
}
