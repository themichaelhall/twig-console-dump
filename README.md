# Twig Console Dump

[![Build Status](https://travis-ci.org/themichaelhall/twig-console-dump.svg?branch=master)](https://travis-ci.org/themichaelhall/twig-console-dump)
[![AppVeyor](https://ci.appveyor.com/api/projects/status/github/themichaelhall/twig-console-dump?branch=master&svg=true)](https://ci.appveyor.com/project/themichaelhall/twig-console-dump/branch/master)
[![codecov.io](https://codecov.io/gh/themichaelhall/twig-console-dump/coverage.svg?branch=master)](https://codecov.io/gh/themichaelhall/twig-console-dump?branch=master)
[![StyleCI](https://styleci.io/repos/154186112/shield?style=flat&branch=master)](https://styleci.io/repos/154186112)
[![License](https://poser.pugx.org/michaelhall/twig-console-dump/license)](https://packagist.org/packages/michaelhall/twig-console-dump)
[![Latest Stable Version](https://poser.pugx.org/michaelhall/twig-console-dump/v/stable)](https://packagist.org/packages/michaelhall/twig-console-dump)
[![Total Downloads](https://poser.pugx.org/michaelhall/twig-console-dump/downloads)](https://packagist.org/packages/michaelhall/twig-console-dump)

Twig extension for dumping a variable to the browser console.

## Requirements

- PHP >= 7.1
- Twig >= 2.4

## Install with composer

``` bash
$ composer require michaelhall/twig-console-dump
```

## Basic usage

After enabling this extension, the ```dump()``` function can be used to dump a variable to the browser console.

Internally, this is done via inline JavaScript using ```console.log()``` functions.

The variable is only dumped to the console if debug mode is enabled in Twig, otherwise nothing is outputted.

### Dump a variable

```
{{ dump(foo) }}
```

#### Result

<p>
<img src="https://mh.staticfiles.se/img/console-dump-1.png" width="240" height="106" alt="Dump a variable">
</p>

### Dump a variable with a label

```
{{ dump(foo, 'Bar') }}
```

#### Result

<p>
<img src="https://mh.staticfiles.se/img/console-dump-2.png" width="240" height="106" alt="Dump a variable with a label">
</p>

### Additional options

```
{{ dump(foo, 'Bar', {'script-nonce': 'baz'}) }}
```

- script-nonce: The ```nonce``` attribute to use in the inline ```<script>``` tag for CSP-protection.

## License

MIT