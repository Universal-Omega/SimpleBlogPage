<?php


wfLoadExtension( 'SimpleBlogPage' );

$wgNamespacesWithSubpages[NS_BLOG] = true;
$wgNamespacesWithSubpages[500] = true;
$wgNamespacesWithSubpages[501] = true;

//uncomment these if necessary:
//$wgAllowUserJs = true;
//$wgUseSiteJs = true;

//uncomment these
$wgResourceModules['ext.AddMoreButton']['scripts'][] = 'extensions/SimpleBlogPage/resources/js/AddMoreButton.js';
$wgHooks['EditPage::showEditForm:initial'][] = 'addModule';

function addModule(EditPage $editPage, OutputPage $output ) {
	$output->addModules( 'ext.AddMoreButton' );
}

?>
