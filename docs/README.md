# CSS Optimization Documentation
 
This documentation belongs to the WordPress plugin [CSS Optimization](https://wordpress.org/plugins/css-optimization/).

**The plugin is in beta. Please submit your feedback on the [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).**

The plugin provides in a advanced CSS optimization toolkit. Critical CSS, minification, concatenation, async loading, advanced editor, CSS Lint, Clean CSS (professional), beautifier and more.

Additional features can be requested on the [Github forum](https://github.com/o10n-x/wordpress-css-optimization/issues).

## Getting started

1. [CSS Code Optimization](#css-code-optimization)
2. [CSS Delivery Optimization](#css-delivery-optimization)
3. [Critical CSS / Above The Fold Optimization]()

# CSS Code Optimization

CSS code optimization consists of two main parts: minification and concatenation. Minification compresses CSS to reduce the size while concatenation merges multiple stylesheets into a single stylesheets for faster download performance.

## CSS Minify

The plugins provides the option to minify CSS code using [CSSMin](https://github.com/natxet/CssMin), a PHP based CSS compressor. You can control which stylesheets are included in the minification by enabling the filter option. 

#### CSS Minify Filter

When the filter is enabled you can choose the filter mode `Include List` or `Exclude List`. The Include List option excludes all stylesheets by default and only minifies stylesheets on the list, while the Exclude List option includes all stylesheets by default except the stylesheets on the list.

The filter list accepts parts of HTML stylesheet elements which makes it possible to match based on both CSS code and HTML attributes such as `id="stylesheet-id"`.

#### CSS Minify Options

The CSS minify options are documented on the Google Code Wiki page of CSSMin.

* Filters: https://code.google.com/archive/p/cssmin/wikis/MinifierFilters.wiki
* Plugins: https://code.google.com/archive/p/cssmin/wikis/MinifierPlugins.wiki

## CSS Concat

The plugins provides advanced functionality to concatenate stylesheets. It includes the option to create concat groups, e.g. bundle the most used stylesheets while keeping individual page related stylesheets out of the concatenation, an option to merge Media Query based stylesheets and to concatenate inline stylesheets.

You can chose the option `Minify` to concatenate stylesheets using the CSSMin Minify configuration. By default stylesheets are simply bundled in their original format, which could be their minified version when using CSS minification.

#### Concat Group Filter

The group filter enables to create bundles of stylesheets. The configuration is an array of JSON objects. Each object is a concat group and contains the required properties `match` (stylesheets to match) and `group` (object with group details).

![Concat Group Filter Editor](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/concat-group-filter.png)

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

---

**Note:** The plugin creates short CSS URLs by using a hash index. This means that the first concatenated stylesheet will have the filename `1.css`. The CDN option with CDN mask enables to load the stylesheets from `https://cdn.tld/1.css` resulting in the shortest URL possible.

When you use automated concatenation and the content of stylesheets change on each request, the hash index could grow to a big number. You can reset the hash index from the admin bar menu under `CSS Cache`. When you clear the CSS cache, the hash index is reset to 0.


# CSS Delivery Optimization

CSS delivery optimization enables asynchronous loading of stylesheets. The plugin provides in many options and unique innovations to achieve the best CSS loading performance.

**Note** You can enable debug modus by adding `define('O10N_DEBUG', true);` to wp-config.php. The browser console will show details about CSS loading and a [Performance API](https://developer.mozilla.org/nl/docs/Web/API/Performance) result for each step of the loading and rendering process.

## Async loading

The plugin provides an option to load stylesheets asynchronous using [loadCSS](https://github.com/filamentgroup/loadCSS) enhanced with Media Query support for responsive loading and an option to use `localStorage` cache for improved performance.

When using `rel="preload" as="style"` the stylesheets are always downloaded by the browser and the plugin will provide in a polyfill for browsers that do not support rel="preload". If you prefer to load stylesheets from localStorage, it may be best to not use rel="preload". When using debug modus, the Performance API result can provide an insight into what method provides the best loading performance for your website.

#### Async Load Config Filter

The async load config filter enables to fine tune async load configuration for individual stylesheets or concat groups.

![Async Load Config Filter](https://github.com/o10n-x/wordpress-css-optimization/blob/master/docs/images/async-load-config.png)

`match` is a string or regular expression to match a stylesheet URL.

`regex` is a boolean to enable regular expression based matching.

`async` is a boolean to enable or disable async loading for the matched stylesheet.

`media` is a string representing a Media Query to apply to the stylesheet.

`rel_preload` is a boolean to enable or disable `rel="preload" as="style"` based loading of the stylesheet.

`noscript` is a boolean to enable or disable a noscript fallback for the individual stylesheet.

`load_position` is a string with the two possible values `header` and `timing`. The option header will instantly start loading the stylesheet in the header (on javascript startup time) and timing will enable the `load_timing` option for further configuration.

`load_timing` is an object consisting of the required property `type` and optional timing method related properties. The following timing methods are currently available

* `domReady`
* `requestIdleCallback`
	* `timeout` Optionally, a timeout in milliseconds to force loading of the stylesheet.
	* `setTimeout` Optionally, a time in milliseconds for a setTimeout fallback for browsers that do not support requestIdleCallback. 
* `requestAnimationFrame`
	* `frame` The frame target (default is frame 1)
* `inview`
	* `selector` The CSS selector of the element to watch.
	* `offset` An offset in pixels from the element to trigger loading of the stylesheet.
* `media`
	* `media` The Media Query to trigger loading of the stylesheet.


#### Example Async Load Configuration

```json
[
  {
    "match": "/group-key-(x|y)/",
    "regex": true,
    "async": true,
    "media": "all",
    "noscript": true,
    "localStorage": {
      "max_size": 10000,
      "update_interval": 3600,
      "expire": 86400,
      "head_update": true
    }
  }
]
```

<details/>
  <summary>JSON schema for CSS Concat Group Filter config</summary>

```json
{
	"config": {
        "type": "array",
        "items": {
            "title": "Stylesheet filter configuration",
            "type": "object",
            "properties": {
                "match": {
                    "title": "A string or regular expression to match a stylesheet URL or group key.",
                    "type": "string",
                    "minLength": 1
                },
                "regex": {
                    "title": "Use regular expression match",
                    "type": "boolean",
                    "enum": [
                        true
                    ]
                },
                "media": {
                    "title": "Apply custom media query for responsive preloading.",
                    "type": "string",
                    "minLength": 1
                },
                "async": {
                    "title": "Load stylesheet async (include/exclude)",
                    "type": "boolean",
                    "default": true
                },
                "rel_preload": {
                    "title": "Load stylesheet using rel=preload",
                    "type": "boolean",
                    "default": false
                },
                "noscript": {
                    "title": "Add fallback stylesheets via <noscript>",
                    "type": "boolean",
                    "default": false
                },
                "load_position": {
                    "title": "Load position of CSS",
                    "type": "string",
                    "enum": ["header", "timing"],
                    "default": "header"
                },
                "load_timing": {
		            "title": "Timing configuration",
		            "oneOf": [{
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "domReady"
		                        ],
		                        "default": "domReady"
		                    }
		                },
		                "required": ["type"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "requestIdleCallback"
		                        ],
		                        "default": "requestIdleCallback"
		                    },
		                    "timeout": {
		                        "title": "Timeout to force execution.",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number",
		                            "minimum": 1
		                        }]
		                    },
		                    "setTimeout": {
		                        "title": "setTimeout fallback for browsers that do not support requestIdleCallback (leave blank to disable async execution)",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number",
		                            "minimum": 1
		                        }]
		                    }
		                },
		                "required": ["type"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "requestAnimationFrame"
		                        ],
		                        "default": "requestAnimationFrame"
		                    },
		                    "frame": {
		                        "title": "Frame number to start script execution.",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number",
		                            "minimum": 1,
		                            "default": 1
		                        }]
		                    }
		                },
		                "required": ["type"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "inview"
		                        ],
		                        "default": "inview"
		                    },
		                    "selector": {
		                        "title": "CSS selector",
		                        "type": "string",
		                        "minLength": 1
		                    },
		                    "offset": {
		                        "title": "Offset in pixels from the edge of the element.",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number"
		                        }]
		                    }
		                },
		                "required": ["type", "selector"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "media"
		                        ],
		                        "default": "media"
		                    },
		                    "media": {
		                        "title": "Media query",
		                        "type": "string",
		                        "minLength": 1
		                    }
		                },
		                "required": ["type", "media"]
		            }]
		        },
		        "render_timing": {
		            "title": "Timing configuration",
		            "oneOf": [{
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "domReady"
		                        ],
		                        "default": "domReady"
		                    }
		                },
		                "required": ["type"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "requestIdleCallback"
		                        ],
		                        "default": "requestIdleCallback"
		                    },
		                    "timeout": {
		                        "title": "Timeout to force execution.",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number",
		                            "minimum": 1
		                        }]
		                    },
		                    "setTimeout": {
		                        "title": "setTimeout fallback for browsers that do not support requestIdleCallback (leave blank to disable async execution)",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number",
		                            "minimum": 1
		                        }]
		                    }
		                },
		                "required": ["type"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "requestAnimationFrame"
		                        ],
		                        "default": "requestAnimationFrame"
		                    },
		                    "frame": {
		                        "title": "Frame number to start script execution.",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number",
		                            "minimum": 1,
		                            "default": 1
		                        }]
		                    }
		                },
		                "required": ["type"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "inview"
		                        ],
		                        "default": "inview"
		                    },
		                    "selector": {
		                        "title": "CSS selector",
		                        "type": "string",
		                        "minLength": 1
		                    },
		                    "offset": {
		                        "title": "Offset in pixels from the edge of the element.",
		                        "oneOf": [{
		                            "type": "string",
		                            "enum": [""]
		                        }, {
		                            "type": "number"
		                        }]
		                    }
		                },
		                "required": ["type", "selector"]
		            }, {
		                "type": "object",
		                "properties": {
		                    "type": {
		                        "title": "Timing method",
		                        "type": "string",
		                        "enum": [
		                            "media"
		                        ],
		                        "default": "media"
		                    },
		                    "media": {
		                        "title": "Media query",
		                        "type": "string",
		                        "minLength": 1
		                    }
		                },
		                "required": ["type", "media"]
		            }]
		        },
                "localStorage": {
                    "title": "Override stylesheet cache configuration",
                    "oneOf": [{
                        "type": "boolean"
                    }, {
                        "type": "object",
                        "properties": {
                            "max_size": {
                                "title": "Maximum size of stylesheet to store in cache.",
                                "type": "number",
                                "minimum": 1
                            },
                            "update_interval": {
                                "title": "Interval to update the cache.",
                                "type": "number",
                                "minimum": 1
                            },
                            "expire": {
                                "title": "Expire time in seconds.",
                                "type": "number",
                                "minimum": 1
                            },
                            "head_update": {
                                "title": "Use HTTP HEAD request to update cache based on etag / last-modified headers.",
                                "type": "boolean",
                                "default": true
                            }
                        },
                        "anyOf": [{
                            "required": ["max_size"]
                        }, {
                            "required": ["update_interval"]
                        }, {
                            "required": ["expire"]
                        }, {
                            "required": ["head_update"]
                        }],
                        "additionalProperties": false
                    }]
                }
            },
            "required": ["match", "async"],
            "additionalProperties": false
        },
        "uniqueItems": true
    }
}
```
</details>

---