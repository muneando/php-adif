php-adif
======================
アマチュア無線のログの交換で使用されるADIF型式のデータを解析して、PHPの配列に展開するためのクラスライブラリです。
 
 
使い方
------

データを直接パラメータに引き渡す場合：

	$data = <<<FOO
		test test test
		<EOH>
		<TAG1:6>value1<TAG2:7>value23<TAG3:8>value456<EOR>
	FOO;
		$adif = new adif($data);
		$data = $adif->parser();

ADIFファイルを指定する場合：

	$adif = new adif('LOGLIST.adi');
	$data = $adif->parser();

ADIFファイルは、Hamlogから出力されたファイルを元にしています。したがいまして、文字コードはシフトJISで、値長はマルチバイトが２バイトで格納しなければいけません。UTF-8ならマルチバイトの文字でも１バイトで指定してください。
その場合は、

	$adif = new adif('LOGLIST.adi', array('code' => 'utf-8'));

のようにオプションでコードを指定してください。PHPの配列に展開されるとUTF-8に変換されます。

使い方の詳細は、PHPUnitのテストケースファイル（adifTest.php）を参照してください。


サポート
----------
バグがを見つけた場合は、テストケースを追加して、ご報告くださるようご協力さい。


参照
----------

ADIFデータフォーマット仕様　[http://www.adif.org/](http://www.adif.org/)

 
ライセンス
----------
Copyright &copy; 2012 mune ando
Listributed under the [MIT License][mit].
 
[MIT]: http://www.opensource.org/licenses/mit-license.php
