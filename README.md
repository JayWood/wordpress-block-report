# WordPress Block Report
A [WordPress CLI](https://github.com/wp-cli/wp-cli) Script designed to provide a report of all Gutenberg Blocks on your site in CSV format ( or in the 
terminal ). Easy to use, multi-site compatible with options for post type and post status. Also, with the ability to customize the fields output.

![PHP 7.4+](https://img.shields.io/badge/PHP-^7.4-green?style=for-the-badge&logo=php)
[![In](https://img.shields.io/static/v1?label=&message=LinkedIn&color=blue&style=for-the-badge&logo=linkedin)](https://www.linkedin.com/in/jerrywoodjr/)
[![Twitter](https://img.shields.io/static/v1?label=&message=Twitter&color=cyan&style=for-the-badge&logo=twitter)](https://twitter.com/plugish/)
[![Instagram](https://img.shields.io/static/v1?label=&message=Instagram&color=pink&style=for-the-badge&logo=instagram)](https://www.instagram.com/therealjaywood/)

## Screenshots

_Showing a simple table output excluding details_
![](https://raw.githubusercontent.com/JayWood/wordpress-block-report/main/assets/table.png)

_Showing the resulting CSV of a full export_
![](https://raw.githubusercontent.com/JayWood/wordpress-block-report/main/assets/csv.png)

## Installation

### Composer
Add this repository to your composer.json
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/JayWood/wordpress-block-report"
        }
    ]
}
```

Or if you're fancy:
`composer config repositories.wp-block-report vcs https://github.com/JayWood/wordpress-block-report`

Require the package
`composer require jaywood/wordpress-block-report`

If you happen to use the sweet [Composer Installers](https://github.com/composer/installers) library, this CLI script is
marked as a `wp-cli-package` for ease of use later.

### Manual

1. Download or clone the repository to `wp-content/mu-plugins/wordpress-block-report/` _( Name is up to you )_
1. Create a new file in `wp-content/mu-plugins` call it whatever you want, I use `init.php`
1. Require the file like so:
```
<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once 'wordpress-block-report/block-report.php';
}
```

Now since your `init.php` file is in `wp-content/mu-plugins` it will always load.

## Usage

**Synopsis**: `wp jwcli block report [--post-type=<post_type>] [--fields=<fields>] [--post-status=<post-status>] [--csv]`

> Multisite flags like --url are supported.

### --post-type=<post-type> 
Supports any post type slug that's registered at run-time. Comma separated lists are supported as well.

**Default:** `post,page`   
**Example:**
```
> wp jwcli block report --post-type=article,publication
```

### --post-status=<post-status>
Supports any post status slug that's registered at run-time. Comma separated lists are supported as well.

**Default:** `any`   
**Example:**
```
$> wp jwcli block report --post-status=publish,in-review
```

### --fields=<fields>
Supports specific fields to return within the report.

**Default:** `post_id,name,attributes,innerHtml,innerContent,innerBlocks`
**Example:**   
```
$> wp jwcli block report --fields=post_id,name,innerBlocks
```

### --csv
Prints out the CSV data to the terminal instead of displaying a table.

**Example:**
```
$> wp jwcli block report --csv
```

#### File Output
To output to a file is quite easy with the proper terminal. Below is how you would do this with a unix terminal.
```
$> wp jwcli block report --csv > out.csv
```

_( A wp-cli plugin by [Jay Wood](https://twitter.com/plugish) )_

