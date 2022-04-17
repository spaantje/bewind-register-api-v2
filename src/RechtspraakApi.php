<?php

namespace SpaanProductions\Rechtspraak;

use SoapVar;
use stdClass;
use SoapFault;
use SoapClient;
use SoapHeader;
use Carbon\Carbon;

class RechtspraakApi
{
	/**
	 * Retrieve the token from Rechtspraak
	 *
	 * @return mixed
	 * @throws SoapFault
	 */
	public function token()
	{
		$wsdl = 'https://sts.rechtspraak.nl/adfs/services/trust/mex';
		$username = $_ENV['RECHTSPRAAK_USERNAME'];
		$password = $_ENV['RECHTSPRAAK_PASSWORD'];

		// Try to get it from the cache so we don't have to request it multiple times..
		// Todo: We should cache the token.

		$client = new SoapClient($wsdl, [
			'soap_version' => SOAP_1_2,
			'exceptions' => true,
			'trace' => true,
			'cache_wsdl' => WSDL_CACHE_NONE,
		]);

		$wrapper = new stdClass();
		$wrapper->Username = new SoapVar($username, XSD_STRING, null, null, null, "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
		$wrapper->Password = new SoapVar($password, XSD_STRING, null, null, null, "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");

		$auth = new stdClass();
		$auth->UsernameToken = new SoapVar($wrapper, SOAP_ENC_OBJECT, null, null, null, "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");

		$client->__setSoapHeaders([
			new SoapHeader('http://www.w3.org/2005/08/addressing', 'Action', 'http://docs.oasis-open.org/ws-sx/ws-trust/200512/RST/Issue'),
			new SoapHeader('http://www.w3.org/2005/08/addressing', 'To', 'https://sts.rechtspraak.nl/adfs/services/trust/13/usernamemixed'),
			new SoapHeader('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 'Security', $auth),
		]);

		$EndpointReference = new stdClass();
		$EndpointReference->Address = new SoapVar('https://curateleenbewindregisterservice.rechtspraak.nl/', XSD_STRING, null, null, null, "http://www.w3.org/2005/08/addressing");

		$AppliesTo = new stdClass();
		$AppliesTo->EndpointReference = new SoapVar($EndpointReference, SOAP_ENC_OBJECT, null, null, null, "http://www.w3.org/2005/08/addressing");

		$RSTRequest = new stdClass();
		$RSTRequest->AppliesTo = new SoapVar($AppliesTo, SOAP_ENC_OBJECT, null, null, null, "http://schemas.xmlsoap.org/ws/2004/09/policy");
		$RSTRequest->KeyType = new SoapVar('http://docs.oasis-open.org/ws-sx/ws-trust/200512/Bearer', XSD_STRING, null, null, null, "http://docs.oasis-open.org/ws-sx/ws-trust/200512");
		$RSTRequest->RequestType = new SoapVar('http://docs.oasis-open.org/ws-sx/ws-trust/200512/Issue', XSD_STRING, null, null, null, "http://docs.oasis-open.org/ws-sx/ws-trust/200512");

		$RequestSecurityToken = new SoapVar($RSTRequest, SOAP_ENC_OBJECT, null, null, null, "http://docs.oasis-open.org/ws-sx/ws-trust/200512");

		$response = $client->__soapCall('Trust13IssueAsync', ['RequestSecurityToken' => $RequestSecurityToken], [
			'location' => 'https://sts.rechtspraak.nl/adfs/services/trust/13/usernamemixed',
		]);

		$token = $response->RequestSecurityTokenResponse->any;

		// ToDo: Clean this up!

		$expresion = '/<wsu:Expires .*>(.*)<\/wsu:Expires>/m';
		preg_match($expresion, (string)$token, $matches);

		$expires = Carbon::parse($matches[1], 'Zulu')->setTimezone('Europe/Amsterdam');

		$re = '/<trust:RequestedSecurityToken>(.*)<\/trust:RequestedSecurityToken>/m';
		preg_match($re, $token, $matches);

		return $matches[1];
	}
}
