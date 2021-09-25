# syberisle/filereflection

![Build Status](https://github.com/SyberIsle/filereflection/actions/workflows/tests.yml/badge.svg)

This library complements the PHP [reflection API](http://www.php.net/manual/en/book.reflection.php) with the missing ReflectionFile class.

A few other libraries were available to do this already, but this one implements an important feature missing from other
implementations that could be found: resolution of local type-names according to the
[name resolution rules](http://www.php.net/manual/en/language.namespaces.rules.php).

## Install

Via Composer
```sh
$ composer require syberisle/filereflection
```

## Usage

The interface is very simple:

    ReflectionFile {

        public __construct( string $path )

        public string getPath ( void )
        public string getNamespaceName ( void )
        public string resolveName ( string $name )
        public ReflectionClass getClass ( string $name )
        public ReflectionClass[] getClasses ( void )

    }

Usage of course is straight forward too:

    use SyberIsle\FileReflection\ReflectionFile;

    $file = new ReflectionFile('/path/to/MyNamespace/MyClass.php');

    var_dump($file->resolveName('MyOtherClass')); // => '\MyNamespace\MyOtherClass'

Note that this library currently omits reflection/enumeration of functions, constants, etc.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- [Rasmus Schultz](https://github.com/mindplay-dk)
- [David Lundgren](https://github.com/dlundgren)
- [All Contributors](../../contributors)

## License
The LGPL 3.0+ License. Please see [License File](LICENSE.md) for more information.
