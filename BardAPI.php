<?php

require (dirname(__FILE__).'/API.php');

class BardAPI extends API {

	protected $DB_HOSTNAME = "localhost";
	protected $DB_USERNAME = "root";
	protected $DB_PASSWORD = "itygfmtad";
	protected $DB_DBNAME = "CraigslistPoetry";

	// DEV VARS
	/*
	protected $DB_HOSTNAME = "db428352704.db.1and1.com";
	protected $DB_USERNAME = "dbo428352704";
	protected $DB_PASSWORD = "itygfmtad";
	protected $DB_DBNAME = "db428352704";
	*/
	
	// LIVE VARS
	/*
	protected $DB_HOSTNAME = "db407045506.db.1and1.com";
	protected $DB_USERNAME = "dbo407045506";
	protected $DB_PASSWORD = "testtesttest";
	protected $DB_DBNAME = "db407045506";
	*/
	
	const MAX_NUM_LINES = 10;

	public function getLinesBetween($minId, $maxId) {
		return $this->getTable("poetry_lines", NULL, "line_id>$minId && line_id<$maxId");
	}

/****** HAIKU WORK *******
	public function updateSyllableCount($line_id, $syllable_count) {
		return $this->update("poetry_lines", "syllable_count=$syllable_count", "line_id=$line_id");
	}
	public function getHaiku() {
		$haiku = array();

		$lines_5 = $this->getTable("poetry_lines", "rand()", "syllable_count=5", 2);
		$line_7 = $this->getTable("poetry_lines", "rand()", "syllable_count=7", 1);

		array_push($haiku, $lines_5[0], $line_7[0], $lines_5[1]);

		return $haiku;
	}
*******/
	public function getLineWithId($id=-1) {
		if ($id == -1) return NULL;

		$data = $this->getTable("poetry_lines", NULL, "line_id=$id");
		
		return $data[0];
	}
	public function getPoemWithId($id=-1) {
		if ($id == -1) return NULL;
		$poem = NULL; 

		$query = "SELECT line_text FROM poems left join poetry_lines using(line_id) where poem_id=$id"; 
		$data = $this->select($query);

		if ($data != NULL) {
			$poem = $this->generatePoemArray($id, $data);
			/*
			$poem = array();

			$poem["id"] = $id;
			for ($i=0; $i<count($data); $i++)
				$poem["line"][$i] = $data[$i]["line_text"];
			*/
		}
		return $poem;
	}
	private function generatePoemArray($id, $poem) {
		$poemArray = array();
		$poemArray["id"] = $id;
		for ($i=0;$i<count($poem); $i++)
			$poemArray["line"][$i] = $poem[$i]["line_text"];

		return $poemArray;
	}
	public function getNewPoem() {
		$poem = $this->getTable("poetry_lines", "rand()", "count=0", 10);
		$poemArray = NULL;

		if (count($poem) != self::MAX_NUM_LINES) {
			$numToGet = self::MAX_NUM_LINES - count($poem);
			$moreLines = $this->getTable("poetry_lines", "rand()", "count!=0", $numToGet);
			for ($i=0;$i<count($moreLines);$i++) array_push($poem, $moreLines[$i]);
		}

		$poem_id = $this->storePoem($poem);

		if ($poem_id != NULL)
			$poemArray = $this->generatePoemArray($poem_id, $poem);

		return $poemArray;
	}
	private function storePoem($poem) {
		$poem_id = $this->getLastPoemId() + 1;

		$success = true;
		for ($i=0;$i<count($poem);$i++) {
			$line_id = $poem[$i]["line_id"];

			$success = $success && $this->update("poetry_lines", "count=count+1", "line_id=$line_id");
			$success = $success && $this->insert("poems", "(poem_id, line_id)", "($poem_id, $line_id)");
		}
		return ($success) ? $poem_id : NULL;
	}
	public function getLastPoemId() {
		$query = 'SELECT max(poem_id) FROM poems';
		$result = $this->select($query);
		
		return $result[0]["max(poem_id)"];
	}
}
?>
