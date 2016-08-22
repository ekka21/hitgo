#Hitgo

A command line tool that deploys a PHP application
to hitgo.io server with ease.

## Prerequisites

- PHP > 5.7
- [Composer](https://getcomposer.org/doc/00-intro.md#globally)

## Installation

    composer global require "hitgo/installer"

## Example usages
By default, Hitgo uses `public` directory as a web root. If you don't have `public` directory, Hitgo automatically looks for `htdocs`, `index.php`, or index.html` in your project.

    $ mkdir -p helloworld.com/public
    $ cd helloworld.com && echo '<?php echo "It works!";' >> public/index.php
    $ hitgo

![](https://media.giphy.com/media/3o7TKqI8ZFv83VZRqo/giphy.gif)

## Author Information

Hitgo is created by [Ekkachai Danwanichakul](http://api.hitgo.com/)

