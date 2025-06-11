=== Yak Herd ===
Contributors: tomatillodesign  
Tags: bulk, taxonomy, categories, posts, pages, admin  
Requires at least: 6.0  
Tested up to: 6.5  
Requires PHP: 7.4  
Stable tag: 0.1  
License: GPL-2.0-or-later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

A no-frills admin tool that lets you paste a plain-text list and bulk-create posts, pages, or taxonomy terms with one click.

== Description ==

**Yak Herd** makes repetitive content entry painless:

* Paste one item per line (or comma-separated)  
* Choose **Taxonomy Terms** or **Posts / Pages**  
* Select a taxonomy & optional parent, or pick a post type  
* Click **Create Items** — done.

Everything happens in the admin; there’s no front-end output, no settings pages, and no database overhead beyond the items you create.

> **No Support / Warranty**  
> This plugin is released “as-is.” Chris Liu-Beers (Tomatillo Design) offers **no support, guarantees, or warranties** of any kind. Use at your own risk and back up your site before bulk operations.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install via the Plugins → Add New screen.  
2. Activate **Yak Herd**.  
3. Go to **Tools → Yak Herd** to start herding.

== Usage ==

1. Paste your list into the textarea (one item per line).  
2. Select **Taxonomy Terms** or **Posts / Pages**.  
3. If creating terms, pick a taxonomy and (optionally) a parent term.  
4. If creating posts, pick a post type.  
5. Click **Create Items**. Success and skip notices appear at the top.

== Frequently Asked Questions ==

= Does this edit or delete existing content? =  
No. It only adds new items; duplicates are skipped.

= Can I import custom fields or meta? =  
Not in version 0.1. Only titles/term names are supported.

== Screenshots ==

1. Simple bulk-creation form in the Tools menu.

== Changelog ==

= 0.1 =  
* Initial release – bulk create taxonomy terms or posts/pages.

== Upgrade Notice ==

0.1 – First public release. Backup your database before bulk imports.
