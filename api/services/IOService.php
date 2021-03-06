<?php

class IOService extends BaseService
{

	private $tempDir = "/tmp/";

	public function __construct(&$conn){
		parent::__construct($conn);
	}

	public function process($file){
		$this->validateFile();
		try{
			$fileName = StringUtils::clear($file['name'], true);
			$this->saveFile($file, $fileName );
			$xlsObj = $this->loadExcelFile( $fileName );
			$rows = $this->reedExcelFile( $xlsObj );
			$this->removeFile($fileName);
			return $rows;
		}catch(Exception $ex){
			$logger = Logger::getLogger('api');
			$logger->error('Import of '. $_FILES['xlsFile']['name']. " failed.", $ex);
			throw new InvalidArgumentException(getMessage("errXlsFile"));
		}
	}


	private function saveFile($file, $fileName ){
		move_uploaded_file($file["tmp_name"], $this->getSaveDir(). $fileName);
	}

	private function removeFile($fileName){
		unlink($this->getSaveDir(). $fileName);
	}

	private function loadExcelFile($fileName){
		$inputFileType = PHPExcel_IOFactory::identify($this->getSaveDir().$fileName);
	    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
	    return $objReader->load( $this->getSaveDir().$fileName );
	}


	private function reedExcelFile($xlsFile){
		$sheet = $xlsFile->getSheet(0);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$rows = array();
		for ($row = 0; $row <= $highestRow; $row++){
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
		    if(!empty($rowData[0][0]) && !empty($rowData[0][1])){
		    	$rows[] = array( "question" => $rowData[0][0], "answer" => $rowData[0][1] );
		    }
		}
		return $rows;
	}


	private function getSaveDir(){
		return dirname(dirname(__FILE__)).$this->tempDir;
	}

	private function validateFile(){
		if(!isset($_FILES['file'])){
			throw new InvalidArgumentException(getMessage("errXlsFile"));
		}
		$filename = basename($_FILES['file']['name']);
		$ext = substr($filename, strrpos($filename, '.') + 1);
		$mimes = array(
		    	"application/vnd.ms-excel",
		    	"application/msexcel",
		    	"application/x-msexcel",
		    	"application/x-ms-excel",
		    	"application/x-excel",
		    	"application/x-dos_ms_excel",
		    	"application/xls",
					"application/vnd.ms-excel.sheet.macroEnabled.main+xml",
		    	"application/x-xls"
		);
		$mime = $_FILES["file"]["type"];
		if (($ext != "xls" && $ext != "xlsx") ||
			(!in_array($mime, $mimes) && !notStartsWith("application/vnd.openxmlformats", $mime)) ) {
			throw new InvalidArgumentException(getMessage("errXlsFile"));
		}
	}

		private function startsWith($haystack, $needle) {
	    return strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

}

function notStartsWith($haystack, $needle){
     $length = strlen($needle);
     return !(substr($haystack, 0, $length) === $needle);
}


?>
