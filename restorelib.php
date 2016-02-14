<?php //$Id: backuplib.php,v 1.0 2007/06/17 03:45:29 SerafimPanov Exp $
    //This php script contains all the stuff to backup/restore
    //textanalysis mods

    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------
    //This function executes all the restore procedure about this mod
    function textanalysis_restore_mods($mod,$restore) {

        global $CFG, $oldidarray;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {

            //Now get completed xmlized object   
            $info = $data->info;

            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug
            // if necessary, write to restorelog and adjust date/time fields
            if ($restore->course_startdateoffset) {
                restore_log_date_changes('textanalysis', $restore, $info['MOD']['#'], array('TEXTANALYSISTIME'));
            }
            //Now, build the textanalysis record structure
            $textanalysis->course = $restore->course_id;
            $textanalysis->teacher = backup_todb($info['MOD']['#']['TEACHER']['0']['#']);
            $textanalysis->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $textanalysis->intro = backup_todb($info['MOD']['#']['INTRO']['0']['#']);
            $textanalysis->type = backup_todb($info['MOD']['#']['TYPE']['0']['#']);
            $textanalysis->time = backup_todb($info['MOD']['#']['TIME']['0']['#']);
            
            $user = backup_getid($restore->backup_unique_code,"user",$textanalysis->teacher);
            if ($user) {
                $textanalysis->teacher = $user->new_id;
            }

            //The structure is equal to the db, so insert the textanalysis
            $newid = insert_record ("textanalysis",$textanalysis);

            //Do some output     
            //if (!defined('RESTORE_SILENTLY')) {
            //    echo "<li>".get_string("modulename","textanalysis")." \"".format_string(stripslashes($textanalysis->name),true)."\"</li>";
            //}
            //backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype, $mod->id, $newid);
                //Now check if want to restore user data and do it.
                if (restore_userdata_selected($restore,'textanalysis',$mod->id)) {
                    //Restore textanalysis_messages
                }
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        return $status;
    }


    function textanalysis_restore_logs($restore,$log) {

        $status = true;

        return $status;
    }
    
?>
