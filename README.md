# Enovision Filters Plugin for October CMS

- [Introduction](#introduction)
- [License](#license)
- [Installation](#install)
- [Filters](#filters)
    - [What are filters](#what-are-filters)
    - [add_filter](#add-filter)
    - [apply_filters](#apply-filters)
- [How it was done](#how-it-was-done)        

<a name="introduction"></a>
## Introduction
The Enovision Filters plugin is an easy to implement use of Wordpress a like filters. 

Just like in the Wordpress CMS you can use `add_filter` and `apply_filters` to modify your content before it send
to the browser.

This plugin is a convenient addition when using Rainlab's [blog plugin](https://octobercms.com/plugin/rainlab-blog).

<a name="license"></a>
## License
This plugin is licensed under the MIT license agreements.

This plugin is using code that is adapted from the Wordpress CMS.
The license under which the WordPress software is released is the GPLv2 (or later) from the [Free Software Foundation](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).

<a name="install"></a>
## Installation

Install this plugin


<a name="filters"></a>
## Filters

<a name="what-are-filters"></a>
### What are filters?

Filters are PHP functions that make it possible for the developer to modify the content
before it is send to the browser.

For example you want to add a class to all images in the content, then a filter would come in handy.

You apply a filter to modify content that is offered to you by any plugin, where you don't have influence on the format it is delivered in.

<a name="how-to-use"></a>
### How to use it?

The process exists of 2 steps:

#### add_filter

The **add_filter** is to describe the filter itself and to connect it to a callback function. 

It is possible to have more than one filter on the same content. 

Priority can be given to filters and have the outcome of higher priority filter to be the input of any lower priority filter.

#### apply_filters

The **apply_filters** is the actual appliance of the filters you have defined. When applying you tell the system what
filter (tag) needs to be executed and give the just amount of parameters and the output will be a string that 
can be used in your output.

<a name="add-filter"></a>
### add_filter

The `add_filter` is used to define a filter where you describe:

* filter id (tag)
* callback function 
* number of expected arguments
* priority

Quick sample:
```
public function onRun() {
   $filterService = App::make( 'Enovision\FilterService' );
   $filterService->add_filter( 'filter_post_title', [$this, 'filter_post_title'], 10, 1 );
}

public function filter_post_title( $title ) {
   return '<strong>' . $title . '</strong>';
}
```

Explanation of the parameters:

* id ('filter_post_title') - Unique tag to use when you want to apply the filters
* callback function ( [$this, 'filter_post_title'] ] - `filter_post_title` is a function in `$this` component,  
* priority (10) - The priority on execution, you can have more than one filter with the same tag. Lower number is later execution.
* Number of arguments (1) - Number of arguments to expect, in this case one being `$title`.

You can place this code in any component. If you don't have a component you can use the `Filters`
component in the `enovision\filters\components` folder. This can be used as a placeholder for filters.
This could be the case when you use the `blog/post` layout in the Blog plugin from Rainlab and you don't have
a component of your own to add some filters and you don't want to mess with the plugin core of the Blog or any
third party plugin.

## IMPORTANT

When using the *Filters* component to define your filters, make sure you drag it in the layout in the backend.
Otherwise the filters will not be found.
 

If you use your own component to add the filters add this (THIS):

```
<?php

namespace Your\Plugin\Components;

use Illuminate\Support\Facades\App; \\ <-- THIS


class Filters extends \Cms\Classes\ComponentBase {

   ... your code ...

   function strong_title( $title ) {
       return '<strong>' . $title . '</strong>';
   }

   public function onRun() {
      $filterService = App::make( 'Enovision\FilterService' ); <-- THIS
      $filterService->add_filter( 'filter_change_title', array($this, 'strong_title'), 10, 1 );
   }
}
```

<a name="apply-filters"></a>
### apply_filters

You can apply in the backend and in the PHP that you execute on layouts or pages.

#### Applying in the component

Sample (from backend, excerpt)
```
<?php

namespace Enovision\Rocktober\Components;

... other use ...
use Illuminate\Support\Facades\App;

class Postblock extends \Cms\Classes\ComponentBase {

   ... your code ....

   public function onRun() {
      $fs = App::make( 'Enovision\FilterService' ); <-- THIS

      // This filter we apply here, see listPosts function
      $fs->add_filter( 'filter_title_caps', [ $this, 'filter_title_caps' ], 10, 1 );
      // This filter we apply in the layout, see code below
      $fs->add_filter( 'filter_post_title', [ $this, 'filter_post_title' ], 99, 1 );

      $this->posts = $this->page['posts'] = $this->listPosts();
   }

   /* callback */
   public function filter_post_title($title) {
      return '<i>' . $title . '</i>';
   }

   /* callback */
   public function filter_title_caps( $title ) {
      $new = strtoupper( $title );
      return $new;
   }

   /**
    * @see RainLab\Blog\Components\Posts::prepareVars()
    * @return mixed
    */
   protected function listPosts() {
      $fs = App::make( 'Enovision\FilterService' ); // <-- THIS

      $posts = PostModel::isPublished();
		
      ... other $posts related code ...


      $posts->each( function ( $post ) {
         $post['title'] = $fs->apply_filters('filter_title_caps', $post['title']); // <-- THIS
      });

      return $posts;
   }
}

```

#### Applying in the layout

Sample:
```
description = "Default right Sidebar layout"
==
<?php
use Illuminate\Support\Facades\App; // <-- THIS

function onEnd()
{
  $filterService = App::make('Enovision\FilterService'); // <-- THIS
  // or $filterService = resolve('Enovision\FilterService');
  // in that case you won't need the 'use' at the top

  $url = $this->page->getAttribute('url');
  $category = $this->page->getAttribute('category');

  $this['title'] = $this->page->title;

  if ($url === "/posts/:slug?") {
     if($this->category) {
         $this['title'] = $this->category->name;
     } else {
         $this['title'] = 'Blog';
     };
  } elseif ($url === "/post/:slug") {
     $this['title'] = $this->post->title;
  }

  // Here we apply the filter with the code in the component code just earlier
  $this['title'] = $filterService->apply_filters('filter_post_title', $this['title']);
}
?>
==
<!DOCTYPE html>
<html>
{% partial "site/header" %}
... more twig code ...
```

<a name="how-it-was-done"></a>
### How is was done

Since October CMS is based on Laravel, we have used the service functionality.
In fact this is not much more than a dependency on application level. In the case
of this plugin we have called it `FilterService`. 

This Filter Service has code that is almost identical to the code used in the Wordpress CMS
when it concerns filters. Some functions have been removed, since it didn't seems very much required.
We can't remove filters for example.

The `FilterService` is instantiated as a singleton on application level, so it can
be shared. This singleton is using a class `Filters` in the `classes` folder. This class
is instantiated for every unique `add_filter` tag.