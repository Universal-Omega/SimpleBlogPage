# SimpleBlogPage
Mediawiki extension based on BlogPage with different features and no dependencies. Link: https://www.mediawiki.org/wiki/Extension:SimpleBlogPage

I created SimpleBlogPage to fix some problems I had with BlogPage. It is based on BlogPage version 2.9.0 and differs in several respects:

# How to install

- Download and place the entire SimpleBlogPage directory in a directory called SimpleBlogPage in your extensions/ folder.

- Add the following code at the bottom of your LocalSettings.php:

```php
wfLoadExtension( 'SimpleBlogPage' );
```

- Navigate to Special:Version on your wiki to verify that the extension is successfully installed.

# Information

Major changes:

- Got rid of dependencies on SocialProfile, VoteNY and Comments. So this extension has NO dependencies!
- SimpleBlogPage is NOT compatible with BlogPage, you MUST NOT have both extensions installed at the same time!

Features added:

- Added support for the &#x3C;!--more--&#x3E; tag in the blurb, so that it knows to stop grabbing text for the blurb after the tag. Made the default blurb grab much bigger so you can more easily tell when you forgot to use the &#x3C;!--more--&#x3E; tag.
- Added button to the WikiEditor toolbar to insert the &#x3C;!--more--&#x3E; tag. I drew the logo myself from scratch, pixel by pixel, so there are no copyright/licensing issues. Obviously, you&#x27;ll only see the button if you actually have the WikiEditor extension enabled. 
- Added button to add syntax highlighting (the icon for which I again drew from scratch, to avoid licensing issues), which is a poor man&#x27;s substitute for CodeEditor which I just can&#x27;t seem to get working on my setup. 
- Added "insert quotation" button. Copyright for the icon belongs to Juxn (it's licensed under GPL v2). 
- Added "insert comment" button. Copyright for the icon belongs to Juxn under GPL. The insert comment button generates a colored comment based on your username, it will say "[username]'s notes:" and the color is deterministically generated from a hash of the user's username which means each user will have a different colored comment. 
- Added the author name to the &#x22;created&#x22; line under every listed blog post.
- Added the word count next to the blog post title.

Features removed: 

- Got rid of avatars, votes, comments, &#x201C;most popular&#x201D;, &#x22;opinions count&#x22;, &#x22;about the author&#x22;, and a few other social features 
- Got rid of all special pages except: Articles Home and Create Blog Post
- Blog posts can now only have a single author. Anyone can still edit everyone else&#x27;s blog posts.

Potentially breaking changes:

- Switched to using subpages in order to get the author name for various functions. 
- CreateBlogPost has been changed to create blog posts under User_blog:username/blogposttitle rather than Blog:blogposttitle. 
- The URL User_blog:[username] has been hijacked to display a list of all of the user&#x27;s posts in order of time created with the showUserPosts function 
- Pages with titles of the form User_blog:[username] are not treated as blog posts, in fact they are hidden completely and cannot be viewed normally

Quality of life improvements / pessimizations:

- Got rid of all uses of cache (it wasn&#x2019;t coded properly which was causing the blog home page to always be 15 minutes stale)
- Changed &#x201C;created x days ago&#x201D; in homepage to &#x201C;created by [username] on datetime&#x201D;.
- Added hideContentsub.css to articlesHome and blogpage in extension.json to hide messages like &#x22;go back to ...&#x22; and &#x22;View or restore 2 deleted revisions&#x22;. If you want to see those messages just delete that filename from extension.json.
- Removed padding in main-page-left CSS to get rid of annoying gap between title and content.
- Disallow users from creating blog posts under another user&#x27;s name. Make sure to disallow &#x27;move&#x27; permissions for normal users as they can still move pages to under other user&#x27;s names. Do this by adding the line
&#x60;&#x60;&#x60;php
    $wgGroupPermissions[&#x27;user&#x27;][&#x27;move&#x27;] = false;
&#x60;&#x60;&#x60;
    to your LocalSettings.php
- Increased number of posts on home page to ALL blog posts (well, 1 billion) rather than just the last 25
- Changed the right hand side &#x22;other editors&#x22; box to only appear when other people have edited the page. I got rid of the avatars and replaced them with just the usernames of the editors.

Bug fixes:

- Fixed misspelling of &#x201C;cahce&#x201D; to &#x201C;cache&#x201D;, which was a critical bug that was breaking homepage. Then I removed cache completely. 
- Got rid of duplicate categories bottom bar

Refactoring:

- Got rid of/factored out most of the duplicated code (and there was quite a lot of it). 
- Changed the getAuthors function to grab the author out of the basetext (root of subpage) rather than parsing it out of categories using some hacky regex. 

Protip: https://validator.w3.org/ is a very useful tool for finding mismatched &#x3C;div&#x3E; and &#x3C;/div&#x3E; tags. 

Btw, I haven't audited this code for security. In general, I would highly recommend against running PHP code on a public facing server. It's probably fine to run this (and mediawiki) on your local intranet disconnected from the internet (e.g. put it on a separate VLAN). 
