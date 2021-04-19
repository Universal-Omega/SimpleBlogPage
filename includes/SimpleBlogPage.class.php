<?php
/**
 * Class for handling the viewing of pages in the NS_USER_BLOG namespace.
 *
 * @file
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;

require_once "specials/lib/common.php";

class SimpleBlogPage extends Article {

	public $title = null;

	/**
	 * @var Author name and ID, set by getAuthor.
	 */
	public $AuthorName = '';
	public $AuthorID = 0;

	public function __construct( Title $title ) {
		parent::__construct( $title );
		$this->getAuthor();
	}

	/**
	 * Sets the 2 variables $AuthorName and $AuthorID.
	 */
	function getAuthor() {
		$this->AuthorName = Title::newFromText( $this->getTitle()->getText() )->getRootText();
		$authorObj = User::newFromName( $this->AuthorName ); 
		$this->AuthorID = $authorObj->getId();
	}

	/**
	 * Show all of the user's blog posts
	 *
	 * @param string $user Whose blog posts to show? 
	 */
	function showUserPosts( $user, $output ) {
		// Add CSS
		$output->addModuleStyles( 'ext.blogPage.articlesHome' );

		// Determine the page title and set it
		$name = wfMessage( 'ah-all-posts' );

		$pagetitle = $this->AuthorName . "'s blog posts";

		$output->setPageTitle( $pagetitle );

		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		$today = $contLang->date( wfTimestampNow() );

		// Start building the HTML output
		$output2 = '<div class="main-page-left">';
		$output2 .= '<div class="logged-in-articles">';
		$output2 .= '<p class="main-page-sub-links"><a href="' .
			htmlspecialchars( SpecialPage::getTitleFor( 'ArticlesHome' )->getFullURL() ) . '">' .
			wfMessage( 'ah-all-posts' ) . '</a>';

		$output2 .= getNewestPosts(false, $this->AuthorName);

		$output2 .= '</div>';
		$output2 .= '</div>';

		$output2 .= '<div class="visualClear"></div>';

		$output->addHTML( $output2 );
	}

	public function view() {
		global $wgSimpleBlogPageDisplay;

		$context = $this->getContext();
		$user = $context->getUser();
		$output = $context->getOutput();
		
		$sk = $context->getSkin();

		wfDebugLog( 'SimpleBlogPage', __METHOD__ );

		// Show the user's list of blog posts when viewing Blog:Username
		// Show the list regardless of whether the actual page exists or not
		$exploded = explode("/", $this->getTitle()->getText());
		if ( sizeof( $exploded ) < 2 ) {
			$this::showUserPosts( $user, $output );
			return '';
		}

		// Don't throw a bunch of E_NOTICEs when we're viewing the page of a
		// nonexistent blog post
		if ( !WikiPage::factory( $this->getTitle() )->getID() ) {
			parent::view();
			return '';
		}

		$output->addHTML( "\t\t" . '<div id="blog-page-container">' . "\n" );

		if ( $wgSimpleBlogPageDisplay['leftcolumn'] == true and $this->recentEditors() != '') {
			$output->addHTML( "\t\t\t" . '<div id="blog-page-left">' . "\n" );

			$output->addHTML( "\t\t\t\t" . '<div class="blog-left-units">' . "\n" );

	//		$output->addHTML(
	//			"\t\t\t\t\t" .  "\n"
	//		);

			$output->addHTML( $this->recentEditors() );

			$output->addHTML( '</div>' . "\n" );
			
			$output->addHTML( "\t\t\t" . '</div><!-- #blog-page-left -->' . "\n" );
		}


		$output->addHTML( '<div id="blog-page-middle">' . "\n" );
		$output->addHTML( $this->getByLine() );

		$output->addHTML( "\n<!--start Article::view-->\n" );
		parent::view();

		/**
		 * The page title is being set here before the Article::view()
		 * call above, which overrides whatever we set if we set the title
		 * above that line.
		 *
		 * @see https://phabricator.wikimedia.org/T143145
		 */
		$output->setHTMLTitle( $this->getTitle()->getSubpageText() );
		$output->setPageTitle( $this->getTitle()->getSubpageText() );

		// Get categories
		// note from 1f604: This actually duplicates the categories that are naturally created by mediawiki, so I've commented it out.
		//$cat = $sk->getCategoryLinks();
		//if ( $cat ) {
		//	$output->addHTML( "\n<div id=\"catlinks\" class=\"catlinks\">{$cat}</div>\n" );
		//}

		$output->addHTML( "\n<!--end Article::view-->\n" );

		$output->addHTML( '</div>' . "\n" );

		$output->addHTML( '<div class="visualClear"></div>' . "\n" );
		$output->addHTML( '</div><!-- #blog-page-container -->' . "\n" );
	}


	/**
	 * Get the creation date of the page with the given ID from the revision
	 * table.
	 * The return value of this function can be passed to the various $wgLang
	 * methods for i18n-compatible code.
	 *
	 * @param int $pageId Page ID number
	 * @return int Page creation date
	 */
	public static function getCreateDate( $pageId ) {
			wfDebugLog( 'SimpleBlogPage', "Loading create_date for page {$pageId} from database" );
			$dbr = wfGetDB( DB_REPLICA );
			$createDate = $dbr->selectField(
				'revision',
				'rev_timestamp', // 'UNIX_TIMESTAMP(rev_timestamp) AS create_date',
				[ 'rev_page' => $pageId ],
				__METHOD__,
				[ 'ORDER BY' => 'rev_timestamp ASC' ]
			);

		return $createDate;
	}

	/**
	 * Get the "by X, Y and Z" line, which also contains other nifty
	 * information, such as the date of the last edit and the creation date.
	 *
	 * @return string
	 */
	public function getByLine() {
		$lang = $this->getContext()->getLanguage();

		$count = 0;

		// Get date of last edit
		$timestamp = WikiPage::factory( $this->getTitle() )->getTimestamp();
		$edit_time = [];
		$edit_time['date'] = $lang->date( $timestamp, true );
		$edit_time['time'] = $lang->time( $timestamp, true );
		$edit_time['datetime'] = $lang->timeanddate( $timestamp, true );

		// Get date of when article was created
		$timestamp = self::getCreateDate( WikiPage::factory( $this->getTitle() )->getID() );
		$create_time = [];
		$create_time['date'] = $lang->date( $timestamp, true );
		$create_time['time'] = $lang->time( $timestamp, true );
		$create_time['datetime'] = $lang->timeanddate( $timestamp, true );

		$output = '<div class="blog-byline">' . wfMessage( 'blog-by' )->escaped() . ' ';

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$authors = $linkRenderer->makeLink( Title::newFromText( $this->AuthorName, NS_BLOG ), $this->AuthorName );

		$output .= $authors;

		$output .= '</div>';

		$edit_text = '';
		if ( $create_time['datetime'] != $edit_time['datetime'] ) {
			$edit_text = '<br>' .
				wfMessage(
					'blog-last-edited',
					$edit_time['datetime'],
					$edit_time['date'],
					$edit_time['time']
				)->escaped();
		}
		$output .= "\n" . '<div class="blog-byline-last-edited">' .
			wfMessage(
				'blog-created',
				$create_time['datetime'],
				$create_time['date'],
				$create_time['time']
			)->escaped() .
			" {$edit_text}</div>";
		return $output;
	}

	/**
	 * Get the editors for the current blog post from the revision
	 * table.
	 *
	 * @return array Array containing each editors' user ID and user name
	 */
	public function getEditorsList() {
		$pageTitleId = WikiPage::factory( $this->getTitle() )->getID();
		$editors = [];

		wfDebugLog( 'SimpleBlogPage', "Loading recent editors for page {$pageTitleId} from DB" );
		$dbr = wfGetDB( DB_REPLICA );

		$where = [
			'revactor_page' => $pageTitleId,
			'actor_user IS NOT NULL', // exclude anonymous editors
			"actor_name <> 'MediaWiki default'", // exclude MW default
		];

		// Exclude the author from the editors list
		$where[] = 'actor_user <> ' . $this->AuthorID;

		$res = $dbr->select(
			[ 'revision_actor_temp', 'revision', 'actor' ],
			[ 'DISTINCT revactor_actor', 'actor_name' ],
			$where,
			__METHOD__,
			[ 'ORDER BY' => 'actor_name ASC', 'LIMIT' => 1000000000 ],
			[
				'actor' => [ 'JOIN', 'actor_id = revactor_actor' ],
				'revision_actor_temp' => [ 'JOIN', 'revactor_rev = rev_id' ]
			]
		);

		foreach ( $res as $row ) {
			$editors[] = [
				'actor' => $row->revactor_actor
			];
		}

		return $editors;
	}

	/**
	 * Get the usernames of the people who recently edited this blog post, if
	 * this feature is enabled in SimpleBlogPage config.
	 *
	 * @return string HTML or nothing
	 */
	public function recentEditors() {
		global $wgSimpleBlogPageDisplay;

//		if ( $wgSimpleBlogPageDisplay['recent_editors'] == false ) {
//			return '';
//		}

		$editors = $this->getEditorsList();

		$output = '';

		if ( count( $editors ) > 0 ) {
			$output .= '<div class="recent-container">
			<h2>' . wfMessage( 'blog-recent-editors' )->escaped() . '</h2>';

			foreach ( $editors as $editor ) {
				$actor = User::newFromActorId( $editor['actor'] );
				$actorname = $actor->getName();
				$userTitle = Title::makeTitle( NS_USER, $actorname );


				$output .= '<a href="' . htmlspecialchars( $userTitle->getFullURL() ) .
					'">' . $actorname  . '</a></br>';
			}

			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Get the first $maxChars characters of a page.
	 *
	 * @param string $pageTitle Page title
	 * @param int $namespace Namespace where the page is in
	 * @param int $maxChars Get the first this many characters of the page
	 * @param string $fontSize Font size; small, medium or large
	 * @return string First $maxChars characters from the page
	 */
	public function getBlurb( $pageTitle, $namespace, $maxChars, &$wordcount, $fontSize = 'small' ) {
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();

		// Get raw text
		$title = Title::makeTitle( $namespace, $pageTitle );
		$article = new Article( $title );
		$content = $article->fetchRevisionRecord()->getContent(
			SlotRecord::MAIN,
			RevisionRecord::FOR_THIS_USER,
			$this->getContext()->getUser()
		);
		$text = ContentHandler::getContentText( $content );
		$wordcount = strval(str_word_count($text));

		// Remove some problematic characters
		// Not sure if this is actually needed
		$text = str_replace( '* ', '', $text );
		$text = str_replace( '===', '', $text );
		$text = str_replace( '==', '', $text );
		$text = preg_replace( '@<youtube[^>]*?>.*?</youtube>@si', '', $text ); // <youtube> tags (provided by YouTube extension)
		$text = preg_replace( '@<video[^>]*?>.*?</video>@si', '', $text ); // <video> tags (provided by Video extension)
		if ( ExtensionRegistry::getInstance()->isLoaded( 'Video' ) ) {
			$videoNS = $contLang->getNsText( NS_VIDEO );
			if ( $videoNS === false ) {
				$videoNS = 'Video';
			}
			// [[Video:]] links (provided by Video extension)
			$text = preg_replace( "@\[\[{$videoNS}:[^\]]*?].*?\]@si", '', $text );
		}
		$localizedCategoryNS = $contLang->getNsText( NS_CATEGORY );
		$text = preg_replace( "@\[\[(?:(c|C)ategory|{$localizedCategoryNS}):[^\]]*?].*?\]@si", '', $text ); // categories
		// $text = preg_replace( "@\[\[{$localizedCategoryNS}:[^\]]*?].*?\]@si", '', $text ); // original version of the above line

		// Start looking at text after content, and force no Table of Contents
		$pos = strpos( $text, '<!--start text-->' );
		if ( $pos !== false ) {
			$text = substr( $text, $pos );
		}

		$text = '__NOTOC__ ' . $text;
		$exploded = explode("<!--more-->", $text);
		$text = rtrim($exploded[0]); //remove trailing whitespace (the whitespace before the <!--more--> tag)

		// Run text through parser
		$blurbText = $article->getContext()->getOutput()->parseAsContent( $text );
		$blurbText = strip_tags( $blurbText );

		$pos = strpos( $blurbText, '[' );
		if ( $pos !== false ) {
			$blurbText = substr( $blurbText, 0, $pos );
		}

		// Take first N characters, and then make sure it ends on last full word
		$max = 20000;
		if ( strlen( $blurbText ) > $max ) {
			$blurbText = strrev( strstr( strrev( substr( $blurbText, 0, $max ) ), ' ' ) );
		}

		// Prepare blurb font size
		$blurbFont = '<span class="listpages-blurb-size-';
		if ( $fontSize == 'small' ) {
			$blurbFont .= 'small';
		} elseif ( $fontSize == 'medium' ) {
			$blurbFont .= 'medium';
		} elseif ( $fontSize == 'large' ) {
			$blurbFont .= 'large';
		}
		$blurbFont .= '">';

		// Fix multiple whitespace, returns etc
		$blurbText = trim( $blurbText ); // remove trailing spaces
		$blurbText = preg_replace( '/\s(?=\s)/', '', $blurbText ); // remove double whitespace
		$blurbText = preg_replace( '/[\n\r\t]/', ' ', $blurbText ); // replace any non-space whitespace with a space

		$morelink = '';
		if ( sizeof($exploded) > 1){
			$morelink = ' <a href="' .
			htmlspecialchars( $title->getFullURL() ) . '">' . wfMessage( 'blog-more' )->escaped() .
			'</a></span>';
		}

		return $blurbFont . $blurbText . $morelink;
	}
}
