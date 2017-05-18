<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BraveAmazon
 *
 * @author leo
 */
class BraveAmazon extends Brave {
	var $locale = 'com';
	var $hosts = array(
		'ca' => 'ecs.amazonaws.ca',
		'cn' => 'webservices.amazon.cn',
		'de' => 'ecs.amazonaws.de',
		'es' => 'webservices.amazon.es',
		'fr' => 'ecs.amazonaws.fr',
		'it' => 'webservices.amazon.it',
		'jp' => 'ecs.amazonaws.jp',
		'uk' => 'ecs.amazonaws.co.uk',
		'us' => 'webservices.amazon.com',
		'com' => 'webservices.amazon.com',
	);
	function setLocale($locale) {
		$locale ? $this->locale = $locale : "jp";
	}
	
	function lookupSimilarityItem($asin) {
		$params = array(
			'Operation' => 'SimilarityLookup',
			'ItemId' => $asin,
			'ResponseGroup' => 'Small',
			'AssociateTag' => AWS_ASSOCIATE_TAG,
			'SimilarityType' => 'Intersection',
		);
		$response = $this->aws_signed_request(
				$this->locale, $params, AWS_API_KEY, AWS_API_SECRET_KEY
		);
		return $response;
	}
	
	function lookupItem($itemId, $idType = 'ISBN'){
		$idType = strtoupper($idType);
		$idType = preg_replace('/-[\d]+/','',$idType);
		$params = array(
			'Operation' => 'ItemLookup',
			'ItemId' => $itemId,
			'IdType' => $idType,
			'ResponseGroup' => 'Small,Offers',
			'AssociateTag' => AWS_ASSOCIATE_TAG,
		);
		if (in_array($idType,array('DPCI', 'SKU','UPC', 'EAN','ISBN'))) {
			$params['SearchIndex'] = 'All';
		}
		$response = $this->aws_signed_request(
				$this->locale, $params, AWS_API_KEY, AWS_API_SECRET_KEY
		);
		return $response;
	}
	
	function aws_signed_request($region, $params, $public_key, $private_key) {
		/*
		  Copyright (c) 2009 Ulrich Mierendorff

		  Permission is hereby granted, free of charge, to any person obtaining a
		  copy of this software and associated documentation files (the "Software"),
		  to deal in the Software without restriction, including without limitation
		  the rights to use, copy, modify, merge, publish, distribute, sublicense,
		  and/or sell copies of the Software, and to permit persons to whom the
		  Software is furnished to do so, subject to the following conditions:

		  The above copyright notice and this permission notice shall be included in
		  all copies or substantial portions of the Software.

		  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
		  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
		  DEALINGS IN THE SOFTWARE.
		 */

		/*
		  Parameters:
		  $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
		  $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
		  "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
		  $public_key - your "Access Key ID"
		  $private_key - your "Secret Access Key"
		 */
		
		$host = $this->hosts[$this->locale];
		if (!$host) {
			return array();
		}
		
		// some paramters
		$method = "GET";
		$uri = "/onca/xml";

		// additional parameters
		$params["Service"] = "AWSECommerceService";
		$params["AWSAccessKeyId"] = $public_key;
		// GMT timestamp
		$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
		// API version
		$params["Version"] = "2011-08-01";

		// sort the parameters
		ksort($params);

		// create the canonicalized query
		$canonicalized_query = array();
		foreach ($params as $param => $value) {
			$param = str_replace("%7E", "~", rawurlencode($param));
			$value = str_replace("%7E", "~", rawurlencode($value));
			$canonicalized_query[] = $param . "=" . $value;
		}
		$canonicalized_query = implode("&", $canonicalized_query);

		// create the string to sign
		$string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;

		// calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, true));

		// encode the signature for the request
		$signature = str_replace("%7E", "~", rawurlencode($signature));

		// create request
		$request = "http://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;

		// do request
		$response = file_get_contents($request);
		
		if ($response === false) {
			return array();
		} else {
			$xml = $this->load(EXTEND, 'BraveXml');
			return $xml->xmlToArray($response);
		}
	}

}

?>
