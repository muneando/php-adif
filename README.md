php-adif
======================
This software is a class library that analyzes the data of the type used in the exchange of ADIF log of amateur radio, to deploy an array of PHP.


Usage
------

When passing parameters directly to the data:

	$data = <<<FOO
		test test test
		<EOH>
		<TAG1:6>value1<TAG2:7>value23<TAG3:8>value456<EOR>
	FOO;
		$adif = new adif($data);
		$data = $adif->parser();

ADIF file to specify:

	$adif = new adif('LOGLIST.adi');
	$data = $adif->parser();

ADIF files are based on the file that is output from the Hamlog. Accordingly, the character code in the Shift-JIS, length value should be stored with a 2-byte multi-byte. Please specify in one byte in a multi-byte character if UTF-8.
In that case,

	$adif = new adif('LOGLIST.adi', array('code' => 'utf-8'));

Please specify the option code as. Will be converted to UTF-8 and will be deployed in an array of PHP.

Details on how to use these functions, please refer to the (adifTest.php) test case files of PHPUnit.


Support
----------
If you find any bugs, add a test case, please report it.


Reference
----------

ADIF data format specification　[http://www.adif.org/](http://www.adif.org/)

 
License
----------
Copyright &copy; 2012 mune ando
Listributed under the [MIT License][mit].
 
[MIT]: http://www.opensource.org/licenses/mit-license.php
