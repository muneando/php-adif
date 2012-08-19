<?php
class adif
{

	private $data; //the adif data
	private $records = array(); 

/**
 * ADIFファイルをそれぞれのレコードに区切って初期化する
 * 
 * <EOH>以降を処理の対象にする
 * ＃以下をコメントにする。
 * 各レコードは<EOR>で区切って配列に格納する。
 */
	public function initialize() {
		$pos = mb_strripos($this->data, '<EOH>');
		if($pos === false) {
			throw new Exception('<EOH>がADFIファイルに存在しません。');
		};
		
		$data = '';
		$i = $pos + 5;
		while($i < mb_strlen($this->data)) {
			//skip comments
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
	
	public function load($data) //allows the parser to be fed a string
	{
		$this->data = $data;
	}
	
	public function load_from_file($fname) //allows the user to accept a filename as input
	{
		$this->data = mb_convert_encoding(file_get_contents($fname), 'utf-8', 'sjis-win');
	}
	
/**
 * ADIFのレコードを解析して、フィールドごとに格納する。
 * 
 * @todo 値の中に<>:が混入したら、ADIFの仕様がマルチバイト文字に対応していないため解析が難しい。
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
			while( $i < strlen($record) ) {
								
				$ch = substr($record, $i, 1);
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
							$value = substr($record, $i+1, (int)$valueLen);
							$data[strtoupper($tag)] = mb_convert_encoding($value, 'utf-8', 'sjis-win');
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
}
?>