<?php

/**
* internal library of functions and constants for Poodll modules
* accessed directly by poodll flash widgets on web pages.
* @package mod-poodllpairwork
* @category mod
* @author Justin Hunt
*
*/


/**
* Includes and requires
*/
//relative path is dangerous, so only use it if we have no $CFG already Justin 20120424
if(!isset($CFG)){
require_once("../../config.php");
}
require_once($CFG->dirroot . '/filter/poodll/poodllinit.php');
//commented just while getting other mods working
//require_once('../mod/poodllpairwork/locallib.php');
require_once($CFG->dirroot . '/filter/poodll/poodllresourcelib.php');
//added for moodle 2
require_once($CFG->libdir . '/filelib.php');

	$datatype = optional_param('datatype', "", PARAM_TEXT);    // Type of action/data we are requesting
	$courseid  = optional_param('courseid', 0, PARAM_INT);  // the id of the course 
	$moduleid  = optional_param('moduleid', 0, PARAM_INT);  // the id of the module 
	$hash  = optional_param('hash', "", PARAM_TEXT);  // file or dir hash
	$requestid  = optional_param('requestid', "", PARAM_TEXT);  // file or dir hash
	$paramone  = optional_param('paramone', "", PARAM_TEXT);  // nature of value depends on datatype, maybe path
	$paramtwo  = optional_param('paramtwo', "", PARAM_TEXT);  // nature of value depends on datatype, maybe protocol
	$paramthree  = optional_param('paramthree', "", PARAM_TEXT);  // nature of value depends on datatype, maybe filearea
	$paramfour  = optional_param('paramfour', "", PARAM_TEXT);  // nature of value depends on datatype, maybe filearea

	switch($datatype){

		case "xmlpairs": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml = fetch_xmlpairs($courseid);
			break;
		case "unassignedusers": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml = fetch_unassigned_users(5,$courseid,null);
			break;
		case "offlineusers": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml="";
			break;

		case "courseusers": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_course_users($courseid);
			break;

		case "coursemenu": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_course_menu($courseid);
			break;
			
		case "poodllcastjnlp": 
			header("Content-type: application/x-java-jnlp-file");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_poodllcastdata_jnlp($courseid);
			break;	
			
		case "poodllcastapplet": 
			header("Content-type: text/html");
			$returnxml=fetch_poodllcastdata_applet($courseid);
			break;		
		
		case "poodllcastjnlpapplet": 
			header("Content-type: text/html");
			$returnxml=fetch_poodllcastdata_jnlpapplet($courseid);
			break;
			
		case "oldpoodllaudiolist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_oldpoodllaudiolist($courseid, $paramone, $paramtwo);
			break;

		case "poodllmedialist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			if($paramthree=="poodlldata"){
				$returnxml=fetch_poodllmedialist_poodlldata($courseid, $paramone, $paramtwo);
			}else{
				$returnxml=fetch_poodllmedialist($moduleid, $courseid, $paramone, $paramtwo, $paramthree);
			}
			break;
		
		case "poodllrsslist": 
			header("Content-type: application/rss+xml");
			echo "<rss version=\"2.0\" 
				xmlns:media=\"http://search.yahoo.com/mrss/\"
				xmlns:fp=\"http://flowplayer.org/fprss/\">";
			//moduleid/courseid/path/playerype/filearea
			$returnxml=fetch_poodllaudiolist($moduleid, $courseid, $paramone, $paramtwo, $paramthree,"rss",$paramfour=="true");
			$returnxml .="</rss>";
			break;
			
		case "poodllaudiolist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//moduleid/courseid/path/playerype/filearea
			$returnxml=fetch_poodllaudiolist($moduleid, $courseid, $paramone, $paramtwo, $paramthree,"xml");
			break;
			
		case "poodllflashcards": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			//courseid, cardset id,cardset name, fgcolor, bgcolor 
			$returnxml=fetch_poodllflashcards($courseid, $paramone,$paramtwo,$paramthree,$paramfour);
			break;	
			
		case "poodllflashcardsconvert": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_poodllflashcardsconvert($courseid, $paramone);
			break;		
			
		case "dirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_dirlist($courseid, $paramone);
			break;

		case "repodirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_repodirlist($paramone);
			break;	
			
		case "instancedirlist": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=fetch_instancedirlist($moduleid, $courseid, $paramone, $paramtwo);
			break;
				
		case "instancedeleteall": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_deleteall($moduleid, $courseid, $paramone, $requestid);
			break;
			
		case "instancecopyfile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_copyfilein($moduleid, $courseid, $paramone, $paramtwo,$paramthree, $requestid);
			break;
		
		case "instancedeletefile": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_deletefile($hash, $requestid);
			break;
			
		case "instancecreatedir": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_createdir($moduleid, $courseid, $paramone, $paramtwo, $requestid);
			break;

		case "instancecopydir": 
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml=instance_copydirin($moduleid, $courseid, $paramone, $paramtwo, $paramthree, $requestid);
			break;
			
		default:
			header("Content-type: text/xml");
			echo "<?xml version=\"1.0\"?>\n";
			$returnxml="";
			break;	
		

	}


	echo $returnxml;
	return;


//Fetch the users for this course 
//later we should distinguish teachers and students maybe
function fetch_course_users($courseid){
global $CFG,$DB;

	
	//fetch user objects for all users in course
	$coursecontext = get_context_instance(CONTEXT_COURSE, $courseid);	
	
	//get course and require requester is logged in
	if (! $course = $DB->get_record('course', array('id' => $courseid))) {
		print_error('invalidcourseid');
	}
	require_course_login($course);
	
	//'u.id, u.username, u.firstname, u.lastname, u.picture'
	$users = get_users_by_capability($coursecontext, 'moodle/user:viewdetails');

	//usersummary variable
	$usersummary ="";
	
	//set up xml to return
	$xml_output = "<pairsets>\n";
	$pairstreamname =  rand(1000000, 9999999);
	$xml_output .= "\t<pair name='$pairstreamname' dirty='false'>\n";

	//fill with user "pairelements"
	foreach ($users as $user) {
	             $xml_output .= "\t\t<pairelement username='" . $user->username. 
									"' showname='" .  fullname($user) .
									"' pictureurl='" . fetch_user_picture($user,120) . 
									"' />\n";
				if ($usersummary == ""){
						$usersummary .=  $user->username;					
				}else{
						$usersummary .= ";" . $user->username;					
				}
	}
	//close our list of users in the pairset
	$xml_output .= "\t</pair>\n";

	//We also provide a summary of all the users in this course
	//so that the laszlo client doesn't need to xml divine it
	$xml_output .= "\t<usersummary>\n";
	$xml_output .= $usersummary;
	$xml_output .= "\t</usersummary>\n";
	$xml_output .= "</pairsets>";

	//Return the data
	return $xml_output;
}

//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_poodllcastdata_applet($courseid){
	global $CFG;	
	$baseUrl = $CFG->wwwroot . '/filter/poodll/poodllcast';
	$red5 = $CFG->filter_poodll_servername;
	$app= $CFG->filter_poodll_serverid . "/" . $courseid . "/screencast01";
	$broadcastkey= "1234567";
	$port= $CFG->filter_poodll_serverport;
	$backcolor="AAAAFF";

	
	
	
	
	$html_output =  "<HTML>\n<HEAD>\n<TITLE>Poodllcast</TITLE>\n</HEAD>\n<BODY bgcolor='#$backcolor'>\n";
	$html_output .= "<APPLET CODE='org.redfire.screen.ScreencastApplet.class' CODEBASE='$baseUrl' ARCHIVE='poodllcast.jar' WIDTH='300' HEIGHT='380'>";
    $html_output .= "<PARAM NAME='host'    VALUE='$red5'>";
    $html_output .= "<PARAM NAME='app'    VALUE='$app'>";
    $html_output .= "<PARAM NAME='port'    VALUE='$port'>";
    $html_output .= "<PARAM NAME='publishname'  VALUE='$broadcastkey'>";
    $html_output .= "<PARAM NAME='backcolor'  VALUE='0x$backcolor'>";
	$html_output .= "</APPLET> ";
	
	
	$html_output .="\n</BODY>\n</HTML>";
	
	
	
	
	//Return the data
	return $html_output;


}

//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_poodllcastdata_jnlpapplet($courseid){
	global $CFG;	
	$baseUrl = $CFG->wwwroot;
	$red5 = $CFG->filter_poodll_servername;
	$app= $CFG->filter_poodll_serverid . "/" . $courseid . "/screencast01";
	$broadcastkey= "poodllcast";
	$port= $CFG->filter_poodll_serverport;
	$backcolor="AAAAFF";

	
	
	
	
	$html_output =  "<HTML>\n<HEAD>\n<TITLE>Poodllcast</TITLE>\n</HEAD>\n<BODY bgcolor='#$backcolor'>\n";
	$html_output .= "<APPLET CODE='org.redfire.screen.ScreencastApplet.class' WIDTH='300' HEIGHT='380'>";
    $html_output .= "<PARAM NAME='jnlp_href'    VALUE='" .$baseUrl . "/filter/poodll/poodlllogiclib.php?datatype=poodllcastjnlp&courseid=" .$courseid . "'>";
	$html_output .= "</APPLET> ";
	
	
	$html_output .="\n</BODY>\n</HTML>";
	
	
	
	
	//Return the data
	return $html_output;


}

//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_poodllcastdata_jnlp($courseid){
	global $CFG;	
	$baseUrl = $CFG->{'wwwroot'};
	$red5 = $CFG->filter_poodll_servername;
	$app= $CFG->filter_poodll_serverid . "/" . $courseid . "/screencast01";
	$broadcastkey= "poodllcast";
	$port= $CFG->filter_poodll_serverport;
	$backcolor="AAAAFF";

	


	$html_output =  "<jnlp spec='1.0+' codebase='$baseUrl' href='filter/poodll/poodlllogiclib.php?datatype=poodllcastjnlp&courseid=$courseid'> ";
	$html_output .= "<information> 
			<title>Poodll Screencast</title> 
			<vendor>Justin Hunt</vendor> 
			<homepage>http://www.poodll.com</homepage>
			<description>PoodLL Screencast</description> 
			<description kind='short'>Screencasting in PoodLL, based on the open source Red5-Screenshare, available at http://code.google.com/p/red5-screenshare/</description> 
			<offline-allowed/> 
			</information>
			<security>
			    <all-permissions/>
			</security>";	

	$html_output .= "<resources> <j2se version='1.6+'/>"; 
	$html_output .= "<jar href='" .$baseUrl . "/filter/poodll/poodllcast/poodllcast.jar'/>"; 			
	$html_output .= "</resources> ";
	$html_output .= "<applet-desc main-class='org.redfire.screen.ScreencastApplet' name='poodllcast' width='300' height='380'>";
    $html_output .= "<param name='host'    value='$red5'>";
    $html_output .= "<param name='app'    value='$app'>";
    $html_output .= "<param name='port'    value='$port'>";
    $html_output .= "<param name='publishname'  value='$broadcastkey'>";
    $html_output .= "<param name='backcolor'  value='0x$backcolor'>";
	$html_output .= "</applet-desc> ";
	
	
	$html_output .="\n</jnlp>";
	
	
	
	
	//Return the data
	return $html_output;


}


//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_course_menu($courseid){

	//set up xml to return
	$xml_output = "<coursemenudata>\n";
	$xml_output .= "\t<listitems>\n";

	//fill with list with items, for testing
//	$xml_output .= "\t<item label='one' url='1' />\n";
//	$xml_output .= "\t<item label='two' url='2' />\n";
//	$xml_output .= "\t<item label='three' url='3' />\n";

	//fill list with db items
	$xml_output .= fetchcourseitems($courseid);

	//close xml to return
	$xml_output .= "\t</listitems>\n";
	$xml_output .= "</coursemenudata>";

	//Return the data
	return $xml_output;


}


//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_poodllmedialist($moduleid, $courseid, $path="/", $playertype, $filearea){
global $CFG, $DB;	

	
	//FIlter could submit submission/draft/content/intro as options here
	if($filearea == "") {$filearea ="content";}
	
	//fetch info and ids about the module calling this data
	$course = $DB->get_record('course', array('id'=>$courseid));
	$modinfo = get_fast_modinfo($course);
	$cm = $modinfo->get_cm($moduleid);
	
	//make sure we have a trailing slash and leading slash
	if(strlen($path)>0){
		if(substr($path,-1) !='/'){
			$path .= "/";
		}
		if(substr($path,0,1) !='/'){
			$path = "/" . $path;
		}
	}else{
		$path = "/";
	}

	//set up xml to return	
	$xml_output = "<videos>\n";
	
	//get filehandling objects
	$browser = get_file_browser();
	$fs = get_file_storage();

	//get a handle on the module context
	$thiscontext = get_context_instance(CONTEXT_MODULE,$moduleid);
	$contextid = $thiscontext->id;
	
	//fetch a list of files in this area, and sort them alphabetically
	$files = $fs->get_area_files($contextid, "mod_" . $cm->modname, $filearea);
	usort($files, "cmpFilenames");

	//loop through all the media files and load'em up	
		foreach ($files as $f) {
			$filename =trim($f->get_filename());
			//if we are not a directory and filename is long enough and extension is mp3 or flv or mp4, we proceed
			if ($filename != "."){
				if(strlen($filename)>4){
					$ext = substr($filename,-4);
					if($ext==".mp3" || $ext==".mp4" || $ext==".flv"){
						//fetch our info object
						$fileinfo = $browser->get_file_info($thiscontext, $f->get_component(),$f->get_filearea(), $f->get_itemid(), $f->get_filepath(), $f->get_filename());
						
						//if we are at the dir level
						if($f->get_filepath()==$path){
							//get the url to the file and add it to the XML
							$urltofile = $fileinfo->get_url();
							$xml_output .=  "\t<video videoname='" . basename($filename) ."' playertype='" . $playertype . "' url='" . trim($urltofile) . "'/>\n";
						}
					}
				}
			}
		}





	//close xml to return
	$xml_output .= "</videos>";

	//Return the data
	return $xml_output;


}





//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_poodllmedialist_poodlldata($courseid, $path, $playertype){
global $CFG;	
	
	//work out our directory structure
	$baseDir = $CFG->{'dirroot'} . "/" . $CFG->{'filter_poodll_datadir'}  . "/" . $path;
	$baseURL = $CFG->{'wwwroot'} . "/" . $CFG->{'filter_poodll_datadir'}  . "/" . $path . "/";

	
	//set up xml to return
	$xml_output = "<videos>\n";
	//If protocol is yutu, look for .yutu files	
	if($playertype == "yutu"){
		foreach (glob($baseDir . "/*.yutu") as $filename) {
			$xml_output .=  "\t<video videoname='" . basename($filename) ."' playertype='" . $playertype . "' url='" . substr(basename($filename),0,11) . "'/>\n";
		}
		
	//default protocol is http, which can play back mp4 or flv	
	}else{		
		foreach (glob($baseDir . "/*.{flv,mp4}",GLOB_BRACE) as $filename) {
			$xml_output .=  "\t<video videoname='" . basename($filename) ."' playertype='" . $playertype . "' url='" . $baseURL . basename($filename). "'/>\n";
		}
	}

	//close xml to return
	$xml_output .= "</videos>";

	//Return the data
	return $xml_output;


}

function cmpFilenames($a, $b)
{
    return strcasecmp($a->get_filename(), $b->get_filename());
}

function cmpDirnames($a, $b)
{
    return strcasecmp(basename($a['dirfile']->get_filepath(),"/"), basename($b['dirfile']->get_filepath(),"/"));
}


//This code fetch_poodllaudiolist, was moved into PoodLLResourceLib, because it was needed there too, and including
//poodlllogiclib.php there had the effect of running the top logic (ala exporting xml headers)
//which broke other things
//function_poodllaudiolist();

//Fetch the menu (assignments/resources/quizzes) for this course 
function fetch_oldpoodllaudiolist($courseid, $path, $playertype){
global $CFG;	
	//Handle directories
	$baseDir = $CFG->{'dataroot'} . "/" . $courseid . "/" . $path;
	

	//set up xml to return	
	$xml_output = "<audios>\n";

	$mediapath=$courseid . "/" . $path . "/";
	if ($playertype == "http"){
		$mediapath= $CFG->{'wwwroot'} . "/file.php/" . $mediapath;		
	}

	//if it is a path to a single file, return that as the only audio element in the audio xml file
	//otherwise loop through directory getting all audio files.
	
	
	if(strlen($path) > 4 && (substr($path,-4)==".mp3" || substr($path,-4)==".flv" || substr($path,-4)==".mp4") ){
		$xml_output .=  "\t<audio audioname='" . basename($mediapath) ."' playertype='" . $playertype . "' url='" . $mediapath . "'/>\n";
	}else{

		//If protocol is rtmp, look for flv or mp3		
		if($playertype == "rtmp"){
			$filterstring="/*.{mp3,flv}";

		//default protocol is http, which can play back mp4 or flv	
		}else{		
			$filterstring="/*.{flv,mp4}";
		}
		
		
		foreach (glob($baseDir . $filterstring,GLOB_BRACE) as $filename) {
			$xml_output .=  "\t<audio audioname='" . basename($filename) ."' playertype='" . $playertype . "' url='" . $mediapath . basename($filename). "'/>\n";
		}
	
	}

	//close xml to return
	$xml_output .= "</audios>";

	//Return the data
	return $xml_output;


}


//Fetch a sub directory list for file explorer  
//calls itself recursively, dangerous
function fetch_dir_contents($dir,  $recursive=false){
	$xml_output="";
	$files = scandir($dir);
	if (!empty($files)) {
        foreach ($files as $afile) {
			if ($afile == "." || $afile == "..") {
				continue;
			}
			//here we encode the filename 
			//because the xml breaks otherwise when there arequotes etc.
			$escapedafile =  htmlspecialchars( $afile,ENT_QUOTES);
			if(is_dir($dir."/".$afile)){
				if(!$recursive){
					$xml_output .=  "\t<directory name='" . $escapedafile . "' />\n";
				}else{				
					//recursive
					$xml_output .=  "\t<directory name='" . $escapedafile . "' >\n";
					$xml_output .= fetch_dir_contents($dir."/".$afile,true);	
					$xml_output .=  "\t</directory>";
				}				
			}else{
				$xml_output .=  "\t<file name='" . $escapedafile . "' isleaf='true' />\n";
			}
		}
	}
	return $xml_output;
}



//Fetch a directory list for file explorer 
//this is for the old 1.9 moodle data directory, probably only useful as reference now 
function fetch_dirlist($courseid, $startpath=''){
global $CFG;	
	

	global $basedir;
    global $usecheckboxes;
    global $id;
    global $USER, $CFG;

	//Handle directories
	$fullpath = $CFG->{'dataroot'} . "/" . $courseid . "/" . $startpath;
	
		//open xml to return
	$xml_output = "<directorylist>";
	
	/* Old way which works with php4 : Justin */
	/*
    $directory = opendir($fullpath);             // Find all files
    while (false !== ($afile = readdir($directory))) {
        if ($afile == "." || $afile == "..") {
            continue;
        }

        if (is_dir($fullpath."/".$afile)) {
            $dirlist[] = $afile;
        } else {
            $filelist[] = $afile;
        }
    }
    closedir($directory);	
	*/


	
	/* New way which works with php5, but not is_dir : Justin */
	$files = scandir($fullpath);
	if (!empty($files)) {
       $xml_output .= fetch_dir_contents($fullpath,true);
	}

	
	
	//close xml to return
	$xml_output .= "</directorylist>";

	//Return the data
	return $xml_output;


}

//Fetch a deck of flashcards  
function fetch_poodllflashcards($courseid, $cardsetid=-1,$cardsetname="",$frontcolor=-1, $backcolor=-1){
global $CFG, $DB;	


	//Get question index from db if a question name was specified
	if($cardsetname != ""){
		$question = $DB->get_record('question', array('name'=>$cardsetname,'qtype'=>'match'));
		if($question){$cardsetid=$question->id;}
	}
	
	//get card data from db
	if($CFG->version < 2013051400){
		$subquestions = $DB->get_records('question_match_sub', array('question'=>$cardsetid));
	}else{
		$subquestions = $DB->get_records('qtype_match_subquestions', array('questionid'=>$cardsetid));
	}
	
    if (empty($subquestions)) {
       // notice(get_string('nosubquestions', 'poodllflashcard'));
        return "nothing nothing nothing goddamn it";          
    }
	
	//We really need to put formatting into the filter string itself, not mix it in with the data.
	if($frontcolor==-1){$fgcolor='0xDDDDDD';}
	if($backcolor==-1){$bgcolor='0x000000';}
	
	//set up xml to return
	$xml_output = "<stack frontfgcolor='$frontcolor' frontbgcolor='0x0000FF' backfgcolor='$backcolor' backbgcolor='0xDDDDDD'>\n";
	

	//loop through card data amd make xml doc.
	//see for poodllflashcards freeplayview for extending this with media etc
	foreach ($subquestions as $card) {
		//insert courseid info into path for image tags
		$newtext= str_replace( "src=\"","src=\"/file.php/" . $courseid . "/",$card->questiontext);
		$qinnerheight=" ";
		if($newtext != $card->questiontext){
			$card->questiontext = $newtext;
			$qinnerheight=" innerheight=\"0.8\" ";
		}

		$newtext  = str_replace( "src=\"","src=\"/file.php/" . $courseid . "/",$card->answertext);
		$ainnerheight=" ";
		if($newtext != $card->answertext){
			$card->answertext = $newtext;
			$ainnerheight=" innerheight=\"0.8\" ";
		}
		
		$xml_output .=  "\t<card>\n";
		$xml_output .=  "\t\t<background>0xCCCCCC</background>\n";
		$xml_output .=  "\t\t<front fontsize='18' type='text' " . $qinnerheight . "><![CDATA[" . $card->questiontext . "]]></front>\n";
		$xml_output .=  "\t\t<back fontsize='18' type='text' " . $ainnerheight . "><![CDATA[" . $card->answertext . "]]></back>\n";
		$xml_output .=  "\t</card>\n";
	}

	//close xml to return
	$xml_output .= "</stack>";

	//Return the data
	return $xml_output;


}




//Fetch a deck of flashcards  
function fetch_poodllflashcardsconvert($courseid, $flashcardid){
global $CFG, $DB;	
	//Convert the incoming module id into a flashcard instance id
	$modulerecord = $DB->get_record("course_modules", 'id',$flashcardid,'course',$courseid);
	if ($modulerecord){
		$flashcardid = $modulerecord->instance;
	}

	//Handle directories
	$subquestions = $DB->get_records('poodllflashcard_deckdata', 'flashcardid', $flashcardid);
    if (empty($subquestions)) {
       // notice(get_string('nosubquestions', 'poodllflashcard'));
        return "<stack></stack>";          
    }
	
	//We really need to put formatting into the filter string itself, not mix it in with the data.
	//Poor design, and a pain in the oshiri to tweak. Justin 2010/10/15
	
	//set up xml to return
	$xml_output = "<stack frontfgcolor='0xDDDDDD' frontbgcolor='0x0000FF' backfgcolor='0x000000' backbgcolor='0xDDDDDD'>\n";
	

	//loop through card data amd make xml doc.
	//see for poodllflashcards freeplayview for extending this with media etc
	foreach ($subquestions as $card) {
		//insert courseid info into path for image tags
		$newtext= str_replace( "src=\"","src=\"/file.php/" . $courseid . "/",$card->questiontext);
		$qinnerheight=" ";
		if($newtext != $card->questiontext){
			$card->questiontext = $newtext;
			$qinnerheight=" innerheight=\"0.8\" ";
		}

		$newtext  = str_replace( "src=\"","src=\"/file.php/" . $courseid . "/",$card->answertext);
		$ainnerheight=" ";
		if($newtext != $card->answertext){
			$card->answertext = $newtext;
			$ainnerheight=" innerheight=\"0.8\" ";
		}
		
		$xml_output .=  "\t<card>\n";
		$xml_output .=  "\t\t<background>0xCCCCCC</background>\n";
		$xml_output .=  "\t\t<front fontsize='18' type='text' " . $qinnerheight . "><![CDATA[" . $card->questiontext . "]]></front>\n";
		$xml_output .=  "\t\t<back fontsize='18' type='text' " . $ainnerheight . "><![CDATA[" . $card->answertext . "]]></back>\n";
		$xml_output .=  "\t</card>\n";
	}

	//close xml to return
	$xml_output .= "</stack>";

	//Return the data
	return $xml_output;


}




function fetchcourseitems($courseid){
global $CFG,$DB;


	
	$xml_output="";

	if (! $course = $DB->get_record('course', array('id' => $courseid))) {
		print_error('invalidcourseid');
	}
	require_course_login($course);


	//$modinfo =& get_fast_modinfo($course);
	$modinfo = get_fast_modinfo($course);


	get_all_mods($courseid, $mods, $modnames, $modnamesplural, $modnamesused);
	if (! $sections = get_all_sections($courseid) ) {  
	        $xml_output .= 'Error finding or creating section structures for this course';
	}

		//loop through the sections
          foreach ($sections as $thissection) {
			//display a section seperator for each secton
	        if (!$thissection->visible) {
		       $xml_output .= "\t<item label='---------(hidden)"  . "' url='' />\n";
			   continue;
			}else{
				$xml_output .= "\t<item label='-----------------"  . "' url='' />\n";
			}


	

	
				//loop through all the mods for each section
	          $sectionmods = explode(",", $thissection->sequence);
	          foreach ($sectionmods as $modnumber) {
	              if (empty($mods[$modnumber])) {
	                  continue;
	              }
	  
	              $mod = $mods[$modnumber];
	  

	              if (isset($modinfo->cms[$modnumber])) {
	                  if (!$modinfo->cms[$modnumber]->uservisible) {
	                      // visibility shortcut
	                      continue;
	                  }
						//here we get the name of the mod. We need to encode it 
						//because the xml breaks otherwise when there arequotes etc.
						$instancename =  htmlspecialchars( $modinfo->cms[$modnumber]->name,ENT_QUOTES);
	              } else {
	                  if (!file_exists("$CFG->dirroot/mod/$mod->modname/lib.php")) {
	                      // module not installed
	                      continue;
	                  }
	                  if (!coursemodule_visible_for_user($mod)) {
	                      // full visibility check
	                      continue;
	                  }
					 //we have a mod, but for some reasonwe could not establish its name. 
					$instancename =  "mod with no name" ;
	              }//end of if isset


					//this works for now, but ultimately we will need to ad the "extra" paramaters from $modinfo
	              	$xml_output .= "\t<item label='" . $instancename . "' url='" . urlencode($CFG->wwwroot . "/mod/" . $mod->modname . "/view.php?id=" .$modnumber) ."' />\n";
	          }//end of for each mod
	}//end of for each section

	return $xml_output;


}
?>