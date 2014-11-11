<?php
    require_once('app_setup.php');
	$pageTitle =  ucfirst(util_lang('home'));
    require_once('app_head.php');

//    $notebooks = $USER->getAccessibleNotebooks(Action::getOneFromDb(['name'=>'view'],$DB));
//    $num_notebooks = count($notebooks);

	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

//		echo "<hr />";
//		echo '<h3>'.ucfirst(util_lang('you_possesive')).' '.ucfirst(util_lang('notebooks')).'</h3>';

		# is system admin?
		if ($USER->flag_is_system_admin) {
            // TODO: show special admin-only stuff
		}
    } else
    {
        ?>
        <div class="hero-unit">
            <h2><?php echo LANG_INSTITUTION_NAME; ?></h2>

            <h1><?php echo LANG_APP_NAME; ?></h1>

            <br />

            <p><?php echo util_lang('app_short_description'); ?></p>

            <p><?php echo util_lang('app_sign_in_msg'); ?></p>

        </div>
        <?php
//        if ($num_notebooks > 0) {
//            echo "<hr />\n";
//            echo '<h3>'.ucfirst(util_lang('public')).' '.ucfirst(util_lang('notebooks')).'</h3>';
//        }
    }

//    if ($num_notebooks > 0) {
//        $counter = 0;
//        echo "<ul class=\"unstyled\" id=\"list-of-user-notebooks\" data-notebook-count=\"$num_notebooks\">\n";
//        foreach ($notebooks as $notebook) {
//            $counter++;
//            echo $notebook->renderAsListItem('notebook-item-'.$counter)."\n";
//        }
//        echo "</ul>\n";
//    }

//    if ($USER->canActOnTarget($ACTIONS['create'],new Notebook(['DB'=>$DB]))) {
//        ?>
<!--        <input type="button" id="btn-add-notebook" value="<?php echo util_lang('add_notebook'); ?>"/>--><?php
//    }
?><hr/>
    <ul class="splash-links inline">
        <li><a id="notebooks-splash-link" class="splash-link" href="<?php echo APP_ROOT_PATH; ?>/app_code/notebook.php?action=list"><?php echo ucfirst(util_lang('notebooks')); ?></a></li>
        <li><a id="metadata-structures-splash-link" class="splash-link" href="<?php echo APP_ROOT_PATH; ?>/app_code/metadata_structure.php?action=list"><?php echo ucfirst(util_lang('all_metadata')); ?></a></li>
        <li><a id="metadata-term-sets-splash-link" class="splash-link" href="<?php echo APP_ROOT_PATH; ?>/app_code/metadata_term_set.php?action=list"><?php echo ucfirst(util_lang('all_metadata_term_sets')); ?></a></li>
    </ul>
<?php
require_once('foot.php');
?>