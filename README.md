# Nitier Environment

A lightweight PHP library for managing environment variables from different formats such as `.env`, YAML, and JSON files. This library allows you to load environment variables from various sources, clean and cast values, and access environment variables conveniently.

## Features

- Load environment variables from `.env`, YAML, and JSON files.
- Support for automatic type casting (e.g., boolean, null, integers, floats, arrays, and objects).
- Support for serialized objects and JSON objects.
- Easy-to-use interface to access environment variables.
- Customizable root path for configuration files.

## Requirements

- PHP 7.4 or higher.
- Symfony YAML component (for YAML file support).

## Installation

You can install the library via Composer:

```bash
composer require nitier/environment
```

If you need support for YAML files, install the `symfony/yaml` package:

```bash
composer require symfony/yaml
```

## Usage

### 1. Setting the Root Path

To set the root path for your project (where `.env` and configuration files are located), use the `setRoot` method.

```php
use Nitier\Environment;

Environment::setRoot('/path/to/your/project');
```

### 2. Loading Environment Variables from `.env`

You can load environment variables from a `.env` file by using the `load` method. This will automatically parse the `.env` file and populate the environment variables.

```php
Environment::load('/path/to/.env');
```

If the `.env` file is located in the default root path, you can call:

```php
Environment::load();
```

### 3. Loading Environment Variables from YAML

To load variables from a YAML file, use the `loadYaml` method:

```php
Environment::loadYaml('/path/to/config.yml');
```

### 4. Loading Environment Variables from JSON

To load environment variables from a JSON file, use the `loadJson` method:

```php
Environment::loadJson('/path/to/config.json');
```

### 5. Accessing Environment Variables

You can retrieve environment variables using the `get` method:

```php
$value = Environment::get('VARIABLE_NAME', 'default_value');
```

### 6. Setting Environment Variables

To manually set an environment variable:

```php
Environment::set('VARIABLE_NAME', 'value');
```

You can also unset a variable by passing `null` as the value:

```php
Environment::set('VARIABLE_NAME', null);
```

### 7. Getting All Environment Variables

To retrieve all environment variables as an associative array:

```php
$variables = Environment::all();
```

### 8. Advanced Value Casting

The library supports automatic casting of values:

- `'null'` will be cast to `null`.
- `'true'` and `'false'` will be cast to `true` and `false`, respectively.
- Numeric values will be cast to `int` or `float`.
- JSON and serialized objects will be parsed accordingly.

### Example Usage

```php
use Nitier\Environment;

// Set root path for configuration files
Environment::setRoot('/path/to/project');

// Load variables from .env file
Environment::load();

// Get an environment variable
$envVar = Environment::get('DATABASE_URL');

// Get all environment variables
$allVars = Environment::all();

// Set an environment variable manually
Environment::set('NEW_VAR', 'value');
```

## Contributing

If you would like to contribute to this library, please fork the repository and submit a pull request. Make sure to write tests for any new features or bug fixes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
