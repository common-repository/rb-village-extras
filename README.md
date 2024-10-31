=== Retrobadger Village Extras (rbve) ===

Contributors: retrodans
Tags: village, community, post-types
Requires at least: 4.5
Tested up to: 6.4.3
Stable Tag: 1.1.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A collection of extra types and pieces of bonus shortcodes/functionality to help when developing a village website

== Description ==

A collection of extra types and pieces of bonus shortcodes/functionality to help when developing a village website using Wordpress.  The point is to making it quicker and easier for volunteers to build the sites to help their local community grow without having to develop lots of code, or hack away using a blog post for other types of content.

= Village Documents =

* New post type
* Type tagging (eg. Agenda/Minutes/Newsletter)
* Shortcodes for listing and grouping
* Gives access to administrator, and also some rights to editors OOTB
* Use cases: A page listing all council minutes by month, A page listing all newsletters by month, a pod showing x most recent documents uploaded to the site

= What this plugin doesn't do =

* Events * There are so many plugins already that do this well.  Two that spring to mind
  * Events Manager * This is the one I currently use, as time.ly has started charging for more, therefore any events features are tested with this.
  * All in one events calendar (by time.ly)
  * The Events Calendar * Simple output allowing some advanced theming with css without hacking away too much
* User role management * this plugin will create now permissions, but to utilize them, you will need a management plugin
  * User Role Editor * Possibly a few too many options, but does all you need
* Image gallery * There are so many plugins already that do this well.
  * Envira gallery lite * fast, simple, and seems reliable

= More info =

For further information, enable the module, and go to the settings/info page for the individual post types you would like more information on, as a lot has been put onto there for the end user.

== Screenshots ==

1. Editing a document
2. Rendering lists using the shortcodes

== Dependencies ==

* cmb2 plugin
= Recommendations =
* An event plugin (eg. the [All-in-one event calendar from time.ly](https://wordpress.org/plugins/all-in-one-event-calendar/))
* "User Role Editor" plugin (can be handy IF you are playing with complex roles)

== Installation ==

1. Ensure dependencies have been activated (eg. cmb2)
1. Activate the rbve plugin
1. Refresh admin UI (the 'Village Documents' item in the LHS may not appear instantly)
1. You may want to check the homepage as some themes hardcode how to render posts on the homepage.  This will mean the documents may look broken until you update as per troubleshooting section for this plugin.

=== Optional ===

1. Copy/modify the files from /template into your theme directory (eg. themes/colorway) if relevant (tweaks layout for the new types)

== Shortcodes ==

This plugin supplies a lot of shortcodes, a few of the ones we use are:

* `[rbve_events]` : List of events (using a compatible events module)
  * `proximity` : How close to today should the listed events be (for events these are events in the future)
    * month
  * `categories` : What categories should the events be tagged with (uses category ID)
    * 193
* `[rbve_docs]` : List of document posts uplodaded to the site.
  * `labeltype` : What format should the link to the document use for its text
    * docname
    * monthyear
    * month
  * `limit-year` : Whether we should only show documents for a particular year.
    * 2024
  * `proximity` : How close to today should the listed events be (for documents these are events in the past)
    * month
  * `doctypes` : What type of document to show, uses the tags set when creating the document post
    * council-agenda
    * council-minutes
  * `grouptitle` : Group titles are often added automatically, this allows you to disable this title
    * disabled

== Usage ==

1. Click the 'Village Documents' in the LHS

== Frequently Asked Questions ==

Please, ask me questions, or give me suggestions, the more the better.

= Why do we need a plugin for this? =

Whilst some of us may know how to make custom post-types and shortcodes, not everyone does.  Therefore this plugin is
to help anyone get a village website running with some basic post-types, template code, and ideas on other plugins you
may also want to use.

== Troubleshooting ==

=== Links on homepage blog are going to a 404 ===

This is usually down to how your theme is applying the homepage loop, the best fix for this (at present) would be to jump into your themes template file and find the while loop for the blog area.  Once you have this you can go inside the `<li>` and past the below code

```php
<?php if ($post->post_type === 'rbve_doc') {
    $post_meta = get_post_meta($post->ID);
    print rbve_doc_template($post->post_title, $post_meta['_rbve_doc'][0]);
  } else { ?>
```

You will then want to find the closing `</li>` and add in this closing code
`<?php } ?>`

Please do let us know though, as there should be filters handling this now.

= The new post types are not available =

This can happen when you need to flush the caches, to get around this simply:

* Goto `Settings > Permalinks`
* Click `Save`

= Listing pages are just showing blogs (not businesses or clubs) =

* Check the variables being used (eg. use a new $args not the passed in $atts array)

== Future Milestones ==

* 1.0.x
  * If no documents returned, give some text (can be overridden with shortcode)
  * improvements from feedback and usage
  * new shortcodes
  * Document management improvements
  * Integration with Publicize (jetpack?) * so on publish, it will share the download link (to the file) with something like '<postname> : New <tagname> uploaded <linktofile>'
* 1.1.0 * Local business/organisation post-type and shortcodes (eg. contact details, opening hours)

= Other ideas =

* Ability to add 'walks and cycle rides' to the site in a listable way, with printable maps
* Integration of events with business/organisation pages
* Ability to link an event to a business (may require time.ly)
* Some new shortcodes for the time.ly event calendar to add theming flexibility, and potential embedding on business pages
* Can the templates be handled better (eg. without template files?)

== Related reading ==

= This plugin =

* https://www.smashingmagazine.com/2012/11/complete-guide-custom-post-types/
* http://codex.wordpress.org/Plugin_API/Filter_Reference
* http://codex.wordpress.org/Plugin_API/Filter_Reference/the_content

= General WP links =

* https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/

== Changelog ==

= 1.0.0 =

Initial release of this plugin with village document support and shortcodes

= 1.0.1 =

* New filter by year (eg. limit results to x most recent)
* Fix homepage view
* Added 'special council minutes'

= 1.0.2 =

* Fix for date issue
* Added labeltype filter to just show month (without year)
* show filetype on download link
* add option to now show filetype in shortcode
* add annual minutes
* add ability to hide the group title

= 1.0.3 =

= 1.0.4 =

* Added a new method to handle the url for new custom post types

= 1.0.5 =

IMPORTANT * when you update, you will want to quickly edit your documents, and update the date field, this will implement to the new ordering method

* Add council agendas to the document types
* Have it say 'Add New Document' rather than 'Add New Page'
* A filter to show all-council-minutes (both special and regular) * special minutes just have a flag next to them (special)
* Need to fix this date issue.  Insert a date field, and use that if set.  If not set, then use the published date by default.

= 1.0.6 =

* Added Local businesses/organisations
* Added local clubs/societies

= 1.1.8 =

* Various updates and fixes (including businesses and clubs showing regular posts)

= 1.1.9 =

* Shortcode updates, including "Proximity" filter

= 1.1.10 =

* Categories filter for our new rbve_events styled shortcode
