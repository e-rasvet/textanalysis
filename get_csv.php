<?php  // $Id: mysql.php,v 1.0 2007/07/02 12:37:00 Igor Nikulin

    require_once("../../config.php");
    require_once("lib.php");

    $id   = optional_param('id', 0, PARAM_INT); 
    $a    = optional_param('a', 0, PARAM_INT);  
    $jid  = optional_param('jid', 0, PARAM_INT);  
    $sf   = optional_param('sf', 0, PARAM_INT);  
    $tf   = optional_param('tf', 0, PARAM_INT);  
    $student = optional_param('student', 0, PARAM_INT);  
    $type = optional_param('type');  
    $addselect = optional_param('addselect');  
    

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
        if (! $textanalysis = get_record("textanalysis", "id", $cm->instance)) {
            error("Course module is incorrect");
        }
    } else {
        if (! $textanalysis = get_record("textanalysis", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $textanalysis->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("textanalysis", $textanalysis->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }

    require_login($course->id);

    add_to_log($course->id, "textanalysis", "get CSV", "view.php?id=$id", "$cm->instance");


    $contents = "UserName,Course,Wordcount,WordUniquecount,Number of sentences,Average Persentence,Hardwords,Lexicaldensity,Fogindex\r\n";
    
    if ($type == "gallery") {
        
      require_once $CFG->dirroot . "/gallery2/bootstrap.inc";
      require_once $CFG->dirroot . "/gallery2/embed.php";
      
      $embed = new GalleryEmbed;
      $embed->init();
      $g_api = new GalleryCoreApi;
      
      list ($ret, $usersdata) = $g_api->fetchUsernames();
      
      $addselect_ = explode(":", $addselect);
        
      if ($addselect_) {
        foreach ($addselect_ as $user) {
          if ($user) {

            list ($ret, $items) = $g_api->fetchAllItemIdsByOwnerId($user);
            
            //---------------------????????? ? ????????????---------------------//
            list($ret,$usr) = $g_api->fetchUserByUserName($usersdata[$user]);
            
            $summary = "";
            $summary_s = "";
            foreach ($items as $item) {
                $r_tb=mysql_query("SELECT * FROM ".$storeConfig['tablePrefix']."Item WHERE ".$storeConfig['columnPrefix']."id = '".$item."'");
                $row_tb=mysql_fetch_array($r_tb);
                if ($row_tb[$storeConfig['columnPrefix'] . 'summary'] != $usr->userName) {
                    $summarysize = strlen (trim ($row_tb[$storeConfig['columnPrefix'] . 'summary']));
                    $descriptionsize = strlen (trim ($row_tb[$storeConfig['columnPrefix'] . 'description']));
                    if ($summarysize > $descriptionsize) {
                        $summary .= strip_tags (trim ($row_tb[$storeConfig['columnPrefix'] . 'summary'])) . " [s]<br />";
                        $summary_s .= strip_tags (trim ($row_tb[$storeConfig['columnPrefix'] . 'summary'])) . " ";
                    }
                    else
                    {
                        $summary .= strip_tags (trim ($row_tb[$storeConfig['columnPrefix'] . 'description'])) . " [d]<br />";
                        $summary_s .= strip_tags (trim ($row_tb[$storeConfig['columnPrefix'] . 'description'])) . " ";
                    }
                }
                
            }

            unset ($checker);
            
            //---------------------?????????? ? ??????---------------------------//
            
            $data = Array ();
            
            $data['wordcount'] = wordcount ($summary_s);
            $data['worduniquecount'] = worduniquecount ($summary_s);
            $data['numberofsentences'] = numberofsentences ($summary_s);
            if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
                $data['numberofsentences'] = 1;
            }
            $data['averagepersentence'] = averagepersentence ($summary_s, $data['wordcount'], $data['numberofsentences']);
            list ($data['hardwords'], $data['hardwordspersent']) = hardwords ($summary_s, $data['wordcount']);
            $data['lexicaldensity'] = lexicaldensity ($summary_s, $data['wordcount'], $data['worduniquecount']);
            $data['fogindex'] = fogindex ($summary_s, $data['averagepersentence'], $data['hardwordspersent']);
            $data['laters'] = laters ($summary_s);
            
            foreach ($data['laters'] as $key => $value) {
                $persenttage = round (($value / $data['wordcount']) * 100, 2);
                $persenttageimage = round($persenttage * 2) + 1;
            }
            
            $contents .= $usr->userName.' ('.$usr->fullName.'),' . $course->fullname . ',' . $data['wordcount'] . ',' . $data['worduniquecount'] . ',' . $data['numberofsentences'] . ',' . $data['averagepersentence'] . ',' . $data['hardwords'] . ',' . $data['lexicaldensity'] . ',' . $data['fogindex'] ."\r\n";
            
            
          }
        }
        
      }
    
    }
            else if ($type == "journal") {
            $jurnals = get_records ("journal_entries", "journal", $jid, "modified");
        
            foreach ($jurnals as $jurnal) {
                $jurnal->text = strip_tags($jurnal->text);
                
                $userdata = get_record ("user", "id", $jurnal->userid);
                $data = Array ();
                $data['wordcount'] = wordcount ($jurnal->text);
                $data['worduniquecount'] = worduniquecount ($jurnal->text);
                $data['numberofsentences'] = numberofsentences ($jurnal->text);
                if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
                    $data['numberofsentences'] = 1;
                }
                $data['averagepersentence'] = averagepersentence ($jurnal->text, $data['wordcount'], $data['numberofsentences']);
                list ($data['hardwords'], $data['hardwordspersent']) = hardwords ($jurnal->text, $data['wordcount']);
                $data['lexicaldensity'] = lexicaldensity ($jurnal->text, $data['wordcount'], $data['worduniquecount']);
                $data['fogindex'] = fogindex ($jurnal->text, $data['averagepersentence'], $data['hardwordspersent']);
                $data['laters'] = laters ($jurnal->text);

                $contents .= $userdata->username.' ('.fullname($userdata) . '),' . $course->fullname . ',' . $data['wordcount'] . ',' . $data['worduniquecount'] . ',' . $data['numberofsentences'] . ',' . $data['averagepersentence'] . ',' . $data['hardwords'] . ',' . $data['lexicaldensity'] . ',' . $data['fogindex'] ."\r\n";
            }
        }
        else if ($type == "blog") {
          $coursestudents = get_course_students($course->id);
          foreach ($coursestudents as $student) {
            $blogs = get_records ("post", "userid", $student->id, "lastmodified");
            $text = "";
            foreach ($blogs as $blog) {
                $blog->summary = strip_tags($blog->summary);
                $text .= $blog->summary;
            }
            
                $userdata = get_record ("user", "id", $student->id);
                $data = Array ();
                $data['wordcount'] = wordcount ($text);
                $data['worduniquecount'] = worduniquecount ($text);
                $data['numberofsentences'] = numberofsentences ($text);
                if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
                    $data['numberofsentences'] = 1;
                }
                $data['averagepersentence'] = averagepersentence ($text, $data['wordcount'], $data['numberofsentences']);
                list ($data['hardwords'], $data['hardwordspersent']) = hardwords ($text, $data['wordcount']);
                $data['lexicaldensity'] = lexicaldensity ($text, $data['wordcount'], $data['worduniquecount']);
                $data['fogindex'] = fogindex ($text, $data['averagepersentence'], $data['hardwordspersent']);
                $data['laters'] = laters ($text);
                
                $contents .= $userdata->username.' ('.fullname($userdata) . '),' . $course->fullname . ',' . $data['wordcount'] . ',' . $data['worduniquecount'] . ',' . $data['numberofsentences'] . ',' . $data['averagepersentence'] . ',' . $data['hardwords'] . ',' . $data['lexicaldensity'] . ',' . $data['fogindex'] ."\r\n";
          }
        }
        else if ($type == "chat") {
          $coursestudents = get_course_students($course->id);
          foreach ($coursestudents as $student) {
            $blogs = get_records ("chat_messages", "userid", $student->id, "timestamp");
            $text = "";
            foreach ($blogs as $blog) {
                $blog->message = strip_tags($blog->message);
                $text .= $blog->message;
            }
            
                $userdata = get_record ("user", "id", $student->id);
                $data = Array ();
                $data['wordcount'] = wordcount ($text);
                $data['worduniquecount'] = worduniquecount ($text);
                $data['numberofsentences'] = numberofsentences ($text);
                if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
                    $data['numberofsentences'] = 1;
                }
                $data['averagepersentence'] = averagepersentence ($text, $data['wordcount'], $data['numberofsentences']);
                list ($data['hardwords'], $data['hardwordspersent']) = hardwords ($text, $data['wordcount']);
                $data['lexicaldensity'] = lexicaldensity ($text, $data['wordcount'], $data['worduniquecount']);
                $data['fogindex'] = fogindex ($text, $data['averagepersentence'], $data['hardwordspersent']);
                $data['laters'] = laters ($text);
                
                $contents .= $userdata->username.' ('.fullname($userdata) . '),' . $course->fullname . ',' . $data['wordcount'] . ',' . $data['worduniquecount'] . ',' . $data['numberofsentences'] . ',' . $data['averagepersentence'] . ',' . $data['hardwords'] . ',' . $data['lexicaldensity'] . ',' . $data['fogindex'] ."\r\n";
          }
        }
        else if ($type == "forum") {
          $coursestudents = get_course_students($course->id);
          foreach ($coursestudents as $student) {
              $forums = get_records ("forum_posts", "userid", $student->id);
              $text = "";
              foreach ($forums as $forum) {
                  $forum->message = strip_tags($forum->message);
                  $text .= $forum->message;
              }
              
                $userdata = get_record ("user", "id", $student->id);
                $data = Array ();
                $data['wordcount'] = wordcount ($text);
                $data['worduniquecount'] = worduniquecount ($text);
                $data['numberofsentences'] = numberofsentences ($text);
                if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
                    $data['numberofsentences'] = 1;
                }
                $data['averagepersentence'] = averagepersentence ($text, $data['wordcount'], $data['numberofsentences']);
                list ($data['hardwords'], $data['hardwordspersent']) = hardwords ($text, $data['wordcount']);
                $data['lexicaldensity'] = lexicaldensity ($text, $data['wordcount'], $data['worduniquecount']);
                $data['fogindex'] = fogindex ($text, $data['averagepersentence'], $data['hardwordspersent']);
                $data['laters'] = laters ($text);
                
                $contents .= $userdata->username.' ('.fullname($userdata) . '),' . ',' . $course->fullname . ',' . $data['wordcount'] . ',' . $data['worduniquecount'] . ',' . $data['numberofsentences'] . ',' . $data['averagepersentence'] . ',' . $data['hardwords'] . ',' . $data['lexicaldensity'] . ',' . $data['fogindex'] ."\r\n";
          }
        }
        
header("Content-type: application/octet-stream");
header('Content-Disposition: inline; filename=text_content_analysis_tool.csv'); 
        
echo $contents;

function wordcount ($text) {

    return str_word_count ($text);

}



function worduniquecount ($text) {

    $words  = str_word_count ($text, 1);
    $words_ = Array ();
    
    foreach ($words as $word) {
        if (!in_array($word, $words_)) {
            $words_[] = strtolower ($word);
        }
    }

    return count ($words_);

}



function numberofsentences ($text) {
    $text = strip_tags ($text);
    $noneed = array ("\r", "\n", ".0", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9");
    foreach ($noneed as $noneed_) {
        $text = str_replace ($noneed_, " ", $text);
    }
    $text = str_replace ("!", ".", $text);
    $text = str_replace ("?", ".", $text);
    $textarray = explode (".", $text);
    foreach ($textarray as $textarray_) {
        if (!empty($textarray_) && strlen ($textarray_) > 5) {
            $textarrayf[] = $textarray_;
        }
    } 
    $count = count($textarrayf);
    return $count;
}



function averagepersentence ($text, $words, $sentences) {

    $count = round($words / $sentences, 2);
    return $count;

}



function lexicaldensity ($text, $word, $wordunic) {

    $count = round(($wordunic / $word) * 100, 2);

    return $count;

}



function fogindex ($text, $averagepersentence, $hardwordspersent) {

    $count = round(($averagepersentence + $hardwordspersent) * 0.4, 2);

    return $count;

}



function laters ($text) {

    $words  = str_word_count ($text, 1);
    $words_ = Array ();
    
    $max = 1;
    
    foreach ($words as $word) {
        if (!in_array($word, $words_)) {
            $words_[] = strtolower ($word);
            if (strlen ($word) > $max) {
                $max = strlen ($word);
            }
        }
    }
    
    for ($i=1; $i<=$max; $i++) {
        foreach ($words as $word) {
            if (strlen($word) == $i) {
                $result[$i] ++;
            }
        }
    }

    return $result;

}



function hardwords($text, $wordstotal) {
    $syllables = 0;
    $words = explode(' ', $text);
    for ($i = 0; $i < count($words); $i++) {
        if (count_syllables($words[$i]) > 2) {
            //echo $words[$i] . "/" . count_syllables($words[$i]) ."<br />";
            $syllables ++;
        }
    }

    $score = round(($syllables / $wordstotal) * 100, 2);

    return Array($syllables, $score);
}



function count_syllables($word) {

  $nos = strtoupper($word);
  $syllables = 0;

  $before = strlen($nos);
  $nos = str_replace(array('AA','AE','AI','AO','AU',
  'EA','EE','EI','EO','EU','IA','IE','II','IO',
  'IU','OA','OE','OI','OO','OU','UA','UE',
  'UI','UO','UU'), "", $nos);
  $after = strlen($nos);
  $diference = $before - $after;
  if($before != $after) $syllables += $diference / 2;

  if($nos[strlen($nos)-1] == "E") $syllables --;
  if($nos[strlen($nos)-1] == "Y") $syllables ++;

  $before = $after;
  $nos = str_replace(array('A','E','I','O','U'),"",$nos);
  $after = strlen($nos);
  $syllables += ($before - $after);

  return $syllables;

}



function get_albumname ($albums, $item) {

    global $storeConfig;

    if (in_array ($item, $albums)) {
        mysql_connect ($storeConfig['hostname'],$storeConfig['username'],$storeConfig['password']);
        $request=mysql_select_db ($storeConfig['database']);
        if (!mysql_connect) {
            echo "Error: ".mysql_error ();
        }
        
       $r_tb=mysql_query("SELECT * FROM ".$storeConfig['tablePrefix']."ChildEntity WHERE ".$storeConfig['columnPrefix']."id = '".$item."'");
       $row_tb=mysql_fetch_array($r_tb);
       $rootid = $row_tb[$storeConfig['columnPrefix'] . 'parentId'];
       
       $r_tb=mysql_query("SELECT * FROM ".$storeConfig['tablePrefix']."Item WHERE ".$storeConfig['columnPrefix']."id = '".$rootid."'");
       $row_tb=mysql_fetch_array($r_tb);
       $rootname = $row_tb[$storeConfig['columnPrefix'] . 'title'];
        
       return Array ('album', $rootname);
        
    }
    
    return Array ('no album', '');

}

?>