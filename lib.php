<?php  // $Id: mysql.php,v 1.0 2007/07/02 12:37:00 Igor Nikulin

function textanalysis_add_instance($textanalysis) {

global $CFG, $USER;
    
    $id = $textanalysis->courseid;
    $textanalysis->timemodified = time();
    
    return insert_record("textanalysis", $textanalysis);
}




function textanalysis_update_instance($textanalysis, $id) {
    global $CFG;
   
    $textanalysis->timemodified = time();
    $textanalysis->id = $textanalysis->instance;

    # May have to add extra stuff in here #

    return update_record("textanalysis", $textanalysis);
    
}



function textanalysis_submit_instance($textanalysis, $id) {

    global $CFG;
  

}



function textanalysis_delete_instance($id) {

    global $CFG;

    if (! $textanalysis = get_record("textanalysis", "id", "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records("textanalysis", "id", "$textanalysis->id")) {
        $result = false;
    }

    return $result;
}




function textanalysis_user_outline($course, $user, $mod, $textanalysis) {
    return $return;
}




function textanalysis_user_complete($course, $user, $mod, $textanalysis) {
    return true;
}




function textanalysis_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}




function textanalysis_cron () {
    global $CFG;

    return true;
}




function textanalysis_grades($textanalysisid) {
   return NULL;
}




function textanalysis_get_participants($textanalysisid) {
    global $CFG;
    return get_records_sql("SELECT * FROM ".$CFG->prefix."user");
}




function textanalysis_scale_used ($textanalysisid,$scaleid) {
    $return = false;
   
    return $return;
}


function textanalysis_wordcount ($text) {

    return str_word_count ($text);

}


function textanalysis_worduniquecount ($text) {

    $words  = str_word_count ($text, 1);
    $words_ = Array ();
    
    foreach ($words as $word) {
        if (!in_array($word, $words_)) {
            $words_[] = strtolower ($word);
        }
    }

    return count ($words_);

}



function textanalysis_numberofsentences ($text) {
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

function textanalysis_averagepersentence ($text, $words, $sentences) {

    $count = round($words / $sentences, 2);
    return $count;

}



function textanalysis_lexicaldensity ($text, $word, $wordunic) {

    $count = round(($wordunic / $word) * 100, 2);

    return $count;

}



function textanalysis_fogindex ($text, $averagepersentence, $hardwordspersent) {

    $count = round(($averagepersentence + $hardwordspersent) * 0.4, 2);

    return $count;

}



function textanalysis_laters ($text) {

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



function textanalysis_hardwords($text, $wordstotal) {
    $syllables = 0;
    $words = explode(' ', $text);
    for ($i = 0; $i < count($words); $i++) {
        if (textanalysis_count_syllables($words[$i]) > 2) {
            //echo $words[$i] . "/" . count_syllables($words[$i]) ."<br />";
            $syllables ++;
        }
    }

    $score = round(($syllables / $wordstotal) * 100, 2);

    return Array($syllables, $score);
}



function textanalysis_count_syllables($word) {

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



function textanalysis_printanalizeform($text) {

global $CFG;
    
    $data = Array ();
    
    $text = strip_tags ($text);
    
    $data['wordcount'] = textanalysis_wordcount ($text);
    $data['worduniquecount'] = textanalysis_worduniquecount ($text);
    $data['numberofsentences'] = textanalysis_numberofsentences ($text);
    if ($data['numberofsentences'] == 0 || empty($data['numberofsentences'])) {
        $data['numberofsentences'] = 1;
    }
    $data['averagepersentence'] = textanalysis_averagepersentence ($text, $data['wordcount'], $data['numberofsentences']);
    list ($data['hardwords'], $data['hardwordspersent']) = textanalysis_hardwords ($text, $data['wordcount']);
    $data['lexicaldensity'] = textanalysis_lexicaldensity ($text, $data['wordcount'], $data['worduniquecount']);
    $data['fogindex'] = textanalysis_fogindex ($text, $data['averagepersentence'], $data['hardwordspersent']);
    $data['laters'] = textanalysis_laters ($text);
    

    echo '<p><b>Text analysis:</b></p>';
            
    echo '<div id="function-example"><table cellspacing="20"><tr><td valign="top">';
    
    echo '<table width="600"><tr>';
    echo '<td align="right">Total Word Count: </td><td> <b>' . $data['wordcount'] . '</b></td></tr><tr>';
    echo '<td align="right">Total Unique Words: </td><td> <b>' . $data['worduniquecount'] . '</b></td></tr><tr>';
    echo '<td align="right">Number of Sentences: </td><td> <b>' . $data['numberofsentences'] . '</b></td></tr><tr>';
    echo '<td align="right">Average Words per Sentence: </td><td> <b>' . $data['averagepersentence'] . '</b></td></tr><tr>';
    echo '<td align="right">Hard Words: </td><td> <b>' . $data['hardwords'] . '</b> ('.$data['hardwordspersent'].'%)' . '</td></tr><tr>';
    echo '<td align="right">Lexical Density: </td><td> <b>'.$data['lexicaldensity'].'</b>%' . '</td></tr><tr>';
    echo '<td align="right">Fog Index: </td><td> <b>'.$data['fogindex'] . '</b></td>';
    
    echo '</tr></table>';

    echo '</td><td valign="top">';
    
    echo '<table width="400">';
    
    foreach ($data['laters'] as $key => $value) {
        $persenttage = round (($value / $data['wordcount']) * 100, 2);
        
        $persenttageimage = round($persenttage * 2) + 1;
        
        echo '<tr><td width="140">'.$key.' letter words </td><td width="20">'.$value.'</td><td width="240"><img src="http://www.usingenglish.com/images/bar1.gif" height="10" width="'.$persenttageimage.'"> <em>'.$persenttage.'%</em></td></tr>';
    }
    
    echo '</table>';
    
    echo '</td></tr></table></div>';

}


function textanalysis_getuserimage($userid) {

global $CFG;
    
    if (is_file($CFG->dataroot . "/user/".$userid."/f2.jpg")) {
        $imagepath = $CFG->wwwroot . "/user/pix.php/".$userid."/f2.jpg";
    }
    else
    {
        $imagepath = $CFG->wwwroot . "/pix/u/f2.png";
    }
    
    return $imagepath;

}

?>
