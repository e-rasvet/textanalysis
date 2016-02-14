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

    add_to_log($course->id, "textanalysis", "view", "view.php?id=$id", "$cm->instance");

/// Print the page header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }


    print_header("$course->shortname: $textanalysis->name", "$course->fullname",
                 "$navigation $textanalysis->name", 
                  "", "", true, update_module_button($id, $course->id, $strtextanalysis), 
                  navmenu($course));
                  
?>
<style type="text/css">

a { color: rgb(51, 102, 153); text-decoration: none; }
a:hover { color: rgb(102, 153, 204); text-decoration: underline; }
a:active { color: rgb(102, 153, 204); text-decoration: underline; }
body { background: rgb(255, 255, 255) none repeat; }
body, table { font-family: Verdana, Helvetica, Arial, sans-serif; font-size: 10pt; }
p, li { line-height: 140%; padding: 0.5em; }
a img { border: 0px none ; }

.index-letter-menu {
margin: 1em; 
text-align: center;}

.index-letter {
font-size: 12pt;}

.index-letter-section {
border: 1px dotted rgb(153, 153, 153); 
padding: 0.5em; 
background-color: rgb(238, 238, 238); 
margin-bottom: 1em;}

.index-letter-title {
font-size: 12pt; 
font-weight: bold;}

.function-bold {
font-weight: bold;}

#function-example {
padding: 0.5em; 
background-color: rgb(220, 220, 220); 
}

#title {
font-size: 2em;
text-align: center;}

#function-title {
padding: 0.5em;
background-color: rgb(153, 153, 255);
border-bottom: 1px #6666CC solid;
font-weight: bolder;
font-size: 12pt;}

#function-description {
padding: 0.5em;
}

#function-short-description {
padding: 0.5em;
background-color: rgb(221, 221, 255);
font-weight: bold;}

#function-bottom-space {
margin-bottom: 1em;} 


</style>
<?php
                  
    echo "<br />";
    
    if (!empty ($type)) {
        include('tabs.php');
    }
    
    print_simple_box_start('center', '100%', '#ffffff', 10);
    
    if (empty ($type)) {
    
        //-------------Journal title-----------//
        
        echo "<h2>Journal:</h2><br />";
        
        $jurnals = get_records ("journal", "course", $course->id);
        
        if (is_array($jurnals)) {
            foreach ($jurnals as $jurnal) {
                echo '<a href="?id='.$id.'&type=journal&jid='.$jurnal->id.'">'.$jurnal->name.'</a> (' . count_records ("journal_entries", "journal", $jurnal->id) . ') <br />';
            }
        }
        
        //-------------Forums  title-----------//
        
        echo "<h2>Forums:</h2><br />";
        
        $forums = get_records ("forum", "course", $cm->course);
    
        if (is_array($forums)) {
            foreach ($forums as $forum) {
                if (isteacher($cm->course)) {
                    $summarylink = '<a href="?id='.$id.'&jid='.$forum->id.'&type=forum&tf=1">summary students data</a>';
                }
                else
                {
                    $summarylink = "";
                }
          
                echo '<a href="?id='.$id.'&type=forum&jid='.$forum->id.'">'.$forum->name.'</a> (' . count_records ("forum_discussions", "forum", $forum->id) . ')  '.$summarylink.' <br />';
            }
        }
        
        //-------------Blog    title-----------//
        
        echo "<h2>blogs:</h2><br />";
        
        $allstudents = get_course_students ($course->id);
            
        if (is_array($allstudents)) {
            foreach ($allstudents as $allstudent) {
                if (isteacher($course->id) || $allstudent->id == $USER->id) {
                    echo '<a href="?id='.$id.'&type=blog&student='.$allstudent->id.'">'.fullname($allstudent).'</a> (<a href="?id='.$id.'&type=blog&tf=1&student='.$allstudent->id.'">only summary</a>) count of records:'. count_records ("post", "userid", $allstudent->id) .'<br />';
                }
            }
        }
        
        
        //-------------Chat    title-----------//
        
        echo "<h2>Chat:</h2><br />";
        
        $chats = get_records ("chat", "course", $course->id);
    
        if (is_array($chats)) {
            foreach ($chats as $chat) {
                echo '<a href="?id='.$id.'&type=chat&jid='.$chat->id.'">'.$chat->name.'</a> <br />';
            }
        }
        
        //-------------Gallery title-----------//
        
        if (file_exists("../../gallery2")) { 
        
            echo "<h2>Gallery:</h2><br />";
            
            if (!$students = get_course_students($course->id, "u.firstname ASC, u.lastname ASC", "", 0, 99999,'', '', NULL, '', 'u.id,u.firstname,u.lastname,u.email,u.username')) {
                $students = array();
            }
            
            foreach ($students as $student) {
                $coursestudents[$student->id] = $student->username;
            }
            
            require_once $CFG->dirroot . "/gallery2/bootstrap.inc";
            require_once $CFG->dirroot . "/gallery2/embed.php";
    
            mysql_connect ($storeConfig['hostname'],$storeConfig['username'],$storeConfig['password']);
            $request=mysql_select_db ($storeConfig['database']);
            if (!mysql_connect) {
                echo "Error: ".mysql_error ();
            }
    
            $r_autor = mysql_query("SELECT * FROM ".$storeConfig['tablePrefix']."Entity WHERE ".$storeConfig['columnPrefix']."entityType = 'GalleryAlbumItem'");
            while ($row_table = mysql_fetch_array ($r_autor)) {
                $albums[$row_table[$storeConfig['columnPrefix'] . 'id']] = $row_table[$storeConfig['columnPrefix'] . 'id'];
            }
    
            $embed = new GalleryEmbed;
            $embed->init();
            $g_api = new GalleryCoreApi;

            list ($ret, $usersdata) = $g_api->fetchUsernames();
            
            if ($ret) {
                $ret->getAsHtml(); 
            }

            echo '<center><form action="" method="post"><input type="hidden" name="type" value="gallery" /><select name="addselect[]" size="20" id="addselect" multiple>';
    
            foreach ($usersdata as $userdatakey => $userdatavalue) {
            
              if (in_array ($userdatavalue, $coursestudents)) {

                list($ret,$usr) = $g_api->fetchUserByUserName($userdatavalue);
                list ($ret, $items) = $g_api->fetchAllItemIdsByOwnerId($userdatakey);
                
                if ($ret) {
                    $ret->getAsHtml(); 
                }
        
                if (count($items) > 0) {
                    foreach ($items as $item) {
                    if ($checker != "album") {
                        list ($checker, $rootname) = get_albumname ($albums, $item);
                    }
                }
        
                if (!empty ($rootname)) {
                    echo '<option value="'.$userdatakey.'">'.$userdatavalue.' ('.$usr->fullName.') album: '.$rootname.'</option>';
                }
        
                unset ($checker);
        
                }
              }
            }
    
            echo '</select><br /><br />';
            echo '<input type="submit" value="go"></form></center><br /><br />';
            echo '';
        
        }
    
    } else if ($type == "journal") {
    
        echo "<h2>Journal:</h2><br />";
        echo '<a href="get_csv.php?id='.$id.'&type=journal&jid='.$jid.'">Download CSV</a><br /><br />';
        $jurnals = get_records ("journal_entries", "journal", $jid, "modified");
        
        foreach ($jurnals as $jurnal) {
        
          if (isteacher($course->id) || $jurnal->userid == $USER->id) {
          
            $userdata = get_record ("user", "id", $jurnal->userid);
          
            $imagepath = textanalysis_getuserimage($jurnal->userid);

            echo '<table border="1" cellspacing="0" valign="top" cellpadding="10">';
        
            echo '<tr><td rowspan="2" width="35" valign="top"><a  href="'.$CFG->wwwroot.'/courses/user/view.php?id='.$jurnal->userid.'&course='.$course->id.'"><img class="userpicture" align="middle" src="'.$imagepath.'" border="0" width="35" height="35" alt="" /></a></td><td nowrap="nowrap" width="100%">'.fullname($userdata).'</td></tr><tr><td width="100%">'.format_text($jurnal->text).'<hr />';
            
            textanalysis_printanalizeform($jurnal->text);

            echo '</td></tr>';
        
            echo '</table>';
            
          }
        
        }
            
    } else if ($type == "forum") {
    
        echo "<h2>Forums:</h2><br />";
        echo '<a href="get_csv.php?id='.$id.'&type=forum">Download CSV</a><br /><br />';
        if ($tf == 0) {
        
            if ($sf == 0) {
    
                $forums = get_records ("forum", "id", $jid);
        
                foreach ($forums as $forum) {
                    // and userid = '".$userid1."'
                    $forumentrys = get_records_sql ("SELECT * FROM ".$CFG->prefix."forum_discussions WHERE forum = '".$forum->id."'");
                    if (is_array($forumentrys)) {
                        foreach ($forumentrys as $forumentry) {
                    
                            if (!isteacher($cm->course)) {
                                $userdpostscount = " (" . count_records ("forum_posts", "parent", $forumentry->id, "userid", $USER->id) . ")";
                            }
                            else
                            {
                                $userdpostscount = " (" . count_records ("forum_posts", "parent", $forumentry->id) . ")";
                            }
                        
                    
                            echo '<a href="?id='.$id.'&jid='.$jid.'&type=forum&sf='.$forumentry->id.'">' . $forumentry->name ."</a> ".$userdpostscount.' <br />';
                        }
                    }
        
                }
        
            }
            else
            {
            
                $forums = get_records ("forum_posts", "parent", $sf, "modified");

                foreach ($forums as $forum) {
                
                  if (isteacher($course->id) || $forum->userid == $USER->id) {
                  
            
                    $userdata = get_record ("user", "id", $forum->userid);
          
                    $imagepath = textanalysis_getuserimage($forum->userid);

                    echo '<table border="1" cellspacing="0" valign="top" cellpadding="10">';
        
                    echo '<tr><td rowspan="2" width="35" valign="top"><a  href="'.$CFG->wwwroot.'/courses/user/view.php?id='.$forum->userid.'&course='.$course->id.'"><img class="userpicture" align="middle" src="'.$imagepath.'" border="0" width="35" height="35" alt="" /></a></td><td nowrap="nowrap" width="100%">'.fullname($userdata).'</td></tr><tr><td width="100%">'.format_text($forum->message).'<hr />';
            
                    textanalysis_printanalizeform($forum->message);

                    echo '</td></tr>';
        
                    echo '</table>';
                    
                  }
                
                }
            
            }
        
        }
        else if ($tf == 1)
        {
          if ($student == 0) {
            $allstudents = get_course_students ($course->id);
            
            foreach ($allstudents as $allstudent) {
                echo '<a href="?id='.$id.'&jid='.$jid.'&type=forum&tf=1&student='.$allstudent->id.'">'.fullname($allstudent).'</a> count of posts:'. count_records ("forum_posts", "userid", $allstudent->id) .'<br />';
            }
            
          }
          else
          {
              $userdata = get_record ("user", "id", $student);
              
              $forums = get_records_sql ("SELECT * FROM ".$CFG->prefix."forum_posts WHERE userid = '".$student."'");

              foreach ($forums as $forum) {
                  $text .= $forum->message . " ";
              }
          
              echo '<table border="1" cellspacing="0" valign="top" cellpadding="10">';
        
              echo '<tr><td><b>'.fullname($userdata).'</b><br /><hr />';
            
              textanalysis_printanalizeform($text);

              echo '</td></tr>';
        
              echo '</table>';
          }
        }
        
    } else if ($type == "blog") {
    
        echo "<h2>Blogs:</h2><br />";
        echo '<a href="get_csv.php?id='.$id.'&type=blog">Download CSV</a><br /><br />';
        if ($tf == 0) {
            $blogs = get_records ("post", "userid", $student, "lastmodified");

            foreach ($blogs as $blog) {
                
              if (isteacher($course->id) || $blog->userid == $USER->id) {
                  
                $userdata = get_record ("user", "id", $blog->userid);
          
                $imagepath = textanalysis_getuserimage($blog->userid);

                echo '<table border="1" cellspacing="0" valign="top" cellpadding="10">';
        
                echo '<tr><td rowspan="2" width="35" valign="top"><a  href="'.$CFG->wwwroot.'/courses/user/view.php?id='.$blog->userid.'&course='.$course->id.'"><img class="userpicture" align="middle" src="'.$imagepath.'" border="0" width="35" height="35" alt="" /></a></td><td nowrap="nowrap" width="100%">'.fullname($userdata).' '.format_text($blog->subject).'</td></tr><tr><td width="100%">'.format_text($blog->summary).'<hr />';
            
                textanalysis_printanalizeform($blog->summary);

                echo '</td></tr>';
        
                echo '</table>';
                    
              }
                
            }
        }
        else
        {
            $blogs = get_records ("post", "userid", $student, "lastmodified");

            foreach ($blogs as $blog) {
                
              if (isteacher($course->id) || $blog->userid == $USER->id) {
                  
                  $text .= $blog->summary;
                    
              }
                
            }
            
            $userdata  = get_record ("user", "id", $student);
            $imagepath = textanalysis_getuserimage($student);
            
            echo '<table border="1" cellspacing="0" valign="top" cellpadding="10">';
        
            echo '<tr><td rowspan="2" width="35" valign="top"><a  href="'.$CFG->wwwroot.'/courses/user/view.php?id='.$blog->userid.'&course='.$course->id.'"><img class="userpicture" align="middle" src="'.$imagepath.'" border="0" width="35" height="35" alt="" /></a></td><td nowrap="nowrap" width="100%">'.fullname($userdata).'</td></tr><tr><td width="100%"><hr />';
            
            textanalysis_printanalizeform($text);
            
            echo '</td></tr>';
        
            echo '</table>';
            
        }
        
        
    } else if ($type == "chat") {
    
        echo "<h2>Chat:</h2><br />";
        echo '<a href="get_csv.php?id='.$id.'&type=chat">Download CSV</a><br /><br />';
        if ($tf == 0) {
            $allstudents = get_course_students ($course->id);
            
            foreach ($allstudents as $allstudent) {
              if (isteacher($course->id) || $allstudent->id == $USER->id) {
                echo '<a href="?id='.$id.'&jid='.$jid.'&type=chat&tf=1&student='.$allstudent->id.'">'.fullname($allstudent).'</a> count of posts:'. count_records ("chat_messages", "userid", $allstudent->id) .'<br />';
              }
            }
        }
        else
        {
            $chats = get_records ("chat_messages", "userid", $student, "timestamp");

            foreach ($chats as $chat) {
              if (isteacher($course->id) || $chat->userid == $USER->id) {
                  $text .= $chat->message . " ";
                    
              }
                
            }
            
            $userdata  = get_record ("user", "id", $student);
            $imagepath = textanalysis_getuserimage($student);
            
            echo '<table border="1" cellspacing="0" valign="top" cellpadding="10">';
        
            echo '<tr><td rowspan="2" width="35" valign="top"><a  href="'.$CFG->wwwroot.'/courses/user/view.php?id='.$chat->userid.'&course='.$course->id.'"><img class="userpicture" align="middle" src="'.$imagepath.'" border="0" width="35" height="35" alt="" /></a></td><td nowrap="nowrap" width="100%">'.fullname($userdata).'</td></tr><tr><td width="100%"><hr />';
            
            textanalysis_printanalizeform($text);
            
            echo '</td></tr>';
        
            echo '</table>';
        }
    
    } else if ($type == "gallery") {
    
      $alluserslink = "";
    
      if ($_POST['addselect']) {
        foreach ($_POST['addselect'] as $user) {
            $alluserslink .= $user.":";
        }
      }
    
      echo "<h2>Gallery:</h2><br />";
      echo '<a href="get_csv.php?id='.$id.'&type=gallery&addselect='.$alluserslink.'">Download CSV</a><br /><br />';
        
      require_once $CFG->dirroot . "/gallery2/bootstrap.inc";
      require_once $CFG->dirroot . "/gallery2/embed.php";
      
      $embed = new GalleryEmbed;
      $embed->init();
      $g_api = new GalleryCoreApi;
      
      list ($ret, $usersdata) = $g_api->fetchUsernames();
        
      if ($_POST['addselect']) {
        foreach ($_POST['addselect'] as $user) {

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
            
            echo '<br /><br /><div id="function-title">'.$usr->userName." (".$usr->fullName.") course: ".$course->fullname."</div>";
            
            unset ($checker);
            
            echo '<div id="function-description">' . $summary . '</div>';
            
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
            
            echo '<p><span class="function-bold">Text analysis:</span></p>';
            
            echo '<div id="function-example"><table cellspacing="20"><tr><td valign="top">';
            
            echo '<table width="300"><tr>';
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
            
            $contents .= $usr->userName . ',' . $rootname . ',' . $data['wordcount'] . ',' . $data['worduniquecount'] . ',' . $data['numberofsentences'] . ',' . $data['averagepersentence'] . ',' . $data['hardwords'] . ',' . $data['lexicaldensity'] . ',' . $data['fogindex'] ."\r\n";
            
        }
      }
    
    }
    
    
    
    print_simple_box_end();

    print_footer($course);
    
    



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