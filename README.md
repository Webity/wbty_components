#Wbty Component Library

The Wbty Component Library (and plugin) are the basis for the variety of components that Webity offers for Joomla.

The `lib_wbty_components` folder contains a variety of classes that are used by our components to improve the base functionality offered by Joomla.

For our design staff, we also have the folder `jhtml` which contains a file `jhtml.php`. This file can be used to add easy to call `js` and `html` snippets that are frequently used throughout websites.

The `plg_wbty_components` contains a very simple system plugin that sets up Joomla's autoloader to recognize our `Wbty` prefix for each of our classes. The folder structure for the library is set up to match what the autoloader expects.

This plugin also registers the `WbtyJhtml` class so that `JHtml` calls can be made throughout the site.

## Using `JHtml` code snippets

There are two parts to setting up and using a code snippet.

### Step 1:

Create a function that handles including the code snippet that you want. In many cases, the function should echo html to be included at the point that the function is called. It may also push scripts to `JFactory::getDocument()` for inclusion in the `head` tag.

Our first function, and example, is the `videoresizer` method. This function just grabs a bunch of jQuery script and pushes it to the document's script section.

### Step 2:

To actually have the function get processed, you make a simple call to `JHtml`. Using our `videoresizer` function again, it would look something like this:

	JHtml::_('wbty.videoresizer');

That's it!

If you want to add parameters to the function, you just add them to the declaration of `videoresizer` in `jhtml.php` and then pass them like so:

	JHtml::_('wbty.videoresizer', 'parameter1', $parameter2);

Commit these to this repo and they should be available on all sites using the latest version of WBTY Components!
