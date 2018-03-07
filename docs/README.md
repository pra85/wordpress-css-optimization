# CSS Optimization Documentation
 
This documentation belongs to the WordPress plugin [CSS Optimization](https://wordpress.org/plugins/css-optimization/).

**The plugin is in beta. Please submit your feedback on the [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).**

The plugin provides in a advanced CSS optimization toolkit. Critical CSS, minification, concatenation, async loading, advanced editor, CSS Lint, Clean CSS (professional), beautifier and more.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).

## Getting started

1. CSS Code Optimization
2. CSS Delivery Optimization
3. Critical CSS / Above The Fold Optimization

# CSS Code Optimization

CSS code optimization consists of two main parts: minification and concatenation. Minification compresses CSS to reduce the size while concatenation merges multiple stylesheets into a single stylesheets for faster download performance.

## CSS Minify

The plugins provides the option to minify CSS code using [CSSMin](https://github.com/natxet/CssMin), a PHP based CSS compressor. You can control which stylesheets are included in the minification by enabling the filter option. 

#### CSS Minify Filter

When the filter is enabled you can chose the filter mode `Include List` or `Exclude List`. The Include List option excludes all stylesheets by default and only minifies stylesheets on the list, while the Exclude List option includes all stylesheets by default except the stylesheets on the list.

The filter list accepts parts of HTML stylesheet elements which makes it possible to match based on both CSS code and HTML attributes such as `id="stylesheet-id"`.

#### CSS Minify Options

The CSS minify options are documented on the Google Code Wiki page of CSSMin:

Filters: https://code.google.com/archive/p/cssmin/wikis/MinifierFilters.wiki
Plugins: https://code.google.com/archive/p/cssmin/wikis/MinifierPlugins.wiki

## CSS Concat

The plugins provides advanced functionality to concatenate stylesheets. It includes the option to create concat groups, e.g. bundle the most used stylesheets while keeping individual page related stylesheets out of the concatenation, an option to merge Media Query based stylesheets and to concatenate inline stylesheets.

You can chose the option `Minify` to concatenate stylesheets using the CSSMin Minify configuration. By default stylesheets are simply bundled in their original format, which could be their minified version when using CSS minification.

#### CSS Concat Group Filter

The group filter enables to create bundles of stylesheets. The configuration is an array of JSON objects. Each object is a concat group and contains the required properties `match` (stylesheets to match) and `group` (object with group details).

`match` is an array with strings or JSON objects. The JSON object format contains the required property `string` (match string) and optional properties `regex` (boolean to enable regular expression match) and `exclude` (exclude from group). The match list determines which stylesheets are included in the group.

`group` is an object with the required properties `title` and `key` and the optional property `id` (an HTML attribute ID to add to the stylesheet element). The `key` property is used in the file path which enables to recognize the concat group source file, e.g. `/cache/o10n/css/concat/1:group-key.css`. 

`minify` is a boolean that enables or disables the PHP minifier for concatenation.

`exclude` is a boolean that determines if the group should exclude stylesheets from minification.

#### Example Concat Group Configuration

```json
[
  {
	    "match": [
	    	"stylesheet.css", 
	    	{"string": "/plugin.*.css/", "regex":true}
	    ], 
	    "group": {
	    	"title":"Group title",
	    	"key": "group-file-key",
	    	"id": "id-attr"
	    }, 
	    "minify": true
	}
]
```

<details/>
  <summary>JSON schema for CSS Concat Group Filter config</summary>

```json
{
	"filter": {
        "title": "Concatenation filter",
        "type": "object",
        "properties": {
            "enabled": {
                "title": "CSS Concat",
                "type": "boolean",
                "default": true
            },
            "type": {
                "title": "Default include/exclude",
                "type": "string",
                "enum": [
                    "include",
                    "exclude"
                ],
                "default": "include"
            },
            "config": {
                "title": "Concatenation filter config",
                "type": "array",
                "items": {
                    "title": "Concatenation filter configuration",
                    "type": "object",
                    "properties": {
                        "group": {
                            "title": "Concat group configuration",
                            "type": "object",
                            "properties": {
                                "title": {
                                    "title": "A title for the group",
                                    "type": "string",
                                    "minLength": 1
                                },
                                "key": {
                                    "title": "A group reference key used in the file path.",
                                    "type": "string",
                                    "minLength": 1
                                },
                                "id": {
                                    "title": "An id attribute for the stylesheet element.",
                                    "type": "string",
                                    "minLength": 1
                                }
                            },
                            "required": ["title", "key"],
                            "additionalProperties": false
                        },
                        "match": {
                            "title": "An array of strings to match script elements.",
                            "type": "array",
                            "items": {
                                "oneOf": [{
                                    "title": "A string to match a script element.",
                                    "type": "string",
                                    "minLength": 1
                                }, {
                                    "title": "Filter config object",
                                    "type": "object",
                                    "properties": {
                                        "string": {
                                            "title": "A string to match a script element.",
                                            "type": "string",
                                            "minLength": 1
                                        },
                                        "regex": {
                                            "type": "boolean",
                                            "enum": [true]
                                        },
                                        "exclude": {
                                            "type": "boolean",
                                            "enum": [true]
                                        }
                                    },
                                    "required": ["string"],
                                    "additionalProperties": false
                                }]

                            },
                            "uniqueItems": true
                        },
                        "minify": {
                            "title": "Use minifier for concatenation.",
                            "type": "boolean",
                            "default": true
                        },
                        "exclude": {
                            "title": "Exclude from concatenation",
                            "type": "boolean",
                            "enum": [true]
                        }
                    },
                    "required": ["match"],
                    "additionalProperties": false
                },
                "uniqueItems": true
            }
        },
        "additionalProperties": false
    }
}
```
</details>
