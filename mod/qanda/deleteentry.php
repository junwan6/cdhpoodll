<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT);          // course module ID
$confirm = optional_param('confirm', 0, PARAM_INT);  // commit the operation?
$entry = optional_param('entry', 0, PARAM_INT);    // entry id
$prevmode = required_param('prevmode', PARAM_ALPHA);
$hook = optional_param('hook', '', PARAM_CLEAN);

$url = new moodle_url('/mod/qanda/deleteentry.php', array('id' => $id, 'prevmode' => $prevmode));
if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}
if ($entry !== 0) {
    $url->param('entry', $entry);
}
if ($hook !== '') {
    $url->param('hook', $hook);
}
$PAGE->set_url($url);

$strqanda = get_string("modulename", "qanda");
$strqandas = get_string("modulenameplural", "qanda");
$stredit = get_string("edit");
$entrydeleted = get_string("entrydeleted", "qanda");


if (!$cm = get_coursemodule_from_id('qanda', $id)) {
    print_error("invalidcoursemodule");
}

if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

if (!$entry = $DB->get_record("qanda_entries", array("id" => $entry))) {
    print_error('invalidentry');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/qanda:manageentries', $context);

$manageentries = has_capability('mod/qanda:manageentries', $context);

if (!$qanda = $DB->get_record("qanda", array("id" => $cm->instance))) {
    print_error('invalidid', 'qanda');
}


$strareyousuredelete = get_string("areyousuredelete", "qanda");

if (($entry->userid != $USER->id) and !$manageentries) { // guest id is never matched, no need for special check here
    print_error('nopermissiontodelentry');
}
$ineditperiod = ((time() - $entry->timecreated < $CFG->maxeditingtime) || $qanda->editalways);
if (!$ineditperiod and !$manageentries) {
    print_error('errdeltimeexpired', 'qanda');
}

if ($entry->approved and !$manageentries) { // guest id is never matched, no need for special check here
    print_error(get_string('erralreadyanswered', 'qanda'));
}
/// If data submitted, then process and store.

if ($confirm and confirm_sesskey()) { // the operation was confirmed.
    // if it is an imported entry, just delete the relation
    if ($entry->sourceqandaid) {
        if (!$newcm = get_coursemodule_from_instance('qanda', $entry->sourceqandaid)) {
            print_error('invalidcoursemodule');
        }
        $newcontext = context_module::instance($newcm->id);

        $entry->qandaid = $entry->sourceqandaid;
        $entry->sourceqandaid = 0;
        $DB->update_record('qanda_entries', $entry);

        // move attachments too
        $fs = get_file_storage();

        if ($oldfiles = $fs->get_area_files($context->id, 'mod_qanda', 'attachment', $entry->id)) {
            foreach ($oldfiles as $oldfile) {
                $file_record = new stdClass();
                $file_record->contextid = $newcontext->id;
                $fs->create_file_from_storedfile($file_record, $oldfile);
            }
            $fs->delete_area_files($context->id, 'mod_qanda', 'attachment', $entry->id);
            $entry->attachment = '1';
        } else {
            $entry->attachment = '0';
        }
        $DB->update_record('qanda_entries', $entry);
    } else {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_qanda', 'attachment', $entry->id);
        $DB->delete_records("comments", array('itemid' => $entry->id, 'commentarea' => 'qanda_entry', 'contextid' => $context->id));
        $DB->delete_records("qanda_alias", array("entryid" => $entry->id));
        $DB->delete_records("qanda_entries", array("id" => $entry->id));

        // Update completion state
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $qanda->completionentries) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $entry->userid);
        }

        //delete qanda entry ratings
        require_once($CFG->dirroot . '/rating/lib.php');
        $delopt = new stdClass;
        $delopt->contextid = $context->id;
        $delopt->component = 'mod_qanda';
        $delopt->ratingarea = 'entry';
        $delopt->itemid = $entry->id;
        $rm = new rating_manager();
        $rm->delete_ratings($delopt);
    }

    add_to_log($course->id, "qanda", "delete entry", "view.php?id=$cm->id&amp;mode=$prevmode&amp;hook=$hook", $entry->id, $cm->id);
    redirect("view.php?id=$cm->id&amp;mode=$prevmode&amp;hook=$hook");
} else {        // the operation has not been confirmed yet so ask the user to do so
    $PAGE->navbar->add(get_string('delete'));
    $PAGE->set_title(format_string($qanda->name));
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    //$areyousure = "<b>".format_string($entry->question)."</b><p>$strareyousuredelete</p>";
    //$areyousure = "";
    $question = file_rewrite_pluginfile_urls($entry->question, 'pluginfile.php', $context->id, 'mod_qanda', 'question', $entry->id);
    $answer = file_rewrite_pluginfile_urls($entry->answer, 'pluginfile.php', $context->id, 'mod_qanda', 'answer', $entry->id);

    $areyousure = html_writer::tag('b', get_string("question", "qanda"));
    $areyousure .= html_writer::tag('div', format_text($question, FORMAT_HTML), array('class' => 'question-text')); //qanda_print_entry_answer($entry, $qanda, $cm);//html_writer::tag('div',format_text($entry->question, FORMAT_HTML), array('class' => 'question-text'));
    $areyousure .= '<br />';
    $areyousure .= html_writer::tag('b', get_string("answer", "qanda"));
    $areyousure .= '<br />';
    $areyousure .= html_writer::tag('div', format_text($answer, FORMAT_HTML), array('class' => 'answer-text')); //qanda_print_entry_answer($entry, $qanda, $cm);//html_writer::tag('div',format_text($entry->answer, FORMAT_HTML), array('class' => 'answer-text'));

    $areyousure .= "<br /><p>$strareyousuredelete</p>";
    $linkyes = 'deleteentry.php';
    $linkno = 'view.php';
    $optionsyes = array('id' => $cm->id, 'entry' => $entry->id, 'confirm' => 1, 'sesskey' => sesskey(), 'prevmode' => $prevmode, 'hook' => $hook);
    $optionsno = array('id' => $cm->id, 'mode' => $prevmode, 'hook' => $hook);

    echo $OUTPUT->confirm($areyousure, new moodle_url($linkyes, $optionsyes), new moodle_url($linkno, $optionsno));

    echo $OUTPUT->footer();
}
