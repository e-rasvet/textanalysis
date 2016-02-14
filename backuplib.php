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

    function textanalysis_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += textanalysis_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        return $info;
    }
    
    function textanalysis_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        //Now, if requested, the user_data
        if (!empty($instance->userdata)) {
            $info[$instance->id.'1'][0] = get_string("messages","textanalysis");
            if ($ids = chat_message_ids_by_instance ($instance->id)) { 
                $info[$instance->id.'1'][1] = count($ids);
            } else {
                $info[$instance->id.'1'][1] = 0;
            }
        }
        return $info;
    }

    function textanalysis_backup_mods($bf,$preferences) {

        global $CFG;

        $status = true;

        //Iterate over textanalysis table
        $textanalysiss = get_records ("textanalysis","course",$preferences->backup_course,"id");
        if ($textanalysiss) {
            foreach ($textanalysiss as $textanalysis) {
                if (backup_mod_selected($preferences,'textanalysis',$textanalysis->id)) {
                    $status = textanalysis_backup_one_mod($bf,$preferences,$textanalysis);
                }
            }
        }
 
        return $status;  
    }
    
    function textanalysis_backup_one_mod($bf,$preferences,$textanalysis) {

        global $CFG;
    
        if (is_numeric($textanalysis)) {
            $textanalysis = get_record('textanalysis','id',$textanalysis);
        }
    
        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print textanalysis data
        fwrite ($bf,full_tag("ID",4,false,$textanalysis->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"textanalysis"));
        fwrite ($bf,full_tag("COURSE",4,false,$textanalysis->course));
        fwrite ($bf,full_tag("TEACHER",4,false,$textanalysis->teacher));
        fwrite ($bf,full_tag("NAME",4,false,$textanalysis->name));
        fwrite ($bf,full_tag("INTRO",4,false,$textanalysis->intro));
        fwrite ($bf,full_tag("TYPE",4,false,$textanalysis->type));
        fwrite ($bf,full_tag("TIME",4,false,$textanalysis->time));
        fwrite ($bf,full_tag("TEXTANALYSISTIME",4,false,time()));
        //if we've selected to backup users info, then execute backup_textanalysis_messages
        //End mod
        $status =fwrite ($bf,end_tag("MOD",3,true));

        return $status;
    }
    
    
?>