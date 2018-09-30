# Post Types

When a new thread is started, the user can choose from any of the supported Post Types for the initial post
in the thread. Post Types can provide a custom interface for the form and it's display to users. The included
post types are: 

- Discussion: general purpose prose-entry, as would be seen in most forums. Used for all posts after the first one in a thread.
- Question: Puts the focus on a single question. Allows it to marked as answered.
- Media: Focus on a photo or video
- Poll: Focus on a poll, with additional discussion below.

## Configuration

**Post Types**

Each post type is listed within `application\Config\Forums.php`, within the `$postTypes` array. The alias is displayed
to the user when creating a post. The value is the fully-qualified name of the class to use.

## Class Overview

Each Post Type class must extend the base **Post** entity class. This class provides a few vars that can quickly setup
a post type: 

**$iconClass**

```protected $iconClass = 'post-discussion-icon';```

The class that should be applied to the icon div. The CSS for the theme is expected to provide its own image as a background.

**$formView**

The view file that should be used for the form. This can be fully namespaced, but works best if not, so that the
theme can override the default view.

**$displayView**

The view file that should be used to display the view when not editing it. 

**display()**

This method is used ran whenever the post type is displayed to the user, and it is not being edited.

**displayForm()**

This method is ran whenever the post is being created/edited.

**save()**

This method is called to save the post to the database. This allows any special formatting/etc to be called
before or after a post of this type is saved.
