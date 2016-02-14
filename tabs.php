<?php  // $Id: mysql.php,v 1.0 2007/03/10 16:41:20 Igor Nikulin

/// This file to be included so we can assume config.php has already been included.

    if (empty($textanalysis)) {
        error('You cannot call this script in that way');
    }
    if (!isset($currenttab)) {
        $currenttab = '';
    }
    if (!isset($cm)) {
        $cm = get_coursemodule_from_instance('textanalysis', $textanalysis->id);
    }
    if (!isset($course)) {
        $course = get_record('course', 'id', $textanalysis->course);
    }

    $tabs = array();
    $row  = array();
    $inactive = array();

    $row[] = new tabobject('return', $CFG->wwwroot . "/mod/textanalysis/view.php?id=" . $id , "Main");
    
    $tabs[] = $row;

    print_tabs($tabs, $currenttab, $inactive);

?>
