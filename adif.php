<?php
/**
 * ADIFインポートクラス
 * 
 * ADIFデータを解析して、配列に展開する。
 *
 * PHP versions 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2012, Mune Ando (http://wwww.5cho-me.com/)
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class adif
{

	private $data;						// 処理するADIFデータを格納する。
	private $records = array();			// <EOR>で分割したレコードを格納する。
	private $options = array(			// デフォルトオプション
				'code'	=> 'sjis-win',
				);

/**
 * コンストラクター
 * 
 * @param string $data ADIFデータまたはADIFファイル。ADIFファイルの場合は、拡張子が.adifとなる。
 * @param array $options オプション
 * 					'code' =>　'sjis-win' (デフォルト）
 * 						ADIFデータの文字コード。値長の指定がマルチバイト文字が２バイト固定なので、内部でシフトJISで処理するための措置。
 * 						シフトJISならマルチバイトは２文字、UTF-8ならマルチバイトも１文字にするなど。
 */
	public function __construct($data, $options=array()) {
		
		$this->options = array_merge($this->options, $options);
		
		if(pathinfo($data, PATHINFO_EXTENSION) == 'adif') {
			// ファイルの拡張子がadifであったらファイルからデータを読み込む。
			$this->loadFile($data);
		} else {
			$this->loadData($data);
		}
		
		// データの初期化
		$this->initialize();
		
	}

/**
 * ADIFファイルをそれぞれのレコードに区切って初期化する
 * 
 * <EOH>以降を処理の対象にする
 * ＃以下をコメントにする。
 * 各レコードは<EOR>で区切って配列に格納する。
 */
	public function initialize() {
		
		// ヘッダを無視する。
		$pos = mb_strripos($this->data, '<EOH>');
		if($pos === false) {
			throw new Exception('<EOH>がADFIファイルに存在しません。');
		};
		
		$data = '';
		$i = $pos + 5;
		while($i < mb_strlen($this->data)) {
			
			//　コメントを無視する。
			if(mb_substr($this->data, $i, 1) == "#") {
				while($i < $pos) {
					if(mb_substr($this->data, $i, 1) == "\n") {
						break;
					}
					$i++;
				}
			} else {
				$data = $data . mb_substr($this->data, $i, 1);
			}
			$i++;
		};
		
		$data = str_replace(array("\r\n","\r","\n"), '', $data);
		$data = str_ireplace('<eor>', '<EOR>', $data);
		$this->records = explode('<EOR>', $data);
	}
	
/**
 * ADIFデータとしてストリングを読み込む
 * @param string $data ADIFデータ
 */
	public function loadData($data)
	{
		$this->data = $data;
	}
	
/**
 * ADIFファイルを読み込む。
 * 
 * @param string $fname ADIFファイル名
 */
	public function loadFile($fname)
	{
		$this->data = file_get_contents($fname);
	}

/**
 * ADIFのレコードを解析して、フィールドごとに配列に格納する。
 *
 */
	public function parser() {
		
		$datas = array();
		foreach ($this->records as $record) {
			if(empty($record)) continue;

			$data = array();
			$tag = '';
			$valueLen = '';
			$value = '';
			$status = '';

			$i = 0;
			while( $i < $this->strlen($record) ) {
								
				$ch = $this->substr($record, $i, 1);
				$delimiter = FALSE;
					
				switch ($ch) {
					case '<':
						$tag = '';
						$value = '';
						$status = 'TAG';
						$delimiter = TRUE;
						break;
					case ':':
						if($status == 'TAG') {
							$valueLen = '';
							$status = 'VALUELEN';
							$delimiter = TRUE;
						}
						break;
					case '>':
						if($status == 'VALUELEN') {
							$value = $this->substr($record, $i+1, (int)$valueLen);
							$data[strtoupper($tag)] = $this->convert_encoding($value);
							$i = $i + $valueLen;
							$status = 'VALUE';
							$delimiter = TRUE;
						}
						break;
					default:
				}
				if($delimiter === FALSE) { 
					switch ($status) {
						case 'TAG':
							$tag .= $ch;
							break;
						case 'VALUELEN':
							$valueLen .= $ch;
							break;
					}
				}
				$i = $i + 1;
			}

			$datas[] = $data;
		}
	
		return $datas;
	}
	
/**
 * 文字コードによって文字長を返す。
 * 
 * UFT-8が指定されているときはマルチバイト文字も１文字とし、シフトJISの場合は2文字にする。
 * 
 * @param string $string
 */
	protected function strlen($string) {
		
		if($this->options['code'] == 'sjis-win') {
			return strlen($string);
		} else {
			return mb_strlen($string);
		}
	}
	
/**
 * UTF-8に変換する。
 * 
 * 内部文字コードがシフトJISの場合はUTF-8に変換し、UTF-8の場合はそのまま返す。
 * 
 * @param string $string　変換対象の文字列
 */
	protected function convert_encoding($string) {
	
		if($this->options['code'] == 'sjis-win') {
			return mb_convert_encoding($string, 'utf-8', 'sjis-win');
		} else {
			return $string;
		}
	}

/**
 * 文字列の一部を切り取るが、指定した内部コードによって文字の取り扱いを変える。
 * 
 * @param unknown_type $string 部分文字列を取り出したい文字列。
 * @param unknown_type $start string の中から最初に取り出す文字の位置。
 * @param unknown_type $length string の中から取り出す最大文字数。
 */
	protected function substr($string, $start, $length) {
	
		if($this->options['code'] == 'sjis-win') {
			return substr($string, $start, $length);
		} else {
			return mb_substr($string, $start, $length, 'utf-8');
		}
	}
}
?>