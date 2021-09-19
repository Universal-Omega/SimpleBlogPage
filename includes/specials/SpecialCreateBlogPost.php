<?php
/**
 * A special page to create new blog posts (pages in the NS_USER_BLOG namespace).
 * Based on the CreateForms extension by Aaron Wright and David Pean.
 *
 * @file
 * @ingroup Extensions
 */

use MediaWiki\MediaWikiServices;

class SpecialCreateBlogPost extends FormSpecialPage {
	public function __construct() {
		parent::__construct( 'CreateBlogPost', 'createblogpost' );
	}

	/**
	 * @return array
	 */
	protected function getFormFields() {
		$formDescriptor = [];
		$out = $this->getOutput();
		
		$out->addModuleStyles( 'ext.simpleBlogPage.create.css' );
		$out->addModules( 'ext.simpleBlogPage.create.js' );

		if ( ExtensionRegistry::getInstance()->isLoaded( 'WikiEditor' ) && $this->getContext()->getUser()->getOption( 'usebetatoolbar' ) ) {
			$out->addModules( 'ext.wikiEditor' );
		}

		$cloud = new BlogTagCloud( 1000 );

		// Show the blog rules, if the message containing them ain't empty
		$message = $this->msg( 'blog-create-rules' );
		if ( !$message->isDisabled() ) {
			$formDescriptor['rules'] = [
				'type' => 'info',
				'default' => $message->parse()
			];
		}

		$formDescriptor['title'] = [
			'type' => 'text',
			'label-message' => 'blog-create-title',
			'default' => $this->getRequest()->getVal( 'blogtitle' ),
			'required' => true,
			'cssclass' => 'createblogpost-input',
			'id' => 'title'
		];

		$formDescriptor['content'] = [
			'type' => 'textarea',
			'label-message' => 'blog-create-text',
			'default' => $this->getRequest()->getVal( 'blogcontent' ),
			'required' => true,
			'cssclass' => 'createblogpost-input',
			'id' => 'wpTextbox1'
		];

		$formDescriptor['categories'] = [
			'type' => 'multiselect',
			'default' => [],
			'options' => [],
			'dropdown' => true,
			'label-message' => 'blog-create-categories',
			'help-message' => 'blog-create-category-help',
			'cssclass' => 'createblogpost-input',
			'id' => 'pageCtg'
		];

		foreach ( $cloud->tags as $tag => $att ) {
			$formDescriptor['categories']['options'][$tag] = $tag;
		}

		$formDescriptor['copyrightwarning'] = [
			'type' => 'info',
			'default' => $this->displayCopyrightWarning(),
			'raw' => true
		];

		$formDescriptor['preview'] = [
			'type' => 'check',
			'label-message' => 'showpreview',
		];

		return $formDescriptor;
	}

	/**
	 * @param array $formData
	 * @return bool
	 */
	public function onSubmit( array $formData ) {
		$out = $this->getOutput();
		$user = $this->getUser();
		$request = $this->getRequest();
		$services = MediaWikiServices::getInstance();

		$userSuppliedTitle = $formData['title'];
		$title = Title::makeTitleSafe( NS_USER_BLOG, $user->getName() . '/' .  $userSuppliedTitle );

		// Localized variables that will be used when creating the page
		$contLang = $services->getContentLanguage();
		$localizedCatNS = $contLang->getNsText( NS_CATEGORY );
		$today = $contLang->date( wfTimestampNow() );

		// Create the blog page if it doesn't already exist
		$page = WikiPage::factory( $title );
		if ( $page->exists() ) {
			$out->setPageTitle( $this->msg( 'errorpagetitle' ) );
			$out->addWikiMsg( 'blog-create-error-page-exists' );
			return;
			} elseif ( $formData['preview'] ?? 0 ) {
				// Previewing a blog post
				$out->setPageTitle( $this->msg( 'preview' ) );
				$out->addHTML(
					'<div class="previewnote"><p>' .
					Html::warningBox( $this->msg( 'previewnote' )->text() ) .
					'</p></div>'
				);
				if ( $user->isAnon() ) {
					$out->wrapWikiMsg(
						"<div id=\"mw-anon-preview-warning\" class=\"warningbox\">\n$1</div>",
						'anonpreviewwarning'
					);
				}

				// Modeled after CreateAPage's CreatePageCreateplateForm#showPreview
				$userSuppliedTitle = $formData['title'];
				$title = Title::makeTitleSafe( NS_USER_BLOG, $this->getUser()->getName() . $userSuppliedTitle );

				if ( is_object( $title ) ) {
					$parser = $services->getParser();
					$parserOptions = ParserOptions::newFromUser( $user );
					$preparsed = $parser->preSaveTransform(
						$formData['content'], // We're intentionally ignoring categories (etc.) here
						$title,
						$user,
						$parserOptions,
						true
					);

					$previewableText = $out->parseAsContent( $preparsed ); // $parserOutput->getText( [ 'enableSectionEditLinks' => false ] );

					$out->addHTML( $previewableText );
				}
				return;
			} else {
				// The blog post will be by default categorized into two
				// categories, "Articles by User $1" and "(today's date)",
				// but the user may supply some categories themselves, so
				// we need to take those into account, too.
				$categories = [
					'[[' . $localizedCatNS . ':' .
						$this->msg(
							'blog-by-user-category',
							$this->getUser()->getName()
						)->inContentLanguage()->text() .
					']]' . "\n" .
					"[[{$localizedCatNS}:{$today}]]"
				];

				$userSuppliedCategories = $formData['categories'];
				if ( !empty( $userSuppliedCategories ) ) {
					// Explode along commas so that we will have an array that
					// we can loop over
					foreach ( $userSuppliedCategories as $cat ) {
						$cat = trim( $cat ); // GTFO@excess whitespace
						if ( !empty( $cat ) ) {
							$categories[] = "[[{$localizedCatNS}:{$cat}]]";
						}
					}
				}

				// Convert the array into a string
				$wikitextCategories = implode( "\n", $categories );

				// Perform the edit
				$pageContent = ContentHandler::makeContent(
					// Instead of <vote />, Wikia had Template:Blog Top over
					// here and Template:Blog Bottom at the bottom, where we
					// have the comments tag right now
					'<!--start text-->' . "\n" .
						$formData['content'] . "\n\n" .
						$wikitextCategories .
						"\n__NOEDITSECTION__",
					$page->getTitle()
				);
				
				$page->doEditContent(
					$pageContent,
					$this->msg( 'blog-create-summary' )->inContentLanguage()->text()
				);

				// Redirect the user to the new blog post they just created
				$out->redirect( $title->getFullURL() );
			}

		return true;
	}

	/**
	 * @return string
	 */
	protected function getDisplayFormat() {
		return 'ooui';
	}

	/**
	 * Display the standard copyright notice that is shown on normal edit page,
	 * on the upload form etc.
	 *
	 * @return string HTML
	 */
	public function displayCopyrightWarning() {
		global $wgRightsText;

		if ( $wgRightsText ) {
			$copywarnMsg = 'copyrightwarning';
			$copywarnMsgParams = [
				'[[' . $this->msg( 'copyrightpage' )->inContentLanguage()->text() . ']]',
				$wgRightsText
			];
		} else {
			$copywarnMsg = 'copyrightwarning2';
			$copywarnMsgParams = [
				'[[' . $this->msg( 'copyrightpage' )->inContentLanguage()->text() . ']]'
			];
		}
		return '<div class="copyright-warning">' .
			$this->msg( $copywarnMsg, $copywarnMsgParams )->parseAsBlock() .
			'</div>';
	}
}
